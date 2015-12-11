<?php
namespace Plugin\OmisePaymentGateway\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
class ConfigController {
	public $app;
	
	public function edit(Application $app, Request $request) {
		$this->app = $app;
		$configService = $this->app['eccube.plugin.service.omise_pg_config'];
		return $app['view']->render('OmisePaymentGateway/View/config/edit.twig',
            array(
            	'title' => 'Omiseセッティング',
            ));
	}
}
