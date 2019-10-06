<?php
/******************************************************
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/
class PA_Widget_Featured_Categories extends PA_Widgets {

    public function fields() {
        $this->load->model('catalog/category');
        $get_category = $this->model_catalog_category->getCategories();
        $categories = array();
        foreach ($get_category as $cat_id) {
            $categories[] = array(
                'value' => $cat_id['category_id'],
                'label' => $cat_id['name']
            );
        }
        return array(
            'mask'		=> array(
                'icon'	=> 'fa fa-copyright',
                'label'	=> $this->language->get( 'entry_featured_categories' )
            ),
            'tabs'	=> array(
                'general'		=> array(
                    'label'		=> $this->language->get( 'entry_general_text' ),
                    'fields'	=> array(
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
                            'default' => 'pa_featured_categories',
                            'options'   => $this->getLayoutsOptions(),
                            'none'  => false
                        ),
                        array(
                            'type'  => 'text',
                            'name'  => 'extra_class',
                            'label' => $this->language->get( 'entry_extra_class_text' ),
                            'default' => '',
                            'desc'  => $this->language->get( 'entry_extra_class_desc_text' )
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
                            'type'      => 'select',
                            'name'      => 'banner_type',
                            'label'     => $this->language->get( 'entry_banner_type' ),
                            'default'   => 'left',
                            'options'   => array(
                                array(
                                    'value' => 'left',
                                    'label' => 'Left'
                                ),
                                array(
                                    'value' => 'right',
                                    'label' => 'Right'
                                ),
                                array(
                                    'value' => 'top',
                                    'label' => 'Top'
                                ),
                                array(
                                    'value' => 'bottom',
                                    'label' => 'Bottom'
                                ),
                            )
                        ),
                        array(
                            'type'    => 'text',
                            'name'    => 'name',
                            'label'   => $this->language->get( 'entry_name_text' ),
                            'default' => '',
                            'language'=> true
                        ),
                        array(
                            'type'    => 'text',
                            'name'    => 'button_text',
                            'label'   => $this->language->get( 'entry_banner_button' ),
                            'default' => '',
                            'language'=> true
                        ),
                        
                        array(
                            'type'      => 'text',
                            'name'      => 'url_link',
                            'label'     => $this->language->get( 'entry_url_link' ),
                            'desc'      => $this->language->get( 'entry_link_desc_text' ),
                            'default'   => '',
                        ),
                        array(
                            'type'      => 'select',
                            'name'      => 'category',
                            'label'     => $this->language->get( 'entry_list_category' ),
                            'default'   => '',
                            'options'   => $categories,
                            'multiple'  => true
                        ),
                    )
                ),
                'style'				=> array(
                    'label'			=> $this->language->get( 'entry_styles_text' ),
                    'fields'		=> array(
                        array(
                            'type'	=> 'layout-onion',
                            'name'	=> 'layout_onion',
                            'label'	=> 'entry_box_text'
                        ),
                        array(
                            'type'	=> 'colorpicker',
                            'name'	=> 'color',
                            'label'	=> 'entry_color_text'
                        )
                    )
                )
            )
        );
    }

    public function render( $settings = array(), $content = '' ) {
        $this->load->model('catalog/category');
        $this->load->model('tool/image');
        
        $server = $this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER;
        $settings['server'] = $this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER;
        if ( ! empty( $settings['banner_image'] ) ) {
            $settings['banner_size'] = strtolower( $settings['banner_size'] );
                $srcs = empty( $settings['banner_size'] ) || $settings['banner_size'] == 'full' ? $server . 'image/' . $settings['banner_image'] : false;
                if (strpos( $settings['banner_size'], 'x' ) ) {
                    $srcs = $this->getImageLink($settings['banner_image'], $settings['banner_size']);
            }
                $settings['banner_image'] = $srcs ? $srcs : $settings['banner_image'];
        }

        $settings['categories'] = array ();
        if (!empty ($settings['category'])) {
            foreach ($settings['category'] as $cat_id) {
                $get_catid = $this->model_catalog_category->getCategory($cat_id);
                $settings['categories'][] = array (
                    'category_id'   => isset($get_catid['category_id']) ? $get_catid['category_id'] : '',
                    'category_name' => isset($get_catid['name']) ? $get_catid['name'] : '',
                    'category_link' => $this->url->link('product/category','path=' .(isset($get_catid['category_id']) ? $get_catid['category_id'] : '')),
                );
            }
        }

        if (!empty($settings['layout'])) {
            $args = $this->renderLayout($settings['layout']);
        } else {
            $args = 'extension/module/pavobuilder/pa_featured_categories/pa_featured_categories';
        }
        
        return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
    }

}