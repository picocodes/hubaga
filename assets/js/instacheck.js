//Global instacheck_params

/*** Instacheck. Checkout at the speed of a single click ***/
( function( $ ) {
	"use strict";

	//Whether or not instacheck can be used
	var is_instacheck = function() {
		return $( window ).height() > 500 && $( window ).width() > 350
	}

	// Prepare the variables needed to start the train
	var ajax_url    = instacheck_params.ajaxurl,
		checkoutUrl = instacheck_params.checkout_url,
		accountUrl  = instacheck_params.account_url,
		nonce       = instacheck_params.pc_nonce,
		checkout	= $( '.hubaga-instacheck-wrapper' ),
		loader  	= $( '.hubaga-loader-wrapper' );

	// Init instacheck when a buy button is clicked
	$('.hubaga-buy')
		.on('click',
			function( e ) {

				//Ensure that instacheck is supported
				if( !is_instacheck() ){
					return;
				}

				// Make sure that this is really a buy button
				if( $( this ).data( 'action' ) != 'hubaga_buy' ){
					return;
				}

				// Great! Prevent the default event behaviour
				e.preventDefault();

				// Post data
				var product = $( this ).data( 'product' ),
					_link	= $( this ).attr( 'href' );

				fetchCheckout( {
					nonce: nonce,
					action: 'hubaga_get_checkout',
					fetchedBy: 'instacheck',
					product: product
				} ).fail(
					//If there was a problem; redirect to the checkout page
					function() {
						window.location = _link;
				});
			});

	/* Helper function to fetch the checkouts */
	var fetchCheckout = function( data ) {

		// Hide the checkout overlay and display the loader
		$( checkout ).hide();
		$( loader ).show();

		// Post the data then return a promise
		return $.post(
			ajax_url,
			data,
			function( html ) {

				//If this is JSON....
				if( $.isPlainObject( html ) ) {
					if ( html.action == 'redirect' ) {
						window.location = html.url
					}
				} else {
					var innerCheckout = $( checkout ).find( '.hubaga-instacheck-overlay' );
					$( innerCheckout ).html( html );

					//Appends a close button
					appendCloseButton( innerCheckout, checkout );

					//Updates event handlers
					updateCheckoutHandlers( innerCheckout );
				}
				//Display the checkout
				$( loader ).hide();
				$( checkout ).show();
			});
	}

	// Appends a button to el that closes victiom when clicked
	var appendCloseButton = function( el, victim ) {
		return $( '<span class="hubaga-close">&times;</span>' )
					.appendTo( el )
						.on( 'click.pc_close',
							function( e ) {
								e.preventDefault();
								$( victim ).hide();
							});
	}

	//Updates checkout event handlers
	var updateCheckoutHandlers = function( el ){

		var checkoutForm 	= $( el ).find( '.hubaga-checkout-form:not(.instacheck_disabled)' );

		//Whenever the user clicks outside our checkout overlay
		$( document )
			.on('mouseup.hubaga-instacheck', function(e){
				if(!el.is(e.target) && el.has(e.target).length === 0 ) {
					$( checkout ).hide();
				}
			});

		//Handle form submissions
		$( checkoutForm )
			.on( 'submit.hubaga-instacheck',
				function( e ){
					e.preventDefault();
					var data   = $( checkoutForm ).serialize();
					data = data + '&nonce=' + nonce;
					fetchCheckout( data )
						.fail(
							function( data ) {
								window.location = checkoutUrl + '?' + $( checkoutForm ).serialize();
							});
				});

		//Submit form when a gateway is clicked
		$( checkoutForm )
			.find( '[name="gateway"]' )
				.on( 'change.hubaga-instacheck',
					function( e ){
						$( checkoutForm ).trigger( 'submit' );
					});

		// When a user clicks on the show coupon link...
		$( checkoutForm ).
			find('.hubaga-show-coupon')
				.on('click.hubaga-instacheck',
					function( e ) {
						e.preventDefault();
						$ ( this ).closest( 'form' ).find( '.hubaga-coupon-grid' ).toggle();
					});
		
		// Input field events
		$( checkoutForm ).
			find('.hubaga-field')
				.on( 'focus', function(){
					$(this).addClass( 'hubaga-is-focused' )
				})

				.on( 'blur', function(){
					$(this).removeClass( 'hubaga-is-focused' )
				})

				.on( 'keyup', function(){
					if ($(this).val().length === 0) {
		  				$(this).addClass('hubaga-is-empty');
					} else {
		  				$(this).removeClass('hubaga-is-empty');
					}
				})


		//When a user applies a coupon
	$('.hubaga-coupon-btn')
		.on('click',
			function( e ) {

				e.preventDefault();
				var that   = this;
				var product= $( this ).closest( 'form' ).find( '[name="hubaga_buy"]' ).val();
				var email  = $( this ).closest( 'form' ).find( '[name="email"]' ).val();
				var coupon = $( this ).closest( '.hubaga-coupon-grid' ).find( '.hubaga-coupon-input' ).val();
				if( coupon === '' ) {
					$( this ).closest( '.hubaga-coupon-grid' ).find( '.hubaga-coupon-notices' ).text( instacheck_params.empty_coupon );
					return;
				}

				$( that ).closest( 'form' ).fadeTo( 360, 0.5 );

				$.post(
					ajax_url,
					{
						coupon: coupon,
						action: 'hubaga_apply_coupon',
						product: product,
						email: email,
						nonce: nonce
					},
					function ( json ) {

						$( that ).closest( 'form' ).fadeTo( 360, 1 );

						if ( json.result == 'success' ) {

							$( that ).closest( '.hubaga-coupon-grid' ).hide();
							$( that ).closest( 'form' ).find( '.hubaga-order-total' ).html( json.price );
							$( that ).closest( '.hubaga-coupon-grid' ).find( '.hubaga-coupon-notices' ).text( '' );
							$( that ).closest( 'form' ).find( '.hubaga-coupon-notice' ).hide();

						} else if( json.result == 'error' ){

							$( that ).closest( '.hubaga-coupon-grid' ).find( '.hubaga-coupon-notices' ).text( json.error );

						}else {

							$( that ).closest( '.hubaga-coupon-grid' ).find( '.hubaga-coupon-notices' ).text( instacheck_params.coupon_error );

						}

				}).fail( function(){
					$( that ).closest( 'form' ).fadeTo( 360, 1 );
					$( that ).closest( '.hubaga-coupon-grid' ).find( '.hubaga-coupon-notices' ).text( instacheck_params.coupon_error );
				});

			});
	}

} )( jQuery );
