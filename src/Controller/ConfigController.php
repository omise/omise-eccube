<?php
namespace Plugin\OmisePayment\Controller;
use Eccube\Application;

class ConfigController {
	public function edit(Application $app) {
		
		return $app['view']->render('OmisePaymentGateway/View/config/edit.twig',
            array(
            		
            ));
	}
}
