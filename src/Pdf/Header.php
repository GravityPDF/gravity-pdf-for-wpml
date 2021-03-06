<?php

namespace GFPDF\Plugins\WPML\Pdf;

/**
 * Register Custom WPML PDF Template Header
 *
 * @package     Gravity PDF Developer Toolkit
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
	This file is part of Gravity PDF for WPML.

	Copyright (c) 2018, Blue Liquid Designs

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Class Header
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
class Header {

	/**
	 * @since 1.0
	 */
	public function init() {
		$this->add_filters();
	}

	/**
	 * Add WordPress filters
	 *
	 * @since 1.0
	 */
	public function add_filters() {
		add_filter( 'gfpdf_template_header_details', [ $this, 'add_wpml_header' ] );
	}

	/**
	 * Register a new PDF Template Header "WPML"
	 *
	 * @param array $headers
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function add_wpml_header( $headers ) {
		$headers['wpml'] = 'WPML';

		return $headers;
	}
}
