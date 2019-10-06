<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Animated_Bar extends PA_Widgets {

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-align-left',
				'label'	=> $this->language->get( 'entry_animated_bar' )
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
							'type'		=> 'select',
							'name'		=> 'percent',
							'label'		=> $this->language->get( 'entry_percent' ),
							'default' 	=> 'true',
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
							'name'		=> 'style',
							'label'		=> $this->language->get( 'entry_styles_text' ),
							'default' 	=> 'default',
							'options'	=> array(
								array(
									'value'	=> 'default',
									'label'	=> 'Default'
								),
								array(
									'value'	=> 'striped',
									'label'	=> 'Striped'
								)
							)
						),
						array(
							'type'	=> 'group',
							'name'	=> 'items',
							'label'	=> $this->language->get( 'entry_item' ),
							'fields'	=> array(
								array(
									'type'	=> 'number',
									'name'	=> 'percent_int',
									'label'	=> $this->language->get( 'entry_percent' ),
									'step' 	=> 1,
									'min'	=> 1,
									'default' => 50
								),
								array(
									'type'	=> 'colorpicker',
									'name'	=> 'color',
									'label'	=> $this->language->get( 'entry_color' ),
									'default' => ''
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
   		$language = $this->model_localisation_language->getLanguage( $this->config->get('config_language_id') );
   		$settings['get_items'] = array ();

   		if (!empty ($settings['items'])) {
			foreach ($settings['items'] as $value) {
				if ($value['percent_int'] > 100) {
					$value['percent_int'] = 100;
				}
				$settings['get_items'][] = array (
					'title'		=> isset($value['languages'][$language['code']]['title']) ? $value['languages'][$language['code']]['title'] : '',
					'content'	=> isset($value['languages'][$language['code']]['content']) ? $value['languages'][$language['code']]['content'] : '',
					'percent'	=> isset($value['percent_int']) ? $value['percent_int'] : 0,
					'color'		=> isset($value['color']) ? $value['color'] : '',
				);
			}
		}
		// echo '<pre>'.print_r($settings['get_items'],1);die;
		$args = 'extension/module/pavobuilder/pa_animated_bar/pa_animated_bar';
		return $this->load->view( $args, array( 'settings' => $settings  ) );
	}

}