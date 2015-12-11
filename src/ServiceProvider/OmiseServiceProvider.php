<?php
namespace Plugin\OmisePaymentGateway\ServiceProvider;

use Eccube\Application;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
use Plugin\OmisePaymentGateway\Entity\OmiseConfig;
class OmiseServiceProvider implements ServiceProviderInterface {
	public function register(BaseApplication $app) {
		// ルーティングのとうろく
		$app->match(
				'/' . $app['config']['admin_route'] . '/plugin/OmisePaymentGateway/config',
				'\\Plugin\\OmisePaymentGateway\\Controller\\ConfigController::edit'
				)->bind('plugin_OmisePaymentGateway_config');
		
	}
	
	public function boot(BaseApplication $app) { }
}
