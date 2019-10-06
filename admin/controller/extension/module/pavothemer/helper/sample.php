<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

if ( ! defined( 'DIR_SYSTEM' ) ) exit();

/**
 *
 */
class PavoThemerSampleHelper {

	public static $instance = array();

	public $theme = null;
	public $sampleDir = '';
	public $develop = '';

	public static function instance( $theme = '' ) {
		if ( ! isset( self::$instance[ $theme ] ) ) {
			self::$instance[ $theme ] = new self( $theme );
		}

		return self::$instance[ $theme ];
	}

	public function __construct( $theme = '' ) {
		$this->theme = $theme;
		$this->themePath = DIR_CATALOG . 'view/theme/' . $this->theme . '/';
		$this->develop = DIR_CATALOG . 'view/theme/' . $this->theme . '/development/';
		$this->sampleDir = DIR_CATALOG . 'view/theme/' . $this->theme . '/sample/';
	}

	/**
	 * get samples backup histories inside the theme
	 */
	public function getProfiles() {
		$histories = glob( $this->sampleDir . 'profiles/*' );

		$sampleHistories = array();
		foreach ( $histories as $history ) {
			$history = basename( $history );
			if ( strpos( $history, '.' ) === false ) {
				$sampleHistories[] = $history;
			}
		}

		return $sampleHistories;
	}

	/**
	 * get single sample profile
	 */
	public function getProfile( $key = '' ) {
		$file = $this->sampleDir . 'profiles/' . $key . '/profile.json';
		return $this->getJsonContent( $file );
	}

	/**
	 * delete backup
	 */
	public function delete( $sample = '' ) {
		if ( ! $sample ) return false;
		$dir = $this->sampleDir . 'profiles/' . $sample . '/';
		if ( $dir ) {
			return $this->deleteDirectory( $dir );
		}
		return true;
	}

	/**
	 *
	 */
	public function deleteDirectory( $target = '' ) {
		if ( ! is_writable( $target ) ) {
			@chmod( $target, 0777 );
		}
	    if( is_dir( $target ) ){
	        $files = glob( $target . '*', GLOB_MARK );

	        foreach( $files as $file ) {
	            $this->deleteDirectory( $file );
	        }

	        return @rmdir( $target );
	    } elseif( is_file( $target ) ) {
	        return @unlink( $target );
	    }
	}

	/**
	 * create directory
	 */
	public function makeDir() {
		if ( ! is_dir( $this->sampleDir ) ) {
			if ( ! file_exists( dirname( $this->sampleDir ) ) ) {
				mkdir( dirname( $this->sampleDir ), 0777, true );
			}
			if ( ! is_writable( dirname( $this->sampleDir ) ) ) {
				@chmod( dirname( $this->sampleDir ), 0777 );
			}

			if ( ! file_exists( $this->sampleDir ) ) {
				mkdir( $this->sampleDir, 0777, true );
			}
		}

		// clean folder
		$profiles = $this->getProfiles();
		if ( $profiles ) {
			foreach ( $profiles as $profile ) {
				$dir = $this->sampleDir . 'profiles/' . $profile . '/';
				if ( ! is_writable( $dir ) ) {
					@chmod( dirname( $dir ), 0777 );
				}
				$glob = glob( $dir . '*' );
				if ( empty( $glob ) ) {
					rmdir( $dir );
				}
			}
		}

		$folder = 'pavothemer_' . $this->theme . '_' . time();
		$path = $this->sampleDir . 'profiles/' . $folder . '';
		if ( is_dir( $path ) ) {
			return $folder;
		}
		if ( ! is_writable( $this->sampleDir ) ) {
			@chmod( $this->sampleDir, 0777 );
		}
		return @mkdir( $path, 0777, true ) ? $folder : false;
	}

	/**
	 * write file
	 */
	public function write( $settings = array(), $profile = '', $type = '' ) {
		if ( ! $profile ) return false;
		$file = $this->sampleDir . 'profiles/' . $profile . '/profile.json';
		$content = $this->getJsonContent( $file );

		$content[$type] = $settings;
		if ( $fo = fopen( $file, 'w+' ) ) {
			fwrite( $fo, json_encode( $content ) );
			return fclose( $fo );
		}
		return true;
	}

	/**
	 * tables need to export
	 */
	public function getTablesName() {
		$file = $this->sampleDir . 'tables.json';
		return $this->getJsonContent( $file );
	}

	/**
	 * export sql file
	 */
	public function exportSQL( $data = array(), $profile = '' ) {
		if ( ! $profile ) return false;
		// profile directory
		$dir = $this->sampleDir . 'profiles/' . $profile;
		// create folder to storage xml data
		$xmlDataDir = $dir . '/data/';
		$status = $this->createdirectory( $xmlDataDir );

		if ( $status ) {
			$files = array( 'tables', 'rows' );
			// each files insert data
			foreach ( $files as $file ) {
				$fopen = fopen( $dir . '/' . $file . '.php', 'w+' );
				if ( $fopen && ! empty( $data[ $file ] ) ) {
					$string = '<?php' . "\n";
					foreach ( $data[ $file ] as $k => $line ) {
						if ( $file === 'rows' ) {
							$string .= '$query[\''.$file.'\'][\''.$k.'\'] = array();' . "\n";
							foreach ( $line as $l ) {
								$string .= '$query[\''.$file.'\'][\''.$k.'\'][] = \'' . str_replace( '`"DB_PREFIX"', '`\' . DB_PREFIX . \'', $l ) . '\';' . "\n";
							}
						} else {
							$string .= '$query[\''.$file.'\'][] = "' . str_replace( '`"DB_PREFIX"', '`" . DB_PREFIX . "', $line ) . '";' . "\n";
						}
					}

					fwrite( $fopen, $string );
					$status &= fclose( $fopen );
				}
			}

			if ( !empty($data['xml']) ) {
				// foreach ( $data['xml'] as $table => $xmlString ) {
				// 	$fopen = fopen($xmlDataDir.$table.'.xml', 'w+');
				// 	if ($fopen) {
				// 		fwrite($fopen, $xmlString);
				// 		$status &= fclose($fopen);
				// 	}
				// }
			}

		}

		return $status;
	}

	/**
	 * export images
	 * export images to images.json
	 */
	public function exportImages( $url = '', $profile = '' ) {
		$file = $this->sampleDir . 'profiles/' . $profile . '/images.json';
		// scan image folders
		$folders = array( 'demo' );
		$images = array();
		foreach ( $folders as $folder ) {
			$images = array_merge_recursive( $images, $this->getImageByFolder( DIR_IMAGE . 'catalog/' . $folder ) );
		}

		$data = array(
			'url'		=> $url,
			'images'	=> $images
		); 

		$fopen = fopen( $file, 'w+' );
		if ( $fopen ) {
			fwrite( $fopen, json_encode( $data ) );
			return fclose( $fopen );
		}

		return true;
	}

	/**
	 * get images inside folder
	 */
	public function getImageByFolder( $folder = '' ) {
		$globs = glob( $folder . '/*' );
		$results = array();
		foreach ( $globs as $glob ) {
			if ( is_dir( $glob ) ) {
				$results = array_merge_recursive( $results, $this->getImageByFolder( $glob ) );
			} else if ( is_file( $glob ) ) {
				$info = pathinfo( $glob );
				if ( isset( $info['extension'] ) && in_array( $info['extension'], array( 'png', 'jpg', 'jpeg', 'svg' ) ) ) {
					$size = getimagesize( $glob );
					if ( ! empty( $size['mime'] ) && isset( $size[0], $size[1] ) ) {
						$wxh = $size[0] . 'x' .  $size[1];
						if ( ! isset( $results[$wxh] ) ) {
							$results[$wxh] = array();
						}
						$results[$wxh][] = str_replace( DIR_IMAGE . 'catalog/', '', $glob );
					}
				}
			}
		}

		return $results;
	}

	/**
	 * download images
	 */
	public function downloadImages( $profile = '' ) {
		$file = $this->sampleDir . 'profiles/' . $profile . '/images.json';
		$data = $this->getJsonContent( $file );
		$url = ! empty( $data['url'] ) ? $data['url'] : false;
		if ( ! $url ) {
			return false;
		}

		$images = ! empty( $data['images'] ) ? $data['images'] : array();

		if ( $images ) foreach ( $images as $size => $files ) {
			if ( ! $files ) continue;

			foreach ( $files as $file ) {
				$image = basename( $file );
				$image_url = $url . 'image/catalog/' . $image;
				$image_file = DIR_IMAGE . 'catalog/' . $image;
				if ( file_exists( $image_file ) ) continue;

				PavothemerApiHelper::get( $image_url, array(
							'filename'			=> $image_file
						) );
			}
		}

		return true;
	}

	/**
	 * import sql query
	 */
	public function getImportSQL( $profile = '' ) {
		$dir = $this->sampleDir . 'profiles/' . $profile;
		$query = array( 'tables' => array(), 'rows' => array() );

		foreach ( $query as $file => $data ) {
			$file = $dir . '/' . $file . '.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}

		return $query;
	}

	/**
	 * zip profile to download
	 */
	public function zipProfile( $profile = '' ) {
		$folder = $this->sampleDir . 'profiles/' . $profile;
		$filename = $profile . '.zip';
		// backup before
		if ( file_exists( DIR_DOWNLOAD . $filename ) ) {
			return DIR_DOWNLOAD . $filename;
		}
		$zip = $this->zip( $folder, DIR_DOWNLOAD . $filename );
		if ( $zip ) {
			return DIR_DOWNLOAD . $filename;
		}

		return false;
	}

	/**
	 * create zip file
	 */
	public function zip( $source = '', $destination = '' ) {
	    if ( ! extension_loaded( 'zip' ) || ! file_exists( $source ) ) {
	        return false;
	    }

	    $zip = new ZipArchive();
	    if ( ! $zip->open( $destination, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
	        return false;
	    }

	    $source = str_replace( '\\', '/', realpath( $source ) );

	    if ( is_dir( $source ) === true ) {
	        $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $source ), RecursiveIteratorIterator::SELF_FIRST );

	        foreach ( $files as $file ) {
	            $file = str_replace('\\', '/', $file);

	            // Ignore "." and ".." folders
	            if ( in_array( substr( $file, strrpos( $file, '/' ) + 1 ), array( '.', '..' ) ) ) {
	                continue;
	            }

	            $file = realpath( $file );

	            if ( is_dir( $file ) === true ) {
	                $zip->addEmptyDir( str_replace( $source . '/', '', $file . '/' ) );
	            } elseif ( is_file( $file ) === true ) {
	                $zip->addFromString( str_replace( $source . '/', '', $file ), file_get_contents( $file ) );
	            }
	        }
	    } elseif ( is_file( $source ) === true ) {
	        $zip->addFromString( basename( $source ), file_get_contents( $source ) );
	    }

	    return $zip->close();
	}

	/**
	 * upzip file
	 * @return 0, 1, 2, 3
	 */
	public function extractProfile( $source = '' ) {
		if ( ! $source ) return 0;

		// rename tmp name -> zip
		if ( ! strpos( $source, '.tmp' ) ) return 1;
		$filename = basename( $source, '.tmp' );

		preg_match( '/^pavothemer_(.*?)_([0-9]*?).zip$/i', $filename, $match );
		if ( ! $match ) return 2;
		if ( empty( $match[1] ) || $match[1] !== $this->theme ) return 3;

		$file = dirname( $source ) . '/' . $filename;
		// rename
		rename( $source, $file );

		$zip = new ZipArchive();
		if ( $zip->open( $file ) === true ) {
			$zipFile = $this->sampleDir . 'profiles/' . basename( $file, '.zip' );
			if ( file_exists( $zipFile ) ) {
		    	$zip->close();
		    	return 4;
			}
		    $zip->extractTo( $zipFile );
		    $zip->close();

		    unlink( $file );
		 	return $zipFile;
		}

		return false;
	}

	/**
	 * theme config
	 * modules required
	 * php version
	 */
	public function getThemeConfigs() {
		// theme configuration
		$file = $this->develop . '/config.json';
		$content = $this->getJsonContent( $file );
		return $content ? $content : array();
	}

	/**
	 * content json file
	 */
	public function getJsonContent( $file = '' ) {
		if ( file_exists( $file ) ) {
			return json_decode( file_get_contents( $file ), true );
		}
		return array();
	}

	/**
	 * create image
	 */
	public function createImage( $width = '', $height = '', $bg = '', $txt_color = '', $dest = '' ) {
		$this->createdirectory( dirname( $dest ) );
		// Define the text to show
	    $text = $width . 'x' . $height;

	    // Create the image resource
	    $image = imagecreate($width, $height);

	    // We are making two colors one for BackGround and one for ForGround
		$bg = imagecolorallocate($image, base_convert(substr($bg, 0, 2), 16, 10),
											   base_convert(substr($bg, 2, 2), 16, 10),
											   base_convert(substr($bg, 4, 2), 16, 10));

		$txt_color = imagecolorallocate($image,base_convert(substr($txt_color, 0, 2), 16, 10),
											   base_convert(substr($txt_color, 2, 2), 16, 10),
											   base_convert(substr($txt_color, 4, 2), 16, 10));

	    // Fill the background color
	    imagefill( $image, 0, 0, $bg );

		// Calculating (Actually astimationg :) ) font size
		$fontsize = 20;// ( $width > $height ) ? ( $height / 10 ) : ( $width / 10 );

		// Write the text .. with some alignment astimations
		imagestring($image, $fontsize, ( $width / 2 ) - imagefontwidth( $fontsize ) * strlen( $text ), imagefontheight( $fontsize ) * strlen( $text ), $text, $txt_color);

	    // Tell the browser what kind of file is come in
	   	// header("Content-Type: image/png");
		if( preg_match("#.png#", $dest)){
			//Output the newly created image in png format
	    	imagepng($image, $dest );
		}

	   	if( preg_match("#.jpg#", $dest) || preg_match("#.jpeg#", $dest) ){
			//Output the newly created image in png format
	    	imagejpeg($image, $dest );
		}

	    //Free up resources
	    imagedestroy($image);
	}

	/**
	 * create directory
	 */
	public function createdirectory( $target = '' ) {
		$wrapper = null;

		// From php.net/mkdir user contributed notes.
		$target = str_replace( '//', '/', $target );

		// Put the wrapper back on the target.
		if ( $wrapper !== null ) {
			$target = $wrapper . '://' . $target;
		}

		/*
		 * Safe mode fails with a trailing slash under certain PHP versions.
		 * Use rtrim() instead of untrailingslashit to avoid formatting.php dependency.
		 */
		$target = rtrim($target, '/');
		if ( empty($target) )
			$target = '/';

		if ( file_exists( $target ) )
			return @is_dir( $target );

		// We need to find the permissions of the parent folder that exists and inherit that.
		$target_parent = dirname( $target );
		while ( '.' != $target_parent && ! is_dir( $target_parent ) ) {
			$target_parent = dirname( $target_parent );
		}

		// Get the permission bits.
		if ( $stat = @stat( $target_parent ) ) {
			$dir_perms = $stat['mode'] & 0007777;
		} else {
			$dir_perms = 0777;
		}

		if ( @mkdir( $target, $dir_perms, true ) ) {

			/*
			 * If a umask is set that modifies $dir_perms, we'll have to re-set
			 * the $dir_perms correctly with chmod()
			 */
			if ( $dir_perms != ( $dir_perms & ~umask() ) ) {
				$folder_parts = explode( '/', substr( $target, strlen( $target_parent ) + 1 ) );
				for ( $i = 1, $c = count( $folder_parts ); $i <= $c; $i++ ) {
					@chmod( $target_parent . '/' . implode( '/', array_slice( $folder_parts, 0, $i ) ), $dir_perms );
				}
			}

			return true;
		}

		return false;
	}

}