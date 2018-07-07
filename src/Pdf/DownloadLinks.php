<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Helper\Helper_Trait_Logger;
use GFPDF\Plugins\WPML\Exceptions\GpdfWpmlException;
use GFPDF\Plugins\WPML\Form\GravityFormsInterface;
use GFPDF\Plugins\WPML\Wpml\WpmlInterface;

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
 * Class DownloadLinks
 *
 * @package GFPDF\Plugins\WPML\Pdf
 */
class DownloadLinks {

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
	 * @var PdfInterface
	 *
	 * @since 0.1
	 */
	protected $pdf;

	/**
	 * Holds a cache of the active PDF list
	 *
	 * @var array
	 *
	 * @since    0.1
	 *
	 * @Internal This prevents us reading the template headers from the disk multiple times
	 */
	protected $pdf_list_cache = [];

	/**
	 * DownloadLinks constructor.
	 *
	 * @param WpmlInterface         $wpml
	 * @param GravityFormsInterface $gf
	 * @param PdfInterface          $pdf
	 *
	 * @since 0.1
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
		$this->add_actions();
		$this->add_filters();
	}

	/**
	 * @since 0.1
	 */
	public function add_actions() {
		add_action( 'gform_entry_info', [ $this, 'add_links_to_entry_details' ], 9, 2 );
	}

	/**
	 * @since 0.1
	 */
	public function add_filters() {
		add_filter( 'gfpdf_get_pdf_url', [ $this, 'get_pdf_url_for_language' ], 10, 3 );
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
	public function get_pdf_url_for_language( $url, $pid, $entry_id ) {
		/* @TODO include trailing slash fix in get_pdf_url() */

		try {
			$entry = $this->gf->get_entry( $entry_id );
			$pdf   = $this->pdf->get_pdf( $entry['form_id'], $pid );
		} catch ( GpdfWpmlException $e ) {
			$this->logger->error( $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			] );

			return $url;
		}

		/* Get the PDF Group */
		$template_info = $this->pdf->get_template_info_by_id( $pdf['template'] );
		if ( isset( $template_info['group'] ) ) {
			$pdf['group'] = $template_info['group'];
		}

		/* Translate the URL if the template is WPML-compatible */
		if ( $this->is_template_wpml_compatible( $pdf ) ) {
			return $this->wpml->get_translated_url( $url, $this->gf->get_entry_language_code( $entry['id'] ) );
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
	public function add_links_to_entry_details( $form_id, $entry ) {
		$language_code = $this->gf->get_entry_language_code( $entry['id'] );
		if ( ! $this->wpml->has_site_language( $language_code ) ) {
			return;
		}

		try {
			$form = $this->gf->get_form( $form_id );
		} catch ( GpdfWpmlException $e ) {
			$this->logger->error( $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			] );

			return;
		}

		$pdf_list = $this->get_pdf_urls(
			$this->get_pdf_list( $form, $entry ),
			$form,
			$language_code
		);

		include __DIR__ . '/markup/PdfEntryDetailsLinks.php';

		$this->pdf->remove_filter( 'gform_entry_info', 'Model_PDF', 'view_pdf_entry_detail' );
	}

	/**
	 * Get the list of available PDFs and their associated translation URLs
	 *
	 * @param array  $pdf_list      The list of Gravity PDF URLs
	 * @param array  $form          The Gravity Forms form object
	 * @param string $language_code The
	 *
	 * @return array
	 */
	protected function get_pdf_urls( $pdf_list, $form, $language_code ) {
		$languages  = ! is_wp_error( $form ) ? $this->wpml->get_gravityform_languages( $form ) : $this->wpml->get_site_languages();
		$pdf_action = $this->get_pdf_default_action();

		/* Remove the current language if it exists (handled via main links) */
		if ( isset( $languages[ $language_code ] ) ) {
			unset( $languages[ $language_code ] );
		}

		foreach ( $pdf_list as &$pdf ) {
			$pdf['languages'] = [];

			if ( ! $this->is_template_wpml_compatible( $pdf ) ) {
				continue;
			}

			foreach ( $languages as $lang ) {
				$url = $this->wpml->get_translated_url( $pdf[ $pdf_action ], $lang['code'] );

				/* Only show if the URL has been successfully translated */
				if ( $url !== $pdf[ $pdf_action ] ) {
					$pdf['languages'][] = sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_attr( $url ),
						esc_html( $lang['code'] )
					);
				}
			}
		}

		return $pdf_list;
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
	protected function get_pdf_list( $form, $entry ) {
		$cache_id = 'pdf-list-' . $form['id'] . '-' . $entry['id'];
		if ( isset( $this->pdf_list_cache[ $cache_id ] ) ) {
			$this->logger->notice( sprintf( 'Retrieving PDF list from cache "%s"', $cache_id ) );
			return $this->pdf_list_cache[ $cache_id ];
		}

		$pdf_list = [];

		try {
			$active_pdfs = $this->pdf->get_active_pdfs( $entry['id'] );
		} catch ( GpdfWpmlException $e ) {
			$this->logger->error( $e->getMessage(), [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			] );

			return $pdf_list;
		}

		if ( ! empty( $active_pdfs ) ) {
			foreach ( $active_pdfs as $settings ) {
				$template_info = $this->pdf->get_template_info_by_id( $settings['template'] );

				/* Add additional information about the PDFs to this array for use with the `gfpdf_wpml_group_support` filter */
				try {
					$pdf_list[] = [
						'pid'      => $settings['id'],
						'template' => $settings['template'],
						'name'     => $this->pdf->get_pdf_name( $entry['id'], $settings['id'] ),
						'view'     => $this->pdf->get_pdf_url( $entry['id'], $settings['id'], false ),
						'download' => $this->pdf->get_pdf_url( $entry['id'], $settings['id'], true ),
						'group'    => $template_info['group'],
						'wpml'     => $template_info['wpml'],
					];
				} catch ( GpdfWpmlException $e ) {
					$this->logger->error( $e->getMessage(), [
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					] );
				}
			}
		}

		$pdf_list = apply_filters( 'gfpdf_get_pdf_display_list', $pdf_list, $entry, $form );

		/* Store in cache */
		$this->pdf_list_cache[ $cache_id ] = $pdf_list;

		return $pdf_list;
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
	protected function is_template_wpml_compatible( $pdf ) {
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

	/**
	 * Get the PDF URL Default Action
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	protected function get_pdf_default_action() {
		return strtolower( $this->pdf->get_option( 'default_action', 'view' ) );
	}
}
