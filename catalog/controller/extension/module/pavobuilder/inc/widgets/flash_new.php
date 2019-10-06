<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Flash_New extends PA_Widgets {
	public $header = true;
	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-bolt',
				'label'	=> $this->language->get( 'entry_flash_new' )
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
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' ),
							'default'	=> ''
						),
						array(
							'type'	=> 'group',	
							'name'	=> 'items',
							'label'	=> $this->language->get( 'entry_item' ),
							'fields'	=> array(
								array(
									'type'		=> 'editor',
									'name'		=> 'text_link',
									'label'		=> $this->language->get( 'entry_text_link' ),
									'default'	=> '',
									'language'	=> true
								),
								array(
									'type'		=> 'text',
									'name'		=> 'url_link',
									'label'		=> $this->language->get( 'entry_url_link' ),
									'desc'		=> $this->language->get( 'entry_link_desc_text' ),
									'default'	=> '',
								)
							)
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
		$this->load->model( 'localisation/language' );
		
		//$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.min.js');

   		$language = $this->model_localisation_language->getLanguage( $this->config->get('config_language_id') );
   		$settings['language_code'] = $language['code'];
		$settings['get_language'] = array ();
		foreach ($settings['items'] as $lang) {
			$settings['get_language'][] = array (
				'language' => $lang['languages'][$language['code']],
				'link'	   => $lang['url_link']
			);
		}
		return $this->load->view( 'extension/module/pavoheader/flash_new', array( 'settings' => $settings, 'content' => $content ) );
	}
}