<?php
/**
 * Gravity Tooltips - Admin Module
 *
 * Contains admin related functions
 *
 * @package Gravity Forms Tooltips
 */

// Confirm we haven't already loaded the class.
if ( ! class_exists( 'GF_Tooltips_Admin' ) ) {

/**
 * Declare the admin class.
 */
class GF_Tooltips_Admin
{

	/**
	 * Call our admin related hooks.
	 */
	public function init() {

		// Bail on non admin.
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts',                array( $this, 'scripts_styles'              ),  10      );
		add_action( 'admin_init',                           array( $this, 'reg_settings'                )           );
		add_action( 'admin_notices',                        array( $this, 'active_check'                ),  10      );
		add_action( 'admin_notices',                        array( $this, 'settings_saved'              ),  10      );

		add_filter( 'plugin_action_links',                  array( $this, 'quick_link'                  ),  10, 2   );

		// Back-end GF specifc.
		add_filter( 'gform_addon_navigation',               array( $this, 'create_menu'                 )           );
		add_action( 'gform_field_advanced_settings',        array( $this, 'add_form_builder_field'      ),  10, 2   );
		add_filter( 'gform_tooltips',                       array( $this, 'add_form_builder_tooltip'    )           );
		add_filter( 'gform_noconflict_scripts',             array( $this, 'register_admin_script'       )           );
	}

	/**
	 * load JS for fields.
	 *
	 * @param  string $hook  The admin page we are on.
	 *
	 * @return void
	 */
	public function scripts_styles( $hook ) {

		// Bail if not on the GF page.
		if( ! RGForms::is_gravity_page() ) {
			return;
		}

		// Enqueue our items.
		wp_enqueue_script( 'gftips-admin', plugins_url( '/js/gftips.admin.js', __FILE__ ),  array( 'jquery' ),  GFT_VER, true );
		wp_localize_script( 'gftips-admin', 'gftipsAdmin',
			array(
				'fldTypes' => GF_Tooltips_Helper::show_field_item_types()
			)
		);
	}

	/**
	 * Register our settings for later.
	 *
	 * @return void
	 */
	public function reg_settings() {
		register_setting( 'gf-tooltips', 'gf-tooltips');
	}

	/**
	 * Check that GF is active before loading.
	 *
	 * @return void
	 */
	public function active_check() {

		// Bail without our function.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		// Fetch the screen.
		$screen = get_current_screen();

		// Bail if we don't match up.
		if ( ! is_object( $screen ) || empty( $screen->parent_file ) || 'plugins.php' !== $screen->parent_file ) {
			return;
		}

		// If we don't have our class, show it.
		if ( ! class_exists( 'GFForms' ) ) {

			echo '<div id="message" class="error notice is-dismissible fade below-h2"><p><strong>' . __( 'This plugin requires Gravity Forms to function.', 'gravity-tooltips' ) . '</strong></p></div>';

			// Hide activation method.
			unset( $_GET['activate'] );

			// Deactivate YOURSELF.
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}

		// And just return.
		return;
	}

	/**
	 * Register the admin script with Gravity Forms so that it
	 * gets enqueued when running on no-conflict mode.
	 *
	 * @param  array $scripts  The existing array of scripts.
	 *
	 * @return array $scripts  The updated array of scripts.
	 */
	public function register_admin_script( $scripts ) {

		// Add it to the array if it isn't already there.
		if ( ! in_array( 'gftips-admin', $scripts ) ) {
			$scripts[] = 'gftips-admin';
		}

		// Return the script array.
		return $scripts;
	}

	/**
	 * Add our "settings" links to the plugins page.
	 *
	 * @param  array  $links  The existing array of links.
	 * @param  string $file   The file we are actually loading from.
	 *
	 * @return array  $links  The updated array of links.
	 */
	public function quick_link( $links, $file ) {

		// Bail without caps.
		if ( ! current_user_can( apply_filters( 'gf_tooltips_admin_cap', 'gravityforms_edit_settings' ) ) ) {
			return $links;
		}

		// Set the static var.
		static $this_plugin;

		// Check the base if we aren't paired up.
		if ( ! $this_plugin ) {
			$this_plugin = GFT_BASE;
		}

		// Check to make sure we are on the correct plugin.
		if ( $file != $this_plugin ) {
			return $links;
		}

		// Make our link.
		$setup  = '<a href="' . menu_page_url( 'gf-tooltips', 0 ) . ' ">' . __( 'Settings', 'gravity-tooltips' ) . '</a>';

		// Add it to the array.
		array_unshift( $links, $setup );

		// Return the resulting array.
		return $links;
	}

	/**
	 * Add tooltip settings to main GF admin menu.
	 *
	 * @param  array $menu_items  The array of existing menu items.
	 *
	 * @return array $menu_items  The updated array of menu items.
	 */
	public function create_menu( $menu_items ) {

		// Set up the item.
		$menu_items[] = array(
			'name'        => 'gf-tooltips',
			'label'       => __( 'Tooltips', 'gravity-tooltips' ),
			'permission'  => apply_filters( 'gf_tooltips_admin_cap', 'gravityforms_edit_settings' ),
			'callback'    => array( __class__, 'settings_page' ),
		);

		// Return the items.
		return $menu_items;
	}

	/**
	 * Add the new textfield to the form builder on the advanced tab in GF.
	 *
	 * @param integer $position  The position of the field area.
	 * @param integer $form_id   The ID of the form.
	 *
	 * @return HTML              Our new field.
	 */
	public function add_form_builder_field( $position, $form_id ) {

		// Only run this on our preferred position.
		if ( $position != 50 ) {
			return;
		}

		// Wrap the field in a <li> tag.
		echo '<li class="custom_tooltip_setting field_setting">';

			// Set the label for the field.
			echo '<label class="section_label" for="custom_tooltip">';
				echo __( 'Tooltip Content', 'gravity-tooltips' );
				echo '&nbsp;' . gform_tooltip( 'custom_tooltip_tip', 'tooltip', true );
			echo '</label>';

			// Set the input field.
			echo '<input type="text" class="fieldwidth-3" id="custom_tooltip" size="35" onkeyup="SetFieldProperty(\'customTooltip\', this.value);"/>';

		// Close the <li> tag.
		echo '</li>';
	}

	/**
	 * Add the tooltip text to the GF form field item.
	 *
	 * @param  array $tooltips  The array of existing GF tooltips.
	 *
	 * @return array $tooltips  The updated array of GF tooltips.
	 */
	public function add_form_builder_tooltip( $tooltips ) {

		// The title of the tooltip.
		$title  = '<h6>' . __( 'Custom Tooltip', 'gravity-tooltips' ) . '</h6>';

		// The text itself.
		$text   = __( 'Enter the content you want to appear in the tooltip for this field.', 'gravity-tooltips' );

		// Now add our item to the array.
		$tooltips['custom_tooltip_tip'] = $title . $text;

		// Return the tooltip array.
		return $tooltips;
	}

	/**
	 * Display message on saved settings.
	 *
	 * @return void
	 */
	public function settings_saved() {

		// First check to make sure we're on our settings.
		if ( empty( $_GET['page'] ) || $_GET['page'] !== 'gf-tooltips' ) {
			return;
		}

		// Make sure we have our updated prompt.
		if ( empty( $_GET['settings-updated'] ) ) {
			return;
		}

		// Show our update messages.
		echo '<div id="message" class="updated notice fade is-dismissible">';
			echo '<p>' . __( 'Settings have been saved.', 'gravity-tooltips' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Display main options page structure.
	 *
	 * @return void
	 */
	public static function settings_page() {

		// Bail without caps.
		if ( ! current_user_can( apply_filters( 'gf_tooltips_admin_cap', 'gravityforms_edit_settings' ) ) ) {
			return;
		}

		// Set up our form wrapper.
		echo '<div class="wrap">';

			// Title it.
			echo '<h1>'. __( 'Gravity Forms Tooltips', 'gravity-tooltips' ) . '</h1>';

			// Wrap it.
			echo '<form method="post" action="options.php">';

				// Fetch the data.
				$data   = GF_Tooltips_Helper::get_tooltip_data();

				// Option index checks.
				$type       = ! empty( $data['type'] ) ? $data['type'] : 'icon';
				$size       = ! empty( $data['size'] ) ? $data['size'] : 'default';
				$icon       = ! empty( $data['icon'] ) ? $data['icon'] : 'question';
				$design     = ! empty( $data['design'] ) ? $data['design'] : 'light';
				$target     = ! empty( $data['target'] ) ? $data['target'] : 'right';

				// Our nonce and whatnot.
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
						echo '<th scope="row">' . __( 'Tooltip Size', 'gravity-tooltips' ) . '</th>';
						echo '<td>';

							echo '<p>';
							echo '<input id="gf-size-default" class="gf-tooltip-size" type="radio" name="gf-tooltips[size]" value="default" ' . checked( $size, 'default', false ) . ' />';
							echo '<label for="gf-size-default">' . __( 'Default', 'gravity-tooltips' ) . '</label>';
							echo '</p>';

							echo '<p>';
							echo '<input id="gf-size-small" class="gf-tooltip-size" type="radio" name="gf-tooltips[size]" value="small" ' . checked( $size, 'small', false ) . ' />';
							echo '<label for="gf-size-small">' . __( 'Small', 'gravity-tooltips' ) . '</label>';
							echo '</p>';

							echo '<p>';
							echo '<input id="gf-size-medium" class="gf-tooltip-size" type="radio" name="gf-tooltips[size]" value="medium" ' . checked( $size, 'medium', false ) . ' />';
							echo '<label for="gf-size-medium">' . __( 'Medium', 'gravity-tooltips' ) . '</label>';
							echo '</p>';

							echo '<p>';
							echo '<input id="gf-size-large" class="gf-tooltip-size" type="radio" name="gf-tooltips[size]" value="large" ' . checked( $size, 'large', false ) . ' />';
							echo '<label for="gf-size-large">' . __( 'Large', 'gravity-tooltips' ) . '</label>';
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

					echo self::get_admin_designs( $design );

					echo self::get_admin_placement( $target );

				echo '</tbody></table>';

				echo '<p><input type="submit" class="button-primary" value="'. __( 'Save Changes' ) . '" /></p>';

			echo '</form>';

			echo '<p>';
				echo sprintf( __( 'A more detailed explanation about how the tooltip placement and design choices can be found <a href="%s" target="_blank">here</a>.', 'gravity-tooltips' ), 'http://kushagragour.in/lab/hint/' );
			echo '</p>';

		echo '</div>';
	}

	/**
	 * Get the dropdown for the design.
	 *
	 * @param  string $current  the current selection (if it exists).
	 *
	 * @return mixed/HTML
	 */
	public static function get_admin_designs( $current = '' ) {

		// Set our array.
		$items  = array(
			'light'     => __( 'Light', 'gravity-tooltips' ),
			'dark'      => __( 'Dark', 'gravity-tooltips' ),
			'success'   => __( 'Green', 'gravity-tooltips' ),
			'info'      => __( 'Blue', 'gravity-tooltips' ),
			'error'     => __( 'Red', 'gravity-tooltips' ),
			'warning'   => __( 'Orange', 'gravity-tooltips' )
		);

		// Filter the design options.
		$items  = apply_filters( 'gf_tooltips_design_options', $items );

		// Bail if someone made no fields.
		if ( empty( $items ) ) {
			return;
		}

		// Set an empty field.
		$field  = '';

		// Wrap the field.
		$field .= '<tr>';

			// Set the label.
			$field .= '<th scope="row">' . __( 'Design Style', 'gravity-tooltips' ) . '</th>';

			// Wrap the dropdown.
			$field .= '<td>';

				// Set the <select> item name and ID.
				$field .= '<select name="gf-tooltips[design]" id="gf-option-design">';

				// Loop them and make a dropdown.
				foreach ( $items as $key => $label ) {
					$field .=  '<option value="' . esc_attr( $key ) . '"' . selected( $current, $key, false ) . '>' . esc_attr( $label ) . '</option>';
				}

				// Close the <select> markup.
				$field .= '</select>';
			// Close the dropdown wrapper.
			$field .= '</td>';

		// Close the field wrapper.
		$field .= '</tr>';

		// Return the field.
		return $field;
	}

	/**
	 * Get the placement descriptions.
	 *
	 * @param  string $current  the current selection (if it exists).
	 *
	 * @return mixed/HTML
	 */
	public static function get_admin_placement( $current = '' ) {

		// Set our array.
		$items  = array(
			'top'       => __( 'Top', 'gravity-tooltips' ),
			'right'     => __( 'Right', 'gravity-tooltips' ),
			'bottom'    => __( 'Bottom', 'gravity-tooltips' ),
			'left'      => __( 'Left', 'gravity-tooltips' ),
		);

		// Filter the placement options.
		$items  = apply_filters( 'gf_tooltips_placement_options', $items );

		// Bail if someone made no fields.
		if ( empty( $items ) ) {
			return;
		}

		// Set an empty field.
		$field  = '';

		// Wrap the field.
		$field .= '<tr>';

			// Set the label.
			$field .= '<th scope="row">' . __( 'Target', 'gravity-tooltips' ) . '</th>';

			// Wrap the dropdown.
			$field .= '<td>';

				// Set the <select> item name and ID.
				$field .= '<select name="gf-tooltips[target]" id="gf-option-target">';

				// Loop them and make a dropdown.
				foreach ( $items as $key => $label ) {
					$field .=  '<option value="' . esc_attr( $key ) . '"' . selected( $current, $key, false ) . '>' . esc_attr( $label ) . '</option>';
				}

				// Close the <select> markup.
				$field .= '</select>';

				// Include our description.
				$field .= ' <span class="description">' . __( 'The placement of the tooltip box in relation to the label / icon.', 'gravity-tooltips' ) . '</span>';

			// Close the dropdown wrapper.
			$field .= '</td>';

		// Close the field wrapper.
		$field .= '</tr>';

		// Return the field.
		return $field;
	}

	// End class.
}

// end exists check
}

// Instantiate our class
$GF_Tooltips_Admin = new GF_Tooltips_Admin();
$GF_Tooltips_Admin->init();
