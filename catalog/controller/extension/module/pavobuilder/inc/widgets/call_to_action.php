<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Call_To_Action extends PA_Widgets {

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-bullhorn',
				'label'	=> $this->language->get( 'entry_widget_call_to_action' )
			),
			'tabs'	=> array(
				'general'		=> array(
					'label'		=> $this->language->get( 'entry_general_text' ),
					'fields'	=> array(
						array(
							'type'	=> 'hidden',
							'name'	=> 'uniqid_id',
							'label'	=> $this->language->get( 'entry_column_id_text' ),
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
							'name'	=> 'caption',
							'label'	=> $this->language->get( 'entry_caption_text' ),
							'language'	=> true,
							'default' => ''
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
							'default'	=> '',
							'language'	=> true
						),
						array(
							'type'		=> 'editor',
							'name'		=> 'content',
							'label'		=> $this->language->get( 'entry_content_text' ),
							'default'	=> '',
							'language'	=> true
						),
						array(
                            'type'    => 'text',
                            'name'    => 'button_text',
                            'label'   => $this->language->get( 'entry_banner_button' ),
                            'default' => '',
                            'language'=> true
                        ),
						array(
							'type'		=> 'iconpicker',
							'name'		=> 'icon',
							'label'		=> $this->language->get( 'entry_icon_text' ),
							'default'	=> ''
						),
						array(
							'type'	=> 'select',
							'name'	=> 'icon_position',
							'label'	=> $this->language->get( 'entry_icon_position' ),
							'default' => 'left',
							'options'	=> array(
								array(
									'value'	=> 'left',
									'label'	=> 'Left'
								),
								array(
									'value'	=> 'right',
									'label'	=> 'Right'
								),
							),
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link',
							'label'	=> $this->language->get( 'entry_link_text' ),
							'default' => '',
							'desc'	=> $this->language->get( 'entry_link_desc_text' ),
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
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$class = array();
		if ( ! empty( $settings['extra_class'] ) ) {
			$class[] = $settings['extra_class'];
		}
		if ( ! empty( $settings['effect'] ) ) {
			$class[] = $settings['effect'];
		}

		$settings['class'] = implode( ' ', $class );

		$settings['content'] 	= ! empty( $settings['content'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['content'] ), ENT_QUOTES, 'UTF-8' ) : '';

		if (!empty($settings['layout'])) {
			$args = $this->renderLayout($settings['layout']);
		} else {
			$args = 'extension/module/pavobuilder/pa_call_to_action/pa_call_to_action';
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