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


class GravityForms implements GravityFormsInterface {

	public function saveLanguageCode( $entryId, $languageCode ) {
		gform_update_meta( $entryId, 'wpml_language_code', $languageCode );
	}

	public function getLanguageCode( $entryId ) {
		return gform_get_meta( $entryId, 'wpml_language_code' );
	}
}