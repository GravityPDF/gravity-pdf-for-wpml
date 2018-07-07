<?php

/**
 * Plugin Name:     Gravity PDF for WPML
 * Plugin URI:
 * Description:
 * Author:          Gravity PDF
 * Author URI:      https://gravitypdf.com
 * Text Domain:     gravity-pdf-for-wpml
 * Domain Path:     /languages
 * Version:         1.0.0
 */

/**
 * Check the dependancies are met, then load plugin
 *
 * @package     Gravity PDF for WPML
 * @copyright   Copyright (c) 2018, Blue Liquid Designs
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License
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

define( 'GFPDF_PDF_WPML_FILE', __FILE__ );
define( 'GFPDF_PDF_WPML_VERSION', '1.0.0' );

/**
 * Class GpdfWpmlChecks
 */
class GpdfWpmlChecks {

	/**
	 * Holds any blocker error messages stopping plugin running
	 *
	 * @var array
	 * @since 0.1
	 */
	private $notices = [];

	/**
	 * @var string
	 * @since 0.1
	 */
	private $required_gravitypdf_version = '4.4.0';

	/**
	 * @var string
	 * @since 0.1
	 */
	private $required_wpml_version = '4.0.3';

	/**
	 * @var string
	 * @since 0.1
	 */
	private $required_wpml_gravityforms_version = '1.3.17';

	/**
	 * Run our pre-checks and if it passes bootstrap the plugin
	 *
	 * @since 0.1
	 */
	public function init() {

		/* Test the minimum version requirements are met */
		$this->check_gravitypdf_version();
		$this->check_wpml_version();
		$this->check_wpml_gravityforms_version();

		/* Check if any errors were thrown, enqueue them and exit early */
		if ( count( $this->notices ) > 0 ) {
			add_action( 'admin_notices', [ $this, 'display_notices' ] );

			return;
		}

		add_action(
			'gfpdf_fully_loaded', function() {
				require_once __DIR__ . '/src/bootstrap.php';
			}
		);
	}

	/**
	 * Check if the current version of Gravity PDF is compatible with this add-on
	 *
	 * @since 0.1
	 */
	public function check_gravitypdf_version() {

		/* Check if the Gravity PDF Minimum version requirements are met */
		if (
			defined( 'PDF_EXTENDED_VERSION' ) &&
			version_compare( PDF_EXTENDED_VERSION, $this->required_gravitypdf_version, '>=' )
		) {
			return;
		}

		/* Throw error */
		$this->notices[] = sprintf( esc_html__( 'Gravity PDF Version %s or higher is required to use this add-on. Please install/upgrade Gravity PDF to the latest version.', 'gravity-pdf-for-wpml' ), $this->required_gravitypdf_version );
	}

	/**
	 * Check if the current version of WPML is compatible with this add-on
	 *
	 * @since 0.1
	 */
	public function check_wpml_version() {

		/* Check if the WPML minimum version requirements are met */
		if (
			defined( 'ICL_SITEPRESS_VERSION' ) &&
			version_compare( ICL_SITEPRESS_VERSION, $this->required_wpml_version, '>=' )
		) {
			return;
		}

		/* Throw error */
		$this->notices[] = sprintf( esc_html__( 'WPML Multilingual CMS Version %s or higher is required to use this add-on. Please install/upgrade WPML Multilingual CMS to the latest version.', 'gravity-pdf-for-wpml' ), $this->required_wpml_version );
	}

	/**
	 * Check if the current version of WPML's Gravity Forms Multilingual is compatible with this add-on
	 *
	 * @since 0.1
	 */
	public function check_wpml_gravityforms_version() {

		/* Check if the Gravity PDF Minimum version requirements are met */
		if (
			defined( 'GRAVITYFORMS_MULTILINGUAL_VERSION' ) &&
			version_compare( GRAVITYFORMS_MULTILINGUAL_VERSION, $this->required_wpml_gravityforms_version, '>=' )
		) {
			return;
		}

		/* Throw error */
		$this->notices[] = sprintf( esc_html__( 'Gravity Forms Multilingual Version %s or higher is required to use this add-on. Please install/upgrade WPML\'s Gravity Forms Multilingual plugin to the latest version.', 'gravity-pdf-for-wpml' ), $this->required_wpml_gravityforms_version );
	}

	/**
	 * Helper function to easily display error messages
	 *
	 * @return void
	 *
	 * @since 0.1
	 */
	public function display_notices() {
		?>
		<div class="error">
			<p>
				<strong><?php esc_html_e( 'Gravity PDF for WPML Installation Problem', 'gravity-pdf-for-wpml' ); ?></strong>
			</p>

			<p><?php esc_html_e( 'The minimum requirements for the Gravity PDF for WPML plugin have not been met. Please fix the issue(s) below to continue:', 'gravity-pdf-for-wpml' ); ?></p>
			<ul style="padding-bottom: 0.5em">
				<?php foreach ( $this->notices as $notice ): ?>
					<li style="padding-left: 20px;list-style: inside"><?php echo $notice; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}

/* Initialise the software */
add_action(
	'plugins_loaded', function() {
		$gravitypdf_wpml = new GpdfWpmlChecks();
		$gravitypdf_wpml->init();
	}
);
