<?php
require_once CLASS_EX_REALDIR.'page_extends/admin/LC_Page_Admin_Ex.php';
require_once PLUGIN_UPLOAD_REALDIR.'OmisePaymentGateway/omise-php/lib/Omise.php';

/**
 * 管理画面表示制御の設定クラス
 */
class LC_Page_Plugin_OmisePaymentGateway_Config extends LC_Page_Admin_Ex {
    var $arrForm = array();

    /**
     * 初期化する.
     *
     * @return void
     */
    public function init() {
        parent::init();
        $this->tpl_mainpage = PLUGIN_UPLOAD_REALDIR.'OmisePaymentGateway/templates/admin/config.tpl';
        $this->tpl_subtitle = 'Omise Payment Gateway 設定画面';
    }

    /**
     * プロセス.
     *
     * @return void
     */
    public function process() {
    	$this->action();
    	$this->sendResponse();
    }
    
    /**
     * Page のアクション.
     *
     * @return void
     */
    public function action() {
    	$objFormParam = new SC_FormParam_Ex();
    	$this->initParam($objFormParam);
    	$objFormParam->setParam($_POST);
    	$objFormParam->convParam();
    	$arrForm = array();
    
    	switch ($this->getMode()) {
    		case 'edit':
    			$arrForm = $objFormParam->getHashArray();
    			$this->arrErr = $objFormParam->checkError();
    			// エラーなしの場合にはAPIから確認
    			if (count($this->arrErr) == 0) {
    				try {
	    				// 正しいキーか確認
	    				define('OMISE_PUBLIC_KEY', $arrForm['pkey']);
	    				define('OMISE_SECRET_KEY', $arrForm['skey']);
	    				$omise = OmiseAccount::retrieve();
	    				$omise = OmiseToken::create(array(
	    						'card' => array(
	    								'name' => 'Somchai Prasert',
	    								'number' => '4242424242424242',
	    								'expiration_month' => 10,
	    								'expiration_year' => 2018,
	    								'city' => 'Bangkok',
	    								'postal_code' => '10320',
	    								'security_code' => 123
	    						)
	    				));
	    				
	    				if(self::updateOmiseConfigInfo($arrForm) === 1) {
							$this->tpl_onload = "alert('登録しました。');";
	    				}
    				} catch (OmiseException $e) {
    					$this->tpl_onload = "alert('キーが間違っているため登録できません。');";
    				}
	    		}
    			
    			break;
    		default:
    			$omiseConfig = self::selectOmiseConfigInfo();
    			// $arrForm = $omiseConfig
		    	$arrForm['pkey'] = $omiseConfig['pkey'];
		    	$arrForm['skey'] = $omiseConfig['skey'];
    			break;
    	}
    	$this->arrForm = $arrForm;
    	$this->setTemplate($this->tpl_mainpage);
    }
    
    /**
     * デストラクタ.
     *
     * @return void
     */
    public function destroy() {
    	parent::destroy();
    }
    
    /**
     * パラメーター情報の初期化
     *
     * @param object $objFormParam SC_FormParamインスタンス
     * @return void
     */
    public function initParam(&$objFormParam) {
    	$objFormParam->addParam('Public Key', 'pkey', 29, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
    	$objFormParam->addParam('Secret Key', 'skey', 29, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
    }
    
    /**
     * plg_OmisePaymentGateway_configテーブルからomise_configのレコードを取り出す
     * @return array
     */
    public static function selectOmiseConfigInfo() {
		$objQuery = &SC_Query_Ex::getSingletonInstance();
    	$result = $objQuery->select('info', 'plg_OmisePaymentGateway_config', "name = 'omise_config'");
    	
    	return unserialize($result[0]['info']);
    }

    /**
     * plg_OmisePaymentGateway_configテーブルのomise_configのレコードを更新する
     * @return void
     */
    public static function updateOmiseConfigInfo($info) {
    	$objQuery = &SC_Query_Ex::getSingletonInstance();
    	return $objQuery->update('plg_OmisePaymentGateway_config', array('info' => serialize($info), 'update_date' => 'CURRENT_TIMESTAMP'), "name = 'omise_config'");
    }
}
