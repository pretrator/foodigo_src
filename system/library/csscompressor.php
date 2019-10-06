<?php

/******************************************************
 * @package Pavo Opencart Theme Framework for Opencart 1.5.x
 * @version 3.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) May 2014 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class csscompressor {

	/**
	 *
	 */
	private $path;

	/**
	 *
	 */
	public function __construct(){
		$this->path = DIR_APPLICATION.'view/javascript/pavothemer/';//DIR_CACHE;
		if (!is_dir($this->path)) {
			@mkdir($this->path, 0777, true);
		}
	}

	/**
	 *
	 */
	public  function process( $content, $url ) {
		global $cssURL; $cssURL = $url;
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
        $content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), ' ', $content);
        $content = preg_replace('/[ ]+([{};,:])/', '\1', $content);
        $content = preg_replace('/([{};,:])[ ]+/', '\1', $content);
        $content = preg_replace('/(\}([^\}]*\{\})+)/', '}', $content);
        $content = preg_replace('/<\?(.*?)\?>/mix', '', $content);
        $content = preg_replace_callback('/url\(([^\)]*)\)/', array( $this, 'callbackReplaceURL'), $content);

        return $content;
	}

	/**
	 *
	 */
	public  function replaceURL( $content, $url ){
		global $cssURL; $cssURL = $url;
		$content = preg_replace_callback('/url\(([^\)]*)\)/', array($this, 'callbackReplaceURL'), $content);
        return $content;
	}

	/**
	 *
	 */
	public  function callbackReplaceURL( $matches ) {
        $url = str_replace(array('"', '\''), '', $matches[1]);
        global $cssURL;
        $url = $this->converturl( $url, $cssURL );
        return "url('$url')";
    }

	/**
	 *
	 */
	public  function converturl( $url, $cssurl ) {
        if (preg_match('/^(\/|http)/', $url))
            return $url;
        $base = dirname($cssurl);
        $base = str_replace(dirname(DIR_APPLICATION).'/', HTTPS_SERVER, dirname($cssurl));

        /*absolute or root*/
        while (preg_match('/^\.\.\//', $url)) {
            $base = dirname($base);
            $url = substr($url, 3);
        }
        $url = $base . '/' . $url;
        return $url;
    }

	/**
	 * Load PHP Gzip Extension
	 *
	 * @param boolean $loadGzip
	 * @return boolean true if loaded.
	 */
	public  function loadGZip( $isGZ ) {
		//$encoding = $this->clientEncoding();
		if (!$isGZ){
			$isGZ=false;
		}
		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			$isGZ=false;
		}
		return $isGZ;
	}

	/**
	 *
	 */
	public function delete( $name ){

		if( $this->isExisted($name) ){
			@unlink( $this->path.$name );
		}
	}

	/**
	 *
	 */
	public function isExisted( $name ) {
		return file_exists($this->path.$name);
	}

	/**
	 *
	 */
	public function saveCache( $content, $name ) {
		if( preg_match_all('/(@import) (url)\(([^>]*?)\)\s*\;?/',$content, $matches) ){
			$output = '';
			foreach( $matches[0] as $key => $value ) {
				$output .= $value."\r\n";
				$content = str_replace( $value, "", $content );
			}
			$content = $output.$content;
		}

		$this->delete($name);
		$file = $this->path . $name;

		$handle = fopen($file, 'w');
		fwrite( $handle,($content) );
		fclose($handle);
		return $name;
	}

	/**
	 *
	 */
	public function getFileURL( $name, $url ) {
		$file = DIR_STORAGE.'cache/'.$name;
		$path = str_replace(dirname(DIR_STORAGE).'/', '', $file); 
		return str_replace(dirname(DIR_STORAGE).'/', '', $file);
	}

	public function file_get_contents( $file ) {
		$file = $this->path.$file;
		if ( file_exists($file) ) {
			return file_get_contents($file);
		}
	}
}