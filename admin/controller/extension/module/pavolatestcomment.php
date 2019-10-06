<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/
class ControllerExtensionModulePavolatestcomment extends Controller {
    private $error = array();

    private $data;

    public function index() {

        $this->language->load('extension/module/pavolatestcomment');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/module');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('pavolatestcomment', $this->request->post);
                $this->response->redirect($this->url->link('extension/module/pavolatestcomment', 'user_token=' . $this->session->data['user_token'], 'SSL'));
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
                $this->response->redirect($this->url->link('extension/module/pavolatestcomment', 'user_token=' . $this->session->data['user_token'].'&module_id='.$this->request->get['module_id'], 'SSL'));
            }
            $this->session->data['success'] = $this->language->get('text_success');

        }

        //ALERT
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        //BREADCRUMBS

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL'),
        );

        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('extension/module/pavolatestcomment', 'user_token=' . $this->session->data['user_token'], 'SSL'),
        );

        //COMMON
        $data['user_token'] = $this->session->data['user_token'];

        // DATA
        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/pavolatestcomment', 'user_token=' . $this->session->data['user_token'], 'SSL');
        } else {
            $data['action'] = $this->url->link('extension/module/pavolatestcomment', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
        }
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL');

        // GET DATA SETTING
        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }

        // NAME
        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = 'Module Name';
        }
        
        if (isset($this->request->post['title'])) {
            $data['title'] = $this->request->post['title'];
        } elseif (!empty($module_info)) {
            $data['title'] = isset($module_info['title']) ? $module_info['title'] : '';
        } else {
            $data['title'] = 'This Title';
        }

        // STATUS
        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = 1;
        }


        // limit
        if (isset($this->request->post['limit'])) {
            $data['limit'] = $this->request->post['limit'];
        } elseif (!empty($module_info)) {
            $data['limit'] = $module_info['limit'];
        } else {
            $data['limit'] = '4';
        }

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        // RENDER
        $template = 'extension/module/pavolatestcomment';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view($template, $data));
    }


    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/pavolatestcomment')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}
?>
