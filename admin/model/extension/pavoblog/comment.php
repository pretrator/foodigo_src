<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class ModelExtensionPavoblogComment extends Model {

	/**
	 * create - update comment
	 */
	public function updateComment( $data = array() ) {
		$data = array_merge( array(
			'comment_id'		=> '',
			'comment_text'		=> '',
			'comment_name'		=> '',
			'comment_email'		=> '',
			'comment_status'	=> ''
		), $data );
		extract( $data );

		$sql = "UPDATE " . DB_PREFIX . "pavoblog_comment SET `comment_text` = '".$this->db->escape( $comment_text )."', `comment_name` = '".$this->db->escape( $comment_name )."', `comment_email` = '".$this->db->escape( $comment_email )."', `comment_status` = ".(int)$comment_status." WHERE comment_id = " . (int)$comment_id;
		$this->db->query( $sql );

		return $this->db->countAffected();
	}

	/**
	 * delete comment
	 */
	public function deleteComment( $comment_id = null ) {
		$sql = "DELETE FROM " . DB_PREFIX . "pavoblog_comment WHERE comment_id = " . $comment_id;
		$this->db->query( $sql );
		return $this->db->countAffected();
	}

	/**
	 * get comments
	 */
	public function getComments( $args = array() ) {
		$args = array_merge( array(
				'comment_id'	=> 0,
				'post_id'		=> 0,
				'user_id'		=> 0,
				'parent_id'		=> '',
				'approved'		=> '',
				'order'			=> 'DESC',
				'orderby'		=> 'comment_id',
				'limit'			=> $this->config->get('pavoblog_post_limit'),
				'start'			=> 0
			), $args );
		extract( $args );

		$sql = 'SELECT SQL_CALC_FOUND_ROWS comment.*, postdsc.* FROM ' . DB_PREFIX . 'pavoblog_comment AS comment';
		$sql .= ' INNER JOIN ' . DB_PREFIX . 'pavoblog_post_description AS postdsc ON postdsc.post_id = comment.comment_post_id AND comment.comment_language_id = postdsc.language_id';
		$where = array();

		if ( $comment_id ) {
			$where[] = 'comment_id = ' . $comment_id;
		}

		if ( $post_id ) {
			$where[] = 'post_id = ' . $post_id;
		}

		// user_id
		if ( $user_id ) {
			$where[] = 'user_id = ' . $user_id;
		}

		if ( $parent_id ) {
			$where[] = 'parent_id = ' . $parent_id;
		}

		// approved
		if ( $approved !== '' ) {
			$where[] = 'approved = ' . $approved;
		}

		if ( $where ) {
			$where = implode( ' AND ', $where );
			$sql .= ' WHERE ' . $where;
		}

		if ( $order && $orderby ) {
			switch ( $orderby ) {
				case 'email':
						$orderby = 'comment.comment_email';
					break;
				case 'name':
						$orderby = 'comment.comment_name';
					break;
				case 'date':
						$orderby = 'comment.date_added';
					break;

				default:
						$orderby = 'comment.comment_id';
						$order = 'DESC';
					break;
			}

			$sql .= " ORDER BY $orderby $order";//echo " ORDER BY $orderby $order"; die();
		}

		if ( $start !== false && $limit ) {
			$sql .= " LIMIT $start, $limit";
		}

		$query = $this->db->query( $sql );

		$results = array();
		if ( $query->rows ) foreach ( $query->rows as $row ) {
			$row['edit_link'] = $this->url->link( 'extension/module/pavoblog/comment', 'comment_id='.(int)$row['comment_id'].'&user_token=' . $this->session->data['user_token'], true );
			$row['edit_post_link'] = $this->url->link( 'extension/module/pavoblog/post', 'post_id='.(int)$row['comment_post_id'].'&user_token=' . $this->session->data['user_token'], true );
			$row['delete_link'] = $this->url->link( 'extension/module/pavoblog/deleteComment', 'comment_id='.(int)$row['comment_id'].'&user_token=' . $this->session->data['user_token'], true );
			$row['toggle_approve_link'] = $this->url->link( 'extension/module/pavoblog/toggleCommentStatus', 'comment_id='.(int)$row['comment_id'].'&user_token=' . $this->session->data['user_token'], true );
			$results[] = $row;
		}
		return $results;
	}

	/**
	 * get single comment
	 * @param $comment_id
	 */
	public function getComment( $comment_id = null ) {
		$sql = 'SELECT * FROM ' . DB_PREFIX . 'pavoblog_comment WHERE comment_id = ' . $comment_id;

		$query = $this->db->query( $sql );
		return $query->row;
	}

	/**
	 * update comment status
	 */
	public function updateStatus( $comment_id = false, $status = 1 ) {
		$sql = "UPDATE " . DB_PREFIX . "pavoblog_comment SET `comment_status` = " . (int)$status . " WHERE `comment_id` = " . (int) $comment_id;
		$query = $this->db->query( $sql );
		// affected rows
		return $this->db->countAffected();
	}

	public function getTotals() {
		$query = $this->db->query( 'SELECT FOUND_ROWS()' );
		if ( $query->row && isset( $query->row['FOUND_ROWS()'] ) ) {
			return (int)$query->row['FOUND_ROWS()'];
		}
		return 0;
	}

}
