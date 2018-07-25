<?php

namespace GFPDF\Plugins\WPML\Wpml;

/**
 * Handles the PDF WPML String Registration
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
 * Class RegisterPdfTranslations
 *
 * @package GFPDF\Plugins\WPML\Wpml
 */
class RegisterPdfTranslations {

	/**
	 * @since 0.1
	 */
	public function init() {
		$this->add_filters();
		$this->add_actions();
	}

	public function add_actions() {
		add_action( 'wpml_gf_register_strings', [ $this, 'register_gravitypdf_strings' ], 10, 3 );
	}

	public function add_filters() {
		add_filter( 'wpml_gf_translate_form_values', [ $this, 'transform_gravitypdf_strings' ], 10, 3 );
		add_filter( 'gfpdf_pdf_generator_pre_processing', [ $this, 'set_correct_pdf_settings' ] );
	}

	public function register_gravitypdf_strings( $form_package, $form, $gfml ) {

		$model_form_settings = \GPDFAPI::get_mvc_class( 'Model_Form_Settings' );
		$options             = \GPDFAPI::get_options_class();

		$snh  = new \GFML_String_Name_Helper();
		$pdfs = \GPDFAPI::get_form_pdfs( $form['id'] );

		$_GET['pid'] = $_GET['id'] = 0;

		foreach ( $pdfs as $pid => $settings ) {

			$gfml->register_gf_string( $settings['filename'], $snh->sanitize_string( "pdf-filename-$pid" ), $form_package, sprintf( 'PDF Filename for %s', $settings['name'] ) );

			/* Register the template Text, Paragraph and Rich Text Template Fields automatically */
			$_GET['pid'] = $pid;
			$_GET['id']  = $form['id'];

			$model_form_settings->register_custom_appearance_settings( $settings );
			$fields = $options->get_registered_fields();

			foreach ( $fields['form_settings_custom_appearance'] as $field ) {
				switch ( $field['type'] ) {
					case 'rich_editor':
					case 'textarea':
					case 'text':
						if ( isset( $settings[ $field['id'] ] ) ) {
							$field_type  = $field['type'] !== 'text' ? 'AREA' : 'LINE';
							$field_id    = $snh->sanitize_string( "pdf-{$field['id']}-$pid" );
							$field_label = sprintf( 'PDF %s for %s', $field['name'], $settings['name'] );
							$gfml->register_gf_string( $settings[ $field['id'] ], $field_id, $form_package, $field_label, $field_type );
						}
					break;
				}
			}

			unset( $_GET['pid'] );
			unset( $_GET['id'] );
		}
	}

	public function transform_gravitypdf_strings( $form, $st_context, $gfml ) {

		$pdfs = isset( $form['gfpdf_form_settings'] ) ? $form['gfpdf_form_settings'] : [];

		if ( count( $pdfs ) === 0 ) {
			return $form;
		}

		/** Include admin user capabilities functions to get access to get_editable_roles() */
		require_once ABSPATH . 'wp-admin/includes/user.php';

		$model_form_settings = \GPDFAPI::get_mvc_class( 'Model_Form_Settings' );
		$data                = \GPDFAPI::get_data_class();
		$options             = \GPDFAPI::get_options_class();

		$snh = new \GFML_String_Name_Helper();

		$data->form_settings = [ $form['id'] => $pdfs ];

		$_GET['pid'] = $_GET['id'] = 0;

		foreach ( $pdfs as $pid => $settings ) {

			/* Transform the template Text, Paragraph and Rich Text Template Fields automatically */
			$_GET['pid'] = $pid;
			$_GET['id']  = $form['id'];

			$model_form_settings->register_custom_appearance_settings( $settings );
			$fields = $options->get_registered_fields();

			$form['gfpdf_form_settings'][ $pid ]['filename'] = icl_t( $st_context, $snh->sanitize_string( "pdf-filename-$pid" ), $settings['filename'] );

			foreach ( $fields['form_settings_custom_appearance'] as $field ) {
				switch ( $field['type'] ) {
					case 'rich_editor':
					case 'textarea':
					case 'text':
						if ( isset( $settings[ $field['id'] ] ) ) {
							$field_id = $snh->sanitize_string( "pdf-{$field['id']}-$pid" );

							$form['gfpdf_form_settings'][ $pid ][ $field['id'] ] = icl_t( $st_context, $field_id, $settings[ $field['id'] ] );
						}
					break;
				}
			}

			unset( $_GET['pid'] );
			unset( $_GET['id'] );

			$data->form_settings = [ $form['id'] => $form['gfpdf_form_settings'] ];

			return $form;
		}
	}

	public function set_correct_pdf_settings( $pdf_generator ) {
		$data    = \GPDFAPI::get_data_class();
		$entry   = $pdf_generator->get_entry();
		$form_id = $entry['form_id'];
		$current_settings = $pdf_generator->get_settings();

		if ( isset( $data->form_settings[ $form_id ][ $current_settings['id'] ] ) ) {
			$pdf_generator->set_settings( $data->form_settings[ $form_id ][ $current_settings['id'] ] );
		}

		return $pdf_generator;
	}

}
