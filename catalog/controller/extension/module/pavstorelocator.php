<?php
/******************************************************
 * @package Pavo Store Locator Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
/**
 * @class ControllerExtensionModulePavstorelocator
 * @version 1.0
 */
class ControllerExtensionModulePavstorelocator extends Controller {
	
	/**
	 * Load javavscript lib files
	 */
	public function loadScript( $google_map_api= "" ){
		if( $google_map_api ){

			$key = '//maps.googleapis.com/maps/api/js?sensor=false&libraries=places&key='. $google_map_api ;
			$this->document->addScript( $key );
		}

		$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
  		$this->document->addScript('catalog/view/javascript/jquery/pavquickview.js' );

		
		$this->document->addScript('catalog/view/javascript/jquery/infobox.js' );
		$this->document->addScript('catalog/view/javascript/jquery/markerclusterer.js' );
		$this->document->addScript('catalog/view/javascript/jquery/pavstorelocator.js' );

		$this->document->addScript('catalog/view/javascript/jquery/jquery.fitvids.js' );
		$this->document->addScript('catalog/view/javascript/jquery/perfect-scrollbar.jquery.min.js' );

		
		$this->document->addStyle('catalog/view/javascript/jquery/pavstorelocator.css' );

	}

	/**
	 * render content module
	 */
	public function index() {

		
 		$data = array() ;
 			$this->load->language('extension/module/pavstorelocator');

 		$this->load->model( 'extension/module/pavstorelocator' );	
		$this->load->model('catalog/information');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}
		$language_id = $this->config->get('config_language_id'); 
		$this->load->model('setting/setting');
		$info = $this->model_setting_setting->getSetting( 'pavstorelocator' );
		$information_info = array(
			'title' => $this->language->get('text_breadcrumb_title'),
			'description' => '',
			'meta_title' => '',
			'meta_description' => '',
			'meta_keyword' => '',
			'description'	=> ''
		);	
		$data['description'] = '';
		if( $info && isset($info['pavstorelocator']) && isset($info['pavstorelocator']['seo']) ){
			$information_info = $info['pavstorelocator']['seo'][$language_id]?  $info['pavstorelocator']['seo'][$language_id] : array();
		}

		if( $info && isset($info['pavstorelocator']) && isset($info['pavstorelocator']['setting']) ){
			$tmp = $info['pavstorelocator']['setting'][$language_id]?  $info['pavstorelocator']['setting'][$language_id] : array();
			$information_info = array_merge( $information_info,  $tmp );
		}	
		$api_key = '';
		if( $info && isset($info['pavstorelocator']['google_api_key']) ){
 			$api_key = $info['pavstorelocator']['google_api_key'];
		}	
		$this->loadScript( $api_key ); 

		$layout = 'default';
		if( $info && isset($info['pavstorelocator']) && isset($info['pavstorelocator']['template']) ){
 			$layout = $info['pavstorelocator']['template'];
		}	

		$locations = $this->model_extension_module_pavstorelocator->getLocations();

		
		if( isset($this->request->get['layout']) ){
			$layout = $this->request->get['layout'];
		}
		
		if ($locations) {
			$this->document->setTitle($information_info['meta_title']);
			$this->document->setDescription($information_info['meta_description']);
			$this->document->setKeywords($information_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $information_info['title'],
				'href' => $this->url->link('extension/module/pavstorelocator', 'information_id=' .  $information_id)
			);
			$data['title'] = '';
			$data['heading_title'] = html_entity_decode($information_info['title'], ENT_QUOTES, 'UTF-8');

			$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$locals = array(); 
			$json = array();
			$url = '';

			foreach( $locations as $id => $location ){

				$image = $this->model_tool_image->resize( $location['image'] , 600, 600 );
				$location['image'] = $image;
				$location['images'] = array();

				$imgs = (array) explode( "|", $location['gallery']);

				foreach( $imgs as $key => $img ){
					if( $img )
						$location['images'][] = HTTP_SERVER . "/image/" .$img;
				}

				$latitude = 0;
				$longitude = 0;
				if( $tmp = explode(",", $location['geocode']) ){ 
					$latitude = $tmp[0];
					$longitude = $tmp[1];
				}

				$prop 		   = new stdClass();
		        $prop->id 	   = $location['location_id'];
		        $prop->title   = $location['name'];
		        $prop->url 	   = $url;
		        $prop->lat 	   =  $latitude;
		        $prop->lng 	   =  $longitude;
		        $prop->address =  $location['address'];
		        $prop->phone   =  $location['telephone'];
		        $prop->email   =  $location['email'];
		        $prop->thumb   = $image;
		        $prop->icon    = HTTP_SERVER.'/image/catalog/marker-icon.png'; 
		        $prop->open    = $location['open'];
		        $json[] = $prop;

		        $locations[$id] = $location;
			}

			$data['locations'] = $locations;
			$data['store_url'] = HTTP_SERVER;
			$data['json_maps']	   = json_encode( $json );
			$this->response->setOutput( $this->load->view('extension/module/pavstorelocator/'.$layout, $data) );

		} else {

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'information_id=' . $information_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
?>