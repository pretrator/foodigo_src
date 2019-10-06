<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/
class ControllerExtensionPavoBlogCategory extends Controller
{
    public function index()
    {
        $this->load->language( 'extension/module/pavoblog' );
        $this->load->model( 'extension/pavoblog/category' );
        $this->load->model( 'extension/pavoblog/post' );
        $this->load->model( 'tool/image' );

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        if (isset($this->request->get['blog_cat_id'])) {

            $path = '';

            $parts = explode('_', (string)$this->request->get['blog_cat_id']);

            $category_id = (int)array_pop($parts);

            foreach ($parts as $path_id) {
                if (!$path) {
                    $path = (int)$path_id;
                } else {
                    $path .= '_' . (int)$path_id;
                }

                $category_info = $this->model_extension_pavoblog_category->getCategory($path_id);

                if ($category_info) {
                    $data['breadcrumbs'][] = array(
                        'text' => $category_info['name'],
                        'href' => $this->url->link('extension/pavoblog/category', 'blog_cat_id=' . $path)
                    );
                }
            }
        } else {
            $category_id = 0;
        }

        $category_info = $this->model_extension_pavoblog_category->getCategory( $category_id );

        if ( $category_info ) {
            $this->document->setTitle($category_info['meta_title']);
            $this->document->setDescription($category_info['meta_description']);
            $this->document->setKeywords($category_info['meta_keyword']);

            $data['heading_title'] = $category_info['name'];

            $data['breadcrumbs'][] = array(
                'text' => $category_info['name'],
                'href' => $this->url->link('extension/pavoblog/category', 'blog_cat_id=' . $this->request->get['blog_cat_id'])
            );

            $data['posts'] = array();

            $filter_data = array(
                'category_id' => $category_info['category_id']
            );

            $data['page'] = isset( $this->request->get['page'] ) ? (int)$this->request->get['page'] : 1;
            // posts limit
            $data['limit'] = $args['limit'] = $this->config->get( 'pavoblog_post_limit' ) ? $this->config->get( 'pavoblog_post_limit' ) : 10;
            if ( $data['page'] ) {
                $args['start'] = ( $data['page'] - 1 ) * $args['limit'];
            }

            $filter_data = array_merge($filter_data, $args);

            $results = $this->model_extension_pavoblog_post->getPosts($filter_data);

            $total = count($results);

            $blog_image_width  = !empty( $this->config->get('pavoblog_image_thumb_width') ) ? $this->config->get('pavoblog_image_thumb_width') : 200;
            $blog_image_height = !empty( $this->config->get('pavoblog_image_thumb_height') ) ? $this->config->get('pavoblog_image_thumb_height') : 200;

            // grid columns
            $data['columns'] = $this->config->get( 'pavoblog_grid_columns' ) ? $this->config->get( 'pavoblog_grid_columns' ) : 3;
            $data['layout'] = $layout = $this->config->get( 'pavoblog_default_layout' ) ? $this->config->get( 'pavoblog_default_layout' ) : 'grid';
            $data['date_format'] = $this->config->get( 'pavoblog_date_format' ) ? $this->config->get( 'pavoblog_date_format' ) : 'Y-m-d';
            $data['time_format'] = $this->config->get( 'pavoblog_time_format' ) ? $this->config->get( 'pavoblog_time_format' ) : '';
            $data['posts'] = array();

            if ( $results ) {
                foreach ( $results as $post ) {
                    if ( $post['image'] ) {
                        $post['thumb'] = $this->model_tool_image->resize( $post['image'], $this->config->get('pavoblog_image_thumb_width'), $this->config->get('pavoblog_image_thumb_height' ) );
                    } else {
                        $post['thumb'] = $this->model_tool_image->resize( 'placeholder.png', $this->config->get('pavoblog_image_thumb_width'), $this->config->get('pavoblog_image_thumb_height' ) );
                    }

                    $description = '';
                    if ( ! empty( $post['description'] ) ) {
                        $description = $post['description'];
                    } else if ( ! empty( $post['content'] ) ) {
                        $description = $post['content'];
                    }
                    $description = trim( strip_tags( html_entity_decode( $description, ENT_QUOTES, 'UTF-8' ) ) );
                    $index = @strpos( $description, ' ', (int)$this->config->get( 'pavoblog_post_description_length' ) );
                    $subdescription = substr( $description, 0, $index === false ? 0 : $index );
                    $post['description'] = $subdescription ? $subdescription : $description;
                    $post['href'] = $this->url->link( 'extension/pavoblog/single', 'pavo_post_id=' . $post['post_id'] );
                    $post['author_href'] = ! empty( $post['username'] ) ? $this->url->link( 'extension/pavoblog/archive/author', 'pavo_username=' . $post['username'] ) : '';

                    $data['posts'][] = $post;
                }
            }

            // pagination
            $pavo_pagination = $this->config->get( 'pavoblog_pagination' ) && class_exists( 'Pavo_Pagination' );
            $pagination = $pavo_pagination ? new Pavo_Pagination() : new Pagination();
            $pagination->uniqid = 'pavoblog-pagination';
            $pagination->total = $total;
            $pagination->page = $data['page'];
            $pagination->limit = $args['limit'];
            $pagination->text_next = $this->language->get( 'text_next' );
            $pagination->text_prev = $this->language->get( 'text_prev' );
            $pagination->url = $this->url->link('extension/pavoblog/archive' . '&page={page}', true);

            $data['pagination'] = $pagination->render();
            $data['results'] = sprintf(
                $this->language->get('text_pagination'),
                ($total) ? ( ($data['page'] - 1) * $args['limit'] + 1 ) : 0,
                ( ( ($data['page'] - 1) * $args['limit'] ) > ($total - $args['limit']) ) ? $total : ( ( ($data['page'] - 1) * $args['limit'] ) + $args['limit'] ),
                $total,
                ceil( $total / $args['limit'] )
            );
            // end pagination

            $title = $this->language->get( 'heading_title' );
            if ( ! empty( $category_info['meta_title'] ) ) {
                $title = html_entity_decode( $category_info['meta_title'], ENT_QUOTES, 'UTF-8' );
            }
            $this->document->setTitle( $title );

            // set meta description
            if ( ! empty( $category_info['meta_description'] ) ) {
                $this->document->setDescription( html_entity_decode( $category_info['meta_description'], ENT_QUOTES, 'UTF-8' ) );
            }

            // set meta keyword
            if ( ! empty( $category_info['meta_keyword'] ) ) {
                $this->document->setKeywords( html_entity_decode( $category_info['meta_keyword'], ENT_QUOTES, 'UTF-8' ) );
            }
        }

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('pavoblog/category', $data));
    }
}