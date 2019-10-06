<?php
/******************************************************
 * @package Pavo Store Locator Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class ControllerExtensionModulePavstorelocator extends Controller {

	
	private $error = array();

	/**
	 * install actions
	 * create new permission and tables
	 */
	public function install() {
		$this->load->model('extension/pavstorelocator/location');
		
		$this->model_extension_pavstorelocator_location->install();
	}

	/**
	 * uninstall actions
	 * remove user permission
	 */
	public function uninstall() {
		$this->load->model('extension/pavstorelocator/location');
		$this->model_extension_pavstorelocator_location->uninstall();
	}

	/**
	 * @action index to render content of setting form
	 */
	public function index() {
		
		$this->load->language('extension/module/pavstorelocator');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');
		$this->load->model('setting/setting');
		$this->load->model('tool/image');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {


			if( isset($this->request->post['post_action']) && $this->request->post['post_action'] == 'save_back' ){
				unset( $this->request->post['post_action'] );
				$redirect = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
			}else {
				$redirect = $this->url->link('extension/module/pavstorelocator', 'user_token=' . $this->session->data['user_token'] , true);
			}

			$data['pavstorelocator'] = $this->request->post;

		//	echo '<pre>'.print_r( $data, 1);die;
			$this->model_setting_setting->editSetting( 'pavstorelocator', $data );
	 
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect( $redirect );
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/pavstorelocator', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/pavstorelocator', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		$data['link_products_deal'] = HTTPS_CATALOG."index.php?route=extension/module/pavstorelocator";
		$data['link_search_product_deal'] =  $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token']."&filter_query=extension/module/pavstorelocator", true); 
		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/pavstorelocator', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/pavstorelocator', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['templates']		 = array(
			'default' 	 		 => $this->language->get('default'),
			'horizontal' 	 	 => $this->language->get('horizontal'),
		);

		$info = $this->model_setting_setting->getSetting( 'pavstorelocator' );


		$data['seo'] = $data['setting'] = array();  

		if( $info ){
			$data['seo'] = $info['pavstorelocator']['seo'];
			$data['setting'] = $info['pavstorelocator']['setting'];
		}

		if( isset($info['pavstorelocator']['template']) ){
			$data['template'] = $info['pavstorelocator']['template'];
		}else {
			$data['template'] = '';
		}

		if( isset($info['pavstorelocator']['google_api_key']) ){
			$data['google_api_key'] = $info['pavstorelocator']['google_api_key'];
		}else {
			$data['google_api_key'] = '';
		}
		
		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/pavstorelocator', $data));
	}

	/**
	 * Check data validation
	 */
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/pavstorelocator')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
	
}