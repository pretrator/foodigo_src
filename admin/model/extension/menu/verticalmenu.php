<?php
/******************************************************
 * @package Pav Verticalmenu module for Opencart 3.x
 * @version 2.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) September 2013 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/

class ModelExtensionMenuVerticalmenu extends Model {
	/**
	 * @var Array $children as collections of childrent menus
	 *
	 * @accesss protected
	 */
	protected $children = array();

	/**
	 * Get menu information by id
	 */
	public function getInfo( $id ){
		$sql = ' SELECT m.*, md.title,md.description FROM ' . DB_PREFIX . 'verticalmenu m LEFT JOIN '
							.DB_PREFIX.'verticalmenu_description md ON m.verticalmenu_id=md.verticalmenu_id AND language_id='.(int)$this->config->get('config_language_id') ;

		$sql .= ' WHERE m.verticalmenu_id='.(int)$id;

		$query = $this->db->query( $sql );
		return $query->row;
	}

	/**
	 * get menu description by id
	 */
	public function getMenuDescription( $id ){
		$sql = 'SELECT * FROM '.DB_PREFIX."verticalmenu_description WHERE verticalmenu_id=".$id;
		$query = $this->db->query( $sql );
		return $query->rows;
	}


	/**
	 * get get all  Menu Childrens by Id
	 */
	public function getChild( $id=null, $store_id = 0){
		$sql = ' SELECT m.*, md.title,md.description FROM ' . DB_PREFIX . 'verticalmenu m LEFT JOIN '
								.DB_PREFIX.'verticalmenu_description md ON m.verticalmenu_id=md.verticalmenu_id AND language_id='.(int)$this->config->get('config_language_id') ;

        	$sql .= ' WHERE store_id='.(int)$store_id;
                if( $id != null ) {
			$sql .= ' AND parent_id='.(int)$id;
		}
		$sql .= ' ORDER BY `position`  ';
		$query = $this->db->query( $sql );
		return $query->rows;
	}

	/**
	 * whethere parent has menu childrens
	 */
	public function hasChild( $id ){
		return isset($this->children[$id]);
	}

	/**
	 * get collection of menu childrens by parent ID.
	 */
	public function getNodes( $id ){
		return $this->children[$id];
	}

	//start fix delete tree
	/**
	 * delete mega menu data by id
	 */
	public function delete( $id, $store_id){
		$childs = $this->getChild( null, $store_id );
		foreach($childs as $child ){
			$this->children[$child['parent_id']][] = $child;
		}
		$this->recursiveDelete($id, $store_id);
	}
	/**
	 * recursive delete tree
	 */
	public function recursiveDelete($parent_id, $store_id)
	{
		$sql = " DELETE FROM ".DB_PREFIX ."verticalmenu_description WHERE verticalmenu_id=".(int)$parent_id .";";
		$this->db->query($sql);
		$sql = " DELETE FROM ".DB_PREFIX ."verticalmenu WHERE store_id = ".$store_id." AND verticalmenu_id=".(int)$parent_id .";";
		$this->db->query($sql);

		if( $this->hasChild($parent_id) ){
			$data = $this->getNodes( $parent_id );
			foreach( $data as $menu ){
				if($menu['verticalmenu_id'] > 1) {
					 $this->recursiveDelete( $menu['verticalmenu_id'], $store_id );
				}
			}
		}
	}
	//end fix delete tree

	/**
	 * Render Tree Menu by ID
	 */
	public function getTree( $id=null, $store_id = 0 , $selected ){
		$childs = $this->getChild( $id, $store_id );
		foreach($childs as $child ){
			$this->children[$child['parent_id']][] = $child;
		}
		$parent = 1 ;
		$output = $this->genTree( $parent, 1, $store_id , $selected );
		return $output;
	}



	public function genTree( $parent, $level, $store_id = 0, $selected=0 ){
		if( $this->hasChild($parent) ){
			$data = $this->getNodes( $parent );
			$t = $level == 1?" sortable":"";
			$output = '<ol class="level'.$level. $t.' ">';

			$store = ($store_id > 0)?'&store_id='.$store_id:'';

			foreach( $data as $menu ){
				$url  = $this->url->link('extension/module/pavverticalmenu', 'id='.$menu['verticalmenu_id'].$store.'&user_token=' . $this->session->data['user_token'], true) ;
				$cls = $menu['verticalmenu_id'] == $selected ? 'class="active"':"";
				$output .='<li id="list_'.$menu['verticalmenu_id'].'" '.$cls.' >
				<div><span class="disclose"><span></span></span>'.($menu['title']?$menu['title']:"").' (ID:'.$menu['verticalmenu_id'].') <a class="quickedit" rel="id_'.$menu['verticalmenu_id'].'" href="'.$url .'">E</a><span class="quickdel" rel="id_'.$menu['verticalmenu_id'].'">D</span></div>';
				if($menu['verticalmenu_id'] > 1) {
					$output .= $this->genTree( $menu['verticalmenu_id'], $level+1, $store_id, $selected );
				}
				$output .= '</li>';
			}
			$output .= '</ol>';
			return $output;
		}
		return ;
	}

	/**
	 * render dropdown menu
	 */
	public function getDropdown( $id=null, $selected=1, $store_id = 0  ){
		$this->children = array();
		$childs = $this->getChild( $id, $store_id );
		foreach($childs as $child ){
			$this->children[$child['parent_id']][] = $child;
		}

		$output = '<select class="form-control" name="verticalmenu[parent_id]" >';
		$output .='<option value="1">ROOT</option>';
		$output .= $this->genOption( 1 ,1, $selected );
		$output .= '</select>';
		return $output ;
	}

	/**
	 * render option of dropdown as subs
	 */
	public function genOption( $parent, $level=0, $selected){
		$output = '';
		if( $this->hasChild($parent) ){
			$data = $this->getNodes( $parent );

			foreach( $data as $menu ){
				$select = $selected == $menu['verticalmenu_id'] ? 'selected="selected"':"";
				$output .= '<option value="'.$menu['verticalmenu_id'].'" '.$select.'>'.str_repeat("-",$level) ." ".$menu['title'].' (ID:'.$menu['verticalmenu_id'].')</option>';
				$output .= $this->genOption(  $menu['verticalmenu_id'],$level+1, $selected );
			}
		}

		return $output;
	}

	/**
	 * Mass Update Data for list of childrens by prent IDs
	 */
	public function massUpdate( $data, $root ){
		$child = array();
		foreach( $data as $id => $parentId ){
			if( $parentId <=0 ){
				$parentId = $root;
			}
			$child[$parentId][] = $id;
		}

		foreach( $child as $parentId => $menus ){
			$i = 1;
			foreach( $menus as $menuId ){
				$sql = " UPDATE  ". DB_PREFIX . "verticalmenu SET parent_id=".(int)$parentId.', position='.$i.' WHERE verticalmenu_id='.(int)$menuId;
				$this->db->query( $sql );
				$i++;
			}
		}
	}


	//start import category
	public function checkExitItemMenu($category, $store_id){
		$query = $this->db->query("SELECT verticalmenu_id FROM ".DB_PREFIX."verticalmenu WHERE store_id = ".$store_id." AND `type`='category' AND item=".$category['category_id']);
		return $query->num_rows;
	}
	public function deletecategories($store_id) {
		$query = $this->db->query("SELECT verticalmenu_id FROM ".DB_PREFIX."verticalmenu WHERE store_id = ".$store_id);
		if ($query->num_rows) {
			foreach ($query->rows as $row) {
				$this->db->query( "DELETE FROM ".DB_PREFIX ."verticalmenu_description WHERE verticalmenu_id = ".$row['verticalmenu_id'] );
			}
		}
		$this->db->query( "DELETE FROM ".DB_PREFIX ."verticalmenu WHERE store_id = ".$store_id );
	}

	public function importCategories($store_id = 0){
		$sql = "SELECT cd.`name`,c.* FROM ".DB_PREFIX ."category c
				LEFT JOIN ".DB_PREFIX ."category_description cd ON c.category_id = cd.category_id
				WHERE  cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
				ORDER BY parent_id ASC";
		$query = $this->db->query( $sql );
		if($query->num_rows){
			$categories = $query->rows;
		}
		$this->load->model('catalog/category');
		foreach ($categories as &$category){
			$category['language'] = $this->model_catalog_category->getCategoryDescriptions($category['category_id']);

			if($this->checkExitItemMenu($category, $store_id) == 0){
				if((int)$category['parent_id'] > 0){
					$query1 = $this->db->query("SELECT verticalmenu_id FROM ".DB_PREFIX."verticalmenu WHERE store_id = ".$store_id." AND `type`='category' AND item='".$category['parent_id']."'");
					if($query1->num_rows){
						$verticalmenu_parent_id = (int)$query1->row['verticalmenu_id'];
					}
				} else {
					$verticalmenu_parent_id = 1;
				}
				$this->insertCategory($category, $verticalmenu_parent_id, $store_id);
			}
		}
	}
	public function insertCategory($category = array(), $verticalmenu_parent_id, $store_id = 0){
		$data = array();
		$data['verticalmenu']['position'] = 99;
		$data['verticalmenu']['item'] = $category['category_id'];
		$data['verticalmenu']['published'] = 1;
		$data['verticalmenu']['parent_id'] = $verticalmenu_parent_id;
		$data['verticalmenu']['show_title'] = 1;
		$data['verticalmenu']['widget_id'] = 1;
		$data['verticalmenu']['type_submenu'] = 'menu';
		$data['verticalmenu']['type'] = 'category';
		$data['verticalmenu']['colums'] = 1;
		$data['verticalmenu']['store_id'] = $store_id;
		$data['verticalmenu']['is_group'] = 0;

		$sql = "INSERT INTO ".DB_PREFIX . "verticalmenu ( `";
		$tmp = array();
		$vals = array();
		foreach( $data["verticalmenu"] as $key => $value ){
			$tmp[] = $key;
			$vals[]=$this->db->escape($value);
		}
	 	$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
	 	$this->db->query( $sql );
	 	$data['verticalmenu']['verticalmenu_id'] = $this->db->getLastId();

	 	$this->load->model('localisation/language');
	 	$languages = $this->model_localisation_language->getLanguages();

	 	if( isset($category["language"]) ){
	 		$sql = " DELETE FROM ".DB_PREFIX ."verticalmenu_description WHERE verticalmenu_id=".(int)$data["verticalmenu"]['verticalmenu_id'] ;
	 		$this->db->query( $sql );

	 		foreach( $category["language"] as $key => $categorydes ){

	 			$sql = "INSERT INTO ".DB_PREFIX ."verticalmenu_description(`language_id`, `verticalmenu_id`,`title`)
							VALUES(".$key.",'".$data['verticalmenu']['verticalmenu_id']."','".$this->db->escape($categorydes['name'])."') ";
	 			$this->db->query( $sql );
	 		}
	 	}
	}
	//end import category

	/**
	 * Edit Or Create new children
	 */
	public function editData( $data ) {

		$query = $this->db->query( "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()
        AND COLUMN_NAME='badges' AND TABLE_NAME='".DB_PREFIX."verticalmenu'");
		if(count($query->rows) <= 0){
			$query = $this->db->query("ALTER TABLE `".DB_PREFIX."verticalmenu` ADD COLUMN `badges` text DEFAULT ''");
		}

		if( $data["verticalmenu"] ){
			if( (int)$data['verticalmenu']['verticalmenu_id'] > 0 ){
				$sql = " UPDATE  ". DB_PREFIX . "verticalmenu SET  ";
				$tmp = array();
				foreach( $data["verticalmenu"] as $key => $value ){
					if( $key != "verticalmenu_id" ){
						$tmp[] = "`".$key."`='".$this->db->escape($value)."'";
					}
				}

				$sql .= implode( " , ", $tmp );
				$sql .= " WHERE verticalmenu_id=".$data['verticalmenu']['verticalmenu_id'];

				$this->db->query( $sql );
			} else {
				$data['verticalmenu']['position'] = 99;
				$sql = "INSERT INTO ".DB_PREFIX . "verticalmenu ( `";
				$tmp = array();
				$vals = array();
				foreach( $data["verticalmenu"] as $key => $value ){
					$tmp[] = $key;
					$vals[]=$this->db->escape($value);
				}

			 	$sql .= implode("` , `",$tmp)."`) VALUES ('".implode("','",$vals)."') ";
				$this->db->query( $sql );
				$data['verticalmenu']['verticalmenu_id'] = $this->db->getLastId();
			}
		}

		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();

		if( isset($data["verticalmenu_description"]) ){
			$sql = " DELETE FROM ".DB_PREFIX ."verticalmenu_description WHERE verticalmenu_id=".(int)$data["verticalmenu"]['verticalmenu_id'] ;
			$this->db->query( $sql );

			foreach( $languages as $language ){
				$sql = "INSERT INTO ".DB_PREFIX ."verticalmenu_description(`language_id`, `verticalmenu_id`,`title`,`description`)
					VALUES(".$language['language_id'].",'".$data['verticalmenu']['verticalmenu_id']."','".$this->db->escape($data["verticalmenu_description"][$language['language_id']]['title'])."','"
					.$this->db->escape($data["verticalmenu_description"][$language['language_id']]['description'])."') ";
				$this->db->query( $sql );
			}
		}
		return $data['verticalmenu']['verticalmenu_id'];
	}

	 /**
	  * Automatic checking installation to whethere creating tables and data sample, configuration of modules.
	  */
	public function install(){

		$sql = " SHOW TABLES LIKE '".DB_PREFIX."verticalmenu'";
		$query = $this->db->query( $sql );

		if( count($query->rows) <=0 ){
			//$file = DIR_APPLICATION.'model/sample/module.php';
			$file = (DIR_APPLICATION).'model/sample/'.$this->config->get('theme_default_directory').'/sample.php';
			if( file_exists($file) ){
				require_once( DIR_APPLICATION.'model/sample/module.php' );
		 		$sample = new ModelSampleModule( $this->registry );
		 	    $result = $sample->installSampleQuery( $this->config->get('theme_default_directory'),'pavverticalmenu', true );
		 	    $result = $sample->installSample( $this->config->get('theme_default_directory'),'pavverticalmenu', true );
			}
		}

		$sql = " SHOW TABLES LIKE '".DB_PREFIX."verticalmenu_widgets'";
		$query = $this->db->query( $sql );
		$sql = array();
		if( count($query->rows) <= 0 ){
			$sql[]  = "
				CREATE TABLE IF NOT EXISTS `".DB_PREFIX."verticalmenu_widgets` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(250) NOT NULL,
				  `type` varchar(255) NOT NULL,
				  `params` text NOT NULL,
				  `store_id` int(11) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ; ";

			$sql[] = "INSERT INTO `".DB_PREFIX."verticalmenu_widgets` VALUES (1, 'Video Opencart Installation', 'video_code', 'a:1:{s:10:\"video_code\";s:168:\"&lt;iframe width=&quot;300&quot; height=&quot;315&quot; src=&quot;//www.youtube.com/embed/cUhPA5qIxDQ&quot; frameborder=&quot;0&quot; allowfullscreen&gt;&lt;/iframe&gt;\";}', 0);";
			$sql[] = "INSERT INTO `".DB_PREFIX."verticalmenu_widgets` VALUES (2, 'Demo HTML Sample', 'html', 'a:1:{s:4:\"html\";a:1:{i:1;s:275:\"Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel. Dorem ipsum dolor sit amet consectetur adipiscing elit congue sit amet erat roin tincidunt vehicula lorem in adipiscing urna iaculis vel.\";}}', 0);";
			$sql[] = "INSERT INTO `".DB_PREFIX."verticalmenu_widgets` VALUES (3, 'Products Latest', 'product_list', 'a:4:{s:9:\"list_type\";s:6:\"newest\";s:5:\"limit\";s:1:\"6\";s:11:\"image_width\";s:3:\"120\";s:12:\"image_height\";s:3:\"120\";}', 0);";
			$sql[] = "INSERT INTO `".DB_PREFIX."verticalmenu_widgets` VALUES (4, 'Products In Cat 20', 'product_category', 'a:4:{s:11:\"category_id\";s:2:\"20\";s:5:\"limit\";s:1:\"6\";s:11:\"image_width\";s:3:\"120\";s:12:\"image_height\";s:3:\"120\";}', 0);";
			$sql[] = "INSERT INTO `".DB_PREFIX."verticalmenu_widgets` VALUES (5, 'Manufactures', 'banner', 'a:4:{s:8:\"group_id\";s:1:\"8\";s:11:\"image_width\";s:2:\"80\";s:12:\"image_height\";s:2:\"80\";s:5:\"limit\";s:2:\"12\";}', 0);";
			$sql[] = "INSERT INTO `".DB_PREFIX."verticalmenu_widgets` VALUES (6, 'PavoThemes Feed', 'feed', 'a:1:{s:8:\"feed_url\";s:55:\"http://www.pavothemes.com/opencart-themes.feed?type=rss\";}', 0);";

			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}

		$sql = " SHOW TABLES LIKE '".DB_PREFIX."verticalmenu'";
		$query = $this->db->query( $sql );

		if( count($query->rows) <=0 ) {
			$sql = array();
			$sql[]  = "
				CREATE TABLE IF NOT EXISTS `".DB_PREFIX."verticalmenu` (
				  `verticalmenu_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `image` varchar(255) NOT NULL DEFAULT '',
				  `parent_id` int(11) NOT NULL DEFAULT '0',
				  `is_group` smallint(6) NOT NULL DEFAULT '2',
				  `width` varchar(255) DEFAULT NULL,
				  `submenu_width` varchar(255) DEFAULT NULL,
				  `colum_width` varchar(255) DEFAULT NULL,
				  `submenu_colum_width` varchar(255) DEFAULT NULL,
				  `item` varchar(255) DEFAULT NULL,
				  `colums` varchar(255) DEFAULT '1',
				  `type` varchar(255) NOT NULL,
				  `is_content` smallint(6) NOT NULL DEFAULT '2',
				  `show_title` smallint(6) NOT NULL DEFAULT '1',
				  `type_submenu` varchar(10) NOT NULL DEFAULT '1',
				  `level_depth` smallint(6) NOT NULL DEFAULT '0',
				  `published` smallint(6) NOT NULL DEFAULT '1',
				  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0',
				  `position` int(11) unsigned NOT NULL DEFAULT '0',
				  `show_sub` smallint(6) NOT NULL DEFAULT '0',
				  `url` varchar(255) DEFAULT NULL,
				  `target` varchar(25) DEFAULT NULL,
				  `privacy` smallint(5) unsigned NOT NULL DEFAULT '0',
				  `position_type` varchar(25) DEFAULT 'top',
				  `menu_class` varchar(25) DEFAULT NULL,
				  `description` text,
				  `content_text` text,
				  `submenu_content` text,
				  `level` int(11) NOT NULL,
				  `left` int(11) NOT NULL,
				  `right` int(11) NOT NULL,
				  `widget_id` int(11) DEFAULT '0',
				  `badges` text DEFAULT '',
				  PRIMARY KEY (`verticalmenu_id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;
			";
			$sql[] = "
			CREATE TABLE IF NOT EXISTS `".DB_PREFIX."verticalmenu_description` (
			  `verticalmenu_id` int(11) NOT NULL,
			  `language_id` int(11) NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `description` text NOT NULL,
			  PRIMARY KEY (`verticalmenu_id`,`language_id`),
			  KEY `name` (`title`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
			";
			foreach( $sql as $q ){
				$query = $this->db->query( $q );
			}
		}
		$query = $this->db->query( 
			"SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()
        	AND COLUMN_NAME='widget_id' AND TABLE_NAME='".DB_PREFIX."verticalmenu'"
        );
		if(count($query->rows) <= 0){
			$query = $this->db->query("ALTER TABLE `".DB_PREFIX."verticalmenu` ADD COLUMN `widget_id` int DEFAULT '0'");
		}
		$query = $this->db->query( 
			"SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE()
        	AND COLUMN_NAME='icon' AND TABLE_NAME='".DB_PREFIX."verticalmenu'"
        );
		if(count($query->rows) <= 0){
			$query = $this->db->query("ALTER TABLE `".DB_PREFIX."verticalmenu` ADD COLUMN `icon` varchar(25) DEFAULT NULL");
		}
	}

}

?>