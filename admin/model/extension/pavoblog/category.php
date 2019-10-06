<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class ModelExtensionPavoblogCategory extends Model {

	/**
	 * get all categories with args
	 */
	public function getCategories( $args = array() ) {
		$args = array_merge( array(
				'parent_id'		=> '',
				'status'		=> '',
				'order'			=> 'DESC',
				'orderby'		=> 'name',
				'start'			=> 0,
				'not_in'		=> array(),
				'limit'			=> $this->config->get('pavoblog_post_limit') ? $this->config->get('pavoblog_post_limit') : 10,
				'language_id'	=> $this->config->get( 'config_language_id' )
			), $args );

		extract( $args );

		$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id FROM " . DB_PREFIX . "pavoblog_category_path cp";
		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category c1 ON (cp.category_id = c1.category_id)";
		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category c2 ON (cp.path_id = c2.category_id)";
		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category_description cd1 ON (cp.path_id = cd1.category_id)";
		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category_description cd2 ON (cp.category_id = cd2.category_id)";
		$sql .= " WHERE cd1.language_id=" .$language_id. " AND cd2.language_id=" .$language_id;

		//Filter
		$sql .= " GROUP BY cp.category_id";

		if ( $order && $orderby ) {
			switch ( $orderby ) {
				case 'name':
						$orderby = 'cd1.name';
					break;
				
				default:
						$orderby = 'c1.category_id';
					break;
			}
			$sql .= " ORDER BY $orderby $order";
		}

		if ( $limit != -1 ) {
			$sql .= ' LIMIT ' . $start . ', ' . $limit;
		}

		$query = $this->db->query( $sql );

		return $query->rows;
	}

	public function getTotals() {
		$query = $this->db->query( 'SELECT FOUND_ROWS()' );
		if ( $query->row && isset( $query->row['FOUND_ROWS()'] ) ) {
			return (int)$query->row['FOUND_ROWS()'];
		}
		return 0;
	}

	/**
	 * get single category
	 * @param $category_id
	 */
	public function getCategory( $category_id = null ) {
		$sql = 'SELECT * FROM ' . DB_PREFIX . 'pavoblog_category AS category WHERE category_id = ' . $category_id;
		$query = $this->db->query( $sql );

		return $query->row;
	}

	public function getCategoryDescription( $category_id = null, $language_id = null ) {
		$sql = 'SELECT * FROM ' . DB_PREFIX . 'pavoblog_category_description WHERE category_id = ' . $category_id;
		if ( $language_id ) {
			$sql .= ' AND language_id = ' . $language_id;
		}

		$results = array();
		$query = $this->db->query( $sql );
		if ( $language_id ) return $query->row;

		foreach ( $query->rows as $result) {
			$results[$result['language_id'] ] = $result;
		}

		return $results;
	}

	public function getCategoryStore( $category_id = null ) {
		$sql = 'SELECT store_id FROM ' . DB_PREFIX . 'pavoblog_category_to_store WHERE category_id = ' . $category_id;
		$query = $this->db->query( $sql );
		$results = array();
		foreach ( $query->rows as $row ) {
			$results[] = isset( $row['store_id'] ) ? $row['store_id'] : 0;
		}
		return $results;
	}

	public function getSeoUrlData( $category_id = null ) {
		$sql = "SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'pavo_cat_id=" . $category_id . "'";
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

	/**
	 * create - update category
	 */
	public function addCategory( $data = array() ) { //echo "<pre>".print_r($data,1);die;
		$params = array(
			'image' 	=> ! empty( $data['image'] ) 		? $this->db->escape( $data['image'] ) : '',
			'parent_id' => ! empty( $data['parent_id'] ) 	? (int)$data['parent_id'] : 0,
			'column' 	=> ! empty( $data['column'] ) 		? (int)$data['column'] : 1,
			'status'	=> ! empty( $data['status'] ) 		? (int)$data['status'] : 1
		);

		//echo "<pre>".print_r($params,1);die;
		$sql = "INSERT INTO " . DB_PREFIX . "pavoblog_category ( `image`, `parent_id`, `column`, `status`, `date_added`, `date_modified` )";
		$sql .= " VALUES ( '".$params['image']."', '".$params['parent_id']."', '".$params['column']."', '".$params['status']."', NOW(), NOW() )";

		$this->db->query( $sql );
		// category id
		$category_id = $this->db->getLastId(); //echo $category_id;die;

		// category data
		foreach ( $data['category_data'] as $language_id => $value ) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$level = 0;

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "pavoblog_category_path` WHERE category_id = '" . (int)$params['parent_id'] . "' ORDER BY `level` ASC");

		foreach ($query->rows as $result) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "pavoblog_category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', `level` = '" . (int)$level . "'");

			$level++;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "pavoblog_category_path` SET `category_id` = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', `level` = '" . (int)$level . "'");

		if (isset($data['category_store'])) {
			foreach ( $data['category_store'] as $store_id ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ( ! empty( $keyword )) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'pavo_cat_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('pavoblog_category');

		return $category_id;
	}

	/**
	 * edit category
	 */
	public function editCategory( $category_id, $data = array() ) {
		$params = array(
			'image' 	=> ! empty( $data['image'] ) 		? $this->db->escape( $data['image'] ) : '',
			'parent_id' => ! empty( $data['parent_id'] ) 	? (int)$data['parent_id'] : 0,
			'column' 	=> ! empty( $data['column'] ) 		? (int)$data['column'] : 1,
			'status'	=> ! empty( $data['status'] ) 		? (int)$data['status'] : 0,
		);

		$sql = "UPDATE " . DB_PREFIX . "pavoblog_category SET `image` = '".$params['image']."', `parent_id` = '".$params['parent_id']."', `column` = '".$params['column']."', `status` = '".$params['status']."', `date_modified` = NOW() WHERE category_id = '".$category_id."'";
		// excute query
		$this->db->query( $sql );

		// category description
		$this->db->query("DELETE FROM " . DB_PREFIX . "pavoblog_category_description WHERE category_id = '" . (int)$category_id . "'");
		// category data
		foreach ( $data['category_data'] as $language_id => $value ) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		// MySQL Hierarchical Data Closure Table Pattern
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "pavoblog_category_path` WHERE path_id = '" . (int)$category_id . "' ORDER BY level ASC");

		if ($query->rows) {
			foreach ($query->rows as $category_path) {
				// Delete the path below the current one
				$this->db->query("DELETE FROM `" . DB_PREFIX . "pavoblog_category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' AND level < '" . (int)$category_path['level'] . "'");

				$path = array();

				// Get the nodes new parents
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "pavoblog_category_path` WHERE category_id = '" . (int)$params['parent_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Get whats left of the nodes current path
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "pavoblog_category_path` WHERE category_id = '" . (int)$category_path['category_id'] . "' ORDER BY level ASC");

				foreach ($query->rows as $result) {
					$path[] = $result['path_id'];
				}

				// Combine the paths with a new level
				$level = 0;

				foreach ($path as $path_id) {
					$this->db->query("REPLACE INTO `" . DB_PREFIX . "pavoblog_category_path` SET category_id = '" . (int)$category_path['category_id'] . "', `path_id` = '" . (int)$path_id . "', level = '" . (int)$level . "'");

					$level++;
				}
			}
		} else {
			// Delete the path below the current one
			$this->db->query("DELETE FROM `" . DB_PREFIX . "pavoblog_category_path` WHERE category_id = '" . (int)$category_id . "'");

			// Fix for records with no paths
			$level = 0;

			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "pavoblog_category_path` WHERE category_id = '" . (int)$params['parent_id'] . "' ORDER BY level ASC");

			foreach ($query->rows as $result) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "pavoblog_category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

				$level++;
			}

			$this->db->query("REPLACE INTO `" . DB_PREFIX . "pavoblog_category_path` SET category_id = '" . (int)$category_id . "', `path_id` = '" . (int)$category_id . "', level = '" . (int)$level . "'");
		}

		// category to store
		$this->db->query("DELETE FROM " . DB_PREFIX . "pavoblog_category_to_store WHERE category_id = '" . (int)$category_id . "'");
		if (isset($data['category_store'])) {
			foreach ( $data['category_store'] as $store_id ) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "pavoblog_category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'pavo_cat_id=" . (int)$category_id . "'");
		if (isset($data['category_seo_url'])) {
			foreach ($data['category_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if ( ! empty( $keyword )) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'pavo_cat_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->cache->delete('pavoblog_category');

		return $category_id;
	}

	/**
	 * delete category
	 */
	public function deleteCategory( $cat_id = null ) {

		$this->db->query("DELETE FROM " . DB_PREFIX . "pavoblog_category_path WHERE category_id = '" . (int)$cat_id . "'");

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "pavoblog_category_path WHERE path_id = '" . (int)$cat_id . "'");

		foreach ($query->rows as $result) {
			$this->deleteCategory($result['category_id']);
		}
		
		$this->db->query( "DELETE FROM " . DB_PREFIX . 'pavoblog_category WHERE category_id = ' . (int)$cat_id );
		$query = $this->db->query( 'SELECT category_id FROM ' . DB_PREFIX . 'pavoblog_category WHERE parent_id = ' . (int)$cat_id );
		if ( $query->cols ) {
			foreach ( $query->cols as $cat_id ) {
				$this->delete( $cat_id );
			}
		}

		$this->db->query( 'DELETE FROM ' . DB_PREFIX . 'pavoblog_category_description WHERE category_id = ' . (int) $cat_id );

	}

}
