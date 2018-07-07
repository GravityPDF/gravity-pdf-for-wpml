<?php

namespace GFPDF\Plugins\WPML\Wpml;

use WPML_Package;

/**
 * Handles all the WPML Interaction
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
 * Class Wpml
 *
 * @package GFPDF\Plugins\WPML\Wpml
 */
class Wpml implements WpmlInterface {

	/**
	 * Holds a cache of the Gravity Forms translations
	 *
	 * @var array
	 *
	 * @since    0.1
	 *
	 * @Internal This prevents repeat database lookups each request
	 */
	protected $gravityforms_translations = [];

	/**
	 * Convert the URL to its translated counterpart
	 *
	 * @param string $url           The URL to convert
	 * @param string $language_code The two-character language code
	 *
	 * @return boolean
	 *
	 * @since 0.1
	 */
	public function get_translated_url( $url, $language_code ) {
		$translated_url = apply_filters( 'wpml_permalink', $url, $language_code );
		return $translated_url !== null ? $translated_url : $url;
	}

	/**
	 * Get the default site language
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_default_site_language() {
		return apply_filters( 'wpml_default_language', '' );
	}

	/**
	 * Get the current site language
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_current_site_language() {
		return apply_filters( 'wpml_current_language', '' );
	}

	/**
	 * Return all active WPML languages. Don't skip languages with missing translations
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function get_site_languages() {
		return apply_filters( 'wpml_active_languages', '', [ 'skip_missing' => 0 ] );
	}

	/**
	 * Check if the site has an active WPML language
	 *
	 * @param string $language_code The two-character language code
	 *
	 * @return boolean
	 *
	 * @since 0.1
	 */
	public function has_site_language( $language_code ) {
		$languages = $this->get_site_languages();

		return isset( $languages[ $language_code ] );
	}

	/**
	 * Dynamically set the current active language
	 *
	 * @param string $language_code The two-character language code
	 *
	 * @since 0.1
	 */
	public function set_site_language( $language_code ) {
		do_action( 'wpml_switch_language', $language_code );
	}

	/**
	 * Restore the language back to the original
	 *
	 * @since 0.1
	 */
	public function restore_site_language() {
		do_action( 'wpml_switch_language', null );
	}

	/**
	 * Get a list of all languages the Gravity Form has been translated into
	 *
	 * @param array $form The Gravity Forms form object
	 *
	 * @return array A list containing the two-character language codes
	 *
	 * @since 0.1
	 */
	public function get_gravityform_languages( $form ) {
		$gf_languages        = [];
		$available_languages = $this->get_site_languages();

		foreach ( $available_languages as $language_code => $language ) {
			if ( $this->has_translated_gravityform( $form, $language_code ) ) {
				$gf_languages[ $language_code ] = $language;
			}
		}

		return $gf_languages;
	}

	/**
	 * Check if the Gravity Form has been translated
	 *
	 * @param array  $form          The Gravity Forms form object
	 * @param string $language_code The two-character language code
	 *
	 * @return boolean
	 *
	 * @since 0.1
	 */
	public function has_translated_gravityform( $form, $language_code ) {
		if ( $this->get_default_site_language() === $language_code ) {
			return true;
		}

		$available_languages = $this->get_gravityforms_translations( $form );
		$status              = isset( $available_languages[ $language_code ] ) ? $available_languages[ $language_code ]->status : 0;

		if ( defined( 'ICL_TM_NEEDS_UPDATE' ) && defined( 'ICL_TM_COMPLETE' ) ) {
			switch ( $status ) {
				case ICL_TM_NEEDS_UPDATE:
				case ICL_TM_COMPLETE:
					return true;
			}
		}

		return false;
	}

	/**
	 * Get the translated Gravity Forms form object
	 *
	 * @param array  $form          The Gravity Forms form object
	 * @param string $language_code The two-character language code
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function get_translated_gravityform( $form, $language_code ) {
		$this->set_site_language( $language_code );
		$translated_form = apply_filters( 'gform_pre_render', $form, false, [] );
		$this->restore_site_language();

		return $translated_form;
	}

	/**
	 * Get all available translations for a particular Gravity Form
	 *
	 * @param array $form The Gravity Forms form object
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	protected function get_gravityforms_translations( $form ) {
		global $sitepress, $wpdb;

		if (
			! class_exists( 'WPML_Package' ) ||
			! method_exists( $sitepress, 'get_element_trid' ) ||
			! method_exists( $sitepress, 'get_element_translations' )
		) {
			return [];
		}

		if ( isset( $this->gravityforms_translations[ $form['id'] ] ) ) {
			return $this->gravityforms_translations[ $form['id'] ];
		}

		/* Get available translations for a Gravity Form */
		$package                = new \WPML_Package( $GLOBALS['wpml_gfml_tm_api']->get_form_package( $form ) );
		$element_type           = $package->get_package_element_type();
		$trid                   = $sitepress->get_element_trid( $package->ID, $element_type );
		$available_translations = $sitepress->get_element_translations( $trid, $element_type );

		/*
		 * Prepare a single SQL statement to get the translation status from WPML
		 *
		 * Disabled SQL code-sniff check because we're dynamically populating the placeholders
		 * phpcs:disable WordPress.WP.PreparedSQL.NotPrepared
		 */
		$placeholder = implode( ', ', array_fill( 0, count( $available_translations ), '%d' ) );
		$sql         = "SELECT translation_id, status FROM {$wpdb->prefix}icl_translation_status WHERE translation_id IN ($placeholder)";

		$translation_status = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				array_map(
					function( $translation ) {
							return $translation->translation_id;
					}, $available_translations
				)
			)
		);
		/* phpcs:enable */

		/* Convert the translation status to a easy-to-check array */
		$translation_status_by_id = [];
		foreach ( $translation_status as $translation ) {
			$translation_status_by_id[ $translation->translation_id ] = $translation->status;
		}

		/* Assign the status to the translation */
		foreach ( $available_translations as $translation ) {
			$translation->status = isset( $translation_status_by_id[ $translation->translation_id ] ) ? $translation_status_by_id[ $translation->translation_id ] : 0;
		}

		/* Cache the results */
		$this->gravityforms_translations[ $form['id'] ] = $available_translations;

		return $available_translations;
	}
}
