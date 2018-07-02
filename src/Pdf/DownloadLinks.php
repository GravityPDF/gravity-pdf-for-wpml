<?php

namespace GFPDF\Plugins\WPML\Pdf;

use GFPDF\Helper\Helper_Abstract_Form;
use GFPDF\Helper\Helper_Abstract_Options;
use GFPDF\Helper\Helper_Interface_Actions;
use GFPDF\Helper\Helper_Interface_Filters;
use GFPDF\Helper\Helper_Templates;
use GFPDF\Model\Model_PDF;

use GFPDF\Plugins\WPML\Form\GravityFormsInterface;
use GFPDF\Plugins\WPML\Wpml\Wpml;
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
class DownloadLinks implements Helper_Interface_Actions, Helper_Interface_Filters {

	/**
	 * @var Wpml
	 * @since 0.1
	 */
	protected $wpml;

	/**
	 * @var GravityFormsInterface
	 * @since 0.1
	 */
	protected $gf;

	/**
	 * @var Helper_Abstract_Form
	 * @since 0.1
	 */
	protected $gform;

	/**
	 * @var Helper_Abstract_Options
	 * @since 0.1
	 */
	protected $options;

	/**
	 * @var Helper_Templates
	 * @since 0.1
	 */
	protected $templates;

	/**
	 * @var Model_PDF
	 * @since 0.1
	 */
	protected $modelPdf;

	protected $pdfCache = [];

	/**
	 * DownloadLinks constructor.
	 *
	 * @param WpmlInterface           $wpml
	 * @param GravityFormsInterface   $gf
	 * @param Helper_Abstract_Form    $gform
	 * @param Helper_Abstract_Options $options
	 * @param Helper_Templates        $templates
	 * @param Model_PDF               $modelPdf
	 *
	 * @since 0.1
	 */
	public function __construct( WpmlInterface $wpml, GravityFormsInterface $gf, Helper_Abstract_Form $gform, Helper_Abstract_Options $options, Helper_Templates $templates, Model_PDF $modelPdf ) {
		$this->wpml      = $wpml;
		$this->gf        = $gf;
		$this->gform     = $gform;
		$this->options   = $options;
		$this->templates = $templates;
		$this->modelPdf  = $modelPdf;
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
		add_action( 'gform_entry_info', [ $this, 'addLinksToEntryDetails' ], 9, 2 );
	}

	/**
	 * @since 0.1
	 */
	public function add_filters() {
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
		$entry = $this->gform->get_entry( $entry_id );
		$pdf   = isset( $entry['form_id'] ) ? $this->options->get_pdf( $entry['form_id'], $pid ) : null;

		if ( is_wp_error( $pdf ) || $pdf === null ) {
			return $url;
		}

		/* Get the PDF Group */
		$templateInfo = $this->templates->get_template_info_by_id( $pdf['template'] );
		if ( isset( $templateInfo['group'] ) ) {
			$pdf['group'] = $templateInfo['group'];
		}

		/* Translate the URL if the template is WPML-compatible */
		if ( $this->isTemplateWpmlCompatible( $pdf ) ) {
			return $this->wpml->getTranslatedUrl( $url, $this->gf->getLanguageCode( $entry['id'] ) );
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
		$languageCode = $this->gf->getLanguageCode( $entry['id'] );
		if ( ! $this->wpml->hasSiteLanguage( $languageCode ) ) {
			return;
		}

		$form    = $this->gform->get_form( $form_id );
		$pdfList = $this->getPdfUrls(
			$this->getPdfList( $form, $entry ),
			$form,
			$languageCode
		);

		?>
        <strong><?php esc_html_e( 'PDFs', 'gravity-forms-pdf-extended' ); ?></strong><br />
		<?php foreach ( $pdfList as $pdf ): ?>
            <div class="gfpdf_detailed_pdf_container">
                <span>
                    <?php echo esc_html( $pdf['name'] ); ?>
	                <?php if ( count( $pdf['languages'] ) > 0 ): ?>
                        (<?= implode( ', ', $pdf['languages'] ); ?>)
	                <?php endif; ?>
                </span>

                <div>
                    <a href="<?php echo esc_url( $pdf['view'] ); ?>" target="_blank" class="button">
						<?php esc_html_e( 'View', 'gravity-forms-pdf-extended' ); ?>
                    </a>

                    <a href="<?php echo esc_url( $pdf['download'] ); ?>" class="button">
						<?php esc_html_e( 'Download', 'gravity-forms-pdf-extended' ); ?>
                    </a>
                </div>
            </div>
		<?php endforeach;

		/* Remove the standard  */
		remove_filter( 'gform_entry_info', [ $this->modelPdf, 'view_pdf_entry_detail' ] );
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
		$languages = ( ! is_wp_error( $form ) ) ? $this->wpml->getGravityFormLanguages( $form ) : $this->wpml->getSiteLanguages();
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
						'<a href="%s">%s</a>',
						esc_attr( $url ),
						esc_html( $lang['code'] )
					);
				}
			}
		}

		return $pdfList;
	}

	protected function isTemplateWpmlCompatible( $pdf ) {
		if ( isset( $pdf['wpml'] ) && $pdf['wpml'] == 'true' ) {
			return true;
		}

		if ( isset( $pdf['group'] ) ) {
			$supportedTemplateGroups = apply_filters( 'gfpdf_wpml_group_support', [ 'Core', 'Universal (Premium)' ], $pdf );
			if ( in_array( $pdf['group'], $supportedTemplateGroups ) ) {
				return true;
			}
		}

		return false;
	}

	protected function getPdfList( $form, $entry ) {
		$cache_id = 'pdf-list-' . $form['id'] . '-' . $entry['id'];
		if ( isset( $this->pdfCache[ $cache_id ] ) ) {
			return $this->pdfCache[ $cache_id ];
		}

		$activePdfs = ( isset( $form['gfpdf_form_settings'] ) ) ? $this->modelPdf->get_active_pdfs( $form['gfpdf_form_settings'], $entry ) : [];
		$pdfList    = [];

		if ( ! empty( $activePdfs ) ) {
			foreach ( $activePdfs as $settings ) {
				$templateInfo = $this->templates->get_template_info_by_id( $settings['template'] );

				/* Add additional information about the PDFs to this array for use with the `gfpdf_wpml_group_support` filter */
				$pdfList[] = [
					'pid'      => $settings['id'],
					'template' => $settings['template'],
					'name'     => $this->modelPdf->get_pdf_name( $settings, $entry ),
					'view'     => $this->modelPdf->get_pdf_url( $settings['id'], $entry['id'], false ),
					'download' => $this->modelPdf->get_pdf_url( $settings['id'], $entry['id'], true ),
					'group'    => $templateInfo['group'],
					'wpml'     => $templateInfo['wpml'],
				];
			}
		}

		$pdfList = apply_filters( 'gfpdf_get_pdf_display_list', $pdfList, $entry, $form );

		/* Store in cache */
		$this->pdfCache[ $cache_id ] = $pdfList;

		return $pdfList;
	}

	protected function getPdfDefaultAction() {
		return strtolower( $this->options->get_option( 'default_action', 'view' ) );
	}
}