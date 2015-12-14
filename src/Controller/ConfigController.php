<?php
namespace Plugin\OmisePaymentGateway\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Plugin\OmisePaymentGateway\Form\Type\ConfigType;

class ConfigController {
	public $app;
	
	public function edit(Application $app, Request $request) {
		$this->app = $app;
		$configService = $this->app['eccube.plugin.service.omise_pg_config'];
        $configYml = $configService->getPluginConfig();
        $omiseConfig = $this->app['eccube.plugin.omise.repository.omise_pg_config']
            ->findOneBy(array('code' => $configYml['code']));
        
        $type = new ConfigType($this->app, $omiseConfig->getUnserializeInfo());
        $form = $this->app['form.factory']->createBuilder($type)->getForm();
        
		return $app['view']->render('OmisePaymentGateway/View/config/edit.twig',
            array(
            	'plugin_name' => $configYml['name'],
            	'company_name' => $configYml['const']['OMISE_COMPANY_NAME'],
            	'url_ja' => $configYml['const']['OMISE_URL_JA'],
            ));
	}
}
