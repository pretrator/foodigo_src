<?php
/**
 * @package Pav Megamenu module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2012 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class ControllerExtensionModulePavmegamenu extends Controller {

	public $data;
	static $MEGAMENU;
	public function index($setting) {
		static $module = 0;
			
		$this->load->model('catalog/product'); 
		$this->load->model('tool/image');
		$this->load->model( 'extension/menu/megamenu' );
		
		$this->language->load('extension/module/pavmegamenu');

		
		$config_theme = $this->config->get('config_theme');
		$this->data['button_cart'] = $this->language->get('button_cart');
		if (file_exists('catalog/view/theme/' . $config_theme . '/stylesheet/pavmegamenu/style.css')) {
			$this->document->addStyle('catalog/view/theme/' . $config_theme . '/stylesheet/pavmegamenu/style.css');
		} else {
			// $this->document->addStyle('catalog/view/theme/default/stylesheet/pavmegamenu/style.css');
		}
		
		$params = $this->config->get( 'params' );
	 	
		$this->load->model('setting/setting');
		$params = $this->model_setting_setting->getSetting( 'pavmegamenu_params' );

		 
		if( isset($params['pavmegamenu_params']) && !empty($params['pavmegamenu_params']) ){
	 		$params = json_decode( $params['pavmegamenu_params'] );
	 	}
		
		//get store
		$store_id = $this->config->get('config_store_id');
		$this->data['store_id'] = $store_id;

		$parent = '1';
		if( !self::$MEGAMENU ){ 
			self::$MEGAMENU = $this->model_extension_menu_megamenu->getTree( $parent, true, $params, $store_id);
		} 
		$this->data['treemenu'] = self::$MEGAMENU;
 

		$template = 'extension/module/pavmegamenu';
		return $this->load->view($template, $this->data);

	}
}
?>