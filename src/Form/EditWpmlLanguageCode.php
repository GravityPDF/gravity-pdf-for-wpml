<?php

namespace GFPDF\Plugins\WPML\Form;

use GFPDF\Helper\Helper_Interface_Actions;
use GPDFAPI;
use GFForms;
use GFFormsModel;

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
 * Class EditWpmlLanguageCode
 *
 * @package GFPDF\Plugins\WPML\Form
 */
class EditWpmlLanguageCode implements Helper_Interface_Actions {

	/**
	 * Initialise our module
	 *
	 * @since 0.1
	 */
	public function init() {
		$gform = GPDFAPI::get_form_class();

		/* Only load if user can edit Gravity Forms entries, we're in the right location and it hasn't been disabled */
		if (
			apply_filters( 'gravitypdf_wpml_disable_change_language', false ) ||
			! $gform->has_capability( 'gravityforms_edit_entries' ) ||
			! in_array( GFForms::get_page(), [ 'entry_detail_edit', 'entry_detail' ] )
		) {
			return;
		}

		$this->add_actions();
	}

	/**
	 * @since 0.1
	 */
	public function add_actions() {
		add_action( 'gform_entry_info', [ $this, 'addLanguageSelector' ], 5, 2 );
		add_action( "gform_after_update_entry", [ $this, 'updateLanguage' ], 10, 2 );
	}

	/**
	 * Output the field to change the entry language
	 *
	 * @param int   $form_id
	 * @param array $entry
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	function addLanguageSelector( $form_id, $entry ) {
		$currentLanguageCode = gform_get_meta( $entry['id'], 'wpml_language_code' );
		$languages           = apply_filters( 'wpml_active_languages', '' );

		if ( GFForms::get_page() !== 'entry_detail_edit' ) {
			$this->addLanguageSelectorView( $currentLanguageCode, $languages );
		} elseif ( strlen( $currentLanguageCode ) > 0 ) {
			$this->addLanguageSelectorView( $currentLanguageCode, $languages );
		}
	}

	protected function addLanguageSelectorView( $currentLanguageCode, $languages ) {
		/* Add language if missing */
		if ( strlen( $currentLanguageCode ) > 0 && ! isset( $languages[ $currentLanguageCode ] ) ) {
			$languages[ $currentLanguageCode ] = [
				'code'            => $currentLanguageCode,
				'native_name'     => $currentLanguageCode,
				'translated_name' => $currentLanguageCode,
			];
		}

		ob_start();
		?>
        <label for="change_wpml_language"><?= esc_html__( 'Change Language:', 'gravity-pdf-for-wpml' ); ?></label>
        <select name="gpdf_language" id="change_wpml_language" class="widefat">
			<?php foreach ( $languages as $lang ): ?>
                <option value="<?= esc_attr( $lang['code'] ); ?>" <?php selected( $currentLanguageCode, $lang['code'] ); ?>>
					<?= esc_attr( $lang['native_name'] ); ?>
					<?php if ( $lang['native_name'] !== $lang['translated_name'] ): ?>
                        (<?= esc_attr( $lang['translated_name'] ); ?>)
					<?php endif; ?>
                </option>
			<?php endforeach; ?>
        </select>

        <input name="gpdf_original_language" value="<?= esc_attr( $currentLanguageCode ); ?>" type="hidden" />

        <br><br>

		<?php
		echo ob_get_clean();
	}

	protected function addLanguageSelectorEdit( $currentLanguageCode, $languages ) {
		$nativeLang     = $languages[ $currentLanguageCode ]['native_name'];
		$translatedLang = $languages[ $currentLanguageCode ]['translated_name'];

		echo sprintf(
			__( 'Language: %s', 'gravity-pdf-for-wpml' ),
			( $nativeLang === $translatedLang ) ? $nativeLang : "$nativeLang ($translatedLang)"
		);

		echo '<br><br>';
	}

	/**
	 * When the entry creator is changed, add a note to the entry
	 *
	 * @param  array $form
	 * @param  int   $entry_id
	 *
	 * @return void
	 */
	function updateLanguage( $form, $entry_id ) {
		global $current_user;

		$newLanguageCode = ( isset( $_POST['gpdf_language'] ) ) ? $_POST['gpdf_language'] : '';
		$oldLanguageCode = ( isset( $_POST['gpdf_original_language'] ) ) ? $_POST['gpdf_original_language'] : '';
		$languages       = apply_filters( 'wpml_active_languages', '' );

		/* Do nothing if old/new codes are the same, or cannot find the new code */
		if ( $newLanguageCode === $oldLanguageCode || ! isset( $languages[ $newLanguageCode ] ) ) {
			return;
		}

		/* Update the language codes */
		gform_update_meta( $entry_id, 'wpml_language_code', $newLanguageCode );

		/* Add note about change to language if there is an old language code */
		if ( ! isset( $languages[ $oldLanguageCode ] ) ) {
			return;
		}

		$userData = get_userdata( $current_user->ID );

		$oldNativeLang     = $languages[ $oldLanguageCode ]['native_name'];
		$oldTranslatedLang = $languages[ $oldLanguageCode ]['translated_name'];
		$newNativeLang     = $languages[ $newLanguageCode ]['native_name'];
		$newTranslatedLang = $languages[ $newLanguageCode ]['translated_name'];

		$message = sprintf(
			__( 'Changed entry language from %s to %s', 'gravity-pdf-for-wpml' ),
			( $oldNativeLang === $oldTranslatedLang ) ? $oldNativeLang : "$oldNativeLang ($oldTranslatedLang)",
			( $newNativeLang === $newTranslatedLang ) ? $newNativeLang : "$newNativeLang ($newTranslatedLang)"
		);

		GFFormsModel::add_note( $entry_id, $current_user->ID, $userData->display_name, $message, 'note' );
	}

}
