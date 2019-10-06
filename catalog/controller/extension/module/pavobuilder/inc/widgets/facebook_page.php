<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Facebook_Page extends PA_Widgets {

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-facebook',
				'label'	=> $this->language->get( 'entry_facebook_page' )
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
							'default' => '',
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' )
						),
						array(
							'type'	=> 'text',
							'name'	=> 'facebook_url',
							'label'	=> $this->language->get( 'entry_facebook_url' ),
							'default'	=> '',
							'mask'		=> true
						),
						array(
							'type'	=> 'select',
							'name'	=> 'show_post',
							'label'	=> $this->language->get( 'entry_show_post' ),
							'default' => 'timeline',
							'options'	=> array(
								array(
									'value'	=> '',
									'label'	=> 'None'
								),
								array(
									'value'	=> 'timeline',
									'label'	=> 'Timeline'
								),
								array(
									'value'	=> 'events',
									'label'	=> 'Events'
								),
								array(
									'value'	=> 'messages',
									'label'	=> 'Messages'
								),
								array(
									'value'	=> 'timeline, events',
									'label'	=> 'Timeline & Events'
								),
								array(
									'value'	=> 'timeline, messages',
									'label'	=> 'Timeline & Messages'
								),
								array(
									'value'	=> 'events, messages',
									'label'	=> 'Events & Messages'
								),
								array(
									'value'	=> 'timeline, events, messages',
									'label'	=> 'Timeline, Events & Messages'
								),
							),
						),
						array(
							'type'	=> 'number',
							'name'	=> 'page_width',
							'label'	=> $this->language->get( 'entry_page_width' ),
							'default' => '500',
							'desc'	=> $this->language->get( 'entry_page_width_desc' )
						),
						array(
							'type'	=> 'number',
							'name'	=> 'page_height',
							'label'	=> $this->language->get( 'entry_page_height' ),
							'default' => '400',
							'desc'	=> $this->language->get( 'entry_page_height_desc' )
						),
						array(
							'type'	=> 'select',
							'name'	=> 'header_size',
							'label'	=> $this->language->get( 'entry_header_size' ),
							'default' => 'false',
							'options'	=> array(
								array(
									'value'	=> 'false',
									'label'	=> 'Full'
								),
								array(
									'value'	=> 'true',
									'label'	=> 'Small'
								)
							)
						),
						array(
							'type'	=> 'select',
							'name'	=> 'header_photo',
							'label'	=> $this->language->get( 'entry_header_photo' ),
							'default' => 'false',
							'options'	=> array(
								array(
									'value'	=> 'false',
									'label'	=> 'Show'
								),
								array(
									'value'	=> 'true',
									'label'	=> 'Hide'
								)
							)
						),
						array(
							'type'	=> 'select',
							'name'	=> 'friend_face',
							'label'	=> $this->language->get( 'entry_friend_face' ),
							'default' => 'true',
							'options'	=> array(
								array(
									'value'	=> 'false',
									'label'	=> 'Hide'
								),
								array(
									'value'	=> 'true',
									'label'	=> 'Show'
								)
							)
						),
						array(
							'type'	=> 'select',
							'name'	=> 'language_fb',
							'label'	=> $this->language->get( 'entry_languages' ),
							'default' => 'en_US',
							'language'	=> true,
							'options'	=> array(
								array(
									'value'	=> 'en_PI',
									'label'	=> 'English (Pirate)'
								),
								array(
									'value'	=> 'en_GB',
									'label'	=> 'English (UK)'
								),
								array(
									'value'	=> 'en_UD',
									'label'	=> 'English (Upside Down)'
								),
								array(
									'value'	=> 'es_LA',
									'label'	=> 'Español'
								),
								array(
									'value'	=> 'en_US',
									'label'	=> 'English (US)'
								),
								array(
									'value'	=> 'ar_AR',
									'label'	=> 'العربية (Arabic)'
								),
								array(
									'value'	=> 'fr_FR',
									'label'	=> 'France'
								),
								array(
									'value'	=> 'fr_CA',
									'label'	=> 'Français (Canada)'
								),
								array(
									'value'	=> 'es_CO',
									'label'	=> 'Español (Colombia)'
								),
								array(
									'value'	=> 'es_ES',
									'label'	=> 'Español (España)'
								),
								array(
									'value'	=> 'it_IT',
									'label'	=> 'Italiano'
								),
								array(
									'value'	=> 'pt_BR',
									'label'	=> 'Português (Brasil)'
								),
								array(
									'value'	=> 'pt_PT',
									'label'	=> 'Português (Portugal)'
								),
								array(
									'value'	=> 'ko_KR',
									'label'	=> '한국어 (Korean)'
								),
								array(
									'value'	=> 'vi_VN',
									'label'	=> 'Vietnamese'
								),
								array(
									'value'	=> 'zh_TW',
									'label'	=> '中文(台灣)'
								),
								array(
									'value'	=> 'zh_CN',
									'label'	=> '中文(简体)'
								),
								array(
									'value'	=> 'ja_JP',
									'label'	=> '日本語 (Japan)'
								),
							)
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
						'name'	=> 'color',
						'label'	=> 'entry_color_text',
						'selector' => ''
					)
				)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {

		return $this->load->view( 'extension/module/pavobuilder/pa_facebook_page/pa_facebook_page', array( 'settings' => $settings ) );
	}

}