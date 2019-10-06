<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Accordion extends PA_Widgets {

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-outdent',
				'label'	=> $this->language->get( 'entry_accordion' )
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
							'type'		=> 'iconpicker',
							'name'		=> 'icon_open',
							'label'		=> $this->language->get( 'entry_icon_open' ),
							'default'	=> ''
						),
						array(
							'type'		=> 'iconpicker',
							'name'		=> 'icon_close',
							'label'		=> $this->language->get( 'entry_icon_close' ),
							'default'	=> ''
						),
						array(
							'type'	=> 'group',
							'name'	=> 'items',
							'label'	=> $this->language->get( 'entry_item' ),
							'fields'	=> array(
								array(
									'type'		=> 'text',
									'name'		=> 'title',
									'label'		=> $this->language->get( 'entry_title_text' ),
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
						array(
							'type'	=> 'select',
							'name'	=> 'layout',
							'label'	=> $this->language->get( 'entry_layout_text' ),
							'default' => '',
							'options'	=> $this->getLayoutsOptions(),
							'none' => false
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
   		$language = $this->model_localisation_language->getLanguage( $this->config->get('config_language_id') );
   		$settings['get_items'] = array ();

   		if (!empty ($settings['items'])) {
			foreach ($settings['items'] as $value) {

				if (!empty ($value['languages'][$language['code']]['content'])) {
				$description = $value['languages'][$language['code']]['content'];
				$description = html_entity_decode( htmlspecialchars_decode( $description ), ENT_QUOTES, 'UTF-8' );
				}
				$settings['get_items'][] = array (
					'title'		=> isset($value['languages'][$language['code']]['title']) ? $value['languages'][$language['code']]['title'] : '',
					'content'	=> isset($description) ? $description : '',
				);
			}
		}

		$args = 'extension/module/pavobuilder/pa_accordion/pa_accordion';
		return $this->load->view( $args, array( 'settings' => $settings  ) );
	}

}