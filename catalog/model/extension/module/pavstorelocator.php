<?php
class ModelExtensionModulePavstorelocator extends Model {
	public function getLocations($data = array()) { 

		 $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "location WHERE 1=1");

		return $query->rows;
	}
}
