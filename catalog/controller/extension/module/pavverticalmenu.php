<?php
/**
 * @package Pav Verticalmenu module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2012 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class ControllerExtensionModulePavverticalmenu extends Controller {

	public $data;
	static $VERTICAL_MEGAMENU;
	public function index($setting) {
		static $module = 0;
			
		$this->load->model('catalog/product'); 
		$this->load->model('tool/image');
		$this->load->model( 'extension/menu/verticalmenu' );
		
		$this->language->load('extension/module/pavverticalmenu');

		
		$config_theme = $this->config->get('config_theme');
		$this->data['button_cart'] = $this->language->get('button_cart');

		if( isset($setting['loadcss']) ){
			if (file_exists('catalog/view/theme/' . $config_theme . '/stylesheet/pavverticalmenu/style.css')) {
				$this->document->addStyle('catalog/view/theme/' . $config_theme . '/stylesheet/pavverticalmenu/style.css');
			} else {
				$this->document->addStyle('catalog/view/theme/default/stylesheet/pavverticalmenu/style.css');
			}
		}
		 
		$params = $this->config->get( 'params' );
	 	
		$this->load->model('setting/setting');
		$params = $this->model_setting_setting->getSetting( 'pavverticalmenu_params' );

		 
		if( isset($params['pavverticalmenu_params']) && !empty($params['pavverticalmenu_params']) ){
	 		$params = json_decode( $params['pavverticalmenu_params'] );
	 	}
		
		//get store
		$store_id = $this->config->get('config_store_id');
		$this->data['store_id'] = $store_id;

		$parent = '1';
		
		if( !self::$VERTICAL_MEGAMENU ){  
			self::$VERTICAL_MEGAMENU = $this->model_extension_menu_verticalmenu->getTree( $parent, true, $params, $store_id);
		} 
		$this->data['treemenu'] = self::$VERTICAL_MEGAMENU; 

		$template = 'extension/module/pavverticalmenu';
		return $this->load->view($template, $this->data);

	}
}
?>