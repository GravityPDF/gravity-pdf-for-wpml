<?php

namespace GFPDF\Plugins\WPML\Options;

use GFPDF\Plugins\WPML\Pdf\PdfInterface;

/**
 * Handles the plugins global PDF Settings
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
 * Class Settings
 *
 * @package GFPDF\Plugins\WPML\Options
 */
class Settings {

	/**
	 * @var PdfInterface
	 *
	 * @since 0.1
	 */
	protected $pdf;

	/**
	 * Settings constructor.
	 *
	 * @param PdfInterface $pdf
	 *
	 * @since 0.1
	 */
	public function __construct( PdfInterface $pdf ) {
		$this->pdf = $pdf;
	}

	/**
	 * @since 0.1
	 */
	public function init() {
		$this->add_filters();
	}

	/**
	 * @since 0.1
	 */
	public function add_filters() {
		add_filter( 'gfpdf_settings_extensions', [ $this, 'add_global_settings' ] );
		add_filter( 'gfpdf_form_settings_custom_appearance', [ $this, 'add_pdf_settings' ], 200 );
	}

	/**
	 * Add PDF setting to Template tab
	 *
	 * @param array $settings
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function add_pdf_settings( $settings ) {

		$template = isset( $_POST['template'] ) ? $_POST['template'] : $this->get_template_name_from_current_page();
		$pdf      = $this->pdf->get_template_info_by_id( $template );

		if ( $this->pdf->is_template_wpml_compatible( $pdf ) ) {
			$translation_field = [
				'id'      => 'wpml_enable_translation',
				'name'    => esc_html__( 'WPML', 'gravity-forms-pdf-extended' ),
				'type'    => 'checkbox',
				'desc'    => esc_html__( 'Enable automatic PDF translation with WPML?', 'gravity-forms-pdf-extended' ),
				'tooltip' => '<h6>' . esc_html__( 'WPML PDF Translation', 'gravity-pdf-for-wpml' ) . '</h6>' . esc_html__( 'When enabled, the PDF will be automatically translated when viewed, sent via email, or generated via the API.', 'gravity-pdf-for-wpml' ),
			];

			return $this->array_unshift_assoc( $settings, 'wpml_enable_translation', $translation_field );
		}

		return $settings;
	}

	/**
	 * Add global extension settings
	 *
	 * @param array $settings
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function add_global_settings( $settings ) {
		$wpml_settings = [
			'wpml_desc' => [
				'id'    => 'wpml_desc',
				'type'  => 'descriptive_text',
				'desc'  =>
					'<h4 class="section-title">' . esc_html__( 'Gravity PDF for WPML', 'gravity-pdf-for-wpml' ) . '</h4><p>' .
					sprintf(
						esc_html__( 'PDFs will only be translated into the appropriate language if the PDF template is WPML-compatible and the form has been translated. By default, all free Core templates and paid %1$sUniversal templates%2$s are WPML-compatible.', 'gravity-pdf-for-wpml' ),
						'<a href="https://gravitypdf.com/store/#universal">',
						'</a>'
					) . '</p>',
				'class' => 'gfpdf-no-padding',
			],

			'wpml_admin_default_language' => [
				'id'      => 'wpml_admin_default_language',
				'name'    => esc_html__( 'Default PDF Admin Language', 'gravity-pdf-for-wpml' ),
				'type'    => 'radio',
				'options' => [
					'user-language'    => esc_html__( 'Current User', 'gravity-pdf-for-wpml' ),
					'default-language' => esc_html__( 'Site Default', 'gravity-pdf-for-wpml' ),
					'entry-language'   => esc_html__( 'Entry', 'gravity-pdf-for-wpml' ),
				],
				'std'     => 'user-language',
				'desc'    => esc_html__( 'Set the default language used when viewing PDFs in the admin area.', 'gravity-pdf-for-wpml' ),
				'tooltip' => '<h6>' . esc_html__( 'Default PDF Admin Language', 'gravity-pdf-for-wpml' ) . '</h6>' . sprintf( esc_html__( 'When the option %1$sCurrent User%2$s is selected, the PDF language will default to the active user\'s choice. When %1$sSite Default%2$s, the WPML primary language will be used. And if %1$sEntry%2$s, the entry submitter language will be used.', 'gravity-pdf-for-wpml' ), '<code>', '</code>' ),
			],
		];

		return array_merge( $settings, $wpml_settings );
	}

	/**
	 * Insert a value or key/value pair at the beginning of the array
	 *
	 * @param array  $array
	 * @param string $key
	 * @param mixed  $val
	 *
	 * @return array
	 */
	protected function array_unshift_assoc( &$array, $key, $val ) {
		$array         = array_reverse( $array, true );
		$array[ $key ] = $val;
		return array_reverse( $array, true );
	}

	/**
	 * @return string
	 * @since 0.1
	 */
	protected function get_template_name_from_current_page() {
		$model = \GPDFAPI::get_mvc_class( 'Model_Form_Settings' );
		return $model->get_template_name_from_current_page();
	}
}
