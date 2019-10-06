<?php
/******************************************************
 * @package Pav Flash Sale module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Nov 2017  PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class ControllerExtensionModulePavflashsale extends Controller {

	private $error = array();

	/**
	 * install actions
	 * create new permission and tables
	 */
	public function install() {
		$this->load->model( 'extension/pavflashsale/setting' );
		$this->model_extension_pavflashsale_setting->install();
	}

	/**
	 * uninstall actions
	 * remove user permission
	 */
	public function uninstall() {
		$this->load->model( 'extension/pavflashsale/setting' );
		$this->model_extension_pavflashsale_setting->uninstall();
	}

	/**
	 * @action index to render content of setting form
	 */
	public function index() {
		
		$this->load->language('extension/module/pavflashsale');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');
		$this->load->model('setting/setting');
		$this->load->model('tool/image');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {


			if( isset($this->request->post['post_action']) && $this->request->post['post_action'] == 'save_back' ){
				unset( $this->request->post['post_action'] );
				$redirect = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
			}else {
				$redirect = $this->url->link('extension/module/pavflashsale', 'user_token=' . $this->session->data['user_token'] , true);
			}

			$data['pavflashsale'] = $this->request->post;



			$this->model_setting_setting->editSetting( 'pavflashsale', $data );
	 
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
				'href' => $this->url->link('extension/module/pavflashsale', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/pavflashsale', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		$data['link_products_deal'] = HTTPS_CATALOG."index.php?route=extension/module/pavflashsale";
		$data['link_search_product_deal'] =  $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token']."&filter_query=extension/module/pavflashsale", true); 
		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/pavflashsale', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/pavflashsale', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['ranges']		 = array(
			'10' 	 => $this->language->get('text_10_days'),
			'15' 	 => $this->language->get('text_15_days'),
			'20' 	 => $this->language->get('text_20_days'),
			'30' 	 => $this->language->get('text_30_days'),
			'45' 	 => $this->language->get('text_45_days'),
			'1' 	 => $this->language->get('text_today_days'),
			'nowtof' => $this->language->get('text_now_to_future'),
			'date'   => $this->language->get('text_specified_date'),


		);

		if (isset($this->request->post['module_setting'])) {
			$data['module_setting'] = $this->request->post['module_setting'];
		} elseif (!empty($module_info)) {
			$data['module_setting'] = $module_info['module_setting'];
		} else {
			$data['module_setting'] = array();
		}

		$info = $this->model_setting_setting->getSetting( 'pavflashsale' );

		$data['seo'] = $data['setting'] = array();  

		if( $info ){
			$data['seo'] = $info['pavflashsale']['seo'];
			$data['setting'] = $info['pavflashsale']['setting'];
			$data['config'] = $info['pavflashsale']['config'];
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

		$this->response->setOutput($this->load->view('extension/module/pavflashsale', $data));
	}

	/**
	 * Check data validation
	 */
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/pavflashsale')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
}