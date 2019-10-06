<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */
if ( ! defined( 'DIR_SYSTEM' ) ) exit();

class PavoThemerHelper {

	private $_customizes = array();

	private static $instance = array();

	public function __construct( $theme = 'default' ) {
		$this->theme = $theme;
	}

	public static function instance( $theme = 'default' ) {
		if ( empty( self::$instance[$theme] ) ) {
			self::$instance[$theme] = new self( $theme );
		}

		return self::$instance[$theme];
	}

	/**
	 * write file
	 */
	public function writeFile( $file = '', $content = '' ) {
		if ( ! is_writable( dirname( $file ) ) || ( file_exists( $file ) && ! is_writable( $file ) ) ) return false;
		$fopen = fopen( $file, 'w+' );
		if ( $fopen ) {
			fwrite( $fopen, $content );
			return fclose( $fopen );
		}

		return false;
	}

	/**
	 * Get Skins
	 *
	 * @param $theme string
	 * @return array skins
	 */
	public function getSkins() {
		$infos = glob( ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/stylesheet/skins/*.json' );
		$skins = array();
		foreach ( $infos as $file ) {
			$filename = basename( $file, '.json' );
			$data = json_decode( file_get_contents( $file ), true );
			if ( $filename !== 'cache' ) {
				$cssFile = ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/stylesheet/skins/'.$filename.'.css';
				if ( file_exists( $cssFile ) && $filename != 'cache.json' ) {
					$skins[] = array(
						'text'	=> ! empty( $data['name'] ) ? $data['name'] : $name,
						'value'	=> $filename
					);
				}
			}
		}
		return $skins;
		// return $this->files2Options( glob( ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/stylesheet/skins/*.css' ), '', '.css', array( 'cache' ) );
	}

	/**
	 *
	 * Get Css Profiles
	 *
	 * @param $theme string 'default'
	 * @return array css files
	 * @since 1.0.0
	 */
	public function getCssProfiles() {
		return $this->files2Options( glob( ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/stylesheet/customizes/*.css' ), '', '.css' );
	}

	public function getCustomizes() {
		// setting files
		$files = $this->getCustomizeFiles( $this->theme );
		$settingHelder = PavoThemerSettingHelper::instance( $this->theme );
		if ( $files ) {
			foreach ( $files as $file ) {
				$fileInfo = pathinfo( $file );
				$this->_customizes[ $fileInfo['filename'] ] = $settingHelder->getSettingFile( $file );
			}
		}

		return $this->_customizes;
	}

	public function getThemeInfo( ){

		$data = array();

		$file = ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/development/info.xml';

		libxml_use_internal_errors( true );
		$xml = simplexml_load_file( $file, 'SimpleXMLElement', LIBXML_NOCDATA );
		if ( $xml === false ) {
			return $data;
		}
		if ( $xml instanceof SimpleXMLElement ) {
			foreach ($xml as $key => $value) {
				foreach ( (array) $value as $k => $v) {
					$data[$key] = $v;
				}
			}
        }
        return $data;
	}

	/**
	 * Get customize files
	 */
	public function getCustomizeFiles() {
		return glob( ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/development/customizes/*.xml' );
	}

	public function getBgImages() {
		$files = glob( ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/image/bg-images/*' );
		$results = array();
        $results[] = array(
                    'text'  => 'None',
                    'value' => ""
                );
		foreach ( $files as $file ) {
			$results[] = array(
					'text'	=> basename( $file ),
					'value'	=> "'../../image/bg-images/" . basename($file) . "'"
				);
		}

		return $results;
	}

	/**
	 * Get headers layouts
	 */
	public function getHeaders() {
		return $this->files2Options( glob( ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/template/common/header*.twig' ), 'header' );
	}

	/**
	 * Get footers layouts
	 */
	public function getFooters() {
		return $this->files2Options( glob( ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/template/common/footer*.twig' ), 'footer' );
	}

	/**
	 * Get Product Detail Layouts
	 */
	public function getProductDefailLayouts() {
		return $this->files2Options( glob( ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/template/product/product*.twig' ), 'product' );
	}

	/**
	 * Get Product Detail Layouts
	 */
	public function getProductCategoryLayouts() {
		return $this->files2Options( glob( ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/template/product/category*.twig' ), 'category' );
	}

	public function getProductGridLayouts() {
		return $this->files2Options( glob( ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/template/product/layout/*.twig' ), 'category' );
	}


	/**
	 * file to select options
	 */
	public function files2Options( $files = array(), $prefix = '', $ext = '.twig', $ignores = array() ) {
		$options = array();
		if ( $files ) {
			foreach ( $files as $file ) {
				$name = basename( $file, $ext );
				if ( ! in_array( $name, $ignores ) ) {
					$options[] = array(
							'text'	=> str_replace( "_", " ", implode( ' ', array_map( 'ucfirst', array_merge( array( $prefix, '' ), array( str_replace( $prefix, '', $name ) ) ) ) ) ),
							'value'	=> $name
						);
				}
			}
		}
		return $options;
	}

	/**
	 * get animate effects
	 */
	public function getAnimates() {
		$animates = array();
		$file = ( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $this->theme . '/stylesheet/animate.min.css';

		if ( file_exists( $file ) ) {
			$content = file_get_contents( $file );
			// get all current animate supported
			preg_match_all( '/[^.]keyframes[^.](.*?)\{/i', $content, $matches );
			if ( ! empty( $matches[1] ) ) {
				$matches[1] = array_map( 'trim', $matches[1] );
				foreach ( $matches[1] as $animate ) {
					$animates[$animate] = ucfirst( $animate );
				}
			}
		}

		return $animates;
	}
}
