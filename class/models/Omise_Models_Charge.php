<?php
/**
 * This file is part of EC-CUBE Plugin OmiseExt
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */

/**
 * Omise charge handler
 *
 * @author Akira / Omise
 */
class Omise_Models_Charge
{
    /**
     * @var $arrOrder テーブルからロードした order のデータ
     */
    public $arrOrder;

    public function __construct($order_id)
    {
        $objPurchase    = new SC_Helper_Purchase_Ex();
        $this->arrOrder = $objPurchase->getOrder($order_id);
    }

    /**
     * Sync Omise Charge data and local data on ECCUBE db
     */
    public function syncOmise()
    {
        $objOmiseWrapper = new OmiseWrapper();
        $objCharge   = $objOmiseWrapper->chargeRetrieve(self::getChargeId());
        $objPurchase = new SC_Helper_Purchase_Ex();
        $updateData  = array(OMISE_MDL_CHARGE_DATA_COL => serialize($this->lfConvertToDbChargeData($objCharge)));
        $objQuery    = SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();
        $objPurchase->sfUpdateOrderStatus(
            $this->arrOrder['order_id'],
            null, // 対応状況 ＊nullは変更なし
            null, // 加算ポイント ＊nullは変更なし
            null, // 使用ポイント ＊nullは変更なし
            $updateData
        );
        $objQuery->commit();
        $order_id = $this->arrOrder['order_id'];
        $this->arrOrder = $objPurchase->getOrder($order_id);
    }

    /**
     * Get Omise Charge id
     * @return string Omise Charge id string
     */
    public function getChargeId()
    {
        $arrData = $this->lfGetChargeData();
        return $arrData['id'];
    }

    /**
     * Return true if refunded
     * @return boolean
     */
    public function isRefunded()
    {
        $arrData = $this->lfGetChargeData();
        return ($arrData['real_amount'] == 0) ? true : false;
    }

    /**
     * Return true if livemode
     * @return boolean
     */
    public function isLiveCharge()
    {
        $arrData = $this->lfGetChargeData();
        return $arrData['livemode'];
    }

    /**
     * Get the Omise Charge detail page url string
     * @return string The Omise Charge detail page
     */
    public function getOmisePage()
    {
        return sprintf('https://dashboard.omise.co/%s/charges/%s',
            $this->isLiveCharge() ? 'live' : 'test',
            $this->getChargeId());
    }

    /**
     * Return true if the Omise Charge was captured
     * @return boolean
     */
    public function isCaptured()
    {
        $arrData = $this->lfGetChargeData();
        return ($arrData['captured'] == 1) ? true : false;
    }

    /**
     * Get amount from the Omise Charge
     * @return string amount of the Omise Charge
     */
    public function getAmount()
    {
        $arrData = $this->lfGetChargeData();
        return $arrData['real_amount'];
    }

    /**
     * Get refund from the Omise Charge
     * @return string amount of the Omise Charge
     */
    public function getRefunded()
    {
        $arrData = $this->lfGetChargeData();
        return $arrData['refunded'];
    }

    /**
     * Return the Omise Charge mapping array
     * @return array mapping array
     * e.g.
     *  array(
     *    'id' => 'chrg_xxxx_xxxxxxxx',
     *    'livemode' => '0',
     *    'captured' => '0',
     *    'real_amount' => '2100',
     *    'refunded' => '0'
     *  )
     */
    private function lfGetChargeData()
    {
        return unserialize($this->arrOrder[OMISE_MDL_CHARGE_DATA_COL]);
    }

    /**
     * Create Charge on Omise and finish shopping order
     *
     * @param  array        $arrPayer  peyment method ('customer' => customer_id or 'card' => token_id)
     * @return string|null  The error message if occured
     */
    public function createCharge($arrPayer)
    {
        $arrChargeParams = $this->lfComposeChargeParam($arrPayer);
        try {
            $objOmiseWrapper = new OmiseWrapper();

            $charge = $objOmiseWrapper->chargeCreate($arrChargeParams);

            if ($charge['capture']) {
                $result = $this->validateChargeCaptured($charge);
            } else {
                $result = $this->validateChargeAuthorized($charge);
            }

            if ($result !== true) {
                throw new Exception($result);
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $objPurchase = new SC_Helper_Purchase_Ex();
        $updateData  = array(OMISE_MDL_CHARGE_DATA_COL => serialize($this->lfConvertToDbChargeData($charge)));
        $objQuery    = SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();
        $objPurchase->sfUpdateOrderStatus(
            $this->arrOrder['order_id'],
            (OmiseConfig::getInstance()->autocapture == 1) ? ORDER_PRE_END : ORDER_PAY_WAIT,
            null, // 加算ポイント
            null, // 使用ポイント
            $updateData
        );
        $objQuery->commit();
        $objPurchase->sendOrderMail($this->arrOrder['order_id']);

        return null;
    }

    /**
     * Compose Omise Charge param
     * @param  array  $arrPayer  peyment method ('customer' => customer_id or 'card' => token_id)
     * @return array             mapping array of Omise Charge param
     */
    private function lfComposeChargeParam($arrPayer)
    {
        assert(count($arrPayer) === 1, '$arrPayer must have 1 payer specification');
        $arrChargeParams = array(
            'amount'      => intval($this->arrOrder['payment_total'], 10),
            'currency'    => OMISE_CURRENCY,
            'description' => OMISE_API_DESC_CHARGE . $this->arrOrder['order_id'],
            'capture'     => OmiseConfig::getInstance()->autocapture
        );

        foreach ($arrPayer as $k => $v) {
            $arrChargeParams[$k] = $v;
        }

        return $arrChargeParams;
    }

    /**
     * Convert keys
     * @param  OmiseCharge $objCharge The OmiseCharge instance
     * @return array  map array
     */
    private function lfConvertToDbChargeData($objCharge) {
        return array(
            'id'          => $objCharge['id'],
            'livemode'    => $objCharge['livemode'],
            'captured'    => $objCharge['paid'],
            'real_amount' => $objCharge['amount'] - $objCharge['refunded'],
            'refunded'    => $objCharge['refunded']
        );
    }

    /**
     * Capture the Omise Charge
     * @return string|null  The error message if occured
     */
    public function capture()
    {
        $current_total = intval($this->arrOrder['payment_total'], 10);
        if ($this->getAmount() < $current_total) {
            return sprintf('仮売上金額(%s円)以上で実売上化することはできません。合計金額を仮売上金額以下にするか、金額を増額する場合は購入者に連絡し、再度購入処理を行ってください。', number_format($this->getAmount()));
        }

        try {
            $objOmiseWrapper = new OmiseWrapper();
            $charge = $objOmiseWrapper->chargeCapture($this->getChargeId());

            $result = $this->validateChargeCaptured($charge);
            if ($result !== true) {
                throw new Exception($result);
            }
        } catch (OmiseException $e) {
            return $e->getMessage();
        }

        $objPurchase = new SC_Helper_Purchase_Ex();
        $updateData  = array(OMISE_MDL_CHARGE_DATA_COL => serialize($this->lfConvertToDbChargeData($charge)));
        $objQuery    = SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();
        $objPurchase->sfUpdateOrderStatus(
            $this->arrOrder['order_id'],
            ORDER_PRE_END,
            null, // 加算ポイント
            null, // 使用ポイント
            $updateData
        );

        $objQuery->commit();
        $objPurchase->sendOrderMail($this->arrOrder['order_id']);

        return null;
    }

    /**
     * Refund the Omise Charge
     * @return string|null  The error message if occured
     */
    public function refund()
    {
        try {
            $objOmiseWrapper = new OmiseWrapper();
            $objCharge = $objOmiseWrapper->chargeRefund($this->getChargeId());
        } catch (OmiseException $e) {
            return $e->getMessage();
        }

        $objPurchase = new SC_Helper_Purchase_Ex();
        $updateData  = array(OMISE_MDL_CHARGE_DATA_COL => serialize($this->lfConvertToDbChargeData($objCharge)));
        $objQuery    = SC_Query_Ex::getSingletonInstance();
        $objQuery->begin();
        $objPurchase->sfUpdateOrderStatus(
            $this->arrOrder['order_id'],
            ORDER_CANCEL,
            null, // 加算ポイント
            null, // 使用ポイント
            $updateData
        );

        $objQuery->commit();
        $objPurchase->sendOrderMail($this->arrOrder['order_id']);

        return null;
    }

    /**
     * Validate if charge is authorized.
     *
     * @param  OmiseCharge  $charge
     * @return string|bool  The error message if occured
     */
    protected function validateChargeAuthorized($charge)
    {
        if (! isset($charge['object']) || $charge['object'] !== 'charge') {
            return '注文情報の状態が不正です。<br>この手続きは無効となりました。';
        }

        if ($charge['status'] === 'pending' && $charge['authorized'] === true) {
            return true;
        }

        return "支払いに失敗しました、" . $charge['failure_message'] . ' (' . $charge['failure_code'] . ')';
    }

    /**
     * Validate if charge is captured.
     *
     * @param  OmiseCharge  $charge
     * @return string|bool  The error message if occured
     */
    protected function validateChargeCaptured($charge)
    {
        if (! isset($charge['object']) || $charge['object'] !== 'charge') {
            return '注文情報の状態が不正です。<br>この手続きは無効となりました。';
        }

        if ($charge['status'] === 'successful' && $charge['paid'] === true) {
            return true;
        }

        return "支払いに失敗しました、" . $charge['failure_message'] . ' (' . $charge['failure_code'] . ')';
    }
}
