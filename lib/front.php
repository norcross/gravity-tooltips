<?php
/**
 * Gravity Tooltips - Front Module
 *
 * Contains front end related functions
 *
 * @package Gravity Forms Tooltips
 */

// Confirm we haven't already loaded the class.
if ( ! class_exists( 'GF_Tooltips_Front' ) ) {

/**
 * Declare the front class.
 */
class GF_Tooltips_Front
{

	/**
	 * Call our front end related hooks.
	 */
	public function init() {

		// Bail on admin.
		if ( is_admin() ) {
			return;
		}

		// The GF actions and filters we use.
		add_action( 'gform_enqueue_scripts',                array( $this, 'scripts_styles'              ),  10, 2   );
		add_action( 'gform_field_css_class',                array( $this, 'set_tooltip_class'           ),  10, 3   );
		add_filter( 'gform_field_content',                  array( $this, 'set_tooltip_display'         ),  10, 5   );
	}

	/**
	 * Load our CSS file,
	 *
	 * @return void
	 */
	public function scripts_styles( $form, $is_ajax ) {

		// Set our filename based on debug.
		$file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'hint.css' : 'hint.min.css';

		// Optional filter of file location (for using CDN or whatnot).
		$file   = apply_filters( 'gf_tooltips_css_url', plugins_url( '/css/' . $file, __FILE__ ) );

		// Bail if it isn't there anymore.
		if ( empty( $file ) ) {
			return;
		}

		// Now call the actual file.
		wp_enqueue_style( 'gf-tooltips', esc_url( $file ), array(), '2.0.0', 'all' );
	}

	/**
	 * Set a CSS class on the item.
	 *
	 * @param string $classes  The current string of classes available.
	 * @param array  $field    The array of data contained in a field.
	 * @param array  $form     An array of all the form data.
	 *
	 * @return string $classes  The updated string of classes available.
	 */
	public function set_tooltip_class( $classes, $field, $form ){

		// Bail if no tooltip actually exists.
		if ( empty( $field['customTooltip'] ) ) {
			return $classes;
		}

		// get our setting and add our class.
		if ( false !== $type = GF_Tooltips_Helper::get_tooltip_data( 'type', 'icon' ) ) {
			$classes .= ' gf-tooltip gf-tooltip-' . esc_attr( $type );
		}

		// Return the classes.
		return $classes;
	}

	/**
	 * Set up the tooltip display.
	 *
	 * @param string $content   The entire field markup.
	 * @param object $field     The entire field object.
	 * @param array  $value     Possible pre-filled values in a field.
	 * @param integer $lead_id  A lead ID on a form.
	 * @param integer $form_id  The form ID.
	 *
	 * @return HTML             Our tooltip.
	 */
	public function set_tooltip_display( $content, $field, $value, $lead_id, $form_id ) {

		// This is only for the front end.
		if ( is_admin() ) {
			return $content;
		}

		// Fetch our field types.
		$ftypes = GF_Tooltips_Helper::show_field_item_types();

		// Bail if this object has no type, or isn't one we allow.
		if ( empty( $field->type ) || ! in_array( $field->type, $ftypes ) ) {
			return $content;
		}

		// Bail if no tooltip actually exists.
		if ( empty( $field->customTooltip ) ) {
			return $content;
		}

		// Grab our tooltip type first (and bail without).
		if ( false === $type = GF_Tooltips_Helper::get_tooltip_data( 'type', 'icon' ) ) {
			return $content;
		}

		// Filter the text.
		if ( false === $text = apply_filters( 'gf_tooltips_text', $field->customTooltip, $field, $form_id ) ) {
			return $content;
		}

		// Render and return.
		return self::render_tooltip_markup( $field->customTooltip, $type, $content, $field, $form_id );
	}

	/**
	 * Filter the markup based on the type.
	 *
	 * @param  string $text    The tooltip text.
	 * @param  string $type    The tooltip display type.
	 * @param  string $render  The entire field markup.
	 *
	 * @return string $render  The updated field markup.
	 */
	public static function render_tooltip_markup( $text = '', $type = '', $render = '', $field, $form_id ) {

		// Grab our tooltip design, target, and size.
		$design = GF_Tooltips_Helper::get_tooltip_data( 'design', 'light' );
		$target = GF_Tooltips_Helper::get_tooltip_data( 'target', 'right' );
		$size   = GF_Tooltips_Helper::get_tooltip_data( 'size', 'default' );

		// Set a class.
		$class  = self::get_tooltip_class( $design, $target, $type, $size );

		// Build out label version.
		if ( 'label' === $type ) {

			// Determine which label class to filter based on field type.
			$lclass = 'section' === $field->type ? 'gsection_title' : 'gfield_label';

			// Determine what to attach to, since sections get handled differently due to markup differences.
			$attach = 'section' === $field->type ? '<h2' : '<label';

			// First add the class.
			$render = GF_Tooltips_Helper::str_replace_limit( $lclass, $lclass . ' ' . $class, $render );

			// Now add the tooltip.
			$render = GF_Tooltips_Helper::str_replace_limit( $attach, $attach . ' data-hint="' . esc_attr( $text ) . '"', $render );
		}

		// Build out icon version.
		if ( 'icon' === $type ) {

			// Get my icon.
			$icon   = self::get_tooltip_icon();

			// Build the markup.
			$setup  = '<span class="gf-icon ' . $class . '" data-hint="' . esc_attr( $text ) . '">' . $icon . '</span>';

			// Determine what to attach to, since sections get handled differently due to markup differences.
			$attach = 'section' === $field->type ? '</h2>' : '</label>';

			// And render it.
			$render = GF_Tooltips_Helper::str_replace_limit( $attach, $setup . $attach, $render );
		}

		// Build out single version.
		if ( 'single' === $type ) {

			// Get my icon.
			$icon   = self::get_tooltip_icon();

			// Build the markup.
			$setup  = '<span class="gf-icon ' . $class . '" data-hint="' . esc_attr( $text ) . '">' . $icon . '</span>';

			// Determine what to attach to, since sections get handled differently due to markup differences.
			$attach = 'section' === $field->type ? '</h2>' : '</div>';

			// Render it.
			$render = GF_Tooltips_Helper::str_replace_limit( $attach, $attach . $setup, $render );
		}

		// Return field content with new tooltip.
		return apply_filters( 'gf_tooltips_filter_display', $render, $type, $field, $form_id );
	}

	/**
	 * Generate and return the CSS class.
	 *
	 * @param  string $design  The user-selected design layout.
	 * @param  string $target  The user-selected placement.
	 * @param  string $type    The field type.
	 * @param  string $size    The tooltip size.
	 *
	 * @return string $class   The full class string used on a field.
	 */
	public static function get_tooltip_class( $design = '', $target = '', $type = '', $size = 'default' ) {

		// Set an empty.
		$class  = '';

		// Set the base class.
		$class .= 'hint--base hint--' . esc_attr( $design ) . ' hint--' . esc_attr( $target );

		// Check for a sizing setup.
		if ( ! empty( $size ) && 'default' !== $size ) {
			$class .= ' hint--has-size hint--' . esc_attr( $size );
		}

		// Check for bounce.
		if ( false !== $bounce = apply_filters( 'gf_tooltips_bounce', true ) ) {
			$class .= ' hint--bounce';
		}

		// Check for rounded.
		if ( false !== $rounded = apply_filters( 'gf_tooltips_rounded', true ) ) {
			$class .= ' hint--rounded';
		}

		// Check for no animation (bounce can't also be used).
		if ( false !== $animate = apply_filters( 'gf_tooltips_no_animated', false ) ) {
			$class .= ' hint--no-animate';
		}

		// Check for static.
		if ( false !== $static = apply_filters( 'gf_tooltips_static', false ) ) {
			$class .= ' hint--always';
		}

		// Return it filtered.
		return apply_filters( 'gf_tooltips_class', $class, $design, $target, $type );
	}

	/**
	 * Set the default with a filter and return,
	 *
	 * @return HTML  the tooltip icon.
	 */
	public static function get_tooltip_icon() {

		// Get the selected icon type.
		$icon   = GF_Tooltips_Helper::get_tooltip_data( 'icon', 'question' );

		// Set the icon class.
		$class  = apply_filters( 'gf_tooltips_icon_class', 'gftip gftip-' . esc_attr( $icon ) . '-circle' );

		// Return it.
		return apply_filters( 'gf_tooltips_icon', '<i class="' . esc_attr( $class ) . '"></i>' );
	}

	// End class.
}

// end exists check
}

// Instantiate our class
$GF_Tooltips_Front = new GF_Tooltips_Front();
$GF_Tooltips_Front->init();