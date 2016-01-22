<?php
require_once PLUGIN_UPLOAD_REALDIR.'OmisePaymentGateway/omise-php/lib/Omise.php';
class OmisePaymentGateway extends SC_Plugin_Base {
	private $plg_OmisePaymentGateway_currency = 'thb';
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
		
		// 受注テーブルにOmise用のカラムを追加
		$objDb = new SC_Helper_DB_Ex();
		$objDb->sfColumnExists('dtb_order_temp', 'plg_omise_payment_gateway', 'TEXT', '', true);
		$objDb->sfColumnExists('dtb_order', 'plg_omise_payment_gateway', 'TEXT', '', true);
		
		// 顧客テーブルにOmise顧客ID用のカラムを追加
		$objDb->sfColumnExists('dtb_customer', 'plg_omise_payment_gateway_id', 'TEXT', '', true);
		
		copy(PLUGIN_UPLOAD_REALDIR . 'OmisePaymentGateway/logo.png', PLUGIN_HTML_REALDIR . 'OmisePaymentGateway/logo.png');
	}

	/**
	 * @param array $arrPlugin
	 */
	public function uninstall($arrPlugin) {
		// OmiseConfigテーブルの削除
		$objQuery = &SC_Query_Ex::getSingletonInstance();
		$objQuery->query('DROP TABLE '.self::TBL_OMISE_CONFIG);

		SC_Helper_FileManager_Ex::deleteFile(PLUGIN_HTML_REALDIR . 'OmisePaymentGateway/logo.png');
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
		$info = $objQuery->getRow('info', self::TBL_OMISE_CONFIG, "name = '$configName'");
		return unserialize($info['info']);
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
		$objCustomer = new SC_Customer();
		if($objCustomer->isLoginSuccess()) $objPage->tpl_login = true;
		
		$y = date('Y');
		$ey = $y + 10;
		while($y <= $ey) {
			$objPage->arrForm['plg_OmisePaymentGateway_expiration_years'][] = $y++;
		}
		for($i = 1; $i <= 12; ++$i) {
			$objPage->arrForm['plg_OmisePaymentGateway_expiration_months'][] = sprintf('%02d', $i);;
		}
		
		// カード情報の取得
		$objPage->arrForm['plg_OmisePaymentGateway_customer_cards'] = array();
		$objPage->arrForm['plg_OmisePaymentGateway_customer_cards'][] = array(
				'id' => 'plg_OmisePaymentGateway_token',
				'value' => '',
				'display' => '新規入力',
				'onclick' => "$('#plg_OmisePaymentGateway_tbl_new_card').css({display:'table'})",
				'checked' => 'checked="checked"');
		$omiseCustomerID = $this->getOmiseCustomerID();
		try {
			$omiseCustomer = OmiseCustomer::retrieve($omiseCustomerID);
			$i = 0;
			foreach ($omiseCustomer['cards']['data'] as $row) {
				$objPage->arrForm['plg_OmisePaymentGateway_customer_cards'][] = array(
						'id' => 'plg_OmisePaymentGateway_card_select'.$i, 
						'value' => 'existing'.','.$omiseCustomer['id'].','.$row['id'], 
						'display' => '**** **** **** '.$row['last_digits'], 
						'onclick' => "$('#plg_OmisePaymentGateway_tbl_new_card').css({display:'none'})",
						'checked' => '');
				++$i;
			}
		} catch(Exception $e) {
			/** Do Nothing */
		}
	}

	/**
	 * @param LC_Page_Shopping_Payment $objPage
	 * OmiseTokenの検証〜dtb_temp_orderへ決済情報の保存
	 * @return void
	 */
	public function shoppingPaymentActionConfirm($objPage) {
		$paymentInfo = self::selectConfig(self::CONFIG_PAYMENT);
    	if($_POST['payment_id'] == $paymentInfo['credit_payment_id']) {
    		if(!isset($_POST['plg_OmisePaymentGateway_card_select']) || strlen($_POST['plg_OmisePaymentGateway_card_select']) <= 0) {
    			$_SESSION['plg_OmisePaymentGateway_error'] = '不正な入力を検知しました。再度お試しください。';
    			SC_Response_Ex::sendRedirect(SHOPPING_PAYMENT_URLPATH);
    			SC_Response_Ex::actionExit();
    		}
    		
    		$omiseCardPostObj = $_POST['plg_OmisePaymentGateway_card_select'];
    		$omiseCardPostObjAry = preg_split('/,/', $omiseCardPostObj);
    		
    		$postError = false;
    		switch ($omiseCardPostObjAry[0]) {
    			case 'new':
    				if(count($omiseCardPostObjAry) != 2) {
    					$postError = true;
    					break;
    				}
    				// TokenIDの検証
    				try {
    					$this->initOmiseKeys();
    					$omiseToken = OmiseToken::retrieve($omiseCardPostObjAry[1]);
    					 
    					// TokenIDに問題がないので、Customerに追加してdtb_order_tempに反映
    					if(@$_POST['plg_OmisePaymentGateway_memory'] == 1) {
    						$omiseCustomerID = $this->getOmiseCustomerID();
    					} else {
    						$omiseCustomerID = $this->getOmiseCustomerID(true);
    					}
    					
    					$orderTempID = $this->getOrderTempID();
    					 
    					$omiseCustomer = OmiseCustomer::retrieve($omiseCustomerID);
    					$omiseCustomer->update(array(
    							'card' => $omiseCardPostObjAry[1]
    					));
    					 
    					$omiseObj = array('customer' => $omiseCustomerID, 'card' => $omiseToken['card']['id']);
    					 
    					$objQuery = &SC_Query_Ex::getSingletonInstance();
    					$objQuery->update('dtb_order_temp', array('plg_omise_payment_gateway' => serialize($omiseObj), 'update_date' => 'CURRENT_TIMESTAMP'), "order_temp_id = '$orderTempID'");
    				} catch(Exception $e) {
    					$_SESSION['plg_OmisePaymentGateway_error'] = 'エラーが発生しました。'.$e->getMessage();
    					SC_Response_Ex::sendRedirect(SHOPPING_PAYMENT_URLPATH);
    					SC_Response_Ex::actionExit();
    				}
    				
    				break;
    				
    			case 'existing':
    				if(count($omiseCardPostObjAry) != 3) {
    					$postError = true;
    					break;
    				}
    				// CustomerとCardの検証
    				try {
    					$this->initOmiseKeys();
    					$omiseCustomerID = $omiseCardPostObjAry[1];
    					$omiseCardID = $omiseCardPostObjAry[2];
    					$omiseCustomer = OmiseCustomer::retrieve($omiseCustomerID);
    					
    					$omiseCardFind = false;
    					foreach ($omiseCustomer['cards']['data'] as $row) {
    						if($row['id'] === $omiseCardID) {
    							$omiseCardFind = true;
    							break;
    						}
    					}
    					if(!$omiseCardFind) {
    						throw new Exception('選択されたカードが見つかりませんでした。');
    					}
    					
    					// Cardに問題がないのでDBに決済情報の保存
    					$orderTempID = $this->getOrderTempID();
    					
    					$omiseObj = array('customer' => $omiseCustomerID, 'card' => $omiseCardID);
    					
    					$objQuery = &SC_Query_Ex::getSingletonInstance();
    					$objQuery->update('dtb_order_temp', array('plg_omise_payment_gateway' => serialize($omiseObj), 'update_date' => 'CURRENT_TIMESTAMP'), "order_temp_id = '$orderTempID'");
    				} catch(Exception $e) {
    					$_SESSION['plg_OmisePaymentGateway_error'] = 'エラーが発生しました。'.$e->getMessage();
    					SC_Response_Ex::sendRedirect(SHOPPING_PAYMENT_URLPATH);
    					SC_Response_Ex::actionExit();
    				}
    				
    				break;
    				
    			default:
    				$postError = true;
    				break;
    		}
    		if($postError) {
				$_SESSION['plg_OmisePaymentGateway_error'] = '不正な入力を検知しました。再度お試しください。2';
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
		$payment = $objQuery->getRow('payment_id, plg_omise_payment_gateway', 'dtb_order_temp', "order_temp_id = '$orderTempID'");
		
		if($payment['payment_id'] == $paymentInfo['credit_payment_id']) {
			$objPage->arrForm['plg_OmisePaymentGateway_enabled'] = true;
			$objOmise = unserialize($payment['plg_omise_payment_gateway']);
			try {
				$this->initOmiseKeys();
				$omiseCustomer = OmiseCustomer::retrieve($objOmise['customer']);
			
    			$omiseCard = null;
    			foreach ($omiseCustomer['cards']['data'] as $row) {
    				if($row['id'] === $objOmise['card']) {
    					$omiseCard = $row;
    					break;
    				}
    			}
    			if($omiseCard == null) {
    				throw new Exception('選択されたカードが見つかりませんでした。');
    			}
				
				$objPage->arrForm['plg_OmisePaymentGateway_name'] = $omiseCard['name'];
				$objPage->arrForm['plg_OmisePaymentGateway_number'] = '**** **** **** '.$omiseCard['last_digits'];
			} catch (Exception $e) {
    			$_SESSION['plg_OmisePaymentGateway_error'] = 'エラーが発生しました。'.$e->getMessage();
    			SC_Response_Ex::sendRedirect(SHOPPING_PAYMENT_URLPATH);
    			SC_Response_Ex::actionExit();
			}
		} else {
			$objPage->arrForm['plg_OmisePaymentGateway_enabled'] = false;
		}
	}

	/**
	 * @param LC_Page_Admin_Order_Edit $objPage
	 * 注文登録画面で表示する情報の登録
	 * @return void
	 */
	public function adminOrderEditActionAfter($objPage) {
		$orderID = $_POST['order_id'];
		$objQuery = &SC_Query_Ex::getSingletonInstance();
		
		$objOrder = $objQuery->getRow('payment_total, plg_omise_payment_gateway', 'dtb_order', 'order_id = ?', array($orderID));
		
		if($objOrder['plg_omise_payment_gateway'] != null) {
			$objPage->arrForm['plg_OmisePaymentGateway_enabled'] = true;
			$this->initOmiseKeys();
			$objOmise = unserialize($objOrder['plg_omise_payment_gateway']);
			$objCharge = null;
			foreach ($objOmise['charge'] as $row) {
				if($row['enable']) {
					$objCharge = $row;
					break;
				}
			}
			$logs = array();
			for ($i = (count($objOmise['charge']) - 1); $i >= 0; --$i) {
				$logs[] = array(
						'initial' => $objOmise['charge'][$i]['enable'] ? ' * ' : ' - ',
						'create_date' => date("Y/m/d H:i:s", strtotime($objOmise['charge'][$i]['create_date'])),
						'status' => $objOmise['charge'][$i]['status']
					);
			}
			
			$objPage->arrForm['plg_OmisePaymentGateway_logs'] = $logs;
			$objPage->arrForm['plg_OmisePaymentGateway_charge'] = $objCharge['id'];
			$objPage->arrForm['plg_OmisePaymentGateway_captured'] = $objCharge['captured'];
			$objPage->arrForm['plg_OmisePaymentGateway_status'] = $objCharge['status'];
			$objPage->arrForm['plg_OmisePaymentGateway_create_date'] = date("Y/m/d H:i:s", strtotime($objCharge['create_date']));
			if($objCharge['status'] == '返金済み') {
				$objPage->arrForm['plg_OmisePaymentGateway_no_charge'] = true;
				$objPage->arrForm['plg_OmisePaymentGateway_amount'] = '<strike>'.$objCharge['amount'].'円</strike>';
			} else {
				$objPage->arrForm['plg_OmisePaymentGateway_no_charge'] = false;
				$objPage->arrForm['plg_OmisePaymentGateway_amount'] = $objCharge['amount'].'円';
				if($objCharge['amount'] != $objOrder['payment_total']) {
					$objPage->arrForm['plg_OmisePaymentGateway_amount_warning'] = '※ お支払い合計が一致しません（'.$objOrder['payment_total'].'円）。変更する場合は ‘決済金額変更’ ボタンを押してください。';
				}
			}
		} else {
			$objPage->arrForm['plg_OmisePaymentGateway_enabled'] = false;
		}
	}

	/**
	 * @param LC_Page_Admin_Order_Edit $objPage
	 * 決済の確定周りの処理
	 * @return void
	 */
	public function adminOrderEditActionBefore($objPage) {
		switch ($objPage->getMode()) {
			case 'plg_OmisePaymentGateway_charge_capture':
				
				$orderID = $_POST['order_id'];
				$objQuery = &SC_Query_Ex::getSingletonInstance();
				$objOrder = $objQuery->getRow('payment_total, plg_omise_payment_gateway', 'dtb_order', 'order_id = ?', array($orderID));
				$objOmise = unserialize($objOrder['plg_omise_payment_gateway']);
				
				try {
					$this->initOmiseKeys();
					$i = 0;
					while($i < count($objOmise['charge'])) {
						if($objOmise['charge'][$i]['enable']) break;
						$i++;
					}
					$chargeID = $objOmise['charge'][$i]['id'];
					$objOmiseCharge = OmiseCharge::retrieve($chargeID);
					
					if($objOmiseCharge['amount'] != $objOrder['payment_total']) {
						throw new Exception('お支払合計が一致しないため決済できません。');
					}
					
					$objOmiseCharge->capture();
					$objOmise['charge'][$i]['captured'] = $objOmiseCharge['captured'];
					if($objOmiseCharge['captured']) {
						$objOmise['charge'][$i]['status'] = '売上確定済み';
						$objQuery->update('dtb_order', array('plg_omise_payment_gateway' => serialize($objOmise), 'update_date' => 'CURRENT_TIMESTAMP'), "order_id = '$orderID'");
						$objPage->tpl_onload = 'window.alert("売上を確定しました。");';
					}
				} catch(Exception $e) {
					$objPage->tpl_onload = 'window.alert("エラーが発生しました : '.$e->getMessage().'。");';
					break;
				}
				
				break;
				
			case 'plg_OmisePaymentGateway_amount_change':
			case 'plg_OmisePaymentGateway_re_charge':
				$orderID = $_POST['order_id'];
				$objQuery = &SC_Query_Ex::getSingletonInstance();
				$objOrder = $objQuery->getRow('payment_total, plg_omise_payment_gateway', 'dtb_order', 'order_id = ?', array($orderID));
				$objOmise = unserialize($objOrder['plg_omise_payment_gateway']);
				
				try {
					$this->initOmiseKeys();
					$i = 0;
					while($i < count($objOmise['charge'])) {
						if($objOmise['charge'][$i]['enable']) break;
						$i++;
					}
					if($objPage->getMode() === 'plg_OmisePaymentGateway_amount_change') {
						$objOmise['charge'][$i]['status'] = 'キャンセル（決済金額変更）';
						$message = '決済金額を変更しました。';
					} else {
						$message = '再オーソリを実行しました。';
					}
					$objOmiseCharge = OmiseCharge::create(array(
							'amount' => intval($objOrder['payment_total']),
							'currency' => $this->plg_OmisePaymentGateway_currency,
							'customer' => $objOmise['customer'],
							'card' => $objOmise['card'],
							'capture' => false
						));
					$objOmise['charge'][$i]['enable'] = false;
					$objOmise['charge'][] = array('enable' => true, 'id' => $objOmiseCharge['id'], 'status' => '仮売上済み', 'create_date' => $objOmiseCharge['created'], 'amount' => $objOmiseCharge['amount'], 'captured' => false, 'refunds' => array());
					$objQuery->update('dtb_order', array('plg_omise_payment_gateway' => serialize($objOmise), 'update_date' => 'CURRENT_TIMESTAMP'), "order_id = '$orderID'");
				} catch(Exception $e) {
					$objPage->tpl_onload = 'window.alert("エラーが発生しました : '.$e->getMessage().'。");';
					break;
				}
				
				$objPage->tpl_onload = 'window.alert("'.$message.'");';
				break;
				
			case 'plg_OmisePaymentGateway_refund':
				$orderID = $_POST['order_id'];
				$objQuery = &SC_Query_Ex::getSingletonInstance();
				$objOrder = $objQuery->getRow('payment_total, plg_omise_payment_gateway', 'dtb_order', 'order_id = ?', array($orderID));
				$objOmise = unserialize($objOrder['plg_omise_payment_gateway']);
				
				try {
					$this->initOmiseKeys();
					$i = 0;
					while($i < count($objOmise['charge'])) {
						if($objOmise['charge'][$i]['enable']) break;
						$i++;
					}
					$chargeID = $objOmise['charge'][$i]['id'];
					$objOmiseCharge = OmiseCharge::retrieve($chargeID);
					$refund = $objOmiseCharge->refunds()->create(array('amount' => intval($objOmiseCharge['amount'])));
					$objOmise['charge'][$i]['status'] = '返金済み';
					$objOmise['charge'][$i]['refunds'][] = $refund['amount'];
					
					$objQuery->update('dtb_order', array('plg_omise_payment_gateway' => serialize($objOmise), 'update_date' => 'CURRENT_TIMESTAMP'), "order_id = '$orderID'");
					$objPage->tpl_onload = 'window.alert("返金を実行しました。");';
				} catch(Exception $e) {
					$objPage->tpl_onload = 'window.alert("エラーが発生しました : '.$e->getMessage().'。");';
					break;
				}
				
				break;
				
			default:
				break;
		}
	}
	
	// loadClassFileChange
	public function loadClassFileChange(&$classname, &$classpath) {
		if($classname == 'SC_Helper_Purchase_Ex') {
			$classpath = PLUGIN_UPLOAD_REALDIR . 'OmisePaymentGateway/plg_OmisePaymentGateway_SC_Helper_Purchase.php';
			$classname = 'plg_OmisePaymentGateway_SC_Helper_Purchase';
		}
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
				if(strpos($filename, 'order/edit.tpl') !== false) {
					$objTransform->select('table.form', 1)->insertAfter(file_get_contents(PLUGIN_UPLOAD_REALDIR.'OmisePaymentGateway/templates/order/plg_OmisePaymentGateway_edit.tpl'));
				}
				break;
		}
	
		$source = $objTransform->getHTML();
	}
	
	private function getOrderTempID() {
		return $_SESSION['site']['uniqid'];
	}
	
	/**
	 * Omiseの顧客IDを返却する。
	 * $forcedNewがtrueなら問答無用で新規作成
	 * 
	 * @param boolean $forcedNew
	 * @return unknown|OmiseCustomer
	 */
	private function getOmiseCustomerID($forcedNew = false) {
		$this->initOmiseKeys();
		
		//$forcedNewがtrueなら強制的に顧客IDの新規払い出し
		if($forcedNew) {
			$omiseCustomer = OmiseCustomer::create(array());
			return $omiseCustomer['id'];
		}
		
		$objCustomer = new SC_Customer();
		if($objCustomer->isLoginSuccess()) {
			$customerID = $_SESSION['customer']['customer_id'];
			
			$objQuery = &SC_Query_Ex::getSingletonInstance();
			$customer = $objQuery->getRow('plg_omise_payment_gateway_id', 'dtb_customer', 'customer_id = ?', array($customerID));
			if(strlen($customer['plg_omise_payment_gateway_id']) > 0) {
				return $customer['plg_omise_payment_gateway_id'];
			} else {
				$omiseCustomer = OmiseCustomer::create(array());
				$objQuery->update('dtb_customer', array('plg_omise_payment_gateway_id' => $omiseCustomer['id'], 'update_date' => 'CURRENT_TIMESTAMP'), "customer_id = '$customerID'");
				return $omiseCustomer['id'];
			}
		} else {
			$objQuery = &SC_Query_Ex::getSingletonInstance();
			$orderTempID = $this->getOrderTempID();
			$orderTemps = $objQuery->select('plg_omise_payment_gateway', 'dtb_order_temp', 'order_temp_id = ?', array($orderTempID));
			
			if(count($orderTemps) === 1) {
				$omiseInfo = unserialize($orderTemps[0]['plg_omise_payment_gateway']);
				if(isset($omiseInfo['customer'])) {
					return $omiseInfo['customer'];
				}
			}
			
			$omiseCustomer = OmiseCustomer::create(array());
			return $omiseCustomer['id'];
		}
	}
	
	private function initOmiseKeys() {
		$configInfo = self::selectConfig(self::CONFIG_OIMISE);
		
		if(!defined('OMISE_PUBLIC_KEY')) define('OMISE_PUBLIC_KEY', $configInfo['pkey']);
		if(!defined('OMISE_SECRET_KEY')) define('OMISE_SECRET_KEY', $configInfo['skey']);
	}
}
