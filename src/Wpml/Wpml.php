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
 * Class Wpml
 *
 * @package GFPDF\Plugins\WPML\Wpml
 */
class Wpml implements WpmlInterface {

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
		$default_language    = $this->get_default_site_language();

		foreach ( $available_languages as $language_code => $language ) {
			if ( $language_code === $default_language || $this->has_translated_gravityform( $form, $language_code ) ) {
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

		return $form !== $this->get_translated_gravityform( $form, $language_code );
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
}
