<?php
/**
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class ControllerExtensionModulePavoBlog extends Controller {

	/**
	 * data
	 */
	private $data = array();

	// errors storge
	private $errors = array();

	/**
	 * posts list
	 */
	public function index() {
		$this->posts();
	}

	public function posts() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/post' );

		if ( $this->request->server['REQUEST_METHOD'] === 'POST' ) {
			if ( $this->user->hasPermission( 'modify', 'extension/module/pavoblog/post' ) ) {
				$selected = ! empty( $this->request->post['selected'] ) ? $this->request->post['selected'] : array();
				if ( $selected ) {
					foreach ( $selected as $post_id ) {
						$this->model_extension_pavoblog_post->deletePost( $post_id );
					}
					$this->session->data['post_success'] = $this->language->get( 'text_posts_deleted' );
				} else {
					$this->session->data['post_error'] = $this->language->get( 'error_no_select_post' );
				}
			} else {
				$this->session->data['post_error'] = $this->language->get( 'error_permission' );
			}

			$this->response->redirect( $this->url->link( 'extension/module/pavoblog/posts', 'user_token=' . $this->session->data['user_token'], true ) ); exit();
		}

		// has error message
		if ( ! empty( $this->session->data['post_error'] ) ) {
			$this->errors['error_warning'] = $this->session->data['post_error'];
			unset( $this->session->data['post_error'] );
		}

		// has success message
		if ( ! empty( $this->session->data['post_success'] ) ) {
			$this->data['success'] = $this->session->data['post_success'];
			unset( $this->session->data['post_success'] );
		}

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_posts_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/posts', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

		$this->data['add_new_url'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/post', 'user_token=' . $this->session->data['user_token'], true ) );

		// posts
		$paged = ! empty( $this->request->get['page'] ) ? (int)$this->request->get['page'] : 1;
		$limited = $this->config->get('pavoblog_post_limit') ? $this->config->get('pavoblog_post_limit') : 10;

        $this->data['sort'] = $order = isset( $this->request->get['sort'] ) ? $this->request->get['sort'] : '';
        $this->data['order'] = $sort = isset( $this->request->get['order'] ) ? $this->request->get['order'] : '';
        $args = array(
        	'start'	=> $paged ? ( $paged - 1 ) * $limited : 0,
			'limit'	=> $limited
        );
        if ( $order && $sort ) {
        	$args['orderby'] = $order;
        	$args['order'] = $sort;
        }

		$rsort = $sort == 'desc' ? 'asc' : 'desc';
        $this->data['sort_title'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/posts', 'sort=title&order='.$rsort.'&user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ) );
        $this->data['sort_author'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/posts', 'sort=author&order='.$rsort.'&user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ) );
        $this->data['sort_date_added'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/posts', 'sort=date&order='.$rsort.'&user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ) );

		$this->data['posts'] = $this->model_extension_pavoblog_post->getPosts( $args );
		$this->data['date_format'] = $this->config->get( 'pavoblog_date_format' );
		$this->data['time_format'] = $this->config->get( 'pavoblog_time_format' );

		$total = $this->model_extension_pavoblog_post->getTotals();
		$pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $paged;
        $pagination->limit = $limited;
        $pagination->url = $this->url->link('extension/module/pavoblog/posts', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);

        $this->data['pagination'] = $pagination->render();
        $this->data['results'] = sprintf(
        	$this->language->get('text_pagination'),
        	($total) ? ( ($paged - 1) * $limited + 1 ) : 0,
        	( (($paged - 1) * $limited) > ($total - $limited) ) ? $total : ( ( ($paged - 1) * $limited ) + $limited ),
        	$total,
        	ceil( $total / $limited )
        );
		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'posts_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/posts', $this->data ) );
	}

	/**
	 * post view - edit
	 */
	public function post() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/post' );
		$this->load->model( 'extension/pavoblog/category' );
		$this->load->model( 'localisation/language' );
		$this->load->model( 'setting/store' );
		$this->load->model( 'user/user' );

		$post_id = isset( $this->request->get['post_id'] ) ? $this->request->get['post_id'] : 0;
		if ( $this->request->server['REQUEST_METHOD'] === 'POST' && $this->validatePostForm() ) {
			if ( $post_id ) {
				$this->model_extension_pavoblog_post->editPost( $post_id, $this->request->post );
			} else {
				$post_id = $this->model_extension_pavoblog_post->addPost( $this->request->post );
			}

			$this->session->data['success'] = $this->language->get( 'text_success' );
			if ( $post_id ) {
				$this->response->redirect( $this->url->link( 'extension/module/pavoblog/post', 'post_id=' . $post_id . '&user_token=' . $this->session->data['user_token'], true ) ); exit();
			} else {
				$this->response->redirect( $this->url->link( 'extension/module/pavoblog/post', 'user_token=' . $this->session->data['user_token'], true ) ); exit();
			}
		}

		if ( ! empty( $this->session->data['success'] ) ) {
			$this->data['success'] = $this->session->data['success'];
			unset( $this->session->data['success'] );
		}
		// languages
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		$this->data['users'] = array();
		$this->data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get( 'text_default' )
		);

		$stores = $this->model_setting_store->getStores();
		foreach ($stores as $store) {
			$this->data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		$this->load->model( 'tool/image' );
		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' 		=> $this->language->get( 'text_home' ),
			'href' 		=> $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_posts_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/index', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

		// posts
		$this->data['post'] = $post_id ? $this->model_extension_pavoblog_post->getPost( $post_id ) : array();
		$this->data['post']['date_added'] = isset( $this->data['post']['date_added'] ) ? date( 'Y-m-d', strtotime( $this->data['post']['date_added'] ) ) : date( 'Y-m-d' );
		$this->data['post']['thumb'] = ! empty( $this->data['post']['image'] ) ? $this->model_tool_image->resize( $this->data['post']['image'], 100, 100 ) : $this->model_tool_image->resize( 'no_image.png', 100, 100);
		if ( ! isset( $this->data['post']['user_id'] ) ) {
			$this->data['post']['user_id'] = $this->session->data['user_id'];
		}

		// status
		if ( ! isset( $this->data['post']['status'] ) ) {
			$this->data['post']['status'] = 1;
		}
		// featured
		if ( ! isset( $this->data['post']['featured'] ) ) {
			$this->data['post']['featured'] = 1;
		}

		$this->data['post']['categories'] = $this->model_extension_pavoblog_post->getPostCategories( $post_id );

		$this->data['categories'] = $this->model_extension_pavoblog_category->getCategories();
		$this->data['post_data'] = array();
		if ( ! empty( $this->request->post['post_data'] ) ) {
			$this->data['post_data'] = $this->request->post['post_data'];
		} else if ( $post_id ) {
			$this->data['post_data'] = $this->model_extension_pavoblog_post->getPostDescription( $post_id );
		}

		$this->data['post_store'] = array();
		if ( ! empty( $this->request->post['post_store'] ) ) {
			$this->data['post_store'] = $this->request->post['post_store'];
		} else if ( $post_id ) {
			$this->data['post_store'] = $post_id ? $this->model_extension_pavoblog_post->getPostStore( $post_id ) : array();
		}

		$this->data['post_seo_url'] = array();
		if ( ! empty( $this->request->post['post_seo_url'] ) ) {
			$this->data['post_seo_url'] = $this->request->post['post_seo_url'];
		} else if ( $post_id ) {
			$this->data['post_seo_url'] = $this->model_extension_pavoblog_post->getSeoUrlData( $post_id );
		}

		$this->data['thumb'] = isset( $this->data['post']['image'] ) ? $this->data['post']['image'] : $this->model_tool_image->resize('no_image.png', 100, 100);
		$this->data['no_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$this->data['action'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/post', 'user_token=' . $this->session->data['user_token'], true ) );

		$action_url = $this->url->link( 'extension/module/pavoblog/post', 'user_token=' . $this->session->data['user_token'], true );
   		if ( $post_id ) {
   			$action_url = $this->url->link( 'extension/module/pavoblog/post', 'post_id='.$post_id.'&user_token=' . $this->session->data['user_token'], true );
   		}
   		$this->data['action'] = str_replace( '&amp;', '&', $action_url );

		// users
		$users = $this->model_user_user->getUsers();
		foreach ( $users as $user ) {
			$this->data['users'][] = array(
					'user_id'    => $user['user_id'],
					'username'   => $user['username']
				);
		}

		$this->data['video_ajax_url'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/loadVideoPreview', 'user_token=' . $this->session->data['user_token'], true ) );
		$this->data['add_new_url'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/post', 'user_token=' . $this->session->data['user_token'], true ) );
		// enqueue scripts, stylesheet needed to display editor
		$this->document->addScript( 'view/javascript/summernote/summernote.js' );
		$this->document->addScript( 'view/javascript/summernote/opencart.js' );
		$this->document->addStyle( 'view/javascript/summernote/summernote.css' );
		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'post_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/post', $this->data ) );
	}

	/**
	 * load youtube video iframe by url
	 */
	public function loadVideoPreview() {
		$url = ! empty( $this->request->post['url'] ) ? $this->request->post['url'] : false;
		if ( ! $url ) return;
		$this->load->model( 'extension/pavoblog/post' );

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput( json_encode( array(
				'status'	=> true,
				'url'		=> $this->model_extension_pavoblog_post->getYoutubeIframeUrl( $url )
			) ) );
	}

	/**
	 * categories
	 */
	public function categories() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/category' );

		if ( $this->request->server['REQUEST_METHOD'] === 'POST' ) {
			$selected = ! empty( $this->request->post['selected'] ) ? $this->request->post['selected'] : array();
			if ( $selected ) {
				foreach( $selected as $category_id ) {
					$this->model_extension_pavoblog_category->deleteCategory( $category_id );
				}
				$this->session->data['category_success'] = $this->language->get( 'text_categories_deleted' );
			} else {
				$this->session->data['category_error'] = $this->language->get( 'error_no_select_category' );
			}

			$this->response->redirect( str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'], true ) ) );
		}

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_categories_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

   		if ( $this->erros ) {
   			$this->data['errors'] = $this->errors;
   		}

   		if ( ! empty( $this->session->data['success'] ) ) {
   			$this->data['success'] = $this->session->data['success'];
   			unset( $this->session->data['success'] );
   		}
   		$this->data['action']	= str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'], true ) );
   		$this->data['add_new_url']	= str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/category', 'user_token=' . $this->session->data['user_token'], true ) );

   		$this->data['sort'] = isset( $this->request->get['sort'] ) ? $this->request->get['sort'] : '';
   		$this->data['order'] = isset( $this->request->get['order'] ) ? $this->request->get['order'] : '';

		// categories
		$paged = isset( $this->request->get['page'] ) && is_int( $this->request->get['page'] ) ? (int)$this->request->get['page'] : 1;
		$limited = $this->config->get('pavoblog_post_limit') ? $this->config->get('pavoblog_post_limit') : 10;
		$args = array(
   			'start'		=> $paged ? ( $paged - 1 ) * $limited : 0,
   			'limit'		=> $limited
   		);

   		if ( $this->data['sort'] && $this->data['order'] ) {
   			$args['orderby'] = $this->data['sort'];
   			$args['order'] = $this->data['order'];
   		}
   		$this->data['categories'] = $this->model_extension_pavoblog_category->getCategories( $args );

   		$total = $this->model_extension_pavoblog_category->getTotals();
		$pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $paged;
        $pagination->limit = $limited;
        $pagination->url = $this->url->link('extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);

        $this->data['pagination'] = $pagination->render();
        $this->data['results'] = sprintf(
        	$this->language->get('text_pagination'),
        	($total) ? ( ($paged - 1) * $limited + 1 ) : 0,
        	( (($paged - 1) * $limited) > ($total - $limited) ) ? $total : ( ( ($paged - 1) * $limited ) + $limited ),
        	$total,
        	ceil( $total / $limited )
        );

   		if ( $this->data['categories'] ) {
   			foreach ( $this->data['categories'] as $key => $category ) {
   				$category['edit'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/category', 'category_id='.$category['category_id'].'&user_token=' . $this->session->data['user_token'], true ) );
   				$this->data['categories'][$key] = $category;
   			}
   		}

   		$this->data['sort_link'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/categories', 'sort=name&order='.( $this->data['order'] == 'desc' ? 'asc' : 'desc' ).'user_token=' . $this->session->data['user_token'], true ) );
   		$this->data['delete'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'], true ) );
		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'categories_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/categories', $this->data ) );
	}

	/**
	 * add - edit category
	 */
	public function category() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/category' );
		$this->load->model( 'localisation/language' );
		$this->load->model( 'setting/store' );
		$this->load->model( 'tool/image' );

		$category_id = isset( $this->request->get['category_id'] ) ? $this->request->get['category_id'] : 0;
		if ( $this->request->server['REQUEST_METHOD'] === 'POST' && $this->validateCategoryForm() ) {
			if ( $category_id ) {
				$this->model_extension_pavoblog_category->editCategory( $category_id, $this->request->post );
			} else {
				$category_id = $this->model_extension_pavoblog_category->addCategory( $this->request->post );
			}
			$this->session->data['success'] = $this->language->get( 'text_success' );

			if ( $category_id ) {
				$this->response->redirect( $this->url->link( 'extension/module/pavoblog/category', 'category_id=' . $category_id . '&user_token=' . $this->session->data['user_token'], true ) ); exit();
			} else {
				$this->response->redirect( $this->url->link( 'extension/module/pavoblog/category', 'user_token=' . $this->session->data['user_token'], true ) ); exit();
			}
		}

		if ( ! empty( $this->session->data['success'] ) ) {
			$this->data['success'] = $this->session->data['success'];
			unset( $this->session->data['success'] );
		}

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_categories_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

		$this->data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get( 'text_default' )
		);

		$stores = $this->model_setting_store->getStores();
		foreach ($stores as $store) {
			$this->data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

   		// languages
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		$this->data['category'] = $this->model_extension_pavoblog_category->getCategory( $category_id );

		$this->data['category_data'] = array();
		if ( ! empty( $this->request->post['category_data'] ) ) {
			$this->data['category_data'] = $this->request->post['category_data'];
		} else if ( $category_id ) {
			$this->data['category_data'] = $this->model_extension_pavoblog_category->getCategoryDescription( $category_id );
		}

		$this->data['category_seo_url'] = array();
		if ( ! empty( $this->request->post['category_seo_url'] ) ) {
			$this->data['category_seo_url'] = $this->request->post['category_seo_url'];
		} else if ( $category_id ) {
			$this->data['category_seo_url'] = $this->model_extension_pavoblog_category->getSeoUrlData( $category_id );
		}

		$this->data['category']['thumb'] 	= ! empty( $this->data['category']['image'] ) ? $this->model_tool_image->resize( $this->data['category']['image'], 100, 100 ) : $this->model_tool_image->resize( 'no_image.png', 100, 100);
		$this->data['category_store'] 		= $category_id ? $this->model_extension_pavoblog_category->getCategoryStore( $category_id ) : array();

		// categories
   		$this->data['categories'] = $this->model_extension_pavoblog_category->getCategories( array(
   			'not_in'	=> array( $category_id )
   		) );

   		$action_url = $this->url->link( 'extension/module/pavoblog/category', 'user_token=' . $this->session->data['user_token'], true );
   		if ( $category_id ) {
   			$action_url = $this->url->link( 'extension/module/pavoblog/category', 'category_id='.$category_id.'&user_token=' . $this->session->data['user_token'], true );
   		}
   		$this->data['action']	= str_replace( '&amp;', '&', $action_url );
		$this->data['back_url']	= str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'], true ) );

		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'category_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/category', $this->data ) );
	}

	/**
	 * comments list
	 */
	public function comments() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/comment' );

		// delete comments action
		if ( $this->request->server['REQUEST_METHOD'] === 'POST' ) {
			if ( ! $this->user->hasPermission( 'modify', 'extension/module/pavoblog/comment' ) ) {
				$this->session->data['comment_error'] = $this->language->get( 'error_permission' );
			} else {
				$comment_ids = isset( $this->request->post['selected'] ) ? $this->request->post['selected'] : array();
				if ( $comment_ids ) {
					foreach ( $comment_ids as $comment_id ) {
						$this->model_extension_pavoblog_comment->deleteComment( $comment_id );
					}
					$this->session->data['comment_success'] = $this->language->get( 'text_comments_deleted' );
				} else {
					$this->session->data['comment_error'] = $this->language->get( 'error_no_select_comment' );
				}
			}

			$this->response->redirect( str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/comments', 'user_token=' . $this->session->data['user_token'], true ) ) );
		}

		if ( ! empty( $this->session->data['comment_error'] ) ) {
			$this->errors['error_warning'] = $this->session->data['comment_error'];
			unset( $this->session->data['comment_error'] );
		}

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'comments_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/comments', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

   		if ( ! empty( $this->session->data['comment_success'] ) ) {
   			//text_comment_updated
   			$this->data['success'] = $this->session->data['comment_success'];
   			unset( $this->session->data['comment_success'] );
   		}

   		$orderby = isset( $this->request->get['sort'] ) ? $this->request->get['sort'] : '';
   		$order = isset( $this->request->get['order'] ) ? $this->request->get['order'] : '';

   		$paged = isset( $this->request->get['page'] ) ? (int)$this->request->get['page'] : 1;
   		$limited = $this->config->get('pavoblog_post_limit') ? $this->config->get('pavoblog_post_limit') : 10;
   		$this->data['sort'] = $orderby;
   		$this->data['order'] = $order;

		// comments
		$args = array(
   			'start'	=> ( $paged - 1 ) * $limited,
   			'limit'	=> $limited
   		);

		if ( $order && $orderby ) {
			$args = array_merge( array(
				'orderby'		=> $orderby,
				'order'			=> $order
			), $args );
		}

		$order = strtolower( $this->data['order'] ) == 'asc' ? 'desc' : 'asc';
		$this->data['sort_email'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/comments', 'sort=email&order='.$order.'&user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ) );
		$this->data['sort_author'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/comments', 'sort=name&order='.$order.'&user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ) );
		$this->data['sort_date_added'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/comments', 'sort=date&order='.$order.'&user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ) );
   		$this->data['comments'] = $this->model_extension_pavoblog_comment->getComments( $args );

   		$total = $this->model_extension_pavoblog_comment->getTotals();
		$pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $paged;
        $pagination->limit = $limited;
        $pagination->url = $this->url->link('extension/module/pavoblog/comments', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);

        $this->data['pagination'] = $pagination->render();
        $this->data['results'] = sprintf(
        	$this->language->get('text_pagination'),
        	($total) ? ( ($paged - 1) * $limited + 1 ) : 0,
        	( (($paged - 1) * $limited) > ($total - $limited) ) ? $total : ( ( ($paged - 1) * $limited ) + $limited ),
        	$total,
        	ceil( $total / $limited )
        );

   		// delete comments
   		$this->data['delete'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/comments', 'user_token=' . $this->session->data['user_token'], true ) );
		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'comments_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/comments', $this->data ) );
	}

	/**
	 * toggle comment status
	 */
	public function toggleCommentStatus() {
		$comment_id = isset( $this->request->get['comment_id'] ) ? (int)$this->request->get['comment_id'] : false;
		if ( $comment_id ) {
			$this->load->model( 'extension/pavoblog/comment' );
			$this->load->language( 'extension/module/pavoblog' );

			$comment = $this->model_extension_pavoblog_comment->getComment( $comment_id );
			$status = isset( $comment['comment_status'] ) ? $comment['comment_status'] : 0;

			$status = $status ? 0 : 1;

			$this->model_extension_pavoblog_comment->updateStatus( $comment_id, $status );
			$this->session->data['comment_success'] = $this->language->get( 'text_comment_updated' );
		}
		$this->response->redirect( str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/comments', 'user_token=' . $this->session->data['user_token'], true ) ) ); exit();
	}

	/**
	 * view - edit comment
	 */
	public function comment() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/comment' );

		if ( $this->request->server['REQUEST_METHOD'] === 'POST' && $this->validateCommentForm() ) {
			$comment_id = ! empty( $this->request->get['comment_id'] ) ? (int)$this->request->get['comment_id'] : 0;
			$status = $this->model_extension_pavoblog_comment->updateComment( $this->request->post );
			if ( $status ) {
				$this->session->data['comment_success'] = $this->language->get( 'text_comment_updated' );
			}

			$this->response->redirect( str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/comment', 'comment_id='.$comment_id.'&user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ) ) );
		}

		if ( ! empty( $this->session->data['comment_success'] ) ) {
			$this->data['success'] = $this->session->data['comment_success'];
			unset( $this->session->data['comment_success'] );
		}

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'comments_text' ),
			'href'      => $this->url->link( 'extension/module/pavoblog/comments', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);

		$this->data['comment_id'] = ! empty( $_REQUEST['comment_id'] ) ? (int) $_REQUEST['comment_id'] : 0;
		// comments
   		$this->data['comment'] = $this->data['comment_id'] ? $this->model_extension_pavoblog_comment->getComment( $this->data['comment_id'] ) : array();
   		$this->data['delete_link'] = $this->url->link( 'extension/module/pavoblog/deleteComment', 'comment_id='.(int)$this->data['comment_id'].'&user_token=' . $this->session->data['user_token'], true );
   		$this->data['action'] = $this->url->link( 'extension/module/pavoblog/comment', 'comment_id='.(int)$this->data['comment_id'].'&user_token=' . $this->session->data['user_token'], true );
		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'comment_heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );

		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/comment', $this->data ) );
	}

	/**
	 * delete comment action
	 */
	public function deleteComment() {
		$comment_id = isset( $this->request->get['comment_id'] ) ? (int)$this->request->get['comment_id'] : 0;
		$this->load->model( 'extension/pavoblog/comment' );
		$this->load->language( 'extension/module/pavoblog' );
		$status = $this->model_extension_pavoblog_comment->deleteComment( $comment_id );
		if ( $status ) {
			$this->session->data['comment_success'] = $this->language->get( 'text_comment_deleted' );
		}

		// redirect to comments list page
		$this->response->redirect( str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/comments', 'user_token=' . $this->session->data['user_token'], true ) ) ); exit();
	}

	/**
	 * pavo blog settings
	 */
	public function settings() {
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'localisation/language' );
		$this->load->model( 'setting/store' );
		$this->load->model( 'setting/setting' );
		$this->load->model( 'extension/pavoblog/setting' );

		if ( $this->request->server['REQUEST_METHOD'] === 'POST' && $this->validateSettingForm() ) {
			$this->model_setting_setting->editSetting( 'pavoblog', $this->request->post, $this->config->get( 'config_store_id' ) );
			if ( ! empty( $this->request->post['blog_url'] ) ) {
				$this->model_extension_pavoblog_setting->editSeoData( $this->request->post['blog_url'], 'extension/pavoblog/archive' );
			}

			if ( ! empty( $this->request->post['author_url'] ) ) {
				$this->model_extension_pavoblog_setting->editSeoData( $this->request->post['author_url'], 'extension/pavoblog/archive/author' );
			}

			// success message
			$this->session->data['success'] = $this->language->get( 'text_success' );
			$this->response->redirect( str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/settings', 'user_token=' . $this->session->data['user_token'], true ) ) ); exit();
		}

		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'menu_settings_text' ),
			'href' => $this->url->link( 'extension/module/pavoblog/settings', '&user_token=' . $this->session->data['user_token'], true )
		);

		if ( ! empty( $this->session->data['success'] ) ) {
			$this->data['success'] = $this->session->data['success'];
			unset( $this->session->data['success'] );
		}

		$this->data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get( 'text_default' )
		);
		$this->data['blog_url'] = $this->errors ? $this->request->post['blog_url'] : $this->model_extension_pavoblog_setting->getSeoData( 'extension/pavoblog/archive' );

		$this->data['author_url'] = $this->errors ? $this->request->post['author_url'] : $this->model_extension_pavoblog_setting->getSeoData( 'extension/pavoblog/archive/author' );
		$stores = $this->model_setting_store->getStores();
		foreach ($stores as $store) {
			$this->data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		$this->data['settings'] = $this->errors ? $this->request->post : $this->model_setting_setting->getSetting( 'pavoblog' );

		$this->data['pavo_pagination'] = class_exists( 'Pavo_Pagination' );
		$this->data['save_action']	= str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/settings', 'user_token=' . $this->session->data['user_token'], true ) );
		$this->data['errors'] = $this->errors ? $this->errors : array();

		$this->document->setTitle( $this->language->get( 'setting_title' ) );
		$this->data['heading_title'] = $this->language->get( 'setting_title' );

		$this->data = array_merge( array(
			'header'			=> $this->load->controller( 'common/header' ),
			'column_left'		=> $this->load->controller( 'common/column_left' ),
			'footer'			=> $this->load->controller( 'common/footer' ),
		), $this->data );
		$this->response->setOutput( $this->load->view( 'extension/module/pavoblog/settings', $this->data ) );
	}

	/**
	 * validate category form
	 */
	protected function validateCategoryForm() {
		if ( ! $this->user->hasPermission( 'modify', 'extension/module/pavoblog/category' ) ) {
			$this->errors['warning'] = $this->language->get( 'error_permission' );
		}

		foreach ($this->request->post['category_data'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->errors['name'][$language_id] = $this->language->get( 'error_name' );
			}

			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->errors['meta_title'][$language_id] = $this->language->get( 'error_meta_title' );
			}
		}

		if ($this->request->post['category_seo_url']) {
			$this->load->model( 'design/seo_url' );

			foreach ($this->request->post['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->errors['keyword'][$store_id][$language_id] = $this->language->get( 'error_unique' );
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['category_id']) || (($seo_url['query'] != 'pavo_cat_id=' . $this->request->get['category_id'])))) {
								$this->errors['keyword'][$store_id][$language_id] = $this->language->get( 'error_keyword' );
								break;
							}
						}
					}
				}
			}
		}

		if ( $this->errors && ! isset( $this->errors['warning'] ) ) {
			$this->errors['warning'] = $this->language->get( 'error_warning' );
		}

		return ! $this->errors;
	}

	/**
	 * validate post form
	 */
	protected function validatePostForm() {
		if ( ! $this->user->hasPermission( 'modify', 'extension/module/pavoblog/post' )) {
			$this->errors['warning'] = $this->language->get( 'error_permission' );
		}

		foreach ($this->request->post['post_data'] as $language_id => $value) {
			if ( ( utf8_strlen( $value['name'] ) < 1 ) || ( utf8_strlen( $value['name'] ) > 255 ) ) {
				$this->errors['name'][$language_id] = $this->language->get( 'error_name' );
			}

			if ( ( utf8_strlen( $value['meta_title'] ) < 1 ) || ( utf8_strlen( $value['meta_title'] ) > 255 ) ) {
				$this->errors['meta_title'][$language_id] = $this->language->get( 'error_meta_title' );
			}
		}

		if ($this->request->post['post_seo_url']) {
			$this->load->model( 'design/seo_url' );

			foreach ($this->request->post['post_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->errors['keyword'][$store_id][$language_id] = $this->language->get( 'error_unique' );
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['post_id']) || (($seo_url['query'] != 'pavo_post_id=' . $this->request->get['post_id'])))) {
								$this->errors['keyword'][$store_id][$language_id] = $this->language->get( 'error_keyword' );
								break;
							}
						}
					}
				}
			}
		}

		if ( $this->errors && ! isset( $this->errors['warning'] ) ) {
			$this->errors['warning'] = $this->language->get( 'error_warning' );
		}

		return ! $this->errors;
	}

	private function validateSettingForm() {
		if ( ! $this->user->hasPermission( 'modify', 'extension/module/pavoblog/settings' ) ) {
			$this->errors['warning'] = $this->language->get( 'error_permission' );
		}

		if ( empty( $this->request->post['pavoblog_post_limit'] ) ) {
			$this->errors['error_pavoblog_post_limit'] = $this->language->get( 'error_pavoblog_post_limit' );
		}
		if ( empty( $this->request->post['pavoblog_post_description_length'] ) ) {
			$this->errors['error_pavoblog_post_description_length'] = $this->language->get( 'error_pavoblog_post_description_length' );
		}

		// thumb
		if ( empty( $this->request->post['pavoblog_image_thumb_width'] ) ) {
			$this->errors['error_pavoblog_image_thumb_width'] = $this->language->get( 'error_pavoblog_image_thumb_width' );
		}
		if ( empty( $this->request->post['pavoblog_image_thumb_height'] ) ) {
			$this->errors['error_pavoblog_image_thumb_height'] = $this->language->get( 'error_pavoblog_image_thumb_height' );
		}

		// avatar
		if ( empty( $this->request->post['pavoblog_avatar_width'] ) ) {
			$this->errors['error_pavoblog_avatar_width'] = $this->language->get( 'error_pavoblog_image_thumb_width' );
		}
		if ( empty( $this->request->post['pavoblog_avatar_height'] ) ) {
			$this->errors['error_pavoblog_avatar_height'] = $this->language->get( 'error_pavoblog_image_thumb_height' );
		}

		if ( ! empty( $this->request->post['blog_url'] ) || $this->request->post['author_url'] ) {
			$this->load->model( 'design/seo_url' );

			foreach ($this->request->post['blog_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->errors['blog_url'][$store_id][$language_id] = $this->language->get( 'error_unique' );
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if ( $seo_url['store_id'] == $store_id && $seo_url['language_id'] == $language_id && $seo_url['query'] != 'extension/pavoblog/archive' ) {
								$this->errors['blog_url'][$store_id][$language_id] = $this->language->get( 'error_keyword' );
								break;
							}
						}
					}
				}
			}
			foreach ($this->request->post['author_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->errors['author_url'][$store_id][$language_id] = $this->language->get( 'error_unique' );
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if ( $seo_url['store_id'] == $store_id && $seo_url['language_id'] == $language_id && $seo_url['query'] != 'extension/pavoblog/archive/author' ) {
								$this->errors['author_url'][$store_id][$language_id] = $this->language->get( 'error_keyword' );
								break;
							}
						}
					}
				}
			}
		}

		if ( $this->errors && ! isset( $this->errors['warning'] ) ) {
			$this->errors['warning'] = $this->language->get( 'error_warning' );
		}
		return ! $this->errors;
	}

	/**
	 * validate comment form update
	 */
	private function validateCommentForm() {
		if ( ! $this->user->hasPermission( 'modify', 'extension/module/pavoblog/comment' ) ) {
			$this->errors['warning'] = $this->language->get( 'error_permission' );
		}

		if ( empty( $this->request->post['comment_text'] ) ) {
			$this->errors['comment_text'] = $this->language->get( 'error_comment_text' );
		}

		if ( empty( $this->request->post['comment_name'] ) ) {
			$this->errors['comment_name'] = $this->language->get( 'error_comment_name' );
		}

		if ( empty( $this->request->post['comment_email'] ) ) {
			$this->errors['comment_email'] = $this->language->get( 'error_comment_email' );
		} else if ( ! filter_var( $this->request->post['comment_email'], FILTER_VALIDATE_EMAIL ) ) {
			$this->errors['comment_email'] = $this->language->get( 'error_comment_email_invalid' );
		}

		if ( $this->errors && ! isset( $this->errors['warning'] ) ) {
			$this->errors['warning'] = $this->language->get( 'error_warning' );
		}
		return ! $this->errors;
	}

	/**
	 * install actions
	 * create new permission and tables
	 */
	public function install() {
		$this->load->model( 'extension/pavoblog/setting' );
		$this->model_extension_pavoblog_setting->install();
	}

	/**
	 * uninstall actions
	 * remove user permission
	 */
	public function uninstall() {
		$this->load->model( 'extension/pavoblog/setting' );
		$this->model_extension_pavoblog_setting->uninstall();
	}
}