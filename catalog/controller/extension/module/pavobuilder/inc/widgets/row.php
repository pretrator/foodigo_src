<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Row extends PA_Widgets {

	public function fields(){
		$effect_duration_options = array();
		$effect_duration_options[] = array(
				'label'	=> $this->language->get( 'text_default' ),
				'value'	=> ''
			);
		for ( $i = 1; $i <= 10; $i++ ) {
			$effect_duration_options[] = array(
					'label'	=> $i . 's',
					'value'	=> $i . 's'
				);
		}

		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-columns',
				'label'	=> $this->language->get( 'entry_row_text' )
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
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' )
						),
						array(
							'type'	=> 'select',
							'name'	=> 'layout',
							'label'	=> $this->language->get( 'entry_layout_type_text' ),
							'options'	=> array(
								array(
									'label'	=> $this->language->get( 'entry_wide_text' ),
									'value'	=> 'wide'
								),
								array(
									'label'	=> $this->language->get( 'entry_boxed_text' ),
									'value'	=> 'boxed'
								)
							)
						),
						array(
							'type'	=> 'text',
							'name'	=> 'background_video',
							'label'	=> $this->language->get( 'entry_video_url_text' )
						),
						array(
							'type'	=> 'select-animate',
							'name'	=> 'effect',
							'id'	=> 'animate-select',
							'group'	=> true,
							'label'	=> $this->language->get( 'entry_effect_text' )
						),
						array(
							'type'	=> 'select',
							'name'	=> 'effect_duration',
							'label'	=> $this->language->get( 'effect_duration_text' ),
							'options'	=> $effect_duration_options
						),
						array(
							'type'	=> 'checkbox',
							'name'	=> 'parallax',
							'label'	=> $this->language->get( 'entry_parallax_text' )
						),
						array(
							'type'		=> 'select',
							'name'		=> 'no_space',
							'default'	=> 0,
							'label'		=> $this->language->get( 'entry_no_space' ),
							'options'	=> array(
									array(
										'label'	=> $this->language->get( 'text_no' ),
										'value'	=> 0
									),
									array(
										'label'	=> $this->language->get( 'text_yes' ),
										'value'	=> 1
									)
								),
							'none'	=> false
						)
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
							'name'	=> 'background_size',
							'label'	=> $this->language->get( 'entry_background_size' ),
							'options'	=> array(
								array(
									'label'		=> 'None',
									'value'		=> ''
								),
								array(
									'label'		=> 'Auto',
									'value'		=> 'auto'
								),
								array(
									'label'		=> 'Contain',
									'value'		=> 'contain'
								),
								array(
									'label'		=> 'Cover',
									'value'		=> 'cover'
								),
								array(
									'label'		=> 'Inherit',
									'value'		=> 'inherit'
								),
								array(
									'label'		=> 'Initial',
									'value'		=> 'initial'
								),
                                array(
                                    'label'     => 'Unset',
                                    'value'     => 'unset'
                                )
							),
							'css_attr'	=> 'background-size'
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
                                    'label'     => 'Repeat',
                                    'value'     => 'repeat'
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
							'type'	=> 'number',
							'name'	=> 'inner_padding',
							'label'	=> $this->language->get( 'entry_inner_padding' ),
							'selector'	=> '> div > .pa-row-inner',
							'css_attr'	=> 'padding',
							'suffix' => 'px'
						),
						array(
							'type'	=> 'layout-onion',
							'name'	=> 'styles',
							'label'	=> $this->language->get( 'entry_box_text' )
						),
						array(
							'type'	=> 'select',
							'name'	=> 'text-align',
							'label'	=> $this->language->get( 'entry_text_align_text' ),
							'options'	=> array(
                                array(
                                    'label'     => 'Left',
                                    'value'     => 'left'
                                ),
								array(
									'label'	=> 'Justify',
									'value'	=> 'justify'
								),
								array(
									'label'	=> 'Right',
									'value'	=> 'right'
								),
								array(
									'label'	=> 'Center',
									'value'	=> 'center'
								)
							),
							'css_attr'	=> 'text-align',
							'none'	=> false
						)
					)
				),
				'custom'			=> array(
					'label'			=> $this->language->get( 'entry_custom_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'inner_background_color',
							'label'	=> $this->language->get( 'entry_inner_background_color_text' ),
							'selector' => '> div > .pa-row-inner',
							'css_attr'	=> 'background-color'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'inner_border_color',
							'label'	=> $this->language->get( 'entry_inner_border_color_text' ),
							'selector'	=> '> div > .pa-row-inner',
							'css_attr'	=> 'border-color'
						),
						array(
							'type'	=> 'number',
							'name'	=> 'inner_border_width',
							'label'	=> $this->language->get( 'entry_inner_border_width_text' ),
							'selector'	=> '> div > .pa-row-inner',
							'css_attr'	=> 'border-width',
							'min'		=> 0,
							'step'		=> 1,
							'suffix'	=> 'px'
						),
						array(
							'type'	=> 'select',
							'name'	=> 'inner_border_style',
							'label'	=> $this->language->get( 'entry_inner_border_style_text' ),
							'selector'	=> '> div > .pa-row-inner',
							'css_attr'	=> 'border-style',
							'none'		=> false,
							'options'	=> array(
									array(
											'value' => 'none',
											'label'		=> 'None'
										),
									array(
											'value' => 'solid',
											'label'		=> 'Solid'
										),
									array(
											'value' => 'dotted',
											'label'		=> 'Dotted'
										),
									array(
											'value' => 'dashed',
											'label'		=> 'Dashed'
										),
									array(
											'value' => 'double',
											'label'		=> 'Double'
										),
									array(
											'value' => 'groove',
											'label'		=> 'Groove'
										),
									array(
											'value' => 'ridge',
											'label'		=> 'Ridge'
										),
									array(
											'value' => 'inset',
											'label'		=> 'Inset'
										),
									array(
											'value' => 'outset',
											'label'		=> 'Outset'
										),
									array(
											'value' => 'initial',
											'label'		=> 'Initial'
										),
									array(
											'value' => 'inherit',
											'label'		=> 'Inherit'
										)
								)
						),
						array(
							'type'	=> 'number',
							'name'	=> 'inner_padding_top',
							'label'	=> $this->language->get( 'entry_inner_padding_top_text' ),
							'selector'	=> '> div > .pa-row-inner',
							'css_attr'	=> 'padding-top',
							'min'		=> 0,
							'step'		=> 1,
							'suffix'	=> 'px'
						),
						array(
							'type'	=> 'number',
							'name'	=> 'inner_padding_bottom',
							'label'	=> $this->language->get( 'entry_inner_padding_bottom_text' ),
							'selector'	=> '> div > .pa-row-inner',
							'css_attr'	=> 'padding-bottom',
							'min'		=> 0,
							'step'		=> 1,
							'suffix'	=> 'px'
						),
						array(
							'type'	=> 'number',
							'name'	=> 'inner_padding_left',
							'label'	=> $this->language->get( 'entry_inner_padding_left_text' ),
							'selector'	=> '> div > .pa-row-inner',
							'css_attr'	=> 'padding-left',
							'min'		=> 0,
							'step'		=> 1,
							'suffix'	=> 'px'
						),
						array(
							'type'	=> 'number',
							'name'	=> 'inner_padding_right',
							'label'	=> $this->language->get( 'entry_inner_padding_right_text' ),
							'selector'	=> '> div > .pa-row-inner',
							'css_attr'	=> 'padding-right',
							'min'		=> 0,
							'step'		=> 1,
							'suffix'	=> 'px'
						),
					)
				),
				'border'			=> array(
					'label'			=> $this->language->get( 'entry_inner_border_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'text',
							'name'	=> 'inner_border_top',
							'label'	=> $this->language->get( 'entry_inner_border_top_text' ),
							'selector' => '> div > .pa-row-inner',
							'css_attr'	=> 'border-top'
						),
						array(
							'type'	=> 'text',
							'name'	=> 'inner_border_bottom',
							'label'	=> $this->language->get( 'entry_inner_border_bottom_text' ),
							'selector' => '> div > .pa-row-inner',
							'css_attr'	=> 'border-bottom'
						),
						array(
							'type'	=> 'text',
							'name'	=> 'inner_border_left',
							'label'	=> $this->language->get( 'entry_inner_border_left_text' ),
							'selector' => '> div > .pa-row-inner',
							'css_attr'	=> 'border-left'
						),
						array(
							'type'	=> 'text',
							'name'	=> 'inner_border_right',
							'label'	=> $this->language->get( 'entry_inner_border_right_text' ),
							'selector' => '> div > .pa-row-inner',
							'css_attr'	=> 'border-right'
						),
					)
				),
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$class = $data = array();
		if ( ! empty( $settings['extra_class'] ) ) {
			$class[] = $settings['extra_class'] ? $settings['extra_class'] : '';
		}
		if ( ! empty( $settings['parallax'] ) ) {
			$class[] = $settings['parallax'] ? 'pa-parallax' : '';
		}
		if ( ! empty( $settings['effect'] ) ) {
			$class[] = 'animated';
			$data['animate'] = $settings['effect'];
		}

		$settings['class'] = implode( ' ', $class );
		$settings['id'] = ! empty( $settings['uniqid_id'] ) ? $settings['uniqid_id'] : '';
		return $this->load->view( 'extension/module/pavobuilder/pa_row/pa_row', array( 'settings' => $settings, 'data' => $data, 'content' => $content ) );
	}

}