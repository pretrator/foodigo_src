<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Logo extends PA_Widgets {
	public $header =  true;
	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-file-text',
				'label'	=> $this->language->get( 'entry_logo_block' ),
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
							'name'	=> 'logo_type',
							'label'	=> $this->language->get( 'entry_logo_text' ),
							'default' => 'opencart',
							'mask'	=> true,
							'options'	=> array(
								array(
									'value'	=> 'opencart',
									'label'	=> $this->language->get( 'text_use_opencart_logo' )
								),
								array(
									'value'	=> 'custom',
									'label'	=> $this->language->get( 'text_use_custom_logo' )
								)
							)
						),
						array(
							'type'	=> 'image',
							'name'	=> 'src',
							'label'	=> $this->language->get( 'entry_image_text' )
						),
						array(
							'type'	=> 'text',
							'name'	=> 'image_size',
							'label'	=> $this->language->get( 'entry_image_size_text' ),
							'desc'	=> $this->language->get( 'entry_image_size_desc' ),
							'default'		=> 'full',
							'placeholder'	=> '200x400'
						)

					)
				),
				'style'				=> array(
					'label'			=> $this->language->get( 'entry_styles_text' ),
					'fields'		=> array(
                        array(
                            'type'  => 'colorpicker',
                            'name'  => 'background_color',
                            'label' => $this->language->get( 'entry_background_color_text' ),
                            'css_attr'  => 'background-color'
                        ),
						array(
							'type'	=> 'layout-onion',
							'name'	=> 'layout_onion',
							'label'	=> 'entry_box_text'
						)
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {

	 	$settings = array_merge(  array(
	 		'logo_type' => 'opencart'
	 	) , $settings );

 		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if( $settings['logo_type'] == 'custom' ){
			if( file_exists(DIR_IMAGE.$settings['src']) ) {
				$settings['logo'] = $this->getImageLink( $settings['src'], $settings['image_size'] );
			} else {
				$settings['logo'] = $server.'image/catalog/opencart-logo.png';
			}

		} else {
			if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
				$settings['logo'] = $server . 'image/' . $this->config->get('config_logo');
			} else {
				$settings['logo'] = $server.'image/catalog/opencart-logo.png';
			}
		}

		$settings['name'] = $this->config->get('config_name');
		$settings['home'] = $this->url->link('common/home');

		return $this->load->view( 'extension/module/pavoheader/logo', array( 'settings' => $settings ) );
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