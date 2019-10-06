<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Text extends PA_Widgets {

	public $header = true;

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-file-text',
				'label'	=> $this->language->get( 'entry_text_block' ),
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
							'name'	=> 'layout',
							'label'	=> $this->language->get( 'entry_layout_text' ),
							'default' => 'pa_text',
							'options'	=> $this->getLayoutsOptions(),
							'none' 	=> false
						),
						array(
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' ),
							'mask'	=> true
						),
						array(
							'type'		=> 'editor',
							'name'		=> 'content',
							'label'		=> $this->language->get( 'entry_content_text' ),
							'default'	=> '',
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
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'text_link_color',
							'label'	=> $this->language->get( 'entry_text_link_text' ),
							'selector' => 'a',
							'css_attr'	=> 'color'
						),
					)
				),
				'custom'			=> array(
					'label'			=> $this->language->get( 'entry_custom_text' ),
					'fields'		=> array(
						array(
							'type'		=> 'text',
							'name'		=> 'font_size',
							'label'		=> $this->language->get( 'entry_font_size' ),
							'default'	=> '',
							'css_attr'  => 'font-size',
							'suffix' 	=> 'px'
						),
						array(
							'type'		=> 'text',
							'name'		=> 'font_weight',
							'label'		=> $this->language->get( 'entry_font_weight' ),
							'default'	=> '',
							'css_attr'  => 'font-weight'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'font_color',
							'label'	=> $this->language->get( 'entry_color' ),
							'default' => '',
							'css_attr'  => 'color'
						),
						array(
							'type'	=> 'text',
							'name'	=> 'font_transform',
							'label'	=> $this->language->get( 'entry_text_transform' ),
							'default' => '',
							'css_attr'  => 'text-transform'
						)
					)
				),
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$settings['content'] = ! empty( $settings ) && ! empty( $settings['content'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['content'] ), ENT_QUOTES, 'UTF-8' ) : '';

		if (!empty($settings['layout'])) {
			$args = $this->renderLayout($settings['layout']);
		} else {
			$args = 'extension/module/pavobuilder/pa_text/pa_text';
		}
		
		return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
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