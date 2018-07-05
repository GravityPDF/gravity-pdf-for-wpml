<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Helper\Helper_Interface_Actions;
use GFPDF\Helper\Helper_Interface_Filters;
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
 * Class Notifications
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
class Notifications implements Helper_Interface_Actions, Helper_Interface_Filters {

	public $currentLanguage = '';

	/**
	 * @var WpmlInterface
	 */
	protected $wpml;

	/**
	 * @var GravityFormsInterface
	 */
	protected $gf;

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
		/* TODO - Add these actions into core plugin */
		add_action( 'gfpdf_pre_generate_and_save_pdf', [ $this, 'prePdfGeneration' ], 10, 4 );
		add_action( 'gfpdf_post_generate_and_save_pdf', [ $this, 'postPdfGeneration' ] );
	}

	public function add_filters() {
		add_filter( 'gform_form_post_get_meta', [ $this, 'translateGravityForm' ] );
	}

	public function prePdfGeneration( $notifications, $entry, $pdfSettings, $form ) {
		$languageCode = $this->gf->getEntryLanguageCode( $entry['id'] );
		if ( $this->wpml->hasTranslatedGravityForm( $form, $languageCode ) ) {
			return;
		}

		/* Ensure we can translate the form object */
		/* @TODO - add to GF object */
		GFFormsModel::flush_current_forms();
		$this->currentLanguage = $languageCode;
	}

	public function postPdfGeneration() {
		/* @TODO - add to GF object */
		GFFormsModel::flush_current_forms();
		$this->currentLanguage = '';
	}

	/* Get the WPML Gravity Forms class and translate the form */
	public function translateGravityForm( $form ) {
		if ( strlen( $this->currentLanguage ) === 0 ) {
			return $form;
		}

		$this->wpml->setSiteLanguage( $this->currentLanguage );
		$form = $this->wpml->getTranslatedGravityForm( $form );
		$this->wpml->restoreSiteLanguage();

		return $form;
	}
}