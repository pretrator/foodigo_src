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
class ModelExtensionPavothemerBackup extends Model {

    /**
     * Backup the whole database or just some tables
     * Use '*' for whole database or 'table1 table2 table3...'
     * @param string $tables
     */
    public function backup( $tables = '*', $file = '', $gzip = false, $ignores = array( 'user', 'user_group' ) ) {
        // Report all errors
        error_reporting(E_ALL);
        // Set script max execution time
        set_time_limit( 900 ); // 15 minutes
        try {
            /**
            * Tables to export
            */
            if( $tables == '*' ) {
                $tables = array();
                $result = $this->db->query( 'SHOW TABLES' );

                if ( ! empty( $result->rows ) ) {
                    foreach ( $result->rows as $k => $row ) {
                        $values = array_values( $row );
                        if ( ! empty( $values ) ) {
                            $value = $values[0];
                            if ( ! in_array( str_replace( DB_PREFIX, '', $value ), $ignores ) ) {
                                $tables[] = $value;
                            }
                        }
                    }
                }
            } else {
                $tables = is_array( $tables ) ? $tables : explode( ',', $tables );
            }

            $sql = '';
            /**
             * Iterate tables
             */
            foreach( $tables as $table ) {

                $table_name = ( strpos( $table, DB_PREFIX ) === 0 ) ? str_replace( DB_PREFIX, '_DB_PREFIX_', $table ) : $table;
                /**
                 * CREATE TABLE
                 */
                $sql .= 'DROP TABLE IF EXISTS `'. $table_name . '`;';
                $query = $this->db->query( 'SHOW CREATE TABLE `'.$table.'`' );
                $row = $query->row;
                if ( ! empty( $row['Create Table'] ) ) {
                    $sql .= "\n" . str_replace( $table, $table_name, $row['Create Table'] ) . ";\n";

                    /**
                     * INSERT INTO
                     */
                    $query = $this->db->query( 'SELECT COUNT(*) AS numrows FROM `'.$table.'`' );
                    $numRows = ! empty( $query->row['numrows'] ) ? $query->row['numrows'] : 0;

                    // Split table in batches in order to not exhaust system memory
                    $batchSize = 1000; // Number of rows per batch
                    $numBatches = intval( $numRows / $batchSize ) + 1; // Number of while-loop calls to perform

                    for ( $b = 1; $b <= $numBatches; $b++ ) {
                        $query = $this->db->query( 'SELECT * FROM `'.$table.'` LIMIT '.( $b * $batchSize- $batchSize) . ',' . $batchSize );
                        $rows = $query->rows;

                        foreach ( $rows as $row ) {
                            $data = array_values( $row );
                            $values = array();
                            foreach ( $data as $k => $val ) {
                                $val = addslashes( $val );
                                $val = str_replace( "\n","\\n", $val );
                                $val = str_replace( "\r","\\r", $val );
                                $values[] = $val;
                            }
                            $sql .= 'INSERT INTO `' . $table_name . '` VALUES(';
                            $sql .= "'" . implode( "','", $values ) . "'";
                            // $sql .= '"' . implode( '","', $values ) . '"';
                            $sql.= ");\n";
                        }

                        $this->saveFile( $sql, $file );
                        $sql = '';
                    }

                    $sql.="\n\n";
                }
            }

            if ( $gzip ) {
                $this->gzipBackupFile( $file );
            }
        } catch ( Exception $e ) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * Save SQL to file
     * @param string $sql
     */
    protected function saveFile( &$sql, $file = '' ) {
        if ( ! $sql ) return false;

        try {

            if ( ! file_exists( dirname( $file ) ) ) {
                mkdir( dirname( $file ), 0777, true );
            }

            file_put_contents( $file, $sql, FILE_APPEND | LOCK_EX );

        } catch ( Exception $e ) {
            return $e->getMessage();
        }

        return true;
    }

    /*
     * Gzip backup file
     *
     * @param integer $level GZIP compression level (default: 9)
     * @return string New filename (with .gz appended) if success, or false if operation fails
     */
    protected function gzipBackupFile( $source = '', $level = 9 ) {
        $dest =  $source . '.gz';

        $mode = 'wb' . $level;
        if ($fpOut = gzopen($dest, $mode)) {
            if ($fpIn = fopen($source,'rb')) {
                while (!feof($fpIn)) {
                    gzwrite($fpOut, fread($fpIn, 1024 * 256));
                }
                fclose($fpIn);
            } else {
                return false;
            }
            gzclose($fpOut);
            if( ! unlink( $source ) ) {
                return false;
            }
        } else {
            return false;
        }

        return $dest;
    }
}
