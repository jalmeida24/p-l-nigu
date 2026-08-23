( function( wp, wc ) {
	var registerPaymentMethod = wc.wcBlocksRegistry.registerPaymentMethod;
	var getSetting = wc.wcSettings.getSetting;
	var el = wp.element.createElement;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var decodeEntities = wp.htmlEntities.decodeEntities;

	var settings = getSetting( 'appypay_gpo_data', {} );
	var label = decodeEntities( settings.title || 'Multicaixa Express' );

	var Content = function( props ) {
		var emitResponse = props.emitResponse;
		var onPaymentSetup = props.eventRegistration.onPaymentSetup;
		var mobileState = useState( '' );
		var mobile = mobileState[ 0 ];
		var setMobile = mobileState[ 1 ];

		useEffect(
			function() {
				return onPaymentSetup( function() {
					if ( ! mobile ) {
						return {
							type: emitResponse.responseTypes.ERROR,
							message: settings.mobileRequiredText || 'Please add your mobile number',
						};
					}

					return {
						type: emitResponse.responseTypes.SUCCESS,
						meta: {
							paymentMethodData: {
								gpo_mobile: mobile,
							},
						},
					};
				} );
			},
			[ mobile, onPaymentSetup ]
		);

		return el(
			'div',
			{ className: 'wc-appypay-gpo-fields' },
			settings.description ? el( 'p', {}, decodeEntities( settings.description ) ) : null,
			el( 'input', {
				type: 'text',
				className: 'input-text',
				placeholder: settings.mobileLabel || 'Mobile Number',
				value: mobile,
				onChange: function( event ) {
					setMobile( event.target.value );
				},
			} )
		);
	};

	registerPaymentMethod( {
		name: 'appypay_gpo',
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
