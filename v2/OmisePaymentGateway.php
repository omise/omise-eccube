<?php
require_once PLUGIN_UPLOAD_REALDIR.'OmisePaymentGateway/omise-php/lib/Omise.php';
class OmisePaymentGateway extends SC_Plugin_Base {
	const TBL_OMISE_CONFIG = 'plg_OmisePaymentGateway_config';
	const CONFIG_PAYMENT = 'payment_config';
	const CONFIG_OIMISE = 'omise_config';
	
	/**
	 * @param array $arrPlugin
	 */
	public function install($arrPlugin) {
		$objQuery = &SC_Query_Ex::getSingletonInstance();

		// OmiseConfigテーブルの作成
		$fields = [
				'id INT NOT NULL PRIMARY KEY',
				'name TEXT NOT NULL',
				'info TEXT NOT NULL',
				'delete_flg SMALLINT NOT NULL',
				"create_date TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"update_date TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'"
			];
		$sql = sprintf('CREATE TABLE %s (%s)', self::TBL_OMISE_CONFIG, implode(',', $fields));
		$objQuery->query($sql);
		
		// クレカ決済の作成
		$rank = $objQuery->select("MAX(rank) AS 'max_rank'", 'dtb_payment');
		$rank = $rank[0]['max_rank'] + 1;
		
		$params = [
				'payment_id' => $objQuery->nextVal('dtb_payment_payment_id'),
				'payment_method' => 'クレジットカード決済',
				'charge' => 0,
				'charge_flg' => 1,
				'del_flg' => 1,
				'creator_id' => $_SESSION['member_id'],
				'create_date' => 'CURRENT_TIMESTAMP',
				'update_date' => 'CURRENT_TIMESTAMP',
				'charge_flg' => 1,
				'rule_max' => 100,
				'rank' => $rank,
				'fix' => 2
		];
		$objQuery->insert('dtb_payment', $params);
		
		// Omise初期設定の書き込み
		$objQuery->insert(self::TBL_OMISE_CONFIG, [
				'id' => 1,
				'name' => self::CONFIG_OIMISE,
				'info' => serialize(array('pkey' => '', 'skey' => '')),
				'create_date' => 'CURRENT_TIMESTAMP',
				'update_date' => 'CURRENT_TIMESTAMP'
		]);
		$objQuery->insert(self::TBL_OMISE_CONFIG, [
				'id' => 2,
				'name' => self::CONFIG_PAYMENT,
				'info' => serialize(array('credit_payment_id' => $params['payment_id'])),
				'create_date' => 'CURRENT_TIMESTAMP',
				'update_date' => 'CURRENT_TIMESTAMP'
		]);
		
		// 受注テーブルにOmiseToken用のカラムを追加
		$objDb = new SC_Helper_DB_Ex();
		$objDb->sfColumnExists('dtb_order_temp', 'plg_omise_payment_gateway', 'TEXT', '', true);
		$objDb->sfColumnExists('dtb_order', 'plg_omise_payment_gateway', 'TEXT', '', true);
	}

	/**
	 * @param array $arrPlugin
	 */
	public function uninstall($arrPlugin) {
		// OmiseConfigテーブルの削除
		$objQuery = &SC_Query_Ex::getSingletonInstance();
		$objQuery->query('DROP TABLE '.self::TBL_OMISE_CONFIG);
	}
	
	/**
	 * @param array $arrPlugin
	 */
	public function enable($arrPlugin) {
		// クレジット決済の有効化
		$objQuery = &SC_Query_Ex::getSingletonInstance();
    	$info = $objQuery->select('info', self::TBL_OMISE_CONFIG, "name = '".self::CONFIG_PAYMENT."'");
    	$info = unserialize($info[0]['info']);
    	
    	$objQuery->update('dtb_payment', array('del_flg' => 0, 'update_date' => 'CURRENT_TIMESTAMP'), 'payment_id = '.$info['credit_payment_id']);
	}

	/**
	 * @param array $arrPlugin
	 */
	public function disable($arrPlugin) {
		// クレカ決済の無効化
		$objQuery = &SC_Query_Ex::getSingletonInstance();
    	$info = $objQuery->select('info', self::TBL_OMISE_CONFIG, "name = '".self::CONFIG_PAYMENT."'");
    	$info = unserialize($info[0]['info']);
    	
    	$objQuery->update('dtb_payment', array('del_flg' => 1, 'update_date' => 'CURRENT_TIMESTAMP'), 'payment_id = '.$info['credit_payment_id']);
	}
	
	private static function selectConfig($configName) {
		$objQuery = &SC_Query_Ex::getSingletonInstance();
		$info = $objQuery->select('info', self::TBL_OMISE_CONFIG, "name = '$configName'");
		return unserialize($info[0]['info']);
	}
	
	/* -------------------- Hook Points -------------------- */
	/**
	 * @param LC_Page_Shopping_Payment $objPage 
	 * 支払い方法選択画面に表示するオブジェクトのセット関連
	 * @return void
	 */
	public function shoppingPaymentActionAfter($objPage) {
    	$info = self::selectConfig(self::CONFIG_PAYMENT);
		$objPage->arrForm['plg_OmisePaymentGateway_payment_id'] = $info['credit_payment_id'];
    	$info = self::selectConfig(self::CONFIG_OIMISE);
		$objPage->arrForm['plg_OmisePaymentGateway_pkey'] = $info['pkey'];
		$objPage->arrForm['plg_OmisePaymentGateway_expiration_years'] = array();
		$objPage->arrForm['plg_OmisePaymentGateway_expiration_months'] = array();
		if(isset($_SESSION['plg_OmisePaymentGateway_error'])) {
			$objPage->arrForm['plg_OmisePaymentGateway_error'] = $_SESSION['plg_OmisePaymentGateway_error'];
			unset($_SESSION['plg_OmisePaymentGateway_error']);
		}
		
		$y = date('Y');
		$ey = $y + 10;
		while($y <= $ey) {
			$objPage->arrForm['plg_OmisePaymentGateway_expiration_years'][] = $y++;
		}
		for($i = 1; $i <= 12; ++$i) {
			$objPage->arrForm['plg_OmisePaymentGateway_expiration_months'][] = sprintf('%02d', $i);;
		}
	}

	/**
	 * @param LC_Page_Shopping_Payment $objPage
	 * OmiseTokenの検証〜dtb_temp_orderへTokenの保存
	 * @return void
	 */
	public function shoppingPaymentActionConfirm($objPage) {
		$paymentInfo = self::selectConfig(self::CONFIG_PAYMENT);
    	if($_POST['payment_id'] == $paymentInfo['credit_payment_id']) {
    		$tokenID = $_POST['plg_OmisePaymentGateway_token'];
			
    		// TokenIDの検証
    		try {
	    		$this->initOmiseKeys();
	    		$token = OmiseToken::retrieve($tokenID);
    		} catch(OmiseException $e) {
    			$_SESSION['plg_OmisePaymentGateway_error'] = 'E0001: 利用できないカードが選択されました。';
    			SC_Response_Ex::sendRedirect(SHOPPING_PAYMENT_URLPATH);
    			SC_Response_Ex::actionExit();
    		}

    		// TokenIDに問題がないので、dtb_order_tempにTokenIDを追加
    		$orderTempID = $this->getOrderTempID();
    		$omiseObj = array('token' => $tokenID, 'charge' => '');
	    	$objQuery = &SC_Query_Ex::getSingletonInstance();
	    	$count = $objQuery->update('dtb_order_temp', array('plg_omise_payment_gateway' => serialize($omiseObj), 'update_date' => 'CURRENT_TIMESTAMP'), "order_temp_id = '$orderTempID'");
	    	if($count !== 1) {
    			$_SESSION['plg_OmisePaymentGateway_error'] = 'E0002: サーバエラーが発生しました。カード決済がご利用いただけません。';
    			SC_Response_Ex::sendRedirect(SHOPPING_PAYMENT_URLPATH);
    			SC_Response_Ex::actionExit();
	    	}
    	}
	}
	
	/**
	 * @param LC_Page_Shopping_Confirm $objPage
	 * 入力内容確認画面で表示する情報の登録
	 * @return void
	 */
	public function shoppingConfirmActionAfter($objPage) {
		$paymentInfo = self::selectConfig(self::CONFIG_PAYMENT);
		$objQuery = &SC_Query_Ex::getSingletonInstance();
		$orderTempID = $this->getOrderTempID();
		$payment = $objQuery->select('payment_id, plg_omise_payment_gateway', 'dtb_order_temp', "order_temp_id = '$orderTempID'");
		
		if($payment[0]['payment_id'] == $paymentInfo['credit_payment_id']) {
			$objPage->arrForm['plg_OmisePaymentGateway_enabled'] = true;
			$objOmise = unserialize($payment[0]['plg_omise_payment_gateway']);
			try {
				$this->initOmiseKeys();
				$token = OmiseToken::retrieve($objOmise['token']);
				$objPage->arrForm['plg_OmisePaymentGateway_name'] = $token['card']['name'];
				$objPage->arrForm['plg_OmisePaymentGateway_number'] = '**** **** **** '.$token['card']['last_digits'];
			} catch (OmiseException $e) {
    			$_SESSION['plg_OmisePaymentGateway_error'] = 'E0003: 利用できないカードが選択されました。';
    			SC_Response_Ex::sendRedirect(SHOPPING_PAYMENT_URLPATH);
    			SC_Response_Ex::actionExit();
			}
		} else {
			$objPage->arrForm['plg_OmisePaymentGateway_enabled'] = false;
		}
	}
	
	/**
	 * @param LC_Page_Shopping_Confirm $objPage
	 * 決済完了画面に行く前にOmiseChargeを完了する
	 * @return void
	 */
	public function shoppingConfirmActionBefore($objPage) {
		if($objPage->getMode() == 'confirm') {
			$paymentInfo = self::selectConfig(self::CONFIG_PAYMENT);
			$objQuery = &SC_Query_Ex::getSingletonInstance();
			$orderTempID = $this->getOrderTempID();
			$payment = $objQuery->select('payment_id, plg_omise_payment_gateway', 'dtb_order_temp', "order_temp_id = '$orderTempID'");
			
			if($payment[0]['payment_id'] == $paymentInfo['credit_payment_id']) {
				$objOmise = unserialize($payment[0]['plg_omise_payment_gateway']);
				try {
					$this->initOmiseKeys();
					OmiseCharge::create(array(
							'amount' => 1000,
							'currency' => 'thb',
							'card' => $objOmise['token']
					));
				} catch (OmiseException $e) {
					$_SESSION['plg_OmisePaymentGateway_error'] = 'E0003: 利用できないカードが選択されました。';
					SC_Response_Ex::sendRedirect(SHOPPING_PAYMENT_URLPATH);
					SC_Response_Ex::actionExit();
				}
			}
		}
	}
	
	private function getOrderTempID() {
		return $_SESSION['site']['uniqid'];
	}
	
	//SC_FormParam
	public function addParam($class_name, $param) {
// 		if(strpos($class_name, 'LC_Page_Shopping') !== false) {
// 			if(array_key_exists('payment_id', $_POST)) {
// 	    		$paymentInfo = self::selectConfig(self::CONFIG_PAYMENT);
// 				if($_POST['payment_id'] == $paymentInfo['credit_payment_id']) {
// 					$param->addParam('Token', 'plg_OmisePaymentGateway_token', CREDIT_NO_LEN, '', array('EXIST_CHECK'), '', true);
// 				}
// 			}
// 		}
	}
	
	// prefilterTransform
	public function prefilterTransform(&$source, LC_Page_Ex $objPage, $filename) {
		$objTransform = new SC_Helper_Transform($source);
		switch ($objPage->arrPageLayout['device_type_id']) {
			case DEVICE_TYPE_MOBILE:
			case DEVICE_TYPE_SMARTPHONE:
			case DEVICE_TYPE_PC:
				if(strpos($filename, 'shopping/payment.tpl') !== false) {
					$objTransform->select('#payment')->insertAfter(file_get_contents(PLUGIN_UPLOAD_REALDIR.'OmisePaymentGateway/templates/shopping/plg_OmisePaymentGateway_payment.tpl'));
				} else if(strpos($filename, 'shopping/confirm.tpl') !== false) {
					$objTransform->select('#form1 table', 5)->insertAfter(file_get_contents(PLUGIN_UPLOAD_REALDIR.'OmisePaymentGateway/templates/shopping/plg_OmisePaymentGateway_confirm.tpl'));
				}
				break;
	
			case DEVICE_TYPE_ADMIN:
			default:
				break;
		}
	
		$source = $objTransform->getHTML();
	}
	
	private function initOmiseKeys() {
		$configInfo = self::selectConfig(self::CONFIG_OIMISE);
		
		if(!defined('OMISE_PUBLIC_KEY')) define('OMISE_PUBLIC_KEY', $configInfo['pkey']);
		if(!defined('OMISE_SECRET_KEY')) define('OMISE_SECRET_KEY', $configInfo['skey']);
	}
}
