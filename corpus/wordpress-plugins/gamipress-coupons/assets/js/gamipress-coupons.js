(function( $ ) {

    // Prevent the redeem coupon form submission from pressing the enter key

    $('body').on( 'submit', '.gamipress-coupons-form', function(e) {
        e.preventDefault();

        return false;
    });

    $('body').on( 'keypress', '.gamipress-coupons-form', function(e) {
        return e.keyCode != 13;
    });

    // Handle the redeem coupon form submission through clicking the redeem coupon button

    $('body').on( 'click', '.gamipress-coupons-form .gamipress-coupons-form-submit-button', function(e) {
        e.preventDefault();

        var $this               = $(this);
        var form                = $(this).closest('.gamipress-coupons-form');
        var submit_wrap         = form.find('.gamipress-coupons-form-submit');
        var code                = form.find('input[name="code"]').val();

        // Ensure response wrap
        if( submit_wrap.find('.gamipress-coupons-form-response').length === 0 ) {
            submit_wrap.prepend('<div class="gamipress-coupons-form-response" style="display: none;"></div>')
        }

        var response_wrap = submit_wrap.find('.gamipress-coupons-form-response');

        // Check the code
        if( ! code.length ) {
            response_wrap.addClass( 'gamipress-coupons-error' );
            response_wrap.html( gamipress_coupons.empty_code_error );
            response_wrap.slideDown();
            return;
        }

        // Disable the submit button
        $this.prop( 'disabled', true );

        // Hide previous notices
        if( response_wrap.length ) {
            response_wrap.slideUp()
        }

        // Show the loading spinner
        submit_wrap.find( '.gamipress-spinner' ).show();

        /**
         * Event before perform a coupon request
         * Example:  $('body').on( 'gamipress_coupons_before_redeem_coupon_request', '.gamipress-coupons-form', function(e) {});
         *
         * @since 1.0.3
         *
         * @selector    .gamipress-coupons-form
         * @event       gamipress_coupons_before_redeem_coupon_request
         */
        form.trigger( 'gamipress_coupons_before_redeem_coupon_request' );

        $.ajax({
            url: gamipress_coupons.ajaxurl,
            method: 'POST',
            data: form.serialize() + '&action=gamipress_coupons_redeem_coupon',
            success: function( response ) {

                // Add class gamipress-coupons-success on successful coupon, if not will add the class gamipress-coupons-error
                response_wrap.addClass( 'gamipress-coupons-' + ( response.success === true ? 'success' : 'error' ) );

                // Update and show response messages
                response_wrap.html( ( response.data.message !== undefined ? response.data.message : response.data ) );
                response_wrap.slideDown();

                // Restore submit button
                $this.prop( 'disabled', false );

                // Hide the loading spinner
                submit_wrap.find( '.gamipress-spinner' ).hide();

                /**
                 * Triggers 'gamipress_coupons_redeem_coupon_success' on success and 'gamipress_coupons_redeem_coupon_error' on error
                 *
                 * @since 1.0.3
                 *
                 * @selector    .gamipress-coupons-form
                 * @event       gamipress_coupons_redeem_coupon_success|gamipress_coupons_redeem_coupon_error
                 */
                form.trigger( 'gamipress_coupons_redeem_coupon_' + ( response.success === true ? 'success' : 'error' ) );

                /**
                 * Event after perform a coupon request
                 *
                 * @since 1.0.3
                 *
                 * @selector    .gamipress-coupons-form
                 * @event       gamipress_coupons_after_redeem_coupon_request
                 */
                form.trigger( 'gamipress_coupons_after_redeem_coupon_request' );

                // Apply response redirect
                if( response.data.redirect === true
                    && response.data.redirect_url !== undefined
                    && response.data.redirect_url.length ) {

                    window.location.href = response.data.redirect_url;

                }

            },
            error: function( response ) {

                /**
                 * Triggers coupon error
                 *
                 * @since 1.0.3
                 *
                 * @selector    .gamipress-coupons-form
                 * @event       gamipress_coupons_redeem_coupon_error
                 */
                form.trigger( 'gamipress_coupons_redeem_coupon_error' );

                /**
                 * Event after perform a coupon request
                 *
                 * @since 1.0.3
                 *
                 * @selector    .gamipress-coupons-form
                 * @event       gamipress_coupons_after_redeem_coupon_request
                 */
                form.trigger( 'gamipress_coupons_after_redeem_coupon_request' );

            }
        });
    });

})( jQuery );