<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */
if ( ! defined( 'DIR_SYSTEM' ) ) exit();

class PavothemerApiHelper {

	public static $error = '';
	public static $errno = '';

	/**
	 * get request
	 */
	public static function get( $url = '', $data = array() ) {
		$data['method'] = 'GET';
		return self::request( $url, $data );
	}

	/**
	 * post data
	 */
	public static function post( $url = '', $data = array() ) {
		$data['method'] = 'POST';
		return self::request( $url, $data );
	}

	/**
	 * make request to api host
	 */
	public static function request( $url = '', $data = array() ) {

		$data = array_merge( array(
				'method'		=> 'GET',
				'headers'		=> array(),
				'body'			=> array(),
				'timeout'		=> 300,
				'user-agent'	=> '',
				'httpversion'	=> '1.0',
				'filename'		=> false
			), $data );

		$file_open = false;
		if ( $data['filename'] ) {
			$file_open = fopen( $data['filename'], 'w+' );
		}

		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_TIMEOUT, $data['timeout'] );
		curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $data['timeout'] );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );

		// this option is required
		curl_setopt( $curl, CURLOPT_REFERER, HTTP_CATALOG );

		if ( ! empty( $data['user-agent'] ) ) {
			curl_setopt( $curl, CURLOPT_USERAGENT, $data['user-agent'] );
		}

		$method = strtoupper( $data['method'] );
		$post_fields_data = http_build_query( $data['body'] );
		switch ( $method ) {
			case 'HEAD':
				curl_setopt( $handle, CURLOPT_NOBODY, true );
				break;
			case 'POST':
					curl_setopt( $curl, CURLOPT_POST, true );
					curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_fields_data );
				break;

			case 'PUT':
					curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, 'PUT' );
					curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_fields_data );
				break;

			default:
					curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $method );
					if ( $data['body'] ) {
						curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_fields_data );
					}
				break;
		}

		if ( !ini_get('open_basedir') ) {
			curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
		}
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		if ( $file_open ) {
			curl_setopt( $curl, CURLOPT_FILE, $file_open );
		}

		if ( $data['httpversion'] == '1.0' )
			curl_setopt( $curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
		else
			curl_setopt( $curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );

		if ( ! empty( $data['headers'] ) ) {
			curl_setopt( $curl, CURLOPT_HTTPHEADER, $data['headers'] );
		}

		$output = curl_exec( $curl );

		if ( $file_open ) {
			if ( $output ) {
				fwrite( $file_open, $output );
			}
			fclose( $file_open );
		}

		self::$error = curl_error( $curl );
		self::$errno = curl_errno( $curl );

		$results = array(
				'response'	=> array(
						'code'		=> curl_getinfo( $curl, CURLINFO_HTTP_CODE ),
						'message'	=> self::$errno ? self::$error : ''
					),
				'body'		=> $output
			);

		return $results;
	}

}