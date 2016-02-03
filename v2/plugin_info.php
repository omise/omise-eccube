<?php
class plugin_info {
    /* Required variables */
    static $PLUGIN_CODE         = 'OmisePaymentGateway';
    static $CLASS_NAME          = 'OmisePaymentGateway';
    static $PLUGIN_NAME         = 'Omise Payment Gateway';
    static $PLUGIN_VERSION      = '0.2';
    static $COMPLIANT_VERSION   = '2.13.0';
    static $AUTHOR              = 'Omise';
    static $DESCRIPTION         = 'Omise Payment Gateway';
    static $PLUGIN_SITE_URL     = 'https://www.omise.co';
    static $AUTHOR_SITE_URL     = 'https://www.omise.co';
    static $LICENSE             = 'MITL';
    static $HOOK_POINTS       = array(
    		array('LC_Page_Shopping_Payment_action_after', 'shoppingPaymentActionAfter'),
    		array('LC_Page_Shopping_Payment_action_confirm', 'shoppingPaymentActionConfirm'),
    		array('LC_Page_Shopping_Confirm_action_after', 'shoppingConfirmActionAfter'),
    		array('LC_Page_Admin_Order_Edit_action_after', 'adminOrderEditActionAfter'),
    		array('LC_Page_Admin_Order_Edit_action_before', 'adminOrderEditActionBefore'),
    		array('loadClassFileChange', 'loadClassFileChange'),
    		array('prefilterTransform', 'prefilterTransform')
    );
}
