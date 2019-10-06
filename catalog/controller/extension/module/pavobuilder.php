<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */
require_once 'pavobuilder/pavobuilder.php';

class ControllerExtensionModulePavoBuilder extends Controller {

	public function __construct( $registry ) {
		parent::__construct( $registry );
		$this->pavobuilder = PavoBuilder::instance( $registry );
	}

	/**
	 * ajax process
	 */
	public function ajaxProcess()
	{
		$widget = $this->request->request['widget'];
		$action = $this->request->request['action'];
		$results = array();

		try {
			if ( !$widget ) {
				throw new Exception($this->language->get('unknow_widget'));
			}
			$widget = $this->pavobuilder->widgets->getWidget($widget);
			if ( !$widget ) {
				throw new Exception($this->language->get('Widget not found!'));
			}

			$method = 'ajaxProcess';
			if ( method_exists($widget, 'ajaxProcess'.ucfirst($action)) ) {
				$method = 'ajaxProcess' . ucfirst($action);
				$results = $widget->$method();
			} elseif( method_exists($widget, 'ajaxProcess') ) {
				$results = $widget->ajaxProcess();
			}
		} catch (Exception $e) {
			$results['status'] = false;
			$results['message'] = $e->getMessage();
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $results ) );
	}

	public function loadOtherMedia(){

	}

	/**
	 * render layout
	 */
	public function index( $setting ) {
		$this->load->model( 'extension/module/pavobuilder' );
		$this->load->language( 'extension/module/pavobuilder' );
		$uniqid_id = isset( $setting['uniqid_id'] ) ? $setting['uniqid_id'] : md5( uniqid() );
		$builder = ! empty( $setting['content'] ) ? $setting['content'] : array();
		$builder = $this->model_extension_module_pavobuilder->getBuilderData( $uniqid_id, $builder );
		// 59fc1b3635228

		$this->document->addScript( 'catalog/view/javascript/pavobuilder/dist/pavobuilder.min.js' );
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');

		// owlcarousel
		$this->document->addScript('catalog/view/javascript/owl-carousel/owl.carousel.min.js');
		// $this->document->addScript('catalog/view/javascript/owl-carousel/owl2rows.js');
		$this->document->addStyle('catalog/view/javascript/owl-carousel/css/owl.carousel.min.css');
		$this->document->addStyle('catalog/view/javascript/owl-carousel/css/owl.theme.default.css');

		$theme = $this->config->get( 'config_theme' );
		$style = DIR_TEMPLATE . $theme . '/stylesheet/pavobuilder.css';

		if( file_exists($style) ){
			$this->document->addStyle( 'catalog/view/theme/'.$theme.'/stylesheet/pavobuilder.css' );
		} else {
			$this->document->addStyle( 'catalog/view/javascript/pavobuilder/dist/pavobuilder.min.css' );
		}

		if( ! is_dir( DIR_APPLICATION . 'view/theme/default/stylesheet/pavobuilder/' ) ) {
			@mkdir( DIR_APPLICATION . 'view/theme/default/stylesheet/pavobuilder/', 0755, true );
		}

		// $uniqid_id = ! empty( $setting['uniqid_id'] ) ? $setting['uniqid_id'] : '';
		$file = DIR_APPLICATION . 'view/theme/default/stylesheet/pavobuilder/' . $uniqid_id . '.css';
		$direction = $this->language->get('direction');
		if ( $direction === 'rtl' ) {
			$file = DIR_APPLICATION . 'view/theme/default/stylesheet/pavobuilder/' . $uniqid_id . '-rtl.css';
		}

		if ( ! file_exists( $file ) ) {
			$css_processer = new PA_Css( $this->registry );
			$css_processer->save( $uniqid_id, $builder );
		}

		$this->document->addStyle( 'catalog/view/theme/default/stylesheet/pavobuilder/' . $uniqid_id . ( $direction === 'rtl' ? '-rtl' : '' ) . '.css' );

		ob_start();
		if ( ! empty( $builder ) ) {
			foreach ( $builder as $k => $row ) {
				echo $this->renderElement( $row );
			}
		}

		return ob_get_clean();
	}

	/**
	 * render element
	 */
	public function renderElement( $data = array(), $content = '' ) {
		$settings = ! empty( $data['settings'] ) ? $data['settings'] : array();
		if ( ! empty( $settings['background_video'] ) ) {
			$url = $settings['background_video'];
			// validate youtube url
			preg_match( '/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', $url, $match );
			$video_id = ! empty( $match[2] ) ? $match[2] : false;
			$settings['background_video'] = false;
			if ( $video_id ) {
				$query = array(
					'playlist'		=> $video_id,
					'enablejsapi' 	=> 1,
					'iv_load_policy'=> 3,
					'disablekb'		=> 1,
					'autoplay'		=> 1,
					'controls'		=> 0,
					'showinfo'		=> 0,
					'rel'			=> 0,
					'loop'			=> 1,
					'mute'			=> 1,
					'wmode'			=> 'transparent'
				);
				$settings['background_video'] = 'https://youtube.com/embed/' . $video_id . '?' . http_build_query( $query );
			}
		}
		$this->load->model( 'setting/module' );
		$content = '';

		if ( ! empty( $data['row'] ) ) {
			return $this->renderElement( $data['row'] );
		}

		if ( ! empty( $data['columns'] ) || ! empty( $data['elements'] ) ) {
			ob_start();
			$subElements = ! empty( $data['columns'] ) ? $data['columns'] : ( ! empty( $data['elements'] ) ? $data['elements'] : array() );
			foreach ( $subElements as $element ) {
				$subs = ! empty( $data['columns'] ) ? $data['columns'] : ( ! empty( $data['elements'] ) ? $data['elements'] : array() );
				if ( ! empty( $element['element_type'] ) && $element['element_type'] === 'module' && ! empty( $element['moduleCode'] ) ) {
					$module_id = ! empty( $element['moduleId'] ) ? $element['moduleId'] : '';
					$moduleSettings = ! empty( $module_id ) ? $this->model_setting_module->getModule( $element['moduleId'] ) : array();
					ob_start();
					echo $this->load->controller( 'extension/module/' . $element['moduleCode'], $moduleSettings );
					echo $this->load->view( 'extension/module/pavobuilder/pa_element_wrapper', array( 'data' => $data, 'settings' => array( 'specifix_id' => 'pavo-module-id-' . $module_id, 'uniqid_id' => $module_id ), 'content' => ob_get_clean() ) );
				} else if ( $subs ) {
					echo $this->renderElement( $element, $subs );
				}
			}
			$content = ob_get_clean();
		}
		if ( isset( $data['widget'] ) && $data['widget'] == 'pa_column' && ! empty( $data['responsive'] ) ) {
			$settings['responsive'] = $data['responsive'];
		}

		if ( isset( $data['widget'] ) ) {
			if ( ! in_array( $data['widget'], array( 'pa_row', 'pa_column' ) ) ) {
				ob_start();
				echo $this->pavobuilder->widgets->renderWidget( $data['widget'], $settings, $content, $data );
				return $this->load->view( 'extension/module/pavobuilder/pa_element_wrapper', array( 'data' => $data, 'settings' => $settings, 'content' => ob_get_clean() ) );
			}
			return $this->pavobuilder->widgets->renderWidget( $data['widget'], $settings, $content, $data );
		}
	}

}