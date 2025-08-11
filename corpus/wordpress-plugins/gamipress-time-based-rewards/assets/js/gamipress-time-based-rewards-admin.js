(function( $ ) {

    // Rewards
    // -------------------------------------

    var post_type_first_change = true;

    // Hide achievements and ranks fields
    $('.cmb2-id--gamipress-time-based-rewards-rewards .cmb-row[class*="-achievement-id"]').hide();
    $('.cmb2-id--gamipress-time-based-rewards-rewards .cmb-row[class*="-rank-id"]').hide();

    // Post type
    $('body').on('change', '.cmb2-id--gamipress-time-based-rewards-rewards select[id$="post_type"]', function() {

        var post_type = $(this).val();
        var container = $(this).closest('.cmb-repeatable-grouping');
        var min_row = container.find('.cmb-row[class*="-min"]');
        var max_row = container.find('.cmb-row[class*="-max"]');
        var achievement_row = container.find('.cmb-row[class*="-achievement-id"]');
        var achievement_field = container.find('select[id$="achievement_id"]');
        var rank_row = container.find('.cmb-row[class*="-rank-id"]');
        var rank_field = container.find('select[id$="rank_id"]');
        var achievement_type_row = container.find('.cmb-row[class*="-achievement-type"]');
        var achievement_type_field = container.find('select[id$="achievement_type"]');
        var label_field = container.find('input[id$="label"]');
        var label = '';

        var show_achievement = false;
        var show_rank = false;
        var show_achievement_type = false;
        var show_min = false;
        var show_max = false;
        var show_cb = ( post_type_first_change ? 'show' : 'slideDown' );
        var hide_cb = ( post_type_first_change ? 'hide' : 'slideUp' );

        if( post_type in gamipress_time_based_rewards_admin.points_types ) {

            show_min = true;
            show_max = true;

            label = gamipress_time_based_rewards_admin.points_type_label;

        } else if( post_type === 'random_achievement' ) {

            show_achievement_type = true;
            show_min = true;
            show_max = true;

            // Reset value and update the post type
            if( ! post_type_first_change )
                achievement_type_field
                    .val('').trigger('change');

            label = gamipress_time_based_rewards_admin.achievement_type_label;

        } else if( post_type in gamipress_time_based_rewards_admin.achievement_types ) {

            show_achievement = true;
            show_min = true;
            show_max = true;

            // Reset value and update the post type
            if( ! post_type_first_change )
                achievement_field
                    .val('').trigger('change')
                    .data( 'post-type', post_type );

            label = gamipress_time_based_rewards_admin.achievement_type_label;

        } else if( post_type in gamipress_time_based_rewards_admin.rank_types ) {

            show_rank = true;

            // Reset value and update the post type
            if( ! post_type_first_change )
                rank_field
                    .val('').trigger('change')
                    .data( 'post-type', post_type );

            label = gamipress_time_based_rewards_admin.rank_type_label;

        }

        // Toggle fields visibility
        achievement_row[( show_achievement ? show_cb : hide_cb)]();
        rank_row[( show_rank ? show_cb : hide_cb)]();
        achievement_type_row[( show_achievement_type ? show_cb : hide_cb)]();

        // Toggle min and max fields visibility
        min_row[( show_min ? show_cb : hide_cb)]();
        max_row[( show_max ? show_cb : hide_cb)]();

        // Generate the label

        // Force regenerate on empty labels
        if( label_field.val() === '' )
            label_field.attr( 'data-changed', 'false' );

        if( label_field.attr( 'data-changed' ) === 'false' ) {
            label_field.val(label)
        }
    });

    $('.cmb2-id--gamipress-time-based-rewards-rewards select[id$="post_type"]').trigger('change');

    post_type_first_change = false;

    // Adding a new repeatable element needs to trigger again the change function
    $('body').on('click', '.cmb2-id--gamipress-time-based-rewards-rewards .cmb-add-group-row', function() {
        // Add a delay to the checks after add a new group row
        setTimeout( function() {

            post_type_first_change = true;

            var last = $('.cmb2-id--gamipress-time-based-rewards-rewards .cmb-repeatable-grouping').last();
            var post_type = last.find('select[id$="post_type"]');
            var achievement_field = last.find('select[id$="achievement_id"]');
            var rank_field = last.find('select[id$="rank_id"]');
            var achievement_type_field = last.find('select[id$="achievement_type"]');

            // Force change on post type
            post_type.trigger('change');

            // Reset select2 fields
            achievement_field.next('.select2').remove();
            rank_field.next('.select2').remove();
            achievement_type_field.next('.select2').remove();

            // Hide the selectors rows
            achievement_field.closest('.cmb.row').hide();
            rank_field.closest('.cmb.row').hide();
            achievement_type_field.closest('.cmb.row').hide();

            // Reset selectors values
            achievement_field.val( '' );
            rank_field.val( '' );
            achievement_type_field.val( '' );

            // Reset select2
            gamipress_post_selector( achievement_field );
            gamipress_post_selector( rank_field );
            gamipress_selector( achievement_type_field );

            // Reset change attr
            last.find('input[id$="label"]').attr( 'data-changed', 'false' );

            post_type_first_change = false;

        }, 100 );
    });

    // If user manually changes the label, then respect user one
    $('body').on('change', '.cmb2-id--gamipress-time-based-rewards-rewards input[id$="label"]', function() {

        if( $(this).val() !== '' )
            $(this).attr( 'data-changed', 'true' );
        else
            $(this).attr( 'data-changed', 'false' );

    });

    // Time-based rewards slug setting

    $('#gamipress_time_based_rewards_slug').on( 'keyup', function() {
        var field = $(this);
        var slug = $(this).val();
        var preview = $(this).next('.cmb2-metabox-description').find('.gamipress-time-based-rewards-slug');

        if( preview.length )
            preview.text(slug);

        // Delete any existing version of this warning
        $('#slug-warning').remove();

        // Throw a warning on Points/Achievement Type editor if slug is > 20 characters
        if ( slug.length > 20 ) {
            // Set input to look like danger
            field.css({'background':'#faa', 'color':'#a00', 'border-color':'#a55' });

            // Output a custom warning
            // TODO: Localization here
            field.parent().append('<span id="slug-warning" class="cmb2-metabox-description" style="color: #a00;">Time-based Rewards\'s slug supports a maximum of 20 characters.</span>');
        } else {
            // Restore the input style
            field.css({'background':'', 'color':'', 'border-color':''});
        }
    });

    $('#gamipress_time_based_rewards_slug').keyup();

})( jQuery );