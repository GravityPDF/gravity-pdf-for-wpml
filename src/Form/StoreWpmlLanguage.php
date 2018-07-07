<?php

namespace GFPDF\Plugins\WPML\Form;

use GFPDF\Helper\Helper_Trait_Logger;
use GFPDF\Plugins\WPML\Wpml\WpmlInterface;

/**
 * Save the WPML Language Code with Gravity Forms Entry
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
 * Class StoreWpmlLanguage
 *
 * @package GFPDF\Plugins\WPML\Form
 */
class StoreWpmlLanguage {

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
	 * StoreWpmlLanguage constructor.
	 *
	 * @param WpmlInterface         $wpml
	 * @param GravityFormsInterface $gf
	 *
	 * @since 0.1
	 */
	public function __construct( WpmlInterface $wpml, GravityFormsInterface $gf ) {
		$this->wpml = $wpml;
		$this->gf   = $gf;
	}

	/**
	 * @since 0.1
	 */
	public function init() {
		$this->add_actions();
	}

	/**
	 * @since 0.1
	 */
	public function add_actions() {
		add_action( 'gform_entry_created', [ $this, 'save_language_code' ], 10, 1 );
	}

	/**
	 * Store the current user language with the Gravity Form entry
	 *
	 * @param array $entry
	 *
	 * @since 0.1
	 */
	public function save_language_code( $entry ) {
		$language_code = $this->wpml->get_current_site_language();
		$this->gf->save_entry_language_code( $entry['id'], $language_code );
		$this->logger->notice( sprintf( 'WPML language "%1$s" saved to Gravity Forms Entry "%2$s"', $entry['id'], $language_code ) );
	}
}
