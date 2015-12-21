<?php
namespace Plugin\OmisePaymentGateway;

use Eccube\Event\RenderEvent;
use Eccube\Event\ShoppingEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * イベント処理
 */
class OmiseEvent {
	
	private $app;

	public function __construct($app) {
		$this->app = $app;
	}
	
	public function onRenderShoppingBefore(FilterResponseEvent $event) {
		$request = $event->getRequest();
		$response = $event->getResponse();
		$order = $this->app['eccube.repository.order']->findOneBy(array('pre_order_id' => $this->app['eccube.service.cart']->getPreOrderId()));
		$id = $order->getPayment()->getId();

		$omiseConfigService = $this->app['eccube.plugin.service.omise_pg_config'];
		
		// TODO 決済画面でクレジット選択状態での入力表示〜決済投入
		return;
		
	}
	
	public function onRenderShoppingConfirmBefore(FilterResponseEvent $event) {
		
	}
	
	public function onControllerShoppingConfirmBefore() {
		
	}
}
