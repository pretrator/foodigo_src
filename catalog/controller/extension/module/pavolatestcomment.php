<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/
class ControllerExtensionModulePavolatestcomment extends Controller {
    private $data;
    public function index($setting) {
        $this->load->language('extension/module/pavolatestcomment');
        $this->load->model('extension/pavoblog/comment');
        $this->load->model('extension/pavoblog/post');
        
        if( isset($setting['title'][$this->config->get('config_language_id')]) ) {
            $this->data['title'] = html_entity_decode($setting['title'][$this->config->get('config_language_id')], ENT_QUOTES, 'UTF-8');
        }else {
            $this->data['title'] = '';
        }

        $setting = array(
            $this->data['limit'] = $setting['limit'],
            $this->data['name'] = $setting['name']
        );
        $filter_data = array(
            'limit'			=> isset($setting['limit'])?$setting['limit']:5,
            'start'	        => 0,
        );
        $get_comment = array();
        $comments = $this->model_extension_pavoblog_comment->getLatestComment($filter_data);
        
        foreach ($comments as $comment) {
            if( $comment['comment_status'] == 1 ){
                $get_comment[] = array(
                'comment_name'  => $comment['comment_name'],
                'comment_text'  => html_entity_decode($comment['comment_text'], ENT_QUOTES, 'UTF-8'),
                'link'  =>  $this->url->link( 'extension/pavoblog/single','pavo_post_id='.$comment['comment_post_id'] )
                );
            }
        }

        $this->data['get_comment'] = $get_comment;

        return $this->load->view('extension/module/pavolatestcomment', $this->data);
    }
}