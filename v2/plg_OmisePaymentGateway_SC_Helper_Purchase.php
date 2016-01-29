<?php
require_once CLASS_REALDIR . 'helper/SC_Helper_Purchase.php';
require_once PLUGIN_UPLOAD_REALDIR.'OmisePaymentGateway/omise-php/lib/Omise.php';

class plg_OmisePaymentGateway_SC_Helper_Purchase extends SC_Helper_Purchase {
	private $plg_OmisePaymentGateway_currency = 'thb';
	
	/**
	 * 受注を完了する.
	 *
	 * 下記のフローで受注を完了する.
	 *
	 * 1. トランザクションを開始する
	 * 2. カートの内容を検証する.
	 * 3. 受注一時テーブルから受注データを読み込む
	 * 4. ユーザーがログインしている場合はその他の発送先へ登録する
	 * 5. 受注データを受注テーブルへ登録する
	 * 6. トランザクションをコミットする
	 *
	 * 実行中に, 何らかのエラーが発生した場合, 処理を中止しエラーページへ遷移する
	 *
	 * 決済モジュールを使用する場合は対応状況を「決済処理中」に設定し,
	 * 決済完了後「新規受付」に変更すること
	 *
	 * @param  integer $orderStatus 受注処理を完了する際に設定する対応状況
	 * @return void
	 */
	public function completeOrder($orderStatus = ORDER_NEW) {
		$objQuery =& SC_Query_Ex::getSingletonInstance();
		$objSiteSession = new SC_SiteSession_Ex();
		$objCartSession = new SC_CartSession_Ex();
		$objCustomer = new SC_Customer_Ex();
		$customerId = $objCustomer->getValue('customer_id');
	
		$objQuery->begin();
		if (!$objSiteSession->isPrePage()) {
			// エラー時は、正当なページ遷移とは認めない
			$objSiteSess->setNowPage('');
	
			SC_Utils_Ex::sfDispSiteError(PAGE_ERROR, $objSiteSession);
		}
	
		$uniqId = $objSiteSession->getUniqId();
		$this->verifyChangeCart($uniqId, $objCartSession);
	
		$orderTemp = $this->getOrderTemp($uniqId);
		
		try {
			// Omise決済が選ばれているか判定
			$omisePaymentInfo = $objQuery->select('info', 'plg_omisepaymentgateway_config', "name = 'payment_config'");
			$omisePaymentInfo = unserialize($omisePaymentInfo[0]['info']);
			if($orderTemp['payment_id'] == $omisePaymentInfo['credit_payment_id']) {
				// Omiseへの接続情報の初期化
				$omiseConfigInfo = $objQuery->select('info', 'plg_omisepaymentgateway_config', "name = 'omise_config'");
				$omiseConfigInfo = unserialize($omiseConfigInfo[0]['info']);
				if(!defined('OMISE_PUBLIC_KEY')) define('OMISE_PUBLIC_KEY', $omiseConfigInfo['pkey']);
				if(!defined('OMISE_SECRET_KEY')) define('OMISE_SECRET_KEY', $omiseConfigInfo['skey']);

				// Omise決済情報を取得
				$omiseObject = unserialize($orderTemp['plg_omise_payment_gateway']);
				// 仮売上計上
				$omiseCharge = OmiseCharge::create(array(
						'amount' => $orderTemp['payment_total'],
						'currency' => $this->plg_OmisePaymentGateway_currency,
						'customer' => $omiseObject['customer'],
						'card' => $omiseObject['card'],
						'capture' => false
					));
				
				// chargeidをオブジェクトとテーブルに反映
				$omiseObject['charge'] = array();
				$omiseObject['charge'][] = array('enable' => true, 'id' => $omiseCharge['id'], 'status' => '仮売上済み', 'create_date' => $omiseCharge['created'], 'amount' => $omiseCharge['amount'], 'captured' => false, 'refunds' => array());
				$orderTemp['plg_omise_payment_gateway'] = serialize($omiseObject);
				$count = $objQuery->update('dtb_order_temp', array('plg_omise_payment_gateway' => serialize($omiseObject), 'update_date' => 'CURRENT_TIMESTAMP'), "order_temp_id = '".$orderTemp['order_temp_id']."'");
				if($count != 1) {
					throw new Exception('OmisePaymentGateway 決済処理中にエラーが発生しました。');
				}
			} else {
				// Omise決済ではない場合、Omise決済のデータを削除する
				$objQuery->update('dtb_order_temp', array('plg_omise_payment_gateway' => null, 'update_date' => 'CURRENT_TIMESTAMP'), "order_temp_id = '".$orderTemp['order_temp_id']."'");
				$orderTemp['plg_omise_payment_gateway'] = null;
			}
		} catch (Exception $e) {
			$objQuery->rollback();
			$_SESSION['plg_OmisePaymentGateway_error'] = $e->getMessage();
			SC_Response_Ex::sendRedirect(SHOPPING_PAYMENT_URLPATH);
			SC_Response_Ex::actionExit();
		}
	
		$orderTemp['status'] = $orderStatus;
		$cartkey = $objCartSession->getKey();
		$order_id = $this->registerOrderComplete($orderTemp, $objCartSession, $cartkey);
		$isMultiple = SC_Helper_Purchase::isMultiple();
		$shippingTemp =& $this->getShippingTemp($isMultiple);
		foreach ($shippingTemp as $shippingId => $val) {
			$this->registerShipmentItem($order_id, $shippingId, $val['shipment_item']);
		}
	
		$this->registerShipping($order_id, $shippingTemp);
		$objQuery->commit();
	
		//会員情報の最終購入日、購入合計を更新
		if ($customerId > 0) {
			SC_Customer_Ex::updateOrderSummary($customerId);
		}
	
		$this->cleanupSession($order_id, $objCartSession, $objCustomer, $cartkey);
	
		GC_Utils_Ex::gfPrintLog('order complete. order_id=' . $order_id);
	}
}
