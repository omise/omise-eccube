<?php
/**
 * This file is part of EC-CUBE Plugin OmiseExt
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */

require_once PLUGIN_UPLOAD_REALDIR . "OmiseExt/inc/include.php";
require_once OMISE_CLASS_REALDIR . "OmiseWrapper.php";
require_once OMISE_MODELS_REALDIR . "Omise_Models_Charge.php";
require_once OMISE_MODELS_REALDIR . "Omise_Models_Customer.php";

class OmiseExt extends SC_Plugin_Base
{
    /**
     * install will execute on plugin installation.
     *
     * @param  array $arrPlugin plugin_infoを元にDBに登録されたプラグイン情報(dtb_plugin)
     * @return void
     */
    public static function install($arrPlugin, $objPluginInstaller = null)
    {
        OmiseConfig::getInstance()->install($arrPlugin);
    }

    /**
     * uninstall will ececute on uninstalltion
     *
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public static function uninstall($arrPlugin, $objPluginInstaller = null)
    {
        OmiseConfig::getInstance()->uninstall($arrPlugin);
    }

    /**
     * 稼働
     * enableはプラグインを有効にした際に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public static function enable($arrPlugin, $objPluginInstaller = null)
    {
        OmiseConfig::getInstance()->enableOmisePayment($arrPlugin);
    }

    /**
     * 停止
     * disableはプラグインを無効にした際に実行されます.
     * 引数にはdtb_pluginのプラグイン情報が渡されます.
     *
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public static function disable($arrPlugin, $objPluginInstaller = null)
    {
        OmiseConfig::getInstance()->disableOmisePayment($arrPlugin);
    }

    /* -------------------- Hook Points -------------------- */
    /**
     * prefilterコールバック関数
     * テンプレートの変更処理を行います.
     *
     * @param  string     &$source  テンプレートのHTMLソース
     * @param  LC_Page_Ex $objPage  ページオブジェクト
     * @param  string     $filename テンプレートのファイル名
     * @return void
     */
    public function prefilterTransform(&$source, LC_Page_Ex $objPage, $filename)
    {
        $objTransform = new SC_Helper_Transform($source);
        switch ($objPage->arrPageLayout['device_type_id']) {
            case DEVICE_TYPE_MOBILE:
            case DEVICE_TYPE_SMARTPHONE:
            case DEVICE_TYPE_PC:
                break;

            case DEVICE_TYPE_ADMIN:
            case null: // 未設定は admin
                if (strpos($filename, 'order/edit.tpl') !== false) {
                    // 個別受注画面に OmiseのChargeを連携させる
                    $template = OMISE_TEMPLATES_REALDIR . 'admin/omiseext_admin_order_charge_add.tpl';
                    $objTransform->select('div#order')->appendFirst(file_get_contents($template));
                }
                break;
            default:
                break;
        }

        // 変更を実行します
        $source = $objTransform->getHTML();
    }

    /**
     * hook function called before LC_Page_Admin_Order_Edit
     * Send requests to Omise if mode is of plg_omiseext
     */
    public function beforeAdminOrderEdit(LC_Page_Ex $objPage)
    {
        // Capture the Omise Charge
        // 実売上化
        if ($objPage->getMode() === 'plg_omiseext_capture') {
            $_GET['mode'] = 'recalculate';

            $order_id = $_POST['order_id'];
            if (empty($order_id)) {
                return;
            }
            $objCharge = new Omise_Models_Charge($order_id);
            $message = $objCharge->capture();
            if ($message !== null) {
                $objPage->plg_omiseext_capture_error = $message;
            }
        }

        // Refund the Omise Charge
        // 全額返金
        if ($objPage->getMode() === 'plg_omiseext_refund') {
            $_GET['mode'] = 'recalculate';

            $order_id = $_POST['order_id'];
            if (empty($order_id)) {
                return;
            }
            $objCharge = new Omise_Models_Charge($order_id);
            $message = $objCharge->refund();
            if ($message !== null) {
                $objPage->plg_omiseext_refund_error = $message;
            }
        }
    }

    /**
     * hook function called after LC_Page_Admin_Order_Edit
     * Set template variables to show Omise charge statuses
     */
    public function afterAdminOrderEdit(LC_Page_Ex $objPage)
    {
        $order_id = $objPage->arrForm['order_id']['value'];
        if (empty($order_id)) {
            return;
        }

        $objCharge = new Omise_Models_Charge($order_id);
        // Sync Omise Charge data with ECCUBE db
        $objCharge->syncOmise();
        $objPage->plg_omiseext_objCharge = $objCharge;
    }
}

