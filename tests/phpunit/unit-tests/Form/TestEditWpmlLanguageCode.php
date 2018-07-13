<?php

namespace GFPDF\Plugins\WPML\Form;

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
 * Class TestEditWpmlLanguageCode
 *
 * @group   form
 *
 * @package GFPDF\Plugins\WPML\Form
 */
class TestEditWpmlLanguageCode extends \WP_UnitTestCase {

	/**
	 * @var EditWpmlLanguageCode
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

		$this->class = new EditWpmlLanguageCode( $this->wpml, $this->gf );

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

	public function test_init() {
		/* Setup passing checks */
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );
		$_GET['page']         = 'gf_entries';
		$_GET['view']         = 'entry';
		$_POST['screen_mode'] = 'edit';

		$this->class->init();
		$this->assertSame( 5, has_action( 'gform_entry_info', [ $this->class, 'add_language_selector' ] ) );

		remove_all_actions( 'gform_entry_info' );
		add_filter( 'gravitypdf_wpml_disable_change_language', '__return_true' );

		$this->class->init();
		$this->assertFalse( has_action( 'gform_entry_info', [ $this->class, 'add_language_selector' ] ) );

		remove_all_actions( 'gform_entry_info' );
		remove_filter( 'gravitypdf_wpml_disable_change_language', '__return_true' );
		wp_set_current_user( 0 );

		$this->class->init();
		$this->assertFalse( has_action( 'gform_entry_info', [ $this->class, 'add_language_selector' ] ) );

		remove_all_actions( 'gform_entry_info' );
		wp_set_current_user( $user_id );
		$_GET['page'] = 'test';

		$this->class->init();
		$this->assertFalse( has_action( 'gform_entry_info', [ $this->class, 'add_language_selector' ] ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_add_language_selector() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		$this->gf->save_entry_language_code( $entry_id, 'fr' );

		/* Test the Language Viewer */
		ob_start();
		$this->class->add_language_selector( $form_id, [ 'id' => $entry_id ] );
		$this->assertNotFalse( strpos( ob_get_clean(), 'Language: French (Français)' ) );

		/* Test the Language Editor */
		$_GET['page']         = 'gf_entries';
		$_GET['view']         = 'entry';
		$_POST['screen_mode'] = 'edit';

		ob_start();
		$this->class->add_language_selector( $form_id, [ 'id' => $entry_id ] );
		$this->assertNotFalse( strpos( ob_get_clean(), '<select name="gpdf_language" id="change_wpml_language" class="widefat">' ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_update_language() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		$this->gf->save_entry_language_code( $entry_id, 'en' );

		$_POST['gpdf_language'] = 'fr';

		/* Verify the Nonce check works */
		$this->class->update_language( [ 'id' => $form_id ], $entry_id );
		$this->assertEquals( 'en', $this->gf->get_entry_language_code( $entry_id ) );

		/* Run the language changer */
		$_POST['gpdf_original_language_nonce'] = wp_create_nonce( 'gpdf_original_language_nonce' );

		$this->class->update_language( [ 'id' => $form_id ], $entry_id );
		$this->assertEquals( 'fr', $this->gf->get_entry_language_code( $entry_id ) );

		$notes = \GFFormsModel::get_lead_notes( $entry_id );
		$this->assertEquals( 'Changed entry language from English to Français (French)', $notes[0]->value );

		/* Check nothing is run when the language codes haven't changed */
		$_POST['gpdf_language'] = 'fr';

		$this->class->update_language( [ 'id' => $form_id ], $entry_id );
		$this->assertCount( 1, \GFFormsModel::get_lead_notes( $entry_id ) );

		/* Swap the language again */
		$_POST['gpdf_language'] = 'es';
		$this->class->update_language( [ 'id' => $form_id ], $entry_id );
		$this->assertCount( 2, \GFFormsModel::get_lead_notes( $entry_id ) );
	}
}
