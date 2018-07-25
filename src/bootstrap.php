<?php

namespace GFPDF\Plugins\WPML;

use GFPDF\Helper\Licensing\EDD_SL_Plugin_Updater;
use GFPDF\Helper\Helper_Abstract_Addon;
use GFPDF\Helper\Helper_Singleton;
use GFPDF\Helper\Helper_Logger;
use GFPDF\Helper\Helper_Notices;

use GFPDF\Plugins\WPML\Form\EditWpmlLanguageCode;
use GFPDF\Plugins\WPML\Form\GravityForms;
use GFPDF\Plugins\WPML\Form\MergeTags;
use GFPDF\Plugins\WPML\Form\StoreWpmlLanguage;
use GFPDF\Plugins\WPML\Options\GlobalSettings;
use GFPDF\Plugins\WPML\Pdf\DownloadLinks;
use GFPDF\Plugins\WPML\Pdf\FormData;
use GFPDF\Plugins\WPML\Pdf\Header;
use GFPDF\Plugins\WPML\Pdf\Translation;
use GFPDF\Plugins\WPML\Pdf\Pdf;
use GFPDF\Plugins\WPML\Wpml\RegisterPdfTranslations;
use GFPDF\Plugins\WPML\Wpml\Wpml;
use GPDFAPI;

/**
 * Initialise the plugin
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

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class Bootstrap
 *
 * @package GFPDF\Plugins\WPML
 */
class Bootstrap extends Helper_Abstract_Addon {

	/**
	 * Initialise the plugin classes and pass them to our parent class to
	 * handle the rest of the bootstrapping (licensing ect)
	 *
	 * @param array $classes An array of classes to store in our singleton
	 *
	 * @since 0.1
	 */
	public function init( $classes = [] ) {

		/* Register our classes and pass back up to the parent initialiser */
		$wpml = new Wpml();
		$gf   = new GravityForms();
		$pdf  = new Pdf( $gf );

		$classes = array_merge(
			$classes, [
				new Header(),
				new DownloadLinks( $wpml, $gf, $pdf ),
				new Translation( $wpml, $gf, $pdf ),
				new StoreWpmlLanguage( $wpml, $gf ),
				new EditWpmlLanguageCode( $wpml, $gf ),
				new FormData( $gf ),
				new GlobalSettings(),
				new MergeTags( $wpml, $gf ),
				new RegisterPdfTranslations(),
			]
		);

		$this->add_filters();

		/* Run the setup */
		parent::init( $classes );
	}

	/**
	 * @since 0.1
	 */
	public function add_filters() {
		add_filter( 'gfpdf_settings_licenses', [ $this, 'fix_plugin_short_name' ], 20 );
	}

	/**
	 * Fix the plugin short name
	 *
	 * @param array $fields
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function fix_plugin_short_name( $fields ) {
		foreach ( $fields as &$field ) {
			if ( $field['id'] === 'license_' . $this->get_slug() ) {
				$field['name'] = $this->get_name();
			}
		}

		return $fields;
	}

	/**
	 * Check the plugin's license is active and initialise the EDD Updater
	 *
	 * @since 0.1
	 */
	public function plugin_updater() {

		/* Skip over this addon if license status isn't active */
		$license_info = $this->get_license_info();

		new EDD_SL_Plugin_Updater(
			$this->data->store_url,
			$this->get_main_plugin_file(),
			[
				'version'   => $this->get_version(),
				'license'   => $license_info['license'],
				'item_name' => $this->get_short_name(),
				'author'    => $this->get_author(),
				'beta'      => false,
			]
		);
	}
}

/* Use the filter below to replace and extend our Bootstrap class if needed */
$name = 'Gravity PDF for WPML';
$slug = 'gravity-pdf-for-wpml';

$plugin = apply_filters(
	'gfpdf_wpml_initialise', new Bootstrap(
		$slug,
		$name,
		'Gravity PDF',
		GFPDF_PDF_WPML_VERSION,
		GFPDF_PDF_WPML_FILE,
		GPDFAPI::get_data_class(),
		GPDFAPI::get_options_class(),
		new Helper_Singleton(),
		new Helper_Logger( $slug, $name ),
		new Helper_Notices()
	)
);

$plugin->set_edd_download_id( '23026' );
$plugin->set_addon_documentation_slug( 'shop-plugin-gravity-pdf-for-wpml-add-on' );
$plugin->init();

/* Use the action below to access our Bootstrap class, and any singletons saved in $plugin->singleton */
do_action( 'gfpdf_wpml_bootrapped', $plugin );
