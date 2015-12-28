<?php
class plugin_info {
    /* Required variables */
    static $PLUGIN_CODE         = 'OmisePaymentGateway';
    static $CLASS_NAME          = 'OmisePaymentGateway';
    static $PLUGIN_NAME         = 'Omise Payment Gateway';
    static $PLUGIN_VERSION      = '0.1';
    static $COMPLIANT_VERSION   = '2.13.5';
    static $AUTHOR              = 'Omise';
    static $DESCRIPTION         = 'Omise Payment Gateway';
    static $PLUGIN_SITE_URL     = 'https://www.omise.co';
    static $AUTHOR_SITE_URL     = 'https://www.omise.co';
    static $LICENSE             = 'MITL';
    static $HOOK_POINTS       = array(
    		array('LC_Page_Shopping_Payment_action_after', 'shoppingPaymentActionAfter'),
    		array('LC_Page_Shopping_Confirm_action_after', 'shoppingConfirmActionAfter'),
    		array('LC_Page_Shopping_Payment_action_confirm', 'shoppingPaymentActionConfirm'),
    		array('prefilterTransform', 'prefilterTransform')
    );
}
