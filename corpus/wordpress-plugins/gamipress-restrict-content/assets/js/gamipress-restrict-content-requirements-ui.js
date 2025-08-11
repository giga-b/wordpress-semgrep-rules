(function( $ ) {

    // Listen for our change to our trigger type selectors
    $('.requirements-list').on( 'change', '.select-trigger-type', function() {

        // Grab our selected trigger type and achievement selector
        var trigger_type = $(this).val();
        var content_id_input = $(this).siblings('.gamipress-restrict-content-content-id');

        if(
            trigger_type === 'gamipress_restrict_content_unlock_specific_content'
            || trigger_type === 'gamipress_restrict_content_unlock_specific_content_specific_post'
        ) {
            content_id_input.show();
        } else {
            content_id_input.hide();
        }

    });

    // Loop requirement list items to show/hide content ID input on initial load
    $('.requirements-list li').each(function() {

        // Grab our selected trigger type and achievement selector
        var trigger_type = $(this).find('.select-trigger-type').val();
        var content_id_input = $(this).find('.gamipress-restrict-content-content-id');

        if(
            trigger_type === 'gamipress_restrict_content_unlock_specific_content'
            || trigger_type === 'gamipress_restrict_content_unlock_specific_content_specific_post'
        ) {
            content_id_input.show();
        } else {
            content_id_input.hide();
        }

    });

    $('.requirements-list').on( 'update_requirement_data', '.requirement-row', function(e, requirement_details, requirement) {

        if(
            requirement_details.trigger_type === 'gamipress_restrict_content_unlock_specific_content'
            || requirement_details.trigger_type === 'gamipress_restrict_content_unlock_specific_content_specific_post'
        ) {
            requirement_details.content_id = requirement.find( '.gamipress-restrict-content-content-id input' ).val();
        }

    });

})( jQuery );