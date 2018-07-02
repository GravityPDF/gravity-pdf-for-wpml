<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Helper\Helper_Interface_Actions;
use GFPDF\Helper\Helper_Interface_Filters;

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
		add_action( 'gfpdf_pre_generate_and_save_pdf', [ $this, 'starting' ], 10, 2 );
		add_action( 'gfpdf_post_generate_and_save_pdf', [ $this, 'ending' ] );
	}

	public function add_filters() {
		add_filter( 'gform_form_post_get_meta', [ $this, 'convertForm' ] );
	}

	public function starting( $notifications, $entry ) {
		$currentLanguageCode = gform_get_meta( $entry['id'], 'wpml_language_code' );
		$languages           = apply_filters( 'wpml_active_languages', '' );

		if ( ! isset( $languages[ $currentLanguageCode ] ) ) {
			return;
		}

		GFFormsModel::flush_current_forms();
		$this->currentLanguage = $currentLanguageCode;
	}

	public function ending() {
		$this->currentLanguage = '';
	}

	public function convertForm( $form ) {
		global $sitepress;

		/* Don't do anything if WPML class/method doesn't exist or not viewing a PDF */
		if ( ! isset( $GLOBALS['wpml_gfml_tm_api'] ) || get_class( $GLOBALS['wpml_gfml_tm_api'] ) !== 'GFML_TM_API' ) {
			return $form;
		}

		if ( strlen( $this->currentLanguage ) > 0 ) {
			/* Get the WPML Gravity Forms class and translate the form */
			$wpml_gfml_tm_api = $GLOBALS['wpml_gfml_tm_api'];
			$backupLang       = $sitepress->get_current_language();
			$sitepress->switch_lang( $this->currentLanguage );

			$_GET['lang'] = $this->currentLanguage;

			$form = $wpml_gfml_tm_api->gform_pre_render( $form );

			$sitepress->switch_lang( $backupLang );

			return $form;
		}

		if ( ! empty( $GLOBALS['wp']->query_vars['gpdf'] ) && ! empty( $GLOBALS['wp']->query_vars['pid'] ) && ! empty( $GLOBALS['wp']->query_vars['lid'] ) ) {
			/* Get the WPML Gravity Forms class and translate the form */
			$wpml_gfml_tm_api = $GLOBALS['wpml_gfml_tm_api'];
			$form             = $wpml_gfml_tm_api->gform_pre_render( $form );

			return $form;
		}

		return $form;
	}
}