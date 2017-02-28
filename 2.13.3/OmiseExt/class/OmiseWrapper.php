<?php
/**
 * This file is part of EC-CUBE Plugin OmiseExt
 *
 * @copyright 2016 Omise All Rights Reserved.
 * @author Akira Narita
 *
 */

/**
 * Omise API Handler
 *
 * @author Akira / Omise
 */
class OmiseWrapper {

  public function __construct() {
  }

  /**
   * Create new charge
   * @param  array  $arrParams  array contains params
   * @return OmiseCharge        the created OmiseCharge instance
   */
  public function chargeCreate($arrParams) {
    return OmiseCharge::create($arrParams);
  }

  /**
   * Retrieve the charge
   * @param  string  $charge_id  the charge id
   * @param  string  $publickey  the public key
   * @param  string  $secretkey  the secret key
   * @return OmiseCharge         the OmiseCharge instance
   */
  public function chargeRetrieve($charge_id, $publickey = null, $secretkey = null) {
    return OmiseCharge::retrieve($charge_id, $publickey, $secretkey);
  }

  /**
   * Retrieve the charge
   * @param  string  $charge_id  the charge id
   * @param  string  $publickey  the public key
   * @param  string  $secretkey  the secret key
   * @return OmiseCharge         the OmiseCharge instance
   */
  public function chargeRefund($charge_id, $publickey = null, $secretkey = null) {
    $charge = OmiseCharge::retrieve($charge_id, $publickey, $secretkey);
    $refund = $charge->refunds()->create(array('amount' => $charge['amount']));
    $charge->reload();
    return $charge;
  }

  /**
   * Capture the charge
   * @param  string  $charge_id  the charge id
   * @param  string  $publickey  the public key
   * @param  string  $secretkey  the secret key
   * @return OmiseCharge         the OmiseCharge instance
   */
  public function chargeCapture($charge_id, $publickey = null, $secretkey = null) {
    $charge = OmiseCharge::retrieve($charge_id, $publickey, $secretkey);
    return $charge->capture();
  }

  /**
   * Create new customer
   * @param  array  $arrParams  array contains params
   *   email        string     (optional) Customer's email
   *   description  string     (optional) A custom description for the customer
   *   card         object_id  (optional) A card token in case you want to add a card to the customer
   * @return OmiseCustomer      the crearted OmiseCustomer instance
   */
  public function customerCreate($arrParams) {
    return OmiseCustomer::create($arrParams);
  }

  /**
   * Update the customer
   * @param  string  $customer_id  the customer id
   * @param  array   $arrParams    array contains params
   *   email         string        (optional) Customer's email
   *   description   string        (optional) A custom description for the customer
   *   card          object_id     (optional) A card token in case you want to add a card to the customer
   * @param  string  $publickey    the public key
   * @param  string  $secretkey    the secret key
   * @return OmiseCustomer         the OmiseCustomer instance
   */
  public function customerUpdate($customer_id, $arrParams, $publickey = null, $secretkey = null) {
    $customer = OmiseCustomer::retrieve($customer_id, $publickey, $secretkey);
    $customer->update($arrParams);
    return $customer;
  }

  /**
   * Get the Omise Customer
   * @param  string  $customer_id  the customer id
   * @param  string  $publickey    the public key
   * @param  string  $secretkey    the secret key
   * @return OmiseCustomer         the OmiseCustomer instance
   */
  public function customerRetrieve($customer_id, $publickey = null, $secretkey = null) {
    return OmiseCustomer::retrieve($customer_id, $publickey, $secretkey);
  }

  /**
   * Delete the card from the customer
   * @param  string   $customer_id  the customer id
   * @param  string   $card_id      the card id to delete
   * @param  string  $publickey     the public key
   * @param  string  $secretkey     the secret key
   * @return boolean                return the status
   */
  public function customerDeleteCard($customer_id, $card_id, $publickey = null, $secretkey = null) {
    $customer = OmiseCustomer::retrieve($customer_id, $publickey, $secretkey);
    $card = $customer->getCards()->retrieve($card_id);
    $card->destroy();
    return $card->isDestroyed();
  }

  /**
   * Delete the default card from the customer
   * @param  string   $customer_id  the customer id
   * @param  string   $card_id      the card id to delete
   * @param  string  $publickey     the public key
   * @param  string  $secretkey     the secret key
   * @return boolean                return the status
   */
  public function customerDeleteDefaultCard($customer_id, $publickey = null, $secretkey = null) {
    $customer = OmiseCustomer::retrieve($customer_id, $publickey, $secretkey);
    $card = $customer->getCards()->retrieve($customer['default_card']);
    $card->destroy();
    return $card->isDestroyed();
  }

  /**
   * Delete the cards from the customer
   * @param  string   $customer_id  the customer id
   * @return void
   */
  public function destoryAllCustomerCard($customer_id) {
    $customer = OmiseCustomer::retrieve($customer_id);
    $cards = $customer->getCards();
    $cards = $cards['data'];
    foreach ($cards as &$obj) {
      $card = $customer->getCards()->retrieve($obj['id']);
      $card->destroy();
    }
  }


  /**
   * Return customer cards
   * @param  string  $customer_id  the customer id
   * @param  string  $publickey    the public key
   * @param  string  $secretkey    the secret key
   * @return OmiseCardList         the OmiseCardList instance
   */
  public function customerCards($customer_id, $publickey = null, $secretkey = null) {
    $customer = OmiseCustomer::retrieve($customer_id, $publickey, $secretkey);
    return $card = $customer->getCards();
  }

  /**
   * Return customer default card
   * @param  string  $customer_id  the customer id
   * @param  string  $publickey    the public key
   * @param  string  $secretkey    the secret key
   * @return OmiseCard             the OmiseCard instance
   */
  public function customerDefaultCard($customer_id, $publickey = null, $secretkey = null) {
    $customer = OmiseCustomer::retrieve($customer_id, $publickey, $secretkey);
    return $card = $customer->getCards()->retrieve($customer['default_card']);
  }

}
