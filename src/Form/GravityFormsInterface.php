<?php

namespace GFPDF\Plugins\WPML\Form;

use GFPDF\Plugins\WPML\Exceptions\GpdfWpmlException;

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
	 * @throws GpdfWpmlException
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
	 *
	 * @throws GpdfWpmlException
	 *
	 * @since 0.1
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
	 * @param int $entryId The Gravity Form Entry ID
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function getEntryLanguageCode( $entryId );

	/**
	 * Flush the current Gravity Form from the cache
	 *
	 * @since 0.1
	 */
	public function flushCurrentGravityForm();

	/**
	 * Check if the current user has a particular capability
	 *
	 * @param string   $capability
	 * @param int|null $userId
	 *
	 * @return bool
	 *
	 * @since 0.1
	 */
	public function hasCapability( $capability, $userId = null );

	/**
	 * Get the page the user is currently on
	 *
	 * @return string The Gravity Forms page, or an empty string
	 *
	 * @since 0.1
	 */
	public function getPage();

	/**
	 * Add a note to the Gravity Form Entry
	 *
	 * @param int    $entryId
	 * @param string $note
	 *
	 * @since 0.1
	 */
	public function addNote( $entryId, $note );
}
