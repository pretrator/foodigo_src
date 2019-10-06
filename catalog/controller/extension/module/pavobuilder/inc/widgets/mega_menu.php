<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Mega_Menu extends PA_Widgets {
	public $header = true;
	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-th-list',
				'label'	=> $this->language->get( 'entry_mega_menu_hor' ),
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
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'default' => '',
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' )
						),
						array(
							'type'		=> 'select',
							'name'		=> 'menu_type',
							'label'		=> $this->language->get( 'entry_menu_type' ),
							'default' 	=> 'horizontal-menu',
							'options'	=> array(
								array(
									'value'	=> 'horizontal-menu',
									'label'	=> 'Horizontal Style'
								),
								array(
									'value'	=> 'veritcal-menu',
									'label'	=> 'Veritcal Style'
								),
								array(
									'value'	=> 'vertical-dropdown',
									'label'	=> 'Vertical Dropdown'
								),
								array(
									'value'	=> 'vertical-dropdown-showed',
									'label'	=> 'Vertical Dropdown Always Show'
								),
							)
						),
						array(
							'type'	=> 'select',
							'name'	=> 'menu_layout',
							'label'	=> $this->language->get( 'entry_style_text' ),
							'default' => '',
							'options' => array(
								array(
									'value'	=> 'style1',
									'label'	=> 'Style 1'
								),
								array(
									'value'	=> 'style2',
									'label'	=> 'Style 2'
								),
							),
							'relation' => 'menu_type',
							'relation_value' => 'horizontal-menu'
						),
						array(
							'type'		=> 'select',
							'name'		=> 'ismain',
							'label'		=> $this->language->get( 'entry_is_mainmenu' ),
							'default' 	=> '1',
							'options'	=> array(
								array(
									'value'	=> '1',
									'label'	=> 'Yes'
								),
								array(
									'value'	=> '0',
									'label'	=> 'No'
								)
							),
							'desc' => $this->language->get( 'entry_is_mainmenu_desc' )
						),
						array(
							'type'	=> 'text',
							'name'	=> 'title_vertical',
							'label'	=> $this->language->get( 'entry_title_vertical' ),
							'default' => '',
							'language'	=> true
						),
						array(
							'type'		=> 'iconpicker',
							'name'		=> 'icon',
							'label'		=> $this->language->get( 'icon_vertical' ),
							'default'	=> ''
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
							'selector'	=> '.megamenu li .menu-title, .megamenu li .caret',
							'css_attr'	=> 'color'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'submenu_color',
							'label'	=> 'entry_submenu_color_text',
							'selector'	=> '.megamenu li .dropdown-menu a',
							'css_attr'	=> 'color'
						)
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
 		$settings['ismain'] = isset($settings['ismain']) && $settings['ismain'] ? true : false; 
  		$settings['menu']= $this->load->controller('extension/module/pavmegamenu');

		return $this->load->view( 'extension/module/pavoheader/mega_menu', array( 'settings' => $settings ) );
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