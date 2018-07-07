<?php

namespace GFPDF\Plugins\WPML\Form;

use GFPDF\Helper\Helper_Trait_Logger;
use GFPDF\Plugins\WPML\Exceptions\GpdfWpmlException;
use GFPDF\Plugins\WPML\Wpml\WpmlInterface;

/**
 * Allow Gravity Forms Entry Language Code to be viewed / edited
 *
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
class EditWpmlLanguageCode {

	/**
	 * Including Logging Support
	 *
	 * @since 0.1
	 */
	use Helper_Trait_Logger;

	/**
	 * @var WpmlInterface
	 *
	 * @since 0.1
	 */
	protected $wpml;

	/**
	 * @var GravityFormsInterface
	 *
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

		if ( apply_filters( 'gravitypdf_wpml_disable_change_language', false ) ) {
			$this->logger->warning( 'WPML Language Viewer / Switcher disabled via filter' );

			return;
		}

		if ( ! $this->gf->has_capability( 'gravityforms_edit_entries' ) ) {
			$this->logger->warning( 'WPML Language Viewer / Switcher disabled due to insufficient capabilities' );

			return;
		}

		if ( ! in_array( $this->gf->get_page(), [ 'entry_detail_edit', 'entry_detail' ], true ) ) {
			return;
		}

		$this->add_actions();
	}

	/**
	 * @since 0.1
	 */
	public function add_actions() {
		add_action( 'gform_entry_info', [ $this, 'add_language_selector' ], 5, 2 );
		add_action( 'gform_after_update_entry', [ $this, 'update_language' ], 10, 2 );
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
	public function add_language_selector( $form_id, $entry ) {
		$language_code = $this->gf->get_entry_language_code( $entry['id'] );

		if ( $this->gf->get_page() !== 'entry_detail_edit' ) {
			$this->add_language_selector_view( $language_code, $this->wpml->get_site_languages() );
		} elseif ( strlen( $language_code ) > 0 ) {
			try {
				$this->add_language_selector_edit( $language_code, $this->wpml->get_gravityform_languages( $this->gf->get_form( $form_id ) ) );
			} catch ( GpdfWpmlException $e ) {
				$this->logger->error( 'Language Switcher: ' . $e->getMessage(), [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
				] );
			}
		}
	}

	/**
	 * Update the Gravity Form entry language
	 *
	 * @param  array $form
	 * @param  int   $entry_id
	 *
	 * @since 0.1
	 */
	public function update_language( $form, $entry_id ) {

		/* Verify the nonce */
		if (
			! isset( $_POST['gpdf_original_language_nonce'] ) ||
			! wp_verify_nonce( $_POST['gpdf_original_language_nonce'], 'gpdf_original_language_nonce' )
		) {
			$this->logger->warning( 'WPML Language Switcher Nonce Failure', [
				'user_id'  => get_current_user_id(),
				'form_id'  => $form['id'],
				'entry_id' => $entry_id,
			] );

			return;
		}

		$new_language_code = isset( $_POST['gpdf_language'] ) ? $_POST['gpdf_language'] : '';
		$old_language_code = isset( $_POST['gpdf_original_language'] ) ? $_POST['gpdf_original_language'] : '';

		/* Ensure the note languages are displayed in the default site language */
		$this->wpml->set_site_language( $this->wpml->get_default_site_language() );
		$languages = $this->wpml->get_site_languages();

		/* Do nothing if old/new codes are the same, or cannot find the new code */
		if ( $new_language_code === $old_language_code || ! isset( $languages[ $new_language_code ] ) ) {
			return;
		}

		$this->gf->save_entry_language_code( $entry_id, $new_language_code );
		$this->update_language_note( $entry_id, $old_language_code, $new_language_code, $languages );

		$this->wpml->restore_site_language();
	}

	/**
	 * Add a dropdown selector to change the Gravity Forms Entry Language
	 *
	 * @param string $language_code The Gravity Forms Entry Language Code
	 * @param array  $languages     The array of current active WPML languages for the Gravity Form
	 *
	 * @since 0.1
	 */
	protected function add_language_selector_edit( $language_code, $languages ) {
		$languages = $this->add_language_if_doesnt_exist( $language_code, $languages );

		include __DIR__ . '/markup/EntryLanguageEdit.php';
	}

	/**
	 * Add language if doesn't exist in the active Gravity Forms languages
	 *
	 * @param string $language_code The Gravity Forms Entry Language Code
	 * @param array  $languages     The array of current active WPML languages for the Gravity Form
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	protected function add_language_if_doesnt_exist( $language_code, $languages ) {
		if ( strlen( $language_code ) > 0 && ! isset( $languages[ $language_code ] ) ) {
			$site_languages = $this->wpml->get_site_languages();
			if ( isset( $site_languages[ $language_code ] ) ) {
				$languages[ $language_code ] = $site_languages[ $language_code ];
			} else {
				$languages[ $language_code ] = [
					'code'            => $language_code,
					'native_name'     => $language_code,
					'translated_name' => $language_code,
				];
			}
		}

		return $languages;
	}

	/**
	 * Show the current Gravity Forms Entry Language
	 *
	 * @param string $language_code The Gravity Forms Entry Language Code
	 * @param array  $languages     The array of current active WPML languages
	 *
	 * @since 0.1
	 */
	protected function add_language_selector_view( $language_code, $languages ) {
		$native_lang     = isset( $languages[ $language_code ] ) ? $languages[ $language_code ]['native_name'] : $language_code;
		$translated_lang = isset( $languages[ $language_code ] ) ? $languages[ $language_code ]['translated_name'] : $language_code;
		$language        = ( $native_lang === $translated_lang ) ? $translated_lang : "$translated_lang ($native_lang)";

		include __DIR__ . '/markup/EntryLanguageView.php';
	}

	/**
	 * Save a note to the Gravity Form Entry about the language change
	 *
	 * @param int    $entry_id          The Gravity Forms Entry ID
	 * @param string $old_language_code The old Gravity Forms Entry Language Code
	 * @param string $new_language_code The new Gravity Forms Entry Language Code
	 * @param array  $languages         The array of current active WPML languages
	 *
	 * @since 0.1
	 */
	protected function update_language_note( $entry_id, $old_language_code, $new_language_code, $languages ) {
		$old_native_lang     = isset( $languages[ $old_language_code ] ) ? $languages[ $old_language_code ]['native_name'] : $old_language_code;
		$old_translated_lang = isset( $languages[ $old_language_code ] ) ? $languages[ $old_language_code ]['translated_name'] : $old_language_code;
		$new_native_lang     = $languages[ $new_language_code ]['native_name'];
		$new_translated_lang = $languages[ $new_language_code ]['translated_name'];

		$message = sprintf(
			__( 'Changed entry language from %1$s to %2$s', 'gravity-pdf-for-wpml' ),
			( $old_native_lang === $old_translated_lang ) ? $old_native_lang : "$old_native_lang ($old_translated_lang)",
			( $new_native_lang === $new_translated_lang ) ? $new_native_lang : "$new_native_lang ($new_translated_lang)"
		);

		$this->gf->add_note( $entry_id, $message );
		$this->logger->notice( $message );
	}
}
