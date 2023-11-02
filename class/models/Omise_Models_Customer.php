<?php
/**
 * This file is part of EC-CUBE Plugin OmiseExt
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */

/**
 * Omise customer handler
 *
 * @author Akira / Omise
 */
class Omise_Models_Customer
{

  /**
   * @var $customer_id 0 or NULL = user not logged in
   */
  private $customer_id;
  private $objOmiseCustomer;

  public function __construct($customer_id)
  {
      $this->customer_id = intval($customer_id);
  }

  /**
   * Return the customer_id as int type
   * @return [type] [description]
   */
  public function getCustomerId()
  {
      return $this->customer_id;
  }

  /**
   * Fetch the card from Omise Customer
   *
   * @return OmiseCard|null the OmiseCard instance or null
   */
  public function fetchSavedCardForCustomer()
  {
      if ($this->lfIsNoCustomer()) {
          return null;
      }
      $objCustomer = $this->lfFetchOmiseCustomer();
      $objOmiseWrapper = new OmiseWrapper();
      if ($objCustomer !== null) {
          $card = $objOmiseWrapper->customerDefaultCard($this->loadOmiseCustomerId());
          if($card !== null) {
              return $card['object'] === 'card' ? $card : null;
          } else {
              return null;
          }
      } else {
          return null;
      }
  }

  /**
   * Save card onto Omise Customer
   *
   * @param  string $token Omise token id
   * @return string Omise customer id
   */
  public function saveCardForCustomer($token)
  {
      if ($this->lfIsNoCustomer()) {
          return null;
      }
      // load omise customer id
      $omise_id = $this->loadOmiseCustomerId();

      // set attr for updation
      $arrAttributes = array('card' => $token);
      if (isset($_SESSION['customer']['email'])) {
          $arrAttributes['email'] = $_SESSION['customer']['email'];
      }

      // Delete old card, because we just allowed to store 1 card
      $objOmiseWrapper = new OmiseWrapper();
      $objOmiseWrapper->destoryAllCustomerCard($omise_id);

      $omise_id = $this->lfUpdateOmiseCustomer($omise_id, $arrAttributes);
      $this->lfInsertOmiseCustomerId($omise_id);
      return $omise_id;
  }

  /**
   * Delete the default card on the Omise Customer
   * @return void
   */
  public function deleteDefaultCard()
  {
      if ($this->lfIsNoCustomer()) {
          return;
      }
      $objOmiseWrapper = new OmiseWrapper();
      $objOmiseWrapper->customerDeleteDefaultCard($this->loadOmiseCustomerId());
  }

  /**
   * Return true if the user was logged in
   * And return false if the user have no account or not logged in
   * @return boolean
   */

  private function lfIsNoCustomer()
  {
      return $this->customer_id === 0;
  }

  /**
   * Fetch Omise Customer
   * @return OmiseCustomer  the OmiseCustomer instance
   */
  private function lfFetchOmiseCustomer()
  {
      if ($this->objOmiseCustomer !== null) {
          return $this->objOmiseCustomer;
      }
      $omise_customer_id = $this->loadOmiseCustomerId();
      if ($omise_customer_id === null)
          return null;
      try {
          $objOmiseWrapper = new OmiseWrapper();
          $this->objOmiseCustomer = $objOmiseWrapper->customerRetrieve($omise_customer_id);
      } catch (OmiseException $e) {
          $this->lfRemoveOmiseCustomerId();
      }

      return $this->objOmiseCustomer;
  }

  /**
   * Update or Create Omise Customer, and return its customer id
   * @param  string  $omise_id      Omise customer_id
   * @param  array   $arrAttributes card, email, description
   * @return string  The Omise Customer id
   */

  private function lfUpdateOmiseCustomer($omise_id, $arrAttributes)
  {
      $updated = false;
      // Update Omise Customer by customer_id
      $objOmiseWrapper = new OmiseWrapper();
      if ($omise_id !== null) {
          try {
              $this->objOmiseCustomer = $objOmiseWrapper->customerUpdate($omise_id, $arrAttributes);
              $updated = true;
          } catch (OmiseException $e) {
              throw $e;
          }
      }
      // Create new Omise Customer
      if (!$updated) {
          $this->objOmiseCustomer = $objOmiseWrapper->customerCreate($arrAttributes);
      }

      return $this->objOmiseCustomer['id'];
  }

  /**
   * Insert Omise Customer id to dtb_payment.memo04
   * @param  string  $omise_id  The Omise Customer id
   * @return void
   */
  private function lfInsertOmiseCustomerId($omise_id)
  {
      $objQuery = SC_Query_Ex::getSingletonInstance();
      $objQuery->begin();
      $arrMapping = self::lfLoadMapping($objQuery);
      $arrMapping[$this->customer_id] = $omise_id;
      self::lfSaveMapping($objQuery, $arrMapping);
      $objQuery->commit();
  }

  /**
   * Load Omise Customer id from dtb_payment.memo04
   * @return string|null return id string or null (if there was no record for the eccube customer)
   */
  public function loadOmiseCustomerId()
  {
      if ($this->lfIsNoCustomer()) {
          return null;
      }
      $objQuery = SC_Query_Ex::getSingletonInstance();
      $arrMapping = self::lfLoadMapping($objQuery);
      if (!array_key_exists($this->customer_id, $arrMapping)) {
          return null;
      }

      return $arrMapping[$this->customer_id];
  }

  /**
   * Remove Omise Customer from dtb_payment.memo04
   * @return void
   */
  private function lfRemoveOmiseCustomerId()
  {
      $objQuery = SC_Query_Ex::getSingletonInstance();
      $objQuery->begin();
      $arrMapping = self::lfLoadMapping($objQuery);
      if (array_key_exists($this->customer_id, $arrMapping)) {
          unset($arrMapping[$this->customer_id]);
          self::lfSaveMapping($objQuery, $arrMapping);
      }
      $objQuery->commit();
  }

  /**
   * Load arrMapping from dtb_payment.memo04 where module_id = OMISE_MDL_ID
   * This is not ideal way to keep but could stand with upto about 10,000 entries
   * @param  SC_Query_Ex  $objQuery  The SC_Query_Ex singleton instance
   * @return array  Omise Customer mapping array
   *         keys are defined by eccube customer id
   *         values are Omise Customer Id (e.g. cust_xxxx_xxxxxxx)
   *         array(
   *           1: "Omise Customer Id"
   *         )
   */
  private static function lfLoadMapping($objQuery)
  {
      $s_data = $objQuery->getCol(OMISE_MDL_CUSTOMER_DATA_COL, 'dtb_payment', 'module_id = ?',  array(OMISE_MDL_ID));
      $arrMapping = unserialize($s_data[0]);
      return is_array($arrMapping) ? $arrMapping : array();
  }

  /**
   * Save arrMapping to dtb_payment.memo04 where module_id = OMISE_MDL_ID
   * @param  SC_Query_Ex  $objQuery   The SC_Query_Ex singleton instance
   * @param  array        $arrMapping the Omise Customer mapping array
   * @return void
   */
  private static function lfSaveMapping($objQuery, $arrMapping)
  {
      unset($arrMapping[0]); // id > 0 なので 0 は必ず排除
      $arrNewVal = array(OMISE_MDL_CUSTOMER_DATA_COL => serialize($arrMapping));
      $objQuery->update('dtb_payment', $arrNewVal, 'module_id = ?',  array(OMISE_MDL_ID));
  }
}
