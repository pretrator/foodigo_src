<?php
/**
 * $Desc$
 *
 * @version    $Id$
 * @package    $package$
 */

if ( ! class_exists( 'PA_Autoload' ) ) {

	class PA_Autoload {

		/**
		 * base path of includes folder
		 * @var string || null
		 */
		private $path = null;

		/**
		 * PA_Autoload Constructor
		 */
		public function __construct() {
			/**
			 * plugin includes path
			 */
			$this->path = dirname( DIR_SYSTEM ) . '/catalog/controller/extension/module/pavobuilder/inc/';

			if ( function_exists( '__autoload' ) ) {
	            spl_autoload_register( '__autoload' );
	        }

	        spl_autoload_register( array( $this, 'autoload' ) );
		}

		/**
		 * Get file name from class name
		 * @param string
		 * @return string file path
		 */
		private function get_file_name( $classname = '' ) {
			if ( $classname ) {
				return str_replace( 'pa_', '', strtolower( $classname ) ) . '.php';
			}
		}

		/**
		 * include single file
		 */
		private function _include( $file = '' ) {
			if ( file_exists( $file ) && is_readable( $file ) ) {
				require_once $file;
			}
		}

		/**
		 * init include file when class called
		 * @var string
		 */
		public function autoload( $classname ) {
			$file = $this->get_file_name( $classname );

			if ( strpos( $classname, 'PA_Widget_' ) === 0 ) {
				$file = 'widgets/' . str_replace( 'widget_', '', $file );
			}

			$this->_include( $this->path . $file );
		}

	}

	new PA_Autoload();

}