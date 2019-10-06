<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Single_Image extends PA_Widgets {

	public $header = true;

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-picture-o',
				'label'	=> $this->language->get( 'entry_single_image_text' )
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
							'default' => 'pa_single_image',
							'options'	=> $this->getLayoutsOptions(),
							'none' 	=> false
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
							'placeholder'	=> '200x400'
						),
						array(
							'type'	=> 'text',
							'name'	=> 'link',
							'label'	=> $this->language->get( 'entry_link_text' ),
							'default'		=> '#',
							'desc'	=> $this->language->get( 'entry_link_desc_text' ),
							'mask'	=> true
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
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'default' => '',
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
				$this->load->model( 'tool/image' );
				$src = $this->getImageLink($settings['src'], $settings['image_size']);
			}

			$settings['src'] = $src ? $src : '';
		}

		if (!empty($settings['layout'])) {
			$args = $this->renderLayout($settings['layout']);
		} else {
			$args = 'extension/module/pavobuilder/pa_single_image/pa_single_image';
		}
		
		return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
	}

}