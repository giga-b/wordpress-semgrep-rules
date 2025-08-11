(function( $ ) {

    // Counter updater
    setInterval( function() {

        $('.gamipress-time-based-reward:not(.can-claim), .gamipress-single-time-based-reward:not(.can-claim)').each(function() {
            gamipress_time_based_rewards_update_next_claim_counter( $(this) );
        });

    }, 1000 );

    // Claim button click
    $('body').on( 'click', '.gamipress-time-based-reward-claim-button', function(e) {

        var button = $(this);
        var time_based_reward = button.closest('.gamipress-time-based-reward');

        // Support for single template
        if( time_based_reward.length === 0 )
            time_based_reward = button.closest('.gamipress-single-time-based-reward');

        var submit_wrap = button.closest('.gamipress-time-based-reward-claim');
        var spinner = submit_wrap.find('.gamipress-spinner');
        var time_based_reward_id = button.data('id');

        // Disable the button
        button.prop( 'disabled', true );

        // Disable all claim buttons
        $('.gamipress-time-based-reward.can-claim .gamipress-time-based-reward-claim-button').prop( 'disabled', true );
        $('.gamipress-single-time-based-reward.can-claim .gamipress-time-based-reward-claim-button').prop( 'disabled', true );

        // Hide previous notices
        if( submit_wrap.find('.gamipress-time-based-reward-claim-response').length )
            submit_wrap.find('.gamipress-time-based-reward-claim-response').slideUp();

        // Show the spinner
        spinner.show();

        $.ajax( {
            url: gamipress_time_based_rewards.ajaxurl,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'gamipress_time_based_rewards_claim',
                nonce: gamipress_time_based_rewards.nonce,
                time_based_reward_id: time_based_reward_id
            },
            success: function( response ) {

                if( response.success === true ) {

                    // Remove any other rewards pop-up
                    $('.gamipress-time-based-rewards-popup-wrapper').remove();

                    // Display a popup with the earned elements
                    $('body').append( response.data.rewards_popup );

                    $('.gamipress-time-based-rewards-popup-wrapper').fadeIn( 400, function() {

                        // Reset the counter
                        time_based_reward.find('.gamipress-time-based-reward-next-reward-counter').text( response.data.next_claim );

                        // Remove class can-claim
                        time_based_reward.removeClass('can-claim');

                        // Hide the spinner
                        spinner.hide();

                        // Enable all claim buttons
                        $('.gamipress-time-based-reward.can-claim .gamipress-time-based-reward-claim-button').prop( 'disabled', false );
                        $('.gamipress-single-time-based-reward.can-claim .gamipress-time-based-reward-claim-button').prop( 'disabled', false );

                    } );
                } else {

                    // Ensure response wrap
                    if( submit_wrap.find('.gamipress-time-based-reward-claim-response').length === 0 )
                        submit_wrap.prepend('<div class="gamipress-time-based-reward-claim-response gamipress-notice" style="display: none;"></div>');

                    var response_wrap = submit_wrap.find('.gamipress-time-based-reward-claim-response');

                    // Add class gamipress-notice-success on successful unlock, if not will add the class gamipress-notice-error
                    response_wrap.addClass( 'gamipress-notice-' + ( response.success === true ? 'success' : 'error' ) );

                    // Update and show response messages
                    response_wrap.html( response.data );
                    response_wrap.slideDown();

                    // Hide the spinner
                    spinner.hide();

                    // Enable the button
                    button.prop( 'disabled', false );

                    // Enable all claim buttons
                    $('.gamipress-time-based-reward.can-claim .gamipress-time-based-reward-claim-button').prop( 'disabled', false );
                    $('.gamipress-single-time-based-reward.can-claim .gamipress-time-based-reward-claim-button').prop( 'disabled', false );

                }
            }
        });

    });

    // Popup button and overlay click
    $('body').on( 'click', '.gamipress-time-based-reward-popup-button, .gamipress-time-based-rewards-popup-overlay', function(e) {
        $(this).closest('.gamipress-time-based-rewards-popup-wrapper').fadeOut();
    });

})( jQuery );

/**
 * Updates the next claim counter of the given time-based reward
 *
 * @since 1.0.0
 *
 * @param time_based_reward
 */
function gamipress_time_based_rewards_update_next_claim_counter( time_based_reward ) {

    var counter_element = time_based_reward.find('.gamipress-time-based-reward-next-reward-counter');

    // 0H 0M 0S
    var counter = counter_element.text().trim();

    // Bail if counter is on 0
    if( counter === '0H 0M 0S' ) return;

    // array( '0H', '0M', '0S' ) or array( '0D', '0H', '0M', '0S' )
    var counter_parts = counter.split(' ');

    var has_days = false;

    var d = 0;
    var h = 0;
    var m = 0;
    var s = 0;

    if( counter_parts.length === 4 ) {
        has_days = true;

        d = parseInt( counter_parts[0].replace('D', '') );
        h = parseInt( counter_parts[1].replace('H', '') );
        m = parseInt( counter_parts[2].replace('M', '') );
        s = parseInt( counter_parts[3].replace('S', '') );
    } else {
        h = parseInt( counter_parts[0].replace('H', '') );
        m = parseInt( counter_parts[1].replace('M', '') );
        s = parseInt( counter_parts[2].replace('S', '') );
    }

    if( s < 0 ) s = 0;

    if( s === 0 ) {

        if( m < 0 ) m = 0;

        if( m === 0 ) {

            if( h < 0 ) h = 0;

            if( has_days && h === 0 ) {
                h = 24;

                if( d > 0 ) d--;
            }

            m = 60;

            if( h > 0 ) h--;
        }

        s = 60;

        if( m > 0 ) m--;
    }

    if( s > 0 )
        s--;

    var new_counter = ( has_days ? d + 'D ' : '' ) + h + 'H ' + m + 'M ' + s + 'S';

    // Update the counter element text
    counter_element.text(new_counter);

    if( ( d + h + m + s ) === 0 ) {
        // Add class can-claim
        time_based_reward.addClass('can-claim');

        // Enable claim button
        time_based_reward.find('.gamipress-time-based-reward-claim-button').prop( 'disabled', false );

        // TODO: Hide the counter on reach 0?
    }

}

// Unused since can't sync server GMT with javascript GMT date functions
function gamipress_time_based_rewards_get_human_next_claim_date( time_based_reward ) {
    var next_claim_date = time_based_reward.find('.gamipress-time-based-reward-next-reward-counter').data('next-date');

    if( ! next_claim_date.length ) return;

    var date = new Date();
    var next_date = new Date( next_claim_date ).getTime();

    if( date >= next_date )
        return '0H 0M 0S';

    var d, h, m, s,ms;
    ms = next_date - date;
    s = Math.floor(ms / 1000);
    m = Math.floor(s / 60);
    s = s % 60;
    h = Math.floor(m / 60);
    m = m % 60;
    d = Math.floor(h / 24);
    h = h % 24;

    return h + 'H ' + m + 'M ' + s + 'S';

}