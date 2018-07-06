<?php

namespace GFPDF\Plugins\WPML\Form;

use GFPDF\Plugins\WPML\Wpml\WpmlInterface;

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
 * Class EditWpmlLanguageCode
 *
 * @package GFPDF\Plugins\WPML\Form
 */
class EditWpmlLanguageCode {

	/**
	 * @var WpmlInterface
	 * @since 0.1
	 */
	protected $wpml;

	/**
	 * @var GravityFormsInterface
	 * @since 0.1
	 */
	protected $gf;

	/**
	 * EditWpmlLanguageCode constructor.
	 *
	 * @param WpmlInterface         $wpml
	 * @param GravityFormsInterface $gf
	 *
	 * @since 0.1
	 */
	public function __construct( WpmlInterface $wpml, GravityFormsInterface $gf ) {
		$this->wpml = $wpml;
		$this->gf   = $gf;
	}

	/**
	 * Initialise our module if user can edit Gravity Forms entries, we're on the correct admin page and it hasn't been disabled
	 *
	 * @since 0.1
	 */
	public function init() {
		if (
			apply_filters( 'gravitypdf_wpml_disable_change_language', false ) ||
			! $this->gf->hasCapability( 'gravityforms_edit_entries' ) ||
			! in_array( $this->gf->getPage(), [ 'entry_detail_edit', 'entry_detail' ] )
		) {
			return;
		}

		$this->addActions();
	}

	/**
	 * @since 0.1
	 */
	public function addActions() {
		add_action( 'gform_entry_info', [ $this, 'addLanguageSelector' ], 5, 2 );
		add_action( 'gform_after_update_entry', [ $this, 'updateLanguage' ], 10, 2 );
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
	public function addLanguageSelector( $form_id, $entry ) {
		$languageCode = $this->gf->getEntryLanguageCode( $entry['id'] );
		$languages    = $this->wpml->getSiteLanguages();

		if ( $this->gf->getPage() !== 'entry_detail_edit' ) {
			$this->addLanguageSelectorView( $languageCode, $languages );
		} elseif ( strlen( $languageCode ) > 0 ) {
			$this->addLanguageSelectorEdit( $languageCode, $languages );
		}
	}

	/**
	 * Update the Gravity Form entry language
	 *
	 * @param  array $form
	 * @param  int   $entryId
	 *
	 * @since 0.1
	 */
	public function updateLanguage( $form, $entryId ) {
		$newLanguageCode = isset( $_POST['gpdf_language'] ) ? $_POST['gpdf_language'] : '';
		$oldLanguageCode = isset( $_POST['gpdf_original_language'] ) ? $_POST['gpdf_original_language'] : '';

		/* Ensure the note languages are displayed in the default site language */
		$this->wpml->setSiteLanguage( $this->wpml->getDefaultSiteLanguage() );
		$languages = $this->wpml->getSiteLanguages();

		/* Do nothing if old/new codes are the same, or cannot find the new code */
		if ( $newLanguageCode === $oldLanguageCode || ! isset( $languages[ $newLanguageCode ] ) ) {
			return;
		}

		$this->gf->saveEntryLanguageCode( $entryId, $newLanguageCode );
		$this->updateLanguageNote( $entryId, $oldLanguageCode, $newLanguageCode, $languages );

		$this->wpml->restoreSiteLanguage();
	}

	/**
	 * Add a dropdown selector to change the Gravity Forms Entry Language
	 *
	 * @param string $languageCode The Gravity Forms Entry Language Code
	 * @param array  $languages    The array of current active WPML languages
	 *
	 * @since 0.1
	 */
	protected function addLanguageSelectorEdit( $languageCode, $languages ) {
		/*
		 * Add current language if missing
		 * This might occur if a site language has been removed
		 */
		if ( strlen( $languageCode ) > 0 && ! isset( $languages[ $languageCode ] ) ) {
			$languages[ $languageCode ] = [
				'code'            => $languageCode,
				'native_name'     => $languageCode,
				'translated_name' => $languageCode,
			];
		}

		ob_start();
		include __DIR__ . '/markup/EntryLanguageEdit.php';
		echo ob_get_clean();
	}

	/**
	 * Show the current Gravity Forms Entry Language
	 *
	 * @param string $languageCode The Gravity Forms Entry Language Code
	 * @param array  $languages    The array of current active WPML languages
	 *
	 * @since 0.1
	 */
	protected function addLanguageSelectorView( $languageCode, $languages ) {
		$nativeLang     = isset( $languages[ $languageCode ] ) ? $languages[ $languageCode ]['native_name'] : $languageCode;
		$translatedLang = isset( $languages[ $languageCode ] ) ? $languages[ $languageCode ]['translated_name'] : $languageCode;
		$language       = ( $nativeLang === $translatedLang ) ? $translatedLang : "$translatedLang ($nativeLang)";

		ob_start();
		include __DIR__ . '/markup/EntryLanguageView.php';
		echo ob_get_clean();
	}

	/**
	 * Save a note to the Gravity Form Entry about the language change
	 *
	 * @param int    $entryId         The Gravity Forms Entry ID
	 * @param string $oldLanguageCode The old Gravity Forms Entry Language Code
	 * @param string $newLanguageCode The new Gravity Forms Entry Language Code
	 * @param array  $languages       The array of current active WPML languages
	 *
	 * @since 0.1
	 */
	protected function updateLanguageNote( $entryId, $oldLanguageCode, $newLanguageCode, $languages ) {
		$oldNativeLang     = isset( $languages[ $oldLanguageCode ] ) ? $languages[ $oldLanguageCode ]['native_name'] : $oldLanguageCode;
		$oldTranslatedLang = isset( $languages[ $oldLanguageCode ] ) ? $languages[ $oldLanguageCode ]['translated_name'] : $oldLanguageCode;
		$newNativeLang     = $languages[ $newLanguageCode ]['native_name'];
		$newTranslatedLang = $languages[ $newLanguageCode ]['translated_name'];

		$message = sprintf(
			__( 'Changed entry language from %1$s to %2$s', 'gravity-pdf-for-wpml' ),
			( $oldNativeLang === $oldTranslatedLang ) ? $oldNativeLang : "$oldNativeLang ($oldTranslatedLang)",
			( $newNativeLang === $newTranslatedLang ) ? $newNativeLang : "$newNativeLang ($newTranslatedLang)"
		);

		$this->gf->addNote( $entryId, $message );
	}
}
