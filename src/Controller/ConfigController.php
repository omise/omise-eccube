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
        $config = $configService->getPluginConfig();
        
		return $app['view']->render('OmisePaymentGateway/View/config/edit.twig',
            array(
            	'plugin_name' => $config['name'],
            	'company_name' => $config['const']['OMISE_COMPANY_NAME'],
            	'url_ja' => $config['const']['OMISE_URL_JA'],
            ));
	}
}
