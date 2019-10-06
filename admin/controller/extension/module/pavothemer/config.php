<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

defined( 'PAVOTHEMER_DIR' ) || define( 'PAVOTHEMER_DIR', dirname( __FILE__ ) );
// api
defined( 'PAVOTHEMER_API' ) || define( 'PAVOTHEMER_API', 'http://pavothemes.com/api/' );
defined( 'PAVOTHEMER_THEME' ) || define( 'PAVOTHEMER_THEME', false );
defined( 'PAVOTHEMER_MODULE_API' ) || define( 'PAVOTHEMER_MODULE_API', PAVOTHEMER_API . 'modules.php' );
defined( 'PAVOTHEMER_THEMES_API' ) || define( 'PAVOTHEMER_THEMES_API', PAVOTHEMER_API . 'themes.php?t='.time() );

// sample
defined( 'PAVOTHEMER_SAMPLES' ) || define( 'PAVOTHEMER_SAMPLES', 'http://wpsampledemo.com/opencart/' );
defined( 'PAVOTHEMER_SAMPLES_MODULE_API' ) || define( 'PAVOTHEMER_SAMPLES_MODULE_API', PAVOTHEMER_SAMPLES . 'getextensions.php?debug=1' );
defined( 'PAVOTHEMER_SAMPLES_API' ) || define( 'PAVOTHEMER_SAMPLES_API', PAVOTHEMER_SAMPLES . 'samples.php' );
defined( 'PAVOTHEMER_LANG_API' ) || define( 'PAVOTHEMER_LANG_API', PAVOTHEMER_SAMPLES . 'languages.php' );
defined( 'PAVOTHEMER_DEBUG_MODE' ) || define( 'PAVOTHEMER_DEBUG_MODE', true );
defined( 'PAVOTHEMER_DOWN_DEMO_IMAGE' ) || define( 'PAVOTHEMER_DOWN_DEMO_IMAGE', false );