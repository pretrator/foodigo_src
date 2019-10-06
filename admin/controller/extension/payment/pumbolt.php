<?php 
class ControllerExtensionPaymentPumbolt extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('extension/payment/pumbolt');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('payment_pumbolt', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['entry_module'] = $this->language->get('entry_module');
		$data['entry_module_id'] = $this->language->get('entry_module_id');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_order_status'] = $this->language->get('entry_order_status');	
		$data['entry_order_fail_status'] = $this->language->get('entry_order_fail_status');	
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_currency'] = $this->language->get('entry_currency');
		$data['help_currency'] = $this->language->get('help_currency');
		
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');		
		$data['text_edit'] = $this->language->get('text_edit');
		
		$data['entry_merchant'] = $this->language->get('entry_merchant');
		$data['entry_salt'] = $this->language->get('entry_salt');
		$data['entry_total'] = $this->language->get('entry_total');	
		
		$data['help_merchant'] = $this->language->get('help_merchant');
		$data['help_total'] = $this->language->get('help_total');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
        $data['help_salt'] = $this->language->get('help_salt');
		$data['tab_general'] = $this->language->get('tab_general');
		
		if(!isset($this->error['error_merchant'])) $this->error['error_merchant'] ='';
		if(!isset($this->error['error_salt'])) $this->error['error_salt'] = '';
		if(!isset($this->error['error_currency'])) $this->error['error_currency'] = '';
		if(!isset($this->error['error_status'])) $this->error['error_status'] = '';
		if(!isset($this->error['error_module'])) $this->error['error_module'] = '';

 		if ($this->error) {
			$data = array_merge($data,$this->error);
		} 
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true),
      		'separator' => ' :: '
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/payment/pumbolt', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => ' :: '
   		);
				
		$data['action'] = $this->url->link('extension/payment/pumbolt', 'user_token=' . $this->session->data['user_token'], 'SSL');
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');
		
		if (isset($this->request->post['payment_pumbolt_module'])) {
			$data['payment_pumbolt_module'] = $this->request->post['payment_pumbolt_module'];
		} else {
			$data['payment_pumbolt_module'] = $this->config->get('payment_pumbolt_module');
		}
		
		
		if (isset($this->request->post['payment_pumbolt_payu_key'])) {
			$data['payment_pumbolt_payu_key'] = $this->request->post['payment_pumbolt_payu_key'];
		} else {
			$data['payment_pumbolt_payu_key'] = $this->config->get('payment_pumbolt_payu_key');
		}
		
		if (isset($this->request->post['payment_pumbolt_payu_salt'])) {
			$data['payment_pumbolt_payu_salt'] = $this->request->post['payment_pumbolt_payu_salt'];
		} else {
			$data['payment_pumbolt_payu_salt'] = $this->config->get('payment_pumbolt_payu_salt');
		}
		
		if (isset($this->request->post['payment_pumbolt_total'])) {
			$data['payment_pumbolt_total'] = $this->request->post['payment_pumbolt_total'];
		} else {
			$data['payment_pumbolt_total'] = $this->config->get('payment_pumbolt_total'); 
		} 
		
		if (isset($this->request->post['payment_pumbolt_currency'])) {
			$data['payment_pumbolt_currency'] = $this->request->post['payment_pumbolt_currency'];
		} else {
			$data['payment_pumbolt_currency'] = $this->config->get('payment_pumbolt_currency'); 
		} 
				
		if (isset($this->request->post['payment_pumbolt_order_status_id'])) {
			$data['payment_pumbolt_order_status_id'] = $this->request->post['payment_pumbolt_order_status_id'];
		} else {
			$data['payment_pumbolt_order_status_id'] = $this->config->get('payment_pumbolt_order_status_id'); 
		} 

		if (isset($this->request->post['payment_pumbolt_order_fail_status_id'])) {
			$data['payment_pumbolt_order_fail_status_id'] = $this->request->post['payment_pumbolt_order_fail_status_id'];
		} else {
			$data['payment_pumbolt_order_fail_status_id'] = $this->config->get('payment_pumbolt_order_fail_status_id'); 
		} 
		
		$this->load->model('localisation/order_status');
		
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['payment_pumbolt_geo_zone_id'])) {
			$data['payment_pumbolt_geo_zone_id'] = $this->request->post['payment_pumbolt_geo_zone_id'];
		} else {
			$data['payment_pumbolt_geo_zone_id'] = $this->config->get('payment_pumbolt_geo_zone_id'); 
		} 
		
		$this->load->model('localisation/geo_zone');
										
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['payment_pumbolt_status'])) {
			$data['payment_pumbolt_status'] = $this->request->post['payment_pumbolt_status'];
		} else {
			$data['payment_pumbolt_status'] = $this->config->get('payment_pumbolt_status');
		}
		
		if (isset($this->request->post['payment_pumbolt_sort_order'])) {
			$data['payment_pumbolt_sort_order'] = $this->request->post['payment_pumbolt_sort_order'];
		} else {
			$data['payment_pumbolt_sort_order'] = $this->config->get('payment_pumbolt_sort_order');
		}
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

				
		$this->response->setOutput($this->load->view('extension/payment/pumbolt', $data));
	}

	private function validate() {
		$flag=false;
		
		if (!$this->user->hasPermission('modify', 'extension/payment/pumbolt')) {
			$this->error['error_warning'] = $this->language->get('error_permission');
		}
		//PayU both parameters mandatory
		if($this->request->post['payment_pumbolt_payu_key'] || $this->request->post['payment_pumbolt_payu_salt']) {
			if (!$this->request->post['payment_pumbolt_payu_key']) {
				$this->error['error_merchant'] = $this->language->get('error_merchant');
			}
			
			if (!$this->request->post['payment_pumbolt_payu_salt']) {
				$this->error['error_salt'] = $this->language->get('error_salt');
			}
		}
		if($this->request->post['payment_pumbolt_payu_key'] && $this->request->post['payment_pumbolt_payu_salt']) {
			$flag=true;	
		}
		
		if (!$this->request->post['payment_pumbolt_module']) {
			$this->error['error_module'] = $this->language->get('error_module');
		}
		
		if(!$this->request->post['payment_pumbolt_currency'] || strlen($this->request->post['payment_pumbolt_currency']) < 3)
		{
			$this->error['error_currency'] = $this->language->get('error_currency');
		}
		else {
			$this->request->post['payment_pumbolt_currency'] = strtoupper($this->request->post['payment_pumbolt_currency']);
		}
		
		if(!$flag && $this->request->post['payment_pumbolt_status'] == '1')
		{
			$this->error['error_status'] = $this->language->get('error_status');
		}

		return !$this->error;
	}
}
?>