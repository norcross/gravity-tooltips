<?php
/**
 * Plugin Name: Gravity Forms Tooltips
 * Plugin URI: https://github.com/norcross/gravity-tooltips
 * Description: Add custom tooltips in Gravity Forms.
 * Author: Andrew Norcross
 * Author URI: http://andrewnorcross.com/
 * Version: 2.0.3
 * Text Domain: gravity-tooltips
 * Requires WP: 4.0
 * Domain Path: languages
 * GitHub Plugin URI: https://github.com/norcross/gravity-tooltips
 */

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Andrew Norcross
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// Define our base if we haven't already.
if ( ! defined( 'GFT_BASE' ) ) {
	define( 'GFT_BASE', plugin_basename(__FILE__) );
}

// Define our version if we haven't already.
if ( ! defined( 'GFT_VER' ) ) {
	define( 'GFT_VER', '2.0.3' );
}

/**
 * Core class.
 *
 * Contains the loading functionality and sets our default options.
 */
class GF_Tooltips
{

	/**
	 * Static property to hold our singleton instance.
	 *
	 * @var instance
	 */
	static $instance = false;

	/**
	 * This is our constructor. there are many like it, but this one is mine.
	 *
	 * @return GF_Tooltips
	 */
	private function __construct() {
		add_action(	'plugins_loaded',						array( $this, 'textdomain'			)			);
		add_action(	'plugins_loaded',						array( $this, 'load_files'			)			);

		// Activation hook.
		register_activation_hook( __FILE__,                 array( $this, 'set_options'         )           );
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return $instance
	 */
	public static function getInstance() {

		// Check for an instance of the class before loading.
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		// Return the instance.
		return self::$instance;
	}

	/**
	 * Load our textdomain.
	 *
	 * @return string load_plugin_textdomain
	 */
	public function textdomain() {
		load_plugin_textdomain( 'gravity-tooltips', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Load our files.
	 *
	 * @return void
	 */
	public function load_files() {

		// Confirm we have our actual Gravity Forms class first.
		if ( ! class_exists( 'RGForms' ) ) {
			return;
		}

		// Load our admin setup.
		if ( is_admin() ) {
			require_once( 'lib/admin.php' );
		}

		// Load our front end setup.
		if ( ! is_admin() ) {
			require_once( 'lib/front.php' );
		}

		// Load our helper.
		require_once( 'lib/helper.php' );
	}

	/**
	 * Set our options on activation.
	 */
	public function set_options() {

		// Check for data first.
		$exist  = get_option( 'gf-tooltips' );

		// We have it. leave it alone.
		if ( ! empty( $exist ) ) {
			return;
		}

		// Set a data array.
		$data   = array(
			'type'      => 'icon',
			'icon'      => 'question',
			'design'    => 'light',
			'target'    => 'right',
		);

		// Filter them.
		apply_filters( 'gf_tooltips_default_settings', $data );

		// Add the option.
		update_option( 'gf-tooltips', $data, 'no' );
	}

	// End our class.
}

// Instantiate our class
$GF_Tooltips = GF_Tooltips::getInstance();

