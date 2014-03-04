<?php
/*
Plugin Name: Gravity Forms Tooltips
Plugin URI: http://andrewnorcross.com/plugins/gravity-tooltips/
Description: Convert the Gravity Forms description field into tooltips
Author: Andrew Norcross
Version: 1.0
Requires at least: 3.0
Author URI: http://andrewnorcross.com
*/
/*  Copyright 2012 Andrew Norcross

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


if( ! defined( 'GFT_BASE' ) )
	define( 'GFT_BASE', plugin_basename(__FILE__) );

if( ! defined( 'GFT_VER' ) )
	define( 'GFT_VER', '1.0' );

class GF_Tooltips
{

	/**
	 * This is our constructor
	 *
	 * @return GF_Tooltips
	 */
	public function __construct() {
		// back end general
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


/// end class
}

new GF_Tooltips();
