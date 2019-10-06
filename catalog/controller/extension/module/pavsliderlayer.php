<?php
/**
 * @package Pav Sliders Layers module for Opencart 1.5.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class ControllerExtensionModulePavsliderlayer extends Controller {

	protected $mdata = array();

	public function index( $setting ) {

		static $module = 0;

		$this->load->model('extension/pavsliderlayer/slider');
		$this->load->model('tool/image');

		$model = $this->model_extension_pavsliderlayer_slider;
		$group_id = isset($setting['group_id'])?(int)$setting['group_id']:0;

		$this->load->model('setting/setting');
		$storeConfig = $this->model_setting_setting->getSetting('theme_default', $this->store_id );
		$theme_directory = $this->config->get('config_theme');

		if( file_exists('catalog/view/theme/'.$theme_directory.'/stylesheet/sliderlayer/css/typo.css') ) {
			$this->document->addStyle('catalog/view/theme/'.$theme_directory.'/stylesheet/sliderlayer/css/typo.css');
		}else{
			$this->document->addStyle('catalog/view/theme/default/stylesheet/sliderlayer/css/typo.css');
		}
//		$this->document->addScript('catalog/view/javascript/layerslider/jquery.themepunch.plugins.min.js');
		$this->document->addScript('catalog/view/javascript/layerslider/jquery.themepunch.revolution.min.js');

 	 	$url = $this->config->get('config_secure') ? $this->config->get('config_ssl') : $this->config->get('config_url');
 	 	$image_url = $url.'/image/';
 	 	if ( defined('IMAGE_URL') ) {
 	 		$image_url = IMAGE_URL;
 	 	}

 		$this->mdata['url']    = $url;
 		$this->mdata['randID'] = rand( 20,   rand() );

 		$sliderGroup = $model->getSliderGroupById( $group_id );

 		$languageID = $this->config->get('config_language_id');

		$sliders = $model->getSlidersByGroupId($group_id, $languageID);
		if(empty($sliders)){
			$sliders = $model->getSlidersByGroupId($group_id, 1);
		}

		$sliderGroup['params']['hide_navigator_after'] = isset( $sliderGroup['params']['show_navigator'] ) ? 0 : $sliderGroup['params']['hide_navigator_after'];
		$sliderGroup['params']['class'] = ! empty( $sliderGroup['params']['fullwidth']) ? $sliderGroup['params']['fullwidth'] : 'boxed';
		$sliderGroup['params']['fullwidth'] = $sliderGroup['params']['class'] == 'boxed' ? 'off' : 'on';
		$this->mdata['sliderParams'] = $sliderGroup['params'];

		if( isset($sliderGroup['params']['fullwidth']) && (!empty($sliderGroup['params']['fullwidth']) || $sliderGroup['params']['fullwidth'] == 'boxed') ){
			$sliderGroup['params']['image_cropping'] = false;
		}

		foreach( $sliders as $key=> $slider ) {
			$slider["layers"] = array();
			$slider['params'] = @unserialize( $slider["params"] );
			$slider_layersparams = @unserialize( $slider["layersparams"] );

			if ( $slider_layersparams ) {
				$base = '';
				if ( defined('IMAGE_URL') ) {
                	$base = str_replace( HTTP_SERVER, '', IMAGE_URL );
				}
                $base = str_replace( HTTPS_SERVER, '', $base );
				foreach ($slider_layersparams->layers as $layer) {
					$slider['layers_params'][] = array(
							'layer_video_type' 	 => $layer['layer_video_type'],
							'layer_video_id' 	 => $layer['layer_video_id'],
							'layer_video_height' => $layer['layer_video_height'],
							'layer_video_width'  => $layer['layer_video_width'],
							'layer_video_thumb'  => $layer['layer_video_thumb'],
							'layer_id' 			 => $layer['layer_id'],
							'layer_content' 	 => (defined( 'IMAGE_URL' ) ? $base : HTTPS_SERVER.'image/') . $layer['layer_content'],
							'layer_type' 		 => $layer['layer_type'],
							'layer_class' 		 => $layer['layer_class'],
							'layer_caption' 	 => html_entity_decode( str_replace( '_ASM_', '&', $layer['layer_caption']) , ENT_QUOTES, 'UTF-8'),
							'layer_animation' 	 => $layer['layer_animation'],
							'layer_easing' 		 => $layer['layer_easing'],
							'layer_speed' 		 => $layer['layer_speed'],
							'layer_top' 		 => $layer['layer_top'],
							'layer_left' 		 => $layer['layer_left'],
							'layer_endtime' 	 => $layer['layer_endtime'],
							'layer_endspeed' 	 => $layer['layer_endspeed'],
							'layer_endanimation' => $layer['layer_endanimation'],
							'layer_endeasing' 	 => $layer['layer_endeasing'],
							'time_start' 		 => $layer['time_start']
						);
				}
			}
// echo '<pre>'; print_r($slider['layers_params']); die();
			if( $sliderGroup['params']['image_cropping']) {
				 $slider['main_image'] = $model->resize($slider['image'], $sliderGroup['params']['width'],
				 								$sliderGroup['params']['height'],'a');
			}else {
				 $slider['main_image'] = $image_url.$slider['image'];
			}
			if( $sliderGroup['params']['image_cropping']) {
				if( $slider['params']['slider_thumbnail'] ) {
					$slider['thumbnail'] = $model->resize( $slider['params']['slider_thumbnail'], $sliderGroup['params']['thumbnail_width'],
					 								$sliderGroup['params']['thumbnail_height'],'a');
				}else {
					$slider['thumbnail'] = $model->resize($slider['image'], $sliderGroup['params']['thumbnail_width'],
					 								$sliderGroup['params']['thumbnail_height'],'a');
				}
			}else {
				if( $slider['params']['slider_thumbnail'] ) {
					 $slider['thumbnail'] = $image_url.$slider['params']['slider_thumbnail'];
				}else {
					 $slider['thumbnail'] = $image_url.$slider['image'];
				}

			}
			$sliders[$key] = $slider;
		}

		$this->mdata['sliders'] = $sliders;
		$this->mdata['module'] = $module++;

		return $this->load->view('extension/module/pavsliderlayer', $this->mdata);
	}
}
?>