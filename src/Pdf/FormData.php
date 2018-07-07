<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Plugins\WPML\Form\GravityFormsInterface;

/**
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
 * Class FormData
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
class FormData {

	/**
	 * @var GravityFormsInterface
	 *
	 * @since 0.1
	 */
	protected $gf;

	/**
	 * FormData constructor.
	 *
	 * @param GravityFormsInterface $gf
	 *
	 * @since 0.1
	 */
	public function __construct( GravityFormsInterface $gf ) {
		$this->gf = $gf;
	}

	/**
	 * Initialise class
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->add_filters();
	}

	/**
	 * Add WordPress filters
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function add_filters() {
		add_filter( 'gfpdf_form_data', [ $this, 'add_language_key' ], 10, 2 );
	}

	/**
	 * Add the language code to the $form_data array
	 *
	 * @param array $data  The Gravity PDF Form Data Object
	 * @param array $entry The Gravity Forms Entry Object
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function add_language_key( $data, $entry ) {
		$language_code = $this->gf->get_entry_language_code( $entry['id'] );

		if ( isset( $data['misc'] ) && is_array( $data['misc'] ) ) {
			$data['misc']['language_code'] = $language_code;
		}

		return $data;
	}
}
