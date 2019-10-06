<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Video extends PA_Widgets {

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-video-camera',
				'label'	=> $this->language->get( 'entry_video_text' )
			),
			'tabs'	=> array(
				'general'		=> array(
					'label'		=> $this->language->get( 'entry_general_text' ),
					'fields'	=> array(
						array(
							'type'	=> 'hidden',
							'name'	=> 'uniqid_id',
							'label'	=> $this->language->get( 'entry_row_id_text' ),
							'desc'	=> $this->language->get( 'entry_column_desc_text' )
						),
						array(
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' )
						),
						array(
							'type'		=> 'text',
							'name'		=> 'link',
							'label'		=> $this->language->get( 'entry_link_video_text' ),
							'default'	=> 'https://www.youtube.com/watch?v=fNEepYl3LAk',
							'desc'	=> $this->language->get( 'entry_link_video_desc' ),


						),
						array(
							'type'		=> 'checkbox',
							'name'		=> 'autoplay',
							'label'		=> $this->language->get( 'entry_autoplay_text' ),
							'default'	=> 1,
							'desc'		=> $this->language->get( 'entry_autoplay_desc' ),
						),
						array(
							'type'		=> 'checkbox',
							'name'		=> 'fullwidth',
							'label'		=> $this->language->get( 'entry_fullwidth_text' ),
							'default'	=> 1,
							'desc'		=> $this->language->get( 'entry_fullwidth_desc' ),
						),
						array(
							'type'		=> 'text',
							'name'		=> 'width',
							'label'		=> $this->language->get( 'entry_width_text' ),
							'default'	=> '600px'
						),
						array(
							'type'		=> 'text',
							'name'		=> 'height',
							'label'		=> $this->language->get( 'entry_height_text' ),
							'default'	=> '500px'
						)

					)
				),
				'style'				=> array(
					'label'			=> $this->language->get( 'entry_styles_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'layout-onion',
							'name'	=> 'layout_onion',
							'label'	=> 'entry_box_text'
						)
					)
				)
			)
		);
	}

	public function getYoubetuID($url) {
		    $pattern =
		        '%^# Match any youtube URL
		        (?:https?://)?  # Optional scheme. Either http or https
		        (?:www\.)?      # Optional www subdomain
		        (?:             # Group host alternatives
		          youtu\.be/    # Either youtu.be,
		        | youtube\.com  # or youtube.com
		          (?:           # Group path alternatives
		            /embed/     # Either /embed/
		          | /v/         # or /v/
		          | /watch\?v=  # or /watch\?v=
		          )             # End path alternatives.
		        )               # End host alternatives.
		        ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
		        $%x'
		        ;
		    $result = preg_match($pattern, $url, $matches);
		    if (false !== $result && isset($matches[1]) ) {
		        return $matches[1];
		    }
		    return false;
		}


	 public function getHostInfo ($vid_link) {
	  // youtube get video id
		if (preg_match('#youtu#',$vid_link)) {
			return array( 'host_name'=>'youtube', 'original_key' => $this->getYoubetuID($vid_link) );
		}
		// vimeo get video id
		elseif (preg_match('#vimeo#',$vid_link)) {

			if (preg_match('#(?<=/)([\d]+)#', $vid_link, $matches)){
		 		return array('host_name' => 'vimeo', 'original_key' => $matches[0]);
			}
		}

	 	return false;
	}

	public function render( $settings = array(), $content = '' ) {

		$settings = array_merge(  array(
			 'link'  		=> '',
			 'autoplay' 	=> 'h3',
			 'extra_class'  => '',
			 'fullwidth'	=> '',
			 'width'		=> '600px',
			 'height'	 	=> '500px'
		), $settings );

		$video = $this->getHostInfo( $settings['link'] );
// echo '<Pre>'.print_r( $video, 1);die;
		if( isset($video['host_name'])  ){
			$settings['link'] = $video['host_name'] == 'youtube'?'//www.youtube.com/embed/':'//player.vimeo.com/video/';
			$settings['link'] .= $video['original_key'];
		}
		if( $settings['fullwidth'] ){
			$settings['size'] = 'width:100%;height:'.$settings['height'];
		}else {
			$settings['size'] = 'width:'.$settings['width'].';height:'.$settings['height'].';';
		}
		return $this->load->view( 'extension/module/pavobuilder/pa_video/pa_video', array( 'settings' => $settings ) );
	}

	/**
	 * s fields
	 */
	public function validate( $settings = array() ) {
		$language_id = $this->config->get('config_language_id');
		$this->load->model( 'localisation/language' );
		$language = $this->model_localisation_language->getLanguage( $language_id );
		$code = ! empty( $language['code'] ) ? $language['code'] : $this->config->get('config_language');

		if ( ! empty( $settings[$code] ) && ! empty( $settings[$code]['content'] ) ) {
			$settings[$code]['content'] = html_entity_decode( $settings[$code]['content'], ENT_QUOTES, 'UTF-8' );
		}
		return $settings;
	}

}