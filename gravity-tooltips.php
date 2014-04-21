<?php
/*
Plugin Name: Gravity Forms Tooltips
Plugin URI: http://andrewnorcross.com/plugins/gravity-tooltips/
Description: Convert the Gravity Forms description field into tooltips
Author: Andrew Norcross
Version: 1.0.1
Requires at least: 3.8
Author URI: http://andrewnorcross.com
*/
/*  Copyright 2014 Andrew Norcross

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


if( ! defined( 'GFT_BASE' ) ) {
	define( 'GFT_BASE', plugin_basename(__FILE__) );
}

if( ! defined( 'GFT_VER' ) ) {
	define( 'GFT_VER', '1.0.1' );
}

class GF_Tooltips
{

	/**
	 * This is our constructor
	 *
	 * @return GF_Tooltips
	 */
	public function __construct() {
		add_action			(	'plugins_loaded',						array(	$this,	'textdomain'			)			);
		add_action			(	'plugins_loaded',						array(	$this,	'load_files'			)			);
	}

	/**
	 * load textdomain
	 *
	 * @return string load_plugin_textdomain
	 */

	public function textdomain() {

		load_plugin_textdomain( 'gravity-tooltips', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * [load_files description]
	 * @return [type] [description]
	 */
	public function load_files() {

		require_once( 'lib/admin.php' );
		require_once( 'lib/front.php' );

	}

	/**
	 * [show_field_item_types description]
	 * @return [type] [description]
	 */
	static function show_field_item_types() {

		$defaults	= array(
			'text',
			'creditcard',
			'website',
			'phone',
			'number',
			'date',
			'time',
			'textarea',
			'select',
			'multiselect',
			'checkbox',
			'radio',
			'name',
			'address',
			'fileupload',
			'email',
			'post_title',
			'post_content',
			'post_excerpt',
			'post_tags',
			'post_category',
			'post_image',
			'captcha',
			'product',
			'singleproduct',
			'calculation',
			'price',
			'hiddenproduct',
			'list',
			'shipping',
			'singleshipping',
			'option',
			'quantity',
			'donation',
			'total',
			'post_custom_field',
			'password'
		);

		$defaults	= apply_filters( 'gf_tooltips_allowed_fields', $defaults );

		return $defaults;

	}

	/**
	 * [get_qtip_placement description]
	 * @param  [type] $current [description]
	 * @return [type]         [description]
	 */
	static function get_qtip_placement( $current ) {

		$options	= array(
			'topLeft'		=> __( 'Top Left', 'gravity-tooltips' ),
			'topMiddle'		=> __( 'Top Middle', 'gravity-tooltips' ),
			'topRight'		=> __( 'Top Right', 'gravity-tooltips' ),
			'rightTop'		=> __( 'Right Top', 'gravity-tooltips' ),
			'rightMiddle'	=> __( 'Right Middle', 'gravity-tooltips' ),
			'rightBottom'	=> __( 'Right Bottom', 'gravity-tooltips' ),
			'bottomRight'	=> __( 'Bottom Right', 'gravity-tooltips' ),
			'bottomMiddle'	=> __( 'Bottom Middle', 'gravity-tooltips' ),
			'bottomLeft'	=> __( 'Bottom Left', 'gravity-tooltips' ),
			'leftBottom'	=> __( 'Left Bottom', 'gravity-tooltips' ),
			'leftMiddle'	=> __( 'Left Middle', 'gravity-tooltips' ),
			'leftTop'		=> __( 'Left Top', 'gravity-tooltips' )
		);

		$dropdown	= '';

		foreach ( $options as $key => $label ) :

			$dropdown	.= '<option value="' . $key . '"' . selected( $current, $key, false ) . '>' . $label . '</option>';

		endforeach;

		return $dropdown;

	}

	/**
	 * [get_qtip_designs description]
	 * @param  [type] $current [description]
	 * @return [type]         [description]
	 */
	static function get_qtip_designs( $current ) {

		$options	= array(
			'cream'		=> __( 'Cream', 'gravity-tooltips' ),
			'dark'		=> __( 'Dark', 'gravity-tooltips' ),
			'green'		=> __( 'Green', 'gravity-tooltips' ),
			'light'		=> __( 'Light', 'gravity-tooltips' ),
			'red'		=> __( 'Red', 'gravity-tooltips' ),
			'blue'		=> __( 'Blue', 'gravity-tooltips' )
		);

		$dropdown	= '';

		foreach ( $options as $key => $label ) :

			$dropdown	.= '<option value="' . $key . '"' . selected( $current, $key, false ) . '>' . $label . '</option>';

		endforeach;

		return $dropdown;

	}

	/**
	 * [get_tooltip_icon_img description]
	 * @return [type] [description]
	 */
	static function get_tooltip_icon_img() {

		// set the default with a filter
		$icon	= apply_filters( 'gf_tooltips_icon_img', plugins_url( 'lib/img/tooltip-icon.png', __FILE__) );

		// return without markup i.e. the URL of the icon
		return esc_url( $icon );

	}

	/**
	 * do a string replace on the first instance only
	 * @param  [type]  $search  [description]
	 * @param  [type]  $replace [description]
	 * @param  [type]  $string  [description]
	 * @param  integer $limit   [description]
	 * @return [type]           [description]
	 */
	static function str_replace_limit( $search, $replace, $string, $limit = 1 ) {

		if ( is_bool( $pos = ( strpos( $string, $search ) ) ) ) {
			return $string;
		}

		$length	= strlen( $search );

		for ( $i = 0; $i < $limit; $i++ ) {

			$string = substr_replace( $string, $replace, $pos, $length );

			if ( is_bool( $pos = ( strpos( $string, $search ) ) ) ) {
				break;
			}
		}

		return $string;
	}


/// end class
}

new GF_Tooltips();
