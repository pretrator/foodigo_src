<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Tab_Group extends PA_Widgets {

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-object-group',
				'label'	=> $this->language->get( 'entry_tab_group' )
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
							'name'	=> 'type',
							'label'	=> $this->language->get( 'entry_display_type' ),
							'default' => 'vertical',
							'options'	=> array(
								array(
									'value'	=> 'horizontal',
									'label'	=> 'Horizontal'
								),
								array(
									'value'	=> 'vertical',
									'label'	=> 'Vertical'
								)
							),
						),
						array(
							'type'	=> 'select',
							'name'	=> 'position',
							'label'	=> $this->language->get( 'entry_position' ),
							'default' => 'left',
							'options'	=> array(
								array(
									'value'	=> 'left',
									'label'	=> 'Left'
								),
								array(
									'value'	=> 'right',
									'label'	=> 'Right'
								)
							),
						),
						array(
							'type'	=> 'select',
							'name'	=> 'fade_effect',
							'label'	=> $this->language->get( 'entry_fade_effect' ),
							'default' => 'false',
							'options'	=> array(
								array(
									'value'	=> 'false',
									'label'	=> 'Disabled'
								),
								array(
									'value'	=> 'true',
									'label'	=> 'Enabled'
								)
							),
						),
						array(
							'type'	=> 'select',
							'name'	=> 'mouseover',
							'label'	=> $this->language->get( 'entry_open_on_mouseover' ),
							'default' => 'false',
							'options'	=> array(
								array(
									'value'	=> 'false',
									'label'	=> 'Disabled'
								),
								array(
									'value'	=> 'true',
									'label'	=> 'Enabled'
								)
							),
						),
						array(
							'type'	=> 'number',
							'name'	=> 'tab_active',
							'label'	=> $this->language->get( 'entry_tab_active' ),
							'default'       => '1',
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
									'name'	=> 'alt',
									'label'	=> $this->language->get( 'entry_alt_text' ),
									'default' => '',
									'desc'	=> $this->language->get( 'entry_alt_desc_text' )
								),
								array(
									'type'	=> 'text',
									'name'	=> 'name',
									'label'	=> $this->language->get( 'entry_name_text' ),
									'language'	=> true,
									'default' => ''
								),
								array(
									'type'	=> 'text',
									'name'	=> 'caption',
									'label'	=> $this->language->get( 'entry_caption_text' ),
									'language'	=> true,
									'default' => ''
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
									'type'	=> 'text',
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
		$this->load->model( 'tool/image' );
   		$language = $this->model_localisation_language->getLanguage( $this->config->get('config_language_id') );
		$settings['get_items'] = array ();

		if( defined("IMAGE_URL")){
            $server =  IMAGE_URL;
        } else  {
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

					$value['src'] = $src ? $src : '';
				}

				$settings['get_items'][] = array (
					'caption'	=> isset($value['languages'][$language['code']]['caption']) ? $value['languages'][$language['code']]['caption'] : '',
					'title'	=> isset($value['languages'][$language['code']]['title']) ? $value['languages'][$language['code']]['title'] : '',
					'subtitle'	=> isset($value['languages'][$language['code']]['subtitle']) ? $value['languages'][$language['code']]['subtitle'] : '',
					'name'		=> isset($value['languages'][$language['code']]['name']) ? $value['languages'][$language['code']]['name'] : '',
					'content'	=> isset($description) ? $description : '',
					'image'		=> isset($value['src']) ? $value['src'] : '',
					'alt'		=> isset($value['alt']) ? $value['alt'] : '',
				);
			}
		}
		
		$args = 'extension/module/pavobuilder/pa_tab_group/pa_tab_group';
		return $this->load->view( $args, array( 'settings' => $settings  ) );
	}
}