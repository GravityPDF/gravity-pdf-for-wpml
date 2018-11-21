<?php

namespace GFPDF\Plugins\WPML\Form;

/**
 * @package     Gravity PDF for WPML
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/* Exit if accessed directly */

use GFPDF\Plugins\WPML\Wpml\WpmlTesting;

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
 * Class TestMergeTags
 *
 * @group   form
 *
 * @package GFPDF\Plugins\WPML\Form
 */
class TestMergeTags extends \WP_UnitTestCase {

	/**
	 * @var MergeTags
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
	 * @var WpmlTesting
	 *
	 * @since 0.1
	 */
	protected $wpml;

	/**
	 * @since 0.1
	 */
	public function setUp() {

		parent::setUp();

		$this->gf   = new GravityForms();
		$this->wpml = new WpmlTesting();

		$this->class = new MergeTags( $this->wpml, $this->gf );
		$this->class->init();

		$log = new \Monolog\Logger( 'test' );
		$log->pushHandler( new \Monolog\Handler\NullHandler( \Monolog\Logger::INFO ) );
		$this->class->set_logger( $log );
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
	public function test_add_mergetags() {
		$tags = $this->class->add( [] );

		$this->assertEquals( '{wpml:current_language_code}', $tags[0]['tag'] );
	}

	/**
	 * @since 0.1
	 */
	public function test_process_mergetags() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		/* Test current site language tags */
		$this->wpml->set_site_language( 'es' );
		$this->assertEquals( 'es', $this->class->process( '{wpml:current_language_code}', [], [ 'id' => $entry_id ] ) );

		$this->wpml->set_site_language( 'en' );
		$this->assertEquals( 'en', $this->class->process( '{wpml:current_language_code}', [], [ 'id' => $entry_id ] ) );

		/* Test entry language code tags */
		$this->gf->save_entry_language_code( $entry_id, 'fr' );
		$this->assertEquals( 'fr', $this->class->process( '{wpml:entry_language_code}', [], [ 'id' => $entry_id ] ) );

		$this->gf->save_entry_language_code( $entry_id, 'es' );
		$this->assertEquals( 'es', $this->class->process( '{wpml:entry_language_code}', [], [ 'id' => $entry_id ] ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_add_language_code_to_filename() {
		$form     = \GFAPI::get_form( $this->create_form() );
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form['id'],
				13        => 'My Field Data',
			]
		);
		$entry    = \GFAPI::get_entry( $entry_id );

		$this->assertEquals( 'Name (en)', $this->class->add_language_code_to_filename( 'Name', $form, $entry, [ 'filename' => '' ] ) );

		$this->assertEquals( 'Name', $this->class->add_language_code_to_filename( 'Name', $form, $entry, [ 'filename' => 'Name_{wpml:current_language_code}' ] ) );

		add_filter( 'gravitypdf_wpml_disable_auto_pdf_filename_language_code', '__return_true' );

		$this->assertEquals( 'Name', $this->class->add_language_code_to_filename( 'Name', $form, $entry, [ 'filename' => '' ] ) );
	}
}
