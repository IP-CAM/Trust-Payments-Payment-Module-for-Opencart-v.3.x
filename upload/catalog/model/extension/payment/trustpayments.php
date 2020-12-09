<?php

/**
 * Prevents loading inexisting model, but allow plugin status to be correct
 */
class ModelExtensionPaymentTrustPayments extends Model {

	public function getMethod($address, $total){
		return array();
	}
}