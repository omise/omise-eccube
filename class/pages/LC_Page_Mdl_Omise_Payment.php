<?php
/**
 * This file is part of EC-CUBE Plugin OmiseExt module
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */

require_once(CLASS_EX_REALDIR . "page_extends/LC_Page_Ex.php");
require_once(OMISE_CLASS_REALDIR . 'OmiseWrapper.php');
require_once(OMISE_MODELS_REALDIR . 'Omise_Models_Charge.php');
require_once(OMISE_MODELS_REALDIR . 'Omise_Models_Customer.php');

/**
 * Omise module payment page class
 *
 * @package Page
 * @author Akira / Omise
 */
class LC_Page_Mdl_Omise_Payment extends LC_Page_Ex
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->httpCacheControl('nocache');
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    public function action()
    {
        $this->selectTemplate();

        $order_id = $this->getOrderId();
        if ($order_id === NULL) {
            SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', true, '注文情報の取得が出来ませんでした。<br>この手続きは無効となりました。');
        }

        $objPurchase     = new SC_Helper_Purchase_Ex();
        $objCharge       = new Omise_Models_Charge($order_id);
        $this->tpl_title = $objCharge->arrOrder['payment_method'];

        $this->validateOrderConsistency($objCharge->arrOrder);

        $objCustomer     = new Omise_Models_Customer($objCharge->arrOrder['customer_id']);
        $objFormParam    = new SC_FormParam_Ex();
        $this->initFormParam($objFormParam);

        switch ($this->getMode()) {
            case 'delete_card':
                $objCustomer->deleteDefaultCard();
                break;
            
            case 'other_card':
                break;
            
            case 'pay':
                $objFormParam->setParam($_REQUEST);
                $objFormParam->convParam();
                $this->arrErr = $this->checkFormParamError($objFormParam);
                if (empty($this->arrErr)) {
                    $arrData  = $objFormParam->getHashArray();
                    $arrPayer = array();
                    $message  = $this->selectPayer($objCustomer, $arrData, $arrPayer);

                    if ($message !== null) {
                        $this->tpl_omise_charge_error = $message;
                        break;
                    }
                    $message = $objCharge->createCharge($arrPayer);
                    if ($message !== null) {
                        $this->tpl_omise_charge_error = $message;
                        break;
                    }
                    SC_Response_Ex::sendRedirect(SHOPPING_COMPLETE_URLPATH);
                    SC_Response_Ex::actionExit();
                    break;
                }
                break;

            default:
                $this->objSavedCard = $objCustomer->fetchSavedCardForCustomer();
                break;
        }

        $this->tpl_is_registered_customer = $objCustomer->getCustomerId() !== 0;
        $this->tpl_omise_publishable_key  = $arrModuleSetting['publishable_key'];
        $this->tpl_url = $_SERVER['REQUEST_URI'];
    }

    /**
     * Set template, reject mobile
     * @return void
     */
    private function selectTemplate()
    {
        switch (SC_Display_Ex::detectDevice()) {
            case DEVICE_TYPE_MOBILE:
                SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', true, '携帯電話からはクレジットカード決済を利用できせん');
            break;
            
            default:
                $this->tpl_mainpage = OMISE_TEMPLATES_REALDIR . 'default/load_payment_module.tpl';
            break;
        }
    }

    /**
     * Return the order_id which stored on the SESSION
     * @return string|null order_id or null
     */
    private function getOrderId()
    {
        if (isset($_SESSION['order_id'])
            && !SC_Utils_Ex::isBlank($_SESSION['order_id'])
            && SC_Utils_Ex::sfIsInt($_SESSION['order_id'])) {
            return $_SESSION['order_id'];
        }

        return NULL;
    }

    /**
     * Validate order consistency
     * @param  array  $arrOrder  order array
     * @return void
     */
    private function validateOrderConsistency($arrOrder)
    {
        switch ($arrOrder['status']) {
            case ORDER_PENDING:
                break;

            case ORDER_NEW:
            case ORDER_PAY_WAIT:
            case ORDER_PRE_END:
                SC_Response_Ex::sendRedirect(SHOPPING_COMPLETE_URLPATH);
                SC_Response_Ex::actionExit();
                break;

            default:
                SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', true, '注文情報の状態が不正です。<br>この手続きは無効となりました。');
        }

        $objPayment = new SC_Helper_Payment_Ex();
        $arrPayment = $objPayment->get($arrOrder['payment_id']);

        if ($arrPayment === null || $arrPayment['module_id'] !== OMISE_MDL_ID) {
            SC_Utils_Ex::sfDispSiteError(FREE_ERROR_MSG, '', true, '支払方法が不正です。<br>この手続きは無効となりました。');
        }
    }

    /**
     * Initialize form param
     * @param  SC_FormParam_Ex $objFormParam
     * @return void
     */
    private function initFormParam($objFormParam)
    {
        $max_length = 256;
        $objFormParam->addParam('カードトークン', 'omise_token', $max_length, 'a', array('MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
        $objFormParam->addParam('支払方法', 'card_info', $max_length, 'a', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
    }

    /**
     * Check form param error
     * @param  SC_FormParam_Ex $objFormParam
     * @return array  errors
     */
    private function checkFormParamError($objFormParam)
    {
        $arrErr      = $objFormParam->checkError();
        $card_info   = $objFormParam->getValue('card_info');
        $omise_token = $objFormParam->getValue('omise_token');
        
        if (($card_info === 'token' || $card_info === 'customer_from_token') && empty($omise_token)) {
            $arrErr['omise_token'] = 'カードトークンが入力されていません';
        }
        return $arrErr;
    }

    /**
     * Select payment method from customer data and param
     *
     * @param  SC_Mdl_Omise_Models_Customer  $objCustomer
     * @param  array                         $arrPaymentData 利用者の入力
     * @param  array                         &$arrOutPayer   output: 選択された支払いソースの情報
     * @return string|null                   決済時に発生したエラーを購入者に説明するメッセージ
     */
    private function selectPayer($objCustomer, $arrPaymentData, &$arrOutPayer)
    {
        switch ($arrPaymentData['card_info']) {
            case 'customer':
                $customer_id = $objCustomer->loadOmiseCustomerId();
                if ($customer_id === null) {
                    return 'カード情報が見付かりませんでした。カード情報を再入力してください';
                }
                $arrOutPayer['customer'] = $customer_id;
                break;

            case 'customer_from_token':
                $customer_id = $objCustomer->saveCardForCustomer($arrPaymentData['omise_token']);
                if ($customer_id === null) {
                    return 'カード情報の登録に失敗したため決済できませんでした。カード情報を再入力してください';
                }
                $arrOutPayer['customer'] = $customer_id;
                break;

            case 'token':
                $arrOutPayer['card'] = $arrPaymentData['omise_token'];
                break;

            default:
                return '未知の決済方法です。再度入力してください';
        }

        return null;
    }
}
