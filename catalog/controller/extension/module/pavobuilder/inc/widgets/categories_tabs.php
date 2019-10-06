<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Categories_Tabs extends PA_Widgets {

	public function fields() {
		$this->load->language('extension/module/pavproductcategory');
		$this->load->model('catalog/category');
		$get_category = $this->model_catalog_category->getCategories();
		$categories = array();
		foreach ($get_category as $cat_id) {
			$categories[] = array(
				'value'	=> $cat_id['category_id'],
				'label'	=> $cat_id['name']
			);
		}
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-product-hunt',
				'label'	=> $this->language->get( 'entry_category_tab' )
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
							'default' => 'pa_product_category',
							'options'	=> $this->getLayoutsOptions(),
							'none' 	=> false
						),
						array(
                            'type'  => 'text',
                            'name'  => 'extra_class',
                            'label' => $this->language->get( 'entry_extra_class_text' ),
                            'default' => '',
                            'desc'  => $this->language->get( 'entry_extra_class_desc_text' )
                        ),
                        array(
							'type'	=> 'group',
							'name'	=> 'categories',
							'label'	=> $this->language->get( 'entry_item' ),
							'fields'	=> array(
								array(
									'type'		=> 'select',
									'name'		=> 'category',
									'label'		=> $this->language->get( 'entry_title_text' ),
									'default'	=> '',
									'options'	=> $categories
								),
								array(
									'type'		=> 'image',
									'name'		=> 'image',
									'label'		=> $this->language->get( 'entry_image_text' ),
									'default'	=> ''
								),
								array(
									'type'		=> 'text',
									'name'		=> 'description',
									'label'		=> $this->language->get( 'entry_description_text' ),
									'default'	=> '',
									'language'	=> true
								)
							)
						),
						array(
							'type' => 'select',
							'name' => 'sort',
							'label' => $this->language->get('entry_order_by'),
							'options' => array(
								array(
									'value' => 'p.product_id',
									'label' => 'Product ID'
								),
								array(
									'value' => 'p.price',
									'label' => 'Product Price'
								),
								array(
									'value' => 'pd.name',
									'label' => 'Product Description'
								)
							)
						),
						array(
							'type' => 'select',
							'name' => 'order',
							'label' => $this->language->get('entry_order_text'),
							'options' => array(
								array(
									'value' => 'DESC',
									'label' => 'DESC'
								),
								array(
									'value' => 'ASC',
									'label' => 'ASC'
								)
							)
						),
						array(
							'type'	=> 'text',
							'name'	=> 'cat_image_size',
							'label'	=> $this->language->get( 'entry_category_image_size_text' ),
							'desc'	=> $this->language->get( 'entry_image_size_desc' ),
							'default'		=> 'full',
							'placeholder'	=> '200x400'
						),
						array(
							'type'	=> 'text',
							'name'	=> 'product_image_size',
							'label'	=> $this->language->get( 'entry_product_image_size_text' ),
							'desc'	=> $this->language->get( 'entry_image_size_desc' ),
							'default'		=> 'full',
							'placeholder'	=> '200x400'
						),
						array(
							'type'	  => 'number',
							'name'    => 'item',
							'label'	  => $this->language->get( 'entry_item_text' ),
							'desc'    => $this->language->get( 'entry_item_desc_text' ),
							'default' => 4
						),
						array(
							'type'		=> 'number',
							'name'		=> 'rows',
							'label'		=> $this->language->get( 'entry_rows_text' ),
							'default'	=> 1,
							'min'	=> 1,
							'max' => 2
						),
						array(
							'type'		=> 'number',
							'name'		=> 'limit',
							'label'		=> $this->language->get( 'entry_limit_text' ),
							'default'	=> 7
						),
						array(
							'type'		=> 'select',
							'name'		=> 'loop',
							'label'		=> $this->language->get( 'entry_loop' ),
							'desc'		=> $this->language->get( 'entry_loop_desc' ),
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
							'type'		=> 'select',
							'name'		=> 'auto_play',
							'label'		=> $this->language->get( 'entry_auto_play' ),
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
							'type'	  => 'number',
							'name'	  => 'auto_play_time',
							'label'	  => $this->language->get( 'entry_auto_play_time' ),
							'default' => 5000
						),
						array(
							'type'		=> 'select',
							'name'		=> 'pagination',
							'label'		=> $this->language->get( 'entry_pagination_text' ),
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
							),
							'none' => false
						),
						array(
							'type'		=> 'select',
							'name'		=> 'nav',
							'label'		=> $this->language->get( 'entry_navigation_text' ),
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
							),
							'none' => false
						),
						array(
							'type'	  => 'number',
							'name'    => 'desktop',
							'label'	  => $this->language->get( 'entry_desktop_screens' ),
							'default' => 4
						),
						
						array(
							'type'	  => 'number',
							'name'    => 'tablet',
							'label'	  => $this->language->get( 'entry_table_screens' ),
							'default' => 2
						),
						array(
							'type'	  => 'number',
							'name'    => 'mobile',
							'label'	  => $this->language->get( 'entry_mobile_screens' ),
							'default' => 1
						),
						array(
							'type'	  => 'number',
							'name'	  => 'padding',
							'label'	  => $this->language->get( 'entry_slide_padding' ),
							'default' => 0
						),
						array(
							'type'	  => 'number',
							'name'	  => 'margin',
							'label'	  => $this->language->get( 'entry_slide_margin' ),
							'default' => 0
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
		$settings = array_merge(array(
			'extra_class' 		 => '',
			'cat_image_size'     => '100x150',
			'product_image_size' => '250x300',
			'categories'		 => array(),
			'item'				 => 4,
			'rows'				 => 1,
			'limit' 			 => 5,
			'order'              => 'DESC',
			'sort'               => 'p.price',
			'loop'               => false,
		    'auto_play' 		 => false,
		    'auto_play_time' 	 => 5000
		), $settings);

		$this->load->model('catalog/product');
		$this->load->model('catalog/category');

		$code = $this->session->data['language'];
		$categories = array();
		if (!empty($settings['categories'])) {
			foreach ($settings['categories'] as $item) {

				$category['category_id'] = !empty($item['category']) ? $item['category'] : false;
				$get_cat_infor = $this->model_catalog_category->getCategory($category['category_id']);
				$category['name'] = $get_cat_infor['name'];
				$category['image'] = !empty($item['image']) ? $this->getImageLink($item['image'], $settings['cat_image_size']) : '';

				foreach ( $item['languages'] as $c => $lang) {
					if ($c == $code) {
						foreach ($lang as $description => $value) {
							$category[$description] = html_entity_decode($value);
						}
					}
				}
				$products = $this->model_catalog_product->getProducts(array(
					'filter_category_id' => $category['category_id'],
					'sort' => $settings['sort'],
					'order' => $settings['order'],
					'start' => 0,
					'limit' => $settings['limit']
				));
				$category['products'] = $this->getProducts($products, $settings['product_image_size']);
				$categories[] = $category;
			}
		}

		$settings['categories'] = $categories;
		$template = 'extension/module/pavobuilder/pa_categories_tabs/pa_categories_tabs';

		$settings['random_id'] = md5(uniqid());
		$carouselOptions = array(
			'loop' => isset( $settings['loop'] ) && $settings['loop'] === 'true',
			'responsiveClass' => true,
			'items' => !empty($settings['item']) ? (int)$settings['item'] : 2,
			'rows' => !empty($settings['rows']) ? (int)$settings['rows'] : 1,
			'nav' => !empty($settings['nav']) && $settings['nav'] === 'true',
			'dots' => !empty($settings['pagination']) && $settings['pagination'] === 'true',
			'autoplay' => !empty($settings['auto_play']) && $settings['auto_play'] == 'true',
			'autoplayTimeout' => !empty($settings['auto_play_time']) ? $settings['auto_play_time'] : 2000,
			'responsive' => array(
				0 => array(
					'items' => !empty($settings['mobile']) ? $settings['mobile'] : 1,
					'nav' => true
				),
				481 => array(
					'items' => !empty($settings['tablet']) ? $settings['tablet'] : 1,
					'nav' => true
				),
				991 => array(
					'items' => !empty($settings['desktop']) ? $settings['desktop'] : 1,
					'nav' => true
				),
				1025 => array(
					'items' => !empty($settings['item']) ? $settings['item'] : 1,
					'nav' => true
				)
			),
			'margin' => !empty($settings['margin']) ? (int)$settings['margin'] : 0,
			'stagePadding' => !empty($settings['padding']) ? $settings['padding'] : 0
		);
		$settings['carousel'] = $carouselOptions;
		return $this->load->view( $template, array( 'settings' => $settings, 'categories' => $categories ) );
	}

	private function getProducts( $results, $setting ){
		$this->load->model( 'tool/image' );
		$this->load->model( 'catalog/product' );
        $products = array();

        if( defined("IMAGE_URL")){
            $server =  IMAGE_URL;
        } else  {
            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }

        foreach ($results as $result) {
            if ( ! empty( $result['image'] ) ) {
				$setting = strtolower( $setting );
				$src = empty( $setting ) || $setting == 'full' ? $server . $result['image'] : false;
				if ( strpos( $setting, 'x' ) ) {
					$sizes = explode( 'x', $setting );
					$src = $this->getImageLink($result['image'], $setting);
				}

				$result['image'] = $src ? $src : $result['image'];
			}

            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $price = false;
            }

            if ((float)$result['special']) {
                $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                $discount = floor((($result['price']-$result['special'])/$result['price'])*100);
            } else {
                $special = false;
            }

            if ($this->config->get('config_tax')) {
                $tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
            } else {
                $tax = false;
            }
            if ($this->config->get('config_review_status')) {
                $rating = $result['rating'];
            } else {
                $rating = false;
            }

            $images = $this->model_catalog_product->getProductImages( $result['product_id'] );
            $output = array();
            if( $images ){
                foreach( $images as $timage ){
                    if ($timage['image']) {
                    	$tmp = $this->getImageLink( $timage['image'], $setting );
                    } else {
                    	$tmp = $this->getImageLink( 'placeholder.png', $setting );
                    }
                    $output[] = $tmp;
                }
            }

            $products[] = array(
            	'images'     => $output,
                'product_id' => $result['product_id'],
                'thumb'   	 => $result['image'],
                'name'    	 => $result['name'],
                'date_added' => $result['date_added'],
                'discount'   => isset($discount)?'-'.$discount.'%':'',
                'price'   	 => $price,
                'special' 	 => $special,
                'rating'     => $rating,
                'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),
            );
        }
        return $products;
    }

}