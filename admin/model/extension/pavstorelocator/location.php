<?php 
class ModelExtensionPavstorelocatorLocation extends Model { 

	public function install(){

		$sql = 'SHOW COLUMNS FROM `'.DB_PREFIX.'location` LIKE \'video\';';

		
		$query = $this->db->query($sql);

		$data =  $query->rows;

		if( empty($data) ){
			$sqls = array();
			$sqls[] = 'ALTER TABLE `'.DB_PREFIX.'location` ADD `video` VARCHAR(255) NOT NULL;';
		 	$sqls[] = 'ALTER TABLE `'.DB_PREFIX.'location` ADD `gallery` TEXT NOT NULL';
		 	$sqls[] = 'ALTER TABLE `'.DB_PREFIX.'location` ADD `email` VARCHAR(255) NOT NULL;';
		 	$sqls[] = 'ALTER TABLE `'.DB_PREFIX.'location` ADD `country_id` INT(11) NOT NULL;';
		 	$sqls[] = 'ALTER TABLE `'.DB_PREFIX.'location` ADD `zone_id` INT(11) NOT NULL;';
		 	$sqls[] = 'ALTER TABLE `'.DB_PREFIX.'location` ADD `city` VARCHAR(255) NOT NULL';
		 	foreach( $sqls as $q ){
		 		$this->db->query( $q );
		 	}
		}
		$this->checkAddingSeo();
	}

	public function uninstall(){
		$this->removeSeo();
	}

	/**
	 * process adding seo urls follow languages and stores
	 */
	protected function checkAddingSeo(){
		// DEFAULT OPTIONS
		$this->load->model( 'design/seo_url' );
		$this->load->model( 'localisation/language' );
		$this->load->model( 'setting/store' );

		$stores = $this->model_setting_store->getStores();
		$languages = $this->model_localisation_language->getLanguages();
		$store_ids = array( 0 );
		foreach ( $stores as $store ) {
			$store_ids[] = isset( $store['store_id'] ) ? (int)$store['store_id'] : 0;
		}

		$query_strs = array(
				'extension/module/pavstorelocator' 		=> 'pavstorelocator'
		);

		foreach ( $store_ids as $store_id ) {
			foreach ( $languages as $language ) {

				$language_id = isset( $language['language_id'] ) ? (int)$language['language_id'] : 1;
				foreach ( $query_strs as $str => $val ) {
					$sql = "SELECT * FROM " . DB_PREFIX . "seo_url WHERE store_id = " . (int)$store_id . " AND language_id = " . (int)$language_id;
					$sql .= " AND query='".$this->db->escape( $str )."'";
					$query = $this->db->query( $sql );

					if ( ! $query->num_rows ) {
						$this->model_design_seo_url->addSeoUrl( array(
							'store_id'		=> $store_id,
							'language_id'	=> $language_id,
							'query'			=> $str,
							'keyword'		=> $val
						) );
					}
				}
			}
		}
	}

	/**
	 * process removing seo urls follow languages and stores
	 */
	public function removeSeo(){
		// DEFAULT OPTIONS
		$this->load->model( 'design/seo_url' );
		$this->load->model( 'localisation/language' );
		$this->load->model( 'setting/store' );

		$stores = $this->model_setting_store->getStores();
		$languages = $this->model_localisation_language->getLanguages();

		$store_ids = array( 0 );

		foreach ( $stores as $store ) {
			$store_ids[] = isset( $store['store_id'] ) ? (int)$store['store_id'] : 0;
		}

		$query_strs = array(
				'extension/module/pavstorelocator' 		=> 'flashsale'
		);
		
		foreach ( $store_ids as $store_id ) {
			foreach ( $languages as $language ) {

				$language_id = isset( $language['language_id'] ) ? (int)$language['language_id'] : 1;
				foreach ( $query_strs as $str => $val ) {
					$sql = "SELECT * FROM " . DB_PREFIX . "seo_url WHERE store_id = " . (int)$store_id . " AND language_id = " . (int)$language_id;
					$sql .= " AND query='".$this->db->escape( $str )."'";
					$query = $this->db->query( $sql );

					if ( $query->num_rows ) {
						foreach( $query->rows as $row ){
							$this->model_design_seo_url->deleteSeoUrl( $row['seo_url_id'] ); 
						}
					}
				}
			}
		}
	}

	/**
	 * process removing seo urls follow languages and stores
	 */
	public function installTable(){
		$sql = "
			CREATE TABLE `oc_location_description` (
			  `location_id` int(11) NOT NULL,
			  `language_id` int(11) NOT NULL,
			  `title` varchar(64) NOT NULL,
			  `description` mediumtext NOT NULL,
			  `meta_title` varchar(255) NOT NULL,
			  `meta_description` varchar(255) NOT NULL,
			  `meta_keyword` varchar(255) NOT NULL
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;

		";
	}

	/**
	 * Edit location after edting and adding
	 */
	public function edit( $location_id, $data ){

		$this->install();
		
		if( isset($data['images']) && is_array($data['images']) ){
			$data['images'] = implode( '|', $data['images'] );
		}else {
			$data['images'] = '';
		}

		
		$this->db->query("UPDATE " . DB_PREFIX . "location SET email = '" . $this->db->escape($data['email']) . "', video = '" . $this->db->escape($data['video']) . "',gallery = '" . $this->db->escape($data['images']) . "' WHERE location_id = '" . (int)$location_id . "'");
	}
}
?>