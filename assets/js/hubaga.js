
//Global hubaga_params

/*** Hubaga ***/
( function( $ ) {
	"use strict";

	// When a user clicks on the show coupon link...
	$('.hubaga-show-coupon')
		.on('click',
			function( e ) {
				e.preventDefault();
				$ ( this ).closest( 'form' ).find( '.hubaga-coupon-grid' ).toggle();
			});

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
					$( this ).closest( '.hubaga-coupon-grid' ).find( '.hubaga-coupon-notices' ).text( hubaga_params.empty_coupon );
					return;
				}

				$( that ).closest( 'form' ).fadeTo( 360, 0.5 );

				$.post(
					hubaga_params.ajaxurl,
					{
						coupon: coupon,
						action: 'hubaga_apply_coupon',
						product: product,
						email: email,
						nonce: hubaga_params.pc_nonce
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

							$( that ).closest( '.hubaga-coupon-grid' ).find( '.hubaga-coupon-notices' ).text( hubaga_params.coupon_error );

						}

				}).fail( function(){
					$( that ).closest( 'form' ).fadeTo( 360, 1 );
					$( that ).closest( '.hubaga-coupon-grid' ).find( '.hubaga-coupon-notices' ).text( hubaga_params.coupon_error );
				});

			});

	$('.hubaga-field')
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

} )( jQuery );
