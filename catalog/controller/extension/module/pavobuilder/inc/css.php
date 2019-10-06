<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Css extends Controller {

	private static $instance = null;

	private $btPath = '';

	public static function instance( $registry ) {
		if ( ! self::$instance ) {
			self::$instance = new self( $registry );
		}
		return self::$instance;
	}

	public function __construct( $registry ) {
		parent::__construct( $registry );
	}

	public function build( $content = array() ) {
		if ( ! class_exists( 'scssc' ) ) {
			require_once DIR_STORAGE . 'vendor/leafo/scssphp/scss.inc.php';
		}
		$scssc = new scssc();
		$scssc->setFormatter( 'scss_formatter_compressed' );
		$this->btPath = defined( 'HTTPS_CATALOG' ) ? DIR_APPLICATION . 'view/stylesheet/sass' : dirname( DIR_APPLICATION ) . '/admin/view/stylesheet/sass';

		$theme = $this->config->get( 'config_theme' );
		$variablesFiles = array(
				( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $theme . '/sass/bootstrap/_variables.scss',
				( defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION ) . 'view/theme/' . $theme . '/sass/_variables.scss'
			);

		$input = array();
		// maybe we need to override bootstrap variables
		foreach ( $variablesFiles as $file ) {
			if ( file_exists( $file ) ) {
				$input[] = '@import "' . $file . '";';
			}
		}

		if ( ! $input ) {
			$input[] = '$screen-lg-min: ' . $this->getBootstrapVariable( 'screen-lg-min' ) . ' ! default;';
			$input[] = '$screen-md-max: ' . $this->getBootstrapVariable( 'screen-md-max' ) . ' ! default;';
			$input[] = '$screen-md-min: ' . $this->getBootstrapVariable( 'screen-md-min' ) . ' ! default;';
			$input[] = '$screen-sm-max: ' . $this->getBootstrapVariable( 'screen-sm-max' ) . ' ! default;';
			$input[] = '$screen-sm-min: ' . $this->getBootstrapVariable( 'screen-sm-min' ) . ' ! default;';
			$input[] = '$screen-xs-max: ' . $this->getBootstrapVariable( 'screen-xs-max' ) . ' ! default;';
			$input[] = '$screen-xs-min: ' . $this->getBootstrapVariable( 'screen-xs-min' ) . ' ! default;';
		}

		if ( $content ) foreach ( $content as $key => $row ) {
			$input[] = implode( '', $this->elementRender( $row ) );
		}

		$output = $scssc->compile( implode( '', $input ) );
		return $output;
	}

	public function getBootstrapVariable( $var = '', $default = '' ) {
		$path = defined( 'HTTPS_CATALOG' ) ? DIR_APPLICATION . 'view/stylesheet/sass' : dirname( DIR_APPLICATION ) . '/admin/view/stylesheet/sass';
		$variablesContent = file_get_contents( $this->btPath . '/bootstrap/_variables.scss' );
		preg_match( '/\$'.$var.'[\s]*[:]?(.*?)[\s]+!default/', $variablesContent, $match );

		return isset( $match[1] ) ? $match[1] : $default;
	}

	/**
	 * render css each screen mode
	 *
	 * @param $element array
	 * @param $screen string
	 */
	private function elementRender( $element = array() ) {
		$css = array();
		$settings = ! empty( $element['settings'] ) ? $element['settings'] : array();
		$id = isset( $settings['uniqid_id'] ) ? $settings['uniqid_id'] : '';
		if ( $id ) {
			// $responsive = ! empty( $element['responsive'] ) ? $element['responsive'] : array();

			// render base mode
			if ( ! empty( $element['settings'] ) ) {
				$element['settings'] = array_merge( $settings, $element['settings'] );
			}
			// $css[] = implode( '', $this->singularMode( $element ) );
			$responsive = ! empty( $element['responsive'] ) ? $element['responsive'] : array();

			// start responsive
			if ( $responsive ) {
				// $responsive = array_reverse( $responsive );

				foreach ( $responsive as $type => $opt ) {
					// if ( ! empty( $opt['cols'] ) ) {
						$width = array();
						if ( ! empty( $opt['cols'] ) ) {
							$styles = ! empty( $opt['styles'] ) ? $opt['styles'] : array();
							if ( ! empty( $styles['width'] ) ) {
								$width[] = '[data-uniqid="'.$id.'"].col-' . $type . '-' . $opt['cols'] . '{';
								$width[] = 'width:' . $styles['width'] . '% !important;';
								$width[] = '}';
							}
						}

						// width
						if ( ! empty( $opt['cols'] ) && $width ) {
							$customWidth = array();
							switch ( $type ) {
								case 'lg':
									$customWidth[] = '@media (min-width: $screen-lg-min){';
									break;

								case 'md':
									$customWidth[] = '@media (max-width: $screen-md-max) and (min-width: $screen-md-min){';
									break;

								case 'sm':
									$customWidth[] = '@media (max-width: $screen-sm-max) and (min-width: $screen-sm-min){';
									break;

								default:
									$customWidth[] = '@media (max-width: $screen-xs-max) and (min-width: $screen-xs-min){';
									break;
							}
							$customWidth[] = implode( '', $width );
							$customWidth[] = '}';
							$css[] = implode( '', $customWidth );
						}
						// end width

						// overide normal screen
						$cloneElement = $element;
						if ( ! empty( $opt['settings'] ) ) {
							$cloneElement['settings'] = array_merge($element['settings'], $opt['settings']);
						}
						$cloneElement['settings']['uniqid_id'] = $id;
						$cloneElement['settings']['selectors'] = isset( $element['settings'], $element['settings']['selectors'] ) ? $element['settings']['selectors'] : array();
						$customStyle = implode( '', $this->singularMode( $cloneElement ) );

						if ( $customStyle ) {
							// styles
							$styles = array();
								switch ( $type ) {
									case 'lg':
										// $styles[] = '@media (min-width: $screen-lg-min){';
										break;

									case 'md':
										$styles[] = '@media (max-width: $screen-md-max){';
										break;

									case 'sm':
										$styles[] = '@media (max-width: $screen-sm-max){';
										break;

									default:
										$styles[] = '@media (max-width: $screen-xs-max){';
										break;
								}
							$styles[] = $customStyle;
							if ( $type !== 'lg' ) {
								$styles[] = '}';
							}
							// end overide normal screen
							$css[] = implode( ' ', $styles );
							// end styles
						}
					// }
				}
			}
			// end responsive

			// sub element content
			$subs = ! empty( $element['columns'] ) ? $element['columns'] : ( ! empty( $element['elements'] ) ? $element['elements'] : array() );
			$subs = !empty( $element['tabs'] ) ? $element['tabs'] : $subs;
			if ( $subs ) {
				foreach ( $subs as $sub ) {
					if ( isset( $sub['row'] ) ) {
						$css[] = $this->build( array( $sub['row'] ) );
					} else {
						$css[] = implode( '', $this->elementRender( $sub ) );
					}
				}
			}
		}

		return $css;
	}

	/**
	 * singular mode
	 */
	private function singularMode( $element = array() ) {
		$css = array();
		$settings = ! empty( $element['settings'] ) ? $element['settings'] : array();
		$id = isset( $settings['uniqid_id'] ) ? $settings['uniqid_id'] : '';
		// Specific attributes
		if ( isset( $settings['element'], $settings['no_space'] ) && $settings['element'] == 'pa_row' && $settings['no_space'] ) {
			$css[] = '[data-uniqid="'.$id.'"] .pa-row-inner > .row {';
			$css[] = 'margin: 0;';
			$css[] = '}';
			$css[] = '[data-uniqid="'.$id.'"] .pa-row-inner > .row > .pa-column-container {';
			$css[] = 'margin: 0;';
			$css[] = 'padding: 0;';
			$css[] = '}';
		}

		if ( isset( $settings['parallax'] ) && $settings['parallax'] ) {
			$css[] = '[data-uniqid="'.$id.'"] {';
			$css[] = 'background-size: cover;';
			$css[] = 'background-attachment: fixed;';
			$css[] = 'background-position: center center;';
			$css[] = 'position: relative;';
			$css[] = '}';
		}
		// background
		if ( ! empty( $settings['background_image'] ) ) {
			$background_url = ( defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTP_SERVER ) . 'image/';
			if ( defined('IMAGE_URL') ) {
				$background_url = IMAGE_URL;
			}
			$css[] = '[data-uniqid="'.$id.'"] {';
			$css[] = ! empty( $settings['background_image'] ) ? 'background-image: url( ' . $background_url . $settings['background_image'].' )' . ';' : '';
			if ( ! isset( $settings['parallax'] ) || ! $settings['parallax'] ) {
				$css[] = ! empty( $settings['background_repeat'] ) ? 'background-repeat: '.$settings['background_repeat'] . ';' : '';
				$css[] = ! empty( $settings['background_position'] ) ? 'background-position: '.$settings['background_position'] . ';' : '';
			} else {
				$css[] = 'background-size: cover;';
				$css[] = 'background-attachment: fixed;';
				$css[] = 'background-position: center center;';
				$css[] = 'position: relative;';
			}
			$css[] = '}';
		}

		/**
		 * effect duration
		 */
		if ( $id && ! empty( $settings['effect_duration'] ) ) {
			$css[] = '[data-uniqid="'.$id.'"].animated{';
			$css[] = 'animation-duration:' . $settings['effect_duration'] . ';';
			$css[] = '}';
		}

		$layout_onion = ! empty( $settings['layout_onion'] ) ? $settings['layout_onion'] : array();
		$styles = ! empty( $settings['styles'] ) ? array_merge( $layout_onion, $settings['styles'] ) : $layout_onion;

		if ( ! empty( $styles ) || ! empty( $settings['color'] ) ) {
			$css[] = '[data-uniqid="'.$id.'"]{';
			$css[] = ! empty( $settings['color'] ) ? 'color:' . $settings['color'] . ';' : '';
			foreach ( $styles as $attr => $value ) {
				$parser = explode( '_', $attr );
				$name = str_replace( '_', '-', $attr );
				if ( isset( $parser[1] ) && in_array( $parser[1], array( 'top', 'left', 'bottom', 'right' ) ) ) {
					// if ( isset( $settings['disable_padding_margin'] ) && $settings['disable_padding_margin'] && in_array( $parser[0], array( 'padding', 'margin' ) ) ) {
					// 	$css[] = $value ? $name . ':0px !important;' : '';
					// } else {
					// 	$css[] = $value != '' ? $name . ':' . $value . 'px !important;' : '';
					// }
					$css[] = $value != '' ? $name . ':' . $value . 'px !important;' : '';
				} else {
					$css[] = $value != '' ? $name . ':' . $value . ' !important;' : '';
				}
			}
			$css[] = '}';
		}

		$selectors = ! empty( $settings['selectors'] ) ? $settings['selectors'] : array();
		$pixel_attr = array(
			'font-size',
			'margin',
			'padding',
			'margin-right', 'margin-left', 'margin-bottom', 'margin-top',
			'padding-right', 'padding-left', 'padding-bottom', 'padding-top',
			'border-width', 'line-height'
		);
		foreach ( $selectors as $name => $opts ) {
			$slects = ! empty( $opts['selectors'] ) ? $opts['selectors'] : array();
			$css_attr = ! empty( $opts['css_attr'] ) ? $opts['css_attr'] : '';
			$value = ! empty( $settings[$name] ) ? $settings[$name] : '';
			if ( $value == '' ) continue;
			if ( is_string( $slects ) ) {
				$slects = explode( ',', $slects );
			}

			if ( ! empty( $slects ) ) {
				$multi_attr = array();
				foreach ( $slects as $el ) {
					$multi_attr[] = '[data-uniqid="'.$id.'"] ' . $el . '';
				}
				$css[] = implode( ',', $multi_attr );
				$css[] = '{';
			} else {
				$css[] = '[data-uniqid="'.$id.'"]{';
			}
			if ( $css_attr === 'background-image' ) {
				// $css[] = $css_attr . ': url(' . ( defined( 'HTTPS_CATALOG' ) ? HTTPS_CATALOG : HTTP_SERVER ) . 'image/' . $value . ');';
				// if ( ! isset( $settings['parallax'] ) || ! $settings['parallax'] ) {
				// 	$css[] = ! empty( $settings['background_repeat'] ) ? 'background-repeat: '.$settings['background_repeat'] . ';' : '';
				// 	$css[] = ! empty( $settings['background_position'] ) ? 'background-position: '.$settings['background_position'] . ';' : '';
				// } else {
				// 	$css[] = 'background-size: cover;';
				// 	$css[] = 'background-attachment: fixed;';
				// 	$css[] = 'background-position: center center;';
				// 	$css[] = 'position: relative;';
				// }
			} else {
				$css[] = $css_attr . ':' . $value;
			}
			if ( in_array( $css_attr, $pixel_attr ) ) {
				$css[] = 'px;';
			} else {
				$css[] = ';';
			}

			$css[] = '}';

		}
		return $css;
	}

	/**
	 * create css profile for layout
	 */
	public function save( $id = 0, $content = array() ) {
		if ( ! $id ) return true;
		if ( ! $content ) return true;

		$this->load->library( 'rtlcss' );
		$content = $this->build( $content );
		$basePath = defined( 'DIR_CATALOG' ) ? DIR_CATALOG : DIR_APPLICATION;

		if( ! is_dir( $basePath . 'view/theme/default/stylesheet/pavobuilder/' ) ) {
			@mkdir( $basePath . 'view/theme/default/stylesheet/pavobuilder/', 0755, true );
		}

		$file = $basePath . 'view/theme/default/stylesheet/pavobuilder/' . $id . '.css';
		try {
			if ( file_exists( $file ) && ! is_writable( dirname( $file ) ) ) {
				throw new Exception( $this->language->get( 'text_warning' ) . ' <strong>' . dirname( $file ) . '</strong>' );
			}
			$fo = fopen( $file, 'w' );
			if ( $fo ) {
				fwrite( $fo, $content );
				fclose( $fo );
			} else {
				throw new Exception( $this->language->get( 'text_warning' ) . ' <strong>' . $file . '</strong>' );
			}

			$file = $basePath . 'view/theme/default/stylesheet/pavobuilder/' . $id . '-rtl.css';
			if ( file_exists( $file ) && ! is_writable( dirname( $file ) ) ) {
				throw new Exception( $this->language->get( 'text_warning' ) . ' <strong>' . dirname( $file ) . '</strong>' );
			}
			$fo = fopen( $file, 'w' );
			if ( $fo ) {
				fwrite( $fo, rtlcss::transform( $content ) );
				return fclose( $fo );
			} else {
				throw new Exception( $this->language->get( 'text_warning' ) . ' <strong>' . $file . '</strong>' );
			}
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}

}