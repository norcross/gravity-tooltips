<?php
/*
Plugin Name: Gravity Forms Tooltips
Plugin URI: http://andrewnorcross.com/plugins/gravity-tooltips/
Description: Add custom tooltips in Gravity Forms.
Author: Andrew Norcross
Version: 2.0.0
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
	define( 'GFT_VER', '2.0.0' );
}

class GF_Tooltips
{

	/**
	 * Static property to hold our singleton instance
	 * @var instance
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return GF_Tooltips
	 */
	private function __construct() {
		add_action(	'plugins_loaded',						array( $this, 'textdomain'			)			);
		add_action(	'plugins_loaded',						array( $this, 'load_files'			)			);

		// activation hooks
		register_activation_hook( __FILE__,                 array( $this, 'set_options'         )           );
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return
	 */
	public static function getInstance() {

		// check for an instance of the class before loading
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		// return the instance
		return self::$instance;
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
	 * load our files
	 *
	 * @return [type] [description]
	 */
	public function load_files() {

		// load our admin setup
		if ( is_admin() ) {
			require_once( 'lib/admin.php' );
		}

		// load our front end setup
		if ( ! is_admin() ) {
			require_once( 'lib/front.php' );
		}

		// load our helper
		require_once( 'lib/helper.php' );
	}

	/**
	 * set our options if
	 */
	public function set_options() {

		// check for data first
		$exist  = get_option( 'gf-tooltips' );

		// we have it. leave it alone
		if ( ! empty( $exist ) ) {
			return;
		}

		// set a data array
		$data   = array(
			'type'      => 'icon',
			'design'    => 'light',
			'target'    => 'right',
		);

		// add the option
		update_option( 'gf-tooltips', $data, 'no' );
	}

/// end class
}

// Instantiate our class
$GF_Tooltips = GF_Tooltips::getInstance();

