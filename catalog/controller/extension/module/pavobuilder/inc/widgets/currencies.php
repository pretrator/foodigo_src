<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Currencies extends PA_Widgets {

	public $header = true;

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-file-text',
				'label'	=> $this->language->get( 'entry_currency_block' ),
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
							'type'	=> 'colorpicker',
							'name'	=> 'text_link_color',
							'label'	=> $this->language->get( 'entry_text_link_text' ),
							'default' => '',
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'icon_color',
							'label'	=> $this->language->get( 'entry_icon_color' ),
							'default' => '',
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'button_color',
							'label'	=> $this->language->get( 'entry_button_color' ),
							'default' => '',
						),

						array(
							'type'	=> 'select',
							'name'	=> 'cur_layout',
							'label'	=> $this->language->get( 'entry_layout_text' ),
							'default' => 'dropdown',
							'mask'	=> true,
							'options'	=> array(
								array(
									'value'	=> 'dropdown',
									'label'	=> 'Dropdown'
								),
								array(
									'value'	=> 'list',
									'label'	=> 'List'
								),
							)
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
				'custom'				=> array(
					'label'			=> $this->language->get( 'entry_custom_text' ),
					'fields'		=> array(
						array(
							'type'		=> 'text',
							'name'		=> 'hfont_size',
							'label'		=> $this->language->get( 'entry_font_size' ),
							'default'	=> '',
                            'css_attr'  => 'font-size',
							'suffix' 	=> 'px',
                            'selector'  => '.form-currency .btn-link span',
						),
						array(
							'type'		=> 'text',
							'name'		=> 'htext_transform',
							'label'		=> $this->language->get( 'entry_text_transform' ),
							'default'	=> '',
                            'css_attr'  => 'text-transform',
                            'selector'  => '.form-currency .btn-link span',
						)
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$settings['extra_class'] = isset($settings['extra_class']) ? $settings['extra_class'] : '';
	 	

	 	$settings['font'] = array (
	 		'text_link_color'	=> isset($settings['text_link_color']) ? $settings['text_link_color'] : '',
	 		'button_color'	=> isset($settings['button_color']) ? $settings['button_color'] : '',
	 		'icon_color'	=> isset($settings['icon_color']) ? $settings['icon_color'] : '',
	 	);

		$layout = !empty($settings['cur_layout']) ? $settings['cur_layout'] : 'dropdown';
		return $this->index( $settings['extra_class'], $settings['font'], $layout ); 
	}

	public function index($extra_class, $font, $layout) {

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
		$data['text_link_color'] = isset($font['text_link_color']) ? $font['text_link_color'] : '';
		$data['button_color']  	 = isset($font['button_color']) ? $font['button_color'] : '';
		$data['icon_color']  	 = isset($font['icon_color']) ? $font['icon_color'] : '';


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