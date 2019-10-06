<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Contact extends PA_Widgets {

	private $error = array();

	public function ajaxProcessSubmitForm() {
		$this->load->language('information/contact');
		$this->load->language('extension/module/pavobuilder');

		$results = array(
				'status'	=> false
			);
		try {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
				$this->error['name'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['subject']) < 3) || (utf8_strlen($this->request->post['subject']) > 32)) {
				$this->error['subject'] = $this->language->get('error_subject');
			}

			if (!filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
				$this->error['email'] = $this->language->get('error_email');
			}

			if ((utf8_strlen($this->request->post['message']) < 10) || (utf8_strlen($this->request->post['message']) > 3000)) {
				$this->error['message'] = $this->language->get('error_message');
			}

			// Captcha
			if (!empty($this->request->post['captcha']) && $this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('contact', (array)$this->config->get('config_captcha_page'))) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$this->error['captcha'] = $captcha;
				}
			}

			if (!empty($this->error)) {
				$results['errors'] = $this->error;
			} else {
				$mail = new Mail($this->config->get('config_mail_engine'));
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

				$mail->setTo($this->config->get('config_email'));
				$mail->setFrom($this->config->get('config_email'));
				$mail->setReplyTo($this->request->post['email']);
				$mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
				$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['subject']), ENT_QUOTES, 'UTF-8'));
				$mail->setText($this->request->post['message']);
				$mail->send();

				$results['status'] = true;
				$results['success'] = $this->language->get('entry_sent_message');
			}
		} catch (Exception $e) {
			$results['message'] = $e->getMessage();
		}

		return $results;
	}

	public function fields() {
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-flash',
				'label'	=> $this->language->get( 'entry_contact_text' )
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
                            'type'  => 'select',
                            'name'  => 'layout_style',
                            'label' => $this->language->get( 'entry_layout_style' ),
                            'default'   => 'style-default',
                            'options'   => array(
                                array(
                                    'value' => 'style-default',
                                    'label' => 'style-default'
                                ),
                                array(
                                    'value' => 'style1',
                                    'label' => 'style 1'
                                ),
                                array(
                                    'value' => 'style2',
                                    'label' => 'style 2'
                                ),
                                array(
                                    'value' => 'style3',
                                    'label' => 'style 3'
                                ),
                                array(
                                    'value' => 'style4',
                                    'label' => 'style 4'
                                ),
                                array(
                                    'value' => 'style5',
                                    'label' => 'style 5'
                                ),
                                array(
                                    'value' => 'style6',
                                    'label' => 'style 6'
                                ),
                                array(
                                    'value' => 'style7',
                                    'label' => 'style 7'
                                ),
                            )
                        ),
						array(
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' )
						)
					)
				),
				'background'		=> array(
					'label'			=> $this->language->get( 'entry_background_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'background_color',
							'label'	=> $this->language->get( 'entry_background_color_text' ),
							'css_attr'	=> 'background-color'
						),
						array(
							'type'	=> 'image',
							'name'	=> 'background_image',
							'label'	=> $this->language->get( 'entry_background_image_text' ),
							'css_attr'	=> 'background-image'
						),
						array(
							'type'	=> 'select',
							'name'	=> 'background_position',
							'label'	=> $this->language->get( 'entry_background_position' ),
							'options'	=> array(
								array(
									'label'		=> 'None',
									'value'		=> ''
								),
								array(
									'label'		=> 'Inherit',
									'value'		=> 'inherit'
								),
								array(
									'label'		=> 'Top Left',
									'value'		=> 'top left'
								),
								array(
									'label'		=> 'Top Right',
									'value'		=> 'top right'
								),
								array(
									'label'		=> 'Bottom Left',
									'value'		=> 'bottom left'
								),
								array(
									'label'		=> 'Bottom Right',
									'value'		=> 'bottom right'
								),
                                array(
                                    'label'     => 'Bottom Center',
                                    'value'     => 'bottom center'
                                ),
                                array(
                                    'label'     => 'Right Center',
                                    'value'     => 'right center'
                                ),
								array(
									'label'		=> 'Center Center',
									'value'		=> 'center center'
								)
							),
							'css_attr'	=> 'background-position'
						),
						array(
							'type'	=> 'select',
							'name'	=> 'background_repeat',
							'label'	=> $this->language->get( 'entry_background_repeat_text' ),
							'options'	=> array(
								array(
									'label'		=> 'None',
									'value'		=> ''
								),
								array(
									'label'	=> 'No Repeat',
									'value'	=> 'no-repeat'
								),
								array(
									'label'	=> 'Repeat x',
									'value'	=> 'repeat-x'
								),
								array(
									'label'	=> 'Repeat y',
									'value'	=> 'repeat-y'
								)
							),
							'css_attr'	=> 'background-repeat'
						)
					)
				),
				'style'				=> array(
					'label'			=> $this->language->get( 'entry_styles_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'layout-onion',
							'name'	=> 'styles',
							'label'	=> $this->language->get( 'entry_box_text' )
						)
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$settings = array_merge(
			array(
				'uniqid_id' => '',
				'extra_class' =>  ''
			), $settings
		);

		$settings['action'] = $this->getAjaxURI( array(
				'widget' => 'pa_contact',
				'action' => 'submitForm'
			) );

		return $this->load->view( 'extension/module/pavobuilder/pa_contact/pa_contact', array( 'settings' => $settings ) );
	}
}
