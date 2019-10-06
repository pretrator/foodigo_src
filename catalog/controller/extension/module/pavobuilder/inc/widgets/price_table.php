<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Price_Table extends PA_Widgets {

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-usd',
				'label'	=> $this->language->get( 'entry_price_table' ),
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
							'default' => 'layout1',
							'options'	=> $this->getLayoutsOptions(),
							'none' 	=> false
						),
						array(
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' ),
							'mask'	=> true
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
							'type'		=> 'editor',
							'name'		=> 'content',
							'label'		=> $this->language->get( 'entry_content_text' ),
							'default'	=> '',
							'language'	=> true
						),
						array(
							'type'	=> 'text',
							'name'	=> 'price_plan',
							'label'	=> $this->language->get( 'entry_price_plan' ),
							'language'	=> true,
							'default' => ''
						),
						array(
							'type'	=> 'text',
							'name'	=> 'offer',
							'label'	=> $this->language->get( 'entry_offer_text' ),
							'language'	=> true,
							'default' => ''
						),
						array(
                            'type'    => 'text',
                            'name'    => 'button_text',
                            'label'   => $this->language->get( 'entry_banner_button' ),
                            'default' => '',
                            'language'=> true
                        ),
                        array(
	                        'type'  => 'text',
                            'name'  => 'button_link',
                            'label' => $this->language->get( 'entry_button_link' ),
                            'desc'  => $this->language->get( 'entry_banner_link_desc' ),
                            'default'       => ''
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
							'type'	=> 'select',
							'name'	=> 'price',
							'label'	=> $this->language->get( 'entry_price_text' ),
							'default' => 'true',
							'options'	=> array(
								array(
									'value'	=> 'true',
									'label'	=> 'Show'
								),
								array(
									'value'	=> 'false',
									'label'	=> 'Hide'
								)
							),
						),
						array(
							'type'	=> 'text',
							'name'	=> 'currency',
							'label'	=> $this->language->get( 'entry_currency_block' ),
							'default' => ''
						),
						array(
							'type'	=> 'number',
							'name'	=> 'amount',
							'label'	=> $this->language->get( 'entry_amount_text' ),
							'default' => ''
						),
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$settings['content'] = ! empty( $settings ) && ! empty( $settings['content'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['content'] ), ENT_QUOTES, 'UTF-8' ) : '';

		if (!empty($settings['layout'])) {
			$args = $this->renderLayout($settings['layout']);
		} else {
			$args = 'extension/module/pavobuilder/pa_price_table/layout1';
		}

		return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
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