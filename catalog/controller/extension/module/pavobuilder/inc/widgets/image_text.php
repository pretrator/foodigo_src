<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Image_Text extends PA_Widgets {

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-file-image-o',
				'label'	=> $this->language->get( 'entry_widget_image_text' )
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
							'default' => 'layout1',
							// 'multiple'	=> true,
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
							)
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
							'default'		=> 'full',
							'placeholder'	=> '200x200'
						),
						array(
							'type'	=> 'text',
							'name'	=> 'alt',
							'label'	=> $this->language->get( 'entry_alt_text' ),
							'default' => '',
							'desc'	=> $this->language->get( 'entry_alt_desc_text' )
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link',
							'label'	=> $this->language->get( 'entry_link_text' ),
							'default' => '',
							'desc'	=> $this->language->get( 'entry_link_desc_text' ),
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
							'type'	=> 'editor',
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
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' )
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
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$this->load->model( 'tool/image' );
		$class = array();
		if ( ! empty( $settings['extra_class'] ) ) {
			$class[] = $settings['extra_class'];
		}
		if ( ! empty( $settings['effect'] ) ) {
			$class[] = $settings['effect'];
		}

		$settings['class'] = implode( ' ', $class );

		if( defined("IMAGE_URL")){
            $server =  IMAGE_URL;
        } else  {
            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }

		if ( ! empty( $settings['src'] ) ) {
			$settings['image_size'] = strtolower( $settings['image_size'] );
			$src = empty( $settings['image_size'] ) || $settings['image_size'] == 'full' ? $server . $settings['src'] : false;

			if ( strpos( $settings['image_size'], 'x' ) ) {
				$src = $this->getImageLink($settings['src'], $settings['image_size']);
			}

			$settings['src'] = $src ? $src : '';
		}

		$default = array(
			'layout'      => 'layout1',
			'title'   	  => '',
			'subtitle'    => '',
			'content'     => '',
			'extra_class' => ''
		);

		$settings = array_merge( $default, $settings );

		$settings['content'] 	= ! empty( $settings['content'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['content'] ), ENT_QUOTES, 'UTF-8' ) : '';
		$settings['title']      = ! empty( $settings['title'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['title'] ), ENT_QUOTES, 'UTF-8' ) : '';
		$settings['subtitle']   = ! empty( $settings['subtitle'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['subtitle'] ), ENT_QUOTES, 'UTF-8' ) : '';
		
		return $this->load->view( 'extension/module/pavobuilder/pa_image_text/' . $settings['layout'], 
					array( 'settings' => $settings  ) );
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