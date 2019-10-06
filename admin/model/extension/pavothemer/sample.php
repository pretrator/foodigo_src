<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license GNU General Public License version 2
 */

/**
 * EXPORTS:
 * - theme settings
 * - layout settings
 * - tables
 * - layout modules
 */
class ModelExtensionPavothemerSample extends Model {

	/**
	 * import theme settings
	 */
	public function importThemeSettings( $profile = array() ) {
		$this->load->model( 'setting/setting' );
		$settings = ! empty( $profile['themes'] ) ? $profile['themes'] : array();
		$infos = ! empty( $profile['info'] ) ? $profile['info'] : '';
		$theme = ! empty( $profile['info']['theme'] ) ? $profile['info']['theme'] : '';

		$code = 'theme_' . $theme;
		$this->model_setting_setting->editSetting( $code, $profile['theme_settings'], $this->config->get( 'config_store_id' ) );

		return true;
	}

	/**
	 * import modules
	 */
	public function importModules( $profile = array() ) {
		$this->load->model( 'setting/extension' );
		$extensions_installed = $this->model_setting_extension->getInstalled( 'module' );
		$importModules = isset( $profile[ 'extensions' ], $profile[ 'extensions' ]['modules'] ) ? $profile['extensions']['modules'] : array();

		if ( ! $importModules ) return;
		$files = glob( DIR_APPLICATION . 'controller/extension/module/*.php' );

		$data = array();
		if ( $files ) {
			foreach ($files as $file) {
				$extension = basename( $file, '.php' );
				// install action if extension is activated in backup profile
				if ( array_key_exists( $extension, $importModules ) && ! isset( $extensions_installed[$extension] ) && $importModules[$extension]['installed'] ) {
					$this->request->get['extension'] = $extension;

					// load controller
					if ( $this->user->hasPermission( 'modify', 'extension/extension/module/install' ) ) {
						$this->load->controller( 'extension/extension/module/install' );
					}
				}
			}
			// unset extension request
			$this->request->get['extension'] = null;
		}
		// refresh to regenerate ocmod modification
		// $this->load->controller( 'marketplace/modification/refresh' );
	}

	/**
	 * modules required
	 */
	public function installModule( $modulePath = null ) {
		if ( ! $modulePath ) return true;

		$this->session->data['install'] = token( 10 );
		$destination = DIR_UPLOAD . $this->session->data['install'] . '.tmp';
		if ( copy( $modulePath, $destination ) ) {
			return $this->_installModule();
		}
		return true;
	}

	/**
	 * install module
	 */
	public function _installModule() {
		$steps = array(
				'marketplace/install/unzip',
				'marketplace/install/move',
				'marketplace/install/xml',
				'marketplace/install/remove'
			);

		foreach ( $steps as $step ) {
			$this->load->controller( $step );
			ob_start();
			$output = $this->response->output();
			$result = json_decode( ob_get_clean(), true );

			if ( ! empty( $result['error'] ) ) {
				return $result;
			}
		}

		return true;
	}

	public function _activeModules(){
		$modules = array(

		);
		$link =  $this->url->link('extension/extension/' . $extension.'/install', 'user_token=' . $this->session->data['user_token'], true);
	}

	/**
	 * install sql
	 */
	public function installSQL( $query = array() ) {
		try {
			$this->load->model( 'setting/module' );
			$this->load->model( 'extension/pavobuilder/pavobuilder' );
			if ( ! empty( $query['tables'] ) ) {
				foreach ( $query['tables'] as $sql ) {
					$this->db->query( $sql );
				}
			}

			if ( ! empty( $query['rows'] ) ) {
				if ( !empty($query['rows']['pavobuilder']) ) {
					$this->model_setting_module->deleteModulesByCode('pavobuilder');
					$this->model_setting_module->deleteModulesByCode('pavoheader');
					$this->model_extension_pavobuilder_pavobuilder->clearData();
				}
				if ( !empty($query['rows']['megamenu']) ) {
					// clear megamenu
					$this->db->query("DELETE FROM `" . DB_PREFIX . "megamenu`");
					$this->db->query("TRUNCATE `" . DB_PREFIX . "megamenu`");
					$this->db->query("DELETE FROM `" . DB_PREFIX . "megamenu_description`");
					$this->db->query("TRUNCATE `" . DB_PREFIX . "megamenu_description`");
					$this->db->query("DELETE FROM `" . DB_PREFIX . "megamenu_widgets`");
					$this->db->query("TRUNCATE `" . DB_PREFIX . "megamenu_widgets`");
				}
				if ( !empty($query['rows']['verticalmenu']) ) {
					// clear verticalmenu
					$this->db->query("DELETE FROM `" . DB_PREFIX . "verticalmenu`");
					$this->db->query("TRUNCATE `" . DB_PREFIX . "verticalmenu`");
					$this->db->query("DELETE FROM `" . DB_PREFIX . "verticalmenu_description`");
					$this->db->query("TRUNCATE `" . DB_PREFIX . "verticalmenu_description`");
					$this->db->query("DELETE FROM `" . DB_PREFIX . "verticalmenu_widgets`");
					$this->db->query("TRUNCATE `" . DB_PREFIX . "verticalmenu_widgets`");
				}
				foreach ( $query['rows'] as $table => $sql ) {
					$csql = 'SELECT * FROM ' . DB_PREFIX . $table;
					$q = $this->db->query( $csql );
					if ( $q->row ) continue;
					$this->load->model( 'localisation/language' );

					// all languages
					$languages = $this->model_localisation_language->getLanguages();
					foreach ( $sql as $k => $s ) {
						if ( strpos( $s, 'LANGUAGE_ID_REPLACEMENT' ) ) {
							foreach ( $languages as $language ) {
								$sq = str_replace( 'LANGUAGE_ID_REPLACEMENT', $language['language_id'], $s );
								// execute query
								$this->db->query( $sq );
							}
						} else {
							$status = $this->db->query( $s );
						}
					}
				}
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}

		return true;
	}

	public function mappingMenu( $mapping = array() )
	{
		/**
		 * mapping megamenu data
		 */
		$modules_mapping = ! empty( $mapping['module_ids'] ) ? $mapping['module_ids'] : array();
		$sql = "SHOW TABLES LIKE '" . DB_PREFIX . "megamenu'";
		$query = $this->db->query($sql);
		if ( ! empty( $modules_mapping ) && $query->num_rows ) {
			$sql = "SELECT * FROM " . DB_PREFIX . "megamenu WHERE `type_submenu` = 'blkbuilder'";
			$query = $this->db->query( $sql );
			if ( $query->rows ) {
				foreach ( $query->rows as $row ) {
					$widget_id = ! empty( $row['widget_id'] ) ? $row['widget_id'] : false;
					$megamenu_id = ! empty( $row['megamenu_id'] ) ? $row['megamenu_id'] : false;
					$new_megamenu_id = ! empty( $modules_mapping[$widget_id] ) ? $modules_mapping[$widget_id] : false;
					if ( $widget_id && $new_megamenu_id ) {
						$sql = " UPDATE  ". DB_PREFIX . "megamenu SET  `widget_id` = " . (int)$new_megamenu_id;
						$sql .= " WHERE megamenu_id = " . (int)$megamenu_id;
						$this->db->query( $sql );
					}
				}
			}
		}

		// mapping vertical menu
		$sql = "SHOW TABLES LIKE '" . DB_PREFIX . "verticalmenu'";
		$query = $this->db->query($sql);
		if ( ! empty( $modules_mapping ) && $query->num_rows ) {
			$sql = "SELECT * FROM " . DB_PREFIX . "verticalmenu WHERE `type_submenu` = 'blkbuilder'";
			$query = $this->db->query( $sql );
			if ( $query->rows ) {
				foreach ( $query->rows as $row ) {
					$widget_id = ! empty( $row['widget_id'] ) ? $row['widget_id'] : false;
					$verticalmenu_id = ! empty( $row['verticalmenu_id'] ) ? $row['verticalmenu_id'] : false;
					$new_vertical_menu_id = ! empty( $modules_mapping[$widget_id] ) ? $modules_mapping[$widget_id] : false;
					if ( $widget_id && $new_vertical_menu_id ) {
						$sql = " UPDATE  ". DB_PREFIX . "verticalmenu SET  `widget_id` = " . (int)$new_vertical_menu_id;
						$sql .= " WHERE verticalmenu_id = " . (int)$verticalmenu_id;
						$this->db->query( $sql );
					}
				}
			}
		}
	}

	/**
	 * import layouts
	 * #1 import modules to DB_PREFIX . 'module' table
	 * #2 import layouts to DB_PREFIX . 'layout' table
	 * #3 import layout modules to DB_PREFIX . 'layout_module' table
	 * #4 import layout routes to DB_PREFIX . 'layout_route' table
	 * #5 mapping data
	 * old backup layouts, extensions, layout_modules
	 *
	 * @param $demo_store_id like demo1, demo2, demo3
	 * @param content of profile.js
	 */
	public function importLayouts( $demo_store_id = false, $profile = array() ) {
		$this->load->model( 'setting/module' );
		$this->load->model( 'design/layout' );
		$this->load->model( 'setting/store' );
		$this->load->model( 'setting/setting' );
		$this->load->model( 'extension/pavobuilder/pavobuilder' );
		// all key need to mapping is storerage here
		$mapping = array(
				'module_ids'		=> array(),
				'layout_ids'		=> array(),
				'layout_modules'	=> array()
			);
		// stores
		$stores = $this->model_setting_store->getStores();
		$stores[] = array(
			'store_id' => 0
		);

		#1 Import modules
		$modules = isset( $profile['extensions'], $profile['extensions']['modules'] ) ? $profile['extensions']['modules'] : array();
		// $current_modules = $this->model_setting_module->getModules();

		// each modules
		foreach ( $modules as $extension => $module ) {
			// each current modules
			if ( ! empty( $module['setting'] ) ) {
				foreach ( $stores as $store ) {
					$store_id = ! empty( $store['store_id'] ) ? $store['store_id'] : 0;
					$old_setting = $this->model_setting_setting->getSetting( $extension, $store_id );
					$setting = array_merge( $old_setting, $module['setting'] );
					$this->model_setting_setting->editSetting( $extension, $setting, $store_id );
				}
			}
			if ( empty( $module['data'] ) ) continue;
			$current_ex_modules = $this->model_setting_module->getModulesByCode( $extension );

			foreach ( $module['data'] as $data ) {
				$module_id = $data['module_id'];
				// module already exists
				// empty modules
				if ( in_array( $data, $current_ex_modules ) ) {
					$mapping['module_ids'][$module_id] = $module_id;
				} else if ( ! $current_ex_modules ) {
					$this->model_setting_module->addModule( $extension, json_decode( $data['setting'], true ) );
					$mapping['module_ids'][$module_id] = $this->db->getLastId();
				} else {
					$module_installed_id = $create_new = false;
					// module name already exists
					foreach ( $current_ex_modules as $c_mod ) {
						$compare_old = array(
								'name'	=> $data['name'],
								'code'	=> $data['code'],
								'setting'	=> $data['setting']
							);
						$compare_new = array(
								'name'		=> $c_mod['name'],
								'code'		=> $c_mod['code'],
								'setting'	=> $c_mod['setting']
							);
						if ( $compare_old === $compare_new ) {
							$mapping['module_ids'][$module_id] = $module_installed_id = $c_mod['module_id'];
							if ( $module_installed_id ) {
								$create_new = true;
								continue;
							}
						}
					}

					if ( $module_installed_id && ! $create_new ) {
						$this->model_setting_module->editModule( $module_installed_id, json_decode( $data['setting'], true ) );
					} else {
						$this->model_setting_module->addModule( $extension, json_decode( $data['setting'], true ) );
						$mapping['module_ids'][$module_id] = $this->db->getLastId();
					}
				}
			}
		}

		#2 Import layouts
		$layouts = ! empty( $profile['layouts'] ) ? $profile['layouts'] : array();
		$current_layouts = $this->model_design_layout->getLayouts();

		if ( $layouts ) {
			$layout_home_data = array();
			// each backup layouts
			foreach ( $layouts as $layout_data ) {
				// old id
				$layout_id = ! empty( $layout_data['layout_id'] ) ? $layout_data['layout_id'] : false;
				if ( ! $layout_id ) continue;

				// current id, current layout modules data
				$installed_layout_id = $create_new = false;
				// $installed_layout_data = array();
				// each current layout
				$excerpt_module = array(
						'layout_id'	=> $layout_id,
						'name'		=> $layout_data['name']
					);

				// import layouts
				$is_home = false;
				if ( in_array( $excerpt_module, $current_layouts ) ) {
					$mapping['layout_ids'][ $layout_id ] = $installed_layout_id = $layout_id;
					$layout_routes = $this->model_design_layout->getLayoutRoutes( $layout_id );
					foreach ( $layout_routes as $l_route ) {
						if ( isset( $l_route['route'] ) && $l_route['route'] === 'common/home' ) {
							$is_home = true;
							$layout_home_data[$layout_id] = $layout_data;
						}
					}
				} else {
					foreach ( $current_layouts as $c_layout ) {
						if ( $layout_data['name'] === $c_layout['name'] ) {
							// create new layout if their layout name is already exists many times
							if ( $installed_layout_id ) {
								$create_new = true;
								continue;
							}
							$mapping['layout_ids'][ $layout_id ] = $installed_layout_id = $c_layout['layout_id'];
						}
					}

					$layout_data['layout_module'] = $layout_data['layout_modules'];

					#3. Import Layout Modules
					$layout_modules = array();
					if ( ! empty( $layout_data['layout_modules'] ) ) {
						foreach ( $layout_data['layout_modules'] as $k => $module ) {
							// var_dump($layout_id, $mapping['layout_ids']); die();
							$layout_module = array(
								'layout_id'	=> isset( $mapping['layout_ids'][$layout_id] ) ? $mapping['layout_ids'][$layout_id] : 0,
								'code'		=> 0,
								'position'	=> $module['position'],
								'sort_order'=> $module['sort_order']
							);

							$explode = ! empty( $module['code'] ) ? explode( '.', $module['code'] ) : array();
							$module_id = count( $explode ) > 1 ? (int)end( $explode ) : $module['code'];
							if ( is_int( $module_id ) ) {
								if ( ! empty( $mapping['module_ids'][$module_id] ) ) {
									$new_module_id = $mapping['module_ids'][$module_id];
									$layout_module['code'] = str_replace( '.' . $module_id, '.' . $new_module_id, $module['code'] );
								}
							}
							$layout_modules[] = $layout_module;
						}
						// set layout modules
						$layout_data['layout_module'] = $layout_modules;
					}

					// layout routes
					$layout_data['layout_route'] = $layout_data['layout_routes'];
					if ( $layout_data['layout_route'] ) {
						foreach ( $layout_data['layout_route'] as $recode ) {
							if ( isset( $recode['route'], $recode['store_id'] ) && $recode['route'] == 'common/home' && ( $demo_store_id == false || ( $demo_store_id !== false && $demo_store_id == $recode['store_id'] ) ) ) {
								$is_home = true;
								$store_id = isset( $recode['store_id'] ) ? $recode['store_id'] : false;
								$layout_home_data[] = $layout_data;
							}
						}
					}

					if ( ! $is_home ) {
						// create new layout
						if ( $installed_layout_id && ! $create_new ) {
							$this->model_design_layout->editLayout( $installed_layout_id, $layout_data );
						} else {
							$mapping['layout_ids'][ $layout_id ] = $installed_layout_id = $this->model_design_layout->addLayout( $layout_data );
						}
					}
				}

			}

			// home relationship
			if ( $layout_home_data ) {
				foreach ( $layout_home_data as $layout_id => $data ) {
					$layout_data = array(
							'name'			=> ! empty( $data['name'] ) ? $data['name'] : '',
							'layout_module'	=> array(),
							'layout_route'	=> array()
						);
					if ( ! empty( $data['layout_modules'] ) ) {
						if ( ! empty( $data['layout_routes'] ) ) {
							foreach ( $data['layout_routes'] as $route ) {
								$l_route = array(
										'layout_id'	=> isset( $mapping['layout_ids'][$layout_id] ) ? $mapping['layout_ids'][$layout_id] : $layout_id,
										'route'		=> 'common/home',
										'store_id'	=> $this->config->get( 'config_store_id' )
									);
								$layout_data['layout_route'][] = $l_route;

							}
						}

						$layout_home_data['layout_module'] = array();
						foreach ( $data['layout_modules'] as $module ) {
							$m_data = array(
									'layout_id'		=> $layout_id,
									'position'		=> ! empty( $module['position'] ) ? $module['position'] : '',
									'sort_order'	=> ! empty( $module['sort_order'] ) ? $module['sort_order'] : 0,
								);
							$codes = isset( $module['code'] ) ? explode( '.', $module['code'] ) : array();
							$code_id = count( $codes ) > 1 ? end( $codes ) : 0;
							if ( isset( $mapping['module_ids'][$code_id] ) ) {
								$code = $codes[0] . '.' . $mapping['module_ids'][$code_id];
								$m_data['code'] = $code;
							}
							$layout_data['layout_module'][] = $m_data;
						}
						$this->model_design_layout->editLayout( $layout_id, $layout_data );
					}
				}
			}

			// block builder
			$blockBuilders = $this->model_setting_module->getModulesByCode( 'pavobuilder' );
			$headerBuilders = $this->model_setting_module->getModulesByCode( 'pavoheader' );
			$builders = array_merge( $blockBuilders, $headerBuilders );
			if ( $builders ) {
				foreach ( $builders as $k => $builder ) {
					$setting = ! empty( $builder['setting'] ) ? json_decode( $builder['setting'], true ) : array();
					$uniqid_id = ! empty( $setting['uniqid_id'] ) ? $setting['uniqid_id'] : false;

					if ( ! isset( $setting['content'] ) ) {
						$setting['content'] = array();
					}
					$builderData = $this->model_extension_pavobuilder_pavobuilder->getBuilderData( $uniqid_id );
					$setting['content'] = array_merge( $setting['content'], ! empty( $builderData['settings'] ) ? $builderData['settings'] : array() );
					$rows = ! empty( $setting['content'] ) ? $setting['content'] : array();

					if ( ! $rows ) continue;
					$uniqid_id = ! empty( $setting['uniqid_id'] ) ? $setting['uniqid_id'] : false;
					$rows = $this->mappingBuilder( $rows, $mapping );

					$module_id = $builder['module_id'];
					$builder['status'] = 1;
					$builder['uniqid_id'] = $uniqid_id;
					unset( $builder['module_id'] );
					if (isset($setting['content'])) {
						unset($setting['content']);
						$builder = $setting;
					}
					$this->model_setting_module->editModule( $module_id, $builder );
					$this->model_extension_pavobuilder_pavobuilder->saveBuilder( $uniqid_id, $rows );
				}
			}

			// pavo setting block footer mapping
			$mapping_keys = array(
				'pavothemer_header_blockbuilder',
				'pavothemer_footer_blockbuilder',
				'pavothemer_home_headerbuilder'
			);
			// each store one by one
			foreach ( $stores as $store ) {
				$store_id = ! empty( $store['store_id'] ) ? (int)$store['store_id'] : 0;
				$pavosettings = $this->model_setting_setting->getSetting( 'pavothemer', $store_id );
				foreach ( $mapping_keys as $name ) {
					// mapping keys has key is already exists on $mapping['module_ids'], instead it
					if ( array_key_exists( $name, $pavosettings ) && isset( $mapping['module_ids'], $mapping['module_ids'][$pavosettings[$name]] ) ) {
						$pavosettings[$name] = $mapping['module_ids'][$pavosettings[$name]];
					}
				}
				$this->model_setting_setting->editSetting( 'pavothemer', $pavosettings, $store_id );
			}
		}
		return $mapping;
	}

	/**
	 * mapping builder block
	 * @param $row array
	 * @since 1.0.0
	 */
	public function mappingBuilder( $rows = array(), $mapping = array() ) {
		foreach ( $rows as $kr => $row ) {
			$columns = ! empty( $row['columns'] ) ? $row['columns'] : array();
			if ( $columns ) foreach ( $columns as $kc => $col ) {
				$elements = ! empty( $col['elements'] ) ? $col['elements'] : array();
				if ( $elements ) foreach ( $elements as $ke => $element ) {
					if ( isset( $element['moduleId'] ) && isset( $mapping['module_ids'][ $element['moduleId'] ] ) ) {
						$changed = true;
						$new_id = $mapping['module_ids'][ $element['moduleId'] ];
						$mask = isset( $rows[$kr]['columns'][$kc]['elements'][$ke]['mask'] ) ? $rows[$kr]['columns'][$kc]['elements'][$ke]['mask'] : array();
						$mask['module_id'] = $new_id;
						$rows[$kr]['columns'][$kc]['elements'][$ke]['moduleId'] = $new_id;
						$rows[$kr]['columns'][$kc]['elements'][$ke]['mask'] = $mask;
					} else if ( ! empty( $element['row'] ) ) {
						$subs = $this->mappingBuilder( array( $element['row'] ), $mapping );
						$rows[$kr]['columns'][$kc]['elements'][$ke]['row'] = $subs ? $subs[0] : array();
					}
				}
			}
		}
		return $rows;
	}

	/**
	 * get theme settings for export
	 */
	public function getThemeSettings( $theme = '' ) {
		if ( ! $theme ) {
			$theme = $this->config->get( 'config_theme' );
		}
		$this->load->model( 'setting/setting' );
		$code = 'theme_' . $theme;
		return $this->model_setting_setting->getSetting( $code );
	}

	/**
	 * layout modules
	 */
	public function getLayoutModules() {
		$sql = 'SELECT * FROM ' . DB_PREFIX . 'layout_module';
		$query = $this->db->query( $sql );
		return $query->rows;
	}

	/**
	 * export extension modules
	 */
	public function getExtensionModules() {
		$this->load->model( 'setting/extension' );
		$this->load->model( 'setting/module' );
		$this->load->model( 'setting/setting' );

		$extensions = $this->model_setting_extension->getInstalled( 'module' );
		$files = glob( DIR_APPLICATION . 'controller/extension/module/*.php' );

		$data = array();
		if ( $files ) {
			foreach ($files as $file) {
				$extension = basename( $file, '.php' );

				$module_data = array();
				$modules = $this->model_setting_module->getModulesByCode( $extension );
				$settings = $this->model_setting_setting->getSetting( $extension );

				$data['modules'][ $extension ] = array(
					'name'        => $extension,
					'installed'   => in_array( $extension, $extensions ),
					'data' 		  => $modules,
					'setting'	  => $settings
				);
			}
		}

		return $data;
	}

	/**
	 * export tables
	 */
	public function exportTables() {
		$default_tables = array(
			'address',
			'api',
			'api_ip',
			'api_session',
			'attribute',
			'attribute_description',
			'attribute_group',
			'attribute_group_description',
			'banner',
			'banner_image',
			'cart',
			'category',
			'category_description',
			'category_filter',
			'category_path',
			'category_to_layout',
			'category_to_store',
			'country',
			'coupon',
			'coupon_category',
			'coupon_history',
			'coupon_product',
			'currency',
			'custom_field',
			'custom_field_customer_group',
			'custom_field_description',
			'custom_field_value',
			'custom_field_value_description',
			'customer',
			'customer_activity',
			'customer_affiliate',
			'customer_approval',
			'customer_group',
			'customer_group_description',
			'customer_history',
			'customer_ip',
			'customer_login',
			'customer_online',
			'customer_reward',
			'customer_search',
			'customer_transaction',
			'customer_wishlist',
			'download',
			'download_description',
			'event',
			'extension',
			'extension_install',
			'extension_path',
			'filter',
			'filter_description',
			'filter_group',
			'filter_group_description',
			'geo_zone',
			'information',
			'information_description',
			'information_to_layout',
			'information_to_store',
			'language',
			'layout',
			'layout_module',
			'layout_route',
			'length_class',
			'length_class_description',
			// 'location',
			'manufacturer',
			'manufacturer_to_store',
			'marketing',
			'modification',
			// 'module',
			'option',
			'option_description',
			'option_value',
			'option_value_description',
			'order',
			'order_history',
			'order_option',
			'order_product',
			'order_recurring',
			'order_recurring_transaction',
			'order_shipment',
			'order_status',
			'order_total',
			'order_voucher',
		    'product',
		    'product_attribute',
		    'product_description',
		    'product_discount',
		    'product_filter',
		    'product_image',
		    'product_option',
		    'product_option_value',
		    'product_recurring',
		    'product_related',
		    'product_reward',
		    'product_special',
		    'product_to_category',
		    'product_to_download',
		    'product_to_layout',
		    'product_to_store',
		    'recurring',
		    'recurring_description',
		    'return',
		    'return_action',
		    'return_history',
		    'return_reason',
		    'return_status',
		    'review',
		    'seo_url',
		    'session',
		    'setting',
		    'shipping_courier',
		    'statistics',
		    'stock_status',
		    'store',
		    'tax_class',
		    'tax_rate',
		    'tax_rate_to_customer_group',
		    'tax_rule',
		    'theme',
		    'translation',
		    'upload',
		    'user',
		    'user_group',
		    'voucher',
		    'voucher_history',
		    'voucher_theme',
		    'voucher_theme_description',
		    'weight_class',
		    'weight_class_description',
		    'zone',
		    'zone_to_geo_zone'
		);
		$data = array(
				'tables'	=> array(),
				'rows'		=> array(),
				'xml'		=> array()
			);

		$result = $this->db->query( 'SHOW TABLES' );
		$tables = array();
		// all tables
		if ( ! empty( $result->rows ) ) {
		    foreach ( $result->rows as $k => $row ) {
		        $values = array_values( $row );
		        if ( ! empty( $values ) ) {
		            $value = str_replace( DB_PREFIX, '', $values[0] );
		            $tables[] = $value;
		        }
		    }
		}

		if ( $tables ) foreach ( $tables as $k => $table_name ) {
			if ( in_array( $table_name, $default_tables ) ) continue;
			// show create table query
			$sql = 'SHOW CREATE TABLE  ' . DB_PREFIX . $table_name;
			$query = $this->db->query( $sql );

			if ( ! isset($data['xml'][$table_name]) ) {
				$data['xml'][$table_name] = array();
			}
			$xml = array();
			// data results execute query
			$row = $query->row;
			if ( ! empty( $row['Create Table'] ) ) {

				// create table query
				$data['tables'][ $table_name ] = str_replace( 'CREATE TABLE `' . DB_PREFIX . $table_name . '`', 'CREATE TABLE IF NOT EXISTS `' . '"DB_PREFIX"' . $table_name . '`', $row['Create Table'] );

				// table columns
				$sql = 'SHOW COLUMNS FROM ' . DB_PREFIX . $table_name . ' WHERE field="language_id"';
				$query = $this->db->query( $sql );
				$row = $query->row;

				// row table query
				$sql = 'SELECT * FROM ' . DB_PREFIX . $table_name;

				// table has language id
				if ( $row ) {
					$sql .= ' WHERE language_id = ' . $this->config->get( 'config_language_id' );
				}

				$xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
				$xml[] = '<!-- Copyright PavoThemes -->';
				$xml[] = '<table version="1.0" table="'.$table_name.'">';

				// execute query
				$query = $this->db->query( $sql );
				// rows
				$rows = $query->rows;
				if ( $rows ) foreach ( $rows as $row ) {
					$cols = array_keys( $row );
					$values = array_values( $row );
					$values = array_map( array( $this->db, 'escape' ), $values );

					// key
					$key = array_search( 'language_id', $cols );
					if ( $key !== false ) {
						$values[$key] = 'LANGUAGE_ID_REPLACEMENT';
					}

					if ( ! isset( $data['rows'][$table_name] ) ) {
						$data['rows'][$table_name] = array();
					}

					// xml
					$xml[] = "\t".'<row>';
					foreach ($values as $key => $value) {
						$value = str_replace('\\\\\\"', '\\"', addslashes($value));
						$values[$key] = '"'.$value.'"';
						// xml
						$xml[] = "\t\t".'<column name="'.$cols[$key].'"><![CDATA['.$value.']]></column>';
					}
					// xml
					$xml[] = "\t".'</row>';

					if ($table_name !== 'module') {
						$sql = 'INSERT INTO `' . '"DB_PREFIX"' . $table_name . '` (`' . implode( '`, `', $cols ) . '`) VALUES ('.implode(', ', $values).')';
					}
					$data['rows'][$table_name][] = $sql;
				}

				$xml[] = '</table>';
				$data['xml'][$table_name] = implode("\r\n", $xml);
			}
		}

		return $data;
	}

	/**
	 * get all extensions
	 */
	public function getExtensions() {
		$sql = 'SELECT * FROM ' . DB_PREFIX . 'extension';
		$query = $this->db->query( $sql );

		return $query->rows;
	}

}
