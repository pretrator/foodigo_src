<?php 
class ModelExtensionPavflashsaleSetting extends Model {
	 /**
	 * Install Hook Action to check adding SEO URL
	 */
	public function install() {
		
		$this->checkAddingSeo();
	}

	/**
	 * Uninstall Hook Action to check removing SEO URL
	 */
	public function uninstall(){
		$this->removeSeo();
		return true;
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
				'extension/module/pavflashsale' 		=> 'flashsale'
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
				'extension/module/pavflashsale' 		=> 'flashsale'
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
}
?>