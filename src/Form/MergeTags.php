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
 * Class MergeTags
 *
 * @package GFPDF\Plugins\WPML\Form
 */
class MergeTags {

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
		$this->add_filters();
	}

	/**
	 * @since 0.1
	 */
	public function add_filters() {
		add_filter( 'gform_custom_merge_tags', [ $this, 'add' ], 10 );
		add_filter( 'gform_replace_merge_tags', [ $this, 'process' ], 10, 3 );
	}

	/**
	 * Add custom merge tags that include the WPML Language Code / Language
	 *
	 * @param array $tags
	 *
	 * @return array
	 *
	 * @since 0.1
	 */
	public function add( $tags ) {
		$tags [] = [
			'tag'   => '{wpml:current_language_code}',
			'label' => esc_html__( 'Current Language Code', 'gravity-pdf-for-wpml' ),
		];

		return $tags;
	}

	/**
	 * Translate the WPML Merge Tags to their appropriate value
	 *
	 * @param string $text  The text to translate
	 * @param array  $form  The Gravity Forms object
	 * @param array  $entry The Gravity Forms Entry object
	 *
	 * @return string
	 *
	 * @since 0.1
	 */
	public function process( $text, $form, $entry ) {

		/* Exit early if no applicable tags found */
		if ( strpos( $text, '{wpml:' ) === false ) {
			return $text;
		}

		$text = str_replace( '{wpml:current_language_code}', $this->wpml->get_current_site_language(), $text );
		$text = str_replace( '{wpml:entry_language_code}', $this->gf->get_entry_language_code( $entry['id'] ), $text );

		return $text;
	}

}
