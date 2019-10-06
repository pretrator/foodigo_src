<?php
/**
 * @package		OpenCart
 * @author		Pavothemer
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.pavothemes.com/)
 * @link		https://www.pavothemes.com/
 */

/**
 * Pavo_Pagination class
 */
class Pavo_Pagination extends Pagination {
	public $total = 0;
	public $page = 1;
	public $limit = 20;
	public $url = '';
	public $text_first = '|&lt;';
	public $text_last = '&gt;|';
	public $text_next = '&gt;';
	public $text_prev = '&lt;';
	public $uniqid = '';

	/**
     *
     *
     * @return	text
     */
	public function render() {
		$total = $this->total;

		if ($this->page < 1) {
			$page = 1;
		} else {
			$page = $this->page;
		}

		if (!(int)$this->limit) {
			$limit = 10;
		} else {
			$limit = $this->limit;
		}

		$num_pages = ceil($total / $limit);

		$this->url = str_replace('%7Bpage%7D', '{page}', $this->url);

		$output = '<ul class="pavo-pagination'.( $this->uniqid ? ' ' . $this->uniqid : '' ).'">';

		if ($page > 1) {
			$output .= '<li><a href="' . str_replace('{page}', $page - 1, $this->url) . '">' . $this->text_prev . '</a></li>';
		}

		if ( $page == 1 ) {
			$output .= '<li class="active"><span>1</span></li>';
		} else {
			$output .= '<li><a href="' . str_replace(array('&amp;page={page}', '?page={page}', '&page={page}'), '', $this->url) . '">1</a></li>';
		}

		$dotted_before = $dotted_after = false;
		if ( $num_pages > 1 ) {
			for ( $i = 2; $i <= $num_pages - 1; $i++ ) {
				if ( $page == $i ) {
					$output .= '<li class="active"><span>' . $i . '</span></li>';
				} else if (
					( $page <= 3 && $i <= 3 )
					|| ( $i >= $num_pages - 2 && $page <= $num_pages && $page >= $num_pages - 2 )
					|| ( $i == $page - 1 && $i >= 3 && $i < $num_pages - 3 ) || ( $i == $page + 1 && $i <= $num_pages - 2 && $i > 4 )
				) {
					$output .= '<li><a href="' . str_replace('{page}', $i, $this->url) . '">' . $i . '</a></li>';
				} else {
					if ( ( ! $dotted_before && ( $i == $page - 2 || ( $i == 2 && $page > 3 ) ) ) ) {
						$dotted_before = true;
						$output .= '<li><span>...</span></li>';
					}
					if ( ( ! $dotted_after && ( $i == $page + 2 || ( $i == $num_pages - 1 && $page < $num_pages - 2 ) ) ) ) {
						$dotted_after = true;
						$output .= '<li><span>...</span></li>';
					}
				}
			}
		}

		if ( $page == $num_pages ) {
			$output .= '<li class="active"><span>' . $num_pages . '</span></li>';
		} else {
			$output .= '<li><a href="' . str_replace('{page}', $num_pages, $this->url) . '">' . $num_pages . '</a></li>';
		}

		if ( $page < $num_pages ) {
			$output .= '<li><a href="' . str_replace('{page}', $page + 1, $this->url) . '">' . $this->text_next . '</a></li>';
		}

		$output .= '</ul>';

		if ( $num_pages > 1 ) {
			return $output;
		} else {
			return '';
		}
	}
}
