<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class ModelExtensionPavoblogSetting extends Model {

	public function install() {
		// START ADD USER PERMISSION
		$this->load->model( 'user/user_group' );
		// access - modify pavoblog edit
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavoblog/settings' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/settings' );
		// access - modify pavoblog posts
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavoblog/posts' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/post' );
		// categories
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavoblog/categories' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/category' );
		// comments
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavoblog/comments' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/comment' );
		// END ADD USER PERMISSION

		// CREATE TABLES
		// posts, comments, categories
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_post` (
				`post_id` int(11) NOT NULL AUTO_INCREMENT,
				`image` varchar(255) DEFAULT NULL,
				`gallery` text NULL,
				`video` varchar(255) NULL,
				`viewed` int(5) NOT NULL DEFAULT '0',
				`status` tinyint(1) NOT NULL,
				`featured` tinyint(1) NOT NULL,
				`user_id` int(11) NOT NULL,
				`type` varchar(20) NOT NULL,
				`date_added` datetime NOT NULL,
				`date_modified` datetime NOT NULL,
				PRIMARY KEY (`post_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_post_to_store` (
					`post_id` int(11) NOT NULL,
					`store_id` int(11) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
			");

		$this->db->query("
				CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_post_to_category` (
					`post_id` int(11) NOT NULL,
					`category_id` int(11) NOT NULL
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
			");

		// post description
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_post_description` (
				`post_id` int(11) NOT NULL,
				`language_id` int(11) NOT NULL,
				`name` varchar(255) NOT NULL,
				`description` text NULL,
				`content` text NULL,
				`tag` text NULL,
				`meta_title` varchar(255) NOT NULL,
				`meta_description` varchar(255) NOT NULL,
				`meta_keyword` varchar(255) NOT NULL,
				PRIMARY KEY (`post_id`,`language_id`),
				KEY `name` (`name`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		// blog category
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_category` (
			  `category_id` int(11) NOT NULL AUTO_INCREMENT,
			  `image` varchar(255) DEFAULT NULL,
			  `parent_id` int(11) NOT NULL DEFAULT '0',
			  `column` int(3) NOT NULL DEFAULT '1',
			  `status` tinyint(1) NOT NULL,
			  `date_added` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  PRIMARY KEY (`category_id`),
			  KEY `parent_id` (`parent_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		// category description
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_category_description` (
			  `category_id` int(11) NOT NULL,
			  `language_id` int(11) NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `description` text NOT NULL,
			  `meta_title` varchar(255) NOT NULL,
			  `meta_description` varchar(255) NOT NULL,
			  `meta_keyword` varchar(255) NOT NULL,
			  PRIMARY KEY (`category_id`,`language_id`),
			  KEY `name` (`name`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");
		
		// blog category path
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_category_path` (
				`category_id` int(11) NOT NULL,
				`path_id` int(11) NOT NULL,
				`level` int(11) NOT NULL,
				PRIMARY KEY (`category_id`,`path_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		// blog category to store
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_category_to_store` (
				`category_id` int(11) NOT NULL,
				`store_id` int(11) NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		// comment table
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_comment` (
			  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
			  `comment_title` varchar(255) NULL,
			  `comment_email` varchar(96) NOT NULL,
			  `comment_post_id` int(11) NOT NULL,
			  `comment_user_id` int(11) NOT NULL DEFAULT '0',
			  `comment_customer_id` int(11) NOT NULL DEFAULT '0',
			  `comment_name` varchar(64) NOT NULL,
			  `comment_text` text NOT NULL,
			  `comment_rating` int(1) NOT NULL,
			  `comment_status` tinyint(1) NOT NULL DEFAULT '0',
			  `comment_parent_id` int(11) NOT NULL DEFAULT '0',
			  `comment_subscribe` tinyint(1) NOT NULL DEFAULT '0',
			  `comment_store_id` int(11) NOT NULL DEFAULT '0',
			  `comment_language_id` int(11) NOT NULL DEFAULT '0',
			  `date_added` datetime NOT NULL,
			  `date_modified` datetime NOT NULL,
			  PRIMARY KEY (`comment_id`),
			  KEY `comment_post_id` (`comment_post_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		// comment subscribe
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavoblog_subscribe_post` (
			  `subscribe_id` int(11) NOT NULL AUTO_INCREMENT,
			  `subscribe_email` varchar(96) NOT NULL,
			  PRIMARY KEY (`subscribe_id`),
			  UNIQUE (subscribe_email),
			  KEY `subscribe_id` (`subscribe_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
		");

		// DEFAULT OPTIONS
		$this->load->model( 'design/seo_url' );
		$this->load->model( 'localisation/language' );
		$this->load->model( 'setting/store' );

		$stores = $this->model_setting_store->getStores();
		$languages = $this->model_localisation_language->getLanguages();
		$store_ids = array( 0 );
		foreach ( $stores as $store ) {
			$store_ids[] = isset( $store['store_id'] ) ? (int)$store['store_id'] : 0;
		}

		$query_strs = array(
				'extension/pavoblog/archive' 		=> 'blog',
				'extension/pavoblog/archive/author' => 'author'
		);
		foreach ( $store_ids as $store_id ) {
			foreach ( $languages as $language ) {

				$language_id = isset( $language['language_id'] ) ? (int)$language['language_id'] : 1;
				foreach ( $query_strs as $str => $val ) {
					$sql = "SELECT * FROM " . DB_PREFIX . "seo_url WHERE store_id = " . (int)$store_id . " AND language_id = " . (int)$language_id;
					$sql .= " AND query='".$this->db->escape( $str )."'";
					$query = $this->db->query( $sql );

					if ( ! $query->num_rows ) {
						$this->model_design_seo_url->addSeoUrl( array(
							'store_id'		=> $store_id,
							'language_id'	=> $language_id,
							'query'			=> $str,
							'keyword'		=> $val
						) );
					}
				}
			}
		}

		$this->load->model( 'setting/setting' );
		// options insert before
		$settings = $this->model_setting_setting->getSetting( 'pavoblog' );
		$settings = array_merge( array(
			'pavoblog_date_format'				=> 'F j, Y',
			'pavoblog_time_format'				=> 'g:i a',
			'pavoblog_pagination'				=> 1,
			'pavoblog_default_layout'			=> 'grid',
			'pavoblog_grid_columns'				=> 3,
			'pavoblog_post_limit'				=> 10,
			'pavoblog_post_description_length'	=> 200,
			'pavoblog_image_thumb_width'		=> 370,
			'pavoblog_image_thumb_height'		=> 210,
			'pavoblog_auto_approve_comment'		=> 1,
			'pavoblog_comment_avatar_width'		=> 54,
			'pavoblog_comment_avatar_height'	=> 54
		), $settings );
		$this->model_setting_setting->editSetting( 'pavoblog', $settings, $this->config->get( 'config_store_id' ) );

	}

	public function uninstall() {
		// START REMOVE USER PERMISSION
		$this->load->model( 'user/user_group' );
		// access - modify pavoblog edit
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavoblog/settings' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/settings' );
		// access - modify pavoblog posts
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavoblog/posts' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/post' );
		// categories
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavoblog/categories' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/category' );
		// comments
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavoblog/comments' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavoblog/comment' );
		// END REMOVE USER PERMISSION
	}

	public function getSeoData( $query_str = 'extension/pavoblog/archive' ) {
		$sql = "SELECT * FROM " . DB_PREFIX . "seo_url WHERE query='".$this->db->escape( $query_str )."'";

		$query = $this->db->query( $sql );
		$results = array();
		if ( $query->rows ) foreach ( $query->rows as $row ) {
			if ( ! isset( $results[$row['store_id']] ) ) {
				$results[$row['store_id']] = array();
			}
			if ( ! isset( $results[$row['store_id']][$row['language_id']] ) ) {
				$results[$row['store_id']][$row['language_id']] = $row['keyword'];
			}

		}

		return $results;
	}

	/**
	 * update blog seo url data
	 */
	public function editSeoData( $seo_data = array(), $query_str = 'extension/pavoblog/archive' ) {
		if ( ! $seo_data ) return;
		$this->load->model( 'design/seo_url' );

		foreach ( $seo_data as $store_id => $languages ) {
			foreach ( $languages as $language_id => $value ) {

				// delete old
				$sql = "DELETE FROM " . DB_PREFIX . "seo_url WHERE query='".$this->db->escape( $query_str )."' AND language_id='".(int)$language_id."' AND store_id='".(int)$store_id."'";
				$query = $this->db->query( $sql );

				$this->model_design_seo_url->addSeoUrl( array(
					'query'			=> $query_str,
					'store_id'		=> $store_id,
					'language_id'	=> $language_id,
					'keyword'		=> $value
				) );
			}
		}
	}

}
