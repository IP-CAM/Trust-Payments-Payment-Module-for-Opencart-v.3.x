<?php
require_once modification(DIR_SYSTEM . 'library/trustpayments/helper.php');
use TrustPayments\Model\AbstractModel;

class ModelExtensionTrustPaymentsSetup extends AbstractModel {

	public function install(){
		$this->load->model("extension/trustpayments/migration");
		$this->load->model('extension/trustpayments/modification');
		$this->load->model('extension/trustpayments/dynamic');
		
		$this->model_extension_trustpayments_migration->migrate();
		
		try {
			$this->model_extension_trustpayments_modification->install();
			$this->model_extension_trustpayments_dynamic->install();
		}
		catch (Exception $e) {
		}
		
		$this->addPermissions();
		$this->addEvents();
	}

	public function synchronize($space_id){
		\TrustPaymentsHelper::instance($this->registry)->refreshApiClient();
		\TrustPaymentsHelper::instance($this->registry)->refreshWebhook();
		\TrustPayments\Service\MethodConfiguration::instance($this->registry)->synchronize($space_id);
	}

	public function uninstall($purge = true){
		$this->load->model("extension/trustpayments/migration");
		$this->load->model('extension/trustpayments/modification');
		$this->load->model('extension/trustpayments/dynamic');
		
		$this->model_extension_trustpayments_dynamic->uninstall();
		if ($purge) {
			$this->model_extension_trustpayments_migration->purge();
		}
		$this->model_extension_trustpayments_modification->uninstall();
		
		$this->removeEvents();
		$this->removePermissions();
	}

	private function addEvents(){
		$this->getEventModel()->addEvent('trustpayments_create_dynamic_files', 'admin/controller/marketplace/modification/after',
				'extension/trustpayments/event/createMethodConfigurationFiles');
		$this->getEventModel()->addEvent('trustpayments_can_save_order', 'catalog/model/checkout/order/editOrder/before',
				'extension/trustpayments/event/canSaveOrder');
		$this->getEventModel()->addEvent('trustpayments_update_items_after_edit', 'catalog/controller/api/order/edit/after', 'extension/trustpayments/event/update');
		$this->getEventModel()->addEvent('trustpayments_include_scripts', 'catalog/controller/common/header/before',
				'extension/trustpayments/event/includeScripts');
	}

	private function removeEvents(){
		$this->getEventModel()->deleteEventByCode('trustpayments_create_dynamic_files');
		$this->getEventModel()->deleteEventByCode('trustpayments_can_save_order');
		$this->getEventModel()->deleteEventByCode('trustpayments_update_items_after_edit');
		$this->getEventModel()->deleteEventByCode('trustpayments_include_scripts');
	}

	/**
	 * Adds basic permissions.
	 * Permissions per payment method are added while creating the dynamic files.
	 */
	private function addPermissions(){
		$this->load->model("user/user_group");
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/trustpayments/event');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/trustpayments/completion');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/trustpayments/void');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/trustpayments/refund');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/trustpayments/update');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/trustpayments/pdf');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/trustpayments/alert');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/trustpayments/transaction');
	}

	private function removePermissions(){
		$this->load->model("user/user_group");
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/trustpayments/event');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/trustpayments/completion');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/trustpayments/void');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/trustpayments/refund');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/trustpayments/update');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/trustpayments/pdf');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/trustpayments/alert');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/trustpayments/transaction');
	}
}