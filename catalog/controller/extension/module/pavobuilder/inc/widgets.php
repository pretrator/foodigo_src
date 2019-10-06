<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widgets extends Controller {

	public $header = false;

	public static $instance = null;
	private $widgets = array();

	public static function instance( $registry ) {
		if ( ! self::$instance ) {
			self::$instance = new self( $registry );
		}
		return self::$instance;
	}

	public function __construct( $registry ) {
		parent::__construct( $registry );
	}

	/**
	 * load all widgets
	 */
	public function registerWidgets() {
		if ( $this->widgets ) return $this->widgets;
		$files = glob( dirname( DIR_SYSTEM ) . '/catalog/controller/extension/module/pavobuilder/inc/widgets/*.php' );
		foreach ( $files as $file ) {
			$file_name = basename( $file, '.php' );
			if ( strpos( $file_name, 'pa_' ) == 0 && is_file($file) ) {
				require_once $file;
				$name = implode( '_', array_map( 'ucfirst', explode( '_', $file_name ) ) );
				$class_name = 'PA_Widget_' . $name;
				if( class_exists($class_name) ){
					$widget = str_replace( 'widget_', '', strtolower( $class_name ) );
					$this->widgets[ $widget ] = new $class_name( $this->registry );
				}
			}
		}
		return $this;
	}

	/**
	 * get ajax uri
	 */
	public function getAjaxURI( $params = array() ) {
		$params = array_merge( array(
				'user_token'	=> isset($this->session->data['user_token']) ? $this->session->data['user_token'] : ''
			), $params);
		$query = http_build_query( $params );
		return $this->url->link( 'extension/module/pavobuilder/ajaxProcess', $query, true );
	}

	/**
	 * get widgets
	 *
	 * @return array
	 */
	public function getWidgets() {
		if ( ! $this->widgets ) {
			$this->registerWidgets();
		}

		$widgets = array();
		$this->load->language( 'extension/module/pavobuilder' );
		foreach ( $this->widgets as $key => $widget ) {
			$widgets[$key] = array(
					'type' 		=> 'widget',
					'widget' 	=> str_replace( 'widget_', '', strtolower( get_class( $widget ) ) ),
					'group'		=> strip_tags( $this->language->get( 'heading_title' ) ),
					'group_slug'=> 'pa-widgets-list',
					'icon'		=> '',
					'label'		=> '',
					'header'	=> $widget->header
				);
		}
		return $widgets;
	}

	/**
	 * get width
	 *
	 * @param $widget
	 */
	public function getWidget( $widget = '' ) {
		if ( ! $this->widgets ) {
			$this->registerWidgets();
		}

		return ! empty( $this->widgets[$widget] ) ? $this->widgets[$widget] : null;
	}

	/**
	 * render widget
	 */
	public function renderWidget( $widget_code = '', $settings = array(), $content = '', $data = array() ) {
		$language_id = $this->config->get('config_language_id');
		$this->load->model( 'localisation/language' );
		$language = $this->model_localisation_language->getLanguage( $language_id );
		$code = ! empty( $language['code'] ) ? $language['code'] : $this->config->get('config_language');

		$widget = $this->getWidget( $widget_code );
		foreach ( $settings as $key => $setting ) {
			if ( $key === $code ) {
				foreach ( $setting as $name => $value ) {
					$settings[$name] = $value;
				}
			}
		}
		$settings = array_merge( array(
				'products_list_layout' => $this->getProductsListLayout(),
				'product_grid_layout' => $this->getProductGridLayout(),
				'product_grid_style' => $this->getProductGridStyle()
			), $settings );
		return $widget ? $widget->render( $settings, $content, $data ) : '';
	}

	/**
	 * products list layout
	 */
	public function getProductsListLayout() {
		$products_list_layout = '';
		$file = DIR_APPLICATION . 'view/theme/'. $this->config->get('config_theme').'/template/extension/module/pavobuilder/loop/products-list.twig';
	  	if( file_exists( $file ) ){
			$products_list_layout = $this->config->get('config_theme').'/template/extension/module/pavobuilder/loop/products-list.twig';
	  	}

	  	if ( !$products_list_layout ) {
	  		$products_list_layout = 'default/template/extension/module/pavobuilder/loop/products-list.twig';
	  	}
	  	return $products_list_layout;
	}

	/**
	 * get product grid layout
	 */
	public function getProductGridLayout() {
		$product_grid_layout = '';
		$file = DIR_APPLICATION . 'view/theme/'. $this->config->get('config_theme').'/template/product/layout/'. $this->config->get( 'pavothemer_product_grid_layout' ) .'.twig';
	  	if( file_exists( $file ) ){
			$product_grid_layout = $this->config->get('config_theme').'/template/product/layout/'. $this->config->get( 'pavothemer_product_grid_layout' ) .'.twig';
	  	}

	  	if ( !$product_grid_layout ) {
	  		$product_grid_layout = 'default/template/extension/module/pavobuilder/loop/product.twig';
	  	}
	  	return $product_grid_layout;
	}

	/**
	 * get product grid layout
	 */
	public function getProductGridStyle() {
		return $this->config->get('pavothemer_product_grid_style');
	}

	public function getImageLink( $image, $dimension ) {

		if( defined('IMAGE_URL') ){  
            $server = IMAGE_URL;
        } else {
            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }

		$this->load->model( 'tool/image' );
		$sizes = explode( 'x', $dimension );
		if (  count($sizes) == 2 && ! empty( $sizes[0] ) && ! empty( $sizes[1] ) && !empty($dimension) ) {
			return $this->model_tool_image->resize( $image, $sizes[0], $sizes[1] );
		}
		
		return $server . $image;
	}

	/**
	 * get all layouts
	 */
	public function getLayoutsOptions( $type = 'pavobuilder' ) {
		$class = get_class($this);
		$tpl_dir = str_replace('PA_Widget_', '', $class);
		$tpl_dir = 'pa_'.implode('_', array_map('strtolower', explode('_', $tpl_dir)));

		$defaultPath = $themePath = '';
		$theme = $this->config->get( 'config_theme' );
		$layouts = array();

		if ( defined('DIR_CATALOG') ) {
			$defaultPath = DIR_CATALOG.'view/theme/default/template/extension/module/'.$type.'/';
			$themePath = DIR_CATALOG.'view/theme/'.$theme.'/template/extension/module/'.$type.'/';
		} else if ( defined('DIR_APPLICATION') ) {
			$defaultPath = DIR_TEMPLATE.'default/template/extension/module/'.$type.'/';
			$themePath = DIR_TEMPLATE.$theme.'/template/extension/module/'.$type.'/';
		}

		$files = glob($defaultPath.$tpl_dir.'/*.twig');
		foreach ( $files as $file ) {
			$name = basename($file, '.twig');
			$layouts[$name] = array(
					'value' => $name,
					'label' => $name
				);
		}

		$files = glob($themePath.$tpl_dir.'/*.twig');
		foreach ( $files as $file ) {
			$name = basename($file, '.twig');
			$layouts[$name] = array(
					'value' => $name,
					'label' => $name
				);
		}
		return $layouts;
	}

	/**
	 * get layout
	 */
	public function renderLayout( $layout = '', $type = 'pavobuilder' ) {
		$class = get_class($this);
		$tpl_dir = str_replace('PA_Widget_', '', $class);
		$tpl_dir = 'pa_'.implode('_', array_map('strtolower', explode('_', $tpl_dir)));

		return 'extension/module/'.$type.'/'.$tpl_dir.'/'.$layout;
	}

}