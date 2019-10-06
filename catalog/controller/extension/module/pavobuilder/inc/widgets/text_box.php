<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Text_Box extends PA_Widgets {
	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-star',
				'label'	=> $this->language->get( 'entry_text_box' )
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
							'default' => 'pa_text_box',
							'options'	=> $this->getLayoutsOptions(),
							'none' 	=> false
						),
						array(
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' )
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
						),
						array(
							'type'	=> 'text',
							'name'	=> 'iconpicker',
							'label'	=> $this->language->get( 'entry_icon_text' ),
							'default' => '',
						),
						array(
							'type'	=> 'select',
							'name'	=> 'iconstyle',
							'label'	=> $this->language->get( 'entry_icon_style_text' ),
							'default' => '',
							'options'	=> array(
								array(
									'value'	=> '',
									'label'	=> 'Auto'
								),
								array(
									'value'	=> 'icon-radius',
									'label'	=> 'Radius'
								),
								array(
									'value'	=> 'icon-rectangle',
									'label'	=> 'Rectangle'
								)
							)
						),
						array(
							'type'	=> 'image',
							'name'	=> 'src',
							'label'	=> $this->language->get( 'entry_background_text' )
						),
						array(
							'type'	=> 'text',
							'name'	=> 'title',
							'label'	=> $this->language->get( 'entry_title_text' ),
							'desc'	=> $this->language->get( 'entry_title_desc_text' ),
							'language'	=> true,
							'default' => ''
						),
						array(
							'type'	=> 'text',
							'name'	=> 'subtitle',
							'label'	=> $this->language->get( 'entry_subtitle_text' ),
							'desc'	=> $this->language->get( 'entry_subtitle_desc_text' ),
							'placeholder'	=> '',
							'language'	=> true
						),
						array(
							'type'		=> 'textarea',
							'name'		=> 'content',
							'label'		=> $this->language->get( 'entry_content_text' ),
							'default'	=> '',
							'cols'		=> 10,
							'language'	=> true,
							'default' => ''
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
					)
				),
				'custom'			=> array(
					'label'			=> $this->language->get( 'entry_custom_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'icon_color',
							'label'	=> $this->language->get( 'entry_icon_color' ),
							'selector' => '.content-icon',
							'css_attr'	=> 'color'
						),
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {

		$default = array(
			'iconpicker'  => '',
			'iconstyle'   => '',
			'title'   	  => '',
			'subtitle'    => '',
			'content'     => '',
			'extra_class' => '',
			'image'		  => ''
		);

		$settings = array_merge( $default, $settings );
		if( defined("IMAGE_URL")){
            $settings['server'] =  IMAGE_URL;
        } else  {
            $settings['server'] = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }

		$settings['content'] 	= html_entity_decode( htmlspecialchars_decode( $settings['content'] ), ENT_QUOTES, 'UTF-8' ) ;
		$settings['title']      = html_entity_decode( htmlspecialchars_decode( $settings['title'] ), ENT_QUOTES, 'UTF-8' ) ;
		$settings['subtitle']   = html_entity_decode( htmlspecialchars_decode( $settings['subtitle'] ), ENT_QUOTES, 'UTF-8' ) ;

		if (!empty($settings['layout'])) {
			$args = $this->renderLayout($settings['layout']);
		} else {
			$args = 'extension/module/pavobuilder/pa_text_box/pa_text_box';
		}

		return $this->load->view( $args, array( 'settings' => $settings  ) );
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