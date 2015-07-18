<?php
/**
 * Gravity Tooltips - Helper Module
 *
 * Contains some basic helper functions
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

if ( ! class_exists( 'GF_Tooltips_Helper' ) ) {

// Start up the engine
class GF_Tooltips_Helper
{

	/**
	 * get our data set, with optional key
	 *
	 * @param  string $key     [description]
	 * @param  string $default [description]
	 * @return [type]          [description]
	 */
	public static function get_tooltip_data( $key = '', $default = '' ) {

		// get our data
		$data   = get_option( 'gf-tooltips' );

		// bail without data and no default
		if ( empty( $data ) && empty( $default ) ) {
			return false;
		}

		// return the whole dataset if requested
		if ( empty( $key ) ) {
			return $data;
		}

		// check settings and return choice or default
		return ! empty( $data[ $key ] ) ? $data[ $key ] : $default;
	}

	/**
	 * do a string replace on the first instance only
	 *
	 * @param  string  $search  [description]
	 * @param  string  $replace [description]
	 * @param  string  $string  [description]
	 * @param  integer $limit   [description]
	 *
	 * @return [type]           [description]
	 */
	public static function str_replace_limit( $search = '', $replace = '', $string = '', $limit = 1 ) {

		// bail if we don't have what we are looking for
		if ( is_bool( $pos = ( strpos( $string, $search ) ) ) ) {
			return $string;
		}

		// get our length
		$length = strlen( $search );

		// start the loop
		for ( $i = 0; $i < $limit; $i++ ) {

			// do the replace
			$string = substr_replace( $string, $replace, $pos, $length );

			// if we did it, break
			if ( is_bool( $pos = ( strpos( $string, $search ) ) ) ) {
				break;
			}
		}

		// return the string
		return $string;
	}

// end class
}

// end exists check
}


// Instantiate our class
new GF_Tooltips_Helper();
