<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Block_Links extends PA_Widgets {

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-link',
				'label'	=> $this->language->get( 'entry_opencart_block_links' )
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
							'type'	=> 'select',
							'name'	=> 'title',
							'label'	=> $this->language->get( 'entry_title' ),
							'default' => 'true',
							'options'	=> array(
								array(
									'value'	=> 'true',
									'label'	=> 'Enabled'
								),
								array(
									'value'	=> 'false',
									'label'	=> 'Disabled'
								)
							),
							'mask'	=> true
						),
						array(
							'type'	=> 'select',
							'name'	=> 'footer_title',
							'label'	=> $this->language->get( 'entry_footer' ),
							'default' => 'service',
							'options'	=> array(
								array(
									'value'	=> 'service',
									'label'	=> 'Customer Service'
								),
								array(
									'value'	=> 'information',
									'label'	=> 'Information'
								),
								array(
									'value'	=> 'my_account',
									'label'	=> 'My Account'
								),
								array(
									'value'	=> 'extras',
									'label'	=> 'Extras'
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
						)
					)
				),
				'custom'			=> array(
					'label'			=> $this->language->get( 'entry_custom_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'text_link_color',
							'label'	=> $this->language->get( 'entry_text_link_text' ),
							'selector' => 'a',
							'css_attr'	=> 'color'
						),
						array(
							'type'	=> 'text',
							'name'	=> 'text_link_font_size',
							'label'	=> $this->language->get( 'entry_text_link_font_size' ),
							'selector' => 'a',
							'css_attr'	=> 'font-size',
							'suffix' 	=> 'px'
						),
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ){
		$this->load->language( 'extension/module/pavothemer' );
		$this->load->model('catalog/information');

		$settings['informations'] = array();
		$information = $this->model_catalog_information->getInformations();
		foreach ( $information as $result) {
			if ($result['bottom']) {
				$settings['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}
		$settings['contact'] 	= $this->url->link('information/contact');
		$settings['return'] 	= $this->url->link('account/return/add', '', true);
		$settings['sitemap'] 	= $this->url->link('information/sitemap');
		$settings['tracking'] 	= $this->url->link('information/tracking');
		$settings['manufacturer'] = $this->url->link('product/manufacturer');
		$settings['voucher'] 	= $this->url->link('account/voucher', '', true);
		$settings['affiliate']  = $this->url->link('affiliate/login', '', true);
		$settings['special'] 	= $this->url->link('product/special');
		$settings['account'] 	= $this->url->link('account/account', '', true);
		$settings['order'] 		= $this->url->link('account/order', '', true);
		$settings['wishlist'] 	= $this->url->link('account/wishlist', '', true);
		$settings['newsletter'] = $this->url->link('account/newsletter', '', true);
		$settings['powered'] 	= sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		$settings['scripts'] = $this->document->getScripts('footer');

		$args = 'extension/module/pavobuilder/pa_block_links/pa_block_links';
		return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
	}

}