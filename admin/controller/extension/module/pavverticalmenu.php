<?php
/******************************************************
 * @package Pav Verticalmenu module for Opencart 3.x
 * @version 2.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Steptember 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
//require_once(DIR_SYSTEM . 'pavothemes/loader.php');
/**
 * class ControllerExtensionModulePavverticalmenu
 */
class ControllerExtensionModulePavverticalmenu extends Controller {

	/**
	 * @var Array $error.
	 *
	 * @access private
	 */
	private $error = array();

	/**
	 * @var Array $error.
	 *
	 * @access private
	 */
	private $moduleName = 'pavverticalmenu';

	public $mdata;

	public $types = array();

	/**
	 * Index Action
	 */
	public function __construct($registry) {
		$this->registry = $registry;
		// check tables created or not
		$this->load->model('extension/menu/verticalmenu');
		$this->language->load('extension/module/pavverticalmenu');

		$this->types = array(
			'url' 	 	   => $this->language->get('text_url'),
			'category' 	   => $this->language->get('text_category'),
			'information'  => $this->language->get('text_information'),
			'product' 	   => $this->language->get('text_product'),
			'manufacturer' => $this->language->get('text_manufacturer'),
			'html'  	   => $this->language->get('text_html')
		);
	}

	public function isEnableExtension( $module ){
		return file_exists( DIR_APPLICATION.'controller/extension/module/'.$module.'.php' );
	}

	public function install() {
		$this->model_extension_menu_verticalmenu->install();
	}

	public function index() {

		$this->document->setTitle( strip_tags($this->language->get('heading_title')) );

		$this->document->addStyle('view/stylesheet/pavverticalmenu.css');
		$this->document->addScript('view/javascript/pavverticalmenu/jquerycookie.js');
		$this->document->addStyle('view/javascript/jquery/jquery-ui/jquery-ui.min.css');
		$this->document->addScript('view/javascript/jquery/jquery-ui/jquery-ui.min.js');
		$this->document->addScript('view/javascript/summernote/summernote.js');
		$this->document->addStyle('view/javascript/summernote/summernote.css');
		$this->document->addScript('view/javascript/pavverticalmenu/jquery.nestable.js');

		// check tables created or not
		$this->load->model('setting/setting');
		$this->load->model('tool/image');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')  && !empty($this->request->post) ) {
			if (!$this->user->hasPermission('modify', 'extension/module/'.$this->moduleName)) {
				$this->error['warning'] = $this->language->get('error_permission');
			} else {
				$id = 0;
				$this->load->model('extension/menu/verticalmenu');
                $verticalmenu = $this->request->post['verticalmenu'];
                $store_param = isset($verticalmenu['store_id'])?'&store_id='.$verticalmenu['store_id']:'';

				if (  $this->validate() ) {
					$id = $this->model_extension_menu_verticalmenu->editData( $this->request->post );
				}
				$this->session->data['success'] = $this->language->get('text_success');

				if ($this->request->post['save_mode']=='delete-categories'){
					$this->model_extension_menu_verticalmenu->deletecategories($verticalmenu['store_id']);
				}

				if ($this->request->post['save_mode']=='import-categories'){
					$this->model_extension_menu_verticalmenu->importCategories($verticalmenu['store_id']);
				}

				if ( isset($id) && $this->request->post['save_mode']=='save-edit' ) {
                    $this->redirect($this->url->link('extension/module/pavverticalmenu', 'id='.$id.'&user_token=' . $this->session->data['user_token'].$store_param, true));
				} else {
					$this->redirect($this->url->link('extension/module/pavverticalmenu', 'user_token=' . $this->session->data['user_token'].$store_param, true));
				}

				$this->redirect($this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'].$store_param, true));
			}
		}

		//#2 edit code
		$this->load->model('setting/store');
		$action = array();

		$action[] = array(
			'text' => $this->language->get('text_edit'),
			'href' => $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'], true)
		);

		$store_default = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name') . $this->language->get('text_default'),
			'url'      => HTTP_CATALOG,
		);
		$stores = $this->model_setting_store->getStores();
		array_unshift($stores, $store_default);

		foreach ($stores as &$store) {
			$url = '';
			if ($store['store_id'] > 0 ) {
				$url = '&store_id='.$store['store_id'];
			}
			$store['option'] = $this->url->link('extension/extension/pavverticalmenu', $url.'&user_token=' . $this->session->data['user_token'].'&type=module', true);
		}
		$this->mdata['stores'] = $stores;

		if (isset($this->request->get['store_id'])) {
			$store_id = $this->request->get['store_id'];
			$store_param = "&store_id=".$store_id;
		} else {
			$store_id = 0;
			$store_param = "";
		}
		$this->mdata['store_id'] = $store_id;

 		if (isset($this->error['warning'])) {
			$this->mdata['error_warning'] = $this->error['warning'];
		} else {
			$this->mdata['error_warning'] = '';
		}

		if (isset($this->error['dimension'])) {
			$this->mdata['error_dimension'] = $this->error['dimension'];
		} else {
			$this->mdata['error_dimension'] = array();
		}

  		$this->mdata['breadcrumbs'] = array();

   		$this->mdata['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

   		$this->mdata['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', true),
      		'separator' => ' :: '
   		);

   		$this->mdata['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/pavverticalmenu', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => ' :: '
   		);

   		$actionDel  = $this->url->link('extension/module/pavverticalmenu/delete', 'user_token=' . $this->session->data['user_token'].$store_param, true);
   		$updateTree = $this->url->link('extension/module/pavverticalmenu/update', 'root=1'.$store_param.'&user_token=' . $this->session->data['user_token'], true);

		$this->mdata['action']        = $this->url->link('extension/module/pavverticalmenu', 'user_token=' . $this->session->data['user_token'].$store_param, true);
		$this->mdata['actionGetTree'] = $this->url->link('extension/module/pavverticalmenu/gettree', 'user_token=' . $this->session->data['user_token'].$store_param, true);
		$this->mdata['actionDel']     = str_replace("&amp;", "&", $actionDel);
		$this->mdata['actionGetInfo'] = $this->url->link('extension/module/pavverticalmenu/info', 'user_token=' . $this->session->data['user_token'].$store_param, true);
		$this->mdata['updateTree']    = str_replace("&amp;", "&", $updateTree);
		$this->mdata['liveedit_url']  = $this->url->link('extension/module/pavverticalmenu/liveedit', 'root=1'.$store_param.'&user_token=' . $this->session->data['user_token'], true);
		$this->mdata['cancel']        = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].$store_param.'&type=module', true);

		//get current language id
		$this->mdata['language_id'] = $this->config->get('config_language_id');

		$this->mdata['modules'] = array();

		if (isset($this->request->post['pavverticalmenu_module'])) {
			$this->mdata['modules'] = $this->request->post['pavverticalmenu_module'];
		} elseif ($this->config->get('pavverticalmenu_module')) {
			$this->mdata['modules'] = $this->config->get('pavverticalmenu_module');
		}
		$tmp = array('layout_id'=>'','position'=>'','status'=>'','sort_order'=>'');
		if( count($this->mdata['modules']) ){
			$tmp = array_merge($tmp, $this->mdata['modules'][0] );
		}
		$this->mdata['module'] = $tmp;
		$this->load->model('design/layout');

		$this->mdata['currentID'] = 0 ;
		if( isset($this->request->get['id'] ) ){
			$this->mdata['currentID'] = $this->request->get['id'];
		}


		$this->mdata['tree'] = $this->model_extension_menu_verticalmenu->getTree(null, $store_id, $this->mdata['currentID'] );

		$this->mdata['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$this->info();
		$this->mdata['layouts'] = array();
		$this->mdata['layouts'][] = array('layout_id'=>99999, 'name' => $this->language->get('all_page') );
		$this->mdata['layouts'] = array_merge($this->mdata['layouts'],$this->model_design_layout->getLayouts());

		$this->template = 'extension/module/pavverticalmenu/pavverticalmenu';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function render(){

		$this->mdata['header'] = $this->load->controller('common/header');
		$this->mdata['column_left'] = $this->load->controller('common/column_left');
		$this->mdata['footer'] = $this->load->controller('common/footer');

		$this->mdata['objlang'] = $this->language;
		$this->mdata['olang'] = $this->language;
		return $this->load->view( $this->template, $this->mdata) ;
	}

	public function redirect( $url ){
		return $this->response->redirect( $url );
	}

	protected function hasPermssion(){
		return $this->user->hasPermission( 'modify', 'extension/module/'.$this->moduleName );
	}
	/**
	 * Delete Mega Menu Action
	 */
	public function delete(){
		if (!$this->user->hasPermission('modify', 'extension/module/'.$this->moduleName)) {
			$this->error['warning'] = $this->language->get('error_permission');
			die(  $this->error['warning'] );
		}
		if( isset($this->request->get['id']) ){
			$this->load->model('extension/menu/verticalmenu');

			$store_id = isset($this->request->get['store_id'])?$this->request->get['store_id']:0;

			$store = ($store_id == 0)?'':'&store_id='.$store_id;

			$this->model_extension_menu_verticalmenu->delete( (int)$this->request->get['id'], $store_id );

		}
		$this->redirect($this->url->link('extension/module/pavverticalmenu', 'user_token=' . $this->session->data['user_token'].$store, true));
	}

	/**
	 * Update Action
	 */
	public function update(){
		if (!$this->user->hasPermission('modify', 'extension/module/'.$this->moduleName)) {
			$this->error['warning'] = $this->language->get('error_permission');
			die(  $this->error['warning'] );
		}
		$data =  ( ($this->request->post['list']) );
		$root = $this->request->get['root'];

		$this->load->model('extension/menu/verticalmenu');
		$this->model_extension_menu_verticalmenu->massUpdate( $data, $root  );
	}

	/**
	 * Infor Action to Get Mega menu information by id
	 */
	public function info(){
		$id=0;

		if( isset($this->request->post) && isset($this->request->post['id']) ){
			$id = (int)$this->request->post['id'] ;
		}else if( isset($this->request->get["id"]) ){
			$id = (int)$this->request->get['id'];
		}
		if (isset($this->request->get['store_id'])){
			$store_id = $this->request->get['store_id'];
			$store_param = "&store_id=".$store_id;
		} else {
			$store_id = 0;
			$store_param = "";
		}
		$default = array(
			'verticalmenu_id'=>'',
			'title' => '',
			'parent_id'=> '',
			'image' => '',
			'is_group'=>'',
			'width'=>'12',
			'menu_class'=>'',
			'submenu_colum_width'=>'',
			'is_group'=>'',
			'submenu_width'=>'12',
			'column_width'=>'200',
			'submenu_column_width'=>'',
			'colums'=>'1',
			'type' => '',
			'item' => '',
			'is_content'=>'',
			'show_title'=>'1',
			'type_submenu'=>'',
			'level_depth'=>'',
			'status'    => '',
			'position'  => '',
			'show_sub' => '',
			'url' => '',
			'targer' => '',
			'level'=> '',
			'content_text'=>'',
			'submenu_content'=>'',
			'published' => 1,
			'widget_id'=> 0,
			'badges' =>''
		);

		$this->language->load('extension/module/pavverticalmenu');
		$this->load->model('extension/menu/verticalmenu');
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('catalog/manufacturer');
		$this->load->model('catalog/information');
		$this->load->model('localisation/language');
		$this->load->model('tool/image');
		$this->mdata['no_image'] = $this->model_tool_image->resize('no_image.jpg', 16, 16);

		$this->mdata['entry_image'] = 'Image:';
		$this->mdata['text_image_manager'] = $this->language->get('text_image_manager');
		$this->mdata['text_clear'] = $this->language->get('text_clear');
		$this->mdata['text_browse'] = $this->language->get('text_browse');
		$this->mdata['tab_module'] = $this->language->get('tab_module');
		$this->mdata['text_none'] = $this->language->get('text_none');
		$this->mdata['yesno'] = array( '0' => $this->language->get('text_no'),'1'=> $this->language->get('text_yes') );
		$this->mdata['user_token'] = $this->session->data['user_token'];
		$this->mdata['languages'] = $this->model_localisation_language->getLanguages();
		$this->mdata['informations'] = $this->model_catalog_information->getInformations();

		$menu = $this->model_extension_menu_verticalmenu->getInfo( $id );
		$menu = array_merge( $default, $menu );

		$this->mdata['menu'] = $menu;
		$this->mdata['menus'] = $this->model_extension_menu_verticalmenu->getDropdown(null, $menu['parent_id'], $store_id );
		$this->mdata['thumb'] = $this->model_tool_image->resize($menu['image'], 32, 32);
		$this->mdata['menu_description'] = array();
		$descriptions  = $this->model_extension_menu_verticalmenu->getMenuDescription( $id );
		$this->mdata['menu_description'] = array();

		$this->mdata['verticalmenutypes'] = $this->types;

		if( $menu['item'] ){
			switch( $menu['type'] ){
				case 'category':
					$category = $this->model_catalog_category->getCategory( $menu['item'] );
					$menu['verticalmenu-category'] = isset($category['name'])?$category['name']:"";
					$menu['verticalmenu_category'] = $menu['verticalmenu-category'];
					break;
				case 'product':
					$product = $this->model_catalog_product->getProduct( $menu['item'] );
					$menu['verticalmenu-product'] = isset($product['name'])?$product['name']:"";
					$menu['verticalmenu_product'] = $menu['verticalmenu-product'];
					break;
				case 'information':
						$menu['verticalmenu-information'] = $menu['item'] ;
						$menu['verticalmenu_information'] = $menu['verticalmenu-information'];
					break;
				case 'manufacturer':
					$manufacturer = $this->model_catalog_manufacturer->getManufacturer( $menu['item'] );
					$menu['verticalmenu-manufacturer'] = isset($manufacturer['name'])?$manufacturer['name']:"";
					$menu['verticalmenu_manufacturer'] = $menu['verticalmenu-manufacturer'];
					break;
			}
		}
		foreach( $descriptions as $d ){
			$this->mdata['menu_description'][$d['language_id']] = $d;
		}

		if( empty($this->mdata['menu_description']) ){
			foreach(  $this->mdata['languages'] as $language ){
				$this->mdata['menu_description'][$language['language_id']]['title'] = '';
				$this->mdata['menu_description'][$language['language_id']]['description'] = '';
			}
		}

		if( isset($this->request->post['verticalmenu']) ){
			$menu = array_merge($menu, $this->request->post['verticalmenu'] );
		}


		$this->mdata['menu'] = $menu;
		$this->mdata['submenutypes'] = array('menu'=>'Menu', 'html'=>'HTML' );

		$this->mdata['width_aligns'] = array(
			'' => 'auto',
			'aligned-left' => 'Left',
			'aligned-right' => 'Right',
			'aligned-fullwidth' => 'FullWidth'
		);

		if( $this->isEnableExtension('pavobuilder') ){
			$this->mdata['submenutypes']['blkbuilder'] = $this->language->get('text_blockbuilder');
			$this->load->model( 'setting/module' );
			$this->mdata['blockbuilders'] 		  = $this->model_setting_module->getModulesByCode( 'pavobuilder' );
			$link =  $this->url->link( 'extension/module/pavobuilder', 'module_id=MODULE_ID&user_token=' . $this->session->data['user_token'], true );
			$this->mdata['blockbuilder_edit_url'] = $link;
		}

		$this->mdata['text_edit_menu'] = $this->language->get('text_edit_menu');
		$this->mdata['text_create_new'] = $this->language->get('text_create_new');
		$this->template = 'extension/module/pavverticalmenu/pavverticalmenu_form';
		$this->response->setOutput($this->render());

	}

 	/**
 	 * Check Validation
 	 */
	protected function validate() {

		if (!$this->user->hasPermission('modify', 'extension/module/pavverticalmenu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['pavverticalmenu_module'])) {

			foreach ($this->request->post['pavverticalmenu_module'] as $key => $value) {
				if (!$value['position'] || !$value['layout_id']) {
					$this->error['dimension'][$key] = $this->language->get('error_dimension');
				}
			}
			$languageId = (int)$this->config->get('config_language_id');
			$d = isset($this->request->post['verticalmenu_description'][$languageId]['title'])?$this->request->post['verticalmenu_description'][$languageId]['title']:"";
			if( empty($d) ){
				$this->error['missing_title'][]=$this->language->get('error_missing_title');
			}
			foreach ( $this->request->post['verticalmenu_description'] as $key => $value) {
				if( empty($value['title']) ){
					$this->request->post['verticalmenu_description'][$key]['title'] = $d;
				}

			}
			if( isset($this->error['missing_title']) ){
				$this->error['warning'] = implode( "<br>", $this->error['missing_title'] );
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Ajax Menu Information Action
	 */
	public function ajxmenuinfo(){
			$this->language->load('extension/module/pavverticalmenu');
		$this->language->load('extension/module/pavverticalmenu');
		if (!$this->user->hasPermission('modify', 'extension/module/pavverticalmenu')) {
			die( $this->language->get('error_permission') );
		}

		$this->load->model('setting/setting');

		if( isset($this->request->post['params']) ) {
			$params = trim(html_entity_decode($this->request->post['params']));
			$a = json_decode(($params));

			$this->model_setting_setting->editSetting( 'pavverticalmenu_params', array('pavverticalmenu_params'=> $params) );
		}

		return $this->ajxgenmenu();

	}

	/**
	 *  Ajax Live Save Action.
	 */
	public function livesave(){
		$this->ajxgenmenu();
	}

	/**
	 * Ajax Render List Tree Mega Menu Action
	 */
	public function ajxgenmenu( ){

		$this->language->load( 'extension/module/pavverticalmenu' );
 		$this->load->model( 'setting/setting' );
		$this->load->model( 'extension/menu/tree' );
		$parent = '1';

		$this->template = 'extension/module/pavverticalmenu/verticalmenu-tree';

		/* unset mega menu configuration */
		if( isset($this->request->post['reset']) && $this->hasPermssion() ){
	 		$this->model_setting_setting->editSetting( 'pavverticalmenu_params', array('pavverticalmenu_params'=>'') );
	 	}

	 	$params = $this->model_setting_setting->getSetting( 'pavverticalmenu_params' );

	 	if( isset($params['pavverticalmenu_params']) && !empty($params['pavverticalmenu_params']) ){
	 		$params = json_decode( $params['pavverticalmenu_params'] );
	 	}

		if (isset($this->request->get['store_id'])){
			$store_id = $this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

	 	$this->mdata['treemenu'] = $this->model_extension_menu_tree->getTree( 1, true, $params, $store_id );

		echo $this->render();
	}

	public function getLang( $text ){
		return $this->language->get($text);
	}
}
?>