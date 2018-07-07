<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Plugins\WPML\Exceptions\GpdfWpmlException;

/**
 * Interface for Gravity PDF
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
 * Interface PdfInterface
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
interface PdfInterface {

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
	public function get_pdf( $form_id, $pdf_id );

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
	public function get_pdf_name( $entry_id, $pdf_id );

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
	public function get_pdf_url( $entry_id, $pdf_id, $download = false );

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
	public function get_active_pdfs( $entry_id );

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
	public function get_option( $option_name, $default_value );

	/**
	 * Get the PDF Template Header Information
	 *
	 * @param string $template_id
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function get_template_info_by_id( $template_id );

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
	public function remove_filter( $filter_name, $class_name, $method_name, $priority = 10 );
}
