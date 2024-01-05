<?php
/**
 * This file is part of EC-CUBE Plugin OmiseExt
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */
class OmiseConfig
{
    /**
     * @var string
     */
    public $payment_id;
    public $testPublicKey;
    public $testSecretKey;
    public $livePublicKey;
    public $liveSecretKey;

    /**
     * @var boolean
     */
    public $sandbox;
    public $autocapture;

    /**
     * Return this instance to use this class as a singleton
     * @return OmiseConfig  return static instance of this class
     */
    public static function getInstance()
    {
        static $instance;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Initialize its properties and Omise API keys for Omise-PHP library
     */
    public function __construct()
    {
        // get payment_id from dtb_payment.payment_id where dtb_payment.memo03 = 'OmiseExt'
        $this->payment_id = $this->getPaymentId();

        // get config omise data from dtb_plugin.free_field1
        $omise = $this->configOmise();

        // Omise API TEST keys
        $this->testPublicKey = $omise['testPublicKey'];
        $this->testSecretKey = $omise['testSecretKey'];

        // Omise API LIVE keys
        $this->livePublicKey = $omise['livePublicKey'];
        $this->liveSecretKey = $omise['liveSecretKey'];

        // Sandbox flag to switch between TEST and LIVE
        $this->sandbox       = $omise['sandbox'];

        // Autocapture flag for Charge API
        $this->autocapture   = $omise['autocapture'];

        // Store the default key sets
        // note. You can use other key as an argument on functions in Omise-PHP Library.
        if ($this->sandbox == 1) {
            define('OMISE_PUBLIC_KEY', $this->testPublicKey);
            define('OMISE_SECRET_KEY', $this->testSecretKey);
        } else {
            define('OMISE_PUBLIC_KEY', $this->livePublicKey);
            define('OMISE_SECRET_KEY', $this->liveSecretKey);
        }
    }

    /**
     * Return Omise Payment config data
     *
     * @return string  Omise's payment id string
     */
    public function getPaymentId()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $info     = $objQuery->getRow(OMISE_CONFIG_PAYMENT_ID, 'dtb_plugin', "plugin_code = '" . OMISE_PLUGIN_CODE . "'");
        return $info[OMISE_CONFIG_PAYMENT_ID];
    }

    public function isPaymentId($payment_id)
    {
        return ($this->payment_id == $payment_id) ? true : false;
    }

    /**
     * Return Omise config data
     *
     * @return array  Associative array of Omise config
     */
    public function configOmise()
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $info     = $objQuery->getRow(OMISE_CONFIG_OMISE, "dtb_plugin", "plugin_code = '" . OMISE_PLUGIN_CODE . "'");
        return unserialize($info[OMISE_CONFIG_OMISE]);
    }

    /**
     * Update Omise API keys and config data to dtb_plugin.free_field1 where dtb_plugin.plugin_code = 'OmiseExt'
     * @param  array  $array  hash which contain array('testPublicKey' => String, 'testSecretKey' => String, 'livePublicKey' => String, 'liveSecretKey' => String, 'sandbox' => Boolean, 'sutocapture' => Boolean)
     * @return void
     */
    public function updateOmise($array)
    {
        return $this->updatePluginFreeField(serialize($array), OMISE_CONFIG_OMISE);
    }

    /**
     * Update this plugin's payment id to dtb_plugin.free_field2 where dtb_plugin.plugin_code = 'OmiseExt`'
     * This function just use on the first installation of this plugin to EC-CUBE.
     * @param  string  $payment_id  dtb_payment.payment_id where dtb_payment.memo03 = 'OmiseExt'
     * @return void
     */
    public function updatePaymentId($payment_id)
    {
        $this->updatePluginFreeField($payment_id, OMISE_CONFIG_PAYMENT_ID);
    }

    /**
     * Shortcut function to update Omise config and payment_id
     * @param  string  $value     The value which store to $fieldname
     * @param  string  $fieldname Column name
     * @return array              Return errors if failed to update
    */
    private function updatePluginFreeField($value, $fieldname)
    {
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();
        $sqlval = array();
        $sqlval[$fieldname] = $value;
        $sqlval['update_date'] = 'CURRENT_TIMESTAMP';
        $where = "plugin_code = '" . OMISE_PLUGIN_CODE . "'";
        $objQuery->update('dtb_plugin', $sqlval, $where);
        return $objQuery->commit();
    }

    /**
     * Enable Omise Payment on eccube
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public function enableOmisePayment($arrPlugin)
    {
        // Enable Omise payment gateway
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $params   = array("del_flg" => 0, "update_date" => "CURRENT_TIMESTAMP");
        $where    = "payment_id = '" . $this->payment_id . "'";
        $objQuery->update("dtb_payment", $params, $where);
    }

    /**
     * Disable Omise Payment on eccube
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public function disableOmisePayment($arrPlugin)
    {
        // Disable Omise payment gateway
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $params   = array("del_flg" => 1, "update_date" => "CURRENT_TIMESTAMP");
        $where    = "payment_id = '" . $this->payment_id . "'";
        $objQuery->update("dtb_payment", $params, $where);
    }

    /**
     * Uninstall Omise Module and Plugin from eccube
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public function uninstall($arrPlugin)
    {
        // Delete data/downloads/module/mdl_omise
        SC_Helper_FileManager_Ex::deleteFile(OMISE_MDL_REALDIR);
        // Delete plugin html OmiseExt directory
        SC_Helper_FileManager_Ex::deleteFile(PLUGIN_HTML_REALDIR . "OmiseExt");
    }

    /**
     * Install Omise Module and Plugin from eccube
     * @param  array $arrPlugin プラグイン情報の連想配列(dtb_plugin)
     * @return void
     */
    public function install($arrPlugin)
    {
        // SC_Query_Ex singleton instance
        $objQuery = SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();

        // Update initial omise config data to dtb_plugin.free_field1 where dtb_plugin.plugin_code = 'OmiseExt'
        $this->updateOmise(array('testPublicKey' => '', 'testSecretKey' => '', 'livePublicKey' => '', 'liveSecretKey' => '', 'sandbox' => true, 'autocapture' => true));

        // EC-CUBE always create a payment record to dtb_payment
        // But this logic cause sort trouble at `受注管理`
        // Therefore this plugin recycle the payment_id

        // Find this plugin's payment_id
        $payment = $objQuery->getRow('payment_id', 'dtb_payment', "memo03 = '" . OMISE_PLUGIN_CODE . "'");

        // First time installation
        if (is_null($payment) || empty($payment)) {
            // Store rank value to show this payment on the first in the payment list
            $rank = $objQuery->select("MAX(rank) AS maxrank", 'dtb_payment');
            $rank = $rank[0]['maxrank'] + 1;

            // Store OMISE_PLUGIN_CODE to memo03 as a key to find Omise payment_id
            $params = array(
                'payment_id'     => $objQuery->nextVal('dtb_payment_payment_id'),
                'payment_method' => 'クレジットカード決済 [Omise]',
                'charge'         => 0,
                'charge_flg'     => 1,
                'del_flg'        => 1,
                'creator_id'     => $_SESSION['member_id'],
                'create_date'    => 'CURRENT_TIMESTAMP',
                'update_date'    => 'CURRENT_TIMESTAMP',
                'charge_flg'     => 1,
                'rule_max'       => OMISE_AMOUNT_MIN,
                'upper_rule'     => OMISE_AMOUNT_MAX,
                'module_id'      => OMISE_MDL_ID,
                'module_path'    => OMISE_MDL_REALDIR . 'payment.php',
                'memo03'         => OMISE_PLUGIN_CODE,
                'rank'           => $rank,
                'fix'            => 2
            );
            $objQuery->insert('dtb_payment', $params);
            $payment_id = $params['payment_id'];
        } else {
            // Not first time so use payment_id which already stored
            $payment_id = $payment['payment_id'];
        }

        // Update credit payment id to dtb_plugin.free_field2
        $this->updatePaymentId($payment_id);

        // Commit
        $objQuery->commit();

        // Copy Plugin Thumbnail from plugin directory to EC-CUBE PLUGIN_HTML_REALDIR
        copy(OMISE_PLUGIN_REALDIR . 'logo.png', PLUGIN_HTML_REALDIR . 'OmiseExt/logo.png');

        // Copy payment.php to module directory
        if (mkdir(OMISE_MDL_REALDIR, 0777)) {
            copy(OMISE_PLG_MDL_DIR . 'payment.php', OMISE_MDL_REALDIR . 'payment.php');
        }
    }
}

// Just call getInstance to init OmiseConfig
OmiseConfig::getInstance();
