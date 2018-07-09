<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Helper\Helper_Trait_Logger;
use GFPDF\Plugins\WPML\Exceptions\GpdfWpmlException;
use GFPDF\Plugins\WPML\Form\GravityFormsInterface;
use GFPDF\Plugins\WPML\Wpml\WpmlInterface;

/**
 * Handles the Automatic PDF Translation
 *
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
	 * @var PdfInterface
	 *
	 * @since 0.1
	 */
	protected $pdf;

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
	public function __construct( WpmlInterface $wpml, GravityFormsInterface $gf, PdfInterface $pdf ) {
		$this->wpml = $wpml;
		$this->gf   = $gf;
		$this->pdf  = $pdf;
	}

	/**
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
		add_action( 'gfpdf_pre_generate_and_save_pdf_notification', [ $this, 'pre_pdf_generation_notification' ], 10, 4 );
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
	 * When generating PDFs via the API use the current site language, falling back to the default site language
	 *
	 * @param array $form  The Gravity Forms object
	 * @param array $entry The Gravity Forms entry object
	 *
	 * @since    0.1
	 *
	 * @Internal Use the Wpml::set_site_language and Wpml::restore_site_language before and after GPDFAPI::create_pdf, respectively, to control the generated PDF language
	 */
	public function pre_pdf_generation( $form, $entry ) {
		$this->pre_pdf_view_or_download( $entry['id'] );
	}

	/**
	 * When viewing / downloading PDFs, select the correct WPML language for the form, flush the GF cache and store the language code
	 *
	 * @param int $entry_id
	 *
	 * @since 0.1
	 */
	public function pre_pdf_view_or_download( $entry_id ) {
		$language_code = $this->wpml->get_current_site_language();

		try {
			$entry = $this->gf->get_entry( $entry_id );
			$form  = $this->gf->get_form( $entry['form_id'] );

			if ( ! $this->wpml->has_translated_gravityform( $form, $language_code ) ) {
				throw new GpdfWpmlException( sprintf( 'Could not find Gravity Form translation for "%s"', $language_code ) );
			}
		} catch ( GpdfWpmlException $e ) {
			$this->logger->error(
				'PDF View / Download / API: ' . $e->getMessage(), [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				]
			);

			$language_code = $this->wpml->get_default_site_language();
		}

		$this->set_language_code( $language_code );

		$this->logger->notice( sprintf( 'Set PDF WPML Language to "%1$s" for Gravity Forms Entry "%2$s"', $this->current_language, '#' . $entry_id ) );
	}

	/**
	 * When generating PDFs for notifications, select the correct WPML language for the form, flush the GF cache and store the language code
	 *
	 * @param array $form         The Gravity Forms object
	 * @param array $entry        The Gravity Forms entry object
	 * @param array $settings     The Gravity PDF settings object
	 * @param array $notification The Gravity Forms Notification object
	 *
	 * @since 0.1
	 */
	public function pre_pdf_generation_notification( $form, $entry, $settings, $notification ) {

		/* Check if we should translate the PDFs for the current notification */
		$translate_user_notifications = $this->pdf->get_option( 'wpml_user_notification', 'On' );
		if ( $translate_user_notifications === 'On' && ( ! isset( $notification['toType'] ) || $notification['toType'] !== 'field' ) ) {
			$language_code = $this->wpml->get_default_site_language();
		} else {
			$language_code = $this->gf->get_entry_language_code( $entry['id'] );
		}

		/* If we cant find the translated Gravity Form, use the default site language */
		if ( ! $this->wpml->has_translated_gravityform( $form, $language_code ) ) {
			$language_code = $this->wpml->get_default_site_language();
		}

		$this->set_language_code( $language_code );

		$this->logger->notice( sprintf( 'Set PDF WPML Language to "%1$s" for Gravity Forms Entry "%2$s"', $this->current_language, '#' . $entry['id'] ) );
	}

	/**
	 * Flush the GF cache and unset the language code
	 */
	public function post_pdf_generation() {
		$this->reset_language_code();

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

	/**
	 * Set the current language code for the PDF being generated
	 *
	 * @param string $language_code The two-character language code
	 *
	 * @since 0.1
	 */
	public function set_language_code( $language_code ) {
		$this->gf->flush_current_gravityform();
		$this->current_language = $language_code;
	}

	/**
	 * Get the current language code
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_language_code() {
		return $this->current_language;
	}

	/**
	 * Reset the current language code for the PDF being generated
	 *
	 * @since 0.1
	 */
	public function reset_language_code() {
		$this->set_language_code( '' );
	}
}
