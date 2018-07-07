<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Helper\Helper_Trait_Logger;
use GFPDF\Plugins\WPML\Form\GravityFormsInterface;
use GFPDF\Plugins\WPML\Wpml\WpmlInterface;

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
 * Class Translation
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
class Translation {

	/**
	 * Including Logging Support
	 *
	 * @since 0.1
	 */
	use Helper_Trait_Logger;

	/**
	 * @var WpmlInterface
	 *
	 * @since 0.1
	 */
	protected $wpml;

	/**
	 * @var GravityFormsInterface
	 *
	 * @since 0.1
	 */
	protected $gf;

	/**
	 * Holds the current entry language key for the PDF being generated
	 *
	 * @var string
	 *
	 * @since 0.1
	 */
	protected $current_language = '';

	/**
	 * Translation constructor.
	 *
	 * @param WpmlInterface         $wpml
	 * @param GravityFormsInterface $gf
	 *
	 * @since 0.1
	 */
	public function __construct( WpmlInterface $wpml, GravityFormsInterface $gf ) {
		$this->wpml = $wpml;
		$this->gf   = $gf;
	}

	/**
	 * Initialise our module
	 *
	 * @since 0.1
	 */
	public function init() {
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * @since 0.1
	 */
	public function add_actions() {
		/* @TODO - Add this action into core plugin (Controller_PDF) */
		add_action( 'gfpdf_pre_view_or_download_pdf', [ $this, 'pre_pdf_view_or_download' ] );

		/* @TODO - Add these actions into core plugin (Model_PDF, api.php) */
		add_action( 'gfpdf_pre_generate_and_save_pdf_notification', [ $this, 'pre_pdf_generation' ], 10, 2 );
		add_action( 'gfpdf_pre_generate_and_save_pdf', [ $this, 'pre_pdf_generation' ], 10, 2 );

		add_action( 'gfpdf_post_generate_and_save_pdf_notification', [ $this, 'post_pdf_generation' ] );
		add_action( 'gfpdf_post_generate_and_save_pdf', [ $this, 'post_pdf_generation' ] );
	}

	/**
	 * @since 0.1
	 */
	public function add_filters() {
		add_filter( 'gform_form_post_get_meta', [ $this, 'translate_gravityform' ] );
	}

	/**
	 * Flush the GF cache and store the current site language
	 *
	 * @param int $entry_id
	 *
	 * @since 0.1
	 */
	public function pre_pdf_view_or_download( $entry_id ) {
		$this->gf->flush_current_gravityform();
		$this->current_language = $this->wpml->get_current_site_language();

		$this->logger->notice( sprintf( 'Set PDF WPML Language to "%1$s" for Gravity Forms Entry "%2$s"', $this->current_language, '#' . $entry_id ) );
	}

	/**
	 * Check if the entry translated language is valid, flush the GF cache and store the language code
	 *
	 * @param array $form  The Gravity Forms object
	 * @param array $entry The Gravity Forms entry object
	 *
	 * @since 0.1
	 */
	public function pre_pdf_generation( $form, $entry ) {
		$language_code = $this->gf->get_entry_language_code( $entry['id'] );
		if ( ! $this->wpml->has_translated_gravityform( $form, $language_code ) ) {
			return;
		}

		/* Ensure we can translate the form object */
		$this->gf->flush_current_gravityform();
		$this->current_language = $language_code;

		$this->logger->notice( sprintf( 'Set PDF WPML Language to "%1$s" for Gravity Forms Entry "%2$s"', $this->current_language, '#' . $entry['id'] ) );
	}

	/**
	 * Flush the GF cache and unset the language code
	 */
	public function post_pdf_generation() {
		$this->gf->flush_current_gravityform();
		$this->current_language = '';

		$this->logger->notice( 'Clear PDF WPML Language' );
	}

	/**
	 * Get the WPML Gravity Forms class and translate the form
	 *
	 * @param array $form The Gravity Forms object
	 *
	 * @return array
	 */
	public function translate_gravityform( $form ) {
		if ( strlen( $this->current_language ) === 0 ) {
			return $form;
		}

		$this->logger->notice( sprintf( 'Get Gravity Form "%1$s" in language "%2$s"', $form['id'] . ':' . $form['title'], $this->current_language ) );
		return $this->wpml->get_translated_gravityform( $form, $this->current_language );
	}
}
