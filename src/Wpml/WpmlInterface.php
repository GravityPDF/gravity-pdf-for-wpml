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
 * Interface Wpml
 *
 * @package GFPDF\Plugins\WPML\Wpml
 *
 * @since   0.1
 */
interface WpmlInterface {

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
	public function getTranslatedUrl( $url, $languageCode );

	/**
	 * Return all active WPML languages
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function getSiteLanguages();

	/**
	 * Check if the site has an active WPML language
	 *
	 * @param string $languageCode The two-character language code
	 *
	 * @return boolean
	 *
	 * @since 0.1
	 */
	public function hasSiteLanguage( $languageCode );

	/**
	 * Dynamically set the current active language
	 *
	 * @param string $languageCode The two-character language code
	 *
	 * @return null
	 *
	 * @since 0.1
	 */
	public function setSiteLanguage( $languageCode );

	/**
	 * Restore the language back to the original
	 *
	 * @return null
	 *
	 * @since 0.1
	 */
	public function restoreSiteLanguage();

	/**
	 * Get a list of all languages the Gravity Form has been translated into
	 *
	 * @param array $form The Gravity Forms form object
	 *
	 * @return array A list containing the two-character language codes
	 *
	 * @since 0.1
	 */
	public function getGravityFormLanguages( $form );

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
	public function hasTranslatedGravityForm( $form, $languageCode );

	/**
	 * The translated Gravity Forms form object
	 *
	 * @param array $form         The Gravity Forms form object
	 * @param       $languageCode The two-character language code
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function getTranslatedGravityForm( $form, $languageCode );
}