<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Custom_Testimonial extends PA_Widgets {

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-address-card-o',
				'label'	=> $this->language->get( 'entry_custom_testimonial' )
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
							'default' => 'false',
							'options'	=> $this->getLayoutsOptions(),
							'none' 	=> false
						),
						array(
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
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
							'type'	  => 'number',
							'name'    => 'item',
							'label'	  => $this->language->get( 'entry_item_text' ),
							'desc'    => $this->language->get( 'entry_item_desc_text' ),
							'default' => 4
						),
						array(
							'type'		=> 'number',
							'name'		=> 'rows',
							'label'		=> $this->language->get( 'entry_rows_text' ),
							'default'	=> 1,
							'min'	=> 1,
							'max' => 2
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
							)
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
							'type'		=> 'select',
							'name'		=> 'pagination',
							'label'		=> $this->language->get( 'entry_pagination_text' ),
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
							'name'		=> 'navigation',
							'label'		=> $this->language->get( 'entry_navigation_text' ),
							'default' 	=> 'false',
							'options'	=> array(
								array(
									'value'	=> 1,
									'label'	=> 'Enabled'
								),
								array(
									'value'	=> 0,
									'label'	=> 'Disabled'
								)
							),
							'none' => false
						),
						array(
							'type'	=> 'group',
							'name'	=> 'items',
							'label'	=> $this->language->get( 'entry_item' ),
							'fields'	=> array(
								array(
									'type'		=> 'text',
									'name'		=> 'name',
									'label'		=> $this->language->get( 'entry_name_text' ),
									'default'	=> '',
									'language'	=> true
								),
								array(
									'type'		=> 'text',
									'name'		=> 'note',
									'label'		=> $this->language->get( 'entry_note_text' ),
									'default'	=> '',
									'language'	=> true
								),
								array(
									'type'		=> 'text',
									'name'		=> 'title',
									'label'		=> $this->language->get( 'entry_title_text' ),
									'default'	=> '',
									'language'	=> true
								),
								array(
									'type'		=> 'text',
									'name'		=> 'subtitle',
									'label'		=> $this->language->get( 'entry_subtitle_text' ),
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
									'type'	=> 'image',
									'name'	=> 'src',
									'label'	=> $this->language->get( 'entry_image_text' )
								),
								array(
									'type'		=> 'select',
									'name'		=> 'rating',
									'label'		=> $this->language->get( 'entry_rating_text' ),
									'default' 	=> '',
									'options'	=> array(
										array(
											'value'	=> 1,
											'label'	=> '1'
										),
										array(
											'value'	=> 2,
											'label'	=> '2'
										),
										array(
											'value'	=> 3,
											'label'	=> '3'
										),
										array(
											'value'	=> 4,
											'label'	=> '4'
										),
										array(
											'value'	=> 5,
											'label'	=> '5'
										)
									)
								),
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
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$this->load->model( 'localisation/language' );
		$this->load->model('tool/image');
		$this->load->language( 'extension/module/pavobuilder' );
   		$language = $this->model_localisation_language->getLanguage( $this->config->get('config_language_id') );
   		$settings['get_items'] = array ();

   		if( defined("IMAGE_URL")){
            $server =  IMAGE_URL;
        } else {
            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }

   		if (!empty ($settings['items'])) {
			foreach ($settings['items'] as $value) {

				if (!empty ($value['languages'][$language['code']]['content'])) {
				$description = $value['languages'][$language['code']]['content'];
				$description = html_entity_decode( htmlspecialchars_decode( $description ), ENT_QUOTES, 'UTF-8' );
				}

				if ( ! empty( $value['src'] ) ) {
					$settings['image_size'] = strtolower( $settings['image_size'] );
					$src = empty( $settings['image_size'] ) || $settings['image_size'] == 'full' ? $server . $value['src'] : false;
					if ( strpos( $settings['image_size'], 'x' ) ) {
						$src = $this->getImageLink($value['src'], $settings['image_size']);
					}

					$value['src'] = $src ? $src : $value['src'];
				}

				$settings['get_items'][] = array (
					'name'		=> isset($value['languages'][$language['code']]['name']) ? $value['languages'][$language['code']]['name'] : '',
					'title'		=> isset($value['languages'][$language['code']]['title']) ? $value['languages'][$language['code']]['title'] : '',
					'note'		=> isset($value['languages'][$language['code']]['note']) ? $value['languages'][$language['code']]['note'] : '',
					'subtitle'		=> isset($value['languages'][$language['code']]['subtitle']) ? $value['languages'][$language['code']]['subtitle'] : '',
					'content'	=> isset($description) ? $description : '',
					'rating'	=> isset($value['rating']) ? $value['rating'] : 0,
					'image'		=> isset($value['src']) ? $value['src'] : ''
				);
			}
		}

		if (!empty($settings['layout'])) {
			$args = $this->renderLayout($settings['layout']);
		} else {
			$args = 'extension/module/pavobuilder/pa_custom_testimonial/pa_custom_testimonial';
		}
		$carouselOptions = array(
			'loop' => isset( $settings['loop'] ) && $settings['loop'] === 'true' ? true : false,
			'responsiveClass' => true,
			'items' => !empty($settings['item']) ? (int)$settings['item'] : 2,
			'rows' => !empty($settings['rows']) ? (int)$settings['rows'] : 1,
			'nav' => !empty($settings['navigation']) && $settings['navigation'],
			'dots' => !empty($settings['pagination']) && $settings['pagination'] === 'true',
			'autoplay' => !empty($settings['auto_play']) && $settings['auto_play'] == 'true' ? true : false,
			'autoplayTimeout' => !empty($settings['auto_play_time']) ? $settings['auto_play_time'] : 2000,
			'responsive' => array(
				0 => array(
					'items' => !empty($settings['mobile']) ? $settings['mobile'] : 1,
					'nav' => true
				),
				481 => array(
					'items' => !empty($settings['table']) ? $settings['table'] : 1,
					'nav' => true
				),
				769 => array(
					'items' => !empty($settings['item']) ? $settings['item'] : 1,
					'nav' => !empty($settings['navigation']) && $settings['navigation']
				)
			),
			'margin' => !empty($settings['margin']) ? (int)$settings['margin'] : 0,
			'stagePadding' => !empty($settings['padding']) ? $settings['padding'] : 0
		);
		$settings['carousel'] = $carouselOptions;
		return $this->load->view( $args, array( 'settings' => $settings  ) );
	}
}