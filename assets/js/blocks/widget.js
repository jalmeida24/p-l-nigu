( function( wp, wc ) {
	var registerPaymentMethod = wc.wcBlocksRegistry.registerPaymentMethod;
	var getSetting = wc.wcSettings.getSetting;
	var el = wp.element.createElement;
	var decodeEntities = wp.htmlEntities.decodeEntities;

	var settings = getSetting( 'appypay_widget_data', {} );
	var label = decodeEntities( settings.title || 'AppyPay Payment' );

	var Content = function() {
		return settings.description
			? el( 'p', {}, decodeEntities( settings.description ) )
			: null;
	};

	registerPaymentMethod( {
		name: 'appypay_widget',
		label: label,
		ariaLabel: label,
		canMakePayment: function() {
			return true;
		},
		content: el( Content ),
		edit: el( Content ),
		supports: {
			features: [ 'products' ],
		},
	} );
} )( window.wp, window.wc );
