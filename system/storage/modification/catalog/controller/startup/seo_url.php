<?php
class ControllerStartupSeoUrl extends Controller {
	public function index() {
		// Add rewrite to url class
		if ($this->config->get('config_seo_url')) {
			$this->url->addRewrite($this);
		}

		// Decode URL
		if (isset($this->request->get['_route_'])) {
			$parts = explode('/', $this->request->get['_route_']);

			// remove any empty arrays from trailing
			if (utf8_strlen(end($parts)) == 0) {
				array_pop($parts);
			}

			foreach ($parts as $part) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $this->db->escape($part) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

				if ($query->num_rows) {
					$url = explode('=', $query->row['query']);

					if ($url[0] == 'product_id') {
						$this->request->get['product_id'] = $url[1];
					}

					if ($url[0] == 'category_id') {
						if (!isset($this->request->get['path'])) {
							$this->request->get['path'] = $url[1];
						} else {
							$this->request->get['path'] .= '_' . $url[1];
						}
					}

					if ($url[0] == 'manufacturer_id') {
						$this->request->get['manufacturer_id'] = $url[1];
					}

					if ($url[0] == 'information_id') {
						$this->request->get['information_id'] = $url[1];
					}

					if ($query->row['query'] && $url[0] != 'information_id' && $url[0] != 'manufacturer_id' && $url[0] != 'category_id' && $url[0] != 'product_id') {
						
                    if ( $url[0] === 'pavo_cat_id' ) {
                        // archive
                        $this->request->get['route'] = 'extension/pavoblog/archive';
                        $this->request->get['pavo_cat_id'] = $url[1];
                    } else if ( $url[0] === 'pavo_post_id' ) {
                        // single
                        $this->request->get['route'] = 'extension/pavoblog/single';
                        $this->request->get['pavo_post_id'] = $url[1];
                    } else if ( $url[0] === 'extension/pavoblog/archive/author' && ! empty( $parts[1] ) ) {
                        $author = $parts[1];
                        $query = $this->db->query( "SELECT * FROM " . DB_PREFIX . "user WHERE username = '".$this->db->escape( $author )."'" );
                        $user = $query->row;
                        if ( ! empty( $user['user_id'] ) ) {
                            $this->request->get['route'] = 'extension/pavoblog/archive';
                            $this->request->get['pavo_username'] = $user['username'];
                        }
                        break;
                    } else {
                        $this->request->get['route'] = $query->row['query'];
                    }
                
					}
				} else {
					$this->request->get['route'] = 'error/not_found';

					break;
				}
			}

			if (!isset($this->request->get['route'])) {
				if (isset($this->request->get['product_id'])) {
					$this->request->get['route'] = 'product/product';
				} elseif (isset($this->request->get['path'])) {
					$this->request->get['route'] = 'product/category';
				} elseif (isset($this->request->get['manufacturer_id'])) {
					$this->request->get['route'] = 'product/manufacturer/info';
				} elseif (isset($this->request->get['information_id'])) {
					$this->request->get['route'] = 'information/information';
				}
			}
		}
	}

	public function rewrite($link) {
		$url_info = parse_url(str_replace('&amp;', '&', $link));

		$url = '';

		$data = array();

		parse_str($url_info['query'], $data);

		foreach ($data as $key => $value) {
			if (isset($data['route'])) {
				if (($data['route'] == 'product/product' && $key == 'product_id') || (($data['route'] == 'product/manufacturer/info' || $data['route'] == 'product/product') && $key == 'manufacturer_id') || ($data['route'] == 'information/information' && $key == 'information_id')) {
					$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

					if ($query->num_rows && $query->row['keyword']) {
						$url .= '/' . $query->row['keyword'];

						unset($data[$key]);
					}
				
                    } else if ( strpos( $data['route'], 'extension/pavoblog/' ) === 0 ) {

                        if ( $data['route'] === 'extension/pavoblog/archive' ) {
                            if ( $key === 'route' ) {
                                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($value) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
                                if ( $query->num_rows && ! empty( $query->row['keyword'] ) ) {
                                    $url .= '/' . $query->row['keyword'];
                                    unset($data[$key]);
                                }
                                if ( isset( $data['pavo_cat_id'] ) ) {
                                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape('pavo_cat_id=' . (int)$data['pavo_cat_id']) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
                                    if ( $query->num_rows && ! empty( $query->row['keyword'] ) ) {
                                        $url .= '/' . $query->row['keyword'];
                                        unset($data['pavo_cat_id']);
                                    }
                                }
                            }
                        } else if ( $data['route'] === 'extension/pavoblog/archive/author' ) {

                            if ( $key === 'pavo_username' ) {
                                $query = $this->db->query( "SELECT * FROM " . DB_PREFIX . "user WHERE username = '".$this->db->escape( $value )."'" );
                                $user = $query->row;
                                if ( ! empty( $user['user_id'] ) ) {
                                    $url .= '/' . $user['username'];
                                }
                                unset($data[$key]);
                            } else if ( $key === 'route' ) {
                                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($value) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
                                if ( $query->num_rows && ! empty( $query->row['keyword'] ) ) {
                                    $url .= '/' . $query->row['keyword'];
                                }
                            }
                        } else if ( $data['route'] === 'extension/pavoblog/single' && $key === 'pavo_post_id' ) {
                            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($key . '=' . (int)$value) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
                            if ( $query->num_rows && $query->row['keyword'] ) {
                                $url .= '/' . $query->row['keyword'];
                                unset($data[$key]);
                            }
                        }
                    } elseif ($key == 'path') {
                
					$categories = explode('_', $value);

					foreach ($categories as $category) {
						$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE `query` = 'category_id=" . (int)$category . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

						if ($query->num_rows && $query->row['keyword']) {
							$url .= '/' . $query->row['keyword'];
						} else {
							$url = '';

							break;
						}
					}

					unset($data[$key]);
				}
			}
		}

		if ($url) {
			unset($data['route']);

			$query = '';

			if ($data) {
				foreach ($data as $key => $value) {
					$query .= '&' . rawurlencode((string)$key) . '=' . rawurlencode((is_array($value) ? http_build_query($value) : (string)$value));
				}

				if ($query) {
					$query = '?' . str_replace('&', '&amp;', trim($query, '&'));
				}
			}

			return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url . $query;
		} else {
			return $link;
		}
	}
}
