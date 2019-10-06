<?php
class ControllerExtensionModuleSmsAlert extends Controller {
	protected $version = '2.0.0';
	protected $error = array();

	public function index() {
		$this->load->language('extension/module/smsalert');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->load->model('extension/module/smsalert');
				
		$this->getList();
	}
	
	public function insert() {
		$this->load->language('extension/module/smsalert');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->load->model('extension/module/smsalert');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_module_smsalert->addTemplate($this->request->post);		
					
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
		
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->response->redirect($this->url->link('extension/module/smsalert', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
				
		$this->getForm();
	}
	
	public function update() {
		$this->load->language('extension/module/smsalert');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->load->model('extension/module/smsalert');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_module_smsalert->editTemplate($this->request->get['template_id'], $this->request->post);		
					
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
		
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->response->redirect($this->url->link('extension/module/smsalert', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
				
		$this->getForm();
	}
	
	public function delete() {
		$this->load->language('extension/module/smsalert');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->load->model('extension/module/smsalert');
		
		if (isset($this->request->post['selected']) && $this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $template_id) {
				$this->model_extension_module_smsalert->deleteTemplate($template_id);
			}
					
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
		
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->response->redirect($this->url->link('extension/module/smsalert', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		
		$this->getList();
	}
	
	public function copy() {
		$this->load->language('extension/module/smsalert');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		
		$this->load->model('extension/module/smsalert');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['selected'])) {
			foreach ($this->request->post['selected'] as $template_id) {
				$template_info = $this->model_extension_module_smsalert->getTemplate($template_id);
				
				$template_info['name'] .= ' Copy';
				
				$this->model_extension_module_smsalert->addTemplate($template_info);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');
		}
		
		$url = '';
	
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->response->redirect($this->url->link('extension/module/smsalert', 'user_token=' . $this->session->data['user_token'] . $url, true));
	}
	
	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$url = '';
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data = array(
			'sort'		=> $sort,
			'order'		=> $order,
			'page'		=> $page,
			'start'		=> $this->config->get('config_admin_limit') * ($page - 1),
			'limit'		=> $this->config->get('config_admin_limit')
		);
		
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/smsalert', 'user_token=' . $this->session->data['user_token'], true)
   		);
		
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['column_name'] = $this->language->get('column_name');
		$data['column_type'] = $this->language->get('column_type');
		$data['column_store'] = $this->language->get('column_store');
		$data['column_action'] = $this->language->get('column_action');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['tab_config'] = $this->language->get('tab_config');
		$data['tab_list'] = $this->language->get('tab_list');
		
		$data['entry_auth_key'] = $this->language->get('entry_auth_key');
		$data['entry_auth_secret'] = $this->language->get('entry_auth_secret');
		$data['entry_default_senderid'] = $this->language->get('entry_default_senderid');
		$data['entry_debug'] = $this->language->get('entry_debug');
		
		$data['text_no_results'] = $this->language->get('text_no_results');
		
		$data['button_insert'] = $this->language->get('button_insert');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_copy'] = $this->language->get('button_copy');
		
		$data['copy'] = $this->url->link('extension/module/smsalert/copy', 'user_token=' . $this->session->data['user_token'], true);
		$data['action'] = $this->url->link('extension/module/smsalert/delete', 'user_token=' . $this->session->data['user_token'], true);
		$data['insert'] = $this->url->link('extension/module/smsalert/insert', 'user_token=' . $this->session->data['user_token'], true);
		$data['user_token'] = $this->session->data['user_token'];
		
		$data['smsalert_auth_key'] = $this->config->get('smsalert_auth_key');
		$data['smsalert_auth_secret'] = $this->config->get('smsalert_auth_secret');
		$data['smsalert_default_senderid'] = $this->config->get('smsalert_default_senderid');
		$data['smsalert_debug'] = $this->config->get('smsalert_debug');
		$data['module_smsalert_status'] = $this->config->get('module_smsalert_status');
		
		$templates = $this->model_extension_module_smsalert->getTemplates($data);
		
		$data['templates'] = array();
		
		$this->load->model('localisation/order_status');
		$this->load->model('setting/store');
		
		foreach ($templates as $template) {
			$action = array();
			
			$action[] = array(
				'link'		=> $this->url->link('extension/module/smsalert/update', 'user_token=' . $this->session->data['user_token'] . $url . '&template_id=' . $template['sms_template_id'], true),
				'name'		=> $this->language->get('text_edit_template')
			);
			
			$type = $this->model_localisation_order_status->getOrderStatus($template['type']);
			
			if ($type) {
				$type = $this->language->get('text_status') . ' ' . $type['name'];
			} else {
				$type = $this->language->get('text_' . $template['type']);
			}
			
			if ($template['store_id']) {
				$store = $this->model_setting_store->getStore($template['store_id']);
				$store = $store['name'];
			} else {
				$store = $this->language->get('text_default');
			}
			
			$data['templates'][] = array(
				'sms_template_id'		=> $template['sms_template_id'],
				'name'					=> $template['name'],
				'type'					=> $type,
				'store'					=> $store,
				'selected'     			=> isset($this->request->post['selected']) && in_array($template['sms_template_id'], $this->request->post['selected']),
				'action'				=> $action
			);
		}
		
		$url = '';
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		$pagination = new Pagination();
		$pagination->total = $this->model_extension_module_smsalert->getTotalTemplates();
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('extension/module/phe', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
			
		$data['pagination'] = $pagination->render();
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		
		if ($order == 'ASC') {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}
		
		$url = '';
		
		if (isset($this->request->get['page'])) { 
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['sort_name'] = $this->url->link('extension/module/smsalert', 'user_token=' . $this->session->data['user_token'] . '&sort=name&order=' . $order . $url, true);
		$data['sort_type'] = $this->url->link('extension/module/smsalert', 'user_token=' . $this->session->data['user_token'] . '&sort=type&order=' . $order . $url, true);
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/smsalert_list', $data));
	}
	
	protected function getForm() {
		$url = '';
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/smsalert', 'user_token=' . $this->session->data['user_token'], true)
   		);
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['tab_general'] = $this->language->get('tab_general');
		
		$data['entry_type'] = $this->language->get('entry_type');
		$data['entry_store'] = $this->language->get('entry_store');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_country'] = $this->language->get('entry_country');
		$data['entry_message'] = $this->language->get('entry_message');
		$data['entry_bcc'] = $this->language->get('entry_bcc');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_register'] = $this->language->get('text_register');
		$data['text_affiliate'] = $this->language->get('text_affiliate');
		$data['text_order'] = $this->language->get('text_order');
		$data['text_contact'] = $this->language->get('text_contact');
		$data['text_forgotten'] = $this->language->get('text_forgotten');
		$data['text_reward'] = $this->language->get('text_reward');
		$data['text_account_approve'] = $this->language->get('text_account_approve');
		$data['text_account_transaction'] = $this->language->get('text_account_transaction');
		$data['text_affiliate_approve'] = $this->language->get('text_affiliate_approve');
		$data['text_affiliate_transaction'] = $this->language->get('text_affiliate_transaction');
		$data['text_gift_voucher'] = $this->language->get('text_gift_voucher');
		$data['text_status'] = $this->language->get('text_status');
		$data['text_code'] = $this->language->get('text_code');
		$data['text_default'] = $this->language->get('text_default');
		$data['text_select_all'] = $this->language->get('text_select_all');
		$data['text_unselect_all'] = $this->language->get('text_unselect_all');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
		if (isset($this->request->get['template_id'])) {
			$data['action'] = $this->url->link('extension/module/smsalert/update', 'user_token=' . $this->session->data['user_token'] . $url . '&template_id=' . $this->request->get['template_id'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/smsalert/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		}
		
		$data['cancel'] = $this->url->link('extension/module/smsalert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['user_token'] = $this->session->data['user_token'];
		
		if (isset($this->request->get['template_id'])) { 
			$template_info = $this->model_extension_module_smsalert->getTemplate($this->request->get['template_id']);
		} else {
			$template_info = '';
		}
		
		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($template_info)) {
			$data['name'] = $template_info['name'];
		} else {
			$data['name'] = '';
		}
		
		if (isset($this->request->post['type'])) {
			$data['type'] = $this->request->post['type'];
		} elseif (!empty($template_info)) {
			$data['type'] = $template_info['type'];
		} else {
			$data['type'] = '';
		}
		
		$this->load->model('setting/store');
		
		$data['stores'] = $this->model_setting_store->getStores();
		
		if (isset($this->request->post['store_id'])) {
			$data['store_id'] = $this->request->post['store_id'];
		} elseif (!empty($template_info)) {
			$data['store_id'] = $template_info['store_id'];
		} else {
			$data['store_id'] = '';
		}
		
		if (isset($this->request->post['bcc'])) {
			$data['bcc'] = $this->request->post['bcc'];
		} elseif (!empty($template_info)) {
			$data['bcc'] = $template_info['bcc'];
		} else {
			$data['bcc'] = '';
		}
		
		if (isset($this->request->post['country'])) {
			$data['country'] = $this->request->post['country'];
		} else {
			$data['country'] = array();
		}
		
		$this->load->model('localisation/order_status');
		
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif (!empty($template_info)) {
			$data['description'] = $template_info['description'];
		} else {
			$data['description'] = array();
		}
		
		$this->load->model('localisation/language');
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('localisation/country');
		
		$data['countries'] = $this->model_localisation_country->getCountries();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/smsalert_form', $data));
	}
	
	public function config() {
		if ($this->validateDelete()) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('smsalert', $this->request->post);			
			$this->model_setting_setting->editSetting('module', array('module_smsalert_status'=>'1'));
			$this->response->setOutput(json_encode(array()));
		}
    }
	
	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/smsalert')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (empty($this->request->post['name'])) {
			$this->error['warning'] = $this->language->get('error_name');
		}
		
		return !$this->error;
	}
	
	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/module/smsalert')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
	
	public function install() {
		if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
			return;
		}
		
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "sms_template` (
		  `sms_template_id` int(11) NOT NULL AUTO_INCREMENT,
		  `type` varchar(255) NOT NULL,
		  `store_id` int(11) NOT NULL,
		  `name` varchar(32) NOT NULL,
		  `bcc` varchar(255) NOT NULL,
		  PRIMARY KEY (`sms_template_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;");
		
		$this->db->query("CREATE TABLE `" . DB_PREFIX . "sms_template_message` (
		  `sms_template_id` int(11) NOT NULL,
		  `language_id` int(11) NOT NULL,
		  `message` text NOT NULL
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "sms_template` (`sms_template_id`, `type`, `store_id`, `name`, `bcc`) VALUES
			(1, 'register', 0, 'Account Registration (Default)', ''),
			(2, 'affiliate', 0, 'Affiliate Registration (Default)', ''),
			(3, 'order', 0, 'Order Confirmation (Default)', ''),
			(4, 'forgotten', 0, 'Forgotten Password (Default)', ''),
			(5, 'reward', 0, 'Add Reward (Default)', ''),
			(6, 'account_approve', 0, 'Account Approval (Default)', ''),
			(7, 'account_transaction', 0, 'Add Transaction (Default)', ''),
			(8, 'affiliate_approve', 0, 'Affiliate Approval (Default)', ''),
			(9, 'affiliate_transaction', 0, 'Affiliate Add Commission (Defaul)', ''),
			(10, '2', 0, 'Processing (Default)', '');
		");
		
		$this->db->query("INSERT INTO `" . DB_PREFIX . "sms_template_message` (`sms_template_id`, `language_id`, `message`) VALUES
			(1, 1, 'Welcome {firstname} and thank you for registering at " . $this->config->get('config_name') . ". Your account has now been created and you can log in by using your email address and password on our website at " . HTTPS_CATALOG . ".'),
			(2, 1, 'Welcome {firstname} and thank you for joining " . $this->config->get('config_name') . "\'s Affiliate Program. We will inform you once your account has been approved.'),
			(3, 1, 'Hi {firstname},\r\n\r\nYour order #{order_id} has been received and will be processed once payment has been confirmed.'),
			(4, 1, 'Hi {firstname},\r\n\r\nA password reset was requested for your account on " . $this->config->get('config_name') . ". If you did not request for a new password, please inform us immediately and update your password.'),
			(5, 1, 'Hi {firstname},\r\n\r\nYou have received {points} reward points! You may now spend it in our store. You currently have a total of {total_points} reward points.'),
			(6, 1, 'Hi {firstname},\r\n\r\nYour account has now been approved and you can log in by using your email address and password on our website at " . HTTPS_CATALOG . ".'),
			(7, 1, 'Hi {firstname},\r\n\r\nYou have received {credits} store credits! You may now spend it in our store. You currently have a total of {total_credits} store credits.'),
			(8, 1, 'Hi {firstname},\r\n\r\nYour affiliate account has now been approved and you can log in by using your email address and password on our website at " . HTTPS_CATALOG . ".'),
			(9, 1, 'Hi {firstname},\r\n\r\nYou have received {commission} commission! You have currently earned a total of {total_commission} commission.'),
			(10, 1, 'Hi {firstname},\r\n\r\nWe are currently processing your order #{order_id}. Feel free drop us an email if you have any queries.');
		");
		
		$this->load->model('setting/event');
		
		$this->model_setting_event->addEvent('module_smsalert', 'catalog/model/account/customer/addCustomer/after', 'extension/module/smsalert/eventPostModelAccountCustomerAdd');
		$this->model_setting_event->addEvent('module_smsalert', 'catalog/model/account/customer/addAffiliate/after', 'extension/module/smsalert/eventPostModelAccountCustomerAddAffiliate');
		$this->model_setting_event->addEvent('module_smsalert', 'catalog/model/account/customer/addTransaction/after', 'extension/module/smsalert/eventPostModelAccountCustomerAddTransaction');
		$this->model_setting_event->addEvent('module_smsalert', 'catalog/model/account/customer/editCode/after', 'extension/module/smsalert/eventPostModelAccountCustomerEditCode');
		$this->model_setting_event->addEvent('module_smsalert', 'catalog/model/checkout/order/addOrderHistory/before', 'extension/module/smsalert/eventPreModelCheckoutOrderAddOrderHistory');
		$this->model_setting_event->addEvent('module_smsalert', 'admin/model/customer/customer/addReward/after', 'extension/module/smsalert/eventPostModelCustomerCustomerAddReward');
		$this->model_setting_event->addEvent('module_smsalert', 'admin/model/customer/customer/addTransaction/after', 'extension/module/smsalert/eventPostModelCustomerCustomerAddTransaction');
		$this->model_setting_event->addEvent('module_smsalert', 'admin/model/customer/customer_approval/approveCustomer/after', 'extension/module/smsalert/eventPostModelCustomerCustomerApprovalApproveCustomer');
		$this->model_setting_event->addEvent('module_smsalert', 'admin/model/customer/customer_approval/approveAffiliate/after', 'extension/module/smsalert/eventPostModelCustomerCustomerApprovalApproveAffiliate');
	}
	
	public function uninstall() {
		if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
			return;
		}
		
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "sms_template");
		$this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "sms_template_message");
		
		$this->load->model('setting/event');
		
		$this->model_setting_event->deleteEventByCode('module_smsalert');
	}
	
	public function eventPostModelCustomerCustomerAddReward($route, $args, $output) {
		$this->load->model('customer/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_id = isset($args[0]) ? $args[0] : 0;
		$description = isset($args[1]) ? $args[1] : '';
		$points = isset($args[2]) ? $args[2] : 0;
		$order_id = isset($args[3]) ? $args[3] : 0;
		
		$customer_info = $this->model_customer_customer->getCustomer($customer_id);
		
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email'],
			$points,
			$this->model_customer_customer->getRewardTotal($customer_id)
		);
		
		if ($order_id) {
			$this->load->model('sale/order');
			
			$order_info = $this->model_sale_order->getOrder($order_id);
		
			$store_id = $order_info['store_id'];
		} else {
			$store_id = $customer_info['store_id'];
		}
		
		$this->model_extension_module_smsalert->parseSMS('reward', $store_id, $customer_info['telephone'], $replace);
	}
	
	public function eventPostModelCustomerCustomerAddTransaction($route, $args, $output) {
		$this->load->model('customer/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_id = isset($args[0]) ? $args[0] : 0;
		$description = isset($args[1]) ? $args[1] : '';
		$amount = isset($args[2]) ? $args[2] : 0;
		$order_id = isset($args[3]) ? $args[3] : 0;
		
		$customer_info = $this->model_customer_customer->getCustomer($customer_id);
				
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email'],
			$this->currency->format($amount, $this->config->get('config_currency')),
			$this->currency->format($this->model_customer_customer->getTransactionTotal($customer_id), $this->config->get('config_currency'))
		);
		
		if ($order_id) {
			$this->load->model('sale/order');
			
			$order_info = $this->model_sale_order->getOrder($order_id);
		
			$store_id = $order_info['store_id'];
		} else {
			$store_id = $customer_info['store_id'];
		}
		
		$this->model_extension_module_smsalert->parseSMS('account_transaction', $store_id, $customer_info['telephone'], $replace);
	}
	
	public function eventPostModelCustomerCustomerApprovalApproveCustomer($route, $args, $output) {
		$this->load->model('customer/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_info = $this->model_customer_customer->getCustomer($args[0]);
				
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email']
		);
		
		$this->model_extension_module_smsalert->parseSMS('account_approve', $customer_info['store_id'], $customer_info['telephone'], $replace);
	}
	
	public function eventPostModelCustomerCustomerApprovalApproveAffiliate($route, $args, $output) {
		$this->load->model('customer/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_info = $this->model_customer_customer->getCustomer($args[0]);
		
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email']
		);
		
		$this->model_extension_module_smsalert->parseSMS('affiliate_approve', $this->config->get('config_store_id'), $customer_info['telephone'], $replace);
	}
}