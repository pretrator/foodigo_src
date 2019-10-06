<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Button extends PA_Widgets {

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-bold',
				'label'	=> $this->language->get( 'entry_button_text' )
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
							'name'	=> 'link',
							'label'	=> $this->language->get( 'entry_link_text' ),
							'default'	=> '#',
							'desc'	=> $this->language->get( 'entry_link_desc_text' ),
						),
						array(
							'type'	=> 'select',
							'name'	=> 'button_style',
							'label'	=> $this->language->get( 'entry_style_text' ),
							'default' => '',
							'options'	=> array(
								array(
									'value'	=> 'btn-default',
									'label'	=> 'Default'
								),
								array(
									'value'	=> 'btn-primary',
									'label'	=> 'Primary'
								),
								array(
									'value'	=> 'btn-info',
									'label'	=> 'Info'
								),
                                array(
                                    'value' => 'btn-success',
                                    'label' => 'Success'
                                ),
                                array(
									'value'	=> 'btn-warning',
									'label'	=> 'Warning'
								),
								array(
									'value'	=> 'btn-danger',
									'label'	=> 'Danger'
								),
                                array(
                                    'value' => 'btn-link',
                                    'label' => 'Link'
                                ),
							),
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
							'type'		=> 'text',
							'name'		=> 'title',
							'label'		=> $this->language->get( 'entry_title_text' ),
							'desc'		=> $this->language->get( 'entry_title_desc_text' ),
							'default'	=> '',
							'language'	=> true,
						),
						array(
							'type'	=> 'select',
							'name'	=> 'show_icon',
							'label'	=> $this->language->get( 'entry_show_icon' ),
							'default' => 'true',
							'options'	=> array(
								array(
									'value'	=> 'true',
									'label'	=> 'Enabled'
								),
								array(
									'value'	=> 'false',
									'label'	=> 'Disabled'
								)
							),
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
					)
				),
                'style'             => array(
                    'label'         => $this->language->get( 'entry_styles_text' ),
                    'fields'        => array(
                        array(
                            'type'  => 'layout-onion',
                            'name'  => 'layout_onion',
                            'label' => 'entry_box_text'
                        )
                    )
                ),
                'title'			=> array(
					'label'			=> $this->language->get( 'entry_title_text' ),
					'fields'		=> array(
						array(
							'type'		=> 'text',
							'name'		=> 'font_size',
							'label'		=> $this->language->get( 'entry_font_size' ),
							'default'	=> '',
						),
						array(
							'type'		=> 'text',
							'name'		=> 'font_weight',
							'label'		=> $this->language->get( 'entry_font_weight' ),
							'default'	=> '',
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'font_color',
							'label'	=> $this->language->get( 'entry_color' ),
							'default' => ''
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'icon_color',
							'label'	=> $this->language->get( 'entry_icon_color' ),
							'selector' => '.fa',
							'css_attr'	=> 'color'
						),
					)
				),
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		
		return $this->load->view( 'extension/module/pavobuilder/pa_button/pa_button', array( 'settings' => $settings ) );
	}

}