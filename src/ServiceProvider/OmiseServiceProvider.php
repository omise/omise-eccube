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
				'/'.$app['config']['admin_route'].'/plugin/OmisePaymentGateway/config',
				'\\Plugin\\OmisePaymentGateway\\Controller\\ConfigController::edit'
				)->bind('plugin_OmisePaymentGateway_config');
		// Service登録
		$app['eccube.plugin.service.omise_pg_config'] = $app->share(function () use ($app) {
			return new \Plugin\OmisePaymentGateway\Service\OmiseConfigService($app);
		});
		// リポジトリの登録
		$app['eccube.plugin.omise.repository.omise_pg_config'] = $app->share(function () use ($app) {
			return $app['orm.em']->getRepository('Plugin\OmisePaymentGateway\Entity\OmiseConfig');
		});
	}
	
	public function boot(BaseApplication $app) { }
}
