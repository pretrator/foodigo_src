<?php
/******************************************************
 * @package Pav blog module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class ControllerExtensionModulePavnewsletter extends Controller {

	private $mdata = array();

	/**
	 *
	 */
	public function loadScript(){
		$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
  		$this->document->addScript('catalog/view/javascript/jquery/pavnewsletter.js' );
	}

	/**
	 *
	 */
	public function index($setting) {

		// pavo 2.2 fix
		$this->load->language('extension/module/themecontrol');

		$config_theme = $this->config->get('theme_default_directory');

		$this->mdata['objlang']   = $this->language;
		$this->mdata['ourl']      = $this->url;
		$this->mdata['sconfig']   = $this->config;
		$this->mdata['themename'] = $config_theme;
		// pavo 2.2 fix

		static $module = 0;
		$this->load->language('extension/module/pavnewsletter');
		$this->load->model('account/customer');
		$this->load->model('tool/image');
		$this->load->model('extension/pavnewsletter/subscribe');

		$this->mdata['objlang'] = $this->language;

		$general_setting = $this->config->get("pavnewsletter_config");
		$language_id     = $this->config->get("config_language_id");

		$show_box = true;
		if ($this->customer->isLogged()) {
			$customer_email = $this->customer->getEmail();
			if ($this->model_extension_pavnewsletter_subscribe->checkExists($customer_email)) {
				$show_box = false;
			}
			$this->mdata['customer_email'] = $customer_email;
		}

		$this->loadScript();
		$this->document->addScript('catalog/view/javascript/jquery/pavnewsletter.js');
		if ( file_exists( DIR_TEMPLATE . $config_theme  . '/stylesheet/pavnewsletter.css') && isset($setting['loadcss']) && $setting['loadcss'] ) {
			$this->document->addStyle('catalog/view/theme/' . $config_theme . '/stylesheet/pavnewsletter.css');
		} else {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/pavnewsletter.css');
		}

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$this->mdata['base'] = $this->config->get('config_ssl');
		} else {
			$this->mdata['base'] = $this->config->get('config_url');
		}
		$this->mdata['action']   = $this->url->link('extension/module/pavnewsletter/subscribe', '', 'SSL');
		$this->mdata['prefix']   = isset($setting['extra_class']) ? $setting['extra_class'] : '';
		$this->mdata['position'] = isset($setting['position']) ? $setting['position'] : '';

		$this->mdata['description'] = htmlspecialchars_decode( isset($setting['description'][$language_id]) ? $setting['description'][$language_id] : $this->language->get('description') );
		$this->mdata['social']      = htmlspecialchars_decode( isset($setting['social'][$language_id]) ? $setting['social'][$language_id] : '');
		$this->mdata['mode']        = isset($setting['displaymode']) ? $setting['displaymode'] : "default";
		$this->mdata['layout']      = isset($setting['layoutmode']) ? $setting['layoutmode'] : "default";
		$this->mdata['module']      = rand();
		$this->mdata['name']   		= isset($setting['name']) ? $setting['name'] : '';
		$this->mdata['id']	 = uniqid();

		$template = 'extension/module/pavnewsletter/'.$this->mdata['mode'];


		$banner  =  '';
		if( isset($setting['banner']) && $setting['banner'] ){
			if ($this->request->server['HTTPS']) {
				$banner = $this->config->get('config_ssl') . 'image/' . $setting['banner'];
			} else {
				$banner = $this->config->get('config_url') . 'image/' . $setting['banner'];
			}

		}
	 	$this->mdata['banner'] = $banner;
		return $this->load->view($template, $this->mdata);
	}

	public function subscribe() {
		$this->language->load('extension/module/pavnewsletter');
		$this->load->model('account/customer');
		$this->load->model('extension/pavnewsletter/subscribe');

		$json = array();

		$data                = array();
		$data['store_id']    = $this->config->get('config_store_id');
		$data['customer_id'] = 0;
		$data['email']       = $this->request->post['email'];
		$data['store_id']    = 0;
		if (!$this->model_extension_pavnewsletter_subscribe->checkExists($this->request->post['email'])) {
			if ($customer = $this->model_account_customer->getCustomerByEmail($this->request->post['email'])) {
				$data['customer_id'] = $customer['customer_id'];
			}
			$this->model_extension_pavnewsletter_subscribe->storeSubscribe($data);

			$json['success'] = $this->language->get('success_post');

			$json['redirect'] = $this->url->link('common/home', '', 'SSL');
		} else {
			$json['error'] = $this->language->get('error_post');
		}
		$this->response->setOutput(json_encode($json));
	}

	public function subs() {
		$this->language->load('extension/module/pavnewsletter');
		$this->load->model('account/customer');
		$this->load->model('extension/pavnewsletter/subscribe');

		$json = array();

		$data                = array();
		$data['store_id']    = $this->config->get('config_store_id');
		$data['customer_id'] = 0;
		$data['email']       = $this->request->post['email'];
		$data['store_id']    = 0;
		if (!$this->model_extension_pavnewsletter_subscribe->checkExists($this->request->post['email'])) {

			if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL) === false) {
				if ($customer = $this->model_account_customer->getCustomerByEmail($this->request->post['email'])) {
					$data['customer_id'] = $customer['customer_id'];
				}
				$this->model_extension_pavnewsletter_subscribe->storeSubscribe($data);

				$json['success'] = $this->language->get('success_post');

				$json['redirect'] = $this->url->link('common/home', '', 'SSL');

			} else {
				$json['error'] = $this->language->get('valid_email');
			}

		} else {
			$json['error'] = $this->language->get('error_post');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	public function unsubsribe(){
		$this->load->model('extension/pavnewsletter/subscribe');
		if (isset($this->request->get['subscribe_id'])) {
			$subscribe_id = $this->request->get['subscribe_id'];
		} else {
			$subscribe_id = 0;
		}
		if(!empty($subscribe_id)){
			$this->model_extension_pavnewsletter_subscribe->updateAction($subscribe_id, 0);
		}
		$this->response->redirect($this->url->link('extension/module/pavnewsletter/subsribes', 'user_token=' . $this->session->data['user_token'], true));
	}

	protected function validate($data = array()) {
		if (!empty($data)) {
			if (isset($data['email']) && !empty($data['email']) && isset($data['action'])) {
				return true;
			}

		}
		return false;
	}
}
