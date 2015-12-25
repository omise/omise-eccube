<?php
require_once PLUGIN_UPLOAD_REALDIR.'OmisePaymentGateway/LC_Page_Plugin_OmisePaymentGateway_Config.php';

$objPage = new LC_Page_Plugin_OmisePaymentGateway_Config();
register_shutdown_function(array($objPage, 'destroy'));
$objPage->init();
$objPage->process();
?>
