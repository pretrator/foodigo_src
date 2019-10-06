<?php
/**
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */
class ControllerExtensionPavoBlogSingle extends Controller{

	public function index() {
		/**
		 * load model - language
		 */
		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/post' );
		$this->load->model( 'extension/pavoblog/comment' );
		$this->load->model( 'tool/image' );
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		
		$this->document->addScript( 'catalog/view/javascript/jquery/swiper/js/swiper.min.js' );

		$data = array();

		$post_id = isset( $this->request->get['pavo_post_id'] ) ? abs( $this->request->get['pavo_post_id'] ) : false;
		if ( ! $post_id ) {
			$this->response->redirect( str_replace( '&amp;', '&', $this->url->link( 'error', '' ) ) ); exit();
		}
		$post = $this->model_extension_pavoblog_post->getPost( $post_id );

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_blog'),
			'href' => $this->url->link('extension/pavoblog/archive')
		);
		$data['breadcrumbs'][] = array(
			'text'	=> $post['name'],
			'href'	=> $this->url->link( 'extension/pavoblog/single', 'pavo_post_id=' . $post['post_id'] )
		);
		if ( empty( $post['type'] ) ) {
			$post['type'] = 'image';
		}
		$width  = $this->config->get('pavoblog_image_thumb_width');
		$height = $this->config->get('pavoblog_image_thumb_height');
		$blog_image_width  = !empty( $width ) ? $width : 200;
		$blog_image_height = !empty( $height ) ? $height : 200;

		
		if ( ! empty( $post['image'] ) ) {
			if ( $post['image'] && $this->config->get( 'pavoblog_post_single_image_type' ) == 0 ) {
				$post['thumb'] = $this->model_tool_image->resize( $post['image'], $blog_image_width, $blog_image_height );
			} else {
				$post['thumb'] = ( $this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER ) . 'image/' . $post['image'];
			}
		} else {
			$post['thumb'] = $this->model_tool_image->resize( 'placeholder.png', $blog_image_width, $blog_image_height );
		}
		if ( ! empty( $post['content'] ) ) {
			$post['content'] = html_entity_decode( $post['content'], ENT_QUOTES, 'UTF-8' );
		}

		if ( ! empty( $post['tag'] ) ) {
			$post['tag_href'] = $this->url->link( 'extension/pavoblog/archive', 'tag=' . $post['tag'] );
		}
		$post['categories'] = $this->model_extension_pavoblog_post->getCategories( $post_id );
		$post['author_href'] = ! empty( $post['username'] ) ? $this->url->link( 'extension/pavoblog/archive/author', 'pavo_username=' . $post['username'] ) : '';

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
		
		$data['post'] = $post;
		if ( $post['type'] == 'gallery' ) {
			$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
			$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
			
		}
		/**
		 * set document title
		 */
		$title = $this->language->get( 'heading_title' );
		if ( ! empty( $post['meta_title'] ) ) {
			$title = html_entity_decode( $post['meta_title'], ENT_QUOTES, 'UTF-8' );
		}
		$this->document->setTitle( $title );

		// set meta description
		if ( ! empty( $post['meta_description'] ) ) {
			$this->document->setDescription( html_entity_decode( $post['meta_description'], ENT_QUOTES, 'UTF-8' ) );
		}

		// set meta keyword
		if ( ! empty( $post['meta_keyword'] ) ) {
			$this->document->setKeywords( html_entity_decode( $post['meta_keyword'], ENT_QUOTES, 'UTF-8' ) );
		}
		// loaded default stylesheet
		if ( $this->config->get( 'pavoblog_default_style' ) ){
            $file = DIR_TEMPLATE . 'default/stylesheet/pavoblog.min.css';
            if ( file_exists( $file ) ) {
                $file = str_replace( DIR_APPLICATION, basename( DIR_APPLICATION ) . '/', $file );
                $this->document->addStyle( $file );
            }
        }

        //related blog
        $data['related_posts'] 	= $this->config->get('pavoblog_related_posts');
        $data['related_number'] = $this->config->get('pavoblog_related_number');
        $data['related_rows']  	= $this->config->get('pavoblog_related_rows');
        $data['related_width'] 	= $this->config->get('pavoblog_image_related_width');
        $data['related_height'] = $this->config->get('pavoblog_image_related_height');
        $data['date_format'] = $this->config->get( 'pavoblog_date_format' ) ? $this->config->get( 'pavoblog_date_format' ) : 'Y-m-d';
		$data['time_format'] = $this->config->get( 'pavoblog_time_format' ) ? $this->config->get( 'pavoblog_time_format' ) : '';
        $data['gallery_items_per_view'] = $this->config->get('pavoblog_post_gallery_items_per_view') ? $this->config->get('pavoblog_post_gallery_items_per_view') : 5;
        $filter_data = array (
         	'limit'       => $this->config->get('pavoblog_related_limit'),
        	'user_id'     => $post['user_id'],
        	'tag'	  	  => $post['tag'],
         );
        $filter_data['categories'] = array ();
        foreach ($post['categories'] as $cat_id) {
        $filter_data['categories'][] = array (
        	'category_id' => $cat_id['category_id']
        );
     	}
    	
        $get_related_posts = $this->model_extension_pavoblog_post->getRelatedPosts($filter_data);
        $get_related = array ();

        foreach ($get_related_posts as $related) {

        	$get_related[] = array (
        		'post_id'		=> $related['post_id'],
        		'post_name'     => html_entity_decode($related['post_name'], ENT_QUOTES, 'UTF-8'),
                'description'   => html_entity_decode($related['description'], ENT_QUOTES, 'UTF-8'),
                'content'       => html_entity_decode($related['content'], ENT_QUOTES, 'UTF-8'),
                'thumb'         => $this->model_tool_image->resize($related['image'],$data['related_width'],$data['related_height']),
                'link'          =>  $this->url->link( 'extension/pavoblog/single','pavo_post_id='.$related['post_id'] ),
                'date_added'    => isset($related['date_added'])?($related['date_added']):'',
                'user_nicename' => isset($related['user_nicename'])?$related['user_nicename']:'',
                'author_href'	=> $this->url->link( 'extension/pavoblog/archive/author', 'pavo_username=' . $related['username'] ),
                'tag'			=> isset($related['tag'])?($related['tag']):'',
                'tag_href'		=> $this->url->link( 'extension/pavoblog/archive', 'tag=' . $related['tag'] ),
                'category_id'	=> isset($related['category_id'])?($related['category_id']):'',
                'cat_name'		=> html_entity_decode($related['cate_name'], ENT_QUOTES, 'UTF-8'),
                'category_url'	=> $this->url->link( 'extension/pavoblog/archive', 'pavo_cat_id=' . $related['category_id'] )
        	);
        }
    	
        $data['get_related'] = $get_related;
 
		$data['comment_section'] = $this->load->controller( 'extension/pavoblog/comment' );
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		/**
		 * set layout template
		 */
		$this->response->setOutput( $this->load->view( 'pavoblog/single', $data ) );
	}

}