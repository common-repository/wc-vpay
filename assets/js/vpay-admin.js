jQuery( function( $ ) {
	'use strict';

	/**
	 * Object to handle VPay admin functions.
	 */
	var wc_vpay_admin = {
		/**
		 * Initialize.
		 */
		init: function() {

			// Toggle api key settings.
			$( document.body ).on( 'change', '#woocommerce_vpay_testmode', function() {
				var test_secret_key = $( '#woocommerce_vpay_test_secret_key' ).parents( 'tr' ).eq( 0 ),
					test_public_key = $( '#woocommerce_vpay_test_public_key' ).parents( 'tr' ).eq( 0 ),
					live_secret_key = $( '#woocommerce_vpay_live_secret_key' ).parents( 'tr' ).eq( 0 ),
					live_public_key = $( '#woocommerce_vpay_live_public_key' ).parents( 'tr' ).eq( 0 );

				if ( $( this ).is( ':checked' ) ) {
					test_secret_key.show();
					test_public_key.show();
					live_secret_key.hide();
					live_public_key.hide();
				} else {
					test_secret_key.hide();
					test_public_key.hide();
					live_secret_key.show();
					live_public_key.show();
				}
			} );

			$( '#woocommerce_vpay_testmode' ).change();


			$( ".wc-vpay-payment-icons" ).select2( {
				templateResult: formatVPayPaymentIcons,
				templateSelection: formatVPayPaymentIconDisplay
			} );

		}
	};

	function formatVPayPaymentIcons( payment_method ) {
		if ( !payment_method.id ) {
			return payment_method.text;
		}

		var $payment_method = $(
			'<span><img src=" ' + wc_vpay_admin_params.plugin_url + '/assets/images/' + payment_method.element.value.toLowerCase() + '.png" class="img-flag" style="height: 15px; weight:18px;" /> ' + payment_method.text + '</span>'
		);

		return $payment_method;
	};

	function formatVPayPaymentIconDisplay( payment_method ) {
		return payment_method.text;
	};

	wc_vpay_admin.init();

} );
