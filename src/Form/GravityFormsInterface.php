<?php

namespace GFPDF\Plugins\WPML\Form;

use GFPDF\Plugins\WPML\Exceptions\GpdfWpmlException;

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
	 * @param int $form_id The Gravity Form ID
	 *
	 * @return array
	 *
	 * @throws GpdfWpmlException
	 *
	 * @since 0.1
	 */
	public function get_form( $form_id );

	/**
	 * Get a Gravity Form Entry object
	 *
	 * @param int $entry_id The Gravity Form Entry ID
	 *
	 * @return array
	 *
	 * @throws GpdfWpmlException
	 *
	 * @since 0.1
	 */
	public function get_entry( $entry_id );

	/**
	 * Save the language code with the entry
	 *
	 * @param int    $entry_id      The Gravity Form Entry ID
	 * @param string $language_code The two-character language code
	 *
	 * @since 0.1
	 */
	public function save_entry_language_code( $entry_id, $language_code );

	/**
	 * Get the language code from the entry
	 *
	 * @param int $entry_id The Gravity Form Entry ID
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_entry_language_code( $entry_id );

	/**
	 * Flush the current Gravity Form from the cache
	 *
	 * @since 0.1
	 */
	public function flush_current_gravityform();

	/**
	 * Check if the current user has a particular capability
	 *
	 * @param string   $capability
	 * @param int|null $user_id
	 *
	 * @return bool
	 *
	 * @since 0.1
	 */
	public function has_capability( $capability, $user_id = null );

	/**
	 * Get the page the user is currently on
	 *
	 * @return string The Gravity Forms page, or an empty string
	 *
	 * @since 0.1
	 */
	public function get_page();

	/**
	 * Add a note to the Gravity Form Entry
	 *
	 * @param int    $entry_id
	 * @param string $note
	 *
	 * @since 0.1
	 */
	public function add_note( $entry_id, $note );
}
