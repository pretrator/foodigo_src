<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
 */

class ControllerExtensionModulePavoThemer extends Controller {

	public function index(){

        $this->load->language( 'extension/module/pavothemer' );
        $this->load->model( 'extension/module/pavothemer' );
        
        $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
        $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
        $this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.js');
        

        // theme
        $theme = $this->config->get( 'config_theme' );
        // skin
        $skin =  $this->model_extension_module_pavothemer->getConfig( 'default_skin' );

        /**
         * load base styles files
         */
        $files = array(
            'catalog/view/javascript/font-awesome/css/font-awesome.min.css',
        );

        foreach( $files as $file ){
            $this->document->addStyle( $file );
        }

        /**
         * style files
         *
         * stylesheet file
         * stylesheet-rtl file
         * skin file
         */
        $rtl = $this->language->get( 'direction' ) === 'rtl';
        $styles = array(
            // 'stylesheet',
            $rtl ? 'stylesheet-rtl' : 'stylesheet',
            $skin ? 'skins/' . $skin . ( $rtl ? '-rtl' : '' ) : '',
            'customize'
        );

        foreach ( $styles as $style ) {
            if ( ! $style ) continue;
            $style = DIR_TEMPLATE . $theme . '/stylesheet/' . $style;
            $file = false;
            if ( file_exists( $style . '.min.css' ) ) {
                $file = $style . '.min.css';
            } else if ( file_exists( $style . '.css' ) ) {
                $file = $style . '.css';
            }
            if ( $file ) {
                $file = str_replace( DIR_APPLICATION, basename( DIR_APPLICATION ) . '/', $file );
                $this->document->addStyle( $file );
            }
        }

        $this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');

        /**
         * script files
         *
         * common script
         * customize script
         */
        $scripts = array(
            'common',
            'customize'
        );

        foreach ( $scripts as $script ) {
            $script = DIR_TEMPLATE . $theme . '/javascript/' . $script;
            $file = false;
           

            if ( file_exists( $script . '.min.js' ) ) {
                $file = $script . '.min.js';
            } else if ( file_exists( $script . '.js' ) ) {
                $file = $script . '.js';
            }

            if ( $file && filesize( $file ) > 10 ) {  
                $file = str_replace( DIR_APPLICATION, basename( DIR_APPLICATION ) . '/', $file );
                $this->document->addScript( $file );
            }
        }
	}

    public function afterheader(){

    }

    public function productsnav(){
        if (isset($this->request->get['product_id'])) {
            $product_id = (int)$this->request->get['product_id'];
        } else {
            $product_id = 0;
        }

        $this->load->model( 'extension/module/pavothemer' );

        $data = $this->model_extension_module_pavothemer->getProductsNav( $product_id ); 

        $this->response->addHeader('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}