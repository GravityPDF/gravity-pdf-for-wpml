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
 * Class TestTranslation
 *
 * @group   pdf
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
class TestTranslation extends \WP_UnitTestCase {

	/**
	 * @var Translation(
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
		$this->gf   = new GravityForms();
		$this->wpml = new WpmlTesting();

		$this->class = new Translation( $this->wpml, $this->gf, new Pdf( $this->gf ) );

		$log = new \Monolog\Logger( 'test' );
		$log->pushHandler( new \Monolog\Handler\NullHandler( \Monolog\Logger::INFO ) );
		$this->class->set_logger( $log );

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
	public function test_pre_pdf_generation_and_view_or_download() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		/* Test the current site language for French */
		$this->wpml->set_site_language( 'fr' );
		$this->class->pre_pdf_view_or_download( $entry_id );
		$this->assertEquals( 'fr', $this->wpml->get_current_site_language() );
		$this->assertEquals( 'view-download', $this->class->get_pdf_type() );

		$this->wpml->set_site_language( 'fr' );
		$this->class->pre_pdf_generation( [], [ 'id' => $entry_id ] );
		$this->assertEquals( 'fr', $this->wpml->get_current_site_language() );
		$this->assertEquals( 'api', $this->class->get_pdf_type() );

		/* Test the current site language for Spanish */
		$this->wpml->set_site_language( 'es' );
		$this->class->pre_pdf_view_or_download( $entry_id );
		$this->assertEquals( 'es', $this->wpml->get_current_site_language() );

		$this->wpml->set_site_language( 'es' );
		$this->class->pre_pdf_generation( [], [ 'id' => $entry_id ] );
		$this->assertEquals( 'es', $this->wpml->get_current_site_language() );

		/* Test that we fallback to the default site language when a language isn't found */
		$this->wpml->set_site_language( 'hi' );
		$this->class->pre_pdf_view_or_download( $entry_id );
		$this->assertEquals( 'en', $this->wpml->get_current_site_language() );

		$this->wpml->set_site_language( 'hi' );
		$this->class->pre_pdf_generation( [], [ 'id' => $entry_id ] );
		$this->assertEquals( 'en', $this->wpml->get_current_site_language() );

		/* Test the default language English */
		$this->wpml->set_site_language( 'en' );
		$this->class->pre_pdf_view_or_download( $entry_id );
		$this->assertEquals( 'en', $this->wpml->get_current_site_language() );

		$this->wpml->set_site_language( 'en' );
		$this->class->pre_pdf_generation( [], [ 'id' => $entry_id ] );
		$this->assertEquals( 'en', $this->wpml->get_current_site_language() );
	}

	public function test_pre_pdf_generation_notification() {
		$form_id  = $this->create_form();
		$form     = $this->gf->get_form( $form_id );
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);
		$entry    = $this->gf->get_entry( $entry_id );

		/* Test the set language code with the defaults */
		$this->gf->save_entry_language_code( $entry_id, 'fr' );

		$this->class->pre_pdf_generation_notification( $form, $entry, [], [ 'toType' => 'field' ] );
		$this->assertEquals( 'fr', $this->wpml->get_current_site_language() );
		$this->assertEquals( 'notification', $this->class->get_pdf_type() );

		$this->class->pre_pdf_generation_notification( $form, $entry, [], [] );
		$this->assertEquals( 'en', $this->wpml->get_current_site_language() );

		/* Disable the User Notification only option and re-test */
		\GPDFAPI::update_plugin_option( 'wpml_user_notification', 'Off' );

		$this->class->pre_pdf_generation_notification( $form, $entry, [], [] );
		$this->assertEquals( 'fr', $this->wpml->get_current_site_language() );

		$this->gf->save_entry_language_code( $entry_id, 'hi' );
		$this->class->pre_pdf_generation_notification( $form, $entry, [], [] );
		$this->assertEquals( 'en', $this->wpml->get_current_site_language() );
	}

	public function test_translate_gravityform() {
		$form = [
			'id'    => 1,
			'title' => 'My Form',
		];

		$new_form = $this->class->translate_gravityform( $form );
		$this->assertSame( $form, $new_form );

		$this->class->set_language_code( 'es', 'api' );
		$new_form = $this->class->translate_gravityform( $form );
		$this->assertEquals( 'My Form (es)', $new_form['title'] );
	}

	public function test_post_pdf_generation() {
		$this->class->set_language_code( 'fr', 'api' );
		$this->assertEquals( 'fr', $this->wpml->get_current_site_language() );

		$this->class->post_pdf_generation();
		$this->assertEquals( 'en', $this->wpml->get_current_site_language() );
	}
}
