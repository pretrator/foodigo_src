<?php
/******************************************************
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/
class PA_Widget_Interactive_Banner extends PA_Widgets {

    public function fields() {
        return array(
            'mask'      => array(
                'icon'  => 'fa fa-pencil-square-o',
                'label' => $this->language->get( 'entry_interactive_banner' )
            ),
            'tabs'  => array(
                'general'       => array(
                    'label'     => $this->language->get( 'entry_general_text' ),
                    'fields'    => array(
                        array(
                            'type'  => 'hidden',
                            'name'  => 'uniqid_id',
                            'label' => $this->language->get( 'entry_row_id_text' ),
                            'desc'  => $this->language->get( 'entry_column_desc_text' )
                        ),
                        array(
                            'type'  => 'select',
                            'name'  => 'layout',
                            'label' => $this->language->get( 'entry_layout_text' ),
                            'default' => 'pa_interactive_banner',
                            'options'   => $this->getLayoutsOptions(),
                            'none'  => false
                        ),
                        array(
                            'type'  => 'image',
                            'name'  => 'banner_image',
                            'label' => $this->language->get( 'entry_banner_image' )
                        ),
                        array(
                            'type'  => 'text',
                            'name'  => 'banner_size',
                            'label' => $this->language->get( 'entry_banner_image_size' ),
                            'desc'  => $this->language->get( 'entry_banner_image_desc' ),
                            'default'       => 'full',
                            'placeholder'   => '200x400'
                        ),
                        array(
                            'type'  => 'text',
                            'name'  => 'banner_title',
                            'lang'  => true,
                            'label' => $this->language->get( 'entry_banner_title' ),
                            'language'  => true
                        ),
                        array(
                            'type'  => 'editor',
                            'name'  => 'banner_subtitle',
                            'lang'  => true,
                            'label' => $this->language->get( 'entry_banner_subtitle' ),
                            'language'  => true
                        ),
                        array(
                            'type'      => 'editor',
                            'name'      => 'banner_content',
                            'label'     => $this->language->get( 'entry_banner_content' ),
                            'language'  => true,
                            'default'   => ''
                        ),
                        array(
                            'type'    => 'text',
                            'name'    => 'button_text',
                            'label'   => $this->language->get( 'entry_banner_button' ),
                            'default' => 'Shop Now',
                            'language'=> true
                        ),
                        array(
                            'type'  => 'text',
                            'name'  => 'button_link',
                            'label' => $this->language->get( 'entry_button_link' ),
                            'desc'  => $this->language->get( 'entry_banner_link_desc' ),
                            'default'       => '#'
                        ),
                        array(
                            'type'  => 'select',
                            'name'  => 'button_type',
                            'label' => $this->language->get( 'entry_button_type' ),
                            'default'   => 'content',
                            'none' => false,
                            'options'   => array(
                                array(
                                    'value' => 'content',
                                    'label' => 'Content'
                                ),
                                array(
                                    'value' => 'video',
                                    'label' => 'Video'
                                )
                            )
                        ),
                        array(
                            'type'  => 'text',
                            'name'  => 'extra_class',
                            'label' => $this->language->get( 'entry_extra_class_text' ),
                            'default' => '',
                            'desc'  => $this->language->get( 'entry_extra_class_desc_text' )
                        )

                    )
                ),
                'style'             => array(
                    'label'         => $this->language->get( 'entry_styles_text' ),
                    'fields'        => array(
                        array(
                            'type'  => 'layout-onion',
                            'name'  => 'layout_onion',
                            'label' => 'entry_box_text'
                        )
                    )
                )
            )
        );
    }

    public function render( $settings = array(), $content = '' ) {
        $this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
        $this->load->model( 'tool/image' );

        $class = array();
        if ( ! empty( $settings['extra_class'] ) ) {
            $class[] = $settings['extra_class'];
        }
        if ( ! empty( $settings['effect'] ) ) {
            $class[] = $settings['effect'];
        }
        $settings['class'] = implode( ' ', $class );

        if( defined("IMAGE_URL")){
            $server = IMAGE_URL;
        } else {
            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }

        $settings['width'] = $settings['height'] = false;
        if ( ! empty( $settings['banner_image'] ) ) {
            $settings['banner_size'] = strtolower( $settings['banner_size'] );
            $src = empty( $settings['banner_size'] ) || $settings['banner_size'] == 'full' ? $server . $settings['banner_image'] : false;

            if ( strpos( $settings['banner_size'], 'x' ) ) {
                list( $width, $height ) = explode('x', $settings['banner_size']);
                $settings['width'] = $width;
                $settings['height'] = $height;
                $src = $this->getImageLink($settings['banner_image'], $settings['banner_size']);
            }

            $settings['banner_image'] = $src ? $src : '';
        }

        $settings['heading_title']   = !empty( $settings['banner_title'] ) ? $settings['banner_title'] : '';
        $settings['banner_subtitle'] = ! empty( $settings['banner_subtitle'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['banner_subtitle'] ), ENT_QUOTES, 'UTF-8' ) : '';
        $settings['description']     = ! empty( $settings['banner_content'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['banner_content'] ), ENT_QUOTES, 'UTF-8' ) : '';

        if (!empty($settings['layout'])) {
            $args = $this->renderLayout($settings['layout']);
        } else {
            $args = 'extension/module/pavobuilder/pa_interactive_banner/pa_interactive_banner';
        }

        return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
    }

}