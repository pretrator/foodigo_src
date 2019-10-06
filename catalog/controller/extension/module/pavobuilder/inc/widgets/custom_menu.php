<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Custom_Menu extends PA_Widgets {

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-bars',
				'label'	=> $this->language->get( 'entry_custom_menu' )
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
							'default'	=> ''
						),
						array(
							'type'	=> 'select',
							'name'	=> 'menu_type',
							'label'	=> $this->language->get( 'entry_menu_type' ),
							'default' => 'ver',
							'language'	=> true,
							'options'	=> array(
								array(
									'value'	=> 'ver',
									'label'	=> 'Vertical'
								),
								array(
									'value'	=> 'hor',
									'label'	=> 'Horizontal'
								) , 

								array(
									'value'	=> 'dropdown',
									'label'	=> 'Dropdown'
								)
							)
						),
						array(
							'type'	=> 'select',
							'name'	=> 'layout',
							'label'	=> $this->language->get( 'entry_layout_text' ),
							'default' => 'pa_custom_menu',
							'options'	=> $this->getLayoutsOptions(),
							'none' 	=> false
						),
						array(
							'type'		=> 'text',
							'name'		=> 'title',
							'label'		=> $this->language->get( 'entry_title' ),
							'desc'		=> $this->language->get( 'entry_title_desc_text' ),
							'default'	=> '',
							'language'	=> true
						),
						array(
							'type'		=> 'text',
							'name'		=> 'url_title',
							'label'		=> $this->language->get( 'entry_url_title' ),
							'desc'		=> $this->language->get( 'entry_link_desc_text' ),
							'default'	=> '',
							'language'	=> true
						),
						array(
							'type'	=> 'group',	
							'name'	=> 'items',
							'label'	=> $this->language->get( 'entry_item' ),
							'fields'	=> array(
								array(
									'type'		=> 'iconpicker',
									'name'		=> 'icon',
									'label'		=> $this->language->get( 'icon' ),
									'default'	=> ''
								),
								array(
									'type'		=> 'iconpicker',
									'name'		=> 'icon_rtl',
									'label'		=> $this->language->get( 'entry_icon_rtl' ),
									'default'	=> ''
								),
								array(
									'type'		=> 'text',
									'name'		=> 'text_link',
									'label'		=> $this->language->get( 'entry_text_link' ),
									'default'	=> '',
									'language'	=> true
								),
								array(
									'type'		=> 'text',
									'name'		=> 'url_link',
									'label'		=> $this->language->get( 'entry_url_link' ),
									'desc'		=> $this->language->get( 'entry_link_desc_text' ),
									'default'	=> '#',
									'language'	=> true
								)
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
                            'selector'  => '.pavo-widget-custom-menu .custom-menu-item a',
						),
						array(
							'type'		=> 'text',
							'name'		=> 'hfont_weight',
							'label'		=> $this->language->get( 'entry_font_weight' ),
							'default'	=> '',
                            'css_attr'  => 'font-weight',
                            'selector'  => '.pavo-widget-custom-menu .custom-menu-item a',
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'hfont_color',
							'label'	=> $this->language->get( 'entry_color' ),
							'default' => '',
                            'css_attr'  => 'color',
                            'selector'  => '.pavo-widget-custom-menu .custom-menu-item a',
						),
						array(
							'type'		=> 'text',
							'name'		=> 'htext_transform',
							'label'		=> $this->language->get( 'entry_text_transform' ),
							'default'	=> '',
                            'css_attr'  => 'text-transform',
                            'selector'  => '.pavo-widget-custom-menu .custom-menu-item a',
						),
						array(
							'type'		=> 'text',
							'name'		=> 'htext_spacing',
							'label'		=> $this->language->get( 'entry_text_spacing' ),
							'default'	=> '',
                            'css_attr'  => 'letter-spacing',
                            'selector'  => '.pavo-widget-custom-menu .custom-menu-item a',
						),
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$this->load->model( 'localisation/language' );
   		$language = $this->model_localisation_language->getLanguage( $this->config->get('config_language_id') );
   		$settings['language_code'] = isset($settings[$language['code']]) ? $settings[$language['code']] : '';
   		$settings['direction'] = $this->language->get('direction');
		$settings['get_language'] = array ();

		if (!empty($settings['items'])) {
			foreach ($settings['items'] as $lang) {
				$settings['get_language'][] = array (
					'language' => isset($lang['languages'][$language['code']]) ? $lang['languages'][$language['code']] : ''
				);
			}
		}

		$server = $this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER;

		if (!empty($settings['layout'])) {
			$args = $this->renderLayout($settings['layout']);
		} else {
			$args = 'extension/module/pavobuilder/pa_custom_menu/pa_custom_menu';
		}
		
		return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
	}
}