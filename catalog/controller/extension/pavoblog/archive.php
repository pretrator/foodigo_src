<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class ControllerExtensionPavoBlogArchive extends Controller {

	public function index() {
		/**
		 * load model - language
		 */
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/category' );
		$this->load->model( 'extension/pavoblog/post' );
		$this->load->model( 'extension/pavoblog/comment' );
		$this->load->model( 'tool/image' );

		$args = $data = array();

		$data['theme'] = $this->config->get( 'config_theme' );
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_blog'),
			'href' => $this->url->link('extension/pavoblog/archive')
		);
		$category_info = $author_info = array();
		$archive_heading = '';
		if ( isset( $this->request->get['pavo_cat_id'] ) ) {
			$category_id = (int)$this->request->get['pavo_cat_id'];

			// Set the last category breadcrumb
			$category_info = $this->model_extension_pavoblog_category->getCategory( $category_id );


            if ( $category_info ) {
				$args['category_id'] = $category_id;
				$url = '';

				if (isset($this->request->get['sort'])) {
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if (isset($this->request->get['order'])) {
					$url .= '&order=' . $this->request->get['order'];
				}

				if (isset($this->request->get['page'])) {
					$url .= '&page=' . $this->request->get['page'];
				}

				if (isset($this->request->get['limit'])) {
					$url .= '&limit=' . $this->request->get['limit'];
				}

				$data['breadcrumbs'][] = array(
					'text' => sprintf( $this->language->get( 'text_post_category' ), $category_info['name'] ),
					'href' => $this->url->link( 'extension/pavoblog/archive', 'pavo_cat_id=' . (int)$category_id . $url )
				);
			}
		} else if ( ! empty( $this->request->get['user_id'] ) ) {
			$url = '';
			$args['user_id'] = (int)$this->request->get['user_id'];
			$author_info = $this->model_extension_pavoblog_post->getAuthorByUserId( $args['user_id'] );
			// breadcrumbs
			$data['breadcrumbs'][] = array(
					'text' => sprintf( $this->language->get( 'text_post_author' ), $author_info['user_nicename'] ),
					'href' => $this->url->link( 'extension/pavoblog/archive/author', 'pavo_username=' . $author_info['username'] . $url )
				);
		} else if ( ! empty( $this->request->get['pavo_username'] ) ) {
			$url = '';
			$args['username'] = $this->request->get['pavo_username'];
			$author_info = $this->model_extension_pavoblog_post->getAuthorByUsername( $args['username'] );
			// breadcrumbs
			$data['breadcrumbs'][] = array(
					'text' => sprintf( $this->language->get( 'text_post_author' ), $author_info['user_nicename'] ),
					'href' => $this->url->link( 'extension/pavoblog/archive/author', 'pavo_username=' . $author_info['username'] . $url )
				);
		} else if ( ! empty( $this->request->get['tag'] ) ) {
			$url = '';
			$args['tag'] = $this->request->get['tag'];
			// breadcrumbs
			$data['breadcrumbs'][] = array(
					'text' => sprintf( $this->language->get( 'text_post_tag' ), $this->request->get['tag'] ),
					'href' => $this->url->link( 'extension/pavoblog/archive', 'tag=' . $this->request->get['tag'] . $url )
				);
		}

		if ( $category_info ) {
			$archive_heading = sprintf( $this->language->get( 'archive_category_heading' ), $category_info['name'] );
		} else if ( $author_info ) {
			$archive_heading = sprintf( $this->language->get( 'archive_author_heading' ), $author_info['user_nicename'] );
		}

        // category info
        $data['category_info'] = $category_info;
        $data['author_info'] = $author_info;
        $data['archive_heading'] = $archive_heading;

		$data['page'] = isset( $this->request->get['page'] ) ? (int)$this->request->get['page'] : 1;
		// posts limit
		$data['limit'] = $args['limit'] = $this->config->get( 'pavoblog_post_limit' ) ? $this->config->get( 'pavoblog_post_limit' ) : 10;
		if ( $data['page'] ) {
			$args['start'] = ( $data['page'] - 1 ) * $args['limit'];
		}

		/**
		 * posts
		 */
		$posts = $this->model_extension_pavoblog_post->getPosts( $args );
		/**
		 * get totals
		 */
		$total = $this->model_extension_pavoblog_post->getTotals();
		// grid columns
		$data['columns'] = $this->config->get( 'pavoblog_grid_columns' ) ? $this->config->get( 'pavoblog_grid_columns' ) : 3;
		$data['layout'] = $layout = $this->config->get( 'pavoblog_default_layout' ) ? $this->config->get( 'pavoblog_default_layout' ) : 'grid';
		$data['date_format'] = $this->config->get( 'pavoblog_date_format' ) ? $this->config->get( 'pavoblog_date_format' ) : 'Y-m-d';
		$data['time_format'] = $this->config->get( 'pavoblog_time_format' ) ? $this->config->get( 'pavoblog_time_format' ) : '';
		$data['posts'] = array();

		if ( $posts ) foreach ( $posts as $post ) {
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

			//get image user
	        if (!empty($post['user_id'])) {
		        $get_user = $this->model_extension_pavoblog_post->getAuthorByUserId($post['user_id']);

		        $avatar_width = $this->config->get( 'pavoblog_avatar_width' ) ? $this->config->get( 'pavoblog_avatar_width' ) : 54;
				$avatar_height = $this->config->get( 'pavoblog_avatar_height' ) ? $this->config->get( 'pavoblog_avatar_height' ) : 54;
		        if ( ! empty( $get_user['image'] ) ) {
					$post['avatar_thumb'] = $this->model_tool_image->resize( $get_user['image'], $avatar_width, $avatar_height );
				} else {
					$post['avatar_thumb'] = $this->model_tool_image->resize( 'placeholder.png', $avatar_width, $avatar_height );
				}
			}

			if (!empty($post['post_id'])) {
				$comments = $this->model_extension_pavoblog_comment->getComments( $post['post_id'] );
				$post['comment_count'] = 0;
				foreach ( $comments as $comment ) {
					if ( $comment['comment_status'] == 1 ){
						$post['comment_count'] = $post['comment_count'] + 1;
					}
				}
			}
			
			$description = trim( strip_tags( html_entity_decode( $description, ENT_QUOTES, 'UTF-8' ) ) );
			$index = @strpos( $description, ' ', (int)$this->config->get( 'pavoblog_post_description_length' ) );
			$subdescription = substr( $description, 0, $index === false ? 0 : $index );
			$post['description'] = $subdescription ? $subdescription : $description;
			$post['href'] = $this->url->link( 'extension/pavoblog/single', 'pavo_post_id=' . $post['post_id'] );
			$post['author_href'] = ! empty( $post['username'] ) ? $this->url->link( 'extension/pavoblog/archive/author', 'pavo_username=' . $post['username'] ) : '';

			$data['posts'][] = $post;
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
        $sub = !empty($this->request->get['pavo_cat_id']) ? '&pavo_cat_id='.($this->request->get['pavo_cat_id']) : '';
        $pagination->url = $this->url->link('extension/pavoblog/archive' . $sub . '&page={page}');

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf(
        	$this->language->get('text_pagination'),
        	($total) ? ( ($data['page'] - 1) * $args['limit'] + 1 ) : 0,
        	( ( ($data['page'] - 1) * $args['limit'] ) > ($total - $args['limit']) ) ? $total : ( ( ($data['page'] - 1) * $args['limit'] ) + $args['limit'] ),
        	$total,
        	ceil( $total / $args['limit'] )
        );
        // end pagination

		/**
		 * set document title
		 */
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

		// loaded default stylesheet
		if ( $this->config->get( 'pavoblog_default_style' ) ){
            $file = DIR_TEMPLATE . 'default/stylesheet/pavoblog.min.css';
            if ( file_exists( $file ) ) {
                $file = str_replace( DIR_APPLICATION, basename( DIR_APPLICATION ) . '/', $file );
                $this->document->addStyle( $file );
            }
        }
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		/**
		 * set layout template
		 */
		$this->response->setOutput( $this->load->view( 'pavoblog/archive', $data ) );
	}

	/**
	 * author
	 */
	public function author() {
		$this->index();
	}

	/**
	 * ajax set display mode
	 */
	public function ajaxSetMode() {

	}

}