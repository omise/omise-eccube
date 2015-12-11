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
				'\\Plugin\\OmisePaymentGateway\\Controller\\ConfigController::index'
				)->bind('plugin_OmisePaymentGateway_config');
		
		// TODO Service\OmiseConfigServiceを作る
		$app['eccube.plugin.service.omise_config'] = $app->share(function () use ($app) {
			return new \Plugin\RemisePayment\Service\RemiseConfigService($app);
		});
	}
	
	public function boot(BaseApplication $app) { }
}
