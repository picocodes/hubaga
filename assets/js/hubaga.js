//Global hubaga_params

/*** Hubaga ***/
(function($) {
    "use strict";

    // Prepare the variables needed to start the train
    var ajax_url = hubaga_params.ajaxurl,
        checkoutUrl = hubaga_params.checkout_url,
        accountUrl = hubaga_params.account_url,
        nonce = hubaga_params.nonce,
        checkout = $('.hubaga-instacheck-wrapper'),
        loader = $('.hubaga-loader-wrapper');

    //When a user clicks on a buy button, init the checkout
    $('.hubaga-buy')
        .on('click',
            function(e) {

                // Make sure that this is really a buy button
                if ($(this).data('action') != 'hubaga_buy') {
                    return;
                }

                // Great! Prevent the default event behaviour
                e.preventDefault();

                // Hide the checkout overlay and display the loader
                $(checkout).hide();
                $(loader).show();

                // Post data
                var product = $(this).data('product'),
                    _link = $(this).attr('href');

                $.post(
                        ajax_url, {
                            nonce: nonce,
                            product: product,
                            action: 'hubaga_get_checkout'
                        },

                        function(html) {
                            var innerCheckout = $(checkout).find('.hubaga-instacheck-overlay');
                            $(innerCheckout).html(html);
                            $(checkout).show();
                            watchEvents($(innerCheckout).find('form'))
                        }
                    )
                    .fail(
                        //If there was a problem; redirect to the checkout page
                        function() {
                            window.location = _link;
                        })
                    .done(function() {
                        $(loader).hide();
                    });
            });

    var watchEvents = function(form) {

        //Submit form when a gateway is clicked
        $(form)
            .find('[name="gateway"]')
            .on('change click',
                function(e) {
                    $(form).trigger('submit');
                });

        //Show coupon
        $(form).
        find('.hubaga-show-coupon')
            .on('click',
                function(e) {
                    e.preventDefault();

                    var that = this;
                    var product = $(this).closest('form').find('[name="hubaga_buy"]').val();
                    var email = $(this).closest('form').find('[name="email"]').val();
                    var coupon = $(this).closest('.hubaga-coupon-grid').find('.hubaga-coupon-input').val();
                    if (coupon === '') {
                        $(this).closest('.hubaga-coupon-grid').find('.hubaga-coupon-notices').text(hubaga_params.empty_coupon);
                        return;
                    }

                    $(that).closest('form').fadeTo(360, 0.5);
                    $.post(
                        ajax_url, {
                            coupon: coupon,
                            action: 'hubaga_apply_coupon',
                            product: product,
                            email: email,
                            nonce: nonce
                        },
                        function(json) {

                            $(that).closest('form').fadeTo(360, 1);

                            if (json.result == 'success') {

                                $(that).closest('.hubaga-coupon-grid').hide();
                                $(that).closest('form').find('.hubaga-order-total').html(json.price);
                                $(that).closest('.hubaga-coupon-grid').find('.hubaga-coupon-notices').text('');
                                $(that).closest('form').find('.hubaga-coupon-notice').hide();

                            } else if (json.result == 'error') {

                                $(that).closest('.hubaga-coupon-grid').find('.hubaga-coupon-notices').text(json.error);

                            } else {

                                $(that).closest('.hubaga-coupon-grid').find('.hubaga-coupon-notices').text(hubaga_params.coupon_error);

                            }

                        }).fail(function() {
                        $(that).closest('form').fadeTo(360, 1);
                        $(that).closest('.hubaga-coupon-grid').find('.hubaga-coupon-notices').text(hubaga_params.coupon_error);
                    });

                });

        //Apply coupon
        $(form).
        find('.hubaga-coupon-btn')
            .on('click',
                function(e) {
                    e.preventDefault();
                    $(this).closest('form').find('.hubaga-coupon-grid').toggle();
                });

        $(form)
            .on('submit', function(e) {
                e.preventDefault();

                var data = $(this).serialize();
                var parent = $(this).closest('.hubaga-checkout');

                //Hide errors
                parent.find('.hubaga-error').hide();

                //Fade out the form
                parent.css({
                    opacity: 0.4
                })

                //Process the checkout
                hubagaProcessCheckout(data,
                    function(error) {
                        parent.find('.hubaga-error').html(error).show();
                    },
                    function(html) {
                        parent.find('.hubaga-form-wrapper').html(html);
                    }
                ).done(function() {
                    //Fade in the form
                    parent.css({
                        opacity: 1
                    })
                }).fail(
                    function(data) {
                        parent.find('.hubaga-error')
                            .text('Could not connect to the server. Please check your internet connection and try again.')
                            .show();
                    });
            })
    }

    //Global function to process checkouts
    window.hubagaProcessCheckout = function(data, error_cb, html_cb) {
        return $.post(
            hubaga_params.ajax_url,
            data,
            function(json) {
                if ($.isPlainObject(json)) {
                    if (json.action == 'redirect') {
                        window.location = json.body
                    }
                    if (json.action == 'error') {
                        error_cb(json.body)
                    }
                    if (json.action == 'html') {
                        html_cb(json.body)
                    }
                } else {
                    error_cb("The server returned an unknown error. Please try again.");
                }
            });
    }


    // When a user clicks on the show coupon link...
    $('.hubaga-show-coupon')
        .on('click',
            function(e) {
                e.preventDefault();
                $(this).closest('form').find('.hubaga-coupon-grid').toggle();
            });

    //When a user applies a coupon
    $('.hubaga-coupon-btn')
        .on('click',
            function(e) {

                e.preventDefault();
                var that = this;
                var product = $(this).closest('form').find('[name="hubaga_buy"]').val();
                var email = $(this).closest('form').find('[name="email"]').val();
                var coupon = $(this).closest('.hubaga-coupon-grid').find('.hubaga-coupon-input').val();
                if (coupon === '') {
                    $(this).closest('.hubaga-coupon-grid').find('.hubaga-coupon-notices').text(hubaga_params.empty_coupon);
                    return;
                }

                $(that).closest('form').fadeTo(360, 0.5);

                $.post(
                    hubaga_params.ajaxurl, {
                        coupon: coupon,
                        action: 'hubaga_apply_coupon',
                        product: product,
                        email: email,
                        nonce: hubaga_params.pc_nonce
                    },
                    function(json) {

                        $(that).closest('form').fadeTo(360, 1);

                        if (json.result == 'success') {

                            $(that).closest('.hubaga-coupon-grid').hide();
                            $(that).closest('form').find('.hubaga-order-total').html(json.price);
                            $(that).closest('.hubaga-coupon-grid').find('.hubaga-coupon-notices').text('');
                            $(that).closest('form').find('.hubaga-coupon-notice').hide();

                        } else if (json.result == 'error') {

                            $(that).closest('.hubaga-coupon-grid').find('.hubaga-coupon-notices').text(json.error);

                        } else {

                            $(that).closest('.hubaga-coupon-grid').find('.hubaga-coupon-notices').text(hubaga_params.coupon_error);

                        }

                    }).fail(function() {
                    $(that).closest('form').fadeTo(360, 1);
                    $(that).closest('.hubaga-coupon-grid').find('.hubaga-coupon-notices').text(hubaga_params.coupon_error);
                });

            });

})(jQuery);