<?php
/**
 * This file is part of EC-CUBE Plugin OmiseExt
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */

// Plugin code to find this plugin info from dtb_plugin table
define('OMISE_PLUGIN_CODE',           'OmiseExt');
define('OMISE_MDL_CODE',              'mdl_omise');

// Plugin version
define('OMISE_PLUGIN_VERSION',        '2.3');

// Namespace
define('OMISE_PLUGIN_NAMESPACE',      'OmiseECCUBE');

// Use with parama description for Customers API
define('OMISE_API_DESC_CUSTOMER',     OMISE_PLUGIN_NAMESPACE . ' ' . OMISE_PLUGIN_CODE . ' v' . OMISE_PLUGIN_VERSION . ' customer_id: ');
define('OMISE_API_DESC_CHARGE',       OMISE_PLUGIN_NAMESPACE . ' ' . OMISE_PLUGIN_CODE . ' v' . OMISE_PLUGIN_VERSION . ' order_id: ');

// Plugin and module name
define('OMISE_PLUGIN_NAME',           'Omise決済プラグイン');
define('OMISE_MDL_NAME',              'Omise決済モジュール');

// Plugin directory
define('OMISE_PLUGIN_REALDIR',        PLUGIN_UPLOAD_REALDIR . OMISE_PLUGIN_CODE . '/');
define('OMISE_LIB_PHP_REALDIR',       OMISE_PLUGIN_REALDIR . 'omise-php/lib/');
define('OMISE_TEMPLATES_REALDIR',     OMISE_PLUGIN_REALDIR . 'templates/');
define('OMISE_CLASS_REALDIR',         OMISE_PLUGIN_REALDIR . 'class/');
define('OMISE_MODELS_REALDIR',        OMISE_CLASS_REALDIR . 'models/');
define('OMISE_PAGES_REALDIR',         OMISE_CLASS_REALDIR . 'pages/');

// Module directory
define('OMISE_PLG_MDL_DIR',           OMISE_PLUGIN_REALDIR . OMISE_MDL_CODE . '/');
define('OMISE_MDL_REALDIR',           MODULE_REALDIR . OMISE_MDL_CODE . '/');

// Module ID, smile from Thailand :)
define('OMISE_MDL_ID',                '555');

// Currency code
define('OMISE_CURRENCY',              'jpy');

// Minimum amount for Omise Charge
define('OMISE_AMOUNT_MIN',            '100');

// Maxmum amount for Omise Charge
define('OMISE_AMOUNT_MAX',            '999999');

// Column name to store config data on dtb_plugin table where plugin_code = `OmiseExt`
define('OMISE_CONFIG_OMISE',          'free_field1');
// EC-CUBE stores payment data to dtb_payment and we need to store this key to recognize which payment order used
define('OMISE_CONFIG_PAYMENT_ID',     'free_field2');

// Store omise customer map array to dtb_payment.memo04
define('OMISE_MDL_CUSTOMER_DATA_COL', 'memo04');

// Store omise charge map array to dtb_order.memo01
define('OMISE_MDL_CHARGE_DATA_COL',   'memo01');

// Load Omise-PHP library and Config singleton class
require_once OMISE_LIB_PHP_REALDIR . 'Omise.php';
require_once OMISE_CLASS_REALDIR . 'OmiseConfig.php';

// Define user agent on omise-php lib
define('OMISE_USER_AGENT_SUFFIX', OMISE_PLUGIN_NAMESPACE . '/' . OMISE_PLUGIN_VERSION . ' EC-CUBE/' . ECCUBE_VERSION);

// Set Omise API version as `2015-11-17`
define('OMISE_API_VERSION', '2015-11-17');
