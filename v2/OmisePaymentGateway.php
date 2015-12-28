<?php
class OmisePaymentGateway extends SC_Plugin_Base {
	const TBL_NAME_OMISE_CONFIG = 'plg_OmisePaymentGateway_config';
	/**
	 * @param array $arrPlugin
	 */
	public function install($arrPlugin) {
		self::createOmiseConfigTable();
		self::insertOmiseConfig();
	}

	/**
	 * @param array $arrPlugin
	 */
	public function uninstall($arrPlugin) {
		self::drop(self::TBL_NAME_OMISE_CONFIG);
	}
	
	/**
	 * @param array $arrPlugin
	 */
	public function enable($arrPlugin) {
		self::updateCreditPayment(0);
	}

	/**
	 * @param array $arrPlugin
	 */
	public function disable($arrPlugin) {
		self::updateCreditPayment(1);
	}
	
	private static function createOmiseConfigTable() {
		self::create(self::TBL_NAME_OMISE_CONFIG, [
				'id INT NOT NULL PRIMARY KEY',
				'name TEXT NOT NULL',
				'info TEXT NOT NULL',
				'delete_flg SMALLINT NOT NULL',
				"create_date TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'",
				"update_date TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'"
			]);
	}
	
	private static function insertOmiseConfig() {
		$paymentId = self::insertCreditPayment();
		
		self::insert(self::TBL_NAME_OMISE_CONFIG, [
				'id' => 1,
				'name' => 'omise_config',
				'info' => serialize(array('pkey' => '', 'skey' => '')),
				'create_date' => 'CURRENT_TIMESTAMP',
				'update_date' => 'CURRENT_TIMESTAMP'
			]);
		self::insert(self::TBL_NAME_OMISE_CONFIG, [
				'id' => 2,
				'name' => 'payment_config',
				'info' => serialize(array('credit_payment_id' => $paymentId)),
				'create_date' => 'CURRENT_TIMESTAMP',
				'update_date' => 'CURRENT_TIMESTAMP'
			]);
	}
	
	private static function insertCreditPayment() {
		$objQuery = &SC_Query_Ex::getSingletonInstance();
		$objQuery->begin();
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
		
		$objQuery->commit();
		
		return $params['payment_id'];
	}
	
	private static function updateCreditPayment($deleteFlg) {
		$objQuery = &SC_Query_Ex::getSingletonInstance();
    	$info = $objQuery->select('info', 'plg_OmisePaymentGateway_config', "name = 'payment_config'");
    	$info = unserialize($info[0]['info']);
    	
    	return $objQuery->update('dtb_payment', array('del_flg' => $deleteFlg, 'update_date' => 'CURRENT_TIMESTAMP'), 'payment_id = '.$info['credit_payment_id']);
	}
	
	public static function insert($tableName, $params) {
		$objQuery = &SC_Query_Ex::getSingletonInstance();
		$objQuery->insert($tableName, $params);
	}
	
	public static function create($tableName, $fields) {
		$objQuery = &SC_Query_Ex::getSingletonInstance();
		$sql = sprintf('CREATE TABLE %s (%s)', $tableName, implode(',', $fields));
		$objQuery->query($sql);
	}
	
	public static function drop($tableName) {
		$objQuery = &SC_Query_Ex::getSingletonInstance();
		$sql = sprintf('DROP TABLE %s', $tableName);
		$objQuery->query($sql);
	}
	
	public static function selectPaymentConfig() {
		$objQuery = &SC_Query_Ex::getSingletonInstance();
		$info = $objQuery->select('info', 'plg_OmisePaymentGateway_config', "name = 'payment_config'");
		return unserialize($info[0]['info']);
	}
	
	
	
	/* -------------------- Hook Points -------------------- */
	/**
	 * @param LC_Page_Shopping_Payment $objPage 
	 * 支払い方法選択画面
	 * @return void
	 */
	public function shoppingPaymentActionAfter($objPage) {
    	$info = self::selectPaymentConfig();
		$objPage->arrForm['plg_OmisePaymentGateway_payment_id'] = $info['credit_payment_id'];
	}

	/**
	 * @param LC_Page_Shopping_Payment $objPage
	 * 支払い方法確認画面
	 * @return void
	 */
	public function shoppingPaymentActionConfirm($objPage) {
    	$info = self::selectPaymentConfig();
    	if($_POST['payment_id'] == $info['credit_payment_id']) {
    		$number = $_POST['omise_credit_number'];
    		$name = $_POST['omise_name'];
    		$expirationYear = $_POST['omise_expiration_year'];
    		$expirationMonth = $_POST['omise_expiration_month'];
    		$securityCode = $_POST['omise_security_code'];
    		
    		// これまでのオーダ情報はセッションではなくtemp_orderテーブルに格納されているので要注意。
    		// TODO 年明けここから。Omise-API叩く。
    	}
	}
	
	/**
	 * @param LC_Page_Shopping_Confirm $objPage
	 * 支払い方法確認画面
	 * @return void
	 */
	public function shoppingConfirmActionAfter($objPage) {
		
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
