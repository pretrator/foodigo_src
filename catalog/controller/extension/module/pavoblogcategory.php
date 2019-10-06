<?php
/**
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license GNU General Public License version 2
 */
class ControllerExtensionModulePavoblogcategory extends Controller {
    private $data;
    public function index($setting) {
        $this->load->language('extension/module/pavoblogcategory');
        $this->load->model('extension/pavoblog/category');
        $this->load->model('extension/pavoblog/post');

        $categorys  = array();
        $category   = array();
        $get_category = array();
        $categories = $this->model_extension_pavoblog_category->getCategories();
        $posts      = $this->model_extension_pavoblog_post->getPosts();

        $setting    = array(
            $this->data['name'] = $setting['name']
            );
        foreach ($posts as $post_id) {
            $category_id = $this->model_extension_pavoblog_post->getCategories($post_id['post_id']);
                foreach ($category_id as $cid) {
                $get_category[] = array(
                    'category_id' => $cid['category_id']
                );
            }
        }
             
        foreach ($categories as $cat_id) {
            if ($cat_id['parent_id'] == 0) {
                $category[] = array(
                    'name'          => $cat_id['name'],
                    'category_id'   => $cat_id['category_id'],
                    'parent_id'     => $cat_id['parent_id'],
                    'link'  => $this->url->link( 'extension/pavoblog/archive','pavo_cat_id='.$cat_id['category_id'] )
                );
            }
            foreach($categories as $cat) {
                if ($cat['parent_id'] == $cat_id['category_id']) {
                    $categorys[] = array(
                        'name'          => $cat['name'],
                        'category_id'   => $cat['category_id'],
                        'parent_id'     => $cat['parent_id'],
                        'link'          => $this->url->link( 'extension/pavoblog/archive','pavo_cat_id='.$cat['category_id'])
                    );
                }

            }
        }
        $this->data['category']  = $category;
        $this->data['categorys'] = $categorys;
        $this->data['get_category'] = $get_category;
        $this->data['get_language'] = $this->language->get('direction');
        

        return $this->load->view('extension/module/pavoblogcategory', $this->data);
    }
}