<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Plugins\WPML\Exceptions\GpdfWpmlException;
use GFPDF\Plugins\WPML\Form\GravityForms;

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
 * Class TestPdf
 *
 * @group   pdf
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
class TestPdf extends \WP_UnitTestCase {

	/**
	 * @var Pdf
	 *
	 * @since 0.1
	 */
	protected $class;

	/**
	 * @since 0.1
	 */
	public function setUp() {
		$this->class = new Pdf( new GravityForms() );

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
	public function test_get_pdf() {
		/* Test the PDF not existing */
		try {
			$this->class->get_pdf( 1, '2' );
		} catch ( GpdfWpmlException $e ) {

		}

		$this->assertNotNull( $e );

		/* Test the PDF exists */
		$form_id = $this->create_form();
		$pdf     = $this->class->get_pdf( $form_id, '5b14a7c9f3252' );

		$this->assertEquals( 'Zadani', $pdf['name'] );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_pdf_name() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		$name = $this->class->get_pdf_name( $entry_id, '5b14a7c9f3252' );
		$this->assertEquals( 'Zadani', $name );
	}

	/**
	 * @since 0.1
	 */
	public function test_pdf_url() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		$url = $this->class->get_pdf_url( $entry_id, '5b14a7c9f3252' );
		$this->assertEquals( 'http://example.org/?gpdf=1&#038;pid=5b14a7c9f3252&#038;lid=' . $entry_id, $url );

		$url = $this->class->get_pdf_url( $entry_id, '5b14a7c9f3252', true );
		$this->assertEquals( 'http://example.org/?gpdf=1&#038;pid=5b14a7c9f3252&#038;lid=' . $entry_id . '&#038;action=download', $url );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_active_pdfs() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		$this->assertCount( 2, $this->class->get_active_pdfs( $entry_id ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_option() {
		$this->assertEquals( 'test', $this->class->get_option( 'option_name', 'test' ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_template_info_by_id() {
		$template = $this->class->get_template_info_by_id( 'zadani' );
		$this->assertEquals( 'Core', $template['group'] );
	}

	/**
	 * @since 0.1
	 */
	public function test_remove_filter() {
		$model_pdf = \GPDFAPI::get_mvc_class( 'Model_PDF' );

		$this->assertEquals( 10, has_filter( 'gform_entry_info', [ $model_pdf, 'view_pdf_entry_detail' ] ) );
		$this->class->remove_filter( 'gform_entry_info', 'Model_PDF', 'view_pdf_entry_detail' );
		$this->assertFalse( has_filter( 'gform_entry_info', [ $model_pdf, 'view_pdf_entry_detail' ] ) );
	}
}
