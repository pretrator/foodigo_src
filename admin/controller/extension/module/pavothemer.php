<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license GNU General Public License version 2
 */

if ( ! defined( 'DIR_SYSTEM' ) ) exit();

require_once 'vendors/scssphp/scss.inc.php';
use Leafo\ScssPhp\Compiler;

require_once 'pavothemer/pavothemer.php';

/**
 * Theme Control Controller
 * Exports:
 * 			+ theme settings
 * 			+ layouts
 * 			+ tables
 * 			+ layout module
 */
class ControllerExtensionModulePavothemer extends PavoThemerController {

	/**
	 * template file
	 *
	 * @var $template string
	 * @since 1.0.0
	 */
	public $template = 'extension/module/pavothemer/themecontrol';

	public function index() {
		$this->edit();
	}

	/**
	 * Render theme control admin layout
	 *
	 * @since 1.0.0
	 */
	public function edit() {
		// load language file
		$this->load->language('extension/module/pavothemer');
		// load setting model
		$this->load->model( 'setting/setting' );
		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		$theme = $this->config->get( 'config_theme' );
		// setting tabs
		$this->data['settings'] = PavoThemerSettingHelper::instance( $theme )->getSettings();
		$this->data['current_tab'] = isset( $this->request->get['current_tab'] ) ? $this->request->get['current_tab'] : current( array_keys( $this->data['settings'] ) );

		$tab = isset( $this->request->get['tab'] ) ? $this->request->get['tab'] : '';
		// setting tabs

		// validate and update settings
		$notices = array();
		if ( $this->request->server['REQUEST_METHOD'] === 'POST' ) {
			$validated = $this->validate();
			if ( $validated ) {
				// update options
				$this->model_setting_setting->editSetting( 'pavothemer', $this->request->post, $this->config->get( 'config_store_id' ) );
				// update custom asset files

				$themeHelper = PavoThemerHelper::instance( $theme );

				if( defined("_DEMO_") ){
					$file = DIR_CATALOG . 'view/theme/' . $theme . '/development/profiles/demo.json';
					$write = $themeHelper->writeFile( $file, json_encode($this->request->post) );
				}

				try {
					$cacheDir = DIR_CATALOG . 'view/javascript/pavothemer/';
					if (is_dir($cacheDir)) {
						$files = glob($cacheDir."*.{js,css}", GLOB_BRACE);
						if ($files) {
							foreach ($files as $f) {
								unlink($f);
							}
						}
					}
				} catch (Exception $e) {
					$this->session->data['error'] = $e->getMessage();
				}

				// css file
				if ( isset( $this->request->post['pavothemer_custom_css'] ) ) {
					$file = DIR_CATALOG . 'view/theme/' . $theme . '/stylesheet/customize.css';
					$write = $themeHelper->writeFile( $file, $this->request->post['pavothemer_custom_css'] );
					if ( ! $write ) {
						$notices[] = $this->language->get( 'error_permission_in_directory' ) . ' <strong>' . dirname( $file ) . '</strong>';
					}
				}

				// js file
				if ( isset( $this->request->post['pavothemer_custom_js'] ) ) {
					$file = DIR_CATALOG . 'view/theme/' . $theme . '/javascript/customize.js';
					$write = $themeHelper->writeFile( $file, $this->request->post['pavothemer_custom_js'] );
					if ( ! $write ) {
						$notices[] = $this->language->get( 'error_permission_in_directory' ) . ' <strong>' . dirname( $file ) . '</strong>';
					}
				}

				if ( empty( $this->session->data['error'] ) ) {
					$this->session->data['success'] = $this->language->get( 'text_success' );
					$this->response->redirect( str_replace(
												'&amp;',
												'&',
												$this->url->link('extension/module/pavothemer/edit', 'user_token=' . $this->session->data['user_token'], true )
											) );
				}
			}
		}

		if ( ! empty( $this->session->data['success'] ) ) {
			$this->data['success'] = $this->session->data['success'];
			unset( $this->session->data['success'] );
		}
		if ( ! empty( $notices ) ) {
			$this->data['notices'] = $notices;
		}

		foreach ( $this->data['settings'] as $k => $fields ) {
			if ( isset( $fields['item'] ) ) foreach( $fields['item'] as $k2 => $item ) {
				if ( isset( $item['id'] ) ) {
					$name = 'pavothemer_' . $item['id'];
					$value = isset( $this->request->post[ $name ] ) ? $this->request->post[ $name ] : $this->config->get( $name );
					// $value = $value ? $value : ( isset( $item['default'] ) ? $item['default'] : '' );

					$label = $this->language->get( 'setting_' . $item['id'] );
					$label = !preg_match( '/setting_/', $label ) ? $label : ( isset( $item['label'] ) ? $item['label'] : '' );
					// override item value
					$item['value'] = $value;
					$item['label'] = $label;

					// output html render fields
					$item['output'] = $this->renderFieldControl( $item );
					$this->data['settings'][$k]['item'][$k2] = $item;
				}
			}
		}

		$this->data['theme_info'] = PavoThemerHelper::instance( $this->config->get( 'config_theme' ) )->getThemeInfo();
		$this->data['code_editor_get_content_url'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/ajaxGetContent', 'user_token=' . $this->session->data['user_token'], true ) );
		$this->data['theme_management_notices'] = $this->language->get( 'theme_management_notices_text' );
		$this->data['pavothemer_contact'] = $this->config->get( 'pavothemer_contact' ) ? $this->config->get( 'pavothemer_contact' ) : array();

		// enqueue scripts, stylesheet needed to display editor
		$this->document->addScript( 'view/javascript/summernote/summernote.js' );
		$this->document->addScript( 'view/javascript/summernote/opencart.js' );
		$this->document->addStyle( 'view/javascript/summernote/summernote.css' );

		$this->document->addScript( 'view/javascript/codemirror/lib/codemirror.js' );
		$this->document->addScript( 'view/javascript/codemirror/lib/formatting.js' );
		$this->document->addScript( 'view/javascript/codemirror/lib/xml.js' );
		$this->document->addStyle( 'view/javascript/codemirror/lib/codemirror.css' );
		$this->document->addStyle( 'view/javascript/codemirror/theme/monokai.css' );

		$this->document->addScript( 'view/javascript/jquery/colorpicker/bootstrap-colorpicker.min.js' );
		$this->document->addStyle(  'view/javascript/jquery/colorpicker/bootstrap-colorpicker.css' );


		$this->data['google_api_key'] = $this->config->get( 'pavothemer_google_map_api_key' );
		$this->document->addScript( 'view/javascript/pavothemer/dist/settings.min.js' );
		if ( $this->data['google_api_key'] ) {
        	// $this->document->addScript( '//maps.googleapis.com/maps/api/js?key=' . $this->data['google_api_key'] . '&libraries=places' );
		}

		// render admin theme control template
		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );
		$this->response->setOutput( $this->load->view( $this->template, $this->data ) );
	}

	/**
	 * theme management
	 */
	public function management() {
		$this->document->addStyle( 'view/stylesheet/pavothemer/api.css' );
		$this->document->addScript( 'view/javascript/pavothemer/dist/management.min.js' );
		// load language file
		$this->load->language( 'extension/module/pavothemer' );
		// load setting model
		$this->load->model( 'setting/setting' );
		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'heading_title' ),
			'href'      => $this->url->link( 'extension/module/pavothemer/edit', 'user_token=' . $this->session->data['user_token'].'&type=module', 'SSL' ),
      		'separator' => ' :: '
   		);
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get('theme_management_title'),
			'href' => $this->url->link( 'extension/module/pavothemer/management', 'user_token=' . $this->session->data['user_token'], true )
		);

		$this->data['enter_purchased_code_url'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/purchasedCode', 'user_token=' . $this->session->data['user_token'], true ) );
		$this->data['extension_themes_url'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/apiExtensions', 'type=theme&user_token=' . $this->session->data['user_token'], true ) );
		$this->data['extension_download_available_url'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/apiExtensions', 'type=available&user_token=' . $this->session->data['user_token'], true ) );

		$this->data['extension_download_module_available_url'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/apiExtensions', 'type=modules&user_token=' . $this->session->data['user_token'], true ) );

		$this->data['extension_download_extension_url'] =  $this->url->link( 'extension/module/pavothemer/download', 'user_token=' . $this->session->data['user_token'], true );

		$this->data['extension_installed_url'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/apiExtensions', 'type=installed&user_token=' . $this->session->data['user_token'], true ) );
		$this->data['liveinstall_url'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavothemer/livedownload', 'user_token=' . $this->session->data['user_token'], true ) );
		$this->data['activate_url']	= str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavothemer/activateExtension', 'user_token=' . $this->session->data['user_token'], true ) );
		$this->data['deactivate_url'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavothemer/deActivateExtension', 'user_token=' . $this->session->data['user_token'], true ) );
		$this->document->setTitle( $this->language->get( 'theme_management_heading_title' ) );
		$this->data = array_merge( array(
					'header'		=> $this->load->controller( 'common/header' ),
					'column_left' 	=> $this->load->controller( 'common/column_left' ),
					'footer'		=> $this->load->controller( 'common/footer' )
				), $this->data );
		$this->response->setOutput( $this->load->view( 'extension/module/pavothemer/thememanagement', $this->data ) );
	}

	/**
	 * live download modules, themes as extension
	 * @return mixed
	 */
	public function livedownload() {
		$json = array();
		$this->load->model( 'extension/pavothemer/sample' );
		$code = ! empty( $this->request->request['code'] ) ? trim( $this->request->request['code'] ) : 'pa_xstore';
		$url = ! empty( $this->request->request['f'] ) ? trim( $this->request->request['f'] ) : false;

		if( $code && $url ) {
			$url = base64_decode( $url );
			$file = DIR_DOWNLOAD . $code . '.ocmod.zip';
			$request = PavothemerApiHelper::post( $url, array(
									'body'	=> array(
											'action'			=> 'extensions',
											'extension_type'	=> 'theme',
											'code'				=> $code
										),
									'filename'	=> $file
								) );

			$response = ! empty( $request['response'] ) ? $request['response'] : array();
			$body = ! empty( $request['body'] ) ? $request['body'] : '';
			if ( ! isset( $response['code'] ) || $response['code'] !== 200 ) {
				$json['error'] = sprintf( $this->language->get( 'text_error_module_no_found' ), $code );
			} else if ( $body !== true ) {
				$body = json_decode( $body, true );
				if ( isset( $body['error'], $body['message'] ) ) {
					$json['error'] = $body['message'];
				}
			} else {
				$this->model_extension_pavothemer_sample->installModule( $file );
			}
		}

		if ( empty( $json['error'] ) ) {
			$json['status'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	/**
	 * deactivate extension
	 */
	public function deActivateExtension() {
		$type = isset( $_REQUEST['type'] ) ? trim( $_REQUEST['type'] ) : '';
		$code = isset( $_REQUEST['code'] ) ? trim( $_REQUEST['code'] ) : '';

		$this->load->model( 'setting/store' );
		$this->load->model( 'setting/setting' );
		$this->load->language( 'extension/module/pavothemer' );
		$this->load->model( 'setting/extension' );
		$this->load->model( 'user/user_group' );
		$results = array();
		if ( $type == 'theme' ) {

			// default store
			$settings = $this->model_setting_setting->getSetting( 'config' );
			$settings['config_theme'] = 'default';
			$this->model_setting_setting->editSetting( 'config', $settings );

			//
			$stores = $this->model_setting_store->getStores();
			$stores[] = array(
				'store_id'	=> 0
			);
			// load controller
			if ( $this->user->hasPermission('modify', 'extension/extension/theme') ) {
				$this->model_setting_extension->install( 'theme', $code );
				$this->model_user_user_group->removePermission( $this->user->getGroupId(), 'access', 'extension/theme/' . $code );
				$this->model_user_user_group->removePermission( $this->user->getGroupId(), 'modify', 'extension/theme/' . $code );

				// Call install method if it exsits
				$this->load->controller('extension/theme/' . $code . '/uninstall');
			}
			if ( $stores ) {
				foreach ( $stores as $store ) {
					$store_id = isset( $store['store_id'] ) ? $store['store_id'] : 0;
					$settings = $this->model_setting_setting->getSetting( 'config', $store_id );
					$settings['config_theme'] = 'default';
					$this->model_setting_setting->editSetting( 'config', $settings, $store_id );
				}
			}

			$results['activated'] = false;
			$results['type'] = 'theme';
			$results['message'] = $this->language->get( 'text_deactivate_theme_success' );
		} else if ( $type == 'module' ) {
			// load controller
			if ( $this->user->hasPermission('modify', 'extension/extension/module') ) {
				$this->model_setting_extension->uninstall( 'module', $code );
				$this->model_user_user_group->addPermission( $this->user->getGroupId(), 'access', 'extension/module/' . $code );
				$this->model_user_user_group->addPermission( $this->user->getGroupId(), 'modify', 'extension/module/' . $code );

				// Call install method if it exsits
				$this->load->controller('extension/module/' . $code . '/uninstall');
			}
			$results['activated'] = false;
			$results['text'] = $this->language->get( 'text_activate' );
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $results ) );
	}

	/**
	 * activate extension
	 */
	public function activateExtension() {
		$type = isset( $_REQUEST['type'] ) ? trim( $_REQUEST['type'] ) : '';
		$code = isset( $_REQUEST['code'] ) ? trim( $_REQUEST['code'] ) : '';

		$this->load->model( 'setting/store' );
		$this->load->model( 'setting/extension' );
		$this->load->model( 'setting/setting' );
		$this->load->model( 'user/user_group' );
		$this->load->language( 'extension/module/pavothemer' );

		$results = array();
		if ( $type == 'theme' ) {
			//
			$stores = $this->model_setting_store->getStores();
			$stores[] = array(
				'store_id'	=> 0
			);
			// load controller
			if ( $this->user->hasPermission('modify', 'extension/extension/theme') ) {
				$this->model_setting_extension->install( 'theme', $code );
				$this->model_user_user_group->addPermission( $this->user->getGroupId(), 'access', 'extension/theme/' . $code );
				$this->model_user_user_group->addPermission( $this->user->getGroupId(), 'modify', 'extension/theme/' . $code );

				// Call install method if it exsits
				$this->load->controller('extension/theme/' . $code . '/install');

				$settings = $this->config->get( 'theme_' . $code );
				$settings[ 'theme_' . $code . '_directory'] = $code;
				$settings[ 'theme_' . $code . '_status'] = 1;

				$this->model_setting_setting->editSetting( 'theme_' . $code, $settings );
			}
			if ( $stores ) {
				foreach ( $stores as $store ) {
					$store_id = isset( $store['store_id'] ) ? $store['store_id'] : 0;
					$settings = $this->model_setting_setting->getSetting( 'config', $store_id );
					$settings['config_theme'] = $code;
					$this->model_setting_setting->editSetting( 'config', $settings, $store_id );
				}
			}

			$results['activated'] = true;
			$results['type'] = 'theme';
			$results['message'] = sprintf( $this->language->get( 'text_activate_theme_success' ), $code, str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/tools', 'user_token=' . $this->session->data['user_token'], true ) ) );
		} else if ( $type == 'module' ) {
			// load controller
			if ( $this->user->hasPermission('modify', 'extension/extension/module') ) {
				$this->model_setting_extension->install( 'module', $code );
				$this->model_user_user_group->addPermission( $this->user->getGroupId(), 'access', 'extension/module/' . $code );
				$this->model_user_user_group->addPermission( $this->user->getGroupId(), 'modify', 'extension/module/' . $code );

				// Call install method if it exsits
				$this->load->controller('extension/module/' . $code . '/install');
			}
			$results['activated'] = true;
			$results['text'] = $this->language->get( 'text_deactivate' );
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $results ) );
	}

	/**
	 * api get extensions
	 */
	public function apiExtensions() {

		if ( $this->isAjax() ) {
			$this->load->language( 'extension/module/pavothemer' );
			// extensions
			$this->load->model( 'setting/extension' );
			$this->load->model( 'extension/pavothemer/sample' );
			// all modules
			$allExtensionsInstalled = $this->model_extension_pavothemer_sample->getExtensions();

			// type is module or theme
			$apiType = ! empty( $this->request->request['type'] ) ? $this->request->request['type'] : 'themes';

			$cache_key = 'pavothemer_extensions_api' . $apiType;
			$purchased_codes = $this->config->get( 'pavothemer_purchased_codes' );
			$purchased_codes = $purchased_codes ? $purchased_codes : array();
			// get cached before
			// $this->cache->delete( $cache_key );
			$extensions = array();
		//	$extensions = $this->cache->get( $cache_key );
			$results = array(
					'status'	=> false,
					'html'		=> ''
				);

			if ( ! $extensions ) {

				switch ( $apiType ) {
					case 'theme':
					case 'installed':
							# code...
							// make request
							$res = PavothemerApiHelper::post( PAVOTHEMER_THEMES_API, array(
									'body'	=> array(
											'action'			=> 'extensions',
											'extension_type'	=> 'theme'
										)
								) );

							if ( ! empty( $res['response'] ) && ! empty( $res['response']['code'] ) && $res['response']['code'] === 200 ) {
								$body = ! empty( $res['body'] ) ? json_decode( $res['body'], true ) : array();
								$extensions = $body;
							}
						break;

					case 'available':
							# code...
							// download avaiable
							// abx-chd-sdk-xyz-012e
							// var_dump($purchased_codes); die();
							$purchased_codes = $purchased_codes ? $purchased_codes : array();
							$res = PavothemerApiHelper::post( PAVOTHEMER_THEMES_API, array(
									'body'	=> array(
											'action'			=> 'extensions',
											'download-available'=> 1,
											'purchased_codes'	=> $purchased_codes
										)
								) );

							if ( ! empty( $res['response'] ) && ! empty( $res['response']['code'] ) && $res['response']['code'] === 200 ) {
								$body = ! empty( $res['body'] ) ? json_decode( $res['body'], true ) : array();
								$extensions =  $body;
							}

						break;
					case 'modules':
							# code...
							// download avaiable
							// abx-chd-sdk-xyz-012e
							// var_dump($purchased_codes); die();
							$purchased_codes = $purchased_codes ? $purchased_codes : array();
							$res = PavothemerApiHelper::post( PAVOTHEMER_MODULE_API, array(
									'body'	=> array(
											'action'			=> 'extensions',
											'download-available'=> 1,
											'purchased_codes'	=> $purchased_codes
										)
								) );

							if ( ! empty( $res['response'] ) && ! empty( $res['response']['code'] ) && $res['response']['code'] === 200 ) {
								$body = ! empty( $res['body'] ) ? json_decode( $res['body'], true ) : array();

								$extensions =  $body;
							}

						break;

					default:
						# code...
						break;
				}

				$results['status'] = ! empty( $extensions );

			} else {
				$results['status'] = true;
			}

			if ( $results['status'] ) {

				$data = array();

				if ( $extensions ) {

					$store_id = $this->config->get( 'config_store_id' );
					$theme = $this->config->get( 'config_theme' );
					foreach ( $extensions as $k => $extension ) {
						$type = isset( $extension['type'] ) ? $extension['type'] : 'module';
						$code = isset( $extension['code'] ) ? $extension['code'] : '';

						$installed_extensions = $this->model_setting_extension->getInstalled( $type );
						$extension['installed'] = in_array( $code, $installed_extensions );
						$extension['verified'] = in_array( $code, $purchased_codes );
						$extension['activated'] = false;
						if ( $type === 'theme' ) {
							$extension['activated'] = $code === $theme;
							$themes = glob( DIR_CATALOG . 'view/theme/*' );
							$themes = array_map( 'basename', $themes );
							$extension['installed'] = in_array( $code, $themes );
						}

						if( (int)$extension['price'] > 0 ) {
							$extension['price'] = $extension['currency_symbol'] . ' ' . $extension['price'];
							$extension['is_free'] = 0;
						} else {
							$extension['price'] = '<span class="price-free">' . $this->language->get( 'text_free' ) . '</span>';
							$extension['is_free'] = 1;
						}
						$file = isset($extension['download']) ? base64_encode( $extension['download'] ) : '';
						$extension['f'] = $file;
						$extension['download'] = $this->url->link( 'extension/module/pavothemer/livedownload', 'code='.$code.'&f='.$file.'&user_token=' . $this->session->data['user_token'], true );
						$extension['activate'] = $this->url->link( 'extension/module/pavothemer/activateExtension', 'code='.$code.'&type='.$type.'&user_token=' . $this->session->data['user_token'], true );
						$extension['deactivate'] = $this->url->link( 'extension/module/pavothemer/deActivateExtension', 'code='.$code.'&type='.$type.'&user_token=' . $this->session->data['user_token'], true );
 						$extensions[$k] = $extension;
					}
				}

				if ( ! $extensions ) {
					$results['status'] = false;
				} else {
					// set cache
				 //	$this->cache->set( $cache_key, $extensions );
				}

				$results['html'] = $extensions ? $this->load->view( 'extension/module/pavothemer/extensions', array( 'extensions' => $extensions ) ) : $this->language->get( 'entry_no_extension_found' );
			} else {
				$results['html'] = $this->language->get( 'entry_no_extension_found' );
			}

			$this->response->addHeader( 'Content-Type: application/json' );
			$this->response->setOutput( json_encode( $results ) );
		}
	}

	/**
	 * enter purchased code
	 */
	public function purchasedCode() {
		$this->load->language( 'extension/module/pavothemer' );
		$results = array(
				'status'	=> false,
				'message'		=> ''
			);

		// license free or purchased
		$purchased_code = ! empty( $this->request->request['purchased_code'] ) ? $this->request->request['purchased_code'] : '';

		// make request
		$res = PavothemerApiHelper::post( PAVOTHEMER_API, array(
				'body'	=> array(
						'action'			=> 'verify-purchased-code',
						'purchased_code'	=> $purchased_code
					)
			) );

		if ( ! empty( $res['response'] ) && ! empty( $res['response']['code'] ) && $res['response']['code'] === 200 ) {
			$body = ! empty( $res['body'] ) ? json_decode( $res['body'], true ) : array();
			$extensions = ! empty( $body['extensions'] ) ? $body['extensions'] : array();

			if ( ! empty( $body['error'] ) ) {
				$results['message'] = ! empty( $body['message'] ) ? $body['message'] : ( ! empty( $body['response'] ) && ! empty( $body['response']['message'] ) ? $body['response']['message'] : '' );
			} else {
				$this->load->model( 'extension/pavothemer/sample' );
				$this->load->model( 'setting/setting' );

				// delete extensions cached before
				$this->cache->delete( 'pavothemer_extensions_api' );

				$results['status'] = ! empty( $body['error'] ) ? false : true;

				// update setting purchased code
				if ( $results['status'] && isset( $body['extensions'] ) ) {
					$settings = $this->model_setting_setting->getSetting( 'pavothemer' );

					$pavothemer_purchased_codes = $this->config->get( 'pavothemer_purchased_codes' );
					$pavothemer_purchased_codes = $pavothemer_purchased_codes ? $pavothemer_purchased_codes : array();
					$pavothemer_purchased_codes[] = ! empty( $body['purchased_code'] ) ? $body['purchased_code'] : '';
					// update settings
					$settings['pavothemer_purchased_codes'] = $pavothemer_purchased_codes;
					$this->model_setting_setting->editSetting( 'pavothemer', $settings );

					// extensions list
					$results['extension_list'] = $this->load->view( 'extension/module/pavothemer/extensions', array( 'extensions' => $extensions ) );
					// purchased code list
					$results['html'] = $this->load->view( 'extension/module/pavothemer/paids', array( 'extensions' => $extensions ) );
				}

				$results['message'] = ! empty( $body['message'] ) ? $body['message'] : ( ! empty( $body['response'] ) && ! empty( $body['response']['message'] ) ? $body['response']['message'] : '' );
			}
		} else {
			$results['message'] = ! empty( $res['response'] ) && ! empty( $res['response']['message'] ) ? $res['response']['message'] : sprintf( $this->language->get( 'error_curl' ), PavothemerApiHelper::$errno, PavothemerApiHelper::$error );
		}

		if ( $this->isAjax() ) {
			$this->response->addHeader( 'Content-Type: application/json' );
			$this->response->setOutput( json_encode( $results ) );
		} else {
			$this->response->redirect( str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/management', 'user_token=' . $this->session->data['user_token'], true ) ) ); exit();
		}
	}

	/**
	 * Customize
	 *
	 * @since 1.0.0
	 */
	public function customize() {

		$this->load->language( 'extension/module/pavothemer' );
		// add scripts
		if( isset($this->request->post['customize']) && is_array($this->request->post['customize']) ){
			$preview = isset($this->request->post['mode']) && $this->request->post['mode'] == 'preview';

			$customize = ! empty( $this->request->post['customize'] ) ? $this->request->post['customize'] : array();
			$font = ! empty( $this->request->post['font'] ) ? $this->request->post['font'] : array();
			$fontweight = ! empty( $this->request->post['fontweight'] ) ? $this->request->post['fontweight'] : array();
			$creator = array( 'customize' => $customize, 'font' => $font, 'fontweight' => $fontweight );
			$creator['name'] = ! empty( $this->request->post['name'] ) ? $this->request->post['name'] : false;

			$file = $this->_skincreator( $creator, false, $preview );

			$output = array(
				'status' => $file ? true : false,
				'file'	 => $file
			);
			echo json_encode( $output );
			exit();
		}

		$this->document->addScript( 'view/javascript/jquery/colorpicker/bootstrap-colorpicker.min.js' );
		$this->document->addStyle(  'view/javascript/jquery/colorpicker/bootstrap-colorpicker.css' );
		$this->document->addScript( 'view/javascript/pavothemer/dist/customize.min.js' );
		$this->document->addStyle( 'view/stylesheet/pavothemer/dist/customize.min.css' );

		$this->data['iframeURI'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
		$this->data['themeName'] = ucfirst( implode( ' ', explode( '-', implode( ' ', explode( '_', $this->config->get( 'config_theme' ) ) ) ) ) );
		$this->data['current_skin'] = $this->config->get( 'pavothemer_default_skin' );
		if ( isset( $this->request->get['skin'] ) ) {
			$this->data['current_skin'] = $this->request->get['skin'];
		}
		$this->data['action'] = $this->url->link( 'extension/module/pavothemer/customize', 'user_token=' . $this->session->data['user_token'], true );
		$theme = $this->config->get( 'config_theme' );
		$this->data['base_skin_url'] = ( $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG ) . 'catalog/view/theme/' . $theme . '/stylesheet/skins/';
		$creator = PavoThemerSkinCreatorHelper::instance( $theme );
		$skins = array();
		$files = $creator->getCustomSkins();
		$customize_options = array();
		if ( $files ) {
		 	foreach ( $files as $key => $file ) {
		 	 	if( $content = file_get_contents($file) ){
			 	 	$output = array();
 					$output['data'] = $data = json_decode( $content, true );
 					if ( $this->data['current_skin'] === basename( $file, '.json' ) ) {
 						$customize_options = $data;
 					}

			 	 	$filename = basename( $file, '.json' );
			 	 	if ( $filename === 'cache' ) continue;
			 	 	$output['name'] = ! empty( $data['name'] ) ? $data['name'] : $filename;
			 	 	$output['bar']  = '';
			 	 	$output['link'] = $this->url->link('extension/module/pavothemer/skincreator'
						, 'skin='.$output['name'].'&user_token=' . $this->session->data['user_token'], true );
			 	 	$counter = count($data) > 5 ? 5: count($data);

			 	 	$customize = ! empty( $data['customize'] ) ? $data['customize'] : array();
 					foreach ( $customize as $count => $color ) {
 						$output['bar'] .= '<span style="background-color:'.$color.'"></span>';
 					}
 					$skins[ $filename ] = $output;
		 	 	}
		 	}
		}

		$this->data['skins'] = $skins;
		$themeHelper = PavoThemerHelper::instance( $this->config->get( 'config_theme' ) );
		$customizes = $themeHelper->getCustomizes();
		foreach ( $customizes as $file => $customize ) {
			$this->data['settings'][$file] = $this->parseCustomizeOptions( $customize, $customize_options );
		}

		$this->data['customize_url'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/customize', 'user_token=' . $this->session->data['user_token'], true ) );
		$this->data['update_customize_url'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/updateCustomize', 'user_token=' . $this->session->data['user_token'], true ) );
		$this->data['delete_skin_url'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/deleteSkinURL', 'user_token=' . $this->session->data['user_token'], true ) );
		$this->data['admin_url'] = str_replace( '&amp;', '&', $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true ) );
		$this->template = 'extension/module/pavothemer/customize';
		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );
		$this->response->setOutput( $this->load->view( $this->template, $this->data ) );
	}

	/**
	 * update customize
	 * @since 1.0.0
	 */
	public function updateCustomize() {
		// $this->load->model( 'setting/setting' );
		$theme = $this->config->get( 'config_theme' );
		$results = array(
			'status'	=> true,
			'type'		=> 'updated'
		);
		// place script
		$customize = ! empty( $this->request->post['customize'] ) ? $this->request->post['customize'] : array();
		$store_id = ! empty( $this->request->post['store_id'] ) ? (int)$this->request->post['store_id'] : 0;
		$skin = ! empty( $this->request->post['skin'] ) ? $this->request->post['skin'] : '';
		// if ( $skin ) {

			$clone = ! empty( $this->request->post['clone'] ) ? $this->request->post['clone'] : false;
			$file = DIR_CATALOG . 'view/theme/' . $theme . '/stylesheet/skins/'.$clone.'.json' ;
			$creator = array();
			if ( $clone && file_exists( $file ) ) {
				$creator = json_decode( file_get_contents( $file ), true );
				$results['type'] = 'clone';
			} else {
				$customize = ! empty( $this->request->post['customize'] ) ? $this->request->post['customize'] : array();
				$fonts = ! empty( $this->request->post['font'] ) ? $this->request->post['font'] : array();
				$fontweight = ! empty( $this->request->post['fontweight'] ) ? $this->request->post['fontweight'] : array();
				$creator = array( 'customize' => $customize, 'font' => $fonts, 'fontweight' => $fontweight );
				if ( ! $skin ) {
					$results['type'] = 'created';
				}
			}
			$creator['name'] = ! empty( $this->request->post['name'] ) ? $this->request->post['name'] : false;
			$results['name'] = $creator['name'];
			$skin = $clone ? time() : $skin;
			$results['file'] = $this->_skincreator( $creator, $skin );
			$info = basename($results['file']);
			$info = explode('?', $info);
			$results['filename'] = isset($info[0]) ? basename($info[0], '.css') : '';
		// }

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $results ) );
	}

	/**
	 * delete skin url
	 *
	 * @return mixed
	 */
	public function deleteSkinURL() {
		$skin = ! empty( $this->request->request['skin'] ) ? trim( $this->request->request['skin'] ) : '';
		$theme = $this->config->get( 'config_theme' );
		$infoFile = DIR_CATALOG . 'view/theme/' . $theme . '/stylesheet/skins/' . $skin . '.json' ;
		$styleFile = DIR_CATALOG . 'view/theme/' . $theme . '/stylesheet/skins/' . $skin . '.css' ;
		$delete_files = array(
				$infoFile,
				$styleFile
			);

		$status = true;
		foreach ( $delete_files as $file ) {
			if ( file_exists( $file ) ) {
				$status = unlink( $file );
			}
		}

		$results = array(
				'status'	=> $status
			);
		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $results ) );
	}

	/**
	 * check is ajax request
	 */
	public function isAjax() {
		return ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest';
	}

	/**
	 * live install
	 * all download will be place in DIR_DOWNLOAD . '/pavo-' . $theme
	 */
	public function liveinstall( $mass = false ) {
		if ( $mass == false && isset( $this->session->data['pavo_mass_install'] ) ) {
			unset( $this->session->data['pavo_mass_install'] );
		}
		/**
		 * 1. check & download module from server
		 */
		/**
		 * 2. check & download sample
		 */
		/**
		 * 3. import samples by step || import database
		 */

		$this->load->model( 'extension/pavothemer/sample' );
		$this->load->language( 'extension/module/pavothemer' );

		$theme = $this->config->get( 'config_theme' );
		$demo = isset( $this->request->request['demo'] ) ? $this->request->request['demo'] : '';

		// init setup folder download for install
		$download_dir = DIR_DOWNLOAD . $theme . '-' . $demo . '/';
		$module_dir = $download_dir . 'modules/';
		$dirs = array( $download_dir, $module_dir );

		// download dir
		$this->session->data['pavo_demo'] = $demo;
		$this->session->data['pavo_demo_dir'] = $download_dir;

		$path = '';
		foreach ( $dirs as $dir ) {
			if ( ! is_writable( dirname( $dir ) ) ) {
				$path = dirname( $dir );
				break;
			}
			if ( ! is_dir( $dir ) ) {
				mkdir( $dir, 0777, true );
			}
		}

		$results = array();
		if ( $path ) {
			$results['error'] = sprintf( $this->language->get( 'error_permission_dir' ), $path );
			if ( isset( $this->session->data['pavo_mass_install'] ) ) {
				unset( $this->session->data['pavo_mass_install'] );
			}
		} else {
			$results['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/livedownloadmodules', 'user_token=' . $this->session->data['user_token'], true ) );
			$results['text'] = $this->language->get( 'text_download_modules' );
			$results['progress_percent'] = 40;
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $results ) );
	}

	/**
	 * live download modules
	 *
	 * move files, insert modules table, add permission and Call install method if it exsits
	 */
	public function livedownloadmodules() {
		$this->load->language( 'extension/module/pavothemer' );
		$this->load->model( 'extension/pavothemer/sample' );
		$this->load->model( 'setting/extension' );
		$this->load->model( 'setting/module' );
		$this->load->model( 'user/user_group' );

		$demo = ! empty( $this->session->data['pavo_demo'] ) ? $this->session->data['pavo_demo'] : '';
		$demoSample = $this->getSampleData();
		$modules = ! empty( $demoSample['modules'] ) ? $demoSample['modules'] : array();
		$samples = isset( $demoSample['samples'] ) ? $demoSample['samples'] : array();

		foreach ( $samples as $key => $sample ) {
			if ( isset( $sample['key'] ) && $sample['key'] == $demo ) {
				// save sample data to session
				$this->session->data['pavo_sample'] = $sample;
				$this->session->data['pavo_demo_store_id'] = isset( $sample['store_id'] ) ? $sample['store_id'] : 0;
				$modules = ! empty( $sample['modules'] ) ? $sample['modules'] : $modules;
			}
		}

		$extension_modules = $this->model_extension_pavothemer_sample->getExtensionModules();
		$modules_installed = isset( $extension_modules['modules'] ) ? $extension_modules['modules'] : array();
		$mod_required = array();
		foreach ( $modules as $module ) {
			if ( ! isset( $modules_installed[$module] ) || ! isset( $modules_installed[$module]['installed'] ) || ! $modules_installed[$module]['installed'] ) {
				$mod_required[] = $module;
			}
		}

		// results for ajax request
		$results = array();
		// $url = 'http://www.pavothemes.com/download_modules/';
		if ( $mod_required ) {
			foreach ( $mod_required as $module ) {
				$module_file = $module . '.ocmod.zip';
				$file = $this->session->data['pavo_demo_dir'] . 'modules/' . $module_file;
				if ( ! file_exists( $file ) ) {
					// $url . $module_file
					$request = PavothemerApiHelper::post( PAVOTHEMER_SAMPLES_MODULE_API, array(
							'filename'			=> $file,
							'body'				=> array(
								'key'				=> $module,
								'purchased_codes'	=> $this->config->get( 'pavothemer_purchased_codes' ),
								'debug'				=> PAVOTHEMER_DEBUG_MODE ? 1 : 0
							)
						) );

					$response = ! empty( $request['response'] ) ? $request['response'] : array();
					$body = ! empty( $request['body'] ) ? $request['body'] : '';
					if ( ! isset( $response['code'] ) || $response['code'] !== 200 ) {
						$results['error'] = sprintf( $this->language->get( 'text_error_module_no_found' ), $module );
					} else if ( $body !== true ) {
						$body = json_decode( $body, true );
						if ( isset( $body['error'], $body['message'] ) ) {
							$results['error'] = $body['message'];
						}
					}
				}

				if ( empty( $results['error'] ) ) {
					$install = $this->model_extension_pavothemer_sample->installModule( $file );
					if ( $install !== true && ! empty( $install['error'] ) ) {
						$results['error'] = $install['error'];
					} else {
						$this->request->get['extension'] = $module;
						// load controller
						if ( $this->user->hasPermission('modify', 'extension/extension/module') ) {
							$this->model_setting_extension->install( 'module', $this->request->get['extension'] );
							$this->model_user_user_group->addPermission( $this->user->getGroupId(), 'access', 'extension/module/' . $this->request->get['extension'] );
							$this->model_user_user_group->addPermission( $this->user->getGroupId(), 'modify', 'extension/module/' . $this->request->get['extension'] );

							// Call install method if it exsits
							$this->load->controller('extension/module/' . $this->request->get['extension'] . '/install');
						}
						// delete module
						unlink( $file );
					}
				}
			}
		}

		if ( ! isset( $results['error'] ) ) {
			// delete download file
			if ( ! empty( $this->session->data['pavo_demo_dir'] ) ) {
				$modules_dir = $this->session->data['pavo_demo_dir'] . 'modules';
				if ( is_dir( $modules_dir ) ) {
					rmdir( $modules_dir );
				}
			}
			$results['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/livedownloadsample', 'user_token=' . $this->session->data['user_token'], true ) );
			$results['text'] = $this->language->get( 'text_download_sample' );
			$results['progress_percent'] = 60;
		} else if ( isset( $this->session->data['pavo_mass_install'] ) ) {
			unset( $this->session->data['pavo_mass_install'] );
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $results ) );
	}

	/**
	 * live download sample data
	 */
	public function livedownloadsample() {
		$this->load->language( 'extension/module/pavothemer' );

		$results = array();
		$sample = ! empty( $this->session->data['pavo_sample'] ) ? $this->session->data['pavo_sample'] : array();
		$key = ! empty( $sample['key'] ) ? $sample['key'] : '';
		$file = $key ? $key . '.zip' : false;

		if ( ! $file ) {
			$results['error'] = sprintf( $this->language->get( 'text_sample_no_found' ), $this->session->data['pavo_demo'] );
		} else {
			$sample = ! empty( $this->session->data['pavo_sample'] ) ? $this->session->data['pavo_sample'] : array();
			$file = $this->session->data['pavo_demo_dir'] . $file;
			if ( ! file_exists( $file ) && $file ) {
				$request = PavothemerApiHelper::post( PAVOTHEMER_SAMPLES_API, array(
						'filename'			=> $file,
						'body'				=> array(
							'key'				=> $key,
							'theme'				=> $this->config->get( 'config_theme' ),
							'purchased_codes'	=> $this->config->get( 'pavothemer_purchased_codes' ),
							'debug'				=> PAVOTHEMER_DEBUG_MODE ? 1 : 0
						)
					) );

				$response = ! empty( $request['response'] ) ? $request['response'] : array();
				$body = ! empty( $request['body'] ) ? $request['body'] : '';

				if ( ! isset( $response['code'] ) || $response['code'] !== 200 ) {
					$results['error'] = $this->language->get( 'text_sample_no_found' );
				} else if ( $body !== true ) {
					$body = json_decode( $body, true );
					if ( isset( $body['error'], $body['message'] ) ) {
						$results['error'] = $body['message'];
					}
				}
			}

			if ( ! isset( $results['error'] ) && file_exists( $file ) ) {
				// save sample zip file downloaded to session
				$this->session->data['sample_zip_file'] = $file;

				$results['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/liveinstallsample', 'user_token=' . $this->session->data['user_token'], true ) );
				$results['text'] = $this->language->get( 'text_install_sample_data' );
				$results['progress_percent'] = 70;
			}
		}

		if ( ! empty( $results['error'] ) && isset( $this->session->data['pavo_mass_install'] ) ) {
			unset( $this->session->data['pavo_mass_install'] );
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $results ) );
	}

	/**
	 * install sample
	 */
	public function liveinstallsample() {
		$this->load->language( 'extension/module/pavothemer' );
		$this->load->model( 'extension/pavothemer/sample' );

		$demo = ! empty( $this->session->data['pavo_demo'] ) ? $this->session->data['pavo_demo'] : '';
		$dir = ! empty( $this->session->data['pavo_demo_dir'] ) ? $this->session->data['pavo_demo_dir'] : '';

		$results = array();
		if ( ! $demo || ! $dir ) {
			$results['error'] = sprintf( $this->language->get( 'text_sample_no_found' ), $dir );
		} else {
			// download dir
			$sample_file = ! empty( $this->session->data['sample_zip_file'] ) ? $this->session->data['sample_zip_file'] : '';

			if ( ! $sample_file || ! file_exists( $sample_file ) ) {
				$results['error'] = $this->language->get( 'text_sample_no_found' );
			} else {
				$foldername = basename( $sample_file, '.zip' );
				$folder = $this->session->data['pavo_demo_dir'];// . $foldername;
				$glob = glob( $folder . '/*' );
				$is_empty = empty( $glob );
				// if ( ! file_exists( $folder ) || $is_empty ) {
					$zip = new ZipArchive();
					if ( $zip->open( $sample_file ) === true ) {
					    $zip->extractTo( $this->session->data['pavo_demo_dir'] );
					    $zip->close();
					}
					unlink( $sample_file );
				// }

				$sampleDir = $this->session->data['pavo_demo_dir'];// . $foldername;
				$profile = file_exists( $sampleDir . '/profile.json' ) ? file_get_contents( $sampleDir . '/profile.json' ) : false;
				$profile = $profile ? json_decode( $profile, true ) : array();

				// move skins files
				$skinsDir = $sampleDir . '/skins/';
				if ( is_dir($skinsDir) ) {
					$files = glob($skinsDir.'/*.json');
					if ( $files ) {
						$theme = $this->config->get( 'config_theme' );
						$sampleHelper = PavoThemerSampleHelper::instance( $theme );
						foreach ( $files as $file ) {
							$fileName = basename($file);
							$skinfile = $sampleHelper->themePath.'/stylesheet/skins/'.$fileName;
							if ( ! file_exists($skinfile) ) {
								copy($file, $skinfile);
							}
						}
					}
				}

				if ( isset( $this->session->data['pavo_mass_install'] ) && $this->session->data['pavo_mass_install'] ) {
					$this->load->model( 'extension/pavothemer/restore' );
					// start restore demo data ignore user table
					$demosql = $sampleDir . '/demo.sql';
					if ( file_exists( $demosql ) ) {
						$restore = $this->model_extension_pavothemer_restore->restore( $demosql );
						if ( $restore !== true ) {
							$results['error'] = $restore;
						}
					}
				} else if ( ! empty( $profile ) ) {
					if ( isset( $this->session->data['pavo_mass_install'] ) ) {
						unset( $this->session->data['pavo_mass_install'] );
					}
					// start import theme settings
					$this->model_extension_pavothemer_sample->importThemeSettings( $profile );
					// import sql
					$query = array();
					if ( file_exists( $folder . '/rows.php' ) ) {
						require_once $folder . '/rows.php';
					}
					if ( file_exists( $folder . '/tables.php' ) ) {
						require_once $folder . '/tables.php';
					}
					try {
						$this->model_extension_pavothemer_sample->installSQL( $query );
					} catch (Exception $e) {
						$results['error'] = $e->getMessage();
						$results['status'] = false;
					}
					// import layout settings
					$demo_store_id = isset( $this->session->data['pavo_demo_store_id'] ) ? $this->session->data['pavo_demo_store_id'] : 0;
					$mapping_data = $this->model_extension_pavothemer_sample->importLayouts( $demo_store_id, $profile );
					$this->model_extension_pavothemer_sample->mappingMenu($mapping_data);
				}
			}
		}

		if ( ! empty( $results['error'] ) && isset( $this->session->data['pavo_mass_install'] ) ) {
			unset( $this->session->data['pavo_mass_install'] );
		}

		// success
		if ( ! isset( $results['error'] ) ) {
			$results['progress_percent'] = 80;
			$results['text'] = $this->language->get( 'entry_downloading_images' );
			$results['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/livedownloadimages', 'user_token=' . $this->session->data['user_token'], true ) );
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $results ) );
	}

	/**
	 * live download images
	 */
	public function livedownloadimages() {
		$this->load->language( 'extension/module/pavothemer' );
		$sampleHelper = PavoThemerSampleHelper::instance( $this->config->get( 'config_theme' ) );
		$response = array();
		if ( ! empty( $this->session->data['pavo_demo_dir'] ) ) {
			$images_file = $this->session->data['pavo_demo_dir'] . '/images.json';
			$data = file_exists( $images_file ) ? json_decode( file_get_contents( $images_file ), true ) : array();
			$images_error = array();
			if ( $data ) {
				$url = ! empty( $data['url'] ) ? $data['url'] : false;
				$images = ! empty( $data['images'] ) ? $data['images'] : array();
				foreach ( $images as $size => $files ) {
					$sizes = explode( 'x', $size );
					foreach ( $files as $file ) {
						$name = basename( $file );
						$image_url = $url . 'image/catalog/' . $name;
						$image_file = DIR_IMAGE . 'catalog/' . $file;
						if ( file_exists( $image_file ) ) continue;
						// download demo image
						if ( defined( 'PAVOTHEMER_DOWN_DEMO_IMAGE' ) && PAVOTHEMER_DOWN_DEMO_IMAGE ) {
							$status = PavothemerApiHelper::get( $image_url, array(
										'filename'			=> $image_file
									) );
							if ( $status !== true ) {
								$images_error[] = $image_url;
							}
						} else if ( isset( $sizes[0], $sizes[1] ) ) {
							// create placeholder image
							$sampleHelper->createImage( $sizes[0], $sizes[1], 'eeeeee', 'ffffff', $image_file );
						}
					}
				}
			}
		}

		if ( ! empty( $images_error ) ) {
			$response['error'] = $this->language->get( 'entry_images_error' );
		} else {
			if (isset( $this->session->data['pavo_mass_install'] )) {
				$response['progress_percent'] = 90;
				$response['text'] = $this->language->get( 'entry_download_languages_text' );
				$response['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/liveDownloadLanguages', 'user_token=' . $this->session->data['user_token'], true ) );
			} else {
				$response['progress_percent'] = 100;
				$response['success'] = $this->language->get( 'entry_import_success_text' );
				$response['refresh'] = str_replace( '&amp;', '&', $this->url->link('marketplace/modification/refresh', 'user_token=' . $this->session->data['user_token'], true ) );

				$this->removeDir( $this->session->data['pavo_demo_dir'] );

				// delete session
				unset( $this->session->data['pavo_demo'] );
				unset( $this->session->data['pavo_demo_dir'] );
			}
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * live download languages
	 */
	public function liveDownloadLanguages() {
		$this->load->model( 'extension/pavothemer/sample' );
		$this->load->language( 'extension/module/pavothemer' );
		$demoSample = $this->getSampleData();
		if (!empty($demoSample['languages']) && is_array($demoSample['languages'])) {
			foreach ($demoSample['languages'] as $lang) {
				$file = $this->session->data['pavo_demo_dir'] . $lang . '.ocmod.zip';
				if ( ! file_exists( $file ) && $file ) {
					$request = PavothemerApiHelper::post( PAVOTHEMER_LANG_API, array(
							'filename'			=> $file,
							'body'				=> array(
								'lang'				=> $lang,
								'theme'				=> $this->config->get( 'config_theme' ),
								'purchased_codes'	=> $this->config->get( 'pavothemer_purchased_codes' ),
								'debug'				=> PAVOTHEMER_DEBUG_MODE ? 1 : 0
							)
						) );

					$response = ! empty( $request['response'] ) ? $request['response'] : array();
					$body = ! empty( $request['body'] ) ? $request['body'] : '';

					if ( ! isset( $response['code'] ) || $response['code'] !== 200 ) {
						$results['error'] = sprintf($this->language->get( 'text_language_no_found' ), $lang);
					} else if ( $body !== true ) {
						$body = json_decode( $body, true );
						if ( isset( $body['error'], $body['message'] ) ) {
							$results['error'] = $body['message'];
						}
					} else {
						$this->model_extension_pavothemer_sample->installModule( $file );
					}
				}
			}
		}

		$this->removeDir( $this->session->data['pavo_demo_dir'] );

		// delete session
		unset( $this->session->data['pavo_demo'] );
		unset( $this->session->data['pavo_demo_dir'] );

		$response['progress_percent'] = 100;
		$response['success'] = $this->language->get( 'entry_import_success_text' );
		$response['refresh'] = str_replace( '&amp;', '&', $this->url->link('marketplace/modification/refresh', 'user_token=' . $this->session->data['user_token'], true ) );

		if ( ! empty( $results['error'] ) && isset( $this->session->data['pavo_mass_install'] ) ) {
			unset( $this->session->data['pavo_mass_install'] );
		}
		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * mass install demo data
	 */
	public function massinstall() {
		$this->load->model( 'extension/pavothemer/backup' );
		$this->session->data['pavo_mass_install'] = true;
		// start live install
		$this->liveinstall( true );
	}

	/**
	 * remove dir and all files inside it
	 */
	public function removeDir( $dir = '' ) {
		$paths = scandir( $dir );
		// $paths = glob( $dir . '*' );
		$paths = array_diff( $paths, array( '.', '..' ) );
		if ( empty( $paths ) ) {
			rmdir( $dir );
		}
		foreach ( $paths as $path ) {
			$path = $dir . $path;
			if ( is_file( $path ) ) {
				@unlink( $path );
			} else if ( is_dir( $path ) ) {
				$subs = scandir( $path );
				$subs = array_diff( $subs, array( '.', '..' ) );
				if ( empty( $subs ) ) {
					rmdir( $path );
				} else {
					$this->removeDir( $path );
				}
			}
		}
	}

	public function uploadProfile() {
		unset( $this->session->data['pavo_importing_profile'] );
		$this->load->language( 'extension/module/pavothemer' );
		if ( isset( $this->request->files['import'] ) ) {
			$status = false;
			$name = isset( $this->request->files['import']['name'] ) ? $this->request->files['import']['name'] : '';
			$exts = explode( '.' , $name );
			$ext = count( $exts ) > 1 ? end( $exts ) : 0;
			// upload has error
			if ( $this->request->files['import']['error'] != UPLOAD_ERR_OK ) {
				$status = true;
				$response['error'] = $this->language->get('error_upload_' . $this->request->files['import']['error']);
			}
			if ( ! empty( $this->request->files['import']['tmp_name'] ) && is_file( $this->request->files['import']['tmp_name'] ) ) {
				// valid file upload
				if ( $this->request->files['import']['type'] !== 'application/zip' || $ext !== 'zip' ) {
					$status = false;
					$response['error'] = $this->language->get( 'error_upload_invalid_filetype' );
				}
			}

			if ( ! $status ) {
				$this->session->data['pavothemer_upload'] = $this->request->files['import']['name'];
				$file = DIR_UPLOAD . $this->session->data['pavothemer_upload'] . '.tmp';
				// remove old cache file if it already exists
				if ( file_exists( $file ) ) {
					unlink( $file );
				}
				if ( move_uploaded_file( $this->request->files['import']['tmp_name'], $file ) ) {
					$response['next']	= str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/unzipProfile', 'user_token=' . $this->session->data['user_token'], true ) );
					$response['text']	= $this->language->get( 'entry_upzip_export_text' );
					$response['progress_percent'] = 20;
				} else {
					$response['error']	= $this->language->get( 'entry_upload_error_text' );

				}
			}
		}
		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * unzip profile uploaded
	 */
	public function unzipProfile() {
		$this->load->language( 'extension/module/pavothemer' );
		$sampleHelper = PavoThemerSampleHelper::instance( $this->config->get( 'config_theme' ) );
		$file = ! empty( $this->session->data['pavothemer_upload'] ) ? DIR_UPLOAD . $this->session->data['pavothemer_upload'] . '.tmp' : false;
		if ( ! $file || ! file_exists( $file ) ){
			$response['status'] = false;
			$response['error'] 	= $this->language->get( 'error_find_not_found' );
		} else {
			$folder = $sampleHelper->extractProfile( $file );

			if ( is_dir( $folder ) ) {
				$this->session->data['pavo_importing_profile'] = basename( $folder );
				$response['text'] = $this->language->get( 'entry_importing_text' );
				$response['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/importThemeSettings', 'folder='. $this->session->data['pavo_importing_profile'] .'&user_token=' . $this->session->data['user_token'], true ) );
				$response['table'] = $this->sampleTable();
				$response['progress_percent'] = 30;
			} else {
				$response['error'] = $this->language->get( 'error_extract_' . $folder );
			}
		}
		unset( $this->session->data['pavothemer_upload'] );
		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * start import
	 */
	public function importThemeSettings() {
		// load model
		$this->load->model( 'extension/pavothemer/sample' );
		$this->load->model( 'setting/extension' );
		$this->load->language( 'extension/module/pavothemer' );

		$response = array();
		$sampleHelper = PavoThemerSampleHelper::instance( $this->config->get( 'config_theme' ) );
		$folder = ! empty( $this->request->request['folder'] ) ? $this->request->request['folder'] : $folder;
		if ( ! $folder && ! empty( $this->session->data['pavo_importing_profile'] ) ) {
			$folder = $this->session->data['pavo_importing_profile'];
		} else {
			$this->session->data['pavo_importing_profile'] = $folder;
		}

		$profile = $folder ? $sampleHelper->getProfile( $folder ) : array( 'layouts' => array(), 'themes' => array() );

		$status = $this->model_extension_pavothemer_sample->importThemeSettings( $profile );

		if ( $status ) {
			// $response['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/importModules', 'user_token=' . $this->session->data['user_token'], true ) );
			// $response['text'] = $this->language->get( 'entry_installing_module' );
			$response['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/importSQL', 'user_token=' . $this->session->data['user_token'], true ) );
			$response['text'] = $this->language->get( 'entry_installing_table' );
			if ( ! empty( $this->request->request['start'] ) ) {
				$this->session->data['pavo_import_start_theme'] = true;
				$response['progress_percent'] = 30;
			} else {
				$this->session->data['pavo_import_start_theme'] = 35;
			}
		} else {
			$response['error'] = $this->language->get( 'error_import_theme' );
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * import sql
	 */
	public function importSQL() {
		$this->load->language( 'extension/module/pavothemer' );
		$this->load->model( 'extension/pavothemer/sample' );
		$sampleHelper = PavoThemerSampleHelper::instance( $this->config->get( 'config_theme' ) );
		$folder = ! empty( $this->session->data['pavo_importing_profile'] ) ? $this->session->data['pavo_importing_profile'] : '';
		$query = array();
		if ( file_exists( $sampleHelper->sampleDir.'profiles/' . $folder . '/rows.php' ) ) {
			require_once $sampleHelper->sampleDir.'profiles/' . $folder . '/rows.php';
		}
		if ( file_exists( $sampleHelper->sampleDir.'profiles/' . $folder . '/tables.php' ) ) {
			require_once $sampleHelper->sampleDir.'profiles/' . $folder . '/tables.php';
		}
		$status = $this->model_extension_pavothemer_sample->installSQL( $query );
		$response = array();

		if ( $status ) {
			$response['text'] = $this->language->get( 'entry_importing_layout' );
			$response['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/importLayouts', 'user_token=' . $this->session->data['user_token'], true ) );
			if ( ! empty( $this->session->data['pavo_import_start_theme'] ) && $this->session->data['pavo_import_start_theme'] ) {
				$response['progress_percent'] = 50;
			} else {
				$response['progress_percent'] = 50;
			}
		} else {
			$response['error'] = $this->language->get( 'error_import_table' );
		}
		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * import layouts
	 */
	public function importLayouts() {
		$this->load->model( 'extension/pavothemer/sample' );
		$this->load->language( 'extension/module/pavothemer' );
		$sampleHelper = PavoThemerSampleHelper::instance( $this->config->get( 'config_theme' ) );
		$folder = ! empty( $this->session->data['pavo_importing_profile'] ) ? $this->session->data['pavo_importing_profile'] : '';
		$profile = $folder ? $sampleHelper->getProfile( $folder ) : array( 'layouts' => array(), 'themes' => array() );
		$mapping_data = $this->model_extension_pavothemer_sample->importLayouts( false, $profile );
		$this->model_extension_pavothemer_sample->mappingMenu($mapping_data);

		$response['text'] = $this->language->get( 'entry_downloading_images' );
		$response['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/importDownloadImages', 'user_token=' . $this->session->data['user_token'], true ) );
		if ( ! empty( $this->session->data['pavo_import_start_theme'] ) && $this->session->data['pavo_import_start_theme'] ) {
			$response['progress_percent'] = 70;
		} else {
			$response['progress_percent'] = 60;
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * download images
	 */
	public function importDownloadImages() {
		$this->load->language( 'extension/module/pavothemer' );
		$sampleHelper = PavoThemerSampleHelper::instance( $this->config->get( 'config_theme' ) );
		$folder = ! empty( $this->session->data['pavo_importing_profile'] ) ? $this->session->data['pavo_importing_profile'] : '';
		$status = $sampleHelper->downloadImages( $folder );
		$response = array();

		if ( $status ) {
			$response = array(
				'success'	=> $this->language->get('entry_export_success_text'),
				'refresh' => str_replace( '&amp;', '&', $this->url->link('marketplace/modification/refresh', 'user_token=' . $this->session->data['user_token'], true ) )
			);
			$response['progress_percent'] = 100;
			if ( ! empty( $this->request->request['start'] ) ) {
				unset( $this->request->request['start'] );
			}
		} else {
			$response['error'] = $this->language->get( 'error_import_table' );
		}
		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * start export
	 */
	public function startexport() {
		$this->load->model( 'user/user' );
		$this->load->language( 'extension/module/pavothemer' );

		$theme = $this->config->get( 'config_theme' ) ? $this->config->get( 'config_theme' ) : 'default';
		$store_id = $this->config->get( 'config_store_id' );
		$sampleHelper = PavoThemerSampleHelper::instance( $theme );

		$response = array();
		// create backup folder
		$folder = $sampleHelper->makeDir();
		if ( ! $folder ) {
			$response['error'] = $this->language->get( 'error_permission' ) . '<strong> ' . DIR_CATALOG . 'view/theme/'.$theme.'/sample/profiles</strong>';
		} else {
			// save folder had been created as session
			$this->session->data['pavo_backup_profile'] = $folder;
			$user_id = $this->session->data['user_id'];
			$user = $this->model_user_user->getUser( $user_id );
			$infoData = array(
					'email' 			=> $user['email'],
					'theme' 			=> $theme,
					'store_id' 			=> $store_id,
					'url'				=> $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG
				);

			$write = $sampleHelper->write( $infoData, $folder, 'info' );
			if ( ! $write ) {
				unset( $this->session->data['pavo_backup_profile'] );
				$response['error'] = $this->language->get( 'error_permission' ) . '<strong> ' . DIR_CATALOG . 'view/theme/'.$theme.'/sample/profiles</strong>';
			} else {
				$response = array(
						'text'		=> $this->language->get( 'entry_exporting_theme_config' ),
						'next'		=> str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/exportThemeSettings', 'user_token=' . $this->session->data['user_token'], true ) ),
						'progress_percent'	=> 32
					);
			}
		}
		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * export theme settings
	 */
	public function exportThemeSettings() {
		$this->load->model( 'extension/pavothemer/sample' );
		$this->load->language( 'extension/module/pavothemer' );
		// export store settings
		$theme = $this->config->get( 'config_theme' );
		$sampleHelper = PavoThemerSampleHelper::instance( $theme );
		$folder = ! empty( $this->session->data['pavo_backup_profile'] ) ? $this->session->data['pavo_backup_profile'] : '';
		$themeSettings = $this->model_extension_pavothemer_sample->getThemeSettings( $theme );
		$status = $sampleHelper->write( $themeSettings, $folder, 'theme_settings' );

		$response = array();
		if ( ! $status ) {
			$response['error'] = $this->language->get( 'error_permission' ) . '<strong> ' . DIR_CATALOG . 'view/theme/'.$theme.'</strong>';
		} else {
			$response = array(
				'status'	=> $status,
				'progress_percent'	=> 40,
				'text'		=> $this->language->get( 'entry_extension_module_text' ),
				'next'		=> str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/exportExtensions', 'user_token=' . $this->session->data['user_token'], true ) )
			);
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * export modules
	 */
	public function exportExtensions() {
		$this->load->model( 'extension/pavothemer/sample' );
		$this->load->language( 'extension/module/pavothemer' );
		// export store settings
		$theme = $this->config->get( 'config_theme' );
		$sampleHelper = PavoThemerSampleHelper::instance( $theme );
		$extensions = $this->model_extension_pavothemer_sample->getExtensionModules( $theme );
		$folder = ! empty( $this->session->data['pavo_backup_profile'] ) ? $this->session->data['pavo_backup_profile'] : '';
		$status = $sampleHelper->write( $extensions, $folder, 'extensions' );

		$response = array();
		if ( ! $status ) {
			$response['error'] = $this->language->get( 'error_export_module' );
		} else {
			$response['progress_percent'] = 50;
			$response['text'] = $this->language->get( 'entry_exporting_layout_text' );
			$response['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/exportLayoutSettings', 'user_token=' . $this->session->data['user_token'], true ) );
		}

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * export layout settings
	 */
	public function exportLayoutSettings() {
		$this->load->model( 'design/layout' );
		$this->load->language( 'extension/module/pavothemer' );
		$theme = $this->config->get( 'config_theme' );
		$sampleHelper = PavoThemerSampleHelper::instance( $theme );
		$folder = ! empty( $this->session->data['pavo_backup_profile'] ) ? $this->session->data['pavo_backup_profile'] : '';
		$layouts = $this->model_design_layout->getLayouts();
		foreach ( $layouts as $k => $layout ) {
			$layout['layout_modules'] = $this->model_design_layout->getLayoutModules( $layout['layout_id'] );
			$layout['layout_routes'] = $this->model_design_layout->getLayoutRoutes( $layout['layout_id'] );
			$layouts[$k] = $layout;
		}

		$status = $sampleHelper->write( $layouts, $folder, 'layouts' );

		if ( $status ) {
			$response['text'] = $this->language->get( 'entry_exporting_skins_text' );
			$response['progress_percent'] = 60;
			$response['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/exportSkinOptions', 'user_token=' . $this->session->data['user_token'], true ) );
		} else {
			$response['error'] = $this->language->get( 'error_export_layout_text' );
		}
		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * export skins
	 */
	public function exportSkinOptions() {
		$this->load->language( 'extension/module/pavothemer' );
		$theme = $this->config->get( 'config_theme' );
		$skinDefault = $this->config->get( 'pavothemer_default_skin' );
		$folder = ! empty( $this->session->data['pavo_backup_profile'] ) ? $this->session->data['pavo_backup_profile'] : '';

		$is_nicke = defined( 'IMAGE_URL' ) && IMAGE_URL;
		$sampleHelper = PavoThemerSampleHelper::instance( $theme );
		$themeHelper = PavoThemerHelper::instance( $theme );

		try {
			$path = $sampleHelper->sampleDir.'profiles/'.$folder.'/skins/';
			$sampleHelper->createdirectory( $path );
			if ( !$is_nicke ) {
				$allSkins = $themeHelper->getSkins();
				foreach ( $allSkins as $skin ) {
					$skinName = ! empty($skin['value']) ? $skin['value'] : false;
					$this->exportSingleSkin( $skinName, $folder, $sampleHelper );
				}
			} elseif ( $is_nicke && $skinDefault ) {
				$this->exportSingleSkin( $skinDefault, $folder, $sampleHelper );
			}
			$response['text'] = $this->language->get( 'entry_exporting_table_text' );
			$response['progress_percent'] = 70;
			$response['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/exportTables', 'user_token=' . $this->session->data['user_token'], true ) );
		} catch (Exception $e) {
			$response['error'] = $e->getMessage();
		}
		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	public function exportSingleSkin( $skin = '', $profile = '', $sampleHelper = null ) {
		$path = $sampleHelper->sampleDir.'profiles/'.$profile.'/skins/';
		$skinFile = $sampleHelper->themePath.'stylesheet/skins/'.$skin.'.json';
		if (!file_exists($skinFile)) {
			throw new Exception( $this->language->get('entry_file_not_found') );
		} else if (!copy($skinFile, $path.$skin.'.json')) {
			throw new Exception( $this->language->get('entry_export_skin_error') );
		}
		return true;
	}

	/**
	 * export tables
	 */
	public function exportTables() {
		$this->load->model( 'extension/pavothemer/sample' );
		$this->load->language( 'extension/module/pavothemer' );

		$theme = $this->config->get( 'config_theme' );
		$sampleHelper = PavoThemerSampleHelper::instance( $theme );
		// tables need to export is defined in theme/sample/profiles/*/tables.json
		$folder = ! empty( $this->session->data['pavo_backup_profile'] ) ? $this->session->data['pavo_backup_profile'] : '';
		$sql = $this->model_extension_pavothemer_sample->exportTables();

		$status = $sampleHelper->exportSQL( $sql, $folder );

		if ( $status ) {
			// start backup all database
			$this->load->model( 'extension/pavothemer/backup' );
			$sqlFile = $sampleHelper->sampleDir . 'profiles/' . $folder . '/demo.sql';
			$status = $this->model_extension_pavothemer_backup->backup( '*', $sqlFile, false );

			if ( $status === true ) {
				$response['progress_percent'] = 90;
				$response['next'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/exportImages', 'user_token=' . $this->session->data['user_token'], true ) );
				$response['text'] = $this->language->get( 'entry_export_images_text' );
			} else {
				$response['error'] = sprintf( $this->language->get( 'entry_backup_error' ), $sqlFile );
			}
		} else {
			$response['error'] = $this->language->get( 'error_export_table_text' );
		}
		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * export images
	 */
	public function exportImages() {
		$this->load->language( 'extension/module/pavothemer' );

		$theme = $this->config->get( 'config_theme' );
		$sampleHelper = PavoThemerSampleHelper::instance( $theme );

		$folder = ! empty( $this->session->data['pavo_backup_profile'] ) ? $this->session->data['pavo_backup_profile'] : '';

		$url = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
		// $images['images'] = ! empty( $this->session->data['pavo_backbup_images'] ) ? $this->session->data['pavo_backbup_images'] : array();
		$status = $sampleHelper->exportImages( $url, $folder ); //$images,

		$response = array(
			'status'	=> $status,
			'table'		=> $this->sampleTable()
		);

		if ( $status ) {
			$response['progress_percent'] = 100;
			$response['success'] = $this->language->get( 'entry_export_success_text' );
		} else {
			$response['error'] = $this->language->get( 'error_export_images_text' );
		}
		unset( $this->session->data['pavo_backup_profile'] );
		unset( $this->session->data['pavo_backbup_images'] );

		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode( $response ) );
	}

	/**
	 * delete backup sample
	 */
	public function delete() {
		$sample = ! empty( $this->request->post['sample'] ) ? $this->request->post['sample'] : '';
		$theme = ! empty( $this->request->post['theme'] ) ? $this->request->post['theme'] : '';
		$this->load->language( 'extension/module/pavothemer' );
		$sampleHelper = PavoThemerSampleHelper::instance( $theme );

		$status = $sampleHelper->delete( $sample );
		$response = array(
				'status'	=> $status
			);

		if ( $status ) {
			$response['success'] = $this->language->get( 'entry_delete_text' ) . ' <strong>' . $sample . '</strong> ' . $this->language->get( 'entry_successfully_text' );
		} else {
			$response['error'] = $this->language->get( 'error_permission' ) . ': <strong>' . DIR_CATALOG . 'view/theme/'.$theme.'/sample</strong>';
		}

		if ( $this->isAjax() ) {
			$response['table'] = $this->sampleTable();
			$this->response->addHeader( 'Content-Type: application/json' );
			$this->response->setOutput( json_encode( $response ) );
		} else {
			if ( $status ) {
				$this->session->data['success'] = $this->language->get( 'entry_delete_text' ) . ' <strong>' . $sample . '</strong> ' . $this->language->get( 'entry_successfully_text' );
			} else {
				$this->session->data['error'] = $this->language->get( 'error_permission' );
			}
			$this->response->redirect( str_replace(
											'&amp;',
											'&',
											$this->url->link('extension/module/pavothemer/export', 'user_token=' . $this->session->data['user_token'], true )
										) );
			exit();
		}
	}

	/**
	 * download export
	 */
	public function download() {

		$profile = ! empty( $this->request->get['profile'] ) ? $this->request->get['profile'] : false;
		$file = false;
		if ( $profile ) {
			$sampleHelper = PavoThemerSampleHelper::instance( $this->config->get( 'config_theme' ) );
			$file = $sampleHelper->zipProfile( $profile );
		}

		if ( $this->isAjax() ) {
			$response = array(
					'status' 	=> $file ? true : false,
					'url'		=> str_replace(
											'&amp;',
											'&',
											$this->url->link('extension/module/pavothemer/download', 'profile=' . $profile . '&user_token=' . $this->session->data['user_token'], true )
										)
				);
			$this->response->addHeader( 'Content-Type: application/json' );
			$this->response->setOutput( json_encode( $response ) );
		} else {

			if ( ! $file ) {
				$this->response->redirect( str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/tools', 'user_token=' . $this->session->data['user_token'], true ) ) );
				exit();
			} else {
				header('Content-Type: application/zip');
				header('Content-Disposition: attachment; filename="'. basename( $file ) .'"');
				header('Content-Length: '.filesize( $file ) );
				readfile( $file );
				unlink( $file );
				exit();
			}
		}
	}

	/**
	 * tools
	 */
	public function tools() {
		// $string = 'INSERT INTO `' . DB_PREFIX . 'pavobuilder` (`module_uniqid_id`, `settings`) VALUES ("5a6a85e89ef93", "[{\"settings\":{\"uniqid_id\":\"je54rz8o\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"inner_border_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-color\"},\"inner_border_width\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-width\"},\"inner_border_style\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-style\"},\"inner_padding_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-top\"},\"inner_padding_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-bottom\"},\"inner_padding_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-left\"},\"inner_padding_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-right\"},\"inner_border_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-top\"},\"inner_border_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-bottom\"},\"inner_border_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-left\"},\"inner_border_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-right\"}},\"element\":\"pa_row\"},\"columns\":[{\"settings\":{\"element\":\"pa_column\",\"uniqid_id\":\"je54rz8q\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_background_color\":{\"selectors\":\".pa-column-inner\",\"css_attr\":\"background-color\"}}},\"responsive\":{\"lg\":{\"cols\":12,\"styles\":{\"width\":100}},\"md\":{\"cols\":12},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}},\"elements\":[{\"settings\":{\"uniqid_id\":\"je54s54n\",\"selectors\":[],\"size\":\"30\"},\"mask\":[],\"editing\":false,\"widget\":\"pa_spacing\",\"responsive\":{\"lg\":{\"settings\":{\"layout_onion\":{\"border_style\":\"none\"},\"selectors\":[]}}}}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_column\"}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_row\",\"responsive\":{\"lg\":{\"cols\":12},\"md\":{\"cols\":12},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}}},{\"settings\":{\"uniqid_id\":\"je5ajzd4\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"inner_border_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-color\"},\"inner_border_width\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-width\"},\"inner_border_style\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-style\"},\"inner_padding_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-top\"},\"inner_padding_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-bottom\"},\"inner_padding_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-left\"},\"inner_padding_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-right\"},\"inner_border_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-top\"},\"inner_border_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-bottom\"},\"inner_border_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-left\"},\"inner_border_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-right\"}},\"element\":\"pa_row\",\"layout\":\"boxed\",\"parallax\":\"0\",\"inner_border_style\":\"none\",\"inner_background_color\":\"#ffffff\",\"inner_border_bottom\":\"1px solid #ddd\",\"inner_padding_top\":\"20\",\"inner_padding_bottom\":\"20\",\"inner_padding_left\":\"30\",\"inner_padding_right\":\"30\",\"extra_class\":\"text-center\"},\"columns\":[{\"settings\":{\"element\":\"pa_column\",\"uniqid_id\":\"je5ajzd6\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_background_color\":{\"selectors\":\".pa-column-inner\",\"css_attr\":\"background-color\"}}},\"responsive\":{\"lg\":{\"cols\":2,\"styles\":{\"width\":\"20.24040\"}},\"md\":{\"cols\":12},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}},\"elements\":[{\"settings\":{\"uniqid_id\":\"jef2yjxy\",\"selectors\":[],\"src\":\"catalog\\/demo\\/logo.jpg\",\"image_size\":\"101x43\",\"link\":\"#\",\"alt\":\"logo-footer\",\"extra_class\":\"text-center\"},\"mask\":[],\"editing\":false,\"widget\":\"pa_single_image\",\"responsive\":{\"lg\":{\"settings\":{\"layout_onion\":{\"border_style\":\"none\"},\"selectors\":[]}}}}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_column\"},{\"settings\":{\"elements\":[],\"uniqid_id\":\"je5ak4rs\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_background_color\":{\"selectors\":\".pa-column-inner\",\"css_attr\":\"background-color\"}}},\"responsive\":{\"lg\":{\"cols\":6,\"styles\":{\"width\":\"55.65187\"}},\"md\":{\"cols\":12},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}},\"elements\":[{\"settings\":{\"selectors\":[],\"en-gb\":{\"menu_type\":\"hor\"},\"ar\":{\"menu_type\":\"hor\"},\"items\":[{\"languages\":{\"en-gb\":{\"text_link\":\"Home\",\"url_link\":\"#\"},\"ar\":{\"text_link\":\"Home\",\"url_link\":\"#\"}}},{\"languages\":{\"en-gb\":{\"text_link\":\"Company\",\"url_link\":\"#\"},\"ar\":{\"text_link\":\"Company\",\"url_link\":\"#\"}}},{\"languages\":{\"en-gb\":{\"text_link\":\"Affiliates\",\"url_link\":\"#\"},\"ar\":{\"text_link\":\"Affiliates\",\"url_link\":\"#\"}}},{\"languages\":{\"en-gb\":{\"text_link\":\"Support\",\"url_link\":\"#\"},\"ar\":{\"text_link\":\"Support\",\"url_link\":\"#\"}}},{\"languages\":{\"en-gb\":{\"text_link\":\"Privacy\",\"url_link\":\"#\"},\"ar\":{\"text_link\":\"Privacy\",\"url_link\":\"#\"}}},{\"languages\":{\"en-gb\":{\"text_link\":\"FAQ\'s\",\"url_link\":\"#\"},\"ar\":{\"text_link\":\"FAQ\'s\",\"url_link\":\"#\"}}},{\"languages\":{\"en-gb\":{\"text_link\":\"Contact\",\"url_link\":\"#\"},\"ar\":{\"text_link\":\"Contact\",\"url_link\":\"#\"}}}],\"uniqid_id\":\"jddzylto\"},\"mask\":[],\"editing\":false,\"widget\":\"pa_custom_menu\",\"responsive\":{\"lg\":{\"settings\":{\"layout_onion\":{\"padding_top\":\"10\",\"padding_right\":\"0\",\"padding_bottom\":\"10\",\"padding_left\":\"0\",\"border_style\":\"none\"},\"selectors\":[]}}}}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_column\"},{\"settings\":{\"elements\":[],\"uniqid_id\":\"je5ak4ru\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_background_color\":{\"selectors\":\".pa-column-inner\",\"css_attr\":\"background-color\"}}},\"responsive\":{\"lg\":{\"cols\":3,\"styles\":{\"width\":\"24.10575\"}},\"md\":{\"cols\":12},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}},\"elements\":[{\"settings\":{\"selectors\":[],\"facebook\":\"1\",\"link_facebook\":\"#\",\"youtube\":\"1\",\"link_youtube\":\"#\",\"instagram\":\"1\",\"link_instagram\":\"#\",\"twitter\":\"1\",\"link_twitter\":\"#\",\"reddit\":\"1\",\"link_reddit\":\"#\",\"pinterest\":\"0\",\"link_pinterest\":\"#\",\"google_plus\":\"0\",\"link_google_plus\":\"#\",\"flickr\":\"0\",\"vine\":\"0\",\"uniqid_id\":\"jcva6pkz\"},\"mask\":[],\"editing\":false,\"widget\":\"pa_social_network\",\"responsive\":{\"lg\":{\"settings\":{\"layout_onion\":[],\"selectors\":[]}}}}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_column\"}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_row\",\"responsive\":{\"lg\":{\"cols\":12,\"settings\":{\"no_space\":\"0\",\"styles\":{\"border_style\":\"none\"},\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"inner_border_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-color\"},\"inner_border_width\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-width\"},\"inner_border_style\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-style\"},\"inner_padding_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-top\"},\"inner_padding_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-bottom\"},\"inner_padding_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-left\"},\"inner_padding_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-right\"},\"inner_border_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-top\"},\"inner_border_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-bottom\"},\"inner_border_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-left\"},\"inner_border_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-right\"}}}},\"md\":{\"cols\":12,\"settings\":{\"no_space\":\"0\",\"styles\":{\"border_style\":\"none\"},\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"inner_border_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-color\"},\"inner_border_width\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-width\"},\"inner_border_style\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-style\"},\"inner_padding_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-top\"},\"inner_padding_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-bottom\"},\"inner_padding_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-left\"},\"inner_padding_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-right\"},\"inner_border_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-top\"},\"inner_border_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-bottom\"},\"inner_border_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-left\"},\"inner_border_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-right\"}}}},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}}},{\"settings\":{\"uniqid_id\":\"jcvbrill\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"inner_border_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-color\"},\"inner_border_width\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-width\"},\"inner_border_style\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-style\"},\"inner_padding_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-top\"},\"inner_padding_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-bottom\"},\"inner_padding_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-left\"},\"inner_padding_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-right\"},\"inner_border_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-top\"},\"inner_border_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-bottom\"},\"inner_border_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-left\"},\"inner_border_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-right\"}},\"element\":\"pa_row\",\"layout\":\"boxed\"},\"columns\":[{\"settings\":{\"element\":\"pa_column\",\"uniqid_id\":\"jcvbriln\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_background_color\":{\"selectors\":\".pa-column-inner\",\"css_attr\":\"background-color\"}}},\"responsive\":{\"lg\":{\"cols\":12,\"styles\":{\"width\":100}},\"md\":{\"cols\":12},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}},\"elements\":[{\"settings\":{\"uniqid_id\":\"jcvbrkzn\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"inner_border_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-color\"},\"inner_border_width\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-width\"},\"inner_border_style\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-style\"},\"inner_padding_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-top\"},\"inner_padding_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-bottom\"},\"inner_padding_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-left\"},\"inner_padding_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-right\"},\"inner_border_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-top\"},\"inner_border_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-bottom\"},\"inner_border_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-left\"},\"inner_border_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-right\"}}},\"mask\":[],\"editing\":false,\"widget\":\"pa_row\",\"row\":{\"settings\":{\"element\":\"pa_row\",\"uniqid_id\":\"jcvbrkzk\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"inner_border_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-color\"},\"inner_border_width\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-width\"},\"inner_border_style\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-style\"},\"inner_padding_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-top\"},\"inner_padding_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-bottom\"},\"inner_padding_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-left\"},\"inner_padding_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-right\"},\"inner_border_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-top\"},\"inner_border_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-bottom\"},\"inner_border_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-left\"},\"inner_border_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-right\"}}},\"columns\":[{\"settings\":{\"element\":\"pa_column\",\"uniqid_id\":\"jcvbrkzm\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_background_color\":{\"selectors\":\".pa-column-inner\",\"css_attr\":\"background-color\"}}},\"responsive\":{\"lg\":{\"cols\":4,\"styles\":{\"width\":\"35.30445\"}},\"md\":{\"cols\":4,\"styles\":{\"width\":\"34.71877\"}},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}},\"elements\":[{\"settings\":{\"selectors\":[],\"en-gb\":{\"content\":\"<div class=\\\"footer-contact\\\">\\r\\n\\t<h5>Contact Us<\\/h5>\\r\\n\\t<address>\\r\\n\\t\\t<div class=\\\"contact-info\\\">\\r\\n\\t\\t\\t<i class=\\\"fa fa-map-marker\\\"><\\/i>\\r\\n\\t\\t\\t<span> Address: No 40 Baria Sreet 133\\/2 NewYork City, NY, United States<\\/span>\\r\\n\\t\\t<\\/div>\\r\\n\\t\\t<div class=\\\"contact-info\\\">\\r\\n\\t\\t\\t<i class=\\\"fa fa-map-marker\\\"><\\/i>\\r\\n\\t\\t\\t<span>Phone: + 84 123 456 888<\\/span>\\r\\n\\t\\t<\\/div>\\r\\n\\t\\t<div class=\\\"contact-info\\\">\\t\\r\\n\\t\\t\\t<i class=\\\"fa fa-envelope-o\\\"><\\/i>\\r\\n\\t\\t\\t<span>Contact@company.com <\\/span>\\r\\n\\t\\t<\\/div>\\r\\n\\t<\\/address>\\r\\n<\\/div>\"},\"ar\":{\"content\":\"<div class=\\\"footer-contact\\\">\\r\\n\\t<h5>Contact Us<\\/h5>\\r\\n\\t<address>\\r\\n\\t\\t<div class=\\\"contact-info\\\">\\r\\n\\t\\t\\t<i class=\\\"fa fa-map-marker\\\"><\\/i>\\r\\n\\t\\t\\t<span> Address: No 40 Baria Sreet 133\\/2 NewYork City, NY, United States<\\/span>\\r\\n\\t\\t<\\/div>\\r\\n\\t\\t<div class=\\\"contact-info\\\">\\r\\n\\t\\t\\t<i class=\\\"fa fa-map-marker\\\"><\\/i>\\r\\n\\t\\t\\t<span>Phone: + 84 123 456 888<\\/span>\\r\\n\\t\\t<\\/div>\\r\\n\\t\\t<div class=\\\"contact-info\\\">\\t\\r\\n\\t\\t\\t<i class=\\\"fa fa-envelope-o\\\"><\\/i>\\r\\n\\t\\t\\t<span>Contact@company.com <\\/span>\\r\\n\\t\\t<\\/div>\\r\\n\\t<\\/address>\\r\\n<\\/div>\"},\"uniqid_id\":\"jcve2keg\"},\"mask\":[],\"editing\":false,\"widget\":\"pa_text\",\"responsive\":{\"lg\":{\"settings\":{\"layout_onion\":[],\"selectors\":[]}}}}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_column\"},{\"settings\":{\"elements\":[],\"uniqid_id\":\"jcvbrq20\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_background_color\":{\"selectors\":\".pa-column-inner\",\"css_attr\":\"background-color\"}}},\"responsive\":{\"lg\":{\"cols\":2,\"styles\":{\"width\":\"14.46673\"}},\"md\":{\"cols\":2,\"styles\":{\"width\":\"15.28024\"}},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}},\"elements\":[{\"settings\":{\"selectors\":{\"text_link_color\":{\"selectors\":\"a\",\"css_attr\":\"color\"}},\"title\":\"true\",\"footer_title\":\"information\",\"uniqid_id\":\"jcvcc22f\"},\"mask\":[],\"editing\":false,\"widget\":\"pa_block_links\",\"responsive\":{\"lg\":{\"settings\":{\"layout_onion\":[],\"selectors\":[]}}}}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_column\"},{\"settings\":{\"elements\":[],\"uniqid_id\":\"jcvbrq22\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_background_color\":{\"selectors\":\".pa-column-inner\",\"css_attr\":\"background-color\"}}},\"responsive\":{\"lg\":{\"cols\":2,\"styles\":{\"width\":\"19.91194\"}},\"md\":{\"cols\":2,\"styles\":{\"width\":\"19.12916\"}},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}},\"elements\":[{\"settings\":{\"selectors\":{\"text_link_color\":{\"selectors\":\"a\",\"css_attr\":\"color\"}},\"title\":\"true\",\"footer_title\":\"my_account\",\"uniqid_id\":\"jcvcc22i\"},\"mask\":[],\"editing\":false,\"widget\":\"pa_block_links\",\"responsive\":{\"lg\":{\"settings\":{\"layout_onion\":[],\"selectors\":[]}}}}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_column\"},{\"settings\":{\"elements\":[],\"uniqid_id\":\"jcvbrq24\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_background_color\":{\"selectors\":\".pa-column-inner\",\"css_attr\":\"background-color\"}}},\"responsive\":{\"lg\":{\"cols\":4,\"styles\":{\"width\":\"29.75806\"}},\"md\":{\"cols\":4,\"styles\":{\"width\":\"30.87084\"}},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}},\"elements\":[{\"settings\":{\"uniqid_id\":\"jcvcdw01\"},\"mask\":{\"module_id\":\"53\",\"name\":\"Our Newsletter\",\"code\":\"pavnewsletter\",\"type\":\"module\",\"icon\":\"fa fa-opencart\",\"group\":\"Pav Newsletter\",\"group_slug\":\"pavnewsletter\"},\"editing\":false,\"moduleCode\":\"pavnewsletter\",\"moduleId\":53,\"group\":\"pavnewsletter\",\"element_type\":\"module\",\"responsive\":{\"lg\":{\"settings\":{\"selectors\":[]}}}}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_column\"}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_row\",\"responsive\":{\"lg\":{\"settings\":{\"background_color\":\"#ffffff\",\"parallax\":\"0\",\"no_space\":\"0\",\"styles\":{\"padding_top\":\"50\",\"padding_right\":\"30\",\"padding_bottom\":\"30\",\"padding_left\":\"30\"},\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"heading_color\":{\"selectors\":\"h1,h2,h3,h4,h5,h6\",\"css_attr\":\"color\"}}}}}}}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_column\"}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_row\",\"responsive\":{\"lg\":{\"cols\":12,\"settings\":{\"parallax\":\"0\",\"no_space\":\"0\",\"styles\":[],\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"heading_color\":{\"selectors\":\"h1,h2,h3,h4,h5,h6\",\"css_attr\":\"color\"}}}},\"md\":{\"cols\":12},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}}},{\"settings\":{\"uniqid_id\":\"jcvf909n\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"inner_border_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-color\"},\"inner_border_width\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-width\"},\"inner_border_style\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-style\"},\"inner_padding_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-top\"},\"inner_padding_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-bottom\"},\"inner_padding_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-left\"},\"inner_padding_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-right\"},\"inner_border_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-top\"},\"inner_border_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-bottom\"},\"inner_border_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-left\"},\"inner_border_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-right\"}},\"element\":\"pa_row\",\"layout\":\"boxed\"},\"columns\":[{\"settings\":{\"element\":\"pa_column\",\"uniqid_id\":\"jcvf909p\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_background_color\":{\"selectors\":\".pa-column-inner\",\"css_attr\":\"background-color\"}}},\"responsive\":{\"lg\":{\"cols\":12,\"styles\":{\"width\":100}},\"md\":{\"cols\":12},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}},\"elements\":[{\"settings\":{\"uniqid_id\":\"jcvf93nu\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"inner_border_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-color\"},\"inner_border_width\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-width\"},\"inner_border_style\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-style\"},\"inner_padding_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-top\"},\"inner_padding_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-bottom\"},\"inner_padding_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-left\"},\"inner_padding_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-right\"},\"inner_border_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-top\"},\"inner_border_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-bottom\"},\"inner_border_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-left\"},\"inner_border_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-right\"}}},\"mask\":[],\"editing\":false,\"widget\":\"pa_row\",\"row\":{\"settings\":{\"element\":\"pa_row\",\"uniqid_id\":\"jcvf93nr\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"inner_border_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-color\"},\"inner_border_width\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-width\"},\"inner_border_style\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-style\"},\"inner_padding_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-top\"},\"inner_padding_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-bottom\"},\"inner_padding_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-left\"},\"inner_padding_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding-right\"},\"inner_border_top\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-top\"},\"inner_border_bottom\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-bottom\"},\"inner_border_left\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-left\"},\"inner_border_right\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"border-right\"}}},\"columns\":[{\"settings\":{\"element\":\"pa_column\",\"uniqid_id\":\"jcvf93nt\",\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_background_color\":{\"selectors\":\".pa-column-inner\",\"css_attr\":\"background-color\"}}},\"responsive\":{\"lg\":{\"cols\":12},\"md\":{\"cols\":12},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}},\"elements\":[{\"settings\":{\"selectors\":[],\"en-gb\":{\"content\":\"<div class=\\\"text-copyright text-center\\\">\\r\\n\\t\\t\\t\\t\\t\\t\\tCopyright \\u00a9 2017 - <a href=\\\"#\\\">Mesa<\\/a>. All rights reserved. Powered by Opencart\\t\\t\\t\\t\\t\\t<\\/div>\"},\"ar\":{\"content\":\"<div class=\\\"text-copyright text-center\\\">\\r\\n\\t\\t\\t\\t\\t\\t\\tCopyright \\u00a9 2017 - <a href=\\\"#\\\">Mesa<\\/a>. All rights reserved. Powered by Opencart\\t\\t\\t\\t\\t\\t<\\/div>\"},\"uniqid_id\":\"jcvfht9e\"},\"mask\":[],\"editing\":false,\"widget\":\"pa_text\",\"responsive\":{\"lg\":{\"settings\":{\"layout_onion\":[],\"selectors\":[]}}}}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_column\"}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_row\"}}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_column\"}],\"editing\":false,\"element_type\":\"widget\",\"widget\":\"pa_row\",\"responsive\":{\"lg\":{\"cols\":12,\"settings\":{\"parallax\":\"0\",\"no_space\":\"0\",\"styles\":{\"padding_top\":\"20\",\"padding_bottom\":\"20\"},\"selectors\":{\"background_color\":{\"css_attr\":\"background-color\"},\"inner_background_color\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"background-color\"},\"background_image\":{\"css_attr\":\"background-image\"},\"background_position\":{\"css_attr\":\"background-position\"},\"background_repeat\":{\"css_attr\":\"background-repeat\"},\"inner_padding\":{\"selectors\":\".pa-row-inner\",\"css_attr\":\"padding\"},\"heading_color\":{\"selectors\":\"h1,h2,h3,h4,h5,h6\",\"css_attr\":\"color\"}}}},\"md\":{\"cols\":12},\"sm\":{\"cols\":12},\"xs\":{\"cols\":12}}}]")';
		// echo $string; die();
		$this->document->addStyle( 'view/stylesheet/pavothemer/dist/tool.min.css' );
		$this->document->addScript( 'view/javascript/pavothemer/dist/pavothemer.min.js' );
		$this->document->addStyle('view/javascript/jquery/magnific/magnific-popup.css');
		$this->document->addScript('view/javascript/jquery/magnific/jquery.magnific-popup.min.js');

		$this->toolsForm( ! empty( $this->request->get['tab'] ) ? $this->request->get['tab'] : 'import' );
	}

	/**
	 * generate tool form
	 * @since 1.0
	 */
	public function toolsForm( $tab = 'import' ) {
		// load language file
		$this->load->language('extension/module/pavothemer');
		// load setting model
		$this->load->model( 'setting/setting' );
		/**
		 * breadcrumbs data
		 */
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text' => $this->language->get( 'text_home' ),
			'href' => $this->url->link( 'common/dashboard', 'user_token=' . $this->session->data['user_token'], true )
		);
		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get( 'menu_tool_text' ),
			'href'      => $this->url->link( 'extension/module/pavothemer/edit', 'user_token=' . $this->session->data['user_token'], 'SSL' ),
      		'separator' => ' :: '
   		);
		if ( ! empty( $this->session->data['success'] ) ) {
			$this->data['success'] = $this->session->data['success'];
			unset( $this->session->data['success'] );
		}

		$this->data['current_tab'] = $tab;
		$this->data['import_zip_ajax_url'] = $this->url->link( 'extension/module/pavothemer/uploadProfile', 'action=upload&user_token=' . $this->session->data['user_token'], 'SSL' );
		$this->data['import_ajax_url'] = $this->url->link( 'extension/module/pavothemer/uploadProfile', 'user_token=' . $this->session->data['user_token'], 'SSL' );
		$this->data['export_ajax_url'] = str_replace( '&amp;', '&', $this->url->link( 'extension/module/pavothemer/export', 'user_token=' . $this->session->data['user_token'], true ) ); //, 'user_token=' . $this->session->data['user_token'], 'SSL'
		$this->data['delete_export_url'] = str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/delete', 'user_token=' . $this->session->data['user_token'], true ) );

		$this->data['token'] = $this->session->data['user_token'];
		$this->data['sample_histories_table'] = $this->sampleTable();
		$this->data['modules'] = $this->modules_list();
		// get sample data from server
		$samples = $this->getSampleData();
		$samples = ! empty( $samples['samples'] ) ? $samples['samples'] : array();
		$samples = array(
				'samples'			=> $samples,
				'import_live_server'=> $this->url->link( 'extension/module/pavothemer/liveinstall', 'user_token=' . $this->session->data['user_token'], 'SSL' ),
				'refesh_demo'		=> str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/refeshdemo', 'user_token=' . $this->session->data['user_token'], true ) ),
				'user_token'		=> $this->data['token'],
				'liveinstall'		=> str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/liveinstall', 'user_token=' . $this->session->data['user_token'], true ) ),
				'massinstall'		=> str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/massinstall', 'user_token=' . $this->session->data['user_token'], true ) ),
				'exporturl'			=> str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/startexport', 'user_token=' . $this->session->data['user_token'], true ) ),
			);
		$this->data['samplesdemo'] = $this->load->view( 'extension/module/pavothemer/samplesdemo', $samples );
		// set page document title
		if ( $this->language && $this->document ) $this->document->setTitle( $this->language->get( 'heading_title' ) );
		$this->data['errors'] = $this->errors;
		$this->data = array_merge( array(
			'header'		=> $this->load->controller( 'common/header' ),
			'column_left' 	=> $this->load->controller( 'common/column_left' ),
			'footer'		=> $this->load->controller( 'common/footer' )
		), $this->data );
		$this->response->setOutput( $this->load->view( 'extension/module/pavothemer/tool', $this->data ) );
	}

	public function getSampleData() {
		// current theme
		$theme = defined( 'PAVOTHEMER_THEME' ) && PAVOTHEMER_THEME ? PAVOTHEMER_THEME : $this->config->get( 'config_theme' );

	 	$cache_key = 'pavothemer_sample_' . $theme;
	 	// test delete cache
	 	$this->cache->delete( $cache_key );
	 	$output = $this->cache->get( $cache_key );
	 	if( empty($output) ) {
	 		// request uri
	 		// $url = 'http://www.pavothemes.com/samples/'.$theme.'.json';
	 		// $request = PavothemerApiHelper::get( $url );
	 		$request = PavothemerApiHelper::post( PAVOTHEMER_SAMPLES_API, array(
	 			'body'	=> array(
	 				'theme'				=> $theme,
	 				'purchased_codes'	=> $this->config->get( 'pavothemer_purchased_codes' )
	 			)
	 		) );

	 		// response header
	 		// CURLINFO_HTTP_CODE
	 		$response = ! empty( $request['response'] ) ? $request['response'] : array();
	 		// response body
	 		$body = ! empty( $request['body'] ) ? $request['body'] : '';
	 		// curl get data success
	 		$data = array();
	 		if ( ! empty( $response['code'] ) && $response['code'] == 200 ) {
	 			$data = json_decode( $body, true );
	 		}

			$this->cache->set( $cache_key, $data );
			$output = $this->cache->get( $cache_key ) ;
	 	}

	 	return ! empty( $output ) ? $output : array();
	}

	/**
	 * refresh demo list
	 */
	public function refeshdemo() {
		$this->load->language( 'extension/module/pavothemer' );
		// current theme
		$theme = defined( 'PAVOTHEMER_THEME' ) && PAVOTHEMER_THEME ? PAVOTHEMER_THEME : $this->config->get( 'config_theme' );
	 	$cache_key = 'pavothemer_sample_' . $theme;
	 	$this->cache->delete( $cache_key ) ;

		$samples = $this->getSampleData();
		$samples = ! empty( $samples['samples'] ) ? $samples['samples'] : array();
		$samples = array(
				'samples'			=> $samples,
				'import_live_server'=> $this->url->link( 'extension/module/pavothemer/liveinstall', 'user_token=' . $this->session->data['user_token'], 'SSL' ),
				'refesh_demo'		=> str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/refeshdemo', 'user_token=' . $this->session->data['user_token'], true ) ),
				'user_token'		=> $this->session->data['user_token'],
				'liveinstall'		=> str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/liveinstall', 'user_token=' . $this->session->data['user_token'], true ) ),
				'massinstall'		=> str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/massinstall', 'user_token=' . $this->session->data['user_token'], true ) ),
				'exporturl'			=> str_replace( '&amp;', '&', $this->url->link('extension/module/pavothemer/startexport', 'user_token=' . $this->session->data['user_token'], true ) ),
			);
		$this->response->setOutput( $this->load->view( 'extension/module/pavothemer/samplesdemo', $samples ) );
	}

	/**
	 * required modules
	 * @since 1.0
	 */
	private function modules_list() {
		$this->load->model( 'extension/pavothemer/sample' );
		$theme = $this->config->get( 'config_theme' );
		$sample = PavoThemerSampleHelper::instance( $theme );
		$configs = $sample->getThemeConfigs();

		$extension_modules = $this->model_extension_pavothemer_sample->getExtensionModules();
		$extension_modules = isset( $extension_modules['modules'] ) ? $extension_modules['modules'] : array();

		// modules
		$modules = array();
		$config_modules = ! empty( $configs['modules'] ) ? $configs['modules'] : array();
		foreach ( $config_modules as $slug => $info ) {
			if ( isset( $extension_modules[$slug] ) ) {
				$info = array_merge( $extension_modules[$slug], $info );
			}
			$modules[$slug] = $info;
		}

		return $modules;
	}

	/**
	 * print samples table
	 */
	private function sampleTable() {
		$theme = $this->config->get( 'config_theme' );
		$sampleHelper = PavoThemerSampleHelper::instance( $theme );
		$samples = $sampleHelper->getProfiles();
		$data = array();
		$data['samples'] = array();
		foreach ( $samples as $sample ) {
			$name = basename( $sample );
			$timestamp = str_replace( 'pavothemer_' . $theme . '_', '', $name );
			$profile = $sampleHelper->getProfile( $sample );
			$data['samples'][] = array(
					'name'			=> $sample,
					'created_at' 	=> date( 'Y-m-d H:i:s', $timestamp ),
					'created_by'	=> isset( $profile['info'], $profile['info']['email'] ) ? $profile['info']['email'] : '',
					'delete'		=> $this->url->link( 'extension/module/pavothemer/delete', 'profile='.$name.'&user_token=' . $this->session->data['user_token'], 'SSL' ),
					'download'		=> $this->url->link( 'extension/module/pavothemer/download', 'profile='.$name.'&user_token=' . $this->session->data['user_token'], 'SSL' ),
					'import'		=> $this->url->link( 'extension/module/pavothemer/importThemeSettings', 'start=1&user_token=' . $this->session->data['user_token'], 'SSL' )
				);
		}
		$data['theme'] = $theme;
		return $this->load->view( 'extension/module/pavothemer/sampletable', $data );
	}

	/**
	 * Validate post form
	 *
	 * @since 1.0.0
	 */
	public function validate() {

		$has_permision = $this->user->hasPermission( 'modify', 'extension/module/pavothemer' );
		if ( ! $has_permision ) {
			$this->errors['warning'] = $this->language->get( 'error_permision' );
		} else {
			foreach ( $this->data['settings'] as $k => $fields ) {
				if ( isset( $fields['item'] ) ) foreach( $fields['item'] as $k2 => $item ) {
					if ( isset( $item['id'] ) ) {
						if ( isset( $item['required'] ) && $item['required'] && empty( $this->request->post[ 'pavothemer_' . $item['id'] ] ) ) {
							$this->errors[ $item['id'] ] = $this->language->get( 'error_' . $item['id'] );
						}
					}
				}
			}
		}
		return ! $this->errors;
	}

	/**
	 * Render html field for input, textarea, ...
	 *
	 * @since 1.0.0
	 * @return mixed html
	 */
	private function renderFieldControl( $item = array(), $data = array() ) {
		if ( empty( $item['type'] ) ) return;
		$theme = $this->config->get( 'config_theme' );
		$themeHelper = PavoThemerHelper::instance( $theme );
		$type = 'input';
		switch ( $item['type'] ) {
			case 'select_theme':
				# code...
				break;

			case 'select_store':
				# code...
				break;

			case 'select_skin':
				$none = array(
							array(
								'text'	=> $this->language->get( 'text_none' ),
								'value' => ''
							)
						);
				$item['option'] = array_merge( $none, $themeHelper->getSkins() );
				$type = 'select';
				break;
			case 'select_blockbuilder':
				$none = array(
							array(
								'text'	=> $this->language->get( 'text_none' ),
								'value' => ''
							)
						);

				$this->load->model('tool/image');
				$this->load->model('setting/module');
				$blocks = array();
				$pavobuilders = $this->model_setting_module->getModulesByCode( 'pavobuilder' );

				foreach( $pavobuilders as $block ){

					// echo $image;die;
					$blocks[] = array(
						'value'  	   => $block['module_id'],
						'text' 	 	   => $block['name'] ? $block['name'].' (ID:'.$block['module_id'].')':$block['module_id']

					);
				}

				$item['option'] = array_merge( $none, $blocks);
				$type = 'select';
				break;

			case 'select_headerbuilder':
				$none = array(
							array(
								'text'	=> $this->language->get( 'text_none' ),
								'value' => ''
							)
						);

				$this->load->model('tool/image');
				$this->load->model('setting/module');
				$blocks = array();
				$pavobuilders = $this->model_setting_module->getModulesByCode( 'pavoheader' );

				foreach( $pavobuilders as $block ) {

					// echo $image;die;
					$blocks[] = array(
						'value'  	   => $block['module_id'],
						'text' 	 	   => $block['name'] ? $block['name'].' (ID:'.$block['module_id'].')':$block['module_id']

					);
				}

				$item['option'] = array_merge( $none, $blocks);
				$type = 'select';
				break;

			case 'select_bg_image':
				$item['option'] = $themeHelper->getBgImages();
				$type = 'select';
				break;

			case 'select_header':
				$item['option'] = $themeHelper->getHeaders();
				$type = 'select';
				break;

			case 'select_footer':
				$item['option'] = $themeHelper->getFooters();
				$type = 'select';
				break;

			case 'select_product_layout':
				$item['option'] = $themeHelper->getProductDefailLayouts();
				$type = 'select';
				break;

			case 'select_category_layout':
				$item['option'] = $themeHelper->getProductCategoryLayouts();
				$type = 'select';
				break;
			case 'select_product_grid_layout':
				$item['option'] = $themeHelper->getProductGridLayouts();
				$type = 'select';
				break;
			case 'text':
			case 'email':
			case 'tel':
			case 'password':
					$type = 'input';
				break;
			case 'code_editor':
					$type = 'code_editor';
					$item['class'] = 'pavothemer-code-editor';
				break;
			case 'textarea':
			case 'summernote':
			case 'editor':
					$type = 'textarea';
				break;
			case 'style_profile':
					$type = 'select';
					$styleProfiles = $themeHelper->getCssProfiles();
					$item['option'][] = array(
							'text'	=> $this->language->get( 'text_none' ),
							'value' => ''
						);
					if ( $styleProfiles ) foreach ( $styleProfiles as $profile ) {
						$item['option'][] = array(
								'text'	=> $profile,
								'value'	=> $profile
							);
					}
				break;
			case 'link':
					$type = 'link';
					$url = $this->url->link('extension/module/pavothemer/customize', 'user_token=' . $this->session->data['user_token'], 'SSL');
					$item['url'] = $url;
				break;
			case 'colorpicker':
					$type = 'colorpicker';
					$item['class'] = 'pavo-colorpicker';
				break;

			case 'select_font_size':
					$type = 'select';
					$fontsData = ! empty( $data['font'] ) ? $data['font'] : array();
					if ( isset( $fontsData[$item['id']], $fontsData[$item['id']]['weight']) ) {
						$item['value'] = $fontsData[$item['id']]['weight'];
					}

					$item['option'][] = array(
							'text'	=> $this->language->get( 'text_inherit' ),
							'value' => ''
						);

					for ( $i = 9; $i <= 50; $i++ ) {
						$item['option'][] = array(
								'text'	=>  $i.' px',
								'value'	=> $i.'px'
						);
					}
				break;

			case 'select_font_weight':
				if ( isset($item['font'],$item['relation']) && $item['font'] === 'weight' ) {
					$item['name'] = 'fontweight[' . $item['relation'] . ']['.$item['id'].']';
				}
				$type = 'select_font_weight';
				$fontsData = ! empty( $data['fontweight'] ) ? $data['fontweight'] : array();
				if ( ! empty($item['relation']) && ! empty($fontsData[$item['relation']]) && ! empty($fontsData[$item['relation']][$item['id']]) ) {
					$item['value'] = $fontsData[$item['relation']][$item['id']];
				} else if ( !empty($data['customize'][$item['id']]) ) {
					$item['value'] = $data['customize'][$item['id']];
				}
				break;

			case 'select_font':
					$item['options'] = array();
					$webfontFile = DIR_APPLICATION . '/controller/extension/module/pavothemer/webfonts.json';
					$fonts = json_decode( file_get_contents( $webfontFile ), true );
					// $fonts = ! empty( $fonts['items'] ) ? $fonts['items'] : array();
					$fontsData = ! empty( $data['font'] ) ? $data['font'] : array();
					if ( isset( $fontsData[$item['id']]) ) {
						$item['value'] = $fontsData[$item['id']];
					}
					foreach ( $fonts as $name => $font ) {
						$item['option'][] = array(
								'value'	=> $name,
								'text'	=> $name
							);
					}
					$type = $item['type'];
				break;

			default:
				# code...
					$type = $item['type'];
				break;
		}

		if ( !isset($item['name']) ) {
			$item['name'] =  strpos( $item['id'], 'pavothemer_' ) == false ? 'pavothemer_' . $item['id'] : $item['id'];
		}

		$item['class'] = 'form-control' . ( isset( $item['class'] ) ? ' ' .trim( $item['class'] ) : '' );
		// return html
		return $this->load->view( 'extension/module/pavothemer/fields/' . $type, array( 'item' => $item ) );
	}

	public function parseCustomizeOptions( $fields = array(), $customize_options = array() ) {
		if ( empty( $fields['item'] ) ) {
			$fields['output'] = $this->renderFieldControl( $fields, $customize_options );
		} else {
			foreach ( $fields['item'] as $k => $item ) {
				if ( empty( $item['id'] ) ) continue;
				if ( isset( $item['font'] ) ) {
					if ($item['font'] === 'name') {
						$item['name'] = 'font[' . $item['id'] . ']';
					}
				} else {
					$item['name'] = 'customize['.$item['id'].']';
				}

				if ( ! empty( $item['item'] ) ) {
					$item = $this->parseCustomizeOptions( $item, $customize_options );
					$fields['item'][$k] = $item;
				} else {
					$value = isset( $customize_options[ $item['id'] ] ) ? $customize_options[ $item['id'] ] : '';
					if ( isset( $item['font'] ) ) {
						$value = isset( $customize_options['font'][ $item['id'] ], $customize_options['font'][ $item['id'] ][$item['font']] ) ? $customize_options['font'][ $item['id'] ][$item['font']] : '';
					} else {
						$value = isset( $customize_options['customize'][ $item['id'] ] ) ? $customize_options['customize'][ $item['id'] ] : '';
					}
					$item['value'] = $value ? $value : ( isset( $item['default'] ) ? $item['default'] : '' );
					$item['output'] = $this->renderFieldControl( $item, $customize_options );

				}

				$fields['item'][$k] = $item;
			}
		}

		return $fields;
	}

	/**
	 *
	 */
	public function ajaxGetContent() {
		$id = ! empty( $this->request->post['setting'] ) ? $this->request->post['setting'] : false;
		$theme = $this->config->get( 'config_theme' );
		switch ( $id ) {
			case 'pavothemer_custom_css':
					$file = DIR_CATALOG . 'view/theme/' . $theme . '/stylesheet/customize.css';
				break;

			case 'pavothemer_custom_js':
					$file = DIR_CATALOG . 'view/theme/' . $theme . '/javascript/customize.js';
				break;

			default:
				# code...
				break;
		}
		$this->response->addHeader( 'Content-Type: application/json' );
		$this->response->setOutput( json_encode(array(
				'code'	=> htmlspecialchars_decode( is_readable( $file ) ? file_get_contents( $file ) : $this->config->get( $id ) )
			)) );
	}

	/**
	 * request api customer
	 */
	public function api() {

	}

	/**
	 * Insert default pavothemer values to setting table
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function install() {
		$this->load->model('setting/extension');
		$this->model_setting_extension->install('module', 'pavothemer');
		// START ADD USER PERMISSION
		$this->load->model('user/user_group');
		// access - modify pavothemer edit
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavothemer/edit' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavothemer/edit' );
		// access - modify pavothemer customize
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavothemer/customize' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavothemer/customize' );
		// access - modify pavothemer sampledata
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavothemer/tools' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavothemer/tools' );
		// access - modify pavothemer sampledata
		$this->model_user_user_group->addPermission( $this->user->getId(), 'access', 'extension/module/pavothemer/management' );
		$this->model_user_user_group->addPermission( $this->user->getId(), 'modify', 'extension/module/pavothemer/management' );
		// END ADD USER PERMISSION

		$settingFields = PavoThemerSettingHelper::instance( $this->config->get('config_theme') )->getSettings();
		$this->load->model( 'setting/setting' );
		$settings = array();

		// get option if it already activated before
		$defaultOptions = array();
		foreach ( $settingFields as $tab => $fields ) {
			if ( empty( $fields['item'] ) ) continue;
			foreach ( $fields['item'] as $item ) {
				if ( ! isset( $item['id'], $item['default'] ) ) continue;
				// get default options
				if ( ! $this->config->get( 'pavothemer_' . $item['id'] ) ) {
					$defaultOptions[ 'pavothemer_' . $item['id'] ] = $item['default'];
				}
			}
		}

		// insert default option values
		$this->model_setting_setting->editSetting( 'pavothemer', $defaultOptions, $this->config->get( 'config_store_id' ) );
	}

	/**
	 * Uninstall action
	 * @since 1.0.0
	 */
	public function uninstall() {
		// START REMOVE USER PERMISSION
		$this->load->model('user/user_group');
		// access - modify pavothemer edit
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavothemer/edit' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavothemer/edit' );
		// access - modify pavothemer customize
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavothemer/customize' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavothemer/customize' );
		// access - modify pavothemer sampledata
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavothemer/tools' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavothemer/tools' );
		// access - modify pavothemer management
		$this->model_user_user_group->removePermission( $this->user->getId(), 'access', 'extension/module/pavothemer/management' );
		$this->model_user_user_group->removePermission( $this->user->getId(), 'modify', 'extension/module/pavothemer/management' );
		// END REMOVE USER PERMISSION
	}

	/**
	 * Theme Creater
	 */
	protected function _skincreator( $creator = array(), $skin = false, $preview = false ) {

		$output = array();

		$customize = ! empty( $creator['customize'] ) ? $creator['customize'] : array();

		foreach( $customize as $key => $value ){
			if( $value ){
				$output[] = '$'.trim($key).'  			 : '. $value.';';
			}
		}

		$font_importer = array();
		$fonts = ! empty( $creator['font'] ) ? $creator['font'] : array();
		$fontweight = ! empty( $creator['fontweight'] ) ? $creator['fontweight'] : array();

		// mapping fonts
		foreach ( $fonts as $key => $font ) {
			$output[] = '$'.trim($key).'			 : "' . $font . '", Arial, sans-serif !default;';
			$fontName = implode( '+', explode( ' ', $font ) );
			$fWeight = '';
			if ( !empty($fontweight[$key]) ) {
				foreach ( $fontweight[$key] as $k => $weight ) {
					$fWeight .= ':'.$weight;
				}
			}
			$font_importer[] = 'https://fonts.googleapis.com/css?family=' . $fontName . $fWeight;
		}

		// font weight mapping
		foreach ( $fontweight as $weights ) {
			foreach ( $weights as $key => $value ) {
				if ( $value ) {
					$output[] = '$'.trim($key).': '. $value.';';
				}
			}
		}

		// if( ! class_exists( 'scssc' ) ) {
		// 	require DIR_STORAGE . '/vendor/leafo/scssphp/scss.inc.php';
		// }
		// $scss = new scssc();

		$output[] = '$image-theme-path                : \'../../image/\' ;'."\r\n";
		$output[] = '$fonts-theme-path                : \'../../fonts/\' ;'."\r\n";

		$scss = new Compiler();
		$theme = $this->config->get( 'config_theme' );

		$scss->setImportPaths( DIR_CATALOG . 'view/theme/' . $theme . '/sass/' );

		$file = DIR_CATALOG . 'view/theme/' . $theme . '/sass/stylesheet.scss';

		$content = file_get_contents( $file ) ;

		$overrideVariables = implode( "\n\r", $output );
		$replacer = '';
		if ( $font_importer ) foreach ( $font_importer as $font ) {
			$replacer .= '@import url(\'' . $font . '\');' . "\r\n";
		}

		$replacer .= $overrideVariables;
		$replacer .= '@import "bootstrap/variables";' . "\r\n";

		$replace = preg_replace( "#\@import \"bootstrap/variables\"\s*;\s*#", "\r\n". $replacer."\n\r", $content );
		$content = $scss->compile( $replace );
		$name = $skin ? $skin : time();
		$name = $preview ? 'cache' : $name;

		$file = DIR_CATALOG . 'view/theme/' . $theme . '/stylesheet/skins/'.$name.'.css' ;
		if ( $fo = fopen( $file, 'w+' ) ) {
			fwrite( $fo, $content );
			fclose( $fo );
		}

		// rtl process
		$this->load->library( 'rtlcss' );
		$rtlContent = rtlcss::transform( $content );
		$file = DIR_CATALOG . 'view/theme/' . $theme . '/stylesheet/skins/'.$name.'-rtl.css' ;
		if ( $fo = fopen( $file, 'w+' ) ) {
			fwrite( $fo, $rtlContent );
			fclose( $fo );
		}

		$file =  DIR_CATALOG . 'view/theme/'.$theme.'/stylesheet/skins/'.$name.'.json';
		if ( $fo = fopen( $file, 'w+' ) ) {
			fwrite( $fo, json_encode( $creator ) );
			fclose( $fo );
			return HTTP_CATALOG . 'catalog/view/theme/' . $theme . '/stylesheet/skins/'.$name.'.css?tp='.rand();
		}
		return false;

	}
}
