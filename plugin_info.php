<?php
class plugin_info
{
    /* Required variables */
    static $PLUGIN_CODE       = 'OmiseExt';
    static $PLUGIN_NAME       = 'Omise決済プラグイン';
    static $PLUGIN_VERSION    = '2.3';
    static $COMPLIANT_VERSION = '2.17';
    static $AUTHOR            = 'Omise';
    static $DESCRIPTION       = 'Omise決済をEC-CUBEへ追加します。';
    static $PLUGIN_SITE_URL   = 'https://github.com/omise/omise-eccube';
    static $AUTHOR_SITE_URL   = 'https://www.omise.co';
    static $LICENSE           = 'MIT';
    static $CLASS_NAME        = 'OmiseExt';
    static $HOOK_POINTS       = array(
        array('LC_Page_Admin_Order_Edit_action_before', 'beforeAdminOrderEdit'),
        array('LC_Page_Admin_Order_Edit_action_after', 'afterAdminOrderEdit'),
        array('prefilterTransform', 'prefilterTransform')
    );
}
