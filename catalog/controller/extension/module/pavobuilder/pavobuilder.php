<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

// include autoload file
require_once dirname( __FILE__ ) . '/inc/autoload.php';

class PavoBuilder extends Controller {

	public static $instance = null;

	public static function instance( $registry ) {
		if ( ! self::$instance ) {
			self::$instance = new self( $registry );
		}
		return self::$instance;
	}

	public function __construct( $registry ) {
		parent::__construct( $registry );
	}

	public function __get( $key = '' ) {
		switch ( $key ) {
			case 'widgets':
					return PA_Widgets::instance( $this->registry );
				break;

			case 'css':
					return PA_Css::instance( $this->registry );
				break;

			default:
					return $this;
				break;
		}
	}

}