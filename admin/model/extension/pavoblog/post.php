<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class ModelExtensionPavoblogPost extends Model {

	/**
	 * get posts
	 */
	public function getPosts( $args = array() ) {
		$args = array_merge( array(
				'limit'				=> $this->config->get('pavoblog_post_limit') ? $this->config->get('pavoblog_post_limit') : 10,
				'start'				=> 0,
				'order'				=> 'DESC',
				'orderby'			=> 'post_id',
				'language_id'		=> $this->config->get( 'config_language_id' )
			), $args );
		extract( $args );

		$sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . DB_PREFIX . 'pavoblog_post AS posts';
		$sql .= ' LEFT JOIN ' . DB_PREFIX . 'pavoblog_post_description AS post_desc ON posts.post_id = post_desc.post_id';
		$sql .= ' LEFT JOIN ' . DB_PREFIX . 'user AS u ON u.user_id = posts.user_id' ;
		$sql .= ' WHERE post_desc.language_id = ' . $language_id;

		// order
		if ( $order && $orderby ) {
			switch ( $orderby ) {
				case 'post_id':
				case 'ID':
						$orderby = 'posts.post_id';
					break;
				case 'title':
						$orderby = 'post_desc.name';
					break;
				case 'author':
						$orderby = 'u.username';
					break;
				case 'date':
						$orderby = 'posts.date_added';
					break;
				case 'user_id':
						$orderby = 'posts.user_id';
					break;

				default:
						$orderby = 'posts.post_id';
						$order = 'DESC';
					break;
			}
			$sql .= " ORDER BY {$orderby} {$order}";
		}

		// limit
		$sql .= " LIMIT {$start}, {$limit}";
		$query = $this->db->query( $sql );
		$results = array();
		if ( $query->rows ) foreach ( $query->rows as $key => $row ) {
			$row['edit'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavoblog/post', 'post_id='.$row['post_id'].'&user_token=' . $this->session->data['user_token'], true ) );
			$row['user_url'] = str_replace( '&amp;', '&', $this->url->link( 'user/user/edit', 'user_id='.$row['user_id'].'&user_token=' . $this->session->data['user_token'], true ) );
			$results[$key] = $row;
		}
		return $results;
	}

	public function getTotals() {
		$query = $this->db->query( 'SELECT FOUND_ROWS()' );
		if ( $query->row && isset( $query->row['FOUND_ROWS()'] ) ) {
			return (int)$query->row['FOUND_ROWS()'];
		}
		return 0;
	}

	/**
	 * get single post
	 */
	public function getPost( $post_id = null ) {
		$this->load->model( 'tool/image' );
		$sql = "SELECT * FROM " . DB_PREFIX . "pavoblog_post WHERE post_id = " . $post_id;
		$query = $this->db->query( $sql );
		$results = $query->row;
		$data = array();
		foreach ( $results as $name => $value ) {
			$data[$name] = $value;
			if ( $name === 'video' && $value ) {
				$data[ 'iframe_url' ] = $this->getYoutubeIframeUrl( $value );
			} else if ( $name === 'gallery' ) {
				$galleries = json_decode( $value, true );
				$data[$name] = array();
				if ( $galleries ) foreach ( $galleries as $gallery ) {
					$gallery['thumb'] = ! empty( $gallery['image'] ) ? $this->model_tool_image->resize( $gallery['image'], 100, 100 ) : $this->model_tool_image->resize( 'no_image.png', 100, 100);
					$data[$name][] = $gallery;
				}
				$data['gallery_count'] = count( $data[$name] );
			}
		}
		return $data;
	}

 	public function getYoutubeIframeUrl( $url = '' ) {
 		preg_match( '/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i', $url, $match );
		$video_id = ! empty( $match[2] ) ? $match[2] : false;
		$query = array();
		if ( $video_id ) {
			$query = array(
				'playlist'		=> $video_id,
				'enablejsapi' 	=> 1,
				'iv_load_policy'=> 3,
				'disablekb'		=> 1,
				'autoplay'		=> 0,
				'controls'		=> 0,
				'showinfo'		=> 0,
				'rel'			=> 0,
				'loop'			=> 1,
				'mute'			=> 0,
				'wmode'			=> 'transparent'
			);
		}

		return $video_id ? 'https://youtube.com/embed/' . $video_id . '?' . http_build_query( $query ) : '';
 	}

	public function getPostCategories( $post_id = null ) {
		$sql = "SELECT category_id FROM " . DB_PREFIX . "pavoblog_post_to_category WHERE `post_id` = " . (int)$post_id;
		$query = $this->db->query( $sql );
		$results = array();
		foreach ( $query->rows as $row ) {
			$results[] = isset( $row['category_id'] ) ? $row['category_id'] : 0;
		}

		return $results;
	}

	public function getPostDescription( $post_id = null ) {
		$sql = "SELECT * FROM " . DB_PREFIX . "pavoblog_post_description WHERE post_id =" . $post_id;
		$query = $this->db->query( $sql );
		$results = array();
		if ( $query->rows ) foreach ( $query->rows as $row ) {
			$results[$row['language_id']] = $row;
		}
		return $results;
	}

	/**
	 * add post
	 */
	public function addPost( $data = array() ) {
		$data = array_merge( array(
				'name'				=> '',
				'image'				=> '',
				'gallery'			=> array(),
				'video'				=> '',
				'user_id'			=> 1,
				'description'		=> '',
				'content'			=> '',
				'tag'				=> '',
				'date_added'		=> '',
				'dated_modifed'		=> '',
				'viewed'			=> '',
				'type'				=> 'image',
				'post_data'			=> array(),
				'post_seo_url'		=> array(),
				'post_store'		=> array(),
				'categories'		=> array()
			), $data );

		extract( $data );
		$sql = "INSERT INTO " . DB_PREFIX . "pavoblog_post (`image`, `gallery`, `video`, `viewed`, `status`, `featured`, `user_id`, `type`, `date_added`, `date_modified`)";
		$sql .= " VALUES ( '". $this->db->escape( $image ) ."', '".$this->db->escape( json_encode( $gallery ) )."', '".$this->db->escape( $video )."', '".(int)$viewed."', '".(int)$status."', '".(int)$featured."', '".(int)$user_id."', '".$this->db->escape( $type )."', '" . ( $date_added ? $this->db->escape( $date_added ) : 'NOW()' ) . "', NOW() )";

		$this->db->query( $sql );
		$post_id = $this->db->getLastId();

		if ( $post_data ) {
			foreach ( $post_data as $language_id => $data ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_post_description SET `post_id` = " . (int)$post_id . ", `language_id` = '" . (int)$language_id . "', `name` = '" . $this->db->escape( $data['name'] ) . "', `description` = '" . $this->db->escape( $data['description'] ) . "', `content` = '" . $this->db->escape( $data['content'] ) . "', `tag` = '" . $this->db->escape( $data['tag'] ) . "', `meta_title` = '" . $this->db->escape( $data['meta_title'] ) . "', `meta_description` = '" . $this->db->escape( $data['meta_description'] ) . "', `meta_keyword` = '" . $this->db->escape( $data['meta_keyword'] ) . "'");
			}
		}

		if ( $post_store ) {
			foreach ( $post_store as $store_id ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_post_to_store SET `post_id` = " . (int)$post_id . ", `store_id` = '" . (int)$store_id . "'");
			}
		}

		if ( $categories ) {
			foreach ( $categories as $category_id ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_post_to_category SET `post_id` = " . (int)$post_id . ", `category_id` = '" . (int)$category_id . "'");
			}
		}

		if ( $post_seo_url ) {
			foreach ( $post_seo_url as $store_id => $language ) {
				foreach ($language as $language_id => $keyword) {
					if ( ! empty( $keyword )) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'pavo_post_id=" . (int)$post_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('pavoblog_post');

		return $post_id;
	}

	public function editPost( $post_id = null, $data = array() ) {
		$data = array_merge( array(
				'name'				=> '',
				'image'				=> '',
				'gallery'			=> array(),
				'video'				=> '',
				'user_id'			=> 1,
				'description'		=> '',
				'content'			=> '',
				'tag'				=> '',
				'date_added'		=> '',
				'dated_modifed'		=> '',
				'viewed'			=> '',
				'type'				=> 'image',
				'post_data'			=> array(),
				'post_seo_url'		=> array(),
				'post_store'		=> array()
			), $data );

		extract( $data );

		$sql = "UPDATE " . DB_PREFIX . "pavoblog_post SET `image` = '".$image."', `gallery` = '".$this->db->escape( json_encode( $gallery ) )."', `video` = '".$this->db->escape( $video )."', `status` = '".$status."', `featured` = '".$featured."', `user_id` = '".$user_id."', `type` = '".$this->db->escape( $type )."', `date_modified` = NOW()";

		if ( $date_added ) {
			$sql .= ", `date_added` = '".$date_added."'";
		}
		if ( $viewed ) {
			$sql .= ", `viewed` = '".$viewed."'";
		}

		$sql .= " WHERE post_id = ".(int) $post_id."";
		// excute query
		$this->db->query( $sql );

		// post description
		$this->db->query("DELETE FROM " . DB_PREFIX . "pavoblog_post_description WHERE post_id = '" . (int)$post_id . "'");
		// post data
		foreach ( $data['post_data'] as $language_id => $value ) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_post_description SET post_id = '" . (int)$post_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', content = '" . $this->db->escape($value['content']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// post to store
		$this->db->query("DELETE FROM " . DB_PREFIX . "pavoblog_post_to_store WHERE post_id = '" . (int)$post_id . "'");
		if (isset($data['post_store'])) {
			foreach ( $data['post_store'] as $store_id ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_post_to_store SET post_id = '" . (int)$post_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "pavoblog_post_to_category WHERE post_id = '" . (int)$post_id . "'");
		if (isset($data['categories'])) {
			foreach ( $data['categories'] as $category_id ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_post_to_category SET post_id = '" . (int)$post_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'pavo_post_id" . (int)$post_id . "'");
		if (isset($data['post_seo_url'])) {
			foreach ($data['post_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ( ! empty( $keyword )) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'pavo_post_id=" . (int)$post_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('pavoblog_post');

		return $post_id;
	}

	/**
	 * delete post
	 */
	public function deletePost( $post_id = null ) {
		$sql = "DELETE FROM " . DB_PREFIX . "pavoblog_post WHERE post_id = " . $post_id;
		$this->db->query( $sql );

		return $this->db->getLastId();
	}

	public function getPostStore( $post_id = null ) {
		$sql = 'SELECT store_id FROM ' . DB_PREFIX . 'pavoblog_post_to_store WHERE post_id = ' . $post_id;
		$query = $this->db->query( $sql );
		$results = array();
		foreach ( $query->rows as $row ) {
			$results[] = isset( $row['store_id'] ) ? $row['store_id'] : 0;
		}
		return $results;
	}

	public function getSeoUrlData( $post_id = null ) {
		$sql = "SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'pavo_post_id=" . $post_id . "'";
		$query = $this->db->query( $sql );
		$results = array();

		if ( $query->rows ) {
			foreach ( $query->rows as $row ) {
				$store_id = isset( $row['store_id'] ) ? $row['store_id'] : 0;
				$language_id = isset( $row['language_id'] ) ? $row['language_id'] : 0;
				if ( ! isset( $results[$store_id] ) ) {
					$results[$store_id] = array();
				}

				$results[$store_id][$language_id] = isset( $row['keyword'] ) ? $row['keyword'] : '';
			}
		}

		return $results;
	}

}
