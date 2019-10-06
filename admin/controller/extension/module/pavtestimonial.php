<?php
/******************************************************
 * @package Pavo Store Locator Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/

class ControllerExtensionModulePavtestimonial extends Controller
{
    private $error = array();

    private $data = array();

    public function index()
    {
        $this->load->language('extension/module/pavtestimonial');
        $this->load->model('setting/module');
        $this->load->model('setting/extension');
        $this->load->model('tool/image');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');


        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $action = $this->request->post['action_mode'];
            unset($this->request->post['action_mode']);


            $this->request->post['pavtestimonial_module'][0]['testimonial_item'] = $this->request->post['testimonial_item'];
            unset($this->request->post['testimonial_item']);


            $data = array();
            foreach ($this->request->post['pavtestimonial_module'] as $key => $value) {
                $data = $value;
                break;
            }

            if (!isset($this->request->get['module_id'])) {
               $this->request->get['module_id'] = $this->model_setting_module->addModule('pavtestimonial', $data);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $data);
            }


            $this->session->data['success'] = $this->language->get('text_success');
            if (isset($this->request->get['module_id'])) {
                $this->response->redirect($this->url->link('extension/module/pavtestimonial', 'module_id=' . $this->request->get['module_id'] . '&user_token=' . $this->session->data['user_token'], 'SSL'));
            } else {
                $this->response->redirect($this->url->link('extension/module/pavtestimonial', 'user_token=' . $this->session->data['user_token'], 'SSL'));
            }
        }


        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL'),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/pavtestimonial', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => ' :: '
        );


        $data['action'] = $this->url->link('extension/module/pavtestimonial', 'user_token=' . $this->session->data['user_token'], 'SSL');

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL');

        $data['user_token'] = $this->session->data['user_token'];

        // status
        if (isset($this->request->post['pavtestimonial_status'])) {
            $data['pavtestimonial_status'] = $this->request->post['pavtestimonial_status'];
        } else {
            $data['pavtestimonial_status'] = $this->config->get('pavtestimonial_status');
        }

        $d = array(
            'layout_id' => '',
            'position' => '',
            'sort_order' => '1',
            'testimonial_item' => array(),
            'width' => 300,
            'height' => 300,
            'image_navigator' => 0,
            'navimg_height' => 97,
            'navimg_weight' => 177,
            'text_interval' => 8000,
            'column_item' => '1',
            'page_items' => 2,
            'loop' =>1,
            'slides_view' => 1,
            'name' => '',
            'status' => 1,
            'class' => '',
            'auto_play' => 1
        );


        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $d = array_merge($d, $this->model_setting_module->getModule($this->request->get['module_id']));
            $data['selectedid'] = $this->request->get['module_id'];


            $data['subheading'] = $this->language->get('text_edit_module') . $d['name'];
            $data['action'] = $this->url->link('extension/module/pavtestimonial', 'module_id=' . $this->request->get['module_id'] . '&user_token=' . $this->session->data['user_token'], 'SSL');
        } else {
            $data['selectedid'] = 0;
            $data['subheading'] = $this->language->get('text_create_new_module');
            $data['action'] = $this->url->link('extension/module/pavtestimonial', 'user_token=' . $this->session->data['user_token'], 'SSL');
        }


        $data['module'] = $d;

        $testimonial_item = $d['testimonial_item'];

        if ($testimonial_item) {
            $tmp = array();
            foreach ($testimonial_item as $key => $banner) {
                $tmp[] = array(
                    'video_link'    => isset($banner['video_link']) ? trim($banner['video_link']) : "",
                    'layout'        => isset($banner['layout']) ? trim($banner['layout']) : "layout1",
                    'image'         => isset($banner['image']) ? $banner['image'] : "",
                    'thumb'         => $this->model_tool_image->resize($banner['image'], 100, 100),
                    'profile'       => isset($banner['profile']) ? ($banner['profile']) : "",
                    'load_css'      => isset($banner['load_css']) ? ($banner['load_css']) : 1,
                    'description'   => isset($banner['description']) ? ($banner['description']) : ""
                );
            }

            $data['module']['testimonial_item'] = $tmp;
        }


        $this->load->model('design/layout');
        $data['layout_styles'] = array(
            'layout1' => $this->language->get('text_layout_style_1'),
            'layout2' => $this->language->get('text_layout_style_2'),
        );

        $data['yesno'] = array(
            0 => $this->language->get('text_yes'),
            1 => $this->language->get('text_no'),
        );

    //    $data['layouts'][] = array('layout_id' => 99999, 'name' => $this->language->get('all_page'));

    //    $data['layouts'] = array_merge($data['layouts'], $this->model_design_layout->getLayouts());



        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);


        $data['header']         = $this->load->controller('common/header');
        $data['column_left']    = $this->load->controller('common/column_left');
        $data['footer']         = $this->load->controller('common/footer');



        $this->response->setOutput($this->load->view('extension/module/pavtestimonial', $data));
    }


    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/pavtestimonial')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!isset($this->request->post['testimonial_item'])) {
            $this->error['warning'] = $this->language->get('error_missing_banner');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}

