<?php

class GF_Tooltips_Admin
{

	/**
	 * This is our constructor
	 *
	 * @return GF_Tooltips
	 */
	public function __construct() {
		add_action			(	'admin_enqueue_scripts',				array(	$this,	'scripts_styles'			),	10		);
		add_action			(	'admin_init',							array(	$this,	'reg_settings'				)			);
		add_action			(	'admin_notices',						array(	$this,	'active_check'				),	10		);
		add_action			(	'admin_notices',						array(	$this,	'settings_saved'			),	10		);

		add_filter			(	'plugin_action_links',					array(	$this,	'quick_link'				),	10,	2	);

		// backend GF specifc
		add_action			(	'gform_field_advanced_settings',		array(	$this,	'add_form_builder_field'	),	10,	2	);
		add_filter			(	'gform_tooltips',						array(	$this,	'add_form_builder_tooltip'	)			);
		add_filter			(	'gform_addon_navigation',				array(	$this,	'create_menu'				)			);
		add_filter			(	'gform_noconflict_scripts',				array(	$this,	'register_admin_script'		)			);
	}


	/**
	 * check that GF is active before loading
	 * @return void
	 */
	public function active_check() {

		$screen = get_current_screen();

		if ( $screen->parent_file !== 'plugins.php' ) {
			return;
		}

		if ( ! class_exists( 'GFForms' ) ) {

			echo '<div id="message" class="error fade below-h2"><p><strong>'.__( 'This plugin requires Gravity Forms to function.', 'gravity-tooltips' ).'</strong></p></div>';

			// hide activation method
			unset( $_GET['activate'] );

			// deactivate YOURSELF
			deactivate_plugins( plugin_basename( __FILE__ ) );

		}

		return;

	}


	/**
	 * register the admin script with Gravity Forms so that it gets enqueued when running on no-conflict mode
	 * @param  [type] $scripts [description]
	 * @return [type]          [description]
	 */
	public function register_admin_script( $scripts ){

		$scripts[] = 'gftips-admin';

		return $scripts;

	}

	/**
	 * load JS for fields
	 * @param  [type] $hook [description]
	 * @return [type]       [description]
	 */
	public function scripts_styles( $hook ) {

		if( ! RGForms::is_gravity_page() ) {
			return;
		}

		wp_enqueue_script( 'gftips-admin', plugins_url( '/js/gftips.admin.js', __FILE__ ),	array( 'jquery' ),	GFT_VER, true );
		wp_localize_script( 'gftips-admin', 'gftipsAdmin', array(
			'fieldtypes'	=> GF_Tooltips::show_field_item_types()
			)
		);


	}

	/**
	 * show settings link on plugins page
	 * @param  [type] $links [description]
	 * @param  [type] $file  [description]
	 * @return [type]        [description]
	 */
	public function quick_link( $links, $file ) {

		static $this_plugin;

		if ( ! $this_plugin ) {
			$this_plugin = GFT_BASE;
		}

		// check to make sure we are on the correct plugin
		if ( $file != $this_plugin )
			return $links;

		$settings_link  = '<a href="' . menu_page_url( 'gf-tooltips', 0 ).' ">'.__( 'Settings', 'gravity-tooltips' ).'</a>';

		array_unshift( $links, $settings_link );

		return $links;

	}

	/**
	 * add tooltip settings to main GF admin menu
	 * @param  [type] $menu_items [description]
	 * @return [type]             [description]
	 */
	public function create_menu( $menu_items ) {

		$menu_items[] = array(
			'name'      => 'gf-tooltips',
			'label'     => __( 'Tooltips', 'gravity-tooltips' ),
			'callback'  => array( $this, 'settings_page' )
		);

		return $menu_items;
	}

	/**
	 * add the new textfield to the form builder on the advanced tab in GF
	 * @param [type] $position [description]
	 * @param [type] $form_id  [description]
	 */
	public function add_form_builder_field( $position, $form_id ) {

		if ( $position != 50 ) {
			return;
		}

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
	 * @param [type] $tooltips [description]
	 */
	public function add_form_builder_tooltip( $tooltips ) {

		// the title of the tooltip
		$title	= '<h6>'.__( 'Custom Tooltip', 'gravity-tooltips' ).'</h6>';

		// the text
		$text	= __( 'Enter the content you want to appear in the tooltip for this field.', 'gravity-tooltips' );

		$tooltips['custom_tooltip_tip'] = $title.$text;

		return $tooltips;
	}


	/**
	 * register our settings for later
	 * @return void
	 */
	public function reg_settings() {

		register_setting( 'gf-tooltips', 'gf-tooltips');

	}

	/**
	 * display message on saved settings
	 * @return [HTML] message above page
	 */
	public function settings_saved() {

		// first check to make sure we're on our settings
		if ( ! isset( $_REQUEST['page'] ) || isset( $_REQUEST['page'] ) && $_REQUEST['page'] !== 'gf-tooltips' )
			return;

		// make sure we have our updated prompt
		if ( ! isset( $_REQUEST['settings-updated'] ) || isset( $_REQUEST['settings-updated'] ) && $_REQUEST['settings-updated'] !== 'true' )
			return;

		echo '<div id="message" class="updated">';
			echo '<p>'.__( 'Settings have been saved.', 'gravity-tooltips' ).'</p>';
		echo '</div>';

		return;
	}

	/**
	 * Display main options page structure
	 * @return [mixed HTML] the settings page
	 */
	public function settings_page() {

		if ( ! current_user_can( 'manage_options' ) )
			return;

		echo '<div class="wrap">';
			echo '<h2>'. __( 'Gravity Forms Tooltips', 'gravity-tooltips' ) . '</h2>';

			echo '<div id="poststuff" class="metabox-holder has-right-sidebar">';

			echo self::settings_side();
			echo self::settings_open();

			echo '<form method="post" action="options.php">';
				settings_fields( 'gf-tooltips' );
				$data	= get_option( 'gf-tooltips' );

				// option index checks
				$style		= isset( $data['style'] )		? $data['style']	: 'icon';
				$design		= isset( $data['design'] )		? $data['design']	: 'light';
				$target		= isset( $data['target'] )		? $data['target']	: 'topRight';
				$location	= isset( $data['location'] )	? $data['location']	: 'bottomLeft';

				echo '<table class="form-table gf-tooltip-table"><tbody>';

					echo '<tr>';
						echo '<th scope="row">' . __( 'Style', 'gravity-tooltips' ) . '</th>';
						echo '<td>';
							echo '<p>';
							echo '<input id="gf-style-label" class="gf-tooltip-style" type="radio" name="gf-tooltips[style]" value="label" ' . checked( $style, 'label', false ) . ' />';
							echo '<label for="gf-style-label">' . __('Apply tooltip to existing label', 'gravity-tooltips' ) . '</label>';
							echo '</p>';

							echo '<p>';
							echo '<input id="gf-style-icon" class="gf-tooltip-style" type="radio" name="gf-tooltips[style]" value="icon" ' . checked( $style, 'icon', false ) . ' />';
							echo '<label for="gf-style-icon">' . __( 'Insert tooltip icon next to label', 'gravity-tooltips' ) . '</label>';
							echo '</p>';

							echo '<p>';
							echo '<input id="gf-style-single" class="gf-tooltip-style" type="radio" name="gf-tooltips[style]" value="single" ' . checked( $style, 'single', false ) . ' />';
							echo '<label for="gf-style-single">' . __( 'Insert tooltip underneath input field.', 'gravity-tooltips' ) . '</label>';
							echo '</p>';

						echo '</td>';
					echo '</tr>';

					echo '<tr>';
						echo '<th scope="row">' . __( 'Design Style', 'gravity-tooltips' ) . '</th>';
						echo '<td>';
							echo '<select name="gf-tooltips[design]" id="gf-option-design">';
							echo GF_Tooltips::get_qtip_designs( $design );
							echo '</select>';
						echo '</td>';
					echo '</tr>';

					echo '<tr>';
						echo '<th scope="row">' . __( 'Target', 'gravity-tooltips' ) . '</th>';
						echo '<td>';
							echo '<select name="gf-tooltips[target]" id="gf-option-Target">';
							echo GF_Tooltips::get_qtip_placement( $target );
							echo '</select>';
							echo '<p class="description">' . __( 'The placement of the tooltip box in relation to the label / icon.', 'gravity-tooltips' ) . '</p>';
						echo '</td>';
					echo '</tr>';

					echo '<tr>';
						echo '<th scope="row">' . __( 'Location', 'gravity-tooltips' ) . '</th>';
						echo '<td>';
							echo '<select name="gf-tooltips[location]" id="gf-option-location">';
							echo GF_Tooltips::get_qtip_placement( $location );
							echo '</select>';
							echo '<p class="description">' . __( 'The location on the label / icon for the tooltip box to affix to.', 'gravity-tooltips' ) . '</p>';
						echo '</td>';
					echo '</tr>';

				echo '</tbody></table>';

				echo '<p><input type="submit" class="button-primary" value="'. __( 'Save Changes' ) . '" /></p>';

			echo '</form>';

			echo '<p>';
				echo sprintf( __( 'A more detailed explanation about how the tooltip placement and location can be found <a href="%s" target="_blank">here</a>.', 'gravity-tooltips' ), 'http://craigsworks.com/projects/qtip/docs/tutorials/#position' );
			echo '</p>';

			echo self::settings_close();

			echo '</div>';
		echo '</div>';

	}

	/**
	 * Some extra stuff for the settings page
	 * this is just to keep the area cleaner
	 *
	 * @return [mixed HTML] sidebar stuff on settings page
	 */
	static function settings_side() { ?>

		<div id="side-info-column" class="inner-sidebar">
			<div class="meta-box-sortables">
				<div id="admin-about" class="postbox">
					<h3 class="hndle" id="about-sidebar"><?php _e('About the Plugin') ?></h3>
					<div class="inside">
						<p>Talk to <a href="http://twitter.com/norcross" target="_blank">@norcross</a> on twitter or visit the <a href="http://wordpress.org/support/plugin//" target="_blank">plugin support form</a> for bugs or feature requests.</p>
						<p><?php _e('<strong>Enjoy the plugin?</strong>') ?><br />
						<a href="http://twitter.com/?status=I'm using @norcross's PLUGIN NAME - check it out! http://l.norc.co//" target="_blank"><?php _e('Tweet about it') ?></a> <?php _e('and consider donating.') ?></p>
						<p><?php _e('<strong>Donate:</strong> A lot of hard work goes into building plugins - support your open source developers. Include your twitter username and I\'ll send you a shout out for your generosity. Thank you!') ?><br />
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="11085100">
						<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form></p>
					</div>
				</div>
			</div>

			<div class="meta-box-sortables">
				<div id="admin-more" class="postbox">
					<h3 class="hndle" id="about-sidebar"><?php _e('Links') ?></h3>
					<div class="inside">
						<ul>
						<li><a href="http://wordpress.org/extend/plugins//" target="_blank">Plugin on WP.org</a></li>
						<li><a href="https://github.com/norcross/" target="_blank">Plugin on GitHub</a></li>
						<li><a href="http://wordpress.org/support/plugin/" target="_blank">Support Forum</a><li>
						</ul>
					</div>
				</div>
			</div>
		</div> <!-- // #side-info-column .inner-sidebar -->

	<?php }

	static function settings_open() { ?>

		<div id="post-body" class="has-sidebar">
			<div id="post-body-content" class="has-sidebar-content">
				<div id="normal-sortables" class="meta-box-sortables">
					<div id="gf-tooltip-settings" class="postbox">
						<div class="inside gf-tooltip-inside">

	<?php }

	static function settings_close() { ?>

						<br class="clear" />
						</div>
					</div>
				</div>
			</div>
		</div>

	<?php }

/// end class
}

new GF_Tooltips_Admin();