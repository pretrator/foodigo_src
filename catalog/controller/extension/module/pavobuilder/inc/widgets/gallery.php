<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Gallery extends PA_Widgets {

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-picture-o',
				'label'	=> $this->language->get( 'entry_gallery_text' )
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
							'name'	=> 'image_size',
							'label'	=> $this->language->get( 'entry_image_size_text' ),
							'desc'	=> $this->language->get( 'entry_image_size_desc' ),
							'default'		=> 'full',
							'placeholder'	=> '200x400'
						),
						array(
							'type'	=> 'select',
							'name'	=> 'display_type',
							'label'	=> $this->language->get( 'entry_display_type' ),
							'default' => 'grid',
							'options'	=> array(
								array(
									'value'	=> 'grid',
									'label'	=> 'Grid'
								),
								array(
									'value'	=> 'slide',
									'label'	=> 'Slide'
								)
							),
						),
						array(
							'type'		=> 'rangeslider',
							'name'		=> 'columns',
							'label'		=> $this->language->get( 'text_columns_images' ),
							'min'		=> 1,
							'max'		=> 10,
							'double'	=> false,
							'grid'		=> true,
							'default'	=> 6,
							'mask'		=> true
						),
						array(
							'type'	  => 'number',
							'name'    => 'tablet',
							'label'	  => $this->language->get( 'entry_table_screens' ),
							'default' => 2
						),
						array(
							'type'	  => 'number',
							'name'    => 'mobile',
							'label'	  => $this->language->get( 'entry_mobile_screens' ),
							'default' => 1
						),
						array(
							'type'		=> 'rangeslider',
							'name'		=> 'rows',
							'label'		=> $this->language->get( 'text_rows_images' ),
							'min'		=> 1,
							'max'		=> 10,
							'double'	=> false,
							'grid'		=> true,
							'default'	=> 6,
							'mask'		=> true
						),
						array(
							'type'	=> 'group',
							'name'	=> 'items',
							'label'	=> $this->language->get( 'entry_item' ),
							'fields'	=> array(
								array(
									'type'	=> 'image',
									'name'	=> 'src',
									'label'	=> $this->language->get( 'entry_image_text' )
								),
								array(
									'type'	=> 'text',
									'name'	=> 'text_image',
									'label'	=> $this->language->get( 'entry_text_image' ),
									'default'	=> '',
									'language'	=> true
								),
								array(
									'type'	=> 'text',
									'name'	=> 'link',
									'label'	=> $this->language->get( 'entry_link_text' ),
									'default'		=> '',
									'desc'	=> $this->language->get( 'entry_link_desc_text' )
								),
								array(
									'type'	=> 'text',
									'name'	=> 'alt',
									'label'	=> $this->language->get( 'entry_alt_text' ),
									'default' => '',
									'desc'	=> $this->language->get( 'entry_alt_desc_text' )
								),
							)
						),
						array(
							'type'		=> 'select',
							'name'		=> 'nav',
							'label'		=> $this->language->get( 'entry_navigation_text' ),
							'default' 	=> 'false',
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
							'none' => false
						),
						array(
							'type'		=> 'select',
							'name'		=> 'loop',
							'label'		=> $this->language->get( 'entry_loop' ),
							'desc'		=> $this->language->get( 'entry_loop_desc' ),
							'default' 	=> 'false',
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
							'none' => false
						),
						array(
							'type'		=> 'select',
							'name'		=> 'auto_play',
							'label'		=> $this->language->get( 'entry_auto_play' ),
							'default' 	=> 'false',
							'options'	=> array(
								array(
									'value'	=> 'true',
									'label'	=> 'Enabled'
								),
								array(
									'value'	=> 'false',
									'label'	=> 'Disabled'
								)
							)
						),
						array(
							'type'	  => 'number',
							'name'	  => 'auto_play_time',
							'label'	  => $this->language->get( 'entry_auto_play_time' ),
							'default' => 5000
						),
						array(
							'type'	  => 'number',
							'name'	  => 'padding',
							'label'	  => $this->language->get( 'entry_slide_padding' ),
							'default' => 0
						),
						array(
							'type'	  => 'number',
							'name'	  => 'margin',
							'label'	  => $this->language->get( 'entry_slide_margin' ),
							'default' => 0
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
						),
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$this->load->model( 'localisation/language' );
		$this->load->model( 'tool/image' );
		$language = $this->model_localisation_language->getLanguage( $this->config->get('config_language_id') );
		$settings['language_code'] = $language['code'];
		$default = array(
			'image_size'  => '',
			'extra_class' => '',
			'columns'	  => 6,
			'items'		  => array()
		);

		if ( defined("IMAGE_URL")) {
            $server =  IMAGE_URL;
        } else  {
            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }

		$settings['get_item'] = array();
		$settings = array_merge( $default, $settings );
		if ( ! empty( $settings['items'] ) ) {
			foreach ( $settings['items'] as $k => $item ) {
				if ( empty( $item['src'] ) ) {
					$item['src'] = 'http://via.placeholder.com/290x430';
				} else {
					$settings['image_size'] = strtolower( $settings['image_size'] );
		            $src = empty( $settings['image_size'] ) || $settings['image_size'] == 'full' ? $server . $item['src'] : false;

		            if ( strpos( $settings['image_size'], 'x' ) ) {
		                $src = $this->getImageLink($item['src'], $settings['image_size']);
		            }

		            $item['src'] = $src ? $src : '';
				}
				$item['alt'] = ! empty( $item['alt'] ) ? html_entity_decode( htmlspecialchars_decode( $item['alt'] ), ENT_QUOTES, 'UTF-8' ) : '';
				$settings['get_item'][] = $item;
			}
		}

		$carouselOptions = array(
			'loop' => isset( $settings['loop'] ) && $settings['loop'] === 'true',
			'responsiveClass' => true,
			'items' => !empty($settings['columns']) ? (int)$settings['columns'] : 2,
			'rows' => !empty($settings['rows']) ? (int)$settings['rows'] : 1,
			'nav' => !empty($settings['nav']) && $settings['nav'] === 'true',
			'dots' => !empty($settings['pagination']) && $settings['pagination'] === 'true',
			'autoplay' => !empty($settings['auto_play']) && $settings['auto_play'] == 'true',
			'autoplayTimeout' => !empty($settings['auto_play_time']) ? $settings['auto_play_time'] : 2000,
			'responsive' => array(
				0 => array(
					'items' => !empty($settings['mobile']) ? $settings['mobile'] : 1,
					'nav' => true
				),
				767 => array(
					'items' => !empty($settings['tablet']) ? $settings['tablet'] : 1,
					'nav' => true
				),
				991 => array(
					'items' => !empty($settings['columns']) ? $settings['columns'] : 1,
					'nav' => !empty($settings['nav']) && $settings['nav'] === 'true'
				)
			),
			'margin' => !empty($settings['margin']) ? (int)$settings['margin'] : 0,
			'stagePadding' => !empty($settings['padding']) ? $settings['padding'] : 0
		);
		$settings['carousel'] = $carouselOptions;
		return $this->load->view( 'extension/module/pavobuilder/pa_gallery/pa_gallery', array( 'settings' => $settings ) );
	}

}