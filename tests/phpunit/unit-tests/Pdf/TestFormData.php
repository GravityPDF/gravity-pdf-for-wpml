<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Plugins\WPML\Form\GravityForms;
use GFPDF\Plugins\WPML\Wpml\WpmlTesting;

/**
 * @package     Gravity PDF for WPML
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
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
 * Class TestFormData
 *
 * @group   pdf
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
class TestFormData extends \WP_UnitTestCase {

	/**
	 * @var FormData
	 *
	 * @since 0.1
	 */
	protected $class;

	/**
	 * @var GravityForms
	 *
	 * @since 0.1
	 */
	protected $gf;

	/**
	 * @since 0.1
	 */
	public function setUp() {
		$this->gf = new GravityForms();

		$this->class = new FormData( $this->gf );
		$this->class->init();

		parent::setUp();
	}

	/**
	 * @since 0.1
	 */
	protected function create_form() {
		return \GFAPI::add_form( json_decode( file_get_contents( __DIR__ . '/../../json/sample-wpml-form.json' ), true ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_add_language_key() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		$this->gf->save_entry_language_code( $entry_id, 'fr' );

		$form_data = $this->class->add_language_key( [ 'misc' => [] ], [ 'id' => $entry_id ] );
		$this->assertEquals( 'fr', $form_data['misc']['language_code'] );

		$this->gf->save_entry_language_code( $entry_id, 'es' );

		$form_data = $this->class->add_language_key( [ 'misc' => [] ], [ 'id' => $entry_id ] );
		$this->assertEquals( 'es', $form_data['misc']['language_code'] );
	}
}
