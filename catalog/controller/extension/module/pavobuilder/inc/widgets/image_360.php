<?php
	/**
	 * @package Pavothemer for Opencart 3.x
	 * @version 1.0
	 * @author http://www.pavothemes.com
	 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
	 * @license		GNU General Public License version 2
	 */

class PA_Widget_Image_360 extends PA_Widgets {

	public function fields() {
		 
		$this->load->model('design/banner');
		$banners = $this->model_design_banner->getBanners();
		$banner = array();
		foreach ($banners as $value) {
			if ($value['status'] == 1) {
				$banner[] = array (
					'value'	=> $value['banner_id'],
					'label'	=> $value['name']
				);
			}
		}
		return array( 
			'mask'		=> array(
				'icon'	=> 'fa fa-refresh',
				'label'	=> $this->language->get( 'entry_360degree_banner' )
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
                            'type'  => 'text',
                            'name'  => 'extra_class',
                            'label' => $this->language->get( 'entry_extra_class_text' ),
                            'default' => '',
                            'desc'  => $this->language->get( 'entry_extra_class_desc_text' )
                        ),
                        array(
							'type'		=> 'select',
							'name'		=> 'banner_id',
							'label'		=> $this->language->get( 'entry_banner' ),
							'default' 	=> '',
							'options'	=> $banner,
						),
						array(
                            'type'  => 'text',
                            'name'  => 'banner_size',
                            'label' => $this->language->get( 'entry_banner_image_size' ),
                            'desc'  => $this->language->get( 'entry_banner_image_desc' ),
                            'default'       => 'full',
                            'placeholder'   => '200x400'
                        ),
                    	array(
                            'type'  => 'text',
                            'name'  => 'gallery_type',
                            'label' => $this->language->get( 'entry_gallery_type' ),
                            'desc'  => $this->language->get( 'entry_gallery_type_desc' ),
                            'default'       => '.png',
                            'placeholder'   => '.png'
                        ),
            			array(
							'type'		=> 'select',
							'name'		=> 'auto_spin',
							'label'		=> $this->language->get( 'entry_auto_spin' ),
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
                            'type'  => 'number',
                            'name'  => 'width',
                            'label' => $this->language->get( 'entry_width_text' ),
                            'default'       => '400',
                        ),
                        array(
                            'type'  => 'number',
                            'name'  => 'height',
                            'label' => $this->language->get( 'entry_height_text' ),
                            'default'       => '400',
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
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'color',
							'label'	=> 'entry_color_text'
						)
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
 		$this->load->model('design/banner');
		$this->load->model('tool/image');
		$this->document->addScript('catalog/view/javascript/threesixty/threesixty.min.js');
		$this->document->addStyle('catalog/view/javascript/threesixty/threesixty.css');
		$this->load->model( 'tool/image' );
		$this->load->model('extension/module/pavothemer');

		$settings['banners'] = array();
		$banners 		= $this->model_extension_module_pavothemer->getBanners();
		$banner_first 	= reset($banners);

		if (empty($settings['banner_id'])) {
			$settings['banner_id'] = $banner_first['banner_id'];
		}

 		if (!empty($settings['banner_id'])) {
 			$ban_id = 0;
 			foreach ($banners as $key => $value) {
 				if ($settings['banner_id'] == $value['banner_id']) {
					$ban_id = 1;
				}
			}
			
			if ($ban_id != 1) {
				$settings['banner_id'] = $banner_first['banner_id'];
			}
			
 			$get_banner = $this->model_design_banner->getBanner($settings['banner_id']);
 			foreach ($get_banner as $result) {

				if( defined("IMAGE_URL")){
		            $server =  IMAGE_URL;
		        } else  {
		            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
		        }
		        
		        if ( ! empty( $result['image'] ) ) {
		            $settings['banner_size'] = strtolower( $settings['banner_size'] );
		            $src = empty( $settings['banner_size'] ) || $settings['banner_size'] == 'full' ? $server . $result['image'] : false;

		            if ( strpos( $settings['banner_size'], 'x' ) ) {
		                $src = $this->getImageLink($result['image'], $settings['banner_size']);
		            }
		        
		            $result['image'] = $src ? $src : '';
		        }
				$settings['banners'][] = array(
					'image' => $result['image']
				);
			}
 		}
 			
		$args = 'extension/module/pavobuilder/pa_image_360/pa_image_360';
		return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
	}

}