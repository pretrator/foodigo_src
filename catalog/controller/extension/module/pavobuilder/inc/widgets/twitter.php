<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Twitter extends PA_Widgets {

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-twitter',
				'label'	=> $this->language->get( 'entry_twitter' )
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
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' ),
							'default' => ''
						),
						array(
							'type'	=> 'text',
							'name'	=> 'api_key',
							'label'	=> $this->language->get( 'entry_twitter_api_key' ),
							'default'	=> ''
						),
						array(
							'type'	=> 'text',
							'name'	=> 'api_secret_key',
							'label'	=> $this->language->get( 'entry_twitterapi_secret_key' ),
							'default'	=> 'kk9gydOrvpZtE2hpi0UGomhXva5nyGxs2eJ304XWcwAsdBfzfz'
						),
						array(
							'type'	=> 'text',
							'name'	=> 'screen_name',
							'label'	=> $this->language->get( 'entry_screen_name' ),
							'default'	=> 'pavothemes'
						),
						array(
							'type'	  => 'number',
							'name'    => 'count',
							'label'	  => $this->language->get( 'entry_item_text' ),
							'desc'    => $this->language->get( 'entry_item_desc_text' ),
							'default' => 3
						),
						array(
							'type'	  => 'number',
							'name'    => 'str_count',
							'label'	  => $this->language->get( 'entry_text_count' ),
							'default' => 100
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
						)
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$settings = array_merge( array(
				'screen_name'	=> 'pavothemes',
				'api_key'		=> 'eMsuaAILAnXxLelYz4G4w5iz1',
				'api_secret_key'=> 'kk9gydOrvpZtE2hpi0UGomhXva5nyGxs2eJ304XWcwAsdBfzfz',
				'image_size'	=> 'mini',
				'count'			=> 3,
				'str_count'		=> 100
			), $settings );
		extract($settings);

		$params = array(
			'screen_name'	=> $screen_name,
			'api_key'		=> $api_key,
			'api_secret_key'=> $api_secret_key,
			'image_size'	=> $image_size,
			'count'			=> $count,
			'str_count'		=> $str_count
		);
		$key = 'pavobuilder_twitter_feeds_' . implode('', $params);
		$data = $this->cache->get( $key );
		if ( ! $data ) {
			// auth parameters
			$api_key = $api_key; // Consumer Key (API Key)
			$api_secret = $api_secret_key; // Consumer Secret (API Secret)
			$auth_url = 'https://api.twitter.com/oauth2/token';

			// what we want?
			$data_url = 'https://api.twitter.com/1.1/statuses/user_timeline.json?tweet_mode=extended';

			// get api access token
			$api_credentials = base64_encode($api_key.':'.$api_secret);

			$auth_headers = 'Authorization: Basic '.$api_credentials."\r\n".
			                'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'."\r\n";

			$auth_context = stream_context_create(
			    array(
			        'http' => array(
			            'header' => $auth_headers,
			            'method' => 'POST',
			            'content'=> http_build_query(array('grant_type' => 'client_credentials' )),
			        )
			    )
			);

			$feeds = array();
			try {
				$auth_response = json_decode(file_get_contents($auth_url, 0, $auth_context), true);
				$auth_token = !empty($auth_response['access_token']) ? $auth_response['access_token'] : false;

				if ( $auth_token ) {
					// get tweets
					$data_context = stream_context_create( array( 'http' => array( 'header' => 'Authorization: Bearer '.$auth_token."\r\n", ) ) );

					$feeds = json_decode(file_get_contents($data_url.'&count='.$count.'&screen_name='.urlencode($screen_name), 0, $data_context), true);
					// parse feeds
					if ( $str_count ) {
						foreach ( $feeds as $key => $feed ) {
							$full_text = !empty($feed['full_text']) ? $feed['full_text'] : '';
							if ( strlen($full_text) > $str_count ) {
								$str_pos = substr($full_text, 0, $str_count);
								$white_space_pos = strpos($full_text, ' ', strlen($str_pos));
								if ( $white_space_pos ) {
									$full_text = substr($full_text, 0, $white_space_pos);
								}
								$full_text = $full_text . '...';
								$feeds[$key]['full_text'] = $full_text;
							}
							$date_time = $feed['created_at'];
							$dateTime = new DateTime( $date_time );
							$feeds[$key]['date_format'] = $dateTime->format( 'Y-m-d H:i:s' );
						}
					}
				}
			} catch (Exception $e) {
				
			}
			$this->cache->set( $key, $feeds );
		} else {
			$feeds = $data;
		}
//echo "<pre>".print_r($feeds,1);die;
		// result - do what you want
		$settings['feeds'] = $feeds;
		return $this->load->view( 'extension/module/pavobuilder/pa_twitter/default', array( 'settings' => $settings ) );
	}
}