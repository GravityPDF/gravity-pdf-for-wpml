<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Plugins\WPML\Exceptions\GpdfWpmlException;
use GFPDF\Plugins\WPML\Form\GravityFormsInterface;
use GPDFAPI;

/**
 * Handles all the Gravity PDF Interaction
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
 * Class Pdf
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
class Pdf implements PdfInterface {

	/**
	 * @var GravityFormsInterface
	 *
	 * @since 0.1
	 */
	protected $gf;

	/**
	 * Pdf constructor.
	 *
	 * @param GravityFormsInterface $gf
	 *
	 * @since 0.1
	 */
	public function __construct( GravityFormsInterface $gf ) {
		$this->gf = $gf;
	}

	/**
	 * Get the PDF Settings
	 *
	 * @param int    $form_id The Gravity Form Entry ID
	 * @param string $pdf_id  The PDF ID
	 *
	 * @return array
	 *
	 * @throws GpdfWpmlException
	 *
	 * @since 0.1
	 */
	public function get_pdf( $form_id, $pdf_id ) {
		$pdf = GPDFAPI::get_pdf( $form_id, $pdf_id );

		if ( is_wp_error( $pdf ) ) {
			throw new GpdfWpmlException( $pdf->get_error_message() );
		}

		return $pdf;
	}

	/**
	 * Get a PDF's Filename
	 *
	 * @param int    $entry_id The Gravity Form ID
	 * @param string $pdf_id   The PDF ID
	 *
	 * @return string
	 *
	 * @throws GpdfWpmlException
	 *
	 * @since 0.1
	 */
	public function get_pdf_name( $entry_id, $pdf_id ) {
		$entry       = $this->gf->get_entry( $entry_id );
		$pdf_setting = $this->get_pdf( $entry['form_id'], $pdf_id );
		$model_pdf   = GPDFAPI::get_mvc_class( 'Model_PDF' );

		return $model_pdf->get_pdf_name( $pdf_setting, $entry );
	}

	/**
	 * Get a PDF's URL
	 *
	 * @param int    $entry_id The Gravity Form Entry ID
	 * @param string $pdf_id   The PDF ID
	 * @param bool   $download Whether to generate the 'view' or 'download PDF URL
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function get_pdf_url( $entry_id, $pdf_id, $download = false ) {
		$model_pdf = GPDFAPI::get_mvc_class( 'Model_PDF' );
		return $model_pdf->get_pdf_url( $pdf_id, $entry_id, $download );
	}

	/**
	 * Get a list of active PDFs for a particular Gravity Form / Entry
	 *
	 * @param int $entry_id The Gravity Form Entry ID
	 *
	 * @return array
	 *
	 * @throws GpdfWpmlException
	 *
	 * @since 0.1
	 */
	public function get_active_pdfs( $entry_id ) {
		$entry = $this->gf->get_entry( $entry_id );
		$form  = $this->gf->get_form( $entry['form_id'] );

		if ( ! isset( $form['gfpdf_form_settings'] ) || ! is_array( $form['gfpdf_form_settings'] ) ) {
			return [];
		}

		$model_pdf = GPDFAPI::get_mvc_class( 'Model_PDF' );
		return $model_pdf->get_active_pdfs( $form['gfpdf_form_settings'], $entry );
	}

	/**
	 * Get the global PDF Setting
	 *
	 * @param string $option_name   The Global PDF Setting Name
	 * @param mixed  $default_value The fallback when no Global PDF Setting exists
	 *
	 * @return mixed
	 *
	 * @since 0.1
	 */
	public function get_option( $option_name, $default_value ) {
		return GPDFAPI::get_plugin_option( $option_name, $default_value );
	}

	/**
	 * Get the PDF Template Header Information
	 *
	 * @param string $template_id
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function get_template_info_by_id( $template_id ) {
		$template = GPDFAPI::get_templates_class();
		return $template->get_template_info_by_id( $template_id );
	}

	/**
	 * Remove a Gravity PDF Filter
	 *
	 * @param string $filter_name The WordPress Filter to remove
	 * @param string $class_name  The MVC Class the filter was assigned to
	 * @param string $method_name The MVC Method the filter ran
	 * @param int    $priority    The filter priority
	 *
	 * @since 0.1
	 */
	public function remove_filter( $filter_name, $class_name, $method_name, $priority = 10 ) {
		$class = GPDFAPI::get_mvc_class( $class_name );

		remove_filter( $filter_name, [ $class, $method_name ], $priority );
	}

	/**
	 * Checks if the current PDF template is WPML compatible
	 *
	 * @param array $pdf The PDF Settings
	 *
	 * @return bool
	 *
	 * @Internal Either the template specifically includes the `@WPML: true` header, or is apart of the Core/Universal templates
	 *
	 * @since    0.1
	 */
	public function is_template_wpml_compatible( $pdf ) {
		/* Check if has the `@WPML: true` header */
		if ( isset( $pdf['wpml'] ) && $pdf['wpml'] === 'true' ) {
			return true;
		}

		/* Check if group is Core/Universal which has WPML support out of the box */
		if ( isset( $pdf['group'] ) ) {
			$supported_template_groups = apply_filters( 'gfpdf_wpml_group_support', [ 'Core', 'Universal (Premium)' ], $pdf );
			if ( in_array( $pdf['group'], $supported_template_groups, true ) ) {
				return true;
			}
		}

		return false;
	}
}
