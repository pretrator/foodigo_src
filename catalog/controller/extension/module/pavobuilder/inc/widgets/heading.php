<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Heading extends PA_Widgets {

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-file-text',
				'label'	=> $this->language->get( 'entry_heading_block' )
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
							'default' => 'pa_heading',
							'options'	=> $this->getLayoutsOptions(),
							'none' 	=> false
						),
						array(
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' ),
							'mask'	=> true,
						),
						array(
							'type'	=> 'select',
							'name'	=> 'tag',
							'label'	=> $this->language->get( 'entry_layout_text' ),
							'default' => 'h3',
							'mask'	=> true,
							'options'	=> array(
								array(
									'value'	=> 'h1',
									'label'	=> 'h1'
								),
								array(
									'value'	=> 'h2',
									'label'	=> 'h2'
								),
								array(
									'value'	=> 'h3',
									'label'	=> 'h3'
								),
								array(
									'value'	=> 'h4',
									'label'	=> 'h4'
								),
								array(
									'value'	=> 'h5',
									'label'	=> 'h5'
								),
								array(
									'value'	=> 'h6',
									'label'	=> 'h6'
								)
							)
						),
						array(
							'type'	=> 'select',
							'name'	=> 'style',
							'label'	=> $this->language->get( 'entry_layout_text' ),
							'default' => 'nostyle',
							'options'	=> array(
								array(
									'value'	=> 'nostyle',
									'label'	=> 'No Style'
								),
								array(
									'value'	=> 'style-left',
									'label'	=> 'Style Left'
								),
								array(
									'value'	=> 'style-center',
									'label'	=> 'Style Center'
								),
                                array(
                                    'value' => 'style-right',
                                    'label' => 'Style Right'
                                ),
							),
							'mask'	=> true
						),
						// array(
						// 	'type'	=> 'select',
						// 	'name'	=> 'layout',
						// 	'label'	=> $this->language->get( 'entry_style_text' ),
						// 	'default' => '',
						// 	'options' => array(),
						// 	'relation' => 'style',
						// 	'relation_value' => 'style-center'
						// ),
						array(
							'type'		=> 'text',
							'name'		=> 'heading',
							'label'		=> $this->language->get( 'entry_heading_block' ),
							'default'	=> '',
							'language'	=> true
						),
						array(
							'type'		=> 'text',
							'name'		=> 'subheading',
							'label'		=> $this->language->get( 'entry_subtitle_text' ),
							'default'	=> '',
							'language'	=> true
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
				'heading'			=> array(
					'label'			=> $this->language->get( 'entry_heading_block' ),
					'is_global'     => true,
					'fields'		=> array(
						array(
							'type'		=> 'text',
							'name'		=> 'hfont_size',
							'label'		=> $this->language->get( 'entry_font_size' ),
							'default'	=> '',
                            'css_attr'  => 'font-size',
							'suffix' 	=> 'px',
                            'selector'  => '.pavo-widget-heading .content-heading, .pavo-widget-heading.heading_v1 .content-heading, .pavo-widget-heading.heading_v2 .content-heading, .pavo-widget-heading.heading_v3 .content-heading, .pavo-widget-heading.heading_v4 .content-heading, .pavo-widget-heading.heading_v5 .content-heading',
						),
						array(
							'type'		=> 'text',
							'name'		=> 'hfont_family',
							'label'		=> $this->language->get( 'entry_font_family' ),
							'default'	=> '',
                            'css_attr'  => 'font-family',
                            'selector'  => '.pavo-widget-heading .content-heading, .pavo-widget-heading.heading_v1 .content-heading, .pavo-widget-heading.heading_v2 .content-heading, .pavo-widget-heading.heading_v3 .content-heading, .pavo-widget-heading.heading_v4 .content-heading, .pavo-widget-heading.heading_v5 .content-heading',
						),
						array(
							'type'		=> 'text',
							'name'		=> 'hfont_weight',
							'label'		=> $this->language->get( 'entry_font_weight' ),
							'default'	=> '',
                            'css_attr'  => 'font-weight',
                            'selector'  => '.pavo-widget-heading .content-heading, .pavo-widget-heading.heading_v1 .content-heading, .pavo-widget-heading.heading_v2 .content-heading, .pavo-widget-heading.heading_v3 .content-heading, .pavo-widget-heading.heading_v4 .content-heading, .pavo-widget-heading.heading_v5 .content-heading',
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'hfont_color',
							'label'	=> $this->language->get( 'entry_color' ),
							'default' => '',
                            'css_attr'  => 'color',
                            'selector'  => '.pavo-widget-heading .content-heading, .pavo-widget-heading.heading_v1 .content-heading, .pavo-widget-heading.heading_v2 .content-heading, .pavo-widget-heading.heading_v3 .content-heading, .pavo-widget-heading.heading_v4 .content-heading, .pavo-widget-heading.heading_v5 .content-heading',
						),
						array(
							'type'		=> 'text',
							'name'		=> 'htext_transform',
							'label'		=> $this->language->get( 'entry_text_transform' ),
							'default'	=> '',
                            'css_attr'  => 'text-transform',
                            'selector'  => '.pavo-widget-heading .content-heading, .pavo-widget-heading.heading_v1 .content-heading, .pavo-widget-heading.heading_v2 .content-heading, .pavo-widget-heading.heading_v3 .content-heading, .pavo-widget-heading.heading_v4 .content-heading, .pavo-widget-heading.heading_v5 .content-heading',
						),
						array(
							'type'		=> 'text',
							'name'		=> 'htext_spacing',
							'label'		=> $this->language->get( 'entry_text_spacing' ),
							'default'	=> '',
                            'css_attr'  => 'letter-spacing',
                            'selector'  => '.pavo-widget-heading .content-heading, .pavo-widget-heading.heading_v1 .content-heading, .pavo-widget-heading.heading_v2 .content-heading, .pavo-widget-heading.heading_v3 .content-heading, .pavo-widget-heading.heading_v4 .content-heading, .pavo-widget-heading.heading_v5 .content-heading',
						),
					)
				),
				'subtitle'			=> array(
					'label'			=> $this->language->get( 'entry_subtitle_text' ),
					'is_global'     => true,
					'fields'		=> array(
						array(
							'type'		=> 'text',
							'name'		=> 'sfont_size',
							'label'		=> $this->language->get( 'entry_font_size' ),
							'default'	=> '',
							'css_attr'  => 'font-size',
							'suffix' 	=> 'px',
						),
						array(
							'type'		=> 'text',
							'name'		=> 'sfont_weight',
							'label'		=> $this->language->get( 'entry_font_weight' ),
							'default'	=> '',
							'css_attr'  => 'font-weight'
						),
						array(
							'type'		=> 'text',
							'name'		=> 'sfont_style',
							'label'		=> $this->language->get( 'entry_font_style' ),
							'default'	=> '',
							'css_attr'  => 'font-style'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'sfont_color',
							'label'	=> $this->language->get( 'entry_color' ),
							'default' => ''
						),
						array(
							'type'		=> 'text',
							'name'		=> 'stext_spacing',
							'label'		=> $this->language->get( 'entry_text_spacing' ),
							'default'	=> '',
						),
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {

		$settings = array_merge(  array(
			 'heading'	   => '',
			 'subheading'  => '',
			 'tag' 		   => 'h3',
			 'extra_class' => '',
			 'style'	   => '',
		), $settings );

	 	$settings['heading'] =  html_entity_decode( htmlspecialchars_decode( $settings['heading'] ), ENT_QUOTES, 'UTF-8' ) ;
	 	$settings['subheading'] =  html_entity_decode( htmlspecialchars_decode( $settings['subheading'] ), ENT_QUOTES, 'UTF-8' ) ;

	 	if (!empty($settings['layout'])) {
			$args = $this->renderLayout($settings['layout']);
		} else {
			$args = 'extension/module/pavobuilder/pa_heading/pa_heading';
		}

		return $this->load->view( $args, array( 'settings' => $settings ) );
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