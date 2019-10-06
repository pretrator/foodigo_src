<?php
class PA_Widget_Product_List extends PA_Widgets {

	public function fields() {

		$this->load->model('catalog/category');
		$get_category = $this->model_catalog_category->getCategories();
		$categories = array();
		$categories[] = array(
			'value' => '',
			'label' => 'All'
		);
		foreach ($get_category as $cat_id) {
			$categories[] = array(
				'value'	=> $cat_id['category_id'],
				'label'	=> $cat_id['name']
			);
		}
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-product-hunt',
				'label'	=> $this->language->get( 'entry_product_lists' )
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
							'type'	=> 'select',
							'name'	=> 'layout',
							'label'	=> $this->language->get( 'entry_layout_text' ),
							'default' => 'product_list',
							'options'	=> $this->getLayoutsOptions(),
							'none' 	=> false
						),
						array(
							'type'		=> 'select',
							'name'		=> 'title',
							'label'		=> $this->language->get( 'entry_title' ),
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
							'type'		=> 'text',
							'name'		=> 'title_product',
							'label'		=> $this->language->get( 'entry_title_text' ),
							'desc'		=> $this->language->get( 'entry_title_desc_text' ),
							'default'	  => '',
							'language'  => true
						),
						array(
							'type'		=> 'editor',
							'name'		=> 'subtitle_product',
							'label'		=> $this->language->get( 'entry_subtitle_text' ),
							'default'	  => '',
							'language'  => true
						),
						array(
							'type'		  => 'text',
							'name'		  => 'product_size',
							'label'		  => $this->language->get( 'entry_product_size_text' ),
							'desc'		  => $this->language->get( 'entry_image_size_desc' ),
							'default'	  => 'full',
							'placeholder' => '200x400'
						),
						array(
							'type'		=> 'select',
							'name'		=> 'tabs',
							'label'		=> $this->language->get( 'entry_product_group' ),
							'default' 	=> 'best_seller',
							'options'	=> array(
								array(
									'value'	=> 'latest',
									'label'	=> 'Latest'
								),
								array(
									'value'	=> 'best_seller',
									'label'	=> 'Best Seller'
								),
								array(
									'value'	=> 'special',
									'label'	=> 'Special'
								),
								array(
									'value'	=> 'most_viewed',
									'label'	=> 'Most Viewed'
								),
								array(
									'value'	=> 'top_rating',
									'label'	=> 'Top Rating'
								)
							)
						),
						array(
							'type'		=> 'select',
							'name'		=> 'category',
							'label'		=> $this->language->get( 'entry_list_category' ),
							'default' 	=> 'all',
							'options'	=> $categories,
							'multiple' => true
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
		$this->load->model( 'tool/image' );
		$this->load->model('extension/module/pavothemer');
		$this->load->model('catalog/product');

		$settings['subtitle_product'] = ! empty( $settings ) && ! empty( $settings['subtitle_product'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['subtitle_product'] ), ENT_QUOTES, 'UTF-8' ) : '';
		$settings['products'] = array ();
		$filter_data = array(
			'start' => 0,
            'limit' => isset($settings['limit']) ? $settings['limit'] : 10
        );

		$filter_categories = array( );

 		if( !isset($settings['tabs']) ){
 			 $settings['tabs']=  '';
 		}
 		if( isset($settings['category']) && is_array($settings['category']) ){
 			$filter_categories =  $settings['category'];
 		}

		switch (  $settings['tabs'] ) {
			case "top_rating":
				$products 	 = $this->model_extension_module_pavothemer->getTopRatingProducts( $filter_data['limit'], $filter_categories );
				break;
			case "latest":
				$products 		 = $this->model_extension_module_pavothemer->getLatestProducts( $filter_data['limit'],  $filter_categories );
				break;
			case "best_seller":
				$products 	 = $this->model_extension_module_pavothemer->getBestSellerProducts( $filter_data['limit'], $filter_categories );
				break;
				case "special":
				$products 		 = $this->model_extension_module_pavothemer->getProductSpecials( $filter_data, $filter_categories );
				break;
				case "most_viewed":
				$products 	 = $this->model_extension_module_pavothemer->getMostviewedProducts( $filter_data['limit'], $filter_categories );
				break;
				case "top_rating":
				break;
			default:
				$products = $this->model_extension_module_pavothemer->getLatestProducts( $filter_data['limit'], $filter_categories );
				break;
		}
		$settings['products'] =   $products ? $this->getProducts( $products , $settings ) : array();

	  	if (!empty($settings['layout'])) {
			$args = $this->renderLayout($settings['layout']);
		} else {
			$args = 'extension/module/pavobuilder/pa_product_list/product_list';
		}

		return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
	}

	private function getProducts( $results, $setting ){
		$this->load->model( 'tool/image' );
		$this->load->model( 'catalog/product' );
        $products = array();
        $tooltip_width = isset($setting['tooltip_width'])?(int)$setting['tooltip_width']:200;
        $tooltip_height = isset($setting['tooltip_height'])?(int)$setting['tooltip_height']:200;
        if( defined("IMAGE_URL")){
            $server =  IMAGE_URL;
        } else  {
            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }

        foreach ($results as $result) {
            if ( ! empty( $result['image'] ) ) {
				$setting['product_size'] = strtolower( $setting['product_size'] );
				$src = empty( $setting['product_size'] ) || $setting['product_size'] == 'full' ? $server. $result['image'] : false;
				if ( strpos( $setting['product_size'], 'x' ) ) {
					$src = $this->getImageLink($result['image'], $setting['product_size']);
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
                    	$tmp = $this->getImageLink( $timage['image'], $setting['product_size'] );
                    } else {
                    	$tmp = $this->getImageLink( 'placeholder.png', $setting['product_size'] );
                    }
                    $output[] = $tmp;
                }
            }

            $products[] = array(
            	'images'      => $output,
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