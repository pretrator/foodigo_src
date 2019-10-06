<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class ModelExtensionPavoBlogCategory extends Model {

	public function getCategories( $data = array() ) {
		$data = array_merge( array(
			'start'		    => 0,
			'limit'		    => 10,
			'orderby'	    => 'category_id',
			'order'		    => 'DESC',
			'language_id'	=> $this->config->get( 'config_language_id' ),
			'store_id'		=> $this->config->get( 'config_store_id' ),
            'group_by'      =>''
		), $data );
		extract( $data );

		$sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT * FROM " . DB_PREFIX . "pavoblog_category AS cat";
		if ( $data['store_id'] ) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category_to_store AS catSt ON catSt.category_id = cat.category_id AND catSt.store_id = " . (int)$data['store_id'];
		}

		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category_description AS catdesc ON catdesc.category_id = cat.category_id AND catdesc.language_id = " . $this->db->escape( $data['language_id'] );

		$where = ' WHERE 1=1';

		$sql .= $where;
		if ( $data['orderby'] ) {
			$sql .= " ORDER BY catdesc.".$data['orderby'];
		}

        if ( $data['order']  ) {
            $sql .= " ".$data['order'];
        }

		if ( $data['start'] !== '' && $data['limit'] !== '' ) {
			$sql .= " LIMIT {$data['start']}, {$data['limit']}";
		}

		$query = $this->db->query( $sql );
		return $query->rows;
 	}

 	/**
 	 * select single category
 	 */
 	public function getCategory( $category_id = null ) {
 		$language_id = $this->config->get( 'config_language_id' );
 		$store_id = $this->config->get( 'config_store_id' );

 		$sql = "SELECT cat.*, catdesc.* FROM " . DB_PREFIX . "pavoblog_category AS cat";
 		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category_description AS catdesc ON catdesc.category_id = cat.category_id AND catdesc.language_id = " . (int)$language_id;
 		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_category_to_store AS catSt ON catSt.store_id = " . (int)$store_id;
 		$sql .= " WHERE cat.category_id = " . (int) $category_id;
 		$sql .= " GROUP BY cat.category_id";

 		$query = $this->db->query( $sql );
 		return $query->row;
 	}

 	public function getCategoriesp($parent_id = 0) {
		$sql = "SELECT pc.parent_id, pcd.category_id, pcd.name, pcd.language_id FROM " . DB_PREFIX . "pavoblog_category pc 
			LEFT JOIN " . DB_PREFIX . "pavoblog_category_description pcd ON (pc.category_id = pcd.category_id) 
			LEFT JOIN " . DB_PREFIX . "pavoblog_category_to_store AS pc2s ON (pc.category_id = pc2s.category_id) 
			WHERE pc.parent_id = " . (int)$parent_id . " 
			AND pcd.language_id = " . (int)$this->config->get('config_language_id') . "   
			AND pc.status = 1";
		// echo $sql;die;
		$query = $this->db->query($sql);
		return $query->rows;
	}

}