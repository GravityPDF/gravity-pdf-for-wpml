<?php

namespace GFPDF\Plugins\WPML\Pdf;

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
 * Interface PdfInterface
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
interface PdfInterface {

	/**
	 * Get the PDF Settings
	 *
	 * @param int    $formId The Gravity Form Entry ID
	 * @param string $pdfId  The PDF ID
	 *
	 * @return array
	 *
	 * @throws GpdfWpmlException
	 *
	 * @since 0.1
	 */
	public function getPdf( $formId, $pdfId );

	/**
	 * Get a PDF's Filename
	 *
	 * @param int    $entryId The Gravity Form ID
	 * @param string $pdfId   The PDF ID
	 *
	 * @return string
	 *
	 * @throws GpdfWpmlException
	 *
	 * @since 0.1
	 */
	public function getPdfName( $entryId, $pdfId );

	/**
	 * Get a PDF's URL
	 *
	 * @param int    $entryId  The Gravity Form Entry ID
	 * @param string $pdfId    The PDF ID
	 * @param bool   $download Whether to generate the 'view' or 'download PDF URL
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function getPdfUrl( $entryId, $pdfId, $download = false );

	/**
	 * Get a list of active PDFs for a particular Gravity Form / Entry
	 *
	 * @param int $entryId The Gravity Form Entry ID
	 *
	 * @return array
	 *
	 * @throws GpdfWpmlException
	 *
	 * @since 0.1
	 */
	public function getActivePdfs( $entryId );

	/**
	 * Get the global PDF Setting
	 *
	 * @param string $optionName   The Global PDF Setting Name
	 * @param mixed  $defaultValue The fallback when no Global PDF Setting exists
	 *
	 * @return mixed
	 *
	 * @since 0.1
	 */
	public function getOption( $optionName, $defaultValue );

	/**
	 * Get the PDF Template Header Information
	 *
	 * @param string $templateId
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function getTemplateInfoById( $templateId );

	/**
	 * Remove a Gravity PDF Filter
	 *
	 * @param string $filterName The WordPress Filter to remove
	 * @param string $className  The MVC Class the filter was assigned to
	 * @param string $methodName The MVC Method the filter ran
	 * @param int    $priority   The filter priority
	 *
	 * @since 0.1
	 */
	public function removeFilter( $filterName, $className, $methodName, $priority = 10 );
}
