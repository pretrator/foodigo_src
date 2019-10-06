<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
 */

/**
 * The ModelExtensionPavothemerBackup class
 */
class ModelExtensionPavobuilderPavobuilder extends Model {

    /**
     * make install action
     */
    public function intsall() {
        $created = $this->_isCreatedTable();
        if ( ! $created ) {
            return $this->createTables();
        }
    }

    /**
     * check table exists
     */
    public function _isCreatedTable() {
        $sql = 'SHOW TABLES LIKE "' . DB_PREFIX . 'pavobuilder"';
        $query = $this->db->query($sql);
        return $query->num_rows;
    }

    /**
     * create new tables if it does't exists
     */
    public function createTables() {
        return $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "pavobuilder` (
              `module_uniqid_id` CHAR(32) NOT NULL,
              `settings` LONGTEXT NOT NULL,
              PRIMARY KEY (`module_uniqid_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
    }

    /**
     * save page builder settings
     */
    public function saveBuilder( $module_uniqid_id = false, $settings = array() ) {
        // check install before save
        $this->intsall();

        $query = $this->db->query( 'SELECT * FROM `' . DB_PREFIX . 'pavobuilder` WHERE `module_uniqid_id` = "' . $this->db->escape( $module_uniqid_id ) . '"' );

        if ( $query->num_rows ) {
            $sql = 'UPDATE `' . DB_PREFIX . 'pavobuilder` SET `settings` = "' . $this->db->escape( json_encode( $settings ) ) . '" WHERE `module_uniqid_id` = "' . $this->db->escape( $module_uniqid_id ) . '"';
        } else {
            $sql = 'INSERT INTO `' . DB_PREFIX . 'pavobuilder` ( `settings`, `module_uniqid_id` ) VALUES ( "'.$this->db->escape( json_encode( $settings ) ).'", "'.$this->db->escape( $module_uniqid_id ).'" )';
        }

        return $this->db->query( $sql );
    }

    /**
     * get builder data
     */
    public function getBuilderData( $module_uniqid_id = false ) {
        $created = $this->_isCreatedTable();
        $result = array();
        if ( $created && $module_uniqid_id ) {
            $query = $this->db->query( 'SELECT * FROM `' . DB_PREFIX . 'pavobuilder` WHERE `module_uniqid_id` = "' . $this->db->escape( $module_uniqid_id ) . '"' );
            $result = $query->row;
            if ( $result && ! empty( $result['settings'] ) ) {
                $result['settings'] = json_decode( $result['settings'], true );
            }
        }
        return $result;
    }

    public function deleteBuilder($module_uniqid_id = false) {
      return $this->db->query( 'DELETE FROM `' . DB_PREFIX . 'pavobuilder` WHERE `module_uniqid_id` = "' . $this->db->escape( $module_uniqid_id ) . '"' );
    }

    public function clearData() {
      return $this->db->query("DELETE FROM `" . DB_PREFIX . "pavobuilder`");
    }

}
