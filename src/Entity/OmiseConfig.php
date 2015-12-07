<?php
namespace Plugin\OmisePayment\Entity;

class OmiseConfig {
	private $id;
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
	 * 
	 * @return string
	 */
	public function getInfo() {
		return $this->info;
	}
	/**
	 * 
	 * @param string $info
	 * @return \Plugin\OmisePayment\Entity\OmiseConfig
	 */
	public function setInfo($info) {
		$this->info = $info;
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
}
