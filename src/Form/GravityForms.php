<?php

namespace GFPDF\Plugins\WPML\Form;

use GPDFAPI;
use GFFormsModel;
use GFForms;

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


class GravityForms implements GravityFormsInterface {

	/**
	 * Get a Gravity Form object
	 *
	 * @param int $formId The Gravity Form ID
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function getForm( $formId ) {
		$gform = GPDFAPI::get_form_class();

		$form = $gform->get_form( $formId );

		if ( $form === null ) {
			throw new Exception( sprintf( 'Could not find Gravity Form with ID %s', $formId ) );
		}

		return $form;
	}

	/**
	 * Get a Gravity Form Entry object
	 *
	 * @param int $entryId The Gravity Form Entry ID
	 *
	 * @return array
	 */
	public function getEntry( $entryId ) {
		$gform = GPDFAPI::get_form_class();
		$entry = $gform->get_entry( $entryId );

		if ( is_wp_error( $entry ) ) {
			throw new Exception( $entry->get_error_message() );
		}

		return $entry;
	}

	/**
	 * Save the language code with the entry
	 *
	 * @param int    $entryId      The Gravity Form Entry ID
	 * @param string $languageCode The two-character language code
	 *
	 * @since 0.1
	 */
	public function saveEntryLanguageCode( $entryId, $languageCode ) {
		gform_update_meta( $entryId, 'wpml_language_code', $languageCode );
	}

	/**
	 * Get the language code from the entry
	 *
	 * @param $entryId The Gravity Form Entry ID
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function getEntryLanguageCode( $entryId ) {
		$languageCode = gform_get_meta( $entryId, 'wpml_language_code' );

		if ( ! is_string( $languageCode ) ) {
			return '';
		}

		return $languageCode;
	}

	/**
	 * Flush the current Gravity Form from the cache
	 *
	 * @since 0.1
	 */
	public function flushCurrentGravityForm() {
		GFFormsModel::flush_current_forms();
	}

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
	public function hasCapability( $capability, $userId = null ) {
		$gform = GPDFAPI::get_form_class();
		return $gform->has_capability( $capability, $userId );
	}

	/**
	 * Get the page the user is currently on
	 *
	 * @return string The Gravity Forms page, or an empty string
	 *
	 * @since 0.1
	 */
	public function getPage() {
		$page = GFForms::get_page();
		return $page !== false ? $page : '';
	}

	/**
	 * Add a note to the Gravity Form Entry
	 *
	 * @param int    $entryId
	 * @param string $note
	 *
	 * @since 0.1
	 */
	public function addNote( $entryId, $note ) {
		global $current_user;

		$userData    = get_userdata( $current_user->ID );
		$displayName = $userData !== false ? $userData->display_name : 'Unknown';

		GFFormsModel::add_note( $entryId, $current_user->ID, $displayName, $note, 'note' );
	}
}