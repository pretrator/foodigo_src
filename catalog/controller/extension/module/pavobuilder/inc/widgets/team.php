<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Team extends PA_Widgets {

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-address-book-o',
				'label'	=> $this->language->get( 'entry_team_block' )
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
							'default' => 'roadmap',
							'options'	=> array(
								array(
									'value'	=> 'layout1',
									'label'	=> 'Layout 1'
								),
								array(
									'value'	=> 'layout2',
									'label'	=> 'Layout 2'
								),
								array(
									'value'	=> 'layout3',
									'label'	=> 'Layout 3'
								)
							),
							'mask'	=> true
						),
						array(
							'type'	=> 'image',
							'name'	=> 'src',
							'label'	=> $this->language->get( 'entry_image_text' )
						),
						array(
							'type'	=> 'text',
							'name'	=> 'image_size',
							'label'	=> $this->language->get( 'entry_image_size_text' ),
							'desc'	=> $this->language->get( 'entry_image_size_desc' ),
							'default'		=> '290x430',
							'placeholder'	=> '290x430',
							'mask'	=> false
						),
						array(
							'type'	=> 'text',
							'name'	=> 'name',
							'label'	=> $this->language->get( 'entry_name_text' ),
							'desc'	=> $this->language->get( 'entry_name_desc_text' ),
							'placeholder'	=> $this->language->get( 'entry_your_name' ),
							'mask'	=> true
						),
						array(
							'type'	=> 'text',
							'name'	=> 'subtitle',
							'label'	=> $this->language->get( 'entry_subtitle_text' ),
							'desc'	=> $this->language->get( 'entry_subtitle_desc_text' ),
							'placeholder'	=> 'Manager',
							'mask'	=> false
						),
						array(
							'type'		=> 'textarea',
							'name'		=> 'content',
							'label'		=> $this->language->get( 'entry_content_text' ),
							'default'	=> '',
							'cols'		=> 10,
							'language'	=> true,
							'mask'	=> false
						),
						array(
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' ),
							'mask'	=> false
						)
					)
				),
				'social'		=> array(
					'label'		=> $this->language->get( 'entry_social_text' ),
					'fields'	=> array(

						array(
							'type'	=> 'text',
							'name'	=> 'link_facebook',
							'label'	=> $this->language->get( 'entry_link_facebook' ),
							'desc'	=> $this->language->get( 'entry_desc_facebook' ),
							'default' => '#'
						),

						array(
							'type'	=> 'text',
							'name'	=> 'link_google_plus',
							'label'	=> $this->language->get( 'entry_link_google_plus' ),
							'default' => '#'
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link_twitter',
							'label'	=> $this->language->get( 'entry_link_twitter' ),
							'default' => '#'
						),

						array(
							'type'	=> 'text',
							'name'	=> 'link_youtube',
							'label'	=> $this->language->get( 'entry_link_youtube' ),
							'desc'	=> $this->language->get( 'entry_desc_youtube' ),

						),

						array(
							'type'	=> 'text',
							'name'	=> 'link_instagram',
							'label'	=> $this->language->get( 'entry_link_instagram' )
						),


						array(
							'type'	=> 'text',
							'name'	=> 'link_reddit',
							'label'	=> $this->language->get( 'entry_link_reddit' )
						),

						array(
							'type'	=> 'text',
							'name'	=> 'link_pinterest',
							'label'	=> $this->language->get( 'entry_link_pinterest' )
						),


						array(
							'type'	=> 'text',
							'name'	=> 'link_flickr',
							'label'	=> $this->language->get( 'entry_link_flickr' )
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link_vine',
							'label'	=> $this->language->get( 'entry_link_vine' )
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

		$default = array(
			'layout'      => 'layout1',
			'src'	      => 'http://via.placeholder.com/290x430',
			'name'   	  => '',
			'subtitle'    => '',
			'image_size'  => '',
			'content'     => '',
			'extra_class' => ''
		);
		$settings['content'] = ! empty( $settings ) && ! empty( $settings['content'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['content'] ), ENT_QUOTES, 'UTF-8' ) : '';

		$settings = array_merge( $default, $settings );


		if( empty( $settings['src'] ) ) {
			$settings['src'] = 'http://via.placeholder.com/290x430';
		} else {
			$settings['src'] = $this->getImageLink( $settings['src'], $settings['image_size'] );
		}

		return $this->load->view( 'extension/module/pavobuilder/pa_team/' . $settings['layout'], array( 'settings' => $settings, 'content' => $content ) );
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