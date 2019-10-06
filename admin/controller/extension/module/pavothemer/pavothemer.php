<?php 
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license GNU General Public License version 2
 */
 
if ( ! defined( 'DIR_SYSTEM' ) ) exit();

if ( ! class_exists( 'PavoThemerController' ) ) :

	class PavoThemerController extends Controller {

		/**
		 * static $_instance insteadof PavoThemerController class
		 *
		 * @var PavoThemerController
		 * @since 1.0.0
		 */
		private static $_instance = null;

		/**
		 * Data array, pass it when setOutput
		 *
		 * @var $data array
		 * @since 1.0.0
		 */
		public $data = array(
				'notices'	=> array()
			);

		/**
		 * Template path, pass it to render template
		 *
		 * @var template string or null
		 * @since 1.0.0
		 */
		public $template = null;

		/**
		 * errors storge
		 * 
		 * @var $errors array
		 */
		protected $errors = array();

		/**
		 * Constructor Framework Controller
		 * @since 1.0.0
		 */
		public function __construct( $registry ) {
			parent::__construct( $registry );

			// theme init constant
			$this->themeInit();
			require_once dirname( __FILE__ ) . '/config.php';
			require_once PAVOTHEMER_DIR . '/helper/settings.php';
			require_once PAVOTHEMER_DIR . '/helper/api.php';
			require_once PAVOTHEMER_DIR . '/helper/theme.php';
			require_once PAVOTHEMER_DIR . '/helper/sample.php';
			require_once PAVOTHEMER_DIR . '/helper/skincreator.php';
		}

		private function themeInit() {
			$theme = $this->config->get( 'config_theme' );
			$themeConfig = DIR_CATALOG . 'view/theme/' . $theme . '/development/config.json';
			if ( file_exists( $themeConfig ) ) {
				$configs = json_decode( file_get_contents( $themeConfig ), true );
				if ( ! empty( $configs['sample_host'] ) ) {
					defined( 'PAVOTHEMER_SAMPLES' ) || define( 'PAVOTHEMER_SAMPLES', $configs['sample_host'] );
				}
				if ( ! empty( $configs['theme'] ) ) {
					defined( 'PAVOTHEMER_THEME' ) || define( 'PAVOTHEMER_THEME', $configs['theme'] );
				}
			}
			defined( 'PAVOTHEMER_THEME' ) || define( 'PAVOTHEMER_THEME', $theme );
		}

	}

endif;
