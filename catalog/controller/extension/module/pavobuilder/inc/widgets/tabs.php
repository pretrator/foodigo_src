<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Tabs extends PA_Widgets {

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-object-group',
				'label'	=> $this->language->get( 'entry_tab_element' )
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
							'type'	=> 'text',
							'name'	=> 'title',
							'label'	=> $this->language->get( 'entry_title_text' ),
							'placeholder' => $this->language->get( 'entry_title_desc_text' ),
							'language' => true
						),
						array(
							'type' => 'select',
							'name' => 'use_icon',
							'label' => $this->language->get( 'entry_use_icon_text' ),
							'options' => array(
								array( 'label'	=> $this->language->get('entry_enable_text'), 'value' => 1 ),
								array( 'label'	=> $this->language->get('entry_disable_text'), 'value' => 1 )
							),
							'none' => false
						),
						array(
							'type' => 'iconpicker',
							'name' => 'icon',
							'label' => $this->language->get( 'entry_icon_text' )
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

	public function render($settings = array(), $content = null, $data = array()) {
		$tabs = !empty($data['tabs']) ? $data['tabs'] : array();
		$settings['tabs'] = array();
		$code = $this->session->data['language'];
		foreach ( $tabs as $key => $tab ) {
			$rowSettings = !empty($tab['settings']) ? $tab['settings'] : array();
			$row = !empty($tab['row']) ? $tab['row'] : array();
			foreach ( $rowSettings as $name => $value ) {
				if (!in_array($name, array( 'uniqid_id', 'selectors' ))) {
					if ( $name === $code ) {
						foreach ( $value as $n => $v ) {
							$rowSettings[$n] = $v;
						}
					} else {
						$rowSettings[$name] = $value;
					}
				}
			}
			$tab['settings'] = $rowSettings;
			$builder = new ControllerExtensionModulePavoBuilder($this->registry);
			ob_start();
			// echo $this->load->controller( 'extenstion/module/pavobuilder/renderElement', $row );
			echo $builder->renderElement($row);
			$tab['row'] = ob_get_clean();
			$settings['tabs'][$key] = $tab;
		}
		return $this->load->view( 'extension/module/pavobuilder/pa_tabs/pa_tabs', array( 'settings' => $settings ) );
	}
}