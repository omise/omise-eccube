<?php
// {{{ requires
require_once CLASS_EX_REALDIR . 'page_extends/admin/LC_Page_Admin_Ex.php';

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
    function init() {
        parent::init();
        $this->tpl_mainpage = PLUGIN_UPLOAD_REALDIR.'OmisePaymentGateway/templates/config.tpl';
        $this->tpl_subtitle = 'Omise Payment Gateway 設定画面';
    }

    /**
     * プロセス.
     *
     * @return void
     */
    function process() {
    	$this->action();
    	$this->sendResponse();
    }
    
    /**
     * Page のアクション.
     *
     * @return void
     */
    function action() {
    	$objFormParam = new SC_FormParam_Ex();
    	$this->initParam($objFormParam);
    	$objFormParam->setParam($_POST);
    	$objFormParam->convParam();
    
    	$css_file_path = PLUGIN_HTML_REALDIR . "TopicPath/media/topicPath.css";
    	$arrForm = array();
    	$arrForm['pkey'] = '';
    	$arrForm['skey'] = '';
    
    	switch ($this->getMode()) {
    		case 'edit':
    			$arrForm = $objFormParam->getHashArray();
    			$this->arrErr = $objFormParam->checkError();
    			// エラーなしの場合にはデータを更新
    			if (count($this->arrErr) == 0) {
    				// データ更新
    			}
    			break;
    		default:
    			// プラグイン情報を取得.
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
    function destroy() {
    	parent::destroy();
    }
    
    /**
     * パラメーター情報の初期化
     *
     * @param object $objFormParam SC_FormParamインスタンス
     * @return void
     */
    function initParam(&$objFormParam) {
    	$objFormParam->addParam('Public Key', 'pkey', 29, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
    	$objFormParam->addParam('Secret Key', 'skey', 29, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK', 'GRAPH_CHECK'));
    }
}
