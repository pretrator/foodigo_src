<?php
class ControllerExtensionModulePavproductcarousel extends Controller {

	private $data;

	public function index($setting) {

		$this->load->language('extension/module/themecontrol');
		$this->data['config_theme'] = $this->config->get('theme_default_directory');
		
		static $module = 0;

		$this->load->model('catalog/product');
		$this->load->model('extension/module/pavproductcarousel');
		$this->load->model('tool/image');
		$this->load->language('extension/module/pavproductcarousel');
		$this->document->addStyle( 'catalog/view/javascript/jquery/swiper/css/swiper.min.css' );
        $this->document->addStyle( 'catalog/view/javascript/jquery/swiper/css/opencart.css' );
        
        $this->document->addScript( 'catalog/view/javascript/jquery/swiper/js/swiper.min.js' );
		$this->data['button_cart'] = $this->language->get('button_cart');
		
		$default = array(
			'latest' => 1,
			'limit' => 9
		);
	 	$a = array('interval'=> 8000,'auto_play'=>0,'loop' => 1 );
		$setting = array_merge( $a, $setting );
		$this->data['prefix'] 		= isset($setting['prefix'])?$setting['prefix']:'';
		$this->data['fontawesome']  = isset($setting['fontawesome'])?$setting['fontawesome']:'';
		$this->data['width'] 		= isset($setting['width']) ? (int)$setting['width'] : 200;
		$this->data['height'] 		= isset($setting['height']) ? (int)$setting['height'] : 200;
		$this->data['auto_play_mode'] = isset($setting['auto_play']) && $setting['auto_play'] == 1 ? "true" : "false";
		$this->data['interval'] 	= isset($setting['interval']) ? (int)$setting['interval'] : 5000;
		$this->data['cols']   		= isset($setting['cols']) ? (int)$setting['cols'] : 1;
		$this->data['itemsperpage'] = isset($setting['itemsperpage']) ? (int)$setting['itemsperpage'] : 2;
		$this->data['loop']			= isset($setting['loop']) && $setting['loop'] == 1 ? "true" : "false";
		$this->data['tooltip']   	= isset($setting['tooltip'])?(int)$setting['tooltip']:0;
		$this->data['tooltip_placement'] = isset($setting['tooltip_placement'])?$setting['tooltip_placement']:'top';
		$this->data['tooltip_show'] = isset($setting['tooltip_show'])?(int)$setting['tooltip_show']:100;
		$this->data['tooltip_hide'] = isset($setting['tooltip_hide'])?(int)$setting['tooltip_hide']:100;

		$this->data['tooltip_width'] = isset($setting['tooltip_width'])?(int)$setting['tooltip_width']:200;
		$this->data['tooltip_height'] = isset($setting['tooltip_height'])?(int)$setting['tooltip_height']:200;

		$this->data['show_button'] = isset($setting['btn_view_more'])?$setting['btn_view_more']:0;

		$this->data['button_cart'] = $this->language->get('button_cart');

		$heading_title = $this->language->get('heading_title');
		// $this->data['id'] = random(1,9)+substr(($heading_title),0,3);
		
		if ( !empty($setting['tabs'])){
			$this->data['type_product'] = ! empty( $setting['tabs'] ) ? reset($setting['tabs']) : 'latest';
		
			$this->data['button_link'] = $this->url->link('product/product');	
			if($this->data['type_product'] == 'special') {
				$this->data['button_link'] = $this->url->link('product/special');
			}
		}

		$this->data['view_more'] = $this->language->get('label_btn_view_more');

		$this->data['tabs'] = array();

		$data = array(
			'sort'  => 'p.date_added',
			'order' => 'DESC',
			'start' => 0,
			'limit' => isset($setting['limit']) ? (int)$setting['limit'] : 10,
			'product' => isset($setting['product'])?$setting['product']:array(),
		);

		if (!empty($setting['tabs'])) {
		 $setting['tabs'] = array_flip(  $setting['tabs'] );
		}

		$tabs = array(
			'latest' 	 => array(),
			'featured'   => array(),
			'bestseller' => array(),
			'special'    => array(),
			'mostviewed' => array(),
			'toprating' => array()
		);
		if( isset($setting['title'][$this->config->get('config_language_id')]) ) {
			$this->data['title'] = html_entity_decode($setting['title'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
		}else {
			$this->data['title'] = '';
		}
		if( isset($setting['description'][$this->config->get('config_language_id')]) ) {
			$this->data['message'] = html_entity_decode($setting['description'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
		}else {
			$this->data['message'] = '';
		}
		if(isset($setting['tabs']['featured'])){
			$products = $this->getProducts( $this->getFeatured($data), $setting );
			$this->data['heading_title'] = $this->language->get('text_featured');
		}
		if( isset($setting['tabs']['latest']) ){
			$products = $this->getProducts( $this->model_catalog_product->getProducts( $data ), $setting );
			$this->data['heading_title'] = $this->language->get('text_latest');
	 	}
		if( isset($setting['tabs']['bestseller']) ){
			$products = $this->getProducts( $this->model_catalog_product->getBestSellerProducts( $data['limit'] ), $setting );
			$this->data['heading_title'] = $this->language->get('text_bestseller');
	 	}
		if( isset($setting['tabs']['special']) ){
			$products = $this->getProducts( $this->model_catalog_product->getProductSpecials( $data ), $setting );
			$this->data['heading_title'] = $this->language->get('text_special');
		}
		if( isset($setting['tabs']['mostviewed']) ){
			$products = $this->getProducts( $this->model_extension_module_pavproductcarousel->getMostviewedProducts( $data['limit'] ), $setting );
			$this->data['heading_title'] = $this->language->get('text_mostviewed');
		}
		if( isset($setting['tabs']['toprating']) ){
			$products = $this->getProducts( $this->model_extension_module_pavproductcarousel->getTopRatingProducts( $data['limit'] ), $setting );
			$this->data['heading_title'] = $this->language->get('text_toprating');
		}
		if (isset($products)) {
		$this->data['products'] = $products;
		}
		$this->data['module'] = $module++;
		return $this->load->view('extension/module/pavproductcarousel', $this->data);
	}
	private function getFeatured($option = array()){
		$products =  $option['product'];
		$return = array();
		if(!empty($products)){
			$limit = (isset($option['limit']) && !empty($option['limit']))?$option['limit']: 5;
			$products = array_slice($products, 0, (int)$limit);
			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);
				$return[] = $product_info;
			}
		}
		return $return;
	}

	private function getProducts( $results, $setting ){
		$products = array();
		$tooltip_width = isset($setting['tooltip_width'])?(int)$setting['tooltip_width']:200;
		$tooltip_height = isset($setting['tooltip_height'])?(int)$setting['tooltip_height']:200;
		
		foreach ($results as $result) {
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				$product_images = $this->model_catalog_product->getProductImages($result['product_id']);
				if(isset($product_images) && !empty($product_images)) {
					$thumb2 = $this->model_tool_image->resize($product_images[0]['image'], $setting['width'], $setting['height']);
				}
			} else {
				$image = false;
			}

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
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

			if ($this->config->get('config_review_status')) {
				$rating = $result['rating'];
			} else {
				$rating = false;
			}

			$products[] = array(
				'product_id' => $result['product_id'],
				'thumb'   	 => $image,
				'thumb2'   	 => isset($thumb2)?$thumb2:'',
				'date_added'  => $result['date_added'],
				'discount'   => isset($discount)?'-'.$discount.'%':'',
				'name'    	 => $result['name'],
				'price'   	 => $price,
				'special' 	 => $special,
				'rating'     => $rating,
				'description'=> (html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')),
				'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),
			);
		}
		return $products;
	}
}
?>
