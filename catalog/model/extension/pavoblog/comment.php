<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class ModelExtensionPavoBlogComment extends Model {

	/**
	 * get comments by post_id
	 *
	 * @param $post_id
	 * @return $comments
	 */
	public function getComments( $post_id = null ) {
		$subsSql = "(SELECT * FROM " . DB_PREFIX . "pavoblog_comment WHERE `comment_parent_id` = ".(int)$post_id." )";
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM " . DB_PREFIX . "pavoblog_comment AS comments WHERE comments.`comment_post_id` = " . (int)$post_id . " AND comments.`comment_store_id` = " . (int) $this->config->get( 'config_store_id' ) . " ORDER BY comments.comment_id DESC";
		$query = $this->db->query( $sql );

		$results = array();
		$rows = $query->rows;

		// tool image
		$this->load->model( 'tool/image' );
		foreach ( $rows as $k => $row ) {
			if ( isset( $row['comment_customer_id'] ) && $row['comment_customer_id'] ) {
				$image = $this->getUserImage( $row['comment_customer_id'] );
				if ( ! $image ) {
					$image = 'no_image.png';
				}
				$width = $this->config->get( 'pavoblog_comment_avatar_width' ) ? $this->config->get( 'pavoblog_comment_avatar_width' ) : 54;
				$height = $this->config->get( 'pavoblog_comment_avatar_height' ) ? $this->config->get( 'pavoblog_comment_avatar_height' ) : 54;
				$row['thumb'] = $this->model_tool_image->resize( $image, $width, $height );
			}
			$results[ $row['comment_id'] ] = $row;
		}
		return $results;
	}

	/**
	 * get single comment
	 */
	public function getComment( $comment_id = null ) {
		$sql = "SELECT * FROM " . DB_PREFIX . "pavoblog_comment As comm WHERE comm.comment_id = " . (int)$comment_id;
		$query = $this->db->query( $sql );
		return $query->row;
	}

	/*get latest comments by posts*/
    public function getLatestComment( $data = array() )
    {
        $data = array_merge(array(
            'limit' => 2,
            'start' => 0,
            'orderby' => 'date_added',
            'order' => 'DESC',
        ), $data);

        $sql = "SELECT * FROM " . DB_PREFIX . "pavoblog_comment As comm";
        if ($data['orderby']) {
            $sql .= " ORDER BY comm." . $data['orderby'];
        }
        if ($data['order']) {
            $sql .= " " . $data['order'];
        }
        if ($data['start'] !== '' && $data['limit'] !== '') {
            $sql .= " LIMIT {$data['start']}, {$data['limit']}";
        }
        $query = $this->db->query($sql);
        return $query->rows;
    }

	/**
	 * add new comment
	 *
	 * @param $data array( 'comment_title', 'comment_name', 'comment_email', 'comment_text', 'comment_user_id' )
	 * @return comment_id
	 */
	public function addComment( $data = array() ) {
		$data = array_merge( array(
			'comment_title'			=> '',
			'comment_name'			=> '',
			'comment_email'			=> '',
			'comment_text'			=> '',
			'comment_post_id'		=> '',
//			'comment_user_id'		=> '',
			'comment_parent_id'		=> 0,
			'comment_customer_id'	=> '',
			'comment_status'		=> 0,
			'comment_store_id'		=> $this->config->get( 'config_store_id' ),
			'comment_language_id'	=> $this->config->get( 'config_language_id' ),
			'comment_subscribe'		=> 0,
			'post_subscribe'		=> 0
		), $data );

		if ( $this->config->get( 'pavoblog_auto_approve_comment' ) ) {
			$data['comment_status'] = 1;
		}

		//$data['comment_user_id'] = $this->user->getId() ? $this->user->getId() : 0;
		$data['comment_customer_id'] = $this->customer->getId() ? $this->customer->getId() : 0;
		if ( ! $data['comment_name'] ) {
//			$data['comment_name'] = $this->user->getId() ? $this->user->getUserName() : '';
			$data['comment_name'] = ! $data['comment_name'] && $this->customer->getId() ? $this->customer->getFirstName() . ' ' . $this->customer->getLastName() : $data['comment_name'];
		}

		if ( ! $data['comment_email'] ) {
//			$data['comment_email'] = $this->user->getId() ? $this->getUserEmail( $this->user->getId() ) : '';
			$data['comment_email'] = ! $data['comment_email'] && $this->customer->getId() ? $this->customer->getEmail() : $data['comment_email'];
		}

		extract( $data );
		$sql = "INSERT INTO " . DB_PREFIX . "pavoblog_comment ( `comment_title`, `comment_name`, `comment_email`, `comment_text`, `comment_post_id`, `comment_parent_id`, `comment_customer_id`, `comment_status`, `comment_subscribe`, `comment_store_id`, `comment_language_id`, `date_added`, `date_modified` )";
		$sql .= " VALUES( '".$this->db->escape( $data['comment_title'] )."', '".$this->db->escape( $data['comment_name'] )."', '".$this->db->escape( $data['comment_email'] )."', '".$this->db->escape( $data['comment_text'] )."', ".(int)$data['comment_post_id'].", ".(int)$data['comment_parent_id'].", ".(int)$data['comment_customer_id'].", ".(int)$data['comment_status'].", ".(int)$data['comment_subscribe'].", ".(int)$data['comment_store_id'].", ".(int)$data['comment_language_id'].", NOW(), NOW() )";

		// excute query
		$this->db->query( $sql );
		$comment_id = $this->db->getLastId();

		// subscribe post
		if ( $comment_id && $data['post_subscribe'] && $data['comment_email'] ) {
			$sql = "SELECT * FROM " . DB_PREFIX . "pavoblog_subscribe_post WHERE subscribe_email = '".$this->db->escape( $data['comment_email'] )."'";
			$query = $this->db->query( $sql );

			if ( ! $query->row ) {
				$sql = "INSERT INTO " . DB_PREFIX . "pavoblog_subscribe_post ( `subscribe_email` ) VALUES( '".$this->db->escape( $data['comment_email'] )."' )";
				// excute insert subscribe post
				$this->db->query( $sql );
			}
		}

		return $comment_id;
	}

	/**
	 * update comment
	 *
	 * @param $data array( 'comment_title', 'comment_name', 'comment_email', 'comment_text', 'user_id' )
	 * @return comment_id
	 */
	public function updateComment( $data = array() ) {
		$data = array_merge( array(
			'comment_id'			=> '',
			'comment_title'			=> '',
			'comment_name'			=> '',
			'comment_email'			=> '',
			'comment_text'			=> '',
			'comment_post_id'		=> '',
//			'comment_user_id'		=> '',
			'comment_parent_id'		=> 0,
			'comment_customer_id'	=> '',
			'comment_status'		=> 0,
			'comment_subscribe'		=> 0,
			'comment_store_id'		=> $this->config->get( 'config_store_id' ),
			'post_subscribe'		=> 0
		), $data );

		// comment status
		$data['comment_status'] = (int)$this->config->get( 'pavoblog_auto_approve_comment' );

//		$data['comment_user_id'] = $this->user->getId() ? $this->user->getId() : 0;
		$data['comment_customer_id'] = $this->customer->getId() ? $this->customer->getId() : 0;
		if ( ! $data['comment_name'] ) {
//			$data['comment_name'] = $this->user->getId() ? $this->user->getUserName() : '';
			$data['comment_name'] = ! $data['comment_name'] && $this->customer->getId() ? $this->customer->getFirstName() . ' ' . $this->customer->getLastName() : '';
		}

		if ( ! $data['comment_email'] ) {
//			$data['comment_email'] = $this->customer->getId() ? $this->getUserEmail( $this->user->getId() ) : '';
			$data['comment_email'] = ! $data['comment_email'] && $this->customer->getId() ? $this->customer->getEmail() : '';
		}

		extract( $data );

		if ( ! $data['comment_id'] ) return;

		// delete subscribe
		if ( ! $data['comment_subscribe'] && $data['comment_email'] ) {
			$sql = "DELETE * FROM " . DB_PREFIX . "pavoblog_subscribe_post WHERE subscribe_email = '".$this->db->escape( $data['comment_email'] )."'";
			$this->db->query( $sql );
		}

		// update comment
		$sql = "UPDATE " . DB_PREFIX . "pavoblog_comment SET `comment_name` = '".$this->db->escape( $data['comment_name'] )."', `comment_email` = '".$this->db->escape($data['comment_email'])."', `comment_title` = '".$this->db->escape($data['comment_title'])."', `comment_subscribe` = ".(int)$data['comment_subscribe'].", `comment_text` = '".$this->db->escape($data['comemnt_text'])."'";

		$sql .= " WHERE `comment_id` = " . (int)$data['comment_id'];
		$this->db->query( $sql );
		// affected rows
		return $this->db->countAffected();
	}

	/**
	 * delete comment
	 *
	 * @param $data array( 'comment_title', 'comment_name', 'comment_email', 'comment_text', 'user_id' )
	 * @return comment_id
	 */
	public function deleteComment( $comment_id = null ) {
		$sql = "DELETE * FROM " . DB_PREFIX . "pavoblog_subscribe_post WHERE subscribe_comment_id = " . (int)$comment_id;
		// delete subscribe
		$query = $this->db->query( $sql );

		$sql = "DELETE * FROM " . DB_PREFIX . "pavoblog_comment WHERE comment_id = " . (int)$comment_id;
		$query = $this->db->query( $sql );

		// return affected_rows
		return $this->db->countAffected();
	}

	/**
	 * get user's email by user_id
	 * @param $user_id
	 * @return email string
	 */
	public function getUserEmail( $user_id = null ) {
		$query = $this->db->query( "SELECT email FROM " . DB_PREFIX . "user WHERE user_id = " . (int)$user_id );
		return ! empty( $query->row['email'] ) ? $query->row['email'] : '';
	}

	/**
	 * get user image
	 */
	public function getUserImage( $user_id = null ) {
		$query = $this->db->query( "SELECT image FROM " . DB_PREFIX . "user WHERE user_id = " . (int)$user_id );
		return ! empty( $query->row['image'] ) ? $query->row['image'] : '';
	}

	/**
	 * get email subcribed
	 */
	public function getEmailSubcribedPost( $post_id = null ) {
		$sql = "SELECT DISTINCT comm.comment_post_id, comm.comment_email, comm.comment_language_id, comm.comment_store_id, desct.name FROM " . DB_PREFIX . "pavoblog_comment as comm";
		$sql .= " LEFT JOIN " . DB_PREFIX . "pavoblog_post_description AS desct ON desct.post_id = comm.comment_post_id";
		$sql .= " WHERE comm.comment_post_id = " . (int) $post_id . " AND comm.comment_subscribe = 1";

		// excute query
		$query = $this->db->query( $sql );
		
		return $query->rows;
	}

}