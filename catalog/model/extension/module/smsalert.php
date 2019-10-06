<?php
class ModelExtensionModuleSmsAlert extends Model {
	private function getTemplates($type, $store_id) { 
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "sms_template st LEFT JOIN " . DB_PREFIX . "sms_template_message stm ON st.sms_template_id = stm.sms_template_id WHERE type = '" . $this->db->escape($type) . "' AND store_id = '" . (int)$store_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		return $query->rows;
	}
	
	private function getSearch($type) {
		if ($type == 'register') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}',
				'{password}'
			);
		} elseif ($type == 'affiliate') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}'
			);
		} elseif ($type == 'affiliate_transaction') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{commission}',
				'{total_commission}'
			);
		} elseif ($type == 'affiliate_approve') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}'
			);
		} elseif ($type == 'forgotten') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{password}'
			);
		} elseif ($type == 'order') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}',
				'{order_id}',
				'{date_added}',
				'{payment_method}',
				'{shipping_method}',
				'{ip}',
				'{comment_table}',
				'{payment_address}',
				'{shipping_address}',
				'{products}'
			);
		} elseif ($type == 'reward') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{points}',
				'{total_points}'
			);
		} elseif ($type == 'account_approve') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}'
			);
		} elseif ($type == 'account_transaction') {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{credits}',
				'{total_credits}'
			);
		} else {
			$search = array(
				'{firstname}',
				'{lastname}',
				'{email}',
				'{telephone}',
				'{order_id}',
				'{date_added}',
				'{payment_method}',
				'{shipping_method}',
				'{ip}',
				'{comment_table}',
				'{payment_address}',
				'{shipping_address}',
				'{products}'
			);
		}
	
		return $search;
	}
	
	public function parseSMS($type, $store_id, $number, $replace) {
	
		$number = preg_replace('/[^0-9]/', '', $number);
		
		$country_code = 'IN';
		
		$templates = $this->getTemplates($type, $store_id);
	
		
		$template_data = false;
		foreach ($templates as $template) {
			/* $countries = json_decode($template['country'], true);
			
			if ($countries && in_array($country_code, $countries)) { */
				$template_data = $template;
				
			/* 	break;
			} */
		}
	    
		if ($template_data && !empty($template_data['message'])) {
			$search = $this->getSearch($type);
			
			$message = strip_tags(str_replace($search, $replace, html_entity_decode($template_data['message'])));
			
			$this->sendSMS($number, $message);
			
			$numbers = explode(',', $template_data['bcc']);
			foreach ($numbers as $number) {
				if ($number) {
					$this->sendSMS($number, $message);
				}
			}
		} else {
			return false;
		}
	}
	
	private function sendCurl($url, $post_data) {
	
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_USERAGENT, 'OpenCart Two Factor Authentication');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));

		$response = curl_exec($curl);	
		if (curl_errno($curl)) {
			$curl_error = 'SmsAlert cURL Error ' . curl_errno($curl) . ': ' . curl_error($curl);
		} else {
			$curl_error = '';
		}
		
		if ($curl_error) {
			$this->log->write($curl_error);
		}
		
		if ($this->config->get('smsalert_debug')) {
			$this->log->write($response);
		}

		curl_close($curl);
		
		return json_decode($response, true);
        
        
        
        
        
        
        
       
        
        
        
        
        
        
        
	}
	
	private function sendSMS($receiver, $message) {
		if (!$this->config->get('smsalert_auth_key') || !$this->config->get('smsalert_auth_secret')) {
			return;
		}
		
		$post_data = array(
			'user'			=> $this->config->get('smsalert_auth_key'),
			'pwd'			=> $this->config->get('smsalert_auth_secret'),
			'sender'		=> $this->config->get('smsalert_default_senderid'),
			'mobileno'		=> substr($receiver, -10),
			'text'			=> $message
		);
        
         $apiKey = urlencode('vPsOECoMi8c-BHoCjckpsCi2vu61YbJUlnZVWXf9jo');
	
	     // Message details
	     $numbers = urlencode(substr($receiver, -10));
	     $sender = urlencode('FDIGIN');
	     $message = rawurlencode($message);
 
	     // Prepare data for POST request
	     $data = 'apikey=' . $apiKey . '&numbers=' . $numbers . "&sender=" . $sender . "&message=" . $message;
 
    	 // Send the GET request with cURL
	     $ch = curl_init('https://api.textlocal.in/send/?' . $data);
	     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	     $response = curl_exec($ch);
	     curl_close($ch);
	
	     // Process your response here                
	    	//$this->sendCurl(base64_decode('aHR0cDovL2NuYW1lLmNvenl2aXNpb24uY29tL2FwaS9wdXNoLmpzb24='), $post_data);
	}
}