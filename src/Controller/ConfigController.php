<?php
namespace Plugin\OmisePaymentGateway\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Plugin\OmisePaymentGateway\Form\Type\ConfigType;

class ConfigController {
	public $app;
	
	public function edit(Application $app, Request $request) {
		$this->app = $app;
		
		$configService = $this->app['eccube.plugin.service.omise_pg_config'];
        $configYml = $configService->getPluginConfig();
        
        $omiseConfig = $this->app['eccube.plugin.omise.repository.omise_pg_config']
            ->findOneBy(array('code' => $configYml['code']));
        
        $type = new ConfigType($this->app, (array)$omiseConfig->getInfo());
        $form = $this->app['form.factory']->createBuilder($type)->getForm();
        
        if('POST' === $this->app['request']->getMethod()) {
			$form->handleRequest($this->app['request']);
			
            if ($form->isValid()) {
            	die;
                $formData = $form->getData();

                $omiseConfig->setInfo($formData);
                
                $app['orm.em']->persist($omiseConfig);
                $app['orm.em']->flush();
            }
        }
        
		return $app['view']->render('OmisePaymentGateway/View/config/edit.twig',
            array(
            	'form' => $form->createView(),
            	'plugin_name' => $configYml['name'],
            	'company_name' => $configYml['const']['OMISE_COMPANY_NAME'],
            	'url_ja' => $configYml['const']['OMISE_URL_JA'],
            ));
	}
}
