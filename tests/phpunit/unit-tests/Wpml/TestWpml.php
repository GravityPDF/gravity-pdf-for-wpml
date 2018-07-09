<?php

namespace GFPDF\Plugins\WPML\Wpml;

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
 * Class TestWpml
 *
 * @group   wpml
 *
 * @package GFPDF\Plugins\WPML\Wpml
 */
class TestWpml extends \WP_UnitTestCase {

	/**
	 * @var WpmlTesting
	 *
	 * @since 0.1
	 */
	protected $class;

	/**
	 * @since 0.1
	 */
	public function setUp() {

		parent::setUp();

		$this->class = new WpmlTesting();
	}

	/**
	 * @since 0.1
	 */
	public function test_get_translated_url() {
		$url = $this->class->get_translated_url( 'http://example.org/', 'en' );
		$this->assertEquals( 'http://example.org/en/', $url );

		$url = $this->class->get_translated_url( 'http://example.org/', 'it' );
		$this->assertEquals( 'http://example.org/it/', $url );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_default_site_language() {
		$this->assertEquals( 'en', $this->class->get_default_site_language() );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_current_site_language() {
		$this->assertEquals( 'en', $this->class->get_current_site_language() );

		$this->class->set_site_language( 'it' );

		$this->assertEquals( 'it', $this->class->get_current_site_language() );

		$this->class->restore_site_language();

		$this->assertEquals( 'en', $this->class->get_current_site_language() );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_site_languages() {
		$languages = $this->class->get_site_languages();
		$this->assertCount( 6, $languages );
	}

	/**
	 * @since 0.1
	 */
	public function test_has_site_language() {
		$this->assertTrue( $this->class->has_site_language( 'fr' ) );
		$this->assertTrue( $this->class->has_site_language( 'hi' ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_gravityform_languages() {
		$languages = $this->class->get_gravityform_languages( [ 'title' => 'My Form' ] );
		$this->assertCount( 3, $languages );
	}

	/**
	 * @since 0.1
	 */
	public function test_has_translated_gravityform() {
		$form = [ 'title' => 'My Form' ];

		$this->assertTrue( $this->class->has_translated_gravityform( $form, 'en' ) );
		$this->assertTrue( $this->class->has_translated_gravityform( $form, 'es' ) );

		$this->assertTrue( $this->class->has_translated_gravityform( $form, 'fr' ) );
		$this->assertFalse( $this->class->has_translated_gravityform( $form, 'it' ) );
		$this->assertFalse( $this->class->has_translated_gravityform( $form, 'pe' ) );
	}

	/**
	 * @since 0.1
	 */
	public function test_get_translated_gravityform() {
		$form = [ 'title' => 'My Form' ];

		$new_form = $this->class->get_translated_gravityform( $form, 'en' );
		$this->assertEquals( 'My Form', $new_form['title'] );

		$new_form = $this->class->get_translated_gravityform( $form, 'es' );
		$this->assertEquals( 'My Form (es)', $new_form['title'] );

		$new_form = $this->class->get_translated_gravityform( $form, 'it' );
		$this->assertEquals( 'My Form (it)', $new_form['title'] );
	}
}
