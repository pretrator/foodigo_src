<?php
/**
 * @package Pav Flash Sale module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

/**
 * @class ModelPavblogcategory
 *
 * @version 1.0
 */
class ModelExtensionModulePavoBuilder extends Model {

    /**
     * check table exists
     */
    public function _isCreatedTable() {
        $sql = 'SHOW TABLES LIKE "' . DB_PREFIX . 'pavobuilder"';
        $query = $this->db->query($sql);
        return $query->num_rows;
    }

	public function getBuilderData( $module_uniqid_id = false, $default = array() ) {
		$created = $this->_isCreatedTable();
        if ( $created ) {
            $query = $this->db->query( 'SELECT * FROM `' . DB_PREFIX . 'pavobuilder` WHERE `module_uniqid_id` = "' . $this->db->escape( $module_uniqid_id ) . '"' );
            $result = $query->row;
            if ( $result && ! empty( $result['settings'] ) ) {
                return json_decode( $result['settings'], true );
            }
        }
        return $default;
	}

	/**
	 *
	 */
	public function getProductSpecials($data = array()) {
		$this->load->model( 'catalog/product' );
		$filter = "  ";
		if(isset($data['start_date']) && isset($data['to_date'])){
			$filter .= " AND ((ps.date_start <= '{$data['to_date']}') AND (ps.date_end >= '{$data['start_date']}')) ";
		}

		$join = "";
		if (!empty($data['filter_categories'])) {
			$join .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (ps.product_id = p2c.product_id) ";
			$filter .= " AND p2c.category_id IN (" . implode(",",$data["filter_categories"]) . ")";
		}
		$sql = "SELECT DISTINCT ps.product_id,ps.date_start,ps.date_end,ps.price AS special, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = ps.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) ".$join." LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' {$filter} GROUP BY ps.product_id";

		$sort_data = array(
			'pd.name',
			'p.date_added',
			'p.model',
			'ps.price',
			'rating',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY p.sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$product_data = array();

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
			$product_data[$result['product_id']]["date_start"] = $result["date_start"];
			$product_data[$result['product_id']]["date_end"] = $result["date_end"];
			$product_data[$result['product_id']]["special"] = $result["special"];
		}

		return $product_data;
	}

	/**
	 *
	 */
	public function getTotalBought($product_id = 0, $order_status_id = 5){
		$bought = 0;

		$query = $this->db->query("SELECT sum(quantity) as total FROM `" . DB_PREFIX . "order_product` op
			LEFT JOIN `".DB_PREFIX."order` AS o ON op.order_id = o.order_id WHERE op.product_id = ".$product_id." AND o.order_status_id=".$order_status_id);

		if($query->num_rows){
			return $query->row['total'];
		}
		return 0;
	}

}
