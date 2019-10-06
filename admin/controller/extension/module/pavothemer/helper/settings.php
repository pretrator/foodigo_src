<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */
if ( ! defined( 'DIR_SYSTEM' ) ) exit();

/**
 *
 */
class PavoThemerSettingHelper {

	/**
	 * instance instead of new ClassName
	 *
	 * @var PavoThemerSettingHelper
	 */
	private static $_instance = array();

	/**
	 * setting files
	 *
	 * @since 1.0.0
	 */
	private $_settings = array();

	public $theme = null;

	public function __construct( $theme = 'default' ) {
		$this->theme = $theme;
	}

	public static function instance( $theme = 'default' ) {
		if ( empty( self::$_instance[$theme] ) ) {
			self::$_instance[$theme] = new self( $theme );
		}
		return self::$_instance[$theme];
	}

	/**
	 *
	 * Get all xml data settings
	 *
	 * @param $theme - themename
	 * @return array settings page
	 */
	public function getSettings() {
		// setting files
 
		
		$files = $this->getSettingFiles();
		if ( $files ) {
			foreach ( $files as $file ) {
				$fileInfo = pathinfo( $file );
				$this->_settings[ $fileInfo['filename'] ] = $this->getSettingFile( $file );
			}
		}

		$files = $this->getDefaultSettingFiles();
		if ( $files ) {
			foreach ( $files as $file ) {
				$fileInfo = pathinfo( $file );
				if( !isset($this->_settings[ $fileInfo['filename'] ]) ){
					$this->_settings[ $fileInfo['filename'] ] = $this->getSettingFile( $file );
				}
			}
		}

		return $this->_settings;
	}

	/**
	 *
	 * Default setting files
	 *
	 * @since 1.0.0
	 * @param $theme string
	 * @return array
	 */
	public function getDefaultSettingFiles(){  
		return glob( DIR_TEMPLATE . 'extension/module/pavothemer/settings/*.xml' );
	}
	
	/**
	 *
	 * Default setting files
	 *
	 * @since 1.0.0
	 * @param $theme string
	 * @return array
	 */
	public function getSettingFiles() {
		return glob( DIR_CATALOG . 'view/theme/' . $this->theme . '/development/settings/*.xml' );
	}

	/**
	 * get setting in single file
	 *
	 * @param file
	 * @return getXmlDomContent method as array
	 * @since 1.0.0
	 */
	public function getSettingFile( $file = '' ) {
		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			return array();
		}

		$data = array();
		libxml_use_internal_errors( true );
		$xml = simplexml_load_file( $file, 'SimpleXMLElement', LIBXML_NOCDATA );
		if ( $xml === false ) {
			return $data;
		}
		$data = $this->getXmlDomContent( $xml );
		if ( ! empty( $data['item'] ) ) {
			$group = array();
			foreach ( $data['item'] as $item ) {
				if ( isset( $item['group'] ) ) {
					$group[ strtolower( str_replace( ' ', '-', $item['group'] ) ) ] = $item['group'];
				}
			}
			$data['group'] = array_unique( $group );
		}
		return $data;
	}

	/**
	 * 
	 * Parse xml content as array
	 *
	 * @since 1.0.0
	 * @param xml
	 * @return array
	 */
	public function getXmlDomContent( $xml = null ) {
		if ( ! $xml ) return $xml;
		if ( is_string( $xml ) ) {
			return $xml;
		}
		if ( $xml instanceof SimpleXMLElement ) {
			$xml = get_object_vars( $xml );
		}
		$data = array();
		foreach ( $xml as $k => $notes ) {
			$subData = array();
			if ( $notes instanceof SimpleXMLElement ) {
				$notes = $this->getXmlDomContent( $notes );
			}
			if ( is_array( $notes ) ) {
				$subData = array();
				foreach ( $notes as $k2 => $note ) {
					if ( $note instanceof SimpleXMLElement ) {
						$subData[$k2] = $this->getXmlDomContent( $note );
					} else {
						$subData[$k2] = $note;
					}
				}
			} else {
				$subData = $notes;
			}

			$data[$k] = $subData;
		}
		return $data;
	}

}
