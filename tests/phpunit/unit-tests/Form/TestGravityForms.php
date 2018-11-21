<?php

namespace GFPDF\Plugins\WPML\Form;

use GFPDF\Plugins\WPML\Exceptions\GpdfWpmlException;

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
 * Class TestGravityForms
 *
 * @group   gf
 *
 * @package GFPDF\Plugins\WPML\Form
 */
class TestGravityForms extends \WP_UnitTestCase {

	/**
	 * @var GravityForms
	 *
	 * @since 0.1
	 */
	protected $class;

	/**
	 * @since 0.1
	 */
	public function setUp() {
		$this->class = new GravityForms();

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
	public function test_get_form() {
		/* Test the form not existing */
		try {
			$this->class->get_form( 9999 );
		} catch ( GpdfWpmlException $e ) {

		}

		$this->assertNotNull( $e );

		/* Test the form exists */
		$form_id = $this->create_form();
		$form    = $this->class->get_form( $form_id );

		$this->assertEquals( 'Sample WPML Form', $form['title'] );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_entry() {
		/* Test the entry not existing */
		try {
			$this->class->get_entry( 1 );
		} catch ( GpdfWpmlException $e ) {

		}

		$this->assertNotNull( $e );

		/* Test the form exists */
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		$entry = $this->class->get_entry( $entry_id );

		$this->assertEquals( 'My Field Data', $entry[13] );
	}

	/**
	 * @since 0.1
	 */
	public function test_entry_language_code() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		$this->class->save_entry_language_code( $entry_id, 'en' );
		$this->assertEquals( 'en', $this->class->get_entry_language_code( $entry_id ) );

		$this->class->save_entry_language_code( $entry_id, 'es' );
		$this->assertEquals( 'es', $this->class->get_entry_language_code( $entry_id ) );

		$this->class->save_entry_language_code( $entry_id, 'it' );
		$this->assertEquals( 'it', $this->class->get_entry_language_code( $entry_id ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_flush_current_gravityform() {

		/* Fill the Gravity Forms cache */
		$form_id   = $this->create_form();
		$form_meta = \GFFormsModel::get_form_meta( $form_id );

		$this->assertEquals( 'Sample WPML Form', $form_meta['title'] );

		add_filter(
			'gform_form_post_get_meta',
			function( $form ) {
				return 'Fresh copy';
			}
		);

		/* Verify pulling from the cache */
		$form_meta = \GFFormsModel::get_form_meta( $form_id );

		$this->assertEquals( 'Sample WPML Form', $form_meta['title'] );

		/* Flush the cache and verify we have the new value */
		$this->class->flush_current_gravityform();

		$form_meta = \GFFormsModel::get_form_meta( $form_id );

		$this->assertEquals( 'Fresh copy', $form_meta );
	}

	/**
	 * @since 0.1
	 */
	public function test_has_capability() {
		$this->assertFalse( $this->class->has_capability( 'gform_full_access' ) );

		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$this->assertTrue( $this->class->has_capability( 'gform_full_access' ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_page() {
		$_GET['page'] = 'gf_entries';
		$_GET['view'] = 'entry';
		$this->assertEquals( 'entry_detail', $this->class->get_page() );

		$_GET['page']         = 'gf_entries';
		$_GET['view']         = 'entry';
		$_POST['screen_mode'] = 'edit';
		$this->assertEquals( 'entry_detail_edit', $this->class->get_page() );
	}

	/**
	 * @since 0.1
	 */
	public function test_add_note() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				1         => 'My Field Data',
			]
		);

		$this->class->add_note( $entry_id, 'This is my note' );
		$notes = \GFFormsModel::get_lead_notes( $entry_id );

		$this->assertEquals( 'This is my note', $notes[0]->value );
		$this->assertEquals( 'Unknown', $notes[0]->user_name );

		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$this->class->add_note( $entry_id, 'This is my note 2' );
		$notes = \GFFormsModel::get_lead_notes( $entry_id );

		$this->assertEquals( 'This is my note 2', $notes[1]->value );
		$this->assertRegExp( '/user [0-9]+/i', $notes[1]->user_name );
	}
}
