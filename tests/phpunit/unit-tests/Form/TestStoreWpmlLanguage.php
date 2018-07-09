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
 * Class TestStoreWpmlLanguage
 *
 * @group   form
 *
 * @package GFPDF\Plugins\WPML\Form
 */
class TestStoreWpmlLanguage extends \WP_UnitTestCase {

	/**
	 * @var StoreWpmlLanguage
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

		$this->class = new StoreWpmlLanguage( $this->wpml, $this->gf );
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
	public function test_save_language_code() {
		$form_id  = $this->create_form();
		$entry_id = \GFAPI::add_entry(
			[
				'form_id' => $form_id,
				13        => 'My Field Data',
			]
		);

		$this->wpml->set_site_language( 'fr' );
		do_action( 'gform_entry_created', [ 'id' => $entry_id ] );

		$this->assertEquals( 'fr', $this->gf->get_entry_language_code( $entry_id ) );

		$this->wpml->set_site_language( 'es' );
		do_action( 'gform_entry_created', [ 'id' => $entry_id ] );

		$this->assertEquals( 'es', $this->gf->get_entry_language_code( $entry_id ) );
	}
}
