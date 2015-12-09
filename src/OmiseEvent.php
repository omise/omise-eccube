<?php
namespace Plugin\OmisePaymentGateway;

use Eccube\Common\Constant;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * イベント処理
 */
class OmiseEvent {
	
	private $app;

	public function __construct($app) {
		$this->app = $app;
	}
}
