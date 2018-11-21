<?php

namespace GFPDF\Plugins\WPML\Options;

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
 * Class GlobalSettings
 *
 * @package GFPDF\Plugins\WPML\Options
 */
class GlobalSettings {

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
			'wpml_desc'                   => [
				'id'    => 'wpml_desc',
				'type'  => 'descriptive_text',
				'desc'  =>
					'<h4 class="section-title">' . esc_html__( 'Gravity PDF for WPML', 'gravity-pdf-for-wpml' ) . '</h4><p>' .
					sprintf(
						esc_html__( 'PDFs will only be translated into the appropriate language if the PDF template is WPML-compatible AND the Gravity Form has been translated into that language. By default, all free Core templates and paid %1$sUniversal templates%3$s are WPML-compatible. %2$sDevelopers can read more about making templates compatible in the documentation%3$s.', 'gravity-pdf-for-wpml' ),
						'<a href="https://gravitypdf.com/template-shop/#universal">',
						'<a href="https://gravitypdf.com/documentation/v4/shop-plugin-gravity-pdf-for-wpml-add-on/#developer-template-compat">',
						'</a>'
					) . '</p>',
				'class' => 'gfpdf-no-padding',
			],

			'wpml_user_notification'      => [
				'id'      => 'wpml_user_notification',
				'name'    => esc_html__( 'Only Translate User Notification PDFs', 'gravity-pdf-for-wpml' ),
				'type'    => 'radio',
				'desc'    => esc_html__( 'When on, PDFs will only be translated for Notifications that are sent to a Gravity Forms Email field.', 'gravity-pdf-for-wpml' ),
				'options' => [
					'On'  => esc_html__( 'On', 'gravity-pdf-for-wpml' ),
					'Off' => esc_html__( 'Off', 'gravity-pdf-for-wpml' ),
				],
				'std'     => 'Off',
				'tooltip' => '<h6>' . esc_html__( 'Only Translate User Notification PDFs', 'gravity-pdf-for-wpml' ) . '</h6>' . sprintf( esc_html__( 'When on, only Notifications that have the %1$sSend To%2$s setting set to %1$sSelect a Field%2$s will have PDFs translated. When off, PDFs attached to all Notifications will be translated.', 'gravity-pdf-for-wpml' ), '<code>', '</code>' ),
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
				'tooltip' => '<h6>' . esc_html__( 'Default PDF Admin Language', 'gravity-pdf-for-wpml' ) . '</h6>' . sprintf( esc_html__( 'If %1$sCurrent User%2$s the PDF language will default to the active user\'s choice. If %1$sSite Default%2$s the WPML primary language will be used. If %1$sEntry%2$s the entry submitter language will be used.', 'gravity-pdf-for-wpml' ), '<code>', '</code>' ),
			],
		];

		return array_merge( $settings, $wpml_settings );
	}

}
