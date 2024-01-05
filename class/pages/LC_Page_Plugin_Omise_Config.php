<?php
/**
 * This file is part of EC-CUBE Plugin OmiseExt
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */

require_once CLASS_EX_REALDIR . "page_extends/admin/LC_Page_Admin_Ex.php";
require_once PLUGIN_UPLOAD_REALDIR . "OmiseExt/inc/include.php";

/**
 * This class renders Omise Payment Gateway setting page
 * Use on PLUGIN_UPLOAD_REALDIR . "OmiseExt/config.php"
 * For more info, LC_Page_Admin.php can help you
 */
class LC_Page_Plugin_Omise_Config extends LC_Page_Admin_Ex
{
    /**
     * This array use in template file to refer values for inputs in form
     * @var array
     */
    public $arrForm;

    /**
     * Initialize page
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->tpl_mainpage = OMISE_TEMPLATES_REALDIR . 'admin/omiseext_config.tpl';
        $this->tpl_subtitle = OMISE_PLUGIN_NAME . '設定';
    }

    /**
     * Process
     *
     * @return void
     */
    public function process()
    {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Action
     *
     * @return void
     */
    public function action()
    {
        $objFormParam = new SC_FormParam_Ex();
        $this->initParam($objFormParam);
        $objFormParam->setParam($_POST);
        $objFormParam->convParam();
        $arrForm = array();

        switch ($this->getMode()) {
            // Edit ( update Omise Payment Gateway setting )
            case 'edit':
                $arrForm = $objFormParam->getHashArray();
                $this->arrErr = $objFormParam->checkError();
                // Validate if there is no config panel form validation errors
                if (count($this->arrErr) == 0) {
                    // Update config data on to dtb_plugin table, where plugin_code = "OmiseExt"
                    // Updation error check
                    $result = OmiseConfig::getInstance()->updateOmise($arrForm);

                    if (gettype($result) != "array") {
                        $this->tpl_onload = "alert('登録しました。');";
                    } else {
                        $this->tpl_onload = "alert('登録に失敗しました。再度お試しください。);";
                    }
                } else {
                    $this->tpl_onload = "alert('入力に間違えがあります。再度お試しください。);";
                }
                break;

            // Default ( set default values to $arrForm )
            default:
                // Put default values for config panel form
                $arrForm['livePublicKey'] = OmiseConfig::getInstance()->livePublicKey;
                $arrForm['liveSecretKey'] = OmiseConfig::getInstance()->liveSecretKey;
                $arrForm['testPublicKey'] = OmiseConfig::getInstance()->testPublicKey;
                $arrForm['testSecretKey'] = OmiseConfig::getInstance()->testSecretKey;
                $arrForm['sandbox']       = OmiseConfig::getInstance()->sandbox;
                $arrForm['autocapture']   = OmiseConfig::getInstance()->autocapture;
                break;
        }
        $this->arrForm = $arrForm;
        $this->setTemplate($this->tpl_mainpage);
    }

    /**
     * Initialize params for admin form of OmiseExt
     *
     * @param  SC_FormParam_Ex $objFormParam SC_FormParam instance
     * @return void
     */
    public function initParam(&$objFormParam)
    {
        // bigger limit length than current key lenght in order to prepare the future change
        $max_key_length = 256;

        $objFormParam->addParam('LIVE Public Key', 'livePublicKey', $max_key_length, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
        $objFormParam->addParam('LIVE Secret Key', 'liveSecretKey', $max_key_length, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
        $objFormParam->addParam('TEST Public Key', 'testPublicKey', $max_key_length, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
        $objFormParam->addParam('TEST Secret Key', 'testSecretKey', $max_key_length, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
        $objFormParam->addParam('Sandbox', 'sandbox', STEXT_LEN, 'KVa', array());
        $objFormParam->addParam('Auto Capture', 'autocapture', STEXT_LEN, 'KVa', array());
    }
}
