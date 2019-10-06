<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Counter extends PA_Widgets {

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-flash',
				'label'	=> $this->language->get( 'entry_counter_text' )
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
							'name'	=> 'align',
							'label'	=> $this->language->get( 'entry_align_text' ),
							'default' => 'text-center',
							'options'	=> array(
								array(
									'value'	=> 'text-center',
									'label'	=> 'Algin Center'
								),
								array(
									'value'	=> 'text-left',
									'label'	=> 'Algin Left'
								),
								array(
									'value'	=> 'text-right',
									'label'	=> 'Algin Right'
								) 
							)
						),
						array(
							'type'	=> 'select',
							'name'	=> 'color',
							'label'	=> $this->language->get( 'entry_color_text' ),
							'default' => 'no-color',
							'options'	=> array(
								array(
									'value'	=> 'no-color',
									'label'	=> 'Default'
								),
								array(
									'value'	=> 'text-primary',
									'label'	=> 'Primary'
								),
								array(
									'value'	=> 'text-info',
									'label'	=> 'Info'
								),
								array(
									'value'	=> 'text-danger',
									'label'	=> 'Danger'
								),
								array(
									'value'	=> 'text-success',
									'label'	=> 'Success'
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
							'name'	=> 'number',
							'label'	=> $this->language->get( 'entry_number_text' ),
							'default' => 300,
							'mask'	=> true
						),
						array(
							'type'	=> 'text',
							'name'	=> 'speed',
							'label'	=> $this->language->get( 'entry_speed_text' ),
							'default' => 5000,

						),
						array(
							'type'	=> 'text',
							'name'	=> 'label',
							'mask'	=> true,
							'label'	=> $this->language->get( 'entry_label_text' ),
							'default' => 'Your Label',
							'language' => true
						),
						array(
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'default' => '',
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' )
						),
						array(
							'type'		=> 'iconpicker',
							'name'		=> 'icon',
							'label'		=> $this->language->get( 'entry_icon_text' ),
							'default'	=> ''
						),
					)
				),
				'background'		=> array(
					'label'			=> $this->language->get( 'entry_background_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'background_color',
							'label'	=> $this->language->get( 'entry_background_color_text' ),
							'css_attr'	=> 'background-color'
						),
						array(
							'type'	=> 'image',
							'name'	=> 'background_image',
							'label'	=> $this->language->get( 'entry_background_image_text' ),
							'css_attr'	=> 'background-image'
						),
						array(
							'type'	=> 'select',
							'name'	=> 'background_position',
							'label'	=> $this->language->get( 'entry_background_position' ),
							'options'	=> array(
								array(
									'label'		=> 'None',
									'value'		=> ''
								),
								array(
									'label'		=> 'Inherit',
									'value'		=> 'inherit'
								),
								array(
									'label'		=> 'Top Left',
									'value'		=> 'top left'
								),
								array(
									'label'		=> 'Top Right',
									'value'		=> 'top right'
								),
								array(
									'label'		=> 'Bottom Left',
									'value'		=> 'bottom left'
								),
								array(
									'label'		=> 'Bottom Right',
									'value'		=> 'bottom right'
								),
                                array(
                                    'label'     => 'Bottom Center',
                                    'value'     => 'bottom center'
                                ),
                                array(
                                    'label'     => 'Right Center',
                                    'value'     => 'right center'
                                ),
								array(
									'label'		=> 'Center Center',
									'value'		=> 'center center'
								)
							),
							'css_attr'	=> 'background-position'
						),
						array(
							'type'	=> 'select',
							'name'	=> 'background_repeat',
							'label'	=> $this->language->get( 'entry_background_repeat_text' ),
							'options'	=> array(
								array(
									'label'		=> 'None',
									'value'		=> ''
								),
								array(
									'label'	=> 'No Repeat',
									'value'	=> 'no-repeat'
								),
								array(
									'label'	=> 'Repeat x',
									'value'	=> 'repeat-x'
								),
								array(
									'label'	=> 'Repeat y',
									'value'	=> 'repeat-y'
								)
							),
							'css_attr'	=> 'background-repeat'
						)
					)
				),
				'style'				=> array(
					'label'			=> $this->language->get( 'entry_styles_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'layout-onion',
							'name'	=> 'styles',
							'label'	=> $this->language->get( 'entry_box_text' )
						)
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$settings = array_merge( 
			array(
				'align' => '',
				'color' => '',
				'extra_class' =>  ''
			), $settings
		);

		$settings['extra_class'] = $settings['align'].' '.$settings['color'];
		if( isset( $settings['src'] ) ){
			$settings['src'] = $this->getImageLink( $settings['src'], 'full' );
		}
		
		return $this->load->view( 'extension/module/pavobuilder/pa_counter/pa_counter', array( 'settings' => $settings ) );
	}

}