<?php

namespace GFPDF\Plugins\WPML\Form;

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
 * Class StoreWpmlLanguage
 *
 * @package GFPDF\Plugins\WPML\Form
 */
class StoreWpmlLanguage {

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
		$this->addActions();
	}

	/**
	 * @since 0.1
	 */
	public function addActions() {
		add_action( 'gform_entry_created', [ $this, 'saveLanguageCode' ], 10, 1 );
	}

	/**
	 * Store the current user language with the Gravity Form entry
	 *
	 * @param array $entry
	 *
	 * @since 0.1
	 */
	public function saveLanguageCode( $entry ) {
		$this->gf->saveEntryLanguageCode( $entry['id'], $this->wpml->getCurrentSiteLanguage() );
	}
}
