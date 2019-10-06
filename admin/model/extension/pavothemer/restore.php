<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
 */

/**
 * The ModelExtensionPavothemerRestore class
 */
class ModelExtensionPavothemerRestore extends Model {

    /**
     * Backup the whole database or just some tables
     * Use '*' for whole database or 'table1 table2 table3...'
     * @param string $tables
     */
    public function restore( $file = '' ) {
        // Report all errors
        error_reporting( E_ALL );
        // Set script max execution time
        set_time_limit( 900 ); // 15 minutes

        $this->db->query( 'START TRANSACTION' );
        try {
            $sql = '';
            $multiLineComment = false;

            /**
             * Gunzip file if gzipped
             */
            $backupFileIsGzipped = substr( basename( $file ), -3, 3 ) == '.gz' ? true : false;
            if ( $backupFileIsGzipped ) {
                if ( ! $file = $this->gunzipBackupFile( $file ) ) {
                    throw new Exception( 'ERROR: couldn\'t gunzip backup file ' . $file );
                }
            }

            /**
             * Read backup file line by line
             */
            $handle = fopen( $file, 'r' );
            if ($handle) {
                while ( ($line = fgets($handle)) !== false ) {
                    $line = ltrim( rtrim($line) );
                    if ( strlen($line) > 1 ) { // avoid blank lines
                        $lineIsComment = false;
                        if ( preg_match('/^\/\*/', $line) ) {
                            $multiLineComment = true;
                            $lineIsComment = true;
                        }
                        if ( $multiLineComment || preg_match('/^\/\//', $line) ) {
                            $lineIsComment = true;
                        }

                        if ( ! $lineIsComment ) {
                            $sql .= str_replace( '_DB_PREFIX_', DB_PREFIX, $line );
                            if ( preg_match('/;$/', $line) ) {
                                // execute query
                                $query = $this->db->query( $sql );
                                $sql = '';
                            }
                        } else if ( preg_match('/\*\/$/', $line ) ) {
                            $multiLineComment = false;
                        }
                    }
                }
                fclose($handle);
                $this->db->query( 'COMMIT' );
            } else {
                throw new Exception( 'ERROR: couldn\'t open backup file ' . $file );
            }
        } catch ( Exception $e ) {
            // rollback if has error
            $this->db->query( 'ROLLBACK' );
            return $e->getMessage();
        }

        if ( $backupFileIsGzipped ) {
            unlink( $file );
        }

        return true;
    }

    /**
     * Gunzip backup file
     *
     * @return string New filename (without .gz appended and without backup directory) if success, or false if operation fails
     */
    protected function gunzipBackupFile( $file = '' ) {
        // Raising this value may increase performance
        $bufferSize = 4096; // read 4kb at a time
        $error = false;

        $dest = dirname( $file ) . '/' . substr( $file, 0, -3 );

        // Remove $dest file if exists
        if ( file_exists($dest) ) {
            if ( !unlink($dest) ) {
                return false;
            }
        }

        // Open gzipped and destination files in binary mode
        if ( ! $srcFile = gzopen( $file, 'rb' ) ) {
            return false;
        }
        if ( ! $dstFile = fopen( $dest, 'wb' ) ) {
            return false;
        }

        while ( ! gzeof( $srcFile ) ) {
            // Read buffer-size bytes
            // Both fwrite and gzread are binary-safe
            if( ! fwrite( $dstFile, gzread($srcFile, $bufferSize) ) ) {
                return false;
            }
        }

        fclose($dstFile);
        gzclose($srcFile);
        // Return backup filename excluding backup directory
        return $dest;
    }
}

