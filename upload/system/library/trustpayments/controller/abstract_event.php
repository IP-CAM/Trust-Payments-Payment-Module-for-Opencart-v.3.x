<?php

namespace TrustPayments\Controller;

abstract class AbstractEvent extends AbstractController {

	protected function validate(){
		$this->language->load('extension/payment/trustpayments');
		$this->validatePermission();
		// skip valdiating order.
	}
}