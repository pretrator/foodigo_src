<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class ControllerExtensionPavoBlogComment extends Controller {

	private $data 	= array();
	private $errors = array();

	public function index() {
		if ( empty( $this->request->get['pavo_post_id'] ) ) {
			return;
		}

		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/comment' );

		$this->data['logged_in'] = $this->user && $this->user->getId() ? true : false;
		$post_id = (int)$this->request->get['pavo_post_id'];

		$this->data['comment_name'] = $this->data['comment_title'] = $this->request->post['comment_email'] = '';
		if ( isset( $this->session->data['comment_data'] ) && ! empty( $this->session->data['comment_data']['comment_title'] ) ) {
			$this->data['comment_title'] = $this->session->data['comment_data']['comment_title'];
		}

		if ( isset( $this->session->data['comment_data'] ) && ! empty( $this->session->data['comment_data']['comment_text'] ) ) {
			$this->data['comment_text'] = $this->session->data['comment_data']['comment_text'];
		}

		if ( isset( $this->session->data['comment_data'] ) && ! empty( $this->session->data['comment_data']['comment_name'] ) ) {
			$this->data['comment_name'] = $this->session->data['comment_data']['comment_name'];
		} else if ( $this->user && $this->user->getId() ) {
			$this->data['comment_name'] = $this->user->getUserName();
		} else if ( $this->customer && $this->customer->getId() ) {
			$this->data['comment_name'] = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
		}

		if ( isset( $this->session->data['comment_data'] ) && ! empty( $this->session->data['comment_data']['comment_email'] ) ) {
			$this->data['comment_email'] = $this->session->data['comment_data']['comment_email'];
		} else if ( $this->user && $this->user->getId() ) {
			$this->data['comment_email'] = $this->model_extension_pavoblog_comment->getUserEmail( $this->user->getId() );
		} else if ( $this->customer && $this->customer->getId() ) {
			$this->data['comment_email'] = $this->customer->getEmail();
		}

		if ( ! empty( $this->session->data['comment_errors'] ) ) {
			$this->data['errors'] = $this->session->data['comment_errors'];
			unset( $this->session->data['comment_errors'] );
		}

		if ( isset( $this->session->data['comment_data'] ) ) {
			unset( $this->session->data['comment_data'] );
		}

		// facebook comment
		$post_id = $this->request->get['pavo_post_id'];
		$this->data['facebook'] = $this->config->get( 'pavoblog_facebook' );
		$this->data['facebook_url'] = $this->config->get( 'pavoblog_facebook_url' ).'index.php?route=extension/pavoblog/single&pavo_post_id='.$post_id;
		$this->data['facebook_posts'] = $this->config->get( 'pavoblog_facebook_posts' );
		$this->data['facebook_width'] = $this->config->get( 'pavoblog_facebook_width' );
		$this->data['facebook_language'] = $this->config->get( 'pavoblog_facebook_language' );
		$this->data['facebook_app_id'] = $this->config->get( 'pavoblog_facebook_appid' );

		//end facebook comment

		$this->data['date_format'] = $this->config->get( 'pavoblog_date_format' ) ? $this->config->get( 'pavoblog_date_format' ) : 'Y-m-d';
		$this->data['time_format'] = $this->config->get( 'pavoblog_time_format' ) ? $this->config->get( 'pavoblog_time_format' ) : '';
		$this->data['can_reply'] = $this->config->get( 'pavoblog_reply' );
		// comments
		$this->data['comments'] = $this->model_extension_pavoblog_comment->getComments( $post_id );
		foreach ( $this->data['comments'] as $comment ) {
			if ( $comment['comment_status'] == 1 ){
				$this->data['comment_count'] = count( $this->data['comments'] );
			}else{
				$this->data['comment_count'] = 0;
			}
		}

		$this->data['comment_action'] = $this->url->link( 'extension/pavoblog/comment/addComment', '' );
		$this->data['post_id'] = $post_id;
		$this->data['comment_store_id']	= $this->config->get( 'config_store_id' );
		$this->data['language_id']	= $this->config->get( 'config_language_id' );
		$captcha = $this->config->get( 'pavoblog_comment_captcha' );
		$captcha_config = $this->config->get( 'config_captcha' );
		$this->data['captcha'] = $captcha ? $this->load->controller( 'extension/captcha/' . $captcha_config ) : '';
		return $this->load->view( 'pavoblog/single/comments', $this->data );
	}

	/**
	 * add comment
	 */
	public function addComment() {
		// redirect if has error
		if ( empty( $this->request->post['comment_post_id'] ) ) {
			$this->response->redirect( str_replace( '&amp;', '&', $this->url->link('error', '') ) ); exit();
		}

		$this->load->language( 'extension/module/pavoblog' );
		$this->load->model( 'extension/pavoblog/comment' );
		if ( $this->validate() ) {

			$comment_id = $this->model_extension_pavoblog_comment->addComment( $this->request->post );

			$approve_now = $this->config->get( 'pavoblog_auto_approve_comment' );
			if ( $comment_id && $approve_now ) {
				$post_id = isset( $this->request->post['comment_post_id'] ) ? (int)$this->request->post['comment_post_id'] : 0;
				$comment = $this->model_extension_pavoblog_comment->getComment( 28 );
				$email = !empty( $comment['comment_email'] ) ? $comment['comment_email'] : '';
				// send email to users subcribed before
				$this->sendEmailSubcribed( $post_id, array( $email ) );
			}

			$this->response->redirect( str_replace( '&amp;', '&', $this->url->link( 'extension/pavoblog/single', 'pavo_post_id=' . (int)$this->request->post['comment_post_id'] ) ) );
		} else {
			$this->session->data['comment_errors'] = $this->errors;
			$this->session->data['comment_data'] = $this->request->post;
		}

		$this->response->redirect( str_replace( '&amp;', '&', $this->url->link( 'extension/pavoblog/single', 'pavo_post_id=' . (int)$this->request->post['comment_post_id'] ) ) ); exit();
	}

	/**
	 * send email subcribed
	 */
	private function sendEmailSubcribed( $post_id = false, $ignores = array() ) {
		$this->load->model( 'extension/pavoblog/comment' );

		$subcribes = $this->model_extension_pavoblog_comment->getEmailSubcribedPost( $post_id );

		$this->load->model('localisation/language');
		$this->load->model('setting/store');

		if ( $subcribes ) foreach ( $subcribes as $subcribe ) {
			// ignores email
			if ( in_array( $subcribe['comment_email'], $ignores ) ) continue;

			$data = array();
			$href = $this->url->link( 'extension/pavoblog/single', 'pavo_post_id=' . $subcribe['comment_post_id'] );
			$post_name = ! empty( $subcribe['name'] ) ? html_entity_decode( $subcribe['name'], ENT_QUOTES, 'UTF-8' ) : '';
			$store_info = $this->model_setting_store->getStore($subcribe['comment_store_id']);

			if ( $store_info ) {
				$store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');
			} else {
				$store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
			}

			$language_id = $subcribe['comment_language_id'] ? $subcribe['comment_language_id'] : 1;
			$language_info = $this->model_localisation_language->getLanguage( $language_id );

			if ( $language_info ) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			$language = new Language( $language_code );
			$language->load( $language_code );
			$language->load( 'mail/pavoblog_new_comment' );

			$subject = sprintf( $language->get('text_subject'), $store_name );

			// data for email
			$data['text_dear'] = sprintf( $language->get( 'text_dear' ), $store_name );
			$data['text_store'] = $store_name;
			$data['text_message'] = sprintf( $language->get( 'text_message' ), $post_name );
			$data['text_link'] = sprintf( $language->get( 'text_link' ), $href );
			$data['text_thanks'] = $language->get( 'text_thanks' );

			$mail = new Mail( $this->config->get( 'config_mail_engine' ) );
			$mail->parameter = $this->config->get( 'config_mail_parameter' );
			$mail->smtp_hostname = $this->config->get( 'config_mail_smtp_hostname' );
			$mail->smtp_username = $this->config->get( 'config_mail_smtp_username' );
			$mail->smtp_password = html_entity_decode( $this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8' );
			$mail->smtp_port = $this->config->get( 'config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get( 'config_mail_smtp_timeout');

			$mail->setTo( $subcribe['comment_email'] );
			$mail->setFrom( $this->config->get('config_email') );
			$mail->setSender( $store_name );
			$mail->setSubject( $subject );
			$mail->setText( $this->load->view( 'mail/pavoblog_new_comment', $data ) );
			$mail->send();
		}
	}

	/**
	 * validate comment post data
	 */
	public function validate() {
		// Captcha
		$captcha = $this->config->get( 'pavoblog_comment_captcha' );
		if ( $captcha && $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page')) ) {
			$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');
			if ( $captcha ) {
				$this->errors['captcha'] = $captcha;
			}
		}
		if ( empty( $this->request->post['comment_text'] ) ) {
			$this->errors['comment_text'] = $this->language->get( 'text_error_comment_text' );
		}

		if ( empty( $this->request->post['comment_name'] ) ) {
			$this->errors['comment_name'] = $this->language->get( 'text_error_comment_name' );
		}

		if ( empty( $this->request->post['comment_email'] ) ) {
			$this->errors['comment_email'] = $this->language->get( 'text_error_comment_email' );
		}

		if ( $this->errors ) {
			$this->errors['comment_warning'] = $this->language->get( 'text_error_required_field' );
		}

		return ! $this->errors;
	}

}