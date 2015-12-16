<?php
namespace Plugin\OmisePaymentGateway;

use Eccube\Plugin\AbstractPluginManager;
class PluginManager extends AbstractPluginManager {
	private $app;
	
	public function install($config, $app) {
		$this->app = $app;
		$this->migrationSchema($app, __DIR__ . '/Migration', $config['code']);
	}
	public function uninstall($config, $app) {
		$this->app = $app;
		$this->migrationSchema($app, __DIR__ . '/Migration', $config['code'], 0);
	}
	public function enable($config, $app) {
		$this->app = $app;
	}
	public function disable($config, $app) {
		$this->app = $app;
	}
	public function update($config, $app) {
		$this->app = $app;
	}
	
	private function insertOmisePayment() {
		$payment = $this->app['eccube.repository.payment']->findOrCreate(0);
		$payment->setMethod('クレジットカード決済');
		// 手数料設定フラグ
		$payment->setChargeFlg(0);
		// 手数料
		$payment->setCharge(0);
		$payment->setFixFlg(1);
		$payment->setCreateDate(new \DateTime());
		$payment->setUpdateDate(new \DateTime());
		// 最小金額
		$payment->setRuleMin(1);
		$payment->setDelFlg(1);
		$payment->setUpdateDate(new \DateTime());
		
		$this->app['orm.em']->persist($payment);
		$this->app['orm.em']->flush();
	}
}
