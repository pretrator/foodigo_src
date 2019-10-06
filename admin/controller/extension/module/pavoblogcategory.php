<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/
class ControllerExtensionModulePavoblogcategory extends Controller {
    private $error = array();
    private $data ;
    public function index() {
        $this->load->language('extension/module/pavoblogcategory');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/module');


        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('pavoblogcategory', $this->request->post);
                $this->response->redirect($this->url->link('extension/module/pavoblogcategory', 'user_token=' . $this->session->data['user_token'], 'SSL'));
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
                $this->response->redirect($this->url->link('extension/module/pavoblogcategory', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL'));
            }

            $this->session->data['success'] = $this->language->get('text_success');

        }

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        // BREADCRUMBS
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL'),
            'separator' => ' :: '
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/pavoblogcategory', 'user_token=' . $this->session->data['user_token'], 'SSL'),
            'separator' => ' :: '
        );

        // DATA

        if (!isset($this->request->get['module_id'])) {
            $this->data['action'] = $this->url->link('extension/module/pavoblogcategory', 'user_token=' . $this->session->data['user_token'], 'SSL');
        } else {
            $this->data['action'] = $this->url->link('extension/module/pavoblogcategory', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
        }
        $this->data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL');

        // GET DATA SETTING
        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }

        if (isset($this->request->post['name'])) {
            $this->data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $this->data['name'] = $module_info['name'];
        } else {
            $this->data['name'] = 'Module Name';
        }

        // STATUS
        if (isset($this->request->post['status'])) {
            $this->data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $this->data['status'] = $module_info['status'];
        } else {
            $this->data['status'] = 1;
        }

        $this->data['user_token'] = $this->session->data['user_token'];
        // RENDER
        $this->data['header'] = $this->load->controller('common/header');
        $this->data['column_left'] = $this->load->controller('common/column_left');
        $this->data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/pavoblogcategory', $this->data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/pavolatestcomment')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}
