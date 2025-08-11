(function( $ ) {

    // Listen for changes to our trigger type selectors
    $('.requirements-list').on( 'change', '.select-trigger-type', function() {

        // Grab our selected trigger type and custom selectors
        var trigger_type = $(this).val();
        var points_type = $(this).siblings('.select-coupons-points-type');
        var points_amount = $(this).siblings('.input-coupons-points-amount');
        var points_amount_text = $(this).siblings('.coupons-points-amount-text');
        var achievement_type = $(this).siblings('.select-coupons-achievement-type');
        var rank_type = $(this).siblings('.select-coupons-rank-type');

        // Hide all
        points_type.hide();
        points_amount.hide();
        points_amount_text.hide();
        achievement_type.hide();
        rank_type.hide();

        // Points type fields
        if( trigger_type === 'gamipress_coupons_redeem_points_coupon' ) {
            points_type.show();
            points_amount.show();
            points_amount_text.show();
        }

        // Achievement type fields
        if( trigger_type === 'gamipress_coupons_redeem_achievement_coupon' ) {
            achievement_type.show();

            // Trigger the change event
            achievement_type.trigger('change');
        }

        // Rank type fields
        if( trigger_type === 'gamipress_coupons_redeem_rank_coupon' ) {
            rank_type.show();

            // Trigger the change event
            rank_type.trigger('change');
        }

    });

    // Loop requirement list items to show/hide category select on initial load
    $('.requirements-list li').each(function() {

        // Grab our selected trigger type and custom selectors
        var trigger_type = $(this).find('.select-trigger-type').val();
        var points_type = $(this).find('.select-coupons-points-type');
        var points_amount = $(this).find('.input-coupons-points-amount');
        var points_amount_text = $(this).find('.coupons-points-amount-text');
        var achievement_type = $(this).find('.select-coupons-achievement-type');
        var rank_type = $(this).find('.select-coupons-rank-type');

        // Hide all
        points_type.hide();
        points_amount.hide();
        points_amount_text.hide();
        achievement_type.hide();
        rank_type.hide();

        // Points type fields
        if( trigger_type === 'gamipress_coupons_redeem_points_coupon' ) {
            points_type.show();
            points_amount.show();
            points_amount_text.show();
        }

        // Achievement type fields
        if( trigger_type === 'gamipress_coupons_redeem_achievement_coupon' ) {
            achievement_type.show();

            // Trigger the change event
            achievement_type.trigger('change');
        }

        // Rank type fields
        if( trigger_type === 'gamipress_coupons_redeem_rank_coupon' ) {
            rank_type.show();

            // Trigger the change event
            rank_type.trigger('change');
        }

    });

    // Listen for changes to our achievement type selectors
    $('.requirements-list').on( 'change', '.select-coupons-achievement-type', function() {

        // Grab our selected trigger type and custom selectors
        var $this                   = $(this);
        var trigger_type            = $(this).find('.select-trigger-type').val();
        var achievement_type        = $(this).siblings('.select-coupons-achievement-type').val();
        var requirement_id          = $(this).parent('li').attr('data-requirement-id');
        var requirement_type        = $(this).siblings('input[name="requirement_type"]').val();
        var achievement_id_select   = $(this).siblings('.select-coupons-achievement-id');

        // Just has some effect if is a specific trigger type
        if( trigger_type === 'gamipress_coupons_redeem_specific_achievement_coupon' ) {

            // Show a spinner
            $('<span class="spinner is-active" style="float: none;"></span>').insertAfter( $this );

            $.post(
                ajaxurl,
                {
                    action: 'gamipress_get_achievements_options_html',
                    requirement_id: requirement_id,
                    requirement_type: requirement_type,
                    achievement_type: achievement_type
                },
                function( response ) {

                    // Remove the spinner
                    $this.next('.spinner').remove();

                    achievement_id_select.html( response );
                    achievement_id_select.show();
                }
            );

        } else {
            achievement_id_select.hide();
        }

    });

    // Listen for changes to our rank type selectors
    $('.requirements-list').on( 'change', '.select-coupons-rank-type', function() {

        // Grab our selected trigger type and custom selectors
        var $this                   = $(this);
        var trigger_type            = $(this).find('.select-trigger-type').val();
        var rank_type               = $(this).siblings('.select-coupons-rank-type').val();
        var requirement_id          = $(this).parent('li').attr('data-requirement-id');
        var rank_id_select          = $(this).siblings('.select-coupons-rank-id');

        // Just has some effect if is a specific trigger type
        if( trigger_type === 'gamipress_coupons_redeem_specific_rank_coupon' ) {

            // Show a spinner
            $('<span class="spinner is-active" style="float: none;"></span>').insertAfter( $this );

            $.post(
                ajaxurl,
                {
                    action: 'gamipress_get_ranks_options_html',
                    requirement_id: requirement_id,
                    post_type: rank_type
                },
                function( response ) {

                    // Remove the spinner
                    $this.next('.spinner').remove();

                    rank_id_select.html( response );
                    rank_id_select.show();
                }
            );

        } else {
            rank_id_select.hide();
        }

    });

    $('.requirements-list').on( 'update_requirement_data', '.requirement-row', function( e, requirement_details, requirement ) {

        // Points type fields
        if( requirement_details.trigger_type === 'gamipress_coupons_redeem_points_coupon' ) {
            requirement_details.coupons_points_type = requirement.find( '.select-coupons-points-type' ).val();
            requirement_details.coupons_points_amount = requirement.find( '.input-coupons-points-amount' ).val();
        }

        // Achievement type fields
        if( requirement_details.trigger_type === 'gamipress_coupons_redeem_achievement_coupon' ) {
            requirement_details.coupons_achievement_type = requirement.find( '.select-coupons-achievement-type' ).val();
        }

        // Rank type fields
        if( requirement_details.trigger_type === 'gamipress_coupons_redeem_rank_coupon' ) {
            requirement_details.coupons_rank_type = requirement.find( '.select-coupons-rank-type' ).val();
        }

    });

})( jQuery );