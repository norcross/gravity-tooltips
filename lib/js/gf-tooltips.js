//***************************************************************
// apply tooltip to chosen element
//***************************************************************

function applyTooltip( element, tooltip ) {
	//todo: add options for positioning http://craigsworks.com/projects/qtip/docs/tutorials/#position

	jQuery( element ).qtip({
		content:	tooltip,
		show: {
			delay: ttVars.showdelay,
			solo: ttVars.showsolo,
		},
		hide: {
			when: 'mouseout',
			fixed: true,
			delay: ttVars.hidedelay,
			effect: 'fade'
		},
		position: {
			corner: {
				target: ttVars.target,
				tooltip: ttVars.location
			}
		},
		style: {
	//		width:	300,
	//		padding: 10,
	//		color:	'black',
	//		tip:	'bottomLeft',
	//		border: {
	//			width:	4,
	//			radius:	5,
	//			color:	'#666'
	//		},
			name:	ttVars.design
		}
	});
}

//***************************************************************
// reload the icon tooltips
//***************************************************************

function reloadIconTooltip() {

	jQuery( 'li.gf-tooltip-icon' ).each(function() {

		// set my element
		element	= jQuery( this ).find( 'img.gf-tooltip-icon-img' );

		// get my content
		tooltip	= jQuery( this ).find( 'img.gf-tooltip-icon-img' ).data( 'tooltip' );

		// bail if it's missing
		if ( tooltip === '' )
			return;

		// render the tooltip
		applyTooltip( element, tooltip );

	});
}

//***************************************************************
// reload the label tooltips
//***************************************************************

function reloadLabelTooltip() {

	jQuery( 'li.gf-tooltip-label' ).each(function() {

		// set my element
		element	= jQuery( this ).find( 'label' );

		// get my content
		tooltip	= jQuery( this ).find( 'label' ).data( 'tooltip' );

		// bail if it's missing
		if ( tooltip === '' )
			return;

		// render the tooltip
		applyTooltip( element, tooltip );

	});
}

//***************************************************************
// reload the single tooltips
//***************************************************************

function reloadSingleTooltip() {

	jQuery( 'li.gf-tooltip-single' ).each(function() {

		// set my element
		element	= jQuery( this ).find( 'span.gf-tooltip-icon-wrap img' );

		// get my content
		tooltip	= jQuery( this ).find( 'span.gf-tooltip-icon-wrap img' ).data( 'tooltip' );

		// bail if it's missing
		if ( tooltip === '' )
			return;

		// render the tooltip
		applyTooltip( element, tooltip );

	});
}

//***************************************************************
// start the engine
//***************************************************************

jQuery( document ).ready( function($) {

//***************************************************************
// set the tooltip variable before we pass it numerous times
//***************************************************************

	var element;
	var tooltip;

//***************************************************************
// load and apply icon tooltips
//***************************************************************

	jQuery( 'li.gf-tooltip-icon' ).each(function() {

		// set my element
		element	= jQuery( this ).find( 'img.gf-tooltip-icon-img' );

		// get my content
		tooltip	= jQuery( this ).find( 'img.gf-tooltip-icon-img' ).data( 'tooltip' );

		// bail if it's missing
		if ( tooltip === '' )
			return;

		// render the tooltip
		applyTooltip( element, tooltip );

	});

//***************************************************************
// load and apply label tooltips
//***************************************************************

	jQuery( 'li.gf-tooltip-label' ).each(function() {

		// set my element
		element	= jQuery( this ).find( 'label' );

		// get my content
		tooltip	= jQuery( this ).find( 'label' ).data( 'tooltip' );

		// bail if it's missing
		if ( tooltip === '' )
			return;

		// render the tooltip
		applyTooltip( element, tooltip );

	});


//***************************************************************
// load and apply single tooltips
//***************************************************************

	jQuery( 'li.gf-tooltip-single' ).each(function() {

		// set my element
		element	= jQuery( this ).find( 'span.gf-tooltip-icon-wrap img' );

		// get my content
		tooltip	= jQuery( this ).find( 'span.gf-tooltip-icon-wrap img' ).data( 'tooltip' );

		// bail if it's missing
		if ( tooltip === '' )
			return;

		// render the tooltip
		applyTooltip( element, tooltip );

	});

//***************************************************************
// Fire tooltip when multipage is used
//***************************************************************

	jQuery( document ).bind( 'gform_page_loaded', function( event, form_id, current_page ){

		reloadIconTooltip();
		reloadLabelTooltip();
		reloadSingleTooltip();

	});

//***************************************************************
// You're still here? It's over. Go home.
//***************************************************************

});
