<?php

class GF_Tooltips_Front
{

	/**
	 * This is our constructor
	 *
	 * @return GF_Tooltips
	 */
	public function __construct() {

		// GF specific
		add_action			(	'gform_enqueue_scripts',				array(	$this,	'scripts_styles'		),	10,	2	);
		add_action			(	'gform_field_css_class',				array(	$this,	'set_tooltip_class'		),	10,	3	);
		add_filter			(	'gform_field_content',					array(	$this,	'set_tooltip_display'	),	10,	5	);

	}

	/**
	 * [get_tooltip_data description]
	 * @param  boolean $key [description]
	 * @return [type]       [description]
	 */
	static function get_tooltip_data( $key = false, $default = '' ) {

		$data	= get_option( 'gf-tooltips' );

		if ( ! $data )
			return false;

		if ( ! $key )
			return $data;

		// check settings and return choice or default
		$item	= isset( $data[ $key ] ) && ! empty( $data[ $key ] ) ? $data[ $key ] : $default;

		return $item;

	}

	/**
	 * [set_field_class description]
	 * @param [type] $classes [description]
	 * @param [type] $field   [description]
	 * @param [type] $form    [description]
	 */

	public function set_tooltip_class( $classes, $field, $form ){

		// grab option field
		$data	= get_option( 'gf-tooltips' );

		// bail if we have nothing
		if ( ! $data )
			return $classes;

 		// bail if no tooltip actually exists
 		if ( ! isset( $field['customTooltip'] ) || isset( $field['customTooltip'] ) && empty( $field['customTooltip'] ) )
			return $classes;

		// add class for label tooltip
		if ( isset ( $data['style'] ) && $data['style'] == 'label' )
			$classes .= ' gf-tooltip gf-tooltip-label';

		// add class for icon tooltip
		if ( isset ( $data['style'] ) && $data['style'] == 'icon' )
			$classes .= ' gf-tooltip gf-tooltip-icon';

		// add class for icon tooltip
		if ( isset ( $data['style'] ) && $data['style'] == 'single' )
			$classes .= ' gf-tooltip gf-tooltip-single';

		return $classes;
	}

	/**
	 * [set_tooltip_display description]
	 * @param [type] $content [description]
	 * @param [type] $field   [description]
	 * @param [type] $value   [description]
	 * @param [type] $lead_id [description]
	 * @param [type] $form_id [description]
	 */
	public function set_tooltip_display( $content, $field, $value, $lead_id, $form_id ) {

		// this is only for the front end
		if ( is_admin() )
			return $content;

		// grab our tooltip style first
 		$style	= self::get_tooltip_data( 'style', 'icon' );

 		// bail if we have no position set
 		if ( ! $style )
 			return $content;

 		// bail if no tooltip actually exists
 		if ( ! isset( $field['customTooltip'] ) || isset( $field['customTooltip'] ) && empty( $field['customTooltip'] ) )
 			return $content;

 		// get our content and sanitize it
   		$tooltip	= esc_attr( $field['customTooltip'] );

   		// build out label version
   		if ( $style == 'label' )
   			$content	= self::render_tooltip_label( $content, $tooltip );

   		// build out icon version
   		if ( $style == 'icon' )
   			$content	= self::render_tooltip_icon( $content, $tooltip );

   		// build out single version
   		if ( $style == 'single' )
   			$content	= self::render_tooltip_single( $content, $tooltip );

		// return field content with new tooltip
		return $content;

	}

	/**
	 * filter the existing label markup to add the data attribute
	 * @param  [type] $content [description]
	 * @param  [type] $tooltip [description]
	 * @return [type]          [description]
	 */
	static function render_tooltip_label( $content, $tooltip ) {

		$content	= str_replace( '<label', '<label data-tooltip="' . $tooltip . '"', $content );

		return $content;

	}

	/**
	 * [render_tooltip_icon description]
	 * @param  [type] $content [description]
	 * @param  [type] $tooltip [description]
	 * @return [type]          [description]
	 */
	static function render_tooltip_icon( $content, $tooltip ) {

		$img	= self::get_tooltip_icon_img( false );

		$icon	= '<img src="'.esc_url( $img ).'" class="gf-tooltip-icon-img" data-tooltip="' . $tooltip . '">';

		// drop our tooltip on there
		$content	= str_replace( '</label>', $icon.'</label>', $content );

		// send it back
		return $content;

	}

	/**
	 * [render_tooltip_single description]
	 * @param  [type] $content [description]
	 * @param  [type] $tooltip [description]
	 * @return [type]          [description]
	 */
	static function render_tooltip_single( $content, $tooltip ) {

		$img	= self::get_tooltip_icon_img( false );

		$icon	= '<span class="gf-tooltip-icon-wrap"><img src="'.esc_url( $img ).'" class="gf-tooltip-icon-img" data-tooltip="' . $tooltip . '"></span>';

		// drop our tooltip on there
		$content	= str_replace( '</div>', '</div>'.$icon, $content );

		// send it back
		return $content;

	}

	/**
	 * [get_tooltip_icon_img description]
	 * @return [type] [description]
	 */
	static function get_tooltip_icon_img() {

		// set the default with a filter
		$icon	= apply_filters( 'gf_tooltips_icon_img', plugins_url( '/img/tooltip-icon.png', __FILE__) );

		// return without markup i.e. the URL of the icon
		return esc_url( $icon );

	}

	/**
	 * [get_tooltip_customs description]
	 * @param  boolean $option [description]
	 * @return [type]          [description]
	 */
	static function get_tooltip_customs( $option = false ) {

		$showdelay	= apply_filters( 'gf_tooltips_show_delay', 700 );
		$showsolo	= apply_filters( 'gf_tooltips_show_solo', true );

		$hidedelay	= apply_filters( 'gf_tooltips_hide_delay', 300 );

		$data	= array(
			'showdelay'	=> (int) $showdelay,
			'showsolo'	=> (bool) $showsolo,
			'hidedelay'	=> (int) $hidedelay,
		);

		// return the whole array
		if ( ! $option )
			return $data;

		// return the specified option
		return $data[ $option ];

	}

	/**
	 * [scripts_styles description]
	 * @return [type] [description]
	 */
	public function scripts_styles( $form, $is_ajax ) {

		wp_enqueue_style( 'gf-tooltips', plugins_url('/css/gf-tooltips.css', __FILE__), array(), GFT_VER, 'all' );

		if( SCRIPT_DEBUG ) :
		// load non-minified version and debug script with cache breaking timestamp if set
			wp_enqueue_script( 'qtip-tips',		plugins_url( '/js/jquery.qtip.js',			__FILE__ ),	array( 'jquery' ),	time(),	true	);
			wp_enqueue_script( 'qtip-debug',	plugins_url( '/js/jquery.qtip.debug.js',	__FILE__ ),	array( 'jquery' ),	time(),	true	);

		else:
 		// load the normal minified
			wp_enqueue_script( 'qtip-tips-min', plugins_url( '/js/jquery.qtip.min.js',		__FILE__ ),	array( 'jquery' ),	'1.0',	true	);

		endif;
		// now load our scripts and localize
		wp_enqueue_script( 'gf-tooltips', plugins_url( '/js/gf-tooltips.js', __FILE__), array( 'jquery' ), GFT_VER, true );
		// set up variables for later use
		wp_localize_script( 'gf-tooltips', 'ttVars', array(
			'target'		=> self::get_tooltip_data( 'target', 'topRight' ),
			'location'		=> self::get_tooltip_data( 'location', 'bottomLeft' ),
			'design'		=> self::get_tooltip_data( 'design', 'light' ),
			'showdelay'		=> self::get_tooltip_customs( 'showdelay' ),
			'showsolo'		=> self::get_tooltip_customs( 'showsolo' ),
			'hidedelay'		=> self::get_tooltip_customs( 'hidedelay' ),
			)
		);

	}

/// end class
}

new GF_Tooltips_Front();