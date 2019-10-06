<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Countdown extends PA_Widgets {

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-hourglass-half',
				'label'	=> $this->language->get( 'entry_countdown_text' )
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
							'default' => 'layout-none',
							'options'	=> array(
								array(
									'value'	=> 'layout-none',
									'label'	=> 'Default'
								),
								array(
									'value'	=> 'layout-1',
									'label'	=> 'Layout 1'
								),
								array(
									'value'	=> 'layout-2',
									'label'	=> 'Layout 2'
								),
								array(
									'value'	=> 'layout-3',
									'label'	=> 'Layout 3'
								)
							),
							'mask'	=> true
						),

						array(
							'type'	=> 'select',
							'name'	=> 'size',
							'label'	=> $this->language->get( 'entry_size_text' ),
							'default' => 'size-lg',
							'options'	=> array(
								array(
									'value'	=> 'size-lg',
									'label'	=> 'Default'
								),
								array(
									'value'	=> 'size-sm',
									'label'	=> 'Size Small'
								)
							)
						),
						array(
							'type'	=> 'image',
							'name'	=> 'src',
							'label'	=> $this->language->get( 'entry_image_text' )
						),
						array(
							'type'	=> 'datepicker',
							'name'	=> 'end_date',
							'mask'	=> true,
							'label'	=> $this->language->get( 'entry_endate_text' ),
							'default' => '',
							'desc'	=> $this->language->get( 'entry_endate_desc' )
						),
						array(
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'default' => '',
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' )
						)
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {

		$this->load->language('extension/module/pavobuilder');
		
		$this->document->addScript('catalog/view/javascript/jquery.countdown.min.js' );
		$default = array_merge( array(
			'end_date' => '12/14/2020',
			'date_start' => '',
			'auto_repeat_days' => 6, 
			'src' => '',
			'size' => 'size-lg'
		) , $settings );
		if( isset( $settings['src'] ) ){
			$settings['src'] = $this->getImageLink( $settings['src'], 'full' );
		}
	 
		return $this->load->view( 'extension/module/pavobuilder/pa_countdown/pa_countdown', array( 'settings' => $settings ) );
	}

}