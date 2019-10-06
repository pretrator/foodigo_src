<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_QuickLogin extends PA_Widgets {
	public $header =  true;
	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-file-text',
				'label'	=> $this->language->get( 'entry_quicklogin_block' ),
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
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' ),
							'mask'	=> true
						),
						array(
							'type'	=> 'select',
							'name'	=> 'style',
							'label'	=> $this->language->get( 'entry_style_text' ),
							'default' => '',
							'options'	=> array(
								array(
									'value'	=> '',
									'label'	=> 'Default'
								),
								array(
									'value'	=> 'hide',
									'label'	=> 'Only Icon'
								)
							),
							'mask'	=> true
						)
					)
				),
				'style'				=> array(
					'label'			=> $this->language->get( 'entry_styles_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'layout-onion',
							'name'	=> 'layout_onion',
							'label'	=> 'entry_box_text'
						),
					)
				),
				'custom'			=> array(
					'label'			=> $this->language->get( 'entry_custom_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'text_link_color',
							'label'	=> $this->language->get( 'entry_text_link_text' ),
							'selector' => 'span, a',
							'css_attr'	=> 'color'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'icon_color',
							'label'	=> $this->language->get( 'entry_icon_color' ),
							'selector' => '.fa-user, .caret, .icon',
							'css_attr'	=> 'color'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'button_color',
							'label'	=> $this->language->get( 'entry_button_color' ),
							'selector' => 'button, input',
							'css_attr'	=> 'color'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'text_color',
							'label'	=> $this->language->get( 'entry_color_text' ),
							'selector' => 'label',
							'css_attr'	=> 'color'
						),
					)
				),
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$this->load->language('common/header');
		$this->load->language('account/login');
		$this->load->language('extension/module/pavobuilder');

	 	$settings = array_merge(  array(
	 		'description' => '',
	 		'style'		  => ''
	 	) , $settings );

 		$settings['action'] = $this->url->link('account/login', '', true);
		$settings['register'] = $this->url->link('account/register', '', true);
		$settings['forgotten'] = $this->url->link('account/forgotten', '', true);
		$settings['login'] = $this->url->link('account/login', '', true);
 		
 		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$settings['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
		
		$settings['home'] = $this->url->link('common/home');
		$settings['wishlist'] = $this->url->link('account/wishlist', '', true);
		$settings['logged'] = $this->customer->isLogged();
		$settings['account'] = $this->url->link('account/account', '', true);
		$settings['register'] = $this->url->link('account/register', '', true);
		$settings['login'] = $this->url->link('account/login', '', true);
		$settings['order'] = $this->url->link('account/order', '', true);
		$settings['transaction'] = $this->url->link('account/transaction', '', true);
		$settings['download'] = $this->url->link('account/download', '', true);
		$settings['logout'] = $this->url->link('account/logout', '', true);
		$settings['shopping_cart'] = $this->url->link('checkout/cart');
		$settings['checkout'] = $this->url->link('checkout/checkout', '', true);
		$settings['contact'] = $this->url->link('information/contact');
		$settings['telephone'] = $this->config->get('config_telephone');


		return $this->load->view( 'extension/module/pavoheader/quicklogin', array( 'settings' => $settings ) );
	}

	/**
	 * s fields
	 */
	public function validate( $settings = array() ) {
		$language_id = $this->config->get('config_language_id');
		return $settings;
	}

}