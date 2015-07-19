<?php
/**
 * Gravity Tooltips - Admin Module
 *
 * Contains admin related functions
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

if ( ! class_exists( 'GF_Tooltips_Admin' ) ) {

// Start up the engine
class GF_Tooltips_Admin
{

	/**
	 * fire it up
	 *
	 */
	public function init() {

		// bail on non admin
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts',                array( $this, 'scripts_styles'              ),  10      );
		add_action( 'admin_init',                           array( $this, 'reg_settings'                )           );
		add_action( 'admin_notices',                        array( $this, 'active_check'                ),  10      );
		add_action( 'admin_notices',                        array( $this, 'settings_saved'              ),  10      );

		add_filter( 'plugin_action_links',                  array( $this, 'quick_link'                  ),  10, 2   );

		// backend GF specifc
		add_filter( 'gform_addon_navigation',               array( $this, 'create_menu'                 )           );
		add_action( 'gform_field_advanced_settings',        array( $this, 'add_form_builder_field'      ),  10, 2   );
		add_filter( 'gform_tooltips',                       array( $this, 'add_form_builder_tooltip'    )           );
		add_filter( 'gform_noconflict_scripts',             array( $this, 'register_admin_script'       )           );
	}

	/**
	 * load JS for fields
	 *
	 * @param  [type] $hook [description]
	 *
	 * @return [type]       [description]
	 */
	public function scripts_styles( $hook ) {

		// bail if not on the GF page
		if( ! RGForms::is_gravity_page() ) {
			return;
		}

		// load them
		wp_enqueue_script( 'gftips-admin', plugins_url( '/js/gftips.admin.js', __FILE__ ),  array( 'jquery' ),  GFT_VER, true );
		wp_localize_script( 'gftips-admin', 'gftipsAdmin',
			array(
				'fldTypes' => self::show_field_item_types()
			)
		);
	}

	/**
	 * register our settings for later
	 * @return void
	 */
	public function reg_settings() {
		register_setting( 'gf-tooltips', 'gf-tooltips');
	}

	/**
	 * check that GF is active before loading
	 *
	 * @return void
	 */
	public function active_check() {

		// bail without our function
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		// fetch the screen
		$screen = get_current_screen();

		// bail if we don't match up
		if ( ! is_object( $screen ) || empty( $screen->parent_file ) || $screen->parent_file !== 'plugins.php' ) {
			return;
		}

		// if we don't have our class, show it
		if ( ! class_exists( 'GFForms' ) ) {

			echo '<div id="message" class="error notice is-dismissible fade below-h2"><p><strong>' . __( 'This plugin requires Gravity Forms to function.', 'gravity-tooltips' ) . '</strong></p></div>';

			// hide activation method
			unset( $_GET['activate'] );

			// deactivate YOURSELF
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}

		// and just return
		return;
	}

	/**
	 * register the admin script with Gravity Forms so that it gets enqueued when running on no-conflict mode
	 *
	 * @param  [type] $scripts [description]
	 *
	 * @return [type]          [description]
	 */
	public function register_admin_script( $scripts ){

		// add it
		$scripts[] = 'gftips-admin';

		// return them
		return $scripts;
	}

	/**
	 * show settings link on plugins page
	 *
	 * @param  [type] $links [description]
	 * @param  [type] $file  [description]
	 *
	 * @return [type]        [description]
	 */
	public function quick_link( $links, $file ) {

		static $this_plugin;

		if ( ! $this_plugin ) {
			$this_plugin = GFT_BASE;
		}

		// check to make sure we are on the correct plugin
		if ( $file != $this_plugin ) {
			return $links;
		}

		// make our link
		$setup  = '<a href="' . menu_page_url( 'gf-tooltips', 0 ) . ' ">' . __( 'Settings', 'gravity-tooltips' ) . '</a>';

		// add it to the array
		array_unshift( $links, $setup );

		// return it
		return $links;
	}

	/**
	 * add tooltip settings to main GF admin menu
	 *
	 * @param  [type] $menu_items [description]
	 *
	 * @return [type]             [description]
	 */
	public function create_menu( $menu_items ) {

		// set up the item
		$menu_items[] = array(
			'name'      => 'gf-tooltips',
			'label'     => __( 'Tooltips', 'gravity-tooltips' ),
			'callback'  => array( __class__, 'settings_page' )
		);

		// return the items
		return $menu_items;
	}

	/**
	 * add the new textfield to the form builder on the advanced tab in GF
	 *
	 * @param [type] $position [description]
	 *
	 * @param [type] $form_id  [description]
	 */
	public function add_form_builder_field( $position, $form_id ) {

		// only run this on our preferred position
		if ( $position != 50 ) {
			return;
		}

		// add the tooltip for the tooltips
		echo '<li class="custom_tooltip_setting field_setting">';
			echo '<label for="custom_tooltip">';
				echo __( 'Tooltip Content', 'gravity-tooltips' );
				echo '&nbsp;' . gform_tooltip( 'custom_tooltip_tip', 'tooltip', true );
			echo '</label>';

			echo '<input type="text" class="fieldwidth-3" id="custom_tooltip" size="35" onkeyup="SetFieldProperty(\'customTooltip\', this.value);"/>';

		echo '</li>';
	}

	/**
	 * add the tooltip text to the GF form field item
	 *
	 * @param [type] $tooltips [description]
	 */
	public function add_form_builder_tooltip( $tooltips ) {

		// the title of the tooltip
		$title  = '<h6>' . __( 'Custom Tooltip', 'gravity-tooltips' ) . '</h6>';

		// the text
		$text   = __( 'Enter the content you want to appear in the tooltip for this field.', 'gravity-tooltips' );

		$tooltips['custom_tooltip_tip'] = $title . $text;

		// return the actual tooltip
		return $tooltips;
	}

	/**
	 * display message on saved settings
	 *
	 * @return [HTML] message above page
	 */
	public function settings_saved() {

		// first check to make sure we're on our settings
		if ( empty( $_GET['page'] ) || $_GET['page'] !== 'gf-tooltips' ) {
			return;
		}

		// make sure we have our updated prompt
		if ( empty( $_GET['settings-updated'] ) ) {
			return;
		}

		// show our update messages
		echo '<div id="message" class="updated notice fade is-dismissible">';
			echo '<p>' . __( 'Settings have been saved.', 'gravity-tooltips' ) . '</p>';
		echo '</div>';

		// and return
		return;
	}

	/**
	 * Display main options page structure
	 *
	 * @return [mixed HTML] the settings page
	 */
	public static function settings_page() {

		// bail without caps
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// set up our form wrapper
		echo '<div class="wrap">';

			// title it
			echo '<h2>'. __( 'Gravity Forms Tooltips', 'gravity-tooltips' ) . '</h2>';

			// wrap it
			echo '<form method="post" action="options.php">';

				// fetch the data
				$data   = GF_Tooltips_Helper::get_tooltip_data();

				// option index checks
				$type       = ! empty( $data['type'] ) ? $data['type'] : 'icon';
				$icon       = ! empty( $data['icon'] ) ? $data['icon'] : 'question';
				$design     = ! empty( $data['design'] ) ? $data['design'] : 'light';
				$target     = ! empty( $data['target'] ) ? $data['target'] : 'right';

				// our nonce and whatnot
				settings_fields( 'gf-tooltips' );

				echo '<table class="form-table gf-tooltip-table"><tbody>';

					echo '<tr>';
						echo '<th scope="row">' . __( 'Layout Type', 'gravity-tooltips' ) . '</th>';
						echo '<td>';
							echo '<p>';
							echo '<input id="gf-type-label" class="gf-tooltip-type" type="radio" name="gf-tooltips[type]" value="label" ' . checked( $type, 'label', false ) . ' />';
							echo '<label for="gf-type-label">' . __( 'Apply tooltip to existing label', 'gravity-tooltips' ) . '</label>';
							echo '</p>';

							echo '<p>';
							echo '<input id="gf-type-icon" class="gf-tooltip-type" type="radio" name="gf-tooltips[type]" value="icon" ' . checked( $type, 'icon', false ) . ' />';
							echo '<label for="gf-type-icon">' . __( 'Insert tooltip icon next to label', 'gravity-tooltips' ) . '</label>';
							echo '</p>';

							echo '<p>';
							echo '<input id="gf-type-single" class="gf-tooltip-type" type="radio" name="gf-tooltips[type]" value="single" ' . checked( $type, 'single', false ) . ' />';
							echo '<label for="gf-type-single">' . __( 'Insert tooltip underneath input field.', 'gravity-tooltips' ) . '</label>';
							echo '</p>';

						echo '</td>';
					echo '</tr>';

					echo '<tr>';
						echo '<th scope="row">' . __( 'Icon Type', 'gravity-tooltips' ) . '</th>';
						echo '<td>';

							echo '<p>';
							echo '<input id="gf-icon-question" class="gf-tooltip-icon" type="radio" name="gf-tooltips[icon]" value="question" ' . checked( $icon, 'question', false ) . ' />';
							echo '<label for="gf-icon-question">' . __( 'Question Mark', 'gravity-tooltips' ) . '</label>';
							echo '</p>';

							echo '<p>';
							echo '<input id="gf-icon-info" class="gf-tooltip-icon" type="radio" name="gf-tooltips[icon]" value="info" ' . checked( $icon, 'info', false ) . ' />';
							echo '<label for="gf-icon-info">' . __( 'Information Mark', 'gravity-tooltips' ) . '</label>';
							echo '</p>';

						echo '</td>';
					echo '</tr>';

					echo '<tr>';
						echo '<th scope="row">' . __( 'Design Style', 'gravity-tooltips' ) . '</th>';
						echo '<td>';
							echo '<select name="gf-tooltips[design]" id="gf-option-design">';
							echo self::get_admin_designs( $design );
							echo '</select>';
						echo '</td>';
					echo '</tr>';

					echo '<tr>';
						echo '<th scope="row">' . __( 'Target', 'gravity-tooltips' ) . '</th>';
						echo '<td>';
							echo '<select name="gf-tooltips[target]" id="gf-option-Target">';
							echo self::get_admin_placement( $target );
							echo '</select>';
							echo '<p class="description">' . __( 'The placement of the tooltip box in relation to the label / icon.', 'gravity-tooltips' ) . '</p>';
						echo '</td>';
					echo '</tr>';

				echo '</tbody></table>';

				echo '<p><input type="submit" class="button-primary" value="'. __( 'Save Changes' ) . '" /></p>';

			echo '</form>';

			echo '<p>';
				echo sprintf( __( 'A more detailed explanation about how the tooltip placement and design choices can be found <a href="%s" target="_blank">here</a>.', 'gravity-tooltips' ), 'http://kushagragour.in/lab/hint/' );
			echo '</p>';


		echo '</div>';
	}

	/**
	 * set up all the possible field types
	 *
	 * @return array   all the field types
	 */
	public static function show_field_item_types() {

		// set the array
		$defaults   = array(
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

		// return the types, filtered
		return apply_filters( 'gf_tooltips_allowed_fields', $defaults );
	}

	/**
	 * get the placemend descriptions
	 *
	 * @param  string $current  the current selection (if it exists)
	 *
	 * @return mixed/HTML
	 */
	public static function get_admin_placement( $current = '' ) {

		// set our array
		$options   = array(
			'top'       => __( 'Top', 'gravity-tooltips' ),
			'right'     => __( 'Right', 'gravity-tooltips' ),
			'bottom'    => __( 'Bottom', 'gravity-tooltips' ),
			'left'      => __( 'Left', 'gravity-tooltips' ),
		);

		// set an empty
		$drop   = '';

		// loop them and make a dropdown
		foreach ( $options as $key => $label ) {
			$drop  .= '<option value="' . esc_attr( $key ) . '"' . selected( $current, $key, false ) . '>' . esc_attr( $label ) . '</option>';
		}

		// return it
		return $drop;
	}

	/**
	 * get the dropdown for the design
	 *
	 * @param  string $current [description]
	 *
	 * @return mixed/HTML
	 */
	public static function get_admin_designs( $current = '' ) {

		// set our array
		$options   = array(
			'light'     => __( 'Light', 'gravity-tooltips' ),
			'dark'      => __( 'Dark', 'gravity-tooltips' ),
			'success'   => __( 'Green', 'gravity-tooltips' ),
			'info'      => __( 'Blue', 'gravity-tooltips' ),
			'error'     => __( 'Red', 'gravity-tooltips' ),
			'warning'   => __( 'Orange', 'gravity-tooltips' )
		);

		// filter the design options
		$options   = apply_filters( 'gf_tooltips_design_options', $options );

		// set an empty
		$drop   = '';

		// loop them and make a dropdown
		foreach ( $options as $key => $label ) {
			$drop  .= '<option value="' . esc_attr( $key ) . '"' . selected( $current, $key, false ) . '>' . esc_attr( $label ) . '</option>';
		}

		// return it
		return $drop;
	}


// end class
}

// end exists check
}

// Instantiate our class
$GF_Tooltips_Admin = new GF_Tooltips_Admin();
$GF_Tooltips_Admin->init();
