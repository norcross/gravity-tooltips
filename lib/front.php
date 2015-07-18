<?php
/**
 * Gravity Tooltips - Front Module
 *
 * Contains front end related functions
 *
 * @package Gravity Forms Tooltips
 */
/*  Copyright 2013 Reaktiv Studios

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License (GPL v2) only.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'GF_Tooltips_Front' ) ) {

// Start up the engine
class GF_Tooltips_Front
{

	/**
	 * fire it up
	 *
	 */
	public function init() {

		// bail on admin
		if ( is_admin() ) {
			return;
		}

		add_action( 'gform_enqueue_scripts',                array( $this, 'scripts_styles'              ),  10, 2   );
		add_action( 'gform_field_css_class',                array( $this, 'set_tooltip_class'           ),  10, 3   );
		add_filter( 'gform_field_content',                  array( $this, 'set_tooltip_display'         ),  10, 5   );
	}

	/**
	 * set a CSS class on the item
	 *
	 * @param [type] $classes [description]
	 * @param [type] $field   [description]
	 * @param [type] $form    [description]
	 *
	 * @return [type]          [description]
	 */
	public function set_tooltip_class( $classes, $field, $form ){

		// bail if no tooltip actually exists
		if ( empty( $field['customTooltip'] ) ) {
			return $classes;
		}

		// get our setting and add our class
		if ( false !== $type = GF_Tooltips_Helper::get_tooltip_data( 'type', 'icon' ) ) {
			$classes .= ' gf-tooltip gf-tooltip-' . esc_attr( $type );
		}

		// return the classes
		return $classes;
	}

	/**
	 * set up the tooltip display
	 *
	 * @param [type] $content [description]
	 * @param [type] $field   [description]
	 * @param [type] $value   [description]
	 * @param [type] $lead_id [description]
	 * @param [type] $form_id [description]
	 *
	 * @return [type]          [description]
	 */
	public function set_tooltip_display( $content, $field, $value, $lead_id, $form_id ) {

		// this is only for the front end
		if ( is_admin() ) {
			return $content;
		}

		// bail if no tooltip actually exists
		if ( empty( $field['customTooltip'] ) ) {
			return $content;
		}

		// grab our tooltip type first (and bail without)
		if ( false === $type = GF_Tooltips_Helper::get_tooltip_data( 'type', 'icon' ) ) {
			return $content;
		}

		// filter the text
		if ( false === $text = apply_filters( 'gf_tooltips_text', $field['customTooltip'], $field, $form_id ) ) {
			return $content;
		}

		// render and return
		return self::render_tooltip_markup( $field['customTooltip'], $type, $content, $field, $form_id );
	}

	/**
	 * filter the markup based on the type
	 *
	 * @param  string $text    [description]
	 * @param  string $type    [description]
	 * @param  string $render  [description]
	 *
	 * @return [type]          [description]
	 */
	public static function render_tooltip_markup( $text = '', $type = '', $render = '', $field, $form_id ) {

		// grab our tooltip design and target
		$design = GF_Tooltips_Helper::get_tooltip_data( 'design', 'light' );
		$target = GF_Tooltips_Helper::get_tooltip_data( 'target', 'right' );

		// set a class
		$class  = self::get_tooltip_class( $design, $target, $type );

		// build out label version
		if ( $type == 'label' ) {

			// first add the class
			$render = GF_Tooltips_Helper::str_replace_limit( 'gfield_label', 'gfield_label ' . $class, $render );

			// now add the tooltip
			$render = GF_Tooltips_Helper::str_replace_limit( '<label', '<label data-hint="' . esc_attr( $text ) . '"', $render );
		}

		// build out icon version
		if ( $type == 'icon' ) {

			// get my icon
			$icon   = self::get_tooltip_icon();

			// build the markup
			$setup  = '<span class="gf-icon ' . $class . '" data-hint="' . esc_attr( $text ) . '">' . $icon . '</span>';

			// render it
			$render = GF_Tooltips_Helper::str_replace_limit( '</label>', $setup . '</label>', $render );
		}

		// build out single version
		if ( $type == 'single' ) {

			// get my icon
			$icon   = self::get_tooltip_icon();

			// build the markup
			$setup  = '<span class="gf-icon ' . $class . '" data-hint="' . esc_attr( $text ) . '">' . $icon . '</span>';

			// render it
			$render = GF_Tooltips_Helper::str_replace_limit( '</div>', '</div>' . $setup, $render );
		}

		// return field content with new tooltip
		return apply_filters( 'gf_tooltips_filter_display', $render, $type, $field, $form_id );
	}

	/**
	 * load our CSS files
	 *
	 * @return [type] [description]
	 */
	public function scripts_styles( $form, $is_ajax ) {

		// make sure we want fontawesome
		if ( false !== $fontawesome = apply_filters( 'gf_tooltips_use_fontawesome', true ) ) {
			wp_enqueue_style( 'fontawesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array(), '4.3.0', 'all' );
		}

		// set our filename based on debug
		$file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'hint.css' : 'hint.min.css';

		// and the actual hint class
		wp_enqueue_style( 'gf-tooltips', plugins_url( '/css/' . $file, __FILE__ ), array(), '1.3.5', 'all' );
	}

	/**
	 * generate and return the CSS class
	 *
	 * @param  string $design [description]
	 * @param  string $target [description]
	 * @param  string $type   [description]
	 *
	 * @return [type]         [description]
	 */
	public static function get_tooltip_class( $design = '', $target = '', $type = '' ) {

		// set an empty
		$class  = '';

		// set the base class
		$class .= 'hint--' . esc_attr( $design ) . ' hint--' . esc_attr( $target );

		// check for bounce
		if ( false !== $bounce = apply_filters( 'gf_tooltips_bounce', true ) ) {
			$class .= ' hint--bounce';
		}

		// check for rounded
		if ( false !== $rounded = apply_filters( 'gf_tooltips_rounded', true ) ) {
			$class .= ' hint--rounded';
		}

		// check for no animation (bounce can't also be used)
		if ( false !== $animate = apply_filters( 'gf_tooltips_no_animated', false ) ) {
			$class .= ' hint--no-animate';
		}

		// check for static
		if ( false !== $static = apply_filters( 'gf_tooltips_static', false ) ) {
			$class .= ' hint--always';
		}

		// return it filtered
		return apply_filters( 'gf_tooltips_class', $class, $design, $target, $type );
	}

	/**
	 * set the default with a filter and return
	 *
	 * @return [type] [description]
	 */
	public static function get_tooltip_icon() {

		// set the icon class
		$class  = apply_filters( 'gf_tooltips_icon_class', 'fa fa-question-circle' );

		// return it
		return apply_filters( 'gf_tooltips_icon', '<i class="' . esc_attr( $class ) . '"></i>' );
	}


// end class
}

// end exists check
}

// Instantiate our class
$GF_Tooltips_Front = new GF_Tooltips_Front();
$GF_Tooltips_Front->init();