<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Toplist extends PA_Widgets {
	public $header =  true;
	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-file-text',
				'label'	=> $this->language->get( 'entry_toplist_block' ),
			),
			'tabs'	=> array(
				'general'		=> array(
					'label'		=> $this->language->get( 'entry_general_text' ),
					'fields'	=> array(
						array(
							'type'	=> 'hidden',
							'name'	=> 'uniqid_id',
							'label'	=> $this->language->get( 'entry_row_id_text' ),
							'desc'	=> $this->language->get( 'entry_column_desc_text' )
						),
						array(
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' )
						),
						array(
							'type'		=> 'select',
							'name'		=> 'menu_type',
							'label'		=> $this->language->get( 'entry_menu_type' ),
							'default' 	=> 'horizontal-menu',
							'options'	=> array(
								array(
									'value'	=> 'horizontal-menu',
									'label'	=> 'Horizontal Style'
								),
								array(
									'value'	=> 'vertical-dropdown',
									'label'	=> 'Vertical Dropdown'
								),
							)
						),
						array(
							'type'	=> 'select',
							'name'	=> 'option',
							'label'	=> $this->language->get( 'entry_option' ),
							'default' => '',
							'multiple'	=> true,
							'options'	=> array(
								array(
									'value'	=> 'language',
									'label'	=> 'Language'
								),
								array(
									'value'	=> 'currency',
									'label'	=> 'Currency'
								),
								array(
									'value'	=> 'telephone',
									'label'	=> 'Telephone'
								),
								array(
									'value'	=> 'account',
									'label'	=> 'Account'
								),
								array(
									'value'	=> 'wishlist',
									'label'	=> 'Wishlist'
								),
								array(
									'value'	=> 'shopping_cart',
									'label'	=> 'Shopping Cart'
								),
								array(
									'value'	=> 'checkout',
									'label'	=> 'Checkout'
								),
							)
						),

						array(
							'type'	=> 'select',
							'name'	=> 'layout_style',
							'label'	=> $this->language->get( 'entry_layout_style' ),
							'default' => '',
							'options'	=> array(
								array(
									'value'	=> 'default',
									'label'	=> 'Default'
								),
								array(
									'value'	=> 'icon',
									'label'	=> 'Icon'
								),
							)
						),
						array(
							'type'		=> 'select',
							'name'		=> 'wishlist',
							'label'		=> $this->language->get( 'entry_wishlist' ),
							'default' 	=> '',
							'options'	=> array(
								array(
									'value'	=> 'wishlist1',
									'label'	=> 'Wishlist style 1'
								),
							)
						),
						array(
							'type'		=> 'iconpicker',
							'name'		=> 'icon',
							'label'		=> $this->language->get( 'icon_vertical' ),
							'default'	=> ''
						),
						array(
							'type'	=> 'text',
							'name'	=> 'title_vertical',
							'label'	=> $this->language->get( 'entry_title_vertical' ),
							'default' => '',
							'language'	=> true
						),
					)
				),
				'style'				=> array(
					'label'			=> $this->language->get( 'entry_styles_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'layout-onion',
							'name'	=> 'layout_onion',
							'label'	=> 'entry_box_text'
						)
					)
				),
				'custom'			=> array(
					'label'			=> $this->language->get( 'entry_custom_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'number',
							'name'	=> 'link_margin_top',
							'label'	=> $this->language->get( 'entry_link_margin_top_text' ),
							'selector'	=> '.pa-column-inner a',
							'css_attr'	=> 'margin-top',
							'min'		=> 0,
							'step'		=> 1,
							'suffix'	=> 'px'
						),
						array(
							'type'	=> 'number',
							'name'	=> 'link_margin_right',
							'label'	=> $this->language->get( 'entry_link_margin_right_text' ),
							'selector'	=> '.pa-column-inner a',
							'css_attr'	=> 'margin-right',
							'min'		=> 0,
							'step'		=> 1,
							'suffix'	=> 'px'
						),
						array(
							'type'	=> 'number',
							'name'	=> 'link_margin_bottom',
							'label'	=> $this->language->get( 'entry_link_margin_bottom_text' ),
							'selector'	=> '.pa-column-inner a',
							'css_attr'	=> 'margin-bottom',
							'min'		=> 0,
							'step'		=> 1,
							'suffix'	=> 'px'
						),
						array(
							'type'	=> 'number',
							'name'	=> 'link_margin_left',
							'label'	=> $this->language->get( 'entry_link_margin_left_text' ),
							'selector'	=> '.pa-column-inner a',
							'css_attr'	=> 'margin-left',
							'min'		=> 0,
							'step'		=> 1,
							'suffix'	=> 'px'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'text_color',
							'label'	=> $this->language->get( 'entry_color_text' ),
							'selector' => 'span',
							'css_attr'	=> 'color'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'icon_color',
							'label'	=> $this->language->get( 'entry_icon_color' ),
							'selector' => '.fa, .icon, .caret, strong',
							'css_attr'	=> 'color'
						),
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$extra_class = isset($settings['extra_class']) ? $settings['extra_class']: '';
		$settings['currency'] = $this->currency( $extra_class, 'list' );
		$settings['languages'] = $this->language( $extra_class, 'list' );
		$this->load->language('common/header'); 
	 	$data = array_merge(  array(
	 		'logo_type' => 'opencart'
	 	) , $settings );
 		
 		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');
			if (isset($settings['wishlist']) && $settings['wishlist'] == "wishlist1") {
				$data['text_wishlist'] = sprintf($this->model_account_wishlist->getTotalWishlist());
			} else {
				$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
			}
			
		} else {
			if (isset($settings['wishlist']) && $settings['wishlist'] == "wishlist1") {
				$data['text_wishlist'] = sprintf( (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
			} else {
				$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
			}
			
		}
		
		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));

 		$data['wishlist'] 	= $this->url->link('account/wishlist', '', true);
		$data['logged'] 	= $this->customer->isLogged();
		$data['account'] 	= $this->url->link('account/account', '', true);
		$data['register'] 	= $this->url->link('account/register', '', true);
		$data['login'] 		= $this->url->link('account/login', '', true);
		$data['order'] 		= $this->url->link('account/order', '', true);
		$data['transaction'] 	= $this->url->link('account/transaction', '', true);
		$data['download'] 	= $this->url->link('account/download', '', true);
		$data['logout'] 	= $this->url->link('account/logout', '', true);
		$data['shopping_cart'] 	= $this->url->link('checkout/cart');
		$data['checkout'] 	= $this->url->link('checkout/checkout', '', true);
		$data['contact'] 	= $this->url->link('information/contact');
		$data['telephone']	= $this->config->get('config_telephone');
	
		return $this->load->view( 'extension/module/pavoheader/toplist', array( 'settings' => $data ) );
	}

	private function language($extra_class, $layout) {

		$this->load->language('common/language');
		$data['action'] = $this->url->link('common/language/language', '', $this->request->server['HTTPS']);
		$data['code'] = $this->session->data['language'];
		$this->load->model('localisation/language');
		$data['languages'] = array();
		$results = $this->model_localisation_language->getLanguages();
		foreach ($results as $result) {
			if ($result['status']) {
				$data['languages'][] = array(
					'name' => $result['name'],
					'code' => $result['code']
				);
			}
		}
		if (!isset($this->request->get['route'])) {
			$data['redirect'] = $this->url->link('common/home');
		} else {
			$url_data = $this->request->get;
			if ( isset($url_data['profile']) ) {
				unset($url_data['profile']);
			}
			unset($url_data['_route_']);
			$route = $url_data['route'];
			unset($url_data['route']);
			$url = '';
			if ($url_data) {
				$url = '&' . urldecode(http_build_query($url_data, '', '&'));
			}
			$data['redirect'] = $this->url->link($route, $url, $this->request->server['HTTPS']);
		}

		$data['extra_class'] = isset($extra_class) ? $extra_class : '';
		$data['layout']      = isset($layout) ? $layout : '';

		return $this->load->view('common/language', $data);
	}

	private function currency($extra_class, $layout) {
		$this->load->language('common/currency');
		$data['action'] = $this->url->link('common/currency/currency', '', $this->request->server['HTTPS']);
		$data['code'] = $this->session->data['currency'];
		$this->load->model('localisation/currency');
		$data['currencies'] = array();
		$results = $this->model_localisation_currency->getCurrencies();
		foreach ($results as $result) {
			if ($result['status']) {
				$data['currencies'][] = array(
					'title'        => $result['title'],
					'code'         => $result['code'],
					'symbol_left'  => $result['symbol_left'],
					'symbol_right' => $result['symbol_right']
				);
			}
		}
		if (!isset($this->request->get['route'])) {
			$data['redirect'] = $this->url->link('common/home');
		} else {
			$url_data = $this->request->get;
			if ( isset($url_data['profile']) ) {
				unset($url_data['profile']);
			}
			unset($url_data['_route_']);
			$route = $url_data['route'];
			unset($url_data['route']);
			$url = '';
			if ($url_data) {
				$url = '&' . urldecode(http_build_query($url_data, '', '&'));
			}
			$data['redirect'] = $this->url->link($route, $url, $this->request->server['HTTPS']);
		}
		$data['extra_class']     = isset($extra_class) ? $extra_class : '';
		$data['layout']          = isset($layout) ? $layout : '';

		return $this->load->view('common/currency', $data);
	}

	/**
	 * s fields
	 */
	public function validate( $settings = array() ) {
		$language_id = $this->config->get('config_language_id');
		$this->load->model( 'localisation/language' );
		$language = $this->model_localisation_language->getLanguage( $language_id );
		$code = ! empty( $language['code'] ) ? $language['code'] : $this->config->get('config_language');

		if ( ! empty( $settings[$code] ) && ! empty( $settings[$code]['content'] ) ) {
			$settings[$code]['content'] = html_entity_decode( $settings[$code]['content'], ENT_QUOTES, 'UTF-8' );
		}
		return $settings;
	}

}