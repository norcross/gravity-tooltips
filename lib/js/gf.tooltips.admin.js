//***************************************************************
// start the engine
//***************************************************************

jQuery( document ).ready( function($) {

//gforms_edit_form

	if ( typeof fieldSettings === 'undefined' )
		return;

	// pull in our allowed types
	var allowed	= gftipsAdmin.fieldtypes;

	// loop through each possible field type and add our new field
	jQuery.each( fieldSettings, function( type, items ) {

		if( jQuery.inArray( type, allowed ) > -1 ){
			fieldSettings[ type ] += ', .custom_tooltip_setting';
		}

	});

	// bind to the load field settings event to initialize the field
	jQuery( document ).bind( 'gform_load_field_settings', function( event, field, form ) {
		jQuery( '#custom_tooltip' ).val( field['customTooltip'] == undefined ? '' : field['customTooltip'] );
	});

//***************************************************************
// You're still here? It's over. Go home.
//***************************************************************

});
