<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Search extends PA_Widgets {

	public $header = true;

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-file-text',
				'label'	=> $this->language->get( 'entry_search_block' ),
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
							'type'	=> 'select',
							'name'	=> 'style',
							'label'	=> $this->language->get( 'entry_layout_text' ),
							'default' => 'h3',
							'mask'	=> true,
							'options'	=> array(
								array(
									'value'	=> '',
									'label'	=> 'Default'
								),
								array(
									'value'	=> 'popup-search',
									'label'	=> 'PopUp Search'
								)
							)
						),
						array(
							'type'	=> 'select',
							'name'	=> 'layout',
							'label'	=> $this->language->get( 'entry_layout_style' ),
							'default' => '',
							'options'	=> array(
								array(
									'value'	=> 'style-v1',
									'label'	=> 'Style-v1'
								),
								array(
									'value'	=> 'style-v2',
									'label'	=> 'Style-v2'
								),
								array(
									'value'	=> 'style-v3',
									'label'	=> 'Style-v3'
								)
							)
						),
						array(
							'type'	=> 'select',
							'name'	=> 'button',
							'label'	=> $this->language->get( 'entry_button_text' ),
							'default' => 'btn btn-search-default',
							'mask'	=> true,
							'options'	=> array(
								array(
									'value'	=> 'btn btn-search-default',
									'label'	=> 'Default'
								),
								array(
									'value'	=> 'btn btn-primary',
									'label'	=> 'Primary'
								),

								array(
									'value'	=> 'btn btn-info',
									'label'	=> 'Info'
								),
								array(
									'value'	=> 'btn btn-warning',
									'label'	=> 'Warning'
								),
								array(
									'value'	=> 'btn btn-success',
									'label'	=> 'Success'
								)
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
							'name'	=> 'icon_color',
							'label'	=> $this->language->get( 'entry_icon_color' ),
							'selector' => '.popup-search, .searchbox-default .fa',
							'css_attr'	=> 'color'
						),
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {

		$settings = array_merge( array(
			'style' 	 => '',
			'button' 	 => ''
		) , $settings );

		$settings['search'] = $this->load->controller('common/search');  
		 
		return $this->load->view( 'extension/module/pavoheader/search', array( 'settings' => $settings ) );
	}

	/**
	 * s fields
	 */
	public function validate( $settings = array() ) {
		return $settings;
	}

}