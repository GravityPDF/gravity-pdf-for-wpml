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
 * Class TestDownloadLinks
 *
 * @group   pdf
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
class TestDownloadLinks extends \WP_UnitTestCase {

	/**
	 * @var DownloadLinks
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

		$this->class = new DownloadLinks( $this->wpml, $this->gf, new Pdf( $this->gf ) );
		$this->class->init();

		$header = new Header();
		$header->init();

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
	public function test_actions_and_filters() {
		$this->assertSame( 9, has_action( 'gform_entry_info', [ $this->class, 'add_links_to_entry_details' ], 9 ) );
		$this->assertSame( 10, has_filter( 'gfpdf_get_pdf_url', [ $this->class, 'get_pdf_url_for_language' ] ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_pdf_url_for_language() {
		$this->assertEquals( 'failure', $this->class->get_pdf_url_for_language( 'failure', '', 0 ) );

		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		/* Test the Entry Language Code Functionality */
		\GPDFAPI::update_plugin_option( 'wpml_admin_default_language', 'entry-language' );

		$this->gf->save_entry_language_code( $entry_id, 'fr' );
		$this->assertEquals( 'http://example.org/fr/', $this->class->get_pdf_url_for_language( 'http://example.org/', '5b14a7c9f3252', $entry_id ) );

		$this->gf->save_entry_language_code( $entry_id, 'hi' );
		$this->assertEquals( 'http://example.org/en/', $this->class->get_pdf_url_for_language( 'http://example.org/', '5b14a7c9f3252', $entry_id ) );

		/* Test the default language code functionality */
		\GPDFAPI::update_plugin_option( 'wpml_admin_default_language', 'default-language' );

		$this->gf->save_entry_language_code( $entry_id, 'fr' );
		$this->assertEquals( 'http://example.org/en/', $this->class->get_pdf_url_for_language( 'http://example.org/', '5b14a7c9f3252', $entry_id ) );

		/* Test the current site language code functionality */
		\GPDFAPI::update_plugin_option( 'wpml_admin_default_language', 'current-language' );

		$this->wpml->set_site_language( 'fr' );
		$this->assertEquals( 'http://example.org/fr/', $this->class->get_pdf_url_for_language( 'http://example.org/', '5b14a7c9f3252', $entry_id ) );

		$this->wpml->set_site_language( 'hi' );
		$this->assertEquals( 'http://example.org/en/', $this->class->get_pdf_url_for_language( 'http://example.org/', '5b14a7c9f3252', $entry_id ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_add_links_to_entry_details() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);
		$entry    = $this->gf->get_entry( $entry_id );

		$this->assertNull( $this->class->add_links_to_entry_details( 0, $entry ) );

		/* Test the Entry Language Code Functionality */
		\GPDFAPI::update_plugin_option( 'wpml_admin_default_language', 'entry-language' );

		$this->gf->save_entry_language_code( $entry_id, 'fr' );

		ob_start();
		$this->class->add_links_to_entry_details( $form_id, $entry );
		$html = ob_get_clean();

		$this->assertRegExp( '/<a href=\"http:\/\/example.org\/fr\/(.*?)\" class=\"button\">/', $html );
		$this->assertRegExp( '/<a href=\"http:\/\/example.org\/es\/(.*?)\">/', $html );
		$this->assertRegExp( '/<a href=\"http:\/\/example.org\/en\/(.*?)\">/', $html );

		/* Test the default language code functionality */
		\GPDFAPI::update_plugin_option( 'wpml_admin_default_language', 'default-language' );

		ob_start();
		$this->class->add_links_to_entry_details( $form_id, $entry );
		$html = ob_get_clean();

		$this->assertRegExp( '/<a href=\"http:\/\/example.org\/en\/(.*?)\" class=\"button\">/', $html );
		$this->assertRegExp( '/<a href=\"http:\/\/example.org\/fr\/(.*?)\">/', $html );
		$this->assertRegExp( '/<a href=\"http:\/\/example.org\/es\/(.*?)\">/', $html );

		/* Test the current site language code functionality */
		\GPDFAPI::update_plugin_option( 'wpml_admin_default_language', 'current-language' );

		$this->wpml->set_site_language( 'es' );

		ob_start();
		$this->class->add_links_to_entry_details( $form_id, $entry );
		$html = ob_get_clean();

		$this->assertRegExp( '/<a href=\"http:\/\/example.org\/es\/(.*?)\" class=\"button\">/', $html );
		$this->assertRegExp( '/<a href=\"http:\/\/example.org\/en\/(.*?)\">/', $html );
		$this->assertRegExp( '/<a href=\"http:\/\/example.org\/fr\/(.*?)\">/', $html );
	}

	/**
	 * @since 0.1
	 */
	public function is_template_wpml_compatible() {
		$this->assertFalse(
			$this->class->is_template_wpml_compatible(
				[
					'wpml'  => '',
					'group' => '',
				]
			)
		);

		$this->assertTrue( $this->class->is_template_wpml_compatible( [ 'wpml' => 'true' ] ) );
		$this->assertTrue( $this->class->is_template_wpml_compatible( [ 'group' => 'Core' ] ) );

		add_filter(
			'gfpdf_wpml_group_support',
			function( $groups ) {
				$groups[] = 'Other';

				return $groups;
			}
		);

		$this->assertTrue( $this->class->is_template_wpml_compatible( [ 'group' => 'Other' ] ) );
	}
}
