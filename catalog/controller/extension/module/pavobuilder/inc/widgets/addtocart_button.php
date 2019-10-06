<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Addtocart_Button extends PA_Widgets {

	public function fields(){
		$this->load->model('catalog/product');
		$get_product = $this->model_catalog_product->getProducts();
		$products = array();
		foreach ($get_product as $cat_id) {
			$products[] = array(
				'value'	=> $cat_id['product_id'],
				'label'	=> $cat_id['name']
			);
		}
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-shopping-cart',
				'label'	=> $this->language->get( 'entry_addtocart_button' )
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
							'type'		=> 'text',
							'name'		=> 'title',
							'label'		=> $this->language->get( 'entry_title' ),
							'desc'		=> $this->language->get( 'entry_title_desc_text' ),
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
							'type'		=> 'text',
							'name'		=> 'button_text',
							'label'		=> $this->language->get( 'entry_banner_button' ),
							'default'	=> '',
							'language'	=> true
						),
						array(
							'type'	=> 'select',
							'name'	=> 'quantity',
							'label'	=> $this->language->get( 'entry_quantity_text' ),
							'default' => 'false',
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
						),
						array(
							'type'		=> 'text',
							'name'		=> 'quantity_text',
							'label'		=> $this->language->get( 'entry_quantity_text' ),
							'default'	=> '',
							'language'	=> true
						),
						array(
							'type'		=> 'select',
							'name'		=> 'products',
							'label'		=> $this->language->get( 'entry_product' ),
							'default' 	=> '',
							'options'	=> $products,
						),
						array(
							'type'		=> 'iconpicker',
							'name'		=> 'icon',
							'label'		=> $this->language->get( 'entry_icon_text' ),
							'default'	=> ''
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
		$this->load->model('catalog/product');

		$settings['content'] = ! empty( $settings ) && ! empty( $settings['content'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['content'] ), ENT_QUOTES, 'UTF-8' ) : '';

		if (!empty ($settings['products'])) {
			$get_products = $this->model_catalog_product->getProduct($settings['products']);

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($get_products['price'], $get_products['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $price = false;
            }

            if ((float)$get_products['special']) {
                $special = $this->currency->format($this->tax->calculate($get_products['special'], $get_products['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                $discount = floor((($get_products['price']-$get_products['special'])/$get_products['price'])*100);
            } else {
                $special = false;
            }

            if ($this->config->get('config_tax')) {
                $tax = $this->currency->format((float)$get_products['special'] ? $get_products['special'] : $get_products['price'], $this->session->data['currency']);
            } else {
                $tax = false;
            }

			$settings['product_price'] 		= $price;
			$settings['product_special'] 	= $special;
			$settings['product_tax'] 		= $tax;
		}

		$args = 'extension/module/pavobuilder/pa_addtocart_button/pa_addtocart_button';
		return $this->load->view( $args, array( 'settings' => $settings  ) );
	}
}