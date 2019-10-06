<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_MiniCart extends PA_Widgets {
	public $header =  true;
	public function fields(){
		
		$options = array(
			array(
				'value'	=> '',
				'label'	=> $this->language->get( 'text_style_default' )
			)
		);

		$files = glob(  DIR_CATALOG . 'view/theme/' .  $this->config->get( 'config_theme' ) . '/template/common/cart/*.twig' );
	 	if( $files ){
	 		foreach ($files as $file ) {
	 			$name  = str_replace('.twig','',basename( $file ) ); 
	 			$options[] = array(
					'value'	=> $name,
					'label'	=> ucfirst( $name )
				);
	 		}
	 	}

		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-file-text',
				'label'	=> $this->language->get( 'entry_minicart_block' ),
			),
			'tabs'	=> array(
				'general'		=> array(
					'label'		=> $this->language->get( 'entry_general_text' ),
					'fields'	=> array(
						array(
							'type'	=> 'hidden',
							'name'	=> 'uniqid_id',
							'label'	=> $this->language->get( 'entry_column_id_text' ),
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
							'name'	=> 'style',
							'label'	=> $this->language->get( 'entry_style_text' ),
							'default' => 'opencart',
							'mask'	=> true,
							'options'	=>  $options
						),
						array(
							'type'		=> 'select',
							'name'		=> 'layout_style',
							'label'		=> $this->language->get( 'entry_layout_style' ),
							'default' 	=> '',
							'options'	=> array(
								array(
									'value'	=> 'layout1',
									'label'	=> 'Layout 1'
								),
								array(
									'value'	=> 'layout2',
									'label'	=> 'Layout 2'
								),
							)
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'text_color',
							'label'	=> $this->language->get( 'entry_color_text' ),
							'default'	=> ''
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'link_color',
							'label'	=> $this->language->get( 'entry_text_link_text' ),
							'default'	=> ''
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'icon_color',
							'label'	=> $this->language->get( 'entry_icon_color' ),
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
				),
				'custom'			=> array(
					'label'			=> $this->language->get( 'entry_custom_text' ),
					'fields'		=> array(
						array(
							'type'		=> 'text',
							'name'		=> 'hfont_size',
							'label'		=> $this->language->get( 'entry_font_size' ),
							'default'	=> '',
                            'css_attr'  => 'font-size',
							'suffix' 	=> 'px',
                            'selector'  => '.mini-cart .cart-inner .cart-head',
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'icon_color',
							'label'	=> $this->language->get( 'entry_icon_color' ),
							'selector' => '.icon',
							'css_attr'	=> 'color'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'text_link_color',
							'label'	=> $this->language->get( 'entry_text_link_text' ),
							'selector' => 'span.cart-head',
							'css_attr'	=> 'color'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'text_color',
							'label'	=> $this->language->get( 'entry_color_text' ),
							'selector' => 'span.pav-cart-total',
							'css_attr'	=> 'color'
						)
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		
		$settings = array_merge(  array(
	 		'style' => ''
	 	) , $settings );
	 	
	 	if( $settings['style'] == 'default'	){
	 		$style = '';
	 	}else {
	 		$style = '/'.$settings['style'];
	 	}

	 	$settings['extra_class'] = isset($settings['extra_class']) ? $settings['extra_class'] : '';
	 	$settings['layout_style'] = isset($settings['layout_style']) ? $settings['layout_style'] : '';

	 	$settings['font'] = array (
	 		'text_color'	=> isset($settings['text_color']) ? $settings['text_color'] : '',
	 		'link_color'	=> isset($settings['link_color']) ? $settings['link_color'] : '',
	 		'icon_color'	=> isset($settings['icon_color']) ? $settings['icon_color'] : '',
	 	);

	 	return $this->index( $style, $settings['extra_class'], $settings['layout_style'], $settings['font'] ); 
	}

	public function index( $layout, $extra_class, $layout_style, $font ) {
		$this->load->language('common/cart');

		// Totals
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);
			
		// Display prices
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);
		}

		$data['text_items'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		$data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
			} else {
				$image = '';
			}

			$option_data = array();

			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = array(
					'name'  => $option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
					'type'  => $option['type']
				);
			}

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
				
				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
			} else {
				$price = false;
				$total = false;
			}

			$data['products'][] = array(
				'cart_id'   => $product['cart_id'],
				'thumb'     => $image,
				'name'      => $product['name'],
				'model'     => $product['model'],
				'option'    => $option_data,
				'recurring' => ($product['recurring'] ? $product['recurring']['name'] : ''),
				'quantity'  => $product['quantity'],
				'price'     => $price,
				'total'     => $total,
				'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
			);
		}

		// Gift Voucher
		$data['vouchers'] = array();

		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $key => $voucher) {
				$data['vouchers'][] = array(
					'key'         => $key,
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
				);
			}
		}

		$data['totals'] = array();

		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency']),
			);
		}

		$data['cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['extra_class'] = isset($extra_class) ? $extra_class : '';
		$data['layout_style'] = isset($layout_style) ? $layout_style : '';

		$data['text_color']  = isset($font['text_color']) ? $font['text_color'] : '';
		$data['link_color']  = isset($font['link_color']) ? $font['link_color'] : '';
		$data['icon_color']  = isset($font['icon_color']) ? $font['icon_color'] : '';
		
		if( $layout == '/' ){
			$layout = '';
		}
		return $this->load->view('common/cart'.$layout, $data);
	}

	/**
	 * s fields
	 */
	public function validate( $settings = array() ) {
		$language_id = $this->config->get('config_language_id');
	 
		return $settings;
	}

}