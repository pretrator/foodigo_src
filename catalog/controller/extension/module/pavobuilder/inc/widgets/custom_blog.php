<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/
class PA_Widget_Custom_Blog extends PA_Widgets {

    public function fields() {
        $this->load->language('extension/module/pavolatestblogs');
        return array(
            'mask'		=> array(
                'icon'	=> 'fa fa-pencil-square-o',
                'label'	=> $this->language->get( 'entry_custom_blog' )
            ),
            'tabs'	=> array(
                'general'		=> array(
                    'label'		=> $this->language->get( 'entry_general_text' ),
                    'fields'	=> array(
                        array(
                            'type'  => 'hidden',
                            'name'  => 'uniqid_id',
                            'label' => $this->language->get( 'entry_column_id_text' ),
                            'desc'  => $this->language->get( 'entry_column_desc_text' )
                        ),
                        array(
                            'type'  => 'text',
                            'name'  => 'extra_class',
                            'label' => $this->language->get( 'entry_extra_class_text' ),
                            'desc'  => $this->language->get( 'entry_extra_class_desc_text' ),
                        ),
                        array(
                            'type'  => 'select',
                            'name'  => 'layout',
                            'label' => $this->language->get( 'entry_layout_text' ),
                            'default' => 'false',
                            'options'   => $this->getLayoutsOptions(),
                            'none'  => false
                        ),
                        array(
                            'type'  => 'text',
                            'name'  => 'first_image_size',
                            'label' => $this->language->get( 'entry_image_first_size_text' ),
                            'desc'  => $this->language->get( 'entry_image_size_desc' ),
                            'default'       => 'full',
                            'placeholder'   => '200x400'
                        ),
                        array(
                            'type'	=> 'text',
                            'name'	=> 'image_size',
                            'label'	=> $this->language->get( 'entry_image_size_text' ),
                            'desc'	=> $this->language->get( 'entry_image_size_desc' ),
                            'default'		=> 'full',
                            'placeholder'	=> '200x400'
                        ),
                        array(
                            'type'	=> 'number',
                            'name'	=> 'view',
                            'label'	=> $this->language->get( 'entry_view_text' ),
                            'desc'  => $this->language->get( 'entry_view_desc_text' ),
                            'default' => 4
                        ),
                        array(
                            'type'	=> 'number',
                            'name'	=> 'limit',
                            'label'	=> $this->language->get( 'entry_limit_text' ),
                            'default' => 7
                        ),
                        array(
                            'type'	=> 'number',
                            'name'	=> 'rows',
                            'label'	=> $this->language->get( 'entry_rows_text' ),
                            'default' => 1,
                            'min'   => 1,
                            'max' => 2
                        ),
                        array(
                            'type'	=> 'select',
                            'name'	=> 'loop',
                            'label'	=> $this->language->get( 'entry_loop' ),
                            'desc'	=> $this->language->get( 'entry_loop_desc' ),
                            'default' => 'false',
                            'options'	=> array(
                                array(
                                    'value'	=> 'true',
                                    'label'	=> 'Enabled'
                                ),
                                array(
                                    'value'	=> 'false',
                                    'label'	=> 'Disabled'
                                )
                            )
                        ),
                        array(
                            'type'	=> 'select',
                            'name'	=> 'auto_play',
                            'label'	=> $this->language->get( 'entry_auto_play' ),
                            'default' => 'disabled',
                            'options'	=> array(
                                array(
                                    'value'	=> 'enabled',
                                    'label'	=> 'Enabled'
                                ),
                                array(
                                    'value'	=> 'disabled',
                                    'label'	=> 'Disabled'
                                )
                            )
                        ),
                        array(
                            'type'	=> 'number',
                            'name'	=> 'interval',
                            'label'	=> $this->language->get( 'entry_interval' ),
                            'default' => 8000
                        ),
                        array(
                            'type'      => 'select',
                            'name'      => 'pagination',
                            'label'     => $this->language->get( 'entry_pagination_text' ),
                            'default'   => 'false',
                            'options'   => array(
                                array(
                                    'value' => 'true',
                                    'label' => 'Enabled'
                                ),
                                array(
                                    'value' => 'false',
                                    'label' => 'Disabled'
                                )
                            ),
                            'none' => false
                        ),
                        array(
                            'type'      => 'select',
                            'name'      => 'nav',
                            'label'     => $this->language->get( 'entry_navigation_text' ),
                            'default'   => 'false',
                            'options'   => array(
                                array(
                                    'value' => 'true',
                                    'label' => 'Enabled'
                                ),
                                array(
                                    'value' => 'false',
                                    'label' => 'Disabled'
                                )
                            ),
                            'none' => false
                        ),
                        array(
                            'type'    => 'number',
                            'name'    => 'padding',
                            'label'   => $this->language->get( 'entry_slide_padding' ),
                            'default' => 0
                        ),
                        array(
                            'type'    => 'number',
                            'name'    => 'margin',
                            'label'   => $this->language->get( 'entry_slide_margin' ),
                            'default' => 0
                        )
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
                    )
                )
            )
        );
    }

    public function render( $settings = array(), $content = '' ) {
        $date_format = array(
                $this->config->get( 'pavoblog_date_format' ),
                $this->config->get( 'pavoblog_time_format' ),
            );
        $date_format = implode( ' ', $date_format );

        $this->load->model( 'extension/pavoblog/post' );
        $this->load->model( 'extension/pavoblog/category' );
        $this->load->model( 'extension/pavoblog/comment' );
        $this->load->model( 'catalog/product' );
        $this->load->model( 'tool/image' );
        $this->load->language( 'extension/module/pavolatestblogs' );
        $this->load->language( 'extension/module/pavoblog' );
        $this->load->language( 'extension/module/pavobuilder' );

        $settings['description'] = ! empty( $settings['description'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['description'] ), ENT_QUOTES, 'UTF-8' ) : '';
        $settings['posts'] = array();

        $filter_data = array(
            'date_added'	=> date( 'Y-m-d'),
            'date_modified'	=> date( 'Y-m-d'),
            'start'			=> 0,
            'orderby'       => 'date_added',
            'order'         => 'DESC',
            'limit'         => $settings['limit'] ? $settings['limit'] : 6
        );

        $posts = $this->model_extension_pavoblog_post->getLastestPosts( $filter_data );
        if( defined("IMAGE_URL")){
            $server =  IMAGE_URL;
        } else  {
            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }
        foreach( $posts as $post ) {
            if ( ! empty( $post['image'] ) ) {
                $settings['image_size'] = strtolower( $settings['image_size'] );
                $src = empty( $settings['image_size'] ) || $settings['image_size'] == 'full' ? $server . $post['image'] : false;
                if (strpos( $settings['image_size'], 'x' ) ) {
                    $src = $this->getImageLink($post['image'], $settings['image_size']);
                }

                $post['image'] = $src ? $src : $post['image'];
            }

            if ( ! empty( $post['user_image'] ) ) {

                $settings['first_image_size'] = strtolower( $settings['first_image_size'] );
                $avatar_src = empty( $settings['first_image_size'] ) || $settings['first_image_size'] == 'full' ? $server . $post['user_image'] : false;
                if ( strpos( $settings['first_image_size'], 'x' ) ) {
                    $avatar_src = $this->getImageLink($post['user_image'], $settings['first_image_size']);
                }

                $post['user_image'] = $avatar_src ? $avatar_src : $post['user_image'];
            }

            $settings['posts'][] = array(
                'name'          => html_entity_decode( $post['name'], ENT_QUOTES, 'UTF-8' ),
                'description'   => html_entity_decode( $post['description'], ENT_QUOTES, 'UTF-8' ),
                'content'       => html_entity_decode( $post['content'], ENT_QUOTES, 'UTF-8' ),
                'thumb'         => $post['image'],
                'link'          => $this->url->link( 'extension/pavoblog/single', 'pavo_post_id='.$post['post_id'] ),
                'date_added'    => isset( $post['date_added'] ) ? date( $date_format, strtotime( $post['date_added'] ) ) : '',
                'date_modified' => isset( $post['date_modified'] ) ? $post['date_modified']:'',
                'username'      => isset( $post['username'] ) ? $post['username'] : '',
                'userimage'     => $post['user_image'],
                'comment'       => isset($post['post_id']) ? count($this->model_extension_pavoblog_comment->getComments( $post['post_id'] )) : 0
            );
        }

        if (!empty($posts)) {
            $first_blog = reset($posts);
            if ( ! empty( $first_blog['image'] ) ) {

                $settings['first_image_size'] = strtolower( $settings['first_image_size'] );
                $first_src = empty( $settings['first_image_size'] ) || $settings['first_image_size'] == 'full' ? $server . $first_blog['image'] : false;
                if ( strpos( $settings['first_image_size'], 'x' ) ) {
                    $first_src = $this->getImageLink($first_blog['image'], $settings['first_image_size']);
                }

                $first_blog['image'] = $first_src ? $first_src : $first_blog['image'];
            }

            $settings['first_blog'] = array(
                'name'          => isset ($first_blog['name']) ? html_entity_decode( $first_blog['name'], ENT_QUOTES, 'UTF-8' ) : '',
                'description'   => isset($first_blog['description']) ? html_entity_decode( $first_blog['description'], ENT_QUOTES, 'UTF-8' ) : '',
                'content'       => isset($first_blog['content']) ? html_entity_decode( $first_blog['content'], ENT_QUOTES, 'UTF-8' ) : '',
                'thumb'         => isset ($first_blog['image']) ? $first_blog['image'] : '',
                'link'          => isset ($first_blog['post_id']) ? $this->url->link( 'extension/pavoblog/single', 'pavo_post_id='.$first_blog['post_id'] ) : '',
                'date_added'    => isset( $first_blog['date_added'] ) ? date( $date_format, strtotime( $first_blog['date_added'] ) ) : '',
                'date_modified' => isset( $first_blog['date_modified'] ) ? $first_blog['date_modified']:'',
                'username'      => isset( $first_blog['username'] ) ? $first_blog['username'] : ''
            );
        }

        if (!empty($settings['layout'])) {
            $args = $this->renderLayout($settings['layout']);
        } else {
            $args = 'extension/module/pavobuilder/pa_custom_blog/pa_custom_blog';
        }

        $carouselOptions = array(
            'loop' => isset( $settings['loop'] ) && $settings['loop'] === 'true',
            'responsiveClass' => true,
            'items' => !empty($settings['item']) ? (int)$settings['item'] : 2,
            'rows' => !empty($settings['rows']) ? (int)$settings['rows'] : 1,
            'nav' => !empty($settings['nav']) && $settings['nav'] === 'true',
            'dots' => !empty($settings['pagination']) && $settings['pagination'] === 'true',
            'autoplay' => !empty($settings['auto_play']) && $settings['auto_play'] == 'true',
            'autoplayTimeout' => !empty($settings['interval']) ? $settings['interval'] : 2000,
            'responsive' => array(
                0 => array(
                    'items' => !empty($settings['mobile']) ? $settings['mobile'] : 1,
                    'nav' => true
                ),
                481 => array(
                    'items' => !empty($settings['table']) ? $settings['table'] : 1,
                    'nav' => true
                ),
                769 => array(
                    'items' => !empty($settings['item']) ? $settings['item'] : 1,
                    'nav' => true
                )
            ),
            'margin' => !empty($settings['margin']) ? (int)$settings['margin'] : 0,
            'stagePadding' => !empty($settings['padding']) ? $settings['padding'] : 0
        );
        $settings['carousel'] = $carouselOptions;
        return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
    }

}