<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Plugins\WPML\Form\GravityFormsInterface;
use GFPDF\Plugins\WPML\Wpml\WpmlInterface;

/**
 * @package     Gravity PDF for WPML
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

/*
 * Exit if accessed directly
 * phpcs:disable
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/* phpcs:enable */

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
	 * @var WpmlInterface
	 * @since 0.1
	 */
	protected $wpml;

	/**
	 * @var GravityFormsInterface
	 * @since 0.1
	 */
	protected $gf;

	/**
	 * Holds the current entry language key for the PDF being generated
	 *
	 * @var string
	 * @since 0.1
	 */
	protected $currentLanguage = '';

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
		$this->addActions();
		$this->addFilters();
	}

	/**
	 * @since 0.1
	 */
	public function addActions() {
		/* @TODO - Add this action into core plugin (Controller_PDF) */
		add_action( 'gfpdf_pre_view_or_download_pdf', [ $this, 'prePdfViewOrDownload' ] );

		/* @TODO - Add these actions into core plugin (Model_PDF, api.php) */
		add_action( 'gfpdf_pre_generate_and_save_pdf_notification', [ $this, 'prePdfGeneration' ], 10, 2 );
		add_action( 'gfpdf_pre_generate_and_save_pdf', [ $this, 'prePdfGeneration' ], 10, 2 );

		add_action( 'gfpdf_post_generate_and_save_pdf_notification', [ $this, 'postPdfGeneration' ] );
		add_action( 'gfpdf_post_generate_and_save_pdf', [ $this, 'postPdfGeneration' ] );
	}

	/**
	 * @since 0.1
	 */
	public function addFilters() {
		add_filter( 'gform_form_post_get_meta', [ $this, 'translateGravityForm' ] );
	}

	/**
	 * Flush the GF cache and store the current site language
	 *
	 * @since 0.1
	 */
	public function prePdfViewOrDownload() {
		$this->gf->flushCurrentGravityForm();
		$this->currentLanguage = $this->wpml->getCurrentSiteLanguage();
	}

	/**
	 * Check if the entry translated language is valid, flush the GF cache and store the language code
	 *
	 * @param array $form  The Gravity Forms object
	 * @param array $entry The Gravity Forms entry object
	 *
	 * @since 0.1
	 */
	public function prePdfGeneration( $form, $entry ) {
		$languageCode = $this->gf->getEntryLanguageCode( $entry['id'] );
		if ( ! $this->wpml->hasTranslatedGravityForm( $form, $languageCode ) ) {
			return;
		}

		/* Ensure we can translate the form object */
		$this->gf->flushCurrentGravityForm();
		$this->currentLanguage = $languageCode;
	}

	/**
	 * Flush the GF cache and unset the language code
	 */
	public function postPdfGeneration() {
		$this->gf->flushCurrentGravityForm();
		$this->currentLanguage = '';
	}

	/**
	 * Get the WPML Gravity Forms class and translate the form
	 *
	 * @param array $form The Gravity Forms object
	 *
	 * @return array
	 */
	public function translateGravityForm( $form ) {
		if ( strlen( $this->currentLanguage ) === 0 ) {
			return $form;
		}

		return $this->wpml->getTranslatedGravityForm( $form, $this->currentLanguage );
	}
}
