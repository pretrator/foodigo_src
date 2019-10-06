<?php
class ControllerExtensionModuleSmsAlert extends Controller {
	public function eventPostModelAccountCustomerAdd($route, $args, $output) {
		$this->load->model('account/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_info = $this->model_account_customer->getCustomer($output);
		
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email'],
			$customer_info['telephone'],
			$customer_info['password']
		);
		$this->model_extension_module_smsalert->parseSMS('register', $this->config->get('config_store_id'), $customer_info['telephone'], $replace);
	}
	
	public function eventPostModelAccountCustomerAddAffiliate($route, $args, $output) {
		$this->load->model('account/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_info = $this->model_account_customer->getCustomer($args[0]);
		
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email'],
			$customer_info['telephone']
		);
		
		$this->model_extension_module_smsalert->parseSMS('affiliate', $this->config->get('config_store_id'), $customer_info['telephone'], $replace);
	}
	
	public function eventPostModelAccountCustomerAddTransaction($route, $args, $output) {
		$this->load->model('account/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_id = isset($args[0]) ? $args[0] : 0;
		$description = isset($args[1]) ? $args[1] : '';
		$amount = isset($args[2]) ? $args[2] : 0;
		$order_id = isset($args[3]) ? $args[3] : 0;
		
		$customer_info = $this->model_account_customer->getCustomer($customer_id);
				
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email'],
			$this->currency->format($amount, $this->config->get('config_currency')),
			$this->currency->format($this->model_account_customer->getTransactionTotal($customer_id), $this->config->get('config_currency'))
		);
		
		$this->model_extension_module_smsalert->parseSMS('affiliate_transaction', $this->config->get('config_store_id'), $customer_info['telephone'], $replace);
	}
	
	public function eventPostModelAccountCustomerEditCode($route, $args, $output) {
		$this->load->model('account/customer');
		$this->load->model('extension/module/smsalert');
		
		$customer_info = $this->model_account_customer->getCustomerByEmail($args[0]);
		
		$replace = array(
			$customer_info['firstname'],
			$customer_info['lastname'],
			$customer_info['email'],
			$this->url->link('account/reset', 'code=' . $args[1], true)
		);
		
		$this->model_extension_module_smsalert->parseSMS('forgotten', $this->config->get('config_store_id'), $customer_info['telephone'], $replace);
	}
	
	public function eventPreModelCheckoutOrderAddOrderHistory($route, $args) {
		if (isset($args[0])) {
			$order_id = $args[0];
		} else {
			$order_id = 0;
		}

		if (isset($args[1])) {
			$order_status_id = $args[1];
		} else {
			$order_status_id = 0;
		}	

		if (isset($args[2])) {
			$comment = $args[2];
		} else {
			$comment = '';
		}
		
		if (isset($args[3])) {
			$notify = $args[3];
		} else {
			$notify = '';
		}
						
		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		if ($order_info) {
			// Order confirmation
			if (!$order_info['order_status_id'] && $order_status_id) {
				$this->load->model('extension/module/smsalert');
								
				if ($order_info['payment_address_format']) {
					$format = $order_info['payment_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}
				
				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{country}'
				);
			
				$replace = array(
					'firstname' => $order_info['payment_firstname'],
					'lastname'  => $order_info['payment_lastname'],
					'company'   => $order_info['payment_company'],
					'address_1' => $order_info['payment_address_1'],
					'address_2' => $order_info['payment_address_2'],
					'city'      => $order_info['payment_city'],
					'postcode'  => $order_info['payment_postcode'],
					'zone'      => $order_info['payment_zone'],
					'country'   => $order_info['payment_country']  
				);
				
				$payment_address = trim(str_replace($find, $replace, $format));						
				
				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}
				
				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{country}'
				);
			
				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					'country'   => $order_info['shipping_country']  
				);
				
				$shipping_address = trim(str_replace($find, $replace, $format));
				
				$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

				$plain_product_table = '';
				
				foreach ($order_product_query->rows as $product) {
					$plain_product_table .= $product['quantity'] . 'x ' . $product['name'] . '(' . $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']) . ')' . "\n";
					
					$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

					foreach ($order_option_query->rows as $option) {
						$plain_product_table .= '- ' . $option['name'] . ': ' . $option['value'] . "\n";
					}
				}
				
				$replace = array(
					$order_info['firstname'],
					$order_info['lastname'],
					$order_info['email'],
					$order_info['telephone'],
					$order_info['order_id'],
					date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					$order_info['payment_method'],
					$order_info['shipping_method'],
					$order_info['ip'],
					$notify ? $comment : '',
					$payment_address,
					$shipping_address,
					$plain_product_table
				);
				
				$this->model_extension_module_smsalert->parseSMS('order', $order_info['store_id'], $order_info['telephone'], $replace);
			}
			
			// Order update
			if ($order_info['order_status_id'] && $order_status_id && $notify) {
				$this->load->model('extension/module/smsalert');
							
				if ($notify) {
					if ($order_info['payment_address_format']) {
						$format = $order_info['payment_address_format'];
					} else {
						$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
					}
					
					$find = array(
						'{firstname}',
						'{lastname}',
						'{company}',
						'{address_1}',
						'{address_2}',
						'{city}',
						'{postcode}',
						'{zone}',
						'{country}'
					);
				
					$replace = array(
						'firstname' => $order_info['payment_firstname'],
						'lastname'  => $order_info['payment_lastname'],
						'company'   => $order_info['payment_company'],
						'address_1' => $order_info['payment_address_1'],
						'address_2' => $order_info['payment_address_2'],
						'city'      => $order_info['payment_city'],
						'postcode'  => $order_info['payment_postcode'],
						'zone'      => $order_info['payment_zone'],
						'country'   => $order_info['payment_country']  
					);
					
					$payment_address = trim(str_replace($find, $replace, $format));						
					
					if ($order_info['shipping_address_format']) {
						$format = $order_info['shipping_address_format'];
					} else {
						$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
					}
					
					$find = array(
						'{firstname}',
						'{lastname}',
						'{company}',
						'{address_1}',
						'{address_2}',
						'{city}',
						'{postcode}',
						'{zone}',
						'{country}'
					);
				
					$replace = array(
						'firstname' => $order_info['shipping_firstname'],
						'lastname'  => $order_info['shipping_lastname'],
						'company'   => $order_info['shipping_company'],
						'address_1' => $order_info['shipping_address_1'],
						'address_2' => $order_info['shipping_address_2'],
						'city'      => $order_info['shipping_city'],
						'postcode'  => $order_info['shipping_postcode'],
						'zone'      => $order_info['shipping_zone'],
						'country'   => $order_info['shipping_country']  
					);
					
					$shipping_address = trim(str_replace($find, $replace, $format));
					
					$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

					$plain_product_table = '';
					
					foreach ($order_product_query->rows as $product) {
						$plain_product_table .= $product['quantity'] . 'x ' . $product['name'] . '(' . $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']) . ')' . "\n";
						
						$order_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

						foreach ($order_option_query->rows as $option) {
							$plain_product_table .= '- ' . $option['name'] . ': ' . $option['value'] . "\n";
						}
					}
					
					$replace = array(
						$order_info['firstname'],
						$order_info['lastname'],
						$order_info['email'],
						$order_info['telephone'],
						$order_info['order_id'],
						date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
						$order_info['payment_method'],
						$order_info['shipping_method'],
						$order_info['ip'],
						$notify ? $comment : '',
						$payment_address,
						$shipping_address,
						$plain_product_table
					);
					
					$this->model_extension_module_smsalert->parseSMS($order_status_id, $order_info['store_id'], $order_info['telephone'], $replace);
				}
			}
		}
	}
}