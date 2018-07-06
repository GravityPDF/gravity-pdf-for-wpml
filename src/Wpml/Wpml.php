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
	 * @param string $url          The URL to convert
	 * @param string $languageCode The two-character language code
	 *
	 * @return boolean
	 *
	 * @since 0.1
	 */
	public function getTranslatedUrl( $url, $languageCode ) {
		$translatedUrl = apply_filters( 'wpml_permalink', $url, $languageCode );
		return $translatedUrl !== null ? $translatedUrl : $url;
	}

	/**
	 * Get the default site language
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function getDefaultSiteLanguage() {
		return apply_filters( 'wpml_default_language', '' );
	}

	/**
	 * Get the current site language
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function getCurrentSiteLanguage() {
		return apply_filters( 'wpml_current_language', '' );
	}

	/**
	 * Return all active WPML languages. Don't skip languages with missing translations
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function getSiteLanguages() {
		return apply_filters( 'wpml_active_languages', '', [ 'skip_missing' => 0 ] );
	}

	/**
	 * Check if the site has an active WPML language
	 *
	 * @param string $languageCode The two-character language code
	 *
	 * @return boolean
	 *
	 * @since 0.1
	 */
	public function hasSiteLanguage( $languageCode ) {
		$languages = $this->getSiteLanguages();

		return isset( $languages[ $languageCode ] );
	}

	/**
	 * Dynamically set the current active language
	 *
	 * @param string $languageCode The two-character language code
	 *
	 * @return null
	 *
	 * @since 0.1
	 */
	public function setSiteLanguage( $languageCode ) {
		do_action( 'wpml_switch_language', $languageCode );
	}

	/**
	 * Restore the language back to the original
	 *
	 * @return null
	 *
	 * @since 0.1
	 */
	public function restoreSiteLanguage() {
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
	public function getGravityFormLanguages( $form ) {
		$gfLanguages        = [];
		$availableLanguages = $this->getSiteLanguages();
		$defaultLanguage    = $this->getDefaultSiteLanguage();

		foreach ( $availableLanguages as $languageCode => $language ) {
			if ( $languageCode === $defaultLanguage || $this->hasTranslatedGravityForm( $form, $languageCode ) ) {
				$gfLanguages[ $languageCode ] = $language;
			}
		}

		return $gfLanguages;
	}

	/**
	 * Check if the Gravity Form has been translated
	 *
	 * @param array $form         The Gravity Forms form object
	 * @param       $languageCode The two-character language code
	 *
	 * @return boolean
	 *
	 * @since 0.1
	 */
	public function hasTranslatedGravityForm( $form, $languageCode ) {
		if ( $this->getDefaultSiteLanguage() === $languageCode ) {
			return true;
		}

		return $form !== $this->getTranslatedGravityForm( $form, $languageCode );
	}

	/**
	 * Get the translated Gravity Forms form object
	 *
	 * @param array $form         The Gravity Forms form object
	 * @param       $languageCode The two-character language code
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function getTranslatedGravityForm( $form, $languageCode ) {
		$this->setSiteLanguage( $languageCode );
		$translatedForm = apply_filters( 'gform_pre_render', $form, false, [] );
		$this->restoreSiteLanguage();

		return $translatedForm;
	}
}
