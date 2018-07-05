<?php

namespace GFPDF\Plugins\WPML\Form;

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
 * Interface GravityFormsInterface
 *
 * @package GFPDF\Plugins\WPML\Form
 */
interface GravityFormsInterface {

	/**
	 * Get a Gravity Form object
	 *
	 * @param int $formId The Gravity Form ID
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function getForm( $formId );

	/**
	 * Get a Gravity Form Entry object
	 *
	 * @param int $entryId The Gravity Form Entry ID
	 *
	 * @return array
	 */
	public function getEntry( $entryId );

	/**
	 * Save the language code with the entry
	 *
	 * @param int    $entryId      The Gravity Form Entry ID
	 * @param string $languageCode The two-character language code
	 *
	 * @since 0.1
	 */
	public function saveEntryLanguageCode( $entryId, $languageCode );

	/**
	 * Get the language code from the entry
	 *
	 * @param $entryId The Gravity Form Entry ID
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function getEntryLanguageCode( $entryId );
}