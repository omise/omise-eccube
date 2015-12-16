<?php
namespace Plugin\OmisePaymentGateway\Entity;

class OmiseConfig {
	private $id;
    private $code;
    private $payment_id;
	private $info;
	private $delete_flg;
	private $create_date;
	private $update_date;

	/**
	 *
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}
	/**
	 * 
	 * @param integer $id
	 * @return \Plugin\OmisePayment\Entity\OmiseConfig
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}
	/**
	 * 
	 * @param string $code
	 * @return \Plugin\OmisePayment\Entity\OmiseConfig
	 */
	public function setCode($code) {
		$this->code = $code;
		return $this;
	}
	
	/**
	 * @return integer
	 */
	public function setPaymentId() {
		$this->payment_id;
	}
	
	/**
	 * @param integer $payment_id
	 * @return \Plugin\OmisePayment\Entity\OmiseConfig
	 */
	public function setPaymentId($payment_id) {
		$this->payment_id = $payment_id;
		
		return $this;
	}
	
	/**
	 * シリアライズされた文字列が欲しい場合$serializedをtrueにすること
	 * @return mixed
	 */
	public function getInfo($serialized = false) {
		if($serialized) {
			return $this->info;
		} else {
			return unserialize($this->info);
		}
	}
	/**
	 * $infoがシリアライズされている文字列の場合$serializedをtrueにすること。
	 * @param mixed $info
	 * @param boolean $serialized
	 * @return \Plugin\OmisePayment\Entity\OmiseConfig
	 */
	public function setInfo($info, $serialized = false) {
		if($unserialized) {
			$this->info = $info;
		} else {
			$this->info = serialize($info);
		}
		return $this;
	}
	
	/**
	 * 
	 * @return integer
	 */
	public function getDeleteFlg() {
		return $this->delete_flg;
	}
	/**
	 * 
	 * @param integer $delete_flg
	 * @return \Plugin\OmisePayment\Entity\OmiseConfig
	 */
	public function setDeleteFlg($delete_flg) {
		$this->delete_flg = $delete_flg;
		return $this;
	}

	/**
	 * 
	 * @return \DateTime
	 */
	public function getCreateDate() {
		return $this->create_date;
	}
	/**
	 * 
	 * @param $create_date
	 * @return \Plugin\OmisePayment\Entity\OmiseConfig 
	 */
	public function setCreateDate($create_date) {
		$this->create_date = $create_date;
		return $this;
	}
	
	/**
	 * 
	 * @return \DateTime
	 */
	public function getUpdateDate() {
		return $this->update_date;
	}
	/**
	 * 
	 * @param \DateTime $update_date
	 * @return \Plugin\OmisePayment\Entity\OmiseConfig
	 */
	public function setUpdateDate($update_date) {
		$this->update_date = $update_date;
		return $this;
	}
	
    public function __toString()
    {
        return $this->getCode();
    }
}
