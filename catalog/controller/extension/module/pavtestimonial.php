<?php
/******************************************************
 * @package Pavo Store Locator Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

/**
 * @class ControllerExtensionModulePavtestimonial
 * @version 1.0
 */
class ControllerExtensionModulePavtestimonial extends Controller {

	private $data =  array();

	public function index( $setting ) {

		static $module = 0;

		
		$this->load->language('extension/module/themecontrol');

        $this->data['ourl']      = $this->url;
        $this->data['sconfig']   = $this->config;
		$this->data['config_theme'] = $this->config->get('theme_default_directory');

        $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
        $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
        
        $this->document->addScript( 'catalog/view/javascript/jquery/swiper/js/swiper.min.js' );


        if( isset($setting['load_css']) && $setting['load_css'] ){
        	$this->document->addStyle('catalog/view/theme/default/stylesheet/pavtestimonial.css');
        }
        

		//Load language variables
		$this->language->load('extension/module/pavtestimonial');

		$this->data['text_watch_video_testimonial'] = $this->language->get('text_watch_video_testimonial');
		$this->data['text_testimonial']             = $this->language->get('text_testimonial');
		$this->data['text_testimonial_title']       = $this->language->get('text_testimonial_title');

		$this->load->model('tool/image');


		
		$a = array('interval'			=> 8000,
					'auto_play'			=>0,
					'width' 			=> 300,
            		'height' 			=> 300,
					'navimg_height'  	=>97,
					'navimg_weight'  	=>177,
					'loop'				=> 1,
					'slides_view'		=> 1,
					'text_interval'	 	=>8000,
					'column_item'	 	=> '1',
					'page_items'	 	=> 2,
					'class'				=> ''	
		 );

		
		$setting = array_merge( $a, $setting );

		$this->data['testimonials'] 	= array();
		$this->data['setting'] 			= $setting;
		$this->data['auto_play_mode'] 	= isset($setting['auto_play']) && $setting['auto_play'] == 1 ? "true" : "false";
		$this->data['text_interval'] 	= isset($setting['text_interval']) ? (int)$setting['text_interval'] : 5000;
		$this->data['width'] 			= isset($setting['width']) ? (int)$setting['width'] : 200;
		$this->data['loop']				= isset($setting['loop']) && $setting['loop'] == 1 ? "true" : "false";
		$this->data['slides_view'] 		= isset($setting['slides_view']) ? (int)$setting['slides_view'] : 1;
		$this->data['height'] 			= isset($setting['height']) ? (int)$setting['height'] : 200;
		$this->data['cols'] 			= isset($setting['column_item']) ? (int)$setting['column_item']: 1;
		$this->data['row'] 				= isset($setting['page_items']) ? (int)$setting['page_items'] : 1;
		$this->data['class'] 			= isset($setting['class']) ? $setting['class'] : '';

		if( isset($setting['testimonial_item'])){
			foreach( $setting['testimonial_item'] as $testimonial ){
				$testimonial['thumb']			= $this->model_tool_image->resize($testimonial['image'], $setting['width'], $setting['height']);
				$title 							= isset( $testimonial['title'][$this->config->get('config_language_id')] ) ? $testimonial['title'][$this->config->get('config_language_id')]:"";
				$description 					= isset( $testimonial['description'][$this->config->get('config_language_id')] ) ? $testimonial['description'][$this->config->get('config_language_id')]:"";
				$profile 						= isset( $testimonial['profile'][$this->config->get('config_language_id')] ) ? $testimonial['profile'][$this->config->get('config_language_id')]:"";
			 	$testimonial['profile'] 		=  html_entity_decode( $profile, ENT_QUOTES, 'UTF-8');
				$testimonial['description'] 	=  html_entity_decode( $description, ENT_QUOTES, 'UTF-8');

				$this->data['testimonials'][] 	= $testimonial;
			}
		}

		$layout = isset($setting['layout']) ? $setting['layout'] : 'layout1';

		$this->data['module'] = $module++;
		return $this->load->view('extension/module/pavtestimonial/'.$layout, $this->data);
	}
}
?>
