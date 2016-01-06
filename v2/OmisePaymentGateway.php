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
		$objQuery->begin();

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
		
		$objQuery->commit();
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
	 * 支払い方法選択画面
	 * @return void
	 */
	public function shoppingPaymentActionAfter($objPage) {
    	$info = self::selectConfig(self::CONFIG_PAYMENT);
		$objPage->arrForm['plg_OmisePaymentGateway_payment_id'] = $info['credit_payment_id'];
	}

	/**
	 * @param LC_Page_Shopping_Payment $objPage
	 * 支払い方法確認画面
	 * @return void
	 */
	public function shoppingPaymentActionConfirm($objPage) {
    	$paymentInfo = self::selectConfig(self::CONFIG_PAYMENT);
    	if($_POST['payment_id'] == $paymentInfo['credit_payment_id']) {
    		$number = $_POST['omise_credit_number'];
    		$name = $_POST['omise_name'];
    		$expirationYear = $_POST['omise_expiration_year'];
    		$expirationMonth = $_POST['omise_expiration_month'];
    		$securityCode = $_POST['omise_security_code'];
    		
    		$configInfo = self::selectConfig(self::CONFIG_OIMISE);
    		$configInfo['pkey'];
    		$configInfo['skey'];
    		
    		// これまでのオーダ情報はセッションではなくtemp_orderテーブルに格納されているので要注意。
    		// TODO 年明けここから。Omise-API叩く。
//     		$objFormParam = new SC_FormParam_Ex();
//     		$objFormParam->setParam($_POST);
//     		$objFormParam->addParam('カード番号', 'omise_credit_number', '', '', array('EXIST_CHECK','NO_SPTAB'));
//     		$objFormParam->addParam('氏名', 'omise_name', '', '', array('EXIST_CHECK','NO_SPTAB'));
    		
//     		$objPage->arrError = $objFormParam->checkError();

//     		SC_Response_Ex::sendRedirect(SHOPPING_PAYMENT_URLPATH);
//     		//SC_Response_Ex::actionExit();
    	}
	}
	
	/**
	 * @param LC_Page_Shopping_Confirm $objPage
	 * 支払い方法確認画面
	 * @return void
	 */
	public function shoppingConfirmActionAfter($objPage) {
		
	}
	
	//SC_FormParam
	public function addParam($class_name, $param) {
		if(strpos($class_name, 'LC_Page_Shopping') !== false) {
			if(array_key_exists('payment_id', $_POST)) {
	    		$paymentInfo = self::selectConfig(self::CONFIG_PAYMENT);
				if($_POST['payment_id'] == $paymentInfo['credit_payment_id']) {
					$param->addParam('カード番号1', 'omise_credit_number1', CREDIT_NO_LEN, 'n', array('EXIST_CHECK','NUM_CHECK', 'NUM_COUNT_CHECK'));
					$param->addParam('カード番号2', 'omise_credit_number2', CREDIT_NO_LEN, 'n', array('EXIST_CHECK','NUM_CHECK', 'NUM_COUNT_CHECK'));
					$param->addParam('カード番号3', 'omise_credit_number3', CREDIT_NO_LEN, 'n', array('EXIST_CHECK','NUM_CHECK', 'NUM_COUNT_CHECK'));
					$param->addParam('カード番号4', 'omise_credit_number4', CREDIT_NO_LEN, 'n', array('EXIST_CHECK','NUM_CHECK', 'NUM_COUNT_CHECK'));
					$param->addParam('カード名義人', 'omise_name', STEXT_LEN, '', array('EXIST_CHECK','ALPHA_CHECK', 'MAX_LENGTH_CHECK'));
					$param->addParam('有効期限（年）', 'omise_expiration_year', 4, 'n', array('EXIST_CHECK','NUM_CHECK', 'NUM_COUNT_CHECK'));
					$param->addParam('有効期限（月）', 'omise_expiration_month', 2, 'n', array('EXIST_CHECK','NUM_CHECK', 'NUM_COUNT_CHECK'));
					$param->addParam('セキュリティコード', 'omise_security_code', 4, 'n', array('EXIST_CHECK','NUM_CHECK', 'MAX_LENGTH_CHECK'));
				}
			}
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
					$objTransform->select('#payment')->insertAfter($this->includeTpl(PLUGIN_UPLOAD_REALDIR.'OmisePaymentGateway/templates/shopping/payment_credit.tpl'));
				}
				break;
	
			case DEVICE_TYPE_ADMIN:
			default:
				break;
		}
	
		$source = $objTransform->getHTML();
	}
	
	private function includeTpl($fileName) {
		$str = '';
		
		ob_start();
		include $fileName;
		$str = ob_get_contents();
		ob_end_clean();
		
		return $str;
	}
}
