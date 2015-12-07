<?php
namespace Plugin\OmisePayment\ServiceProvider;

use Eccube\Application;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;
class OmiseServiceProvider implements ServiceProviderInterface {
	public function register(BaseApplication $app) {
		// プラグイン固有設定画面
		$app->match(
				'/' . $app["config"]["admin_route"] . '/plugin/OmisePayment/config',
				'\\Plugin\\OmisePayment\\Controller\\ConfigController::edit'
				)->bind('plugin_OmisePayment_config');
	}
	
	public function boot(BaseApplication $app) {
		
	}
}
