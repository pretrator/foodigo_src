<?php
class ControllerExtensionModulePavproductcarousel extends Controller {
	private $error = array();
	private $data;

	public function index() {

		$this->language->load('extension/module/pavproductcarousel');
		$this->load->model('localisation/language');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addScript('view/javascript/summernote/summernote.js');
		$this->document->addStyle('view/javascript/summernote/summernote.css');
		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('pavproductcarousel', $this->request->post);
				$this->response->redirect($this->url->link('extension/module/pavproductcarousel', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
				$this->response->redirect($this->url->link('extension/module/pavproductcarousel', 'user_token=' . $this->session->data['user_token'].'&module_id='.$this->request->get['module_id'], 'SSL'));
			}

			$this->session->data['success'] = $this->language->get('text_success');
		}

		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL'),
      		'separator' => ' :: '
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/pavproductcarousel', 'user_token=' . $this->session->data['user_token'], 'SSL'),
      		'separator' => ' :: '
   		);

 		// DATA

 		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/pavproductcarousel', 'user_token=' . $this->session->data['user_token'], 'SSL');
		} else {
			$data['action'] = $this->url->link('extension/module/pavproductcarousel', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
		}
		$data['cancel'] 	  = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL');
		
		// GET DATA setting
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		// products
		$this->load->model('catalog/product');
		$data['products'] = array();
		if (isset($this->request->post['product'])) {
			$products = $this->request->post['product'];
		} elseif (!empty($module_info['product'])) {
			$products = isset($module_info['product'])?$module_info['product']:array();
		} else {
			$products = array();
		}
		if (!empty($products)) {
			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);
				if ($product_info) {
					$data['products'][] = array(
						'product_id' => $product_info['product_id'],
						'name'       => isset($product_info['name']) ? $product_info['name'] : ''
					);
				}
			}
		} else {
			$data['products'] = array();
		}
		
		$data['user_token'] = $this->session->data['user_token'];

		// NAME
		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = 'Module Name';
		}

		// STATUS
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = 1;
		}

		// LOOP

		if (isset($this->request->post['loop'])) {
			$data['loop'] = $this->request->post['loop'];
		} elseif (!empty($module_info)) {
			$data['loop'] = $module_info['loop'];
		} else {
			$data['loop'] = 1;
		}

		// AUTOPLAY

		if (isset($this->request->post['auto_play'])) {
			$data['auto_play'] = $this->request->post['auto_play'];
		} elseif (!empty($module_info)) {
			$data['auto_play'] = $module_info['auto_play'];
		} else {
			$data['auto_play'] = 1;
		}

		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($module_info)) {
			$data['title'] = isset($module_info['title'])?$module_info['title']:"";
		} else {
			$data['title'] = '';
		}

		// DESCRIPTION
		if (isset($this->request->post['description'])) {
			$data['description'] = $this->request->post['description'];
		} elseif (!empty($module_info)) {
			$data['description'] = $module_info['description'];
		} else {
			$data['description'] = '';
		}

		// CLASS
		if (isset($this->request->post['prefix'])) {
			$data['prefix'] = $this->request->post['prefix'];
		} elseif (!empty($module_info)) {
			$data['prefix'] = $module_info['prefix'];
		} else {
			$data['prefix'] = 'prefix';
		}
		
		// FONTAWESOME
		if (isset($this->request->post['fontawesome'])) {
			$data['fontawesome'] = $this->request->post['fontawesome'];
		} elseif (!empty($module_info)) {
			$data['fontawesome'] = isset($module_info['fontawesome']) ? $module_info['fontawesome'] : '';
		} else {
			$data['fontawesome'] = 'fontawesome';
		}

		// CLASS
		if (isset($this->request->post['tabs'])) {
			$data['tabs'] = $this->request->post['tabs'];
		} elseif (!empty($module_info)) {
			$data['tabs'] = $module_info['tabs'];
		} else {
			$data['tabs'] = array( 1 =>'latest');
		}

		// width
		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($module_info)) {
			$data['width'] = $module_info['width'];
		} else {
			$data['width'] = '600';
		}
		
		// height
		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($module_info)) {
			$data['height'] = $module_info['height'];
		} else {
			$data['height'] = '666';
		}

		// itemsperpage
		if (isset($this->request->post['itemsperpage'])) {
			$data['itemsperpage'] = $this->request->post['itemsperpage'];
		} elseif (!empty($module_info)) {
			$data['itemsperpage'] = $module_info['itemsperpage'];
		} else {
			$data['itemsperpage'] = '4';
		}

		// cols
		if (isset($this->request->post['cols'])) {
			$data['cols'] = $this->request->post['cols'];
		} elseif (!empty($module_info)) {
			$data['cols'] = $module_info['cols'];
		} else {
			$data['cols'] = '1';
		}

		// limit
		if (isset($this->request->post['limit'])) {
			$data['limit'] = $this->request->post['limit'];
		} elseif (!empty($module_info)) {
			$data['limit'] = $module_info['limit'];
		} else {
			$data['limit'] = '4';
		}


		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		$data['yesno'] = array(
			0=>$this->language->get('text_no'),
			1=>$this->language->get('text_yes')
		);

		$tmptabs = array(
			'featured' => $this->language->get('text_featured'),
			'latest' 	 => $this->language->get('text_latest'),
			'bestseller' => $this->language->get('text_bestseller'),
			'special'   => $this->language->get('text_special'),
			'mostviewed' => $this->language->get('text_mostviewed'),
			'toprating' => $this->language->get('text_toprating'),
		);
		$data['tmptabs'] = $tmptabs;	
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		// RENDER
		$template = 'extension/module/pavproductcarousel';
		$data['header'] 		= $this->load->controller('common/header');
		$data['column_left'] 	= $this->load->controller('common/column_left');
		$data['footer'] 		= $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view($template, $data));
}
	public function redirect( $url ){
		return $this->response->redirect( $url );
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/pavproductcarousel')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>
