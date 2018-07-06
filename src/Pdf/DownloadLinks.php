<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Plugins\WPML\Exceptions\GpdfWpmlException;
use GFPDF\Plugins\WPML\Form\GravityFormsInterface;
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
 * Class DownloadLinks
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
class DownloadLinks {

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
	 * @var PdfInterface
	 * @since 0.1
	 */
	protected $pdf;

	/**
	 * Holds a cache of the active PDF list
	 *
	 * @var array
	 * @since    0.1
	 *
	 * @Internal This prevents us reading the template headers from the disk multiple times
	 */
	protected $pdfListCache = [];

	/**
	 * DownloadLinks constructor.
	 *
	 * @param WpmlInterface         $wpml
	 * @param GravityFormsInterface $gf
	 * @param PdfInterface          $pdf
	 *
	 * @since 1.0
	 */
	public function __construct( WpmlInterface $wpml, GravityFormsInterface $gf, PdfInterface $pdf ) {
		$this->wpml = $wpml;
		$this->gf   = $gf;
		$this->pdf  = $pdf;
	}

	/**
	 * Initialise our module
	 *
	 * @since 0.1
	 */
	public function init() {
		$this->addActions();
		$this->addFilters();
	}

	/**
	 * @since 0.1
	 */
	public function addActions() {
		add_action( 'gform_entry_info', [ $this, 'addLinksToEntryDetails' ], 9, 2 );
	}

	/**
	 * @since 0.1
	 */
	public function addFilters() {
		add_filter( 'gfpdf_get_pdf_url', [ $this, 'getPdfUrlForLanguage' ], 10, 3 );
	}

	/**
	 * Get the translated PDF URL, if compatible PDF template
	 *
	 * @param string $url      The current PDF URL
	 * @param string $pid      The PDF ID
	 * @param int    $entry_id The Gravity Forms entry ID
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function getPdfUrlForLanguage( $url, $pid, $entry_id ) {
		/* @TODO include trailing slash fix in get_pdf_url() */

		try {
			$entry = $this->gf->getEntry( $entry_id );
			$pdf   = $this->pdf->getPdf( $entry['form_id'], $pid );
		} catch ( GpdfWpmlException $e ) {
			return $url;
		}

		/* Get the PDF Group */
		$templateInfo = $this->pdf->getTemplateInfoById( $pdf['template'] );
		if ( isset( $templateInfo['group'] ) ) {
			$pdf['group'] = $templateInfo['group'];
		}

		/* Translate the URL if the template is WPML-compatible */
		if ( $this->isTemplateWpmlCompatible( $pdf ) ) {
			return $this->wpml->getTranslatedUrl( $url, $this->gf->getEntryLanguageCode( $entry['id'] ) );
		}

		return $url;
	}

	/**
	 * Include available translated URLs for Core / Universal templates (with filter to add custom templates)
	 * in the Gravity Forms Entry List Page
	 *
	 * @param int   $form_id The Gravity Forms form ID
	 * @param array $entry   The Gravity Forms entry
	 *
	 * @since 0.1
	 */
	public function addLinksToEntryDetails( $form_id, $entry ) {
		$languageCode = $this->gf->getEntryLanguageCode( $entry['id'] );
		if ( ! $this->wpml->hasSiteLanguage( $languageCode ) ) {
			return;
		}

		try {
			$form = $this->gf->getForm( $form_id );
		} catch ( GpdfWpmlException $e ) {
			return;
		}

		$pdfList = $this->getPdfUrls(
			$this->getPdfList( $form, $entry ),
			$form,
			$languageCode
		);

		ob_start();
		include __DIR__ . '/markup/PdfEntryDetailsLinks.php';
		echo ob_get_clean();

		$this->pdf->removeFilter( 'gform_entry_info', 'Model_PDF', 'view_pdf_entry_detail' );
	}

	/**
	 * Get the list of available PDFs and their associated translation URLs
	 *
	 * @param array  $pdfList             The list of Gravity PDF URLs
	 * @param array  $form                The Gravity Forms form object
	 * @param string $currentLanguageCode The
	 *
	 * @return array
	 */
	protected function getPdfUrls( $pdfList, $form, $currentLanguageCode ) {
		$languages = ! is_wp_error( $form ) ? $this->wpml->getGravityFormLanguages( $form ) : $this->wpml->getSiteLanguages();
		$pdfAction = $this->getPdfDefaultAction();

		/* Remove the current language if it exists (handled via main links) */
		if ( isset( $languages[ $currentLanguageCode ] ) ) {
			unset( $languages[ $currentLanguageCode ] );
		}

		foreach ( $pdfList as &$pdf ) {
			$pdf['languages'] = [];

			if ( ! $this->isTemplateWpmlCompatible( $pdf ) ) {
				continue;
			}

			foreach ( $languages as $lang ) {
				$url = $this->wpml->getTranslatedUrl( $pdf[ $pdfAction ], $lang['code'] );

				/* Only show if the URL has been successfully translated */
				if ( $url !== $pdf[ $pdfAction ] ) {
					$pdf['languages'][] = sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_attr( $url ),
						esc_html( $lang['code'] )
					);
				}
			}
		}

		return $pdfList;
	}

	/**
	 * Get a list of active PDFs for an entry
	 *
	 * @param array $form  The Gravity Forms object
	 * @param array $entry The Gravity Forms entry object
	 *
	 * @return array
	 *
	 * @since    0.1
	 *
	 * @Internal We're overriding the standard Gravity PDF function to overload the list with additional data
	 */
	protected function getPdfList( $form, $entry ) {
		$cache_id = 'pdf-list-' . $form['id'] . '-' . $entry['id'];
		if ( isset( $this->pdfListCache[ $cache_id ] ) ) {
			return $this->pdfListCache[ $cache_id ];
		}

		$pdfList = [];

		try {
			$activePdfs = $this->pdf->getActivePdfs( $entry['id'] );
		} catch ( GpdfWpmlException $e ) {
			return $pdfList;
		}

		if ( ! empty( $activePdfs ) ) {
			foreach ( $activePdfs as $settings ) {
				$templateInfo = $this->pdf->getTemplateInfoById( $settings['template'] );

				/* Add additional information about the PDFs to this array for use with the `gfpdf_wpml_group_support` filter */
				try {
					$pdfList[] = [
						'pid'      => $settings['id'],
						'template' => $settings['template'],
						'name'     => $this->pdf->getPdfName( $entry['id'], $settings['id'] ),
						'view'     => $this->pdf->getPdfUrl( $entry['id'], $settings['id'], false ),
						'download' => $this->pdf->getPdfUrl( $entry['id'], $settings['id'], true ),
						'group'    => $templateInfo['group'],
						'wpml'     => $templateInfo['wpml'],
					];
				} catch ( GpdfWpmlException $e ) {
					continue;
				}
			}
		}

		$pdfList = apply_filters( 'gfpdf_get_pdf_display_list', $pdfList, $entry, $form );

		/* Store in cache */
		$this->pdfListCache[ $cache_id ] = $pdfList;

		return $pdfList;
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
	protected function isTemplateWpmlCompatible( $pdf ) {

		/* Check if has the `@WPML: true` header */
		if ( isset( $pdf['wpml'] ) && $pdf['wpml'] == 'true' ) {
			return true;
		}

		/* Check if group is Core/Universal which has WPML support out of the box */
		if ( isset( $pdf['group'] ) ) {
			$supportedTemplateGroups = apply_filters( 'gfpdf_wpml_group_support', [ 'Core', 'Universal (Premium)' ], $pdf );
			if ( in_array( $pdf['group'], $supportedTemplateGroups ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the PDF URL Default Action
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	protected function getPdfDefaultAction() {
		return strtolower( $this->pdf->getOption( 'default_action', 'view' ) );
	}
}
