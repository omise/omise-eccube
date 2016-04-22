<?php
/**
 * This file is part of EC-CUBE Plugin OmiseExt module
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */

require_once PLUGIN_UPLOAD_REALDIR . "OmiseExt/inc/include.php";
require_once OMISE_PAGES_REALDIR . "LC_Page_Mdl_Omise_Payment.php";

$objPage = new LC_Page_Mdl_Omise_Payment();
$objPage->init();
$objPage->process();
