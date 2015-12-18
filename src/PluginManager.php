<?php
namespace Plugin\OmisePaymentGateway;

use Eccube\Plugin\AbstractPluginManager;
class PluginManager extends AbstractPluginManager {
	private $app;
	
	public function install($config, $app) {
		$this->app = $app;
		
		// DBの作成
		$this->migrationSchema($app, __DIR__ . '/Migration', $config['code']);
		
		// 支払い方法の追加
		$payment = $this->insertPayment('credit');
		$this->updateOmiseConfig('payment_id', serialize(['credit_payment_id' => $payment->getId()]));
	}
	public function uninstall($config, $app) {
		$this->app = $app;
		
		// DBの削除
		$this->migrationSchema($app, __DIR__ . '/Migration', $config['code'], 0);
	}
	public function enable($config, $app) {
		$this->app = $app;
		
		// 支払い方法の有効化
		$omiseConfig = $this->selectOmiseConfig('payment_id');
		$info = unserialize($omiseConfig['info']);
		$this->updatePayment($info['credit_payment_id'], 0);
	}
	public function disable($config, $app) {
		$this->app = $app;

		// 支払い方法の無効化
		$omiseConfig = $this->selectOmiseConfig('payment_id');
		$info = unserialize($omiseConfig['info']);
		$this->updatePayment($info['credit_payment_id'], 1);
	}
	public function update($config, $app) {
		$this->app = $app;
	}
	
	private function selectOmiseConfig($name) {
		$select = "SELECT * FROM plg_omise_config WHERE name = '$name'";
		return $this->app['db']->executeQuery($select)->fetch();
	}
	
	private function updateOmiseConfig($name, $info) {
        $updateDate = date('Y-m-d H:i:s');
		$update = "UPDATE plg_omise_config SET info = '$info', update_date = '$updateDate'"
						. " WHERE name = '$name'";
		$this->app['db']->executeUpdate($update);
	}
	
	private function insertPayment($type = 'credit') {
		switch ($type) {
			case 'credit':
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
				
				return $payment;
				
			default :
				return null;
		}
	}
	
	private function updatePayment($paymentId, $deleteFlg) {
		$updateDate = date('Y-m-d H:i:s');
		$update = "UPDATE dtb_payment SET del_flg = $deleteFlg, update_date = '$updateDate'"
			." WHERE payment_id = $paymentId";
		return $this->app['db']->executeUpdate($update);
	}
}
