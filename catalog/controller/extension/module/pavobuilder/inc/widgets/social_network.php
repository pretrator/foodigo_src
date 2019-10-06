<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Social_Network extends PA_Widgets {

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-share-alt-square',
				'label'	=> $this->language->get( 'entry_social_network' )
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
							'type'		=> 'select',
							'name'		=> 'style',
							'label'		=> $this->language->get( 'entry_style_text' ),
							'default' 	=> 'default',
							'options'	=> array(

								array(
									'value'	=> 'default',
									'label'	=> 'Default'
								),

								array(
									'value'	=> 'style-light',
									'label'	=> 'Light'
								),
								array(
									'value'	=> 'style-dark',
									'label'	=> 'Dark'
								),
								array(
									'value'	=> 'style-colorful',
									'label'	=> 'Colorful'
								),
								array(
									'value'	=> 'custom-style1',
									'label'	=> 'Custom style 1'
								)
							)
						),
						array(
							'type'		=> 'select',
							'name'		=> 'layout_style',
							'label'		=> $this->language->get( 'entry_layout_style' ),
							'default' 	=> 'inline',
							'options'	=> array(
								array(
									'value'	=> 'list-inline',
									'label'	=> 'List Inline'
								),

								array(
									'value'	=> 'list-unstyled',
									'label'	=> 'List unstyled'
								),
							)
						),
						array(
							'type'		=> 'select',
							'name'		=> 'size',
							'label'		=> $this->language->get( 'entry_size_text' ),
							'default' 	=> 'true',
							'options'	=> array(

								array(
									'value'	=> '',
									'label'	=> 'Default'
								),

								array(
									'value'	=> 'size-small',
									'label'	=> 'Small'
								),
								array(
									'value'	=> 'size-medium',
									'label'	=> 'Medium'
								),
								array(
									'value'	=> 'size-large',
									'label'	=> 'Large'
								)
							)
						),
						array(
							'type'		=> 'select',
							'name'		=> 'label',
							'label'		=> $this->language->get( 'entry_label_text' ),
							'default' 	=> '',
							'options'	=> array(

								array(
									'value'	=> 'true',
									'label'	=> 'Enabled'
								),

								array(
									'value'	=> 'false',
									'label'	=> 'Disabled'
								),
							)
						),
						array(
							'type'	  => 'checkbox',
							'name'	  => 'facebook',
							'label'	  => $this->language->get( 'entry_facebook' ),
							'default' => 1
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link_facebook',
							'label'	=> $this->language->get( 'entry_link_facebook' ),
							'desc'	=> $this->language->get( 'entry_desc_facebook' )
						),
						array(
							'type'	  => 'checkbox',
							'name'	  => 'youtube',
							'label'	  => $this->language->get( 'entry_youtube' ),
							'default' => 1
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link_youtube',
							'label'	=> $this->language->get( 'entry_link_youtube' ),
							'desc'	=> $this->language->get( 'entry_desc_youtube' )
						),
						array(
							'type'	  => 'checkbox',
							'name'	  => 'instagram',
							'label'	  => $this->language->get( 'entry_instagram' ),
							'default' => 0
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link_instagram',
							'label'	=> $this->language->get( 'entry_link_instagram' )
						),
						array(
							'type'	  => 'checkbox',
							'name'	  => 'twitter',
							'label'	  => $this->language->get( 'entry_twitter' ),
							'default' => 0
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link_twitter',
							'label'	=> $this->language->get( 'entry_link_twitter' )
						),
						array(
							'type'	  => 'checkbox',
							'name'	  => 'reddit',
							'label'	  => $this->language->get( 'entry_reddit' ),
							'default' => 0
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link_reddit',
							'label'	=> $this->language->get( 'entry_link_reddit' )
						),
						array(
							'type'	  => 'checkbox',
							'name'	  => 'pinterest',
							'label'	  => $this->language->get( 'entry_pinterest' ),
							'default' => 0
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link_pinterest',
							'label'	=> $this->language->get( 'entry_link_pinterest' )
						),
						array(
							'type'	  => 'checkbox',
							'name'	  => 'google_plus',
							'label'	  => $this->language->get( 'entry_google_plus' ),
							'default' => 0
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link_google_plus',
							'label'	=> $this->language->get( 'entry_link_google_plus' )
						),
						array(
							'type'	  => 'checkbox',
							'name'	  => 'flickr',
							'label'	  => $this->language->get( 'entry_flickr' ),
							'default' => 0
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link_flickr',
							'label'	=> $this->language->get( 'entry_link_flickr' )
						),
						array(
							'type'	  => 'checkbox',
							'name'	  => 'vine',
							'label'	  => $this->language->get( 'entry_vine' ),
							'default' => 0
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link_vine',
							'label'	=> $this->language->get( 'entry_link_vine' )
						),
						array(
							'type'	=> 'text',
							'name'	=> 'content',
							'label'	=> $this->language->get( 'entry_content_text' ),
							'default' => '',
							'language' => true
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
				),
				'custom'			=> array(
					'label'			=> $this->language->get( 'entry_custom_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'icon_color',
							'label'	=> $this->language->get( 'entry_icon_color' ),
							'selector' => '.fa',
							'css_attr'	=> 'color'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'text_color',
							'label'	=> $this->language->get( 'entry_color_text' ),
							'selector' => 'label',
							'css_attr'	=> 'color'
						),
					)
				),
			)
		);
	}

	public function render( $settings = array(), $content = '' ){
		$settings = array_merge(  array(
			'style' => '',
			'size' => ''
		), $settings );

		$this->load->language( 'extension/module/pavothemer' );
		$args = 'extension/module/pavobuilder/pa_social_network/pa_social_network';
		return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
	}

}