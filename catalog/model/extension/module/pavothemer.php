<?php
/**
 * @package Pavo Themer Framework for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
 */

/**
 * @class ModelExtensionModulePavoThemer
 *
 * @version 1.0
 */
class ModelExtensionModulePavoThemer extends Model {

    public $themeConfig = array() ;

    public function isHome() {
        return (!isset($this->request->get['route']) || isset($this->request->get['route']) && $this->request->get['route'] == 'common/home');
    }

    /**
     * load setting config follow settings
     */
    public function __construct($registry) {
        parent::__construct( $registry );

        if( isset($this->request->get['profile']) && !empty($this->request->get['profile']) && !defined( "_DEMO_" ) ){
            $theme = $this->config->get( 'config_theme' );
            $file = DIR_APPLICATION . 'view/theme/' . $theme . '/development/profiles/'.trim($this->request->get['profile']).'.json';
            if( file_exists($file) ) {
                $this->themeConfig = json_decode( file_get_contents( $file ), true );
            }
        }
    }

    /**
     * get setting configure
     */
    public function getConfig( $key ) {
        return isset($this->themeConfig['pavothemer_'.$key]) ? $this->themeConfig['pavothemer_'.$key] : $this->config->get( 'pavothemer_'.$key );
    }

    /**
     * get Header builder content
     */
    public function getHeader() {
        $id = $this->getConfig( 'header_blockbuilder' );
        $hid = $this->getConfig( 'home_headerbuilder' );

        if ( $this->isHome() && $hid ) {
            $id = $hid;
        }

        if ( $id ) {
            $data['pavoheader'] = '';
            $this->load->model( 'setting/module' );
            $setting_info = $this->model_setting_module->getModule( $id );

            if ($setting_info && $setting_info['status']) {
                $obs = '';
                if ( isset($setting_info['absolute']) ) {
                    switch ($setting_info['absolute']) {
                        case 1:
                            # code...
                            $obs = ' style-absolute';
                            break;

                        case 2:
                            # code...
                            $obs = ' style-absolute-left';
                            break;

                        case 3:
                            # code...
                            $obs = ' style-absolute-right';
                            break;
                        
                        default:
                            # code...
                            $obs = '';
                            break;
                    }
                }
                return '<div class="pavo-header-builder'.$obs.'">'.$this->load->controller('extension/module/pavoheader', $setting_info).'</div>';
            }
        }
    }

    /**
     * get body class
     */
    public function getBodyClass() {
        $id = $this->getConfig( 'header_blockbuilder' );
        $hid = $this->getConfig( 'home_headerbuilder' );
        $class = '';

        if ( $this->isHome() && $hid ) {
            $id = $hid;
        }

        if ( $id ) {
            $this->load->model( 'setting/module' );
            $setting_info = $this->model_setting_module->getModule( $id );

            if ($setting_info && $setting_info['status']) {
                if ( isset($setting_info['absolute']) ) {
                    switch ($setting_info['absolute']) {
                        case 1:
                            # code...
                            $class = ' header-style-absolute';
                            break;

                        case 2:
                            # code...
                            $class = ' header-style-absolute-left';
                            break;

                        case 3:
                            # code...
                            $class = ' header-style-absolute-right';
                            break;
                        
                        default:
                            # code...
                            $class = '';
                            break;
                    }
                }
            }
        }
        return $class;
    }

    /**
     * get Header footer content
     */
    public function getFooter() {
        $id = $this->getConfig( 'footer_blockbuilder' );
        if( (int)$id ) {
            $data['blockbuilder'] = '';
            $this->load->model( 'setting/module' );
            $setting_info = $this->model_setting_module->getModule( $id );

            if ($setting_info && $setting_info['status']) {
               return $this->load->controller('extension/module/pavobuilder', $setting_info);
            }
        }
    }

    protected function getProductNavInfo( $product_info ){
        $this->load->model('tool/image');
        if ($product_info['image']) {
            $popup= $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
        } else {
            $popup = '';
        }
        if ($product_info['image']) {
            $thumb = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
        } else {
            $thumb= '';
        }

        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
        } else {
            $price = false;
        }

        return array(
            'name'       => $product_info['name'],
            'product_id' => $product_info['product_id'],
            'thumb'      => $thumb,
            'popup'      => $popup,
            'price'      => $price,
            'link'       =>  $this->url->link('product/product', 'product_id=' . $product_info['product_id'] )
        );
    }

    /**
     *
     */
    public function getProductsNav( $product_id ){

        $output = array();

        $query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id > '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  ORDER BY p.product_id LIMIT 1");


        $output['next'] =  $query->row ? $this->getProductNavInfo( $query->row ) : array();

        $query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id < '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  ORDER BY p.product_id LIMIT 1");

        $output['prev'] =  $query->row ? $this->getProductNavInfo( $query->row ) : array();

        return $output;
    }
      public function getProduct($product_id) {
        $query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

        if ($query->num_rows) {
            return array(
                'product_id'       => $query->row['product_id'],
                'name'             => $query->row['name'],
                'description'      => $query->row['description'],
                'meta_title'       => $query->row['meta_title'],
                'meta_description' => $query->row['meta_description'],
                'meta_keyword'     => $query->row['meta_keyword'],
                'tag'              => $query->row['tag'],
                'model'            => $query->row['model'],
                'sku'              => $query->row['sku'],
                'upc'              => $query->row['upc'],
                'ean'              => $query->row['ean'],
                'jan'              => $query->row['jan'],
                'isbn'             => $query->row['isbn'],
                'mpn'              => $query->row['mpn'],
                'location'         => $query->row['location'],
                'quantity'         => $query->row['quantity'],
                'stock_status'     => $query->row['stock_status'],
                'image'            => $query->row['image'],
                'manufacturer_id'  => $query->row['manufacturer_id'],
                'manufacturer'     => $query->row['manufacturer'],
                'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
                'special'          => $query->row['special'],
                'reward'           => $query->row['reward'],
                'points'           => $query->row['points'],
                'tax_class_id'     => $query->row['tax_class_id'],
                'date_available'   => $query->row['date_available'],
                'weight'           => $query->row['weight'],
                'weight_class_id'  => $query->row['weight_class_id'],
                'length'           => $query->row['length'],
                'width'            => $query->row['width'],
                'height'           => $query->row['height'],
                'length_class_id'  => $query->row['length_class_id'],
                'subtract'         => $query->row['subtract'],
                'rating'           => round($query->row['rating']),
                'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
                'minimum'          => $query->row['minimum'],
                'sort_order'       => $query->row['sort_order'],
                'status'           => $query->row['status'],
                'date_added'       => $query->row['date_added'],
                'date_modified'    => $query->row['date_modified'],
                'viewed'           => $query->row['viewed']
            );
        } else {
            return false;
        }
    }

    public function getMostviewedProducts($limit, $filter_categories)
    {
        $this->load->model('catalog/product');
        $product_data = array();

        $filter = '' ;
        $join  = '';
        if (!empty($filter_categories)) {
            $join .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) ";
            $filter .= " p2c.category_id IN (" . implode(",",$filter_categories) . ") AND ";
        }

        $query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p
        LEFT JOIN " . DB_PREFIX . "product_to_store p2s
        ON (p.product_id = p2s.product_id)
        ".$join."
        WHERE
        ".$filter."
         p.status = '1'
        AND p.date_available <= NOW()
        AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
        ORDER BY p.viewed DESC LIMIT " . (int)$limit);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
        }

        return $product_data;
    }

    public function getTopRatingProducts($limit, $filter_categories)
    {
        $filter = '' ;
        $join  = '';
        if (!empty($filter_categories)) {
            $join .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) ";
            $filter .= " p2c.category_id IN (" . implode(",",$filter_categories) . ") AND ";
        }

        $this->load->model('catalog/product');
        $product_data = array();
        $sql = ("SELECT p.*, r.rating FROM " . DB_PREFIX . "review r
                LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id)
                ".$join."
                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                WHERE
                    ".$filter."
                    p.date_available <= NOW() AND
                    p.status = '1' AND
                    r.status = '1' AND
                    pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                ORDER BY r.rating DESC LIMIT 0," . (int)$limit);

        $query = $this->db->query($sql);

        if ($query->num_rows > 0) {
            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
                $product_data[$result['product_id']]['rating'] = $result['rating'];
            }
        }

        return $product_data;
    }

    public function getProductSpecials($data = array(),$filter_categories)
    {
        $filter = '' ;
        $join  = '';
        if (!empty($filter_categories)) {
            $join .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) ";
            $filter .= " p2c.category_id IN (" . implode(",",$filter_categories) . ") AND ";
        }

        $sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1
        WHERE r1.product_id = ps.product_id
        AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps
        LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id)
        LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
        " . $join . "
        LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
        WHERE
        ".$filter."
        p.status = '1' AND p.date_available <= NOW() AND
         p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND
          ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND
           ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND
            (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id";

        $sort_data = array(
            'pd.name',
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
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $product_data = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
        }

        return $product_data;
    }

    public function getLatestProducts( $limit, $filter_categories ) {
        $key = 'product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit.md5( implode("", $filter_categories) );

        $this->cache->delete( $key );
        $product_data = $this->cache->get( $key );

        if (!$product_data) {

            $join   = '';
            $filter = '';
            if (!empty($filter_categories)) {
                $join .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) ";
                $filter .= " AND p2c.category_id IN (" . implode(",",$filter_categories) . ") ";
            }

            $query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) ". $join."  WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ".$filter." ORDER BY p.date_added DESC LIMIT " . (int)$limit);

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->cache->set('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $product_data);
        }

        return $product_data;
    }

    public function getBestSellerProducts( $limit, $filter_categories )
    {
         $key = 'product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit. '.' .  md5( implode( "",   $filter_categories ) );
        $product_data = $this->cache->get($key);

        if (!$product_data) {

            $filter = '' ;
            $join  = '';
            if (!empty($filter_categories)) {
                $join .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) ";
                $filter .= " AND p2c.category_id IN (" . implode(",",$filter_categories) . ") ";
            }

            $product_data = array();
            $query = $this->db->query("SELECT op.product_id, SUM(op.quantity) AS total
            FROM " . DB_PREFIX . "order_product op
            LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)
            LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id)
            LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
            " . $join . "
            WHERE o.order_status_id > '0' ".$filter." AND
            p.status = '1' AND p.date_available <= NOW()
            AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
            GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->cache->set($key, $product_data);
        }

        return $product_data;
    }

    public function getMostviewedProductsCategory($limit, $category_id)
    {
        $this->load->model('catalog/product');
        $product_data = array();

        $query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p
        LEFT JOIN " . DB_PREFIX . "product_to_store p2s
        ON (p.product_id = p2s.product_id)
        LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
        WHERE
         p2c.category_id = '" . (int)$category_id . "' AND
         p.status = '1'
        AND p.date_available <= NOW()
        AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
        ORDER BY p.viewed DESC LIMIT " . (int)$limit);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
        }

        return $product_data;
    }

    public function getTopRatingProductsCategory($limit, $category_id)
    {

        $product_data = array();
        $sql = ("SELECT p.*, r.rating FROM " . DB_PREFIX . "review r
                LEFT JOIN " . DB_PREFIX . "product p ON (r.product_id = p.product_id)
                LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
                WHERE
                    p2c.category_id = '" . (int)$category_id . "' AND
                    p.date_available <= NOW() AND
                    p.status = '1' AND
                    r.status = '1' AND
                    pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                ORDER BY r.rating DESC LIMIT 0," . (int)$limit);

        $query = $this->db->query($sql);

        if ($query->num_rows > 0) {
            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->model_catalog_product->getProduct($result['product_id']);
                $product_data[$result['product_id']]['rating'] = $result['rating'];
            }
        }

        return $product_data;
    }

    public function getProductSpecialsCategory($data = array(),$category_id)
    {
        $sql = "SELECT DISTINCT ps.product_id, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r1
        WHERE r1.product_id = ps.product_id
        AND r1.status = '1' GROUP BY r1.product_id) AS rating FROM " . DB_PREFIX . "product_special ps
        LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id)
        LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
        LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
        LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
        WHERE
        p2c.category_id = '" . (int)$category_id . "' AND
        p.status = '1' AND p.date_available <= NOW() AND
         p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND
          ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND
           ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND
            (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) GROUP BY ps.product_id";

        $sort_data = array(
            'pd.name',
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
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $product_data = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
        }

        return $product_data;
    }

    public function getLatestProductsCategory($limit, $category_id) {
        $key = 'product.latest.'
            . (int)$this->config->get('config_language_id')
            . '.' . (int)$this->config->get('config_store_id')
            . '.' . $this->config->get('config_customer_group_id')
            . '.' . (int)$limit. '.' . (int) $category_id;
        $product_data = $this->cache->get($key);

        if (!$product_data) {
            $query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
                LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
                WHERE p.status = '1'
                AND p.date_available <= NOW()
                AND p2c.category_id = '" . (int)$category_id . "'
                AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY p.date_added DESC LIMIT " . (int)$limit);

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->cache->set($key, $product_data);
        }

        return $product_data;
    }

    public function getBestSellerProductsCategory($limit, $category_id)
    {
         $key = 'product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit. '.' . (int) $category_id;
        $product_data = $this->cache->get($key);

        if (!$product_data) {
            $product_data = array();

            $query = $this->db->query("SELECT op.product_id, SUM(op.quantity) AS total
            FROM " . DB_PREFIX . "order_product op
            LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)
            LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id)
            LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
            LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
            WHERE o.order_status_id > '0' AND
            p2c.category_id = '" . (int)$category_id . "' AND
            p.status = '1' AND p.date_available <= NOW()
            AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
            GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->cache->set($key, $product_data);
        }

        return $product_data;
    }

    public function getBanners($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "banner";

        $sort_data = array(
            'name',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function compressCss( $styles ){
        if( !$this->getConfig('compresscss') ){
            return $styles;
        }

        $this->load->library('csscompressor');
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $siteURL = HTTPS_SERVER;
        }else {
            $siteURL = HTTP_SERVER;
        }

        $excludes = array( 'swiper.min.css' );
        $efiles = $this->getConfig( 'excludecssfiles' );

        if ( !empty($efiles) ) {
            $efiles = preg_replace( "#\s+#", "", $efiles );
            $efiles = explode( "," , $efiles );
            $excludes = array_merge_recursive( $efiles , $excludes );
        }

        $mainCssFile = md5( serialize( $styles ).serialize($excludes).$siteURL ).'.css';
        $files = array();
        if( !$this->csscompressor->isExisted( $mainCssFile) ) {
            $string = '';
            foreach ( $styles as $file ) {
                $css = preg_match( "#^http#", $file['href'] ) ? $file['href'] : $siteURL.$file['href'];
                $t = explode( "/", ($css) );
                if( !in_array($t[count($t)-1], $excludes) ) {
                    $css = str_replace(HTTP_SERVER, dirname(DIR_APPLICATION) . '/', $css);
                    $css = str_replace(HTTPS_SERVER, dirname(DIR_APPLICATION) . '/', $css);
                    if (file_exists($css)) {
                        $content = file_get_contents( $css );
                        if( !empty($content)  ){
                            $string .= " \r\n \n\r ". $this->csscompressor->process( $content, $css );
                        }
                    }
                } else {
                    $files[$file['href']] = $file;
                }
            }
            if( $string ) {
                $file = $this->csscompressor->saveCache( $string, $mainCssFile );
            }
        }
        if ( $excludes ) {
            foreach( $styles as $key =>  $file ){
                $css =  preg_match( "#^http#", $file['href'] ) ?$file['href']:$siteURL.$file['href'];
                $t = explode( "/", ($css) ) ;
                if( in_array($t[count($t)-1], $excludes)  ){
                    $files[$key] = $file;
                }
            }
        }
        // $f = $this->csscompressor->getFileURL( $mainCssFile, $siteURL );
        $f = 'catalog/view/javascript/pavothemer/'.$mainCssFile;
        $files[$f] = array(
            'href'  => $f,
            'rel'   => 'stylesheet',
            'media' => 'screen'
        ) ;
        return $files;
    }

    public function compressJs( $jsfiles ) {
        if( !$this->getConfig('combinejs') ) {
            return $jsfiles;
        }

        $this->load->library('csscompressor');

        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $siteURL = HTTPS_SERVER;
        } else {
            $siteURL = HTTP_SERVER;
        }

        $excludes = array( 'jquery.js' );
        $efiles = $this->getConfig( 'excludejsfiles' );

        if(  !empty($efiles) ){
            $efiles = preg_replace( "#\s+#", "", $efiles );
            $efiles = explode( "," , $efiles );
            $excludes = array_merge_recursive( $efiles , $excludes );
        }

        $mainJsFile = md5( serialize( $jsfiles ).serialize($excludes).$siteURL ).'.js';
        $files = array();

        if( !$this->csscompressor->isExisted( $mainJsFile) ){
            $string = "";
            foreach( $jsfiles as  $key => $file ){
                $js =  preg_match( "#^http#", $file) ?$file:$siteURL.$file;
                $t = explode( "/", ($js) ) ;
                if( !in_array($t[count($t)-1], $excludes) && !preg_match("#//#", $file)  ){
                    $content = file_get_contents( $js );
                    if( !empty($content)  ){
                        $string  .=  " \r\n \n\r ". $content;
                    }
                } else {
                    $files[$key] = $file;
                }
            }
            if( $string ){
                $file = $this->csscompressor->saveCache( $string, $mainJsFile );
            }
        }

        if( $excludes ){
            foreach( $jsfiles as $key =>  $file ){
                $js =  preg_match( "#^http#", $file ) ?$file:$siteURL.$file;
                $t = explode( "/", ($js) ) ;
                if( in_array($t[count($t)-1], $excludes) || preg_match("#//#", $file)  ){
                    $files[$key] = $file;
                }
            }
        }

        // $f = $this->csscompressor->getFileURL( $mainJsFile,  $siteURL );
        $f = 'catalog/view/javascript/pavothemer/'.$mainJsFile;
        $files[$mainJsFile] = $f;


        return $files;
    }
}