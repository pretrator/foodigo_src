<?php
class ModelExtensionShippingFlat extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/flat');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_flat_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_flat_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
		$method_data = array();
        //$f=file_get_contents('http://13.126.251.226:55535/'.($this->cart->getProducts())['cart_id'].'/'.(int)$address['zone_id']);
        		$cart_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
        $prods=array();
        foreach ($cart_query->rows as $cart) {
            $qury = $this->db->query('SELECT manufacturer_id FROM oc_product where product_id='.$cart['product_id']);
            foreach ($qury->rows as $ca) {
                array_push($prods,$ca['manufacturer_id']);
            }
        }
        $merchquery=$this->db->query('select block from foodigo.oc_manufacturer where manufacturer_id='.$prods[0]);
        $merchblock=$merchquery->row['block'];
        $numofmanufacturer=count(array_unique($prods));
        $totalcos=$this->cart->getTotal();
       $rf=file_get_contents('http://apifoodigo.ap-south-1.elasticbeanstalk.com:55535/delivery/'.(int)$merchblock.'/'.(int)$address['zone_id'].'/'.$totalcos.'/'.$numofmanufacturer);
		if ($status) {
			$quote_data = array();
			$quote_data['flat'] = array(
				'code'         => 'flat.flat',
				'title'        => $this->language->get('text_description'),
				'cost'         => $rf,
				'tax_class_id' => $this->config->get('shipping_flat_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($rf, $this->config->get('shipping_flat_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			);

			$method_data = array(
				'code'       => 'flat',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_flat_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}