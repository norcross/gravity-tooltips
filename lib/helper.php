<?php
/**
 * Gravity Tooltips - Helper Module
 *
 * Contains some basic helper functions
 *
 * @package Gravity Forms Tooltips
 */

// Confirm we haven't already loaded the class.
if ( ! class_exists( 'GF_Tooltips_Helper' ) ) {

/**
 * Declare the helper class.
 */
class GF_Tooltips_Helper
{

	/**
	 * Get our data set, with optional key.
	 *
	 * @param  string $key      The key within the serialized array.
	 * @param  string $default  Optional default value to return.
	 *
	 * @return mxied  $data     The requested data, or default, or false.
	 */
	public static function get_tooltip_data( $key = '', $default = '' ) {

		// Get our data.
		$data   = get_option( 'gf-tooltips' );

		// Bail without data and no default.
		if ( empty( $data ) && empty( $default ) ) {
			return false;
		}

		// Return the whole dataset if requested.
		if ( empty( $key ) ) {
			return $data;
		}

		// Check settings and return choice or default.
		return ! empty( $data[ $key ] ) ? $data[ $key ] : $default;
	}

	/**
	 * Do a string replace on the first instance only.
	 *
	 * @param  string  $search   The string part we are looking for.
	 * @param  string  $replace  The value we want to replace it with.
	 * @param  string  $string   The entire string we are working on.
	 * @param  integer $limit    How many times to do the search.
	 *
	 * @return string  $string   The potentially modified string we are working on.
	 */
	public static function str_replace_limit( $search = '', $replace = '', $string = '', $limit = 1 ) {

		// Bail if we don't have what we are looking for.
		if ( is_bool( $pos = ( strpos( $string, $search ) ) ) ) {
			return $string;
		}

		// Get our length.
		$length = strlen( $search );

		// Start the loop.
		for ( $i = 0; $i < $limit; $i++ ) {

			// Do the replace.
			$string = substr_replace( $string, $replace, $pos, $length );

			// If we did it, break.
			if ( is_bool( $pos = ( strpos( $string, $search ) ) ) ) {
				break;
			}
		}

		// Return the string.
		return $string;
	}

	// End our class.
}

// End exists check.
}


// Instantiate our class
new GF_Tooltips_Helper();
