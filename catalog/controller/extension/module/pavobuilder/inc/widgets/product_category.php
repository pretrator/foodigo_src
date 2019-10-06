<?php
class PA_Widget_Product_Category extends PA_Widgets {

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
				'label'	=> $this->language->get( 'entry_product_category' )
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
							'options' => $this->getLayoutsOptions(),
							'none' 	=> false
						),
						array(
                            'type'  => 'text',
                            'name'  => 'extra_class',
                            'label' => $this->language->get( 'entry_extra_class_text' ),
                            'default' => '',
                            'desc' => $this->language->get( 'entry_extra_class_desc_text' )
                        ),
                        array(
                            'type'  => 'text',
                            'name'  => 'extra_class1',
                            'label' => $this->language->get( 'entry_extra_class1_text' ),
                            'default' => '',
                            'desc'  => $this->language->get( 'entry_extra_class_desc_text' )
                        ),
						array(
							'type'		=> 'editor',
							'name'		=> 'banner_title',
							'label'		=> $this->language->get( 'entry_extra_banner_title_text' ),
							'default' 	=> '',
							'language'  => true
						),
						array(
							'type'		=> 'text',
							'name'		=> 'name_product1',
							'label'		=> $this->language->get( 'entry_name1_text' ),
							'default'	  => '',
							'language'  => true
						),
						array(
							'type'		=> 'text',
							'name'		=> 'name_product2',
							'label'		=> $this->language->get( 'entry_name2_text' ),
							'default'	  => '',
							'language'  => true
						),
						array(
							'type'		=> 'text',
							'name'		=> 'button_text',
							'label'		=> $this->language->get( 'entry_banner_button' ),
							'default' 	=> '',
							'language'  => true
						),
						array(
							'type'		  => 'text',
							'name'		  => 'product_size1',
							'label'		  => $this->language->get( 'entry_product_size_text' ),
							'desc'		  => $this->language->get( 'entry_image_size_desc' ),
							'default'	  => 'full',
							'placeholder' => '200x400'
						),
						array(
							'type'		=> 'select',
							'name'		=> 'tabs1',
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
							'name'		=> 'category_status',
							'label'		=> $this->language->get( 'entry_list_category' ),
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
							),
							'none' => false
						),
						array(
							'type'		=> 'select',
							'name'		=> 'category',
							'label'		=> $this->language->get( 'entry_list_category' ),
							'default' 	=> '',
							'options'	=> $categories,
							'multiple'	=> true
						),
						array(
							'type'	  => 'number',
							'name'    => 'item1',
							'label'	  => $this->language->get( 'entry_item_text' ),
							'desc'    => $this->language->get( 'entry_item_desc_text' ),
							'default' => 4
						),
						array(
							'type'		=> 'number',
							'name'		=> 'rows1',
							'label'		=> $this->language->get( 'entry_rows_text' ),
							'default'	=> 1,
							'min' => 1,
							'max' => 2
						),
						array(
							'type'		=> 'number',
							'name'		=> 'limit1',
							'label'		=> $this->language->get( 'entry_limit_text' ),
							'default'	=> 7
						),
						array(
							'type'		=> 'select',
							'name'		=> 'loop1',
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
							'name'		=> 'auto_play1',
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
							),
							'none' => false
						),
						array(
							'type'	  => 'number',
							'name'	  => 'auto_play_time1',
							'label'	  => $this->language->get( 'entry_auto_play_time' ),
							'default' => 5000
						),
						array(
							'type'	  => 'number',
							'name'    => 'desktop_1',
							'label'	  => $this->language->get( 'entry_desktop_screens' ),
							'default' => 4
						),
						
						array(
							'type'	  => 'number',
							'name'    => 'tablet_1',
							'label'	  => $this->language->get( 'entry_table_screens' ),
							'default' => 2
						),
						array(
							'type'	  => 'number',
							'name'    => 'mobile_1',
							'label'	  => $this->language->get( 'entry_mobile_screens' ),
							'default' => 1
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
						)
					)
				),
				'product'		=> array(
					'label'		=> $this->language->get( 'entry_second_product_text' ),
					'is_global' => true,
					'fields'	=> array(
						array(
							'type'		=> 'select',
							'name'		=> 'status2',
							'label'		=> $this->language->get( 'entry_category_status' ),
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
							),
							'none' => false
						),
						array(
                            'type'  => 'text',
                            'name'  => 'extra_class2',
                            'label' => $this->language->get( 'entry_extra_class2_text' ),
                            'default' => '',
                            'desc'  => $this->language->get( 'entry_extra_class_desc_text' )
                        ),
						array(
							'type'		  => 'text',
							'name'		  => 'product_size2',
							'label'		  => $this->language->get( 'entry_product_size_text' ),
							'desc'		  => $this->language->get( 'entry_image_size_desc' ),
							'default'	  => 'full',
							'placeholder' => '200x400'
						),
						array(
							'type'		=> 'select',
							'name'		=> 'tabs2',
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
							'type'	  => 'number',
							'name'    => 'item2',
							'label'	  => $this->language->get( 'entry_item_text' ),
							'desc'    => $this->language->get( 'entry_item_desc_text' ),
							'default' => 4
						),
						array(
							'type'		=> 'number',
							'name'		=> 'rows2',
							'label'		=> $this->language->get( 'entry_rows_text' ),
							'default'	=> 1,
							'min' => 1,
							'max' => 2
						),
						array(
							'type'		=> 'number',
							'name'		=> 'limit2',
							'label'		=> $this->language->get( 'entry_limit_text' ),
							'default'	=> 7
						),
						array(
							'type'		=> 'select',
							'name'		=> 'loop2',
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
							'name'		=> 'auto_play2',
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
							'name'	  => 'auto_play_time2',
							'label'	  => $this->language->get( 'entry_auto_play_time' ),
							'default' => 5000
						),
						array(
							'type'	  => 'number',
							'name'    => 'desktop_2',
							'label'	  => $this->language->get( 'entry_desktop_screens' ),
							'default' => 4
						),
						
						array(
							'type'	  => 'number',
							'name'    => 'tablet_2',
							'label'	  => $this->language->get( 'entry_table_screens' ),
							'default' => 2
						),
						array(
							'type'	  => 'number',
							'name'    => 'mobile_2',
							'label'	  => $this->language->get( 'entry_mobile_screens' ),
							'default' => 1
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
						)
					)
				),
				'banner'		=> array(
					'label'		=> $this->language->get( 'entry_banner' ),
					'is_global' => true,
					'fields'	=> array(
						array(
							'type'		=> 'select',
							'name'		=> 'status3',
							'label'		=> $this->language->get( 'entry_category_status' ),
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
							),
							'none' => false
						),
						array(
							'type'	=> 'image',
							'name'	=> 'banner',
							'label'	=> $this->language->get( 'entry_banner' )
						),
						array(
							'type'		  => 'text',
							'name'		  => 'banner_size',
							'label'		  => $this->language->get( 'entry_banner_size_text' ),
							'desc'		  => $this->language->get( 'entry_image_size_desc' ),
							'default'	  => 'full',
							'placeholder' => '200x400'
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
							'name'	=> 'button_link',
							'label'	=> $this->language->get( 'entry_link_text' ),
							'default'	=> '',
							'desc'	=> $this->language->get( 'entry_link_desc_text' ),
						),
						array(
							'type'		=> 'select',
							'name'		=> 'banner_type',
							'label'		=> $this->language->get( 'entry_banner_type' ),
							'default' 	=> 'left',
							'options'	=> array(
								array(
									'value'	=> 'left',
									'label'	=> 'Left'
								),
								array(
									'value'	=> 'right',
									'label'	=> 'Right'
								)
							)
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'background_color',
							'default'	=> '',
							'label'	=> $this->language->get( 'entry_background_color_text' )
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
		$this->load->language('extension/module/pavobuilder');
		$this->load->model( 'tool/image' );
		$this->load->model('extension/module/pavothemer');
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');

		$settings['banner_title'] = ! empty( $settings ) && ! empty( $settings['banner_title'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['banner_title'] ), ENT_QUOTES, 'UTF-8' ) : '';

		if( defined("IMAGE_URL")){
            $server = IMAGE_URL;
        } else  {
            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }

		$settings['server'] = $server;
		if ( ! empty( $settings['banner'] ) ) {
			$settings['banner_size'] = strtolower( $settings['banner_size'] );
			$srcs = empty( $settings['banner_size'] ) || $settings['banner_size'] == 'full' ? $server . $settings['banner'] : false;
			if (strpos( $settings['banner_size'], 'x' ) ) {
				$srcs = $this->getImageLink($settings['banner'], $settings['banner_size']);
			}
			$settings['banner'] = $srcs ? $srcs : $settings['banner'];
		}

		$filter_data1 = array(
			'start' => 0,
            'limit' => isset($settings['limit1']) ? $settings['limit1'] : 10
        );

		$filter_categories = array();

 		if( !isset($settings['tabs1']) ){
 			$settings['tabs1'] = '';
 		}
 		if( isset($settings['category']) && is_array($settings['category']) ){
 			$filter_categories = $settings['category'];
 		}

 		$filter_data2 = array(
			'start' => 0,
            'limit' => isset($settings['limit2']) ? $settings['limit2'] : 10
        );

        if( !isset($settings['tabs2']) ){
 			$settings['tabs2'] = '';
 		}
 		$get_category = array();
 		if (!empty($filter_categories)) {
	 		foreach ($filter_categories as $cat_id) {
	 			$getcategory = $this->model_catalog_category->getCategory($cat_id);
				switch ( $settings['tabs1'] ) {
					case "top_rating":
						$products1 = $this->model_extension_module_pavothemer->getTopRatingProductsCategory( $filter_data1['limit'], $cat_id );
						break;
					case "latest":
						$products1 = $this->model_extension_module_pavothemer->getLatestProductsCategory( $filter_data1['limit'],  $cat_id );
						break;
					case "best_seller":
						$products1 = $this->model_extension_module_pavothemer->getBestSellerProductsCategory( $filter_data1['limit'], $cat_id );
						break;
					case "special":
						$products1 = $this->model_extension_module_pavothemer->getProductSpecialsCategory( $filter_data1, $cat_id );
						break;
					case "most_viewed":
						$products1 = $this->model_extension_module_pavothemer->getMostviewedProductsCategory( $filter_data1['limit'], $cat_id );
						break;
					default:
						$products1 = $this->model_extension_module_pavothemer->getLatestProductsCategory( $filter_data1['limit'], $cat_id );
						break;
				}

				switch ( $settings['tabs2'] ) {
					case "top_rating":
						$products2 = $this->model_extension_module_pavothemer->getTopRatingProductsCategory( $filter_data2['limit'], $cat_id );
						break;
					case "latest":
						$products2 = $this->model_extension_module_pavothemer->getLatestProductsCategory( $filter_data2['limit'],  $cat_id );
						break;
					case "best_seller":
						$products2 = $this->model_extension_module_pavothemer->getBestSellerProductsCategory( $filter_data2['limit'], $cat_id );
						break;
					case "special":
						$products2 = $this->model_extension_module_pavothemer->getProductSpecialsCategory( $filter_data2, $cat_id );
						break;
					case "most_viewed":
						$products2 = $this->model_extension_module_pavothemer->getMostviewedProductsCategory( $filter_data2['limit'], $cat_id );
						break;
					default:
						$products2 = $this->model_extension_module_pavothemer->getLatestProductsCategory( $filter_data2['limit'], $cat_id );
						break;
				}

				if ( ! empty( $getcategory['image'] ) ) {
					$settings['banner_size'] = strtolower( $settings['banner_size'] );
					$srcs = empty( $settings['banner_size'] ) || $settings['banner_size'] == 'full' ? $server . $getcategory['image'] : false;
					if (strpos( $settings['banner_size'], 'x' ) ) {
						$srcs = $this->getImageLink($getcategory['image'], $settings['banner_size']);
					}
					$getcategory['image'] = $srcs ? $srcs : $getcategory['image'];
				}

				$get_category[$cat_id] = array(
	                    'category_id'       => isset($getcategory['category_id']) ? $getcategory['category_id'] : '',
	                    'category_name'     => isset($getcategory['name']) ? $getcategory['name'] : '',
	                    'image'             => !empty($getcategory['image']) ? $getcategory['image'] : $this->model_tool_image->resize('placeholder.png',500,500),
	                    'link'              => $this->url->link('product/category','path=' .(isset($getcategory['category_id']) ? $getcategory['category_id'] : '')),
	                    'products1'         => $products1 ? $this->getProducts( $products1 , $settings['product_size1'] ) : array(),
	                    'products2'         => $products2 ? $this->getProducts( $products2 , $settings['product_size2'] ) : array()
	                );
	 		}
 		}

	  	$settings['get_category'] = $get_category;

	  	if (!empty($settings['layout'])) {
			$args = $this->renderLayout($settings['layout']);
		} else {
			$args = 'extension/module/pavobuilder/pa_product_category/pa_product_category';
		}

		$carouselMainOptions = array(
			'loop' => isset( $settings['loop1'] ) && $settings['loop1'] === 'true' ? true : false,
			'responsiveClass' => true,
			'items' => !empty($settings['item1']) ? (int)$settings['item1'] : 2,
			'rows' => !empty($settings['rows1']) ? (int)$settings['rows1'] : 1,
			'nav' => !empty($settings['nav']) && $settings['nav'] === 'true',
			'dots' => !empty($settings['pagination']) && $settings['pagination'] === 'true',
			'autoplay' => !empty($settings['auto_play1']) && $settings['auto_play1'] == 'true' ? true : false,
			'autoplayTimeout' => !empty($settings['auto_play_time1']) ? $settings['auto_play_time1'] : 2000,
			'responsive' => array(
				0 => array(
					'items' => !empty($settings['mobile_1']) ? $settings['mobile_1'] : 1,
					'nav' => true
				),
				481 => array(
					'items' => !empty($settings['tablet_1']) ? $settings['tablet_1'] : 2,
					'nav' => true
				),
				991 => array(
					'items' => !empty($settings['desktop_1']) ? $settings['desktop_1'] : 3,
					'nav' => true
				),
				1025 => array(
					'items' => !empty($settings['item1']) ? $settings['item1'] : 1,
					'nav' => !empty($settings['nav']) && $settings['nav'] === 'true'
				)
			),
			'margin' => !empty($settings['margin']) ? (int)$settings['margin'] : 0,
			'stagePadding' => !empty($settings['padding']) ? $settings['padding'] : 0
		);
		$settings['mainCarousel'] = $carouselMainOptions;

		$carouselSideOptions = array(
			'loop' => isset( $settings['loop2'] ) && $settings['loop2'] === 'true' ? true : false,
			'responsiveClass' => true,
			'items' => !empty($settings['item2']) ? (int)$settings['item2'] : 2,
			'rows' => !empty($settings['rows2']) ? (int)$settings['rows2'] : 1,
			'nav' => !empty($settings['nav']) && $settings['nav'] === 'true',
			'dots' => !empty($settings['pagination']) && $settings['pagination'] === 'true',
			'autoplay' => !empty($settings['auto_play2']) && $settings['auto_play2'] == 'true' ? true : false,
			'autoplayTimeout' => !empty($settings['auto_play_time2']) ? $settings['auto_play_time2'] : 2000,
			'responsive' => array(
				0 => array(
					'items' => !empty($settings['mobile_2']) ? $settings['mobile_2'] : 1,
					'nav' => true
				),
				481 => array(
					'items' => !empty($settings['tablet_2']) ? $settings['tablet_2'] : 2,
					'nav' => true
				),
				991 => array(
					'items' => !empty($settings['desktop_2']) ? $settings['desktop_2'] : 3,
					'nav' => true
				),
				1025 => array(
					'items' => !empty($settings['item2']) ? $settings['item2'] : 1,
					'nav' => !empty($settings['nav']) && $settings['nav'] === 'true'
				)
			),
			'margin' => !empty($settings['margin']) ? (int)$settings['margin'] : 0,
			'stagePadding' => !empty($settings['padding']) ? $settings['padding'] : 0
		);
		$settings['sideCarousel'] = $carouselSideOptions;
		return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
	}

	private function getProducts( $results, $setting ){
		$this->load->model( 'tool/image' );
		$this->load->model( 'catalog/product' );
        $products = array();

        if( defined("IMAGE_URL")){
            $server = IMAGE_URL;
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
                'discount'   => isset($discount) ? '-'.$discount.'%' : '',
                'price'   	 => $price,
                'special' 	 => $special,
                'rating'     => $rating,
                'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id'])
            );
        }
        return $products;
    }

}