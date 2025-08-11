(function( $ ) {

    var $body = $( 'body' );

    // Listen for unlock post with points button click
    $body.on( 'click', '.gamipress-restrict-content-unlock-post-with-points-button', function(e) {

        var button = $(this);
        var submit_wrap = button.closest('.gamipress-restrict-content-unlock-post-with-points');

        var confirmation = submit_wrap.find('.gamipress-restrict-content-unlock-post-with-points-confirmation');
        confirmation.slideDown();

        submit_wrap.find('.gamipress-restrict-content-unlock-post-with-points-confirm-button').on('click', function() {
            
            var spinner = submit_wrap.find('.gamipress-spinner');
            var data = {};

            // Unlock post data
            data = {
                action: 'gamipress_restrict_content_unlock_post_with_points',
                nonce: gamipress_restrict_content.nonce,
                post_id: button.data('id')
            };

            button.prop('disabled', true);

            // Show the spinner
            spinner.show();

            $.ajax({
                url: gamipress_restrict_content.ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: data,
                success: function( response ) {

                    // Ensure response wrap
                    if( submit_wrap.find('.gamipress-restrict-content-response').length === 0 ) {
                        submit_wrap.prepend('<div class="gamipress-restrict-content-response gamipress-notice" style="display: none;"></div>');
                    }

                    var response_wrap = submit_wrap.find('.gamipress-restrict-content-response');

                    // Add class gamipress-notice-success on successful unlock, if not will add the class gamipress-notice-error
                    response_wrap.addClass('gamipress-notice-' + (response.success === true ? 'success' : 'error'));
                    
                    // Update and show response messages
                    var message = (response.data.message !== undefined ? response.data.message : response.data);
                    response_wrap.html(message);
                    response_wrap.slideDown();
                    spinner.hide();

                    if( response.success === true ) {
                        confirmation.slideUp();
                        button.slideUp();
                        if( response.data.redirect !== undefined ) {
                            // Redirect to the given url
                            window.location.href = response.data.redirect;
                        } else {
                            // Refresh the page
                            location.reload(true);
                        }
                    } else {
                        // Enable the button
                        button.prop('disabled', false);
                    }
                }
            });
        });

        
        submit_wrap.find('.gamipress-restrict-content-unlock-post-with-points-cancel-button').on('click', function() {
            confirmation.slideUp();
        });
    });

    // Listen for unlock content with points button click
    $body.on( 'click', '.gamipress-restrict-content-unlock-content-with-points-button', function(e) {

        var button = $(this);
        var submit_wrap = button.closest('.gamipress-restrict-content-unlock-content-with-points');

        var confirmation = submit_wrap.find('.gamipress-restrict-content-unlock-content-with-points-confirmation');
        confirmation.slideDown();

        submit_wrap.find('.gamipress-restrict-content-unlock-content-with-points-confirm-button').on('click', function() {
            
            var spinner = submit_wrap.find('.gamipress-spinner');
            var data = {};

            // Unlock content data
            data = {
                action: 'gamipress_restrict_content_unlock_content_with_points',
                nonce: gamipress_restrict_content.nonce,
                content_id: button.data('id'),
                post_id: button.data('post-id')
            };
            

            button.prop('disabled', true);

            // Show the spinner
            spinner.show();

            $.ajax({
                url: gamipress_restrict_content.ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: data,
                success: function( response ) {

                    // Ensure response wrap
                    if( submit_wrap.find('.gamipress-restrict-content-response').length === 0 ) {
                        submit_wrap.prepend('<div class="gamipress-restrict-content-response gamipress-notice" style="display: none;"></div>');
                    }

                    var response_wrap = submit_wrap.find('.gamipress-restrict-content-response');

                    // Add class gamipress-notice-success on successful unlock, if not will add the class gamipress-notice-error
                    response_wrap.addClass('gamipress-notice-' + (response.success === true ? 'success' : 'error'));
                    
                    // Update and show response messages
                    var message = (response.data.message !== undefined ? response.data.message : response.data);
                    response_wrap.html(message);
                    response_wrap.slideDown();
                    spinner.hide();

                    if( response.success === true ) {
                        confirmation.slideUp();
                        button.slideUp();
                        if( response.data.redirect !== undefined ) {
                            // Redirect to the given url
                            window.location.href = response.data.redirect;
                        } else {
                            // Refresh the page
                            location.reload(true);
                        }
                    } else {
                        // Enable the button
                        button.prop('disabled', false);
                    }
                }
            });
        });

        
        submit_wrap.find('.gamipress-restrict-content-unlock-content-with-points-cancel-button').on('click', function() {
            confirmation.slideUp();
        });
    });

})( jQuery );
