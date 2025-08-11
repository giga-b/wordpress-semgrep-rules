(function( $ ) {

    // Current user field
    $( '#gamipress_posts_restricted_current_user, #gamipress_posts_unlocked_current_user').on('change', function() {
        var target = $(this).closest('.cmb-row').next(); // User ID field

        if( $(this).prop('checked') ) {
            target.slideUp().addClass('cmb2-tab-ignore');
        } else {
            if( target.closest('.cmb-tabs-wrap').length ) {
                // Just show if item tab is active
                if( target.hasClass('cmb-tab-active-item') ) {
                    target.slideDown();
                }
            } else {
                target.slideDown();
            }

            target.removeClass('cmb2-tab-ignore');
        }
    });

    // On change restrict content unlock by
    $('#gamipress_restrict_content_unlock_by').on('change', function() {
        var prefix = '.cmb2-id-gamipress-restrict-content-';
        var tag_prefix = '#gamipress_restrict_content_wrapper .gamipress-restrict-content-pattern-tags-list #tag-';
        var unlock_by = $(this).val();

        // Initialize fields visibility
        $(prefix + 'points').hide();
        $(prefix + 'points-type').hide();

        $(prefix + 'achievement').hide();
        $(prefix + 'achievement-type').hide();
        $(prefix + 'achievement-count').hide();

        $(prefix + 'rank').hide();

        // Initialize message tags visibility
        $(tag_prefix + 'points').hide();
        $(tag_prefix + 'points-type').hide();
        $(tag_prefix + 'points-balance').hide();

        $(tag_prefix + 'achievement').hide();
        $(tag_prefix + 'achievement-type').hide();
        $(tag_prefix + 'achievement-count').hide();

        $(tag_prefix + 'rank').hide();

        switch( unlock_by ) {
            case 'expend_points':
            case 'points_balance':
                // Show fields
                $(prefix + 'points').show();
                $(prefix + 'points-type').show();
                // Show tags
                $(tag_prefix + 'points').show();
                $(tag_prefix + 'points-type').show();
                $(tag_prefix + 'points-balance').show();
                break;
            case 'achievement':
                // Show fields
                $(prefix + 'achievement').show();
                // Show tags
                $(tag_prefix + 'achievement').show();
                break;
            case 'achievement_type':
                // Show fields
                $(prefix + 'achievement-type').show();
                $(prefix + 'achievement-count').show();
                // Show tags
                $(tag_prefix + 'achievement-type').show();
                $(tag_prefix + 'achievement-count').show();
                break;
            case 'all_achievement_type':
                // Show fields
                $(prefix + 'achievement-type').show();
                // Show tags
                $(tag_prefix + 'achievement-type').show();
                break;
            case 'rank':
                // Show fields
                $(prefix + 'rank').show();
                // Show tags
                $(tag_prefix + 'rank').show();
                break;
        }

    });

    $('#gamipress_restrict_content_unlock_by').trigger('change');

    // On change show content if and hide content if condition
    $('#gamipress_show_content_if_condition, #gamipress_hide_content_if_condition').on('change', function() {

        var group = ( $(this).attr('id') === 'gamipress_show_content_if_condition' ? 'show' : 'hide' );

        var prefix = '.cmb2-id-gamipress-' + group + '-content-if-';
        var tag_prefix = '#gamipress_' + group + '_content_if_wrapper .gamipress-restrict-content-pattern-tags-list #tag-';
        var condition = $(this).val();

        // Initialize fields visibility
        $(prefix + 'points').hide();
        $(prefix + 'points-type').hide();

        $(prefix + 'achievement').hide();
        $(prefix + 'achievement-type').hide();
        $(prefix + 'achievement-count').hide();

        $(prefix + 'rank').hide();

        // Initialize message tags visibility
        $(tag_prefix + 'points').hide();
        $(tag_prefix + 'points-type').hide();
        $(tag_prefix + 'points-balance').hide();

        $(tag_prefix + 'achievement').hide();
        $(tag_prefix + 'achievement-type').hide();
        $(tag_prefix + 'achievement-count').hide();

        $(tag_prefix + 'rank').hide();

        switch( condition ) {
            case 'points_greater':
            case 'points_lower':
                // Show fields
                $(prefix + 'points').show();
                $(prefix + 'points-type').show();
                // Show tags
                $(tag_prefix + 'points').show();
                $(tag_prefix + 'points-type').show();
                $(tag_prefix + 'points-balance').show();
                break;
            case 'achievement':
                // Show fields
                $(prefix + 'achievement').show();
                // Show tags
                $(tag_prefix + 'achievement').show();
                break;
            case 'achievement_type':
                // Show fields
                $(prefix + 'achievement-type').show();
                $(prefix + 'achievement-count').show();
                // Show tags
                $(tag_prefix + 'achievement-type').show();
                $(tag_prefix + 'achievement-count').show();
                break;
            case 'all_achievement_type':
                // Show fields
                $(prefix + 'achievement-type').show();
                // Show tags
                $(tag_prefix + 'achievement-type').show();
                break;
            case 'rank':
                // Show fields
                $(prefix + 'rank').show();
                // Show tags
                $(tag_prefix + 'rank').show();
                break;
        }

    });

    $('#gamipress_show_content_if_condition, #gamipress_hide_content_if_condition').trigger('change');

    var gamipress_restrict_content_editor;

    // Parse [gamipress_restrict_content], [gamipress_show_content_if] and [gamipress_hide_content_if] output
    $('body').on( 'gamipress_construct_shortcode', '#gamipress_restrict_content_wrapper, #gamipress_show_content_if_wrapper, #gamipress_hide_content_if_wrapper', function( e, args ) {

        var selected_text = '';
        var textarea = gamipress_restrict_content_editor[0];

        // Get current selected text

        // Visual mode
        if( window.tinymce.activeEditor )
            selected_text = window.tinymce.activeEditor.selection.getContent();

        // Edit mode
        if( selected_text.length === 0 ) {
            textarea.focus();

            // IE support
            if ( document.selection )
                selected_text = document.selection.createRange().text;

            if( selected_text.length === 0 ) {
                var startPos = textarea.selectionStart;
                var endPos = textarea.selectionEnd;

                // No need to do all this fancy substring stuff unless we have a selection
                if ( startPos !== endPos )
                    selected_text = textarea.value.substring( startPos, endPos );
            }
        }

        args.output += selected_text + '[/' + args.shortcode + ']';

    } );

    $('body').on( 'click', '#insert_gamipress_shortcodes', function(e) {
        gamipress_restrict_content_editor = $(this).closest('.wp-editor-wrap').find( '.wp-editor-area' ).first();
    });

    // Parse [gamipress_restrict_content] atts
    $('body').on( 'gamipress_shortcode_attributes', '#gamipress_restrict_content_wrapper', function( e, args ) {

        switch( args.attributes.unlock_by ) {
            case 'expend_points':
            case 'points_balance':
                // Remove non-required attributes
                //delete args.attributes.points;
                //delete args.attributes.points_type;
                delete args.attributes.achievement;
                delete args.attributes.achievement_type;
                delete args.attributes.achievement_count;
                delete args.attributes.rank;
                break;
            case 'achievement':
                // Remove non-required attributes
                delete args.attributes.points;
                delete args.attributes.points_type;
                //delete args.attributes.achievement;
                delete args.attributes.achievement_type;
                delete args.attributes.achievement_count;
                delete args.attributes.rank;
                break;
            case 'achievement_type':
                delete args.attributes.points;
                delete args.attributes.points_type;
                delete args.attributes.achievement;
                //delete args.attributes.achievement_type;
                //delete args.attributes.achievement_count;
                delete args.attributes.rank;
                break;
            case 'all_achievement_type':
                delete args.attributes.points;
                delete args.attributes.points_type;
                delete args.attributes.achievement;
                //delete args.attributes.achievement_type;
                delete args.attributes.achievement_count;
                delete args.attributes.rank;
                break;
            case 'rank':
                delete args.attributes.points;
                delete args.attributes.points_type;
                delete args.attributes.achievement;
                delete args.attributes.achievement_type;
                delete args.attributes.achievement_count;
                //delete args.attributes.rank;
                break;
        }

    } );

    // Parse [gamipress_show_content_if] and [gamipress_hide_content_if] atts
    $('body').on( 'gamipress_shortcode_attributes', '#gamipress_show_content_if_wrapper, #gamipress_hide_content_if_wrapper', function( e, args ) {

        switch( args.attributes.condition ) {
            case 'points_greater':
            case 'points_lower':
                // Remove non-required attributes
                //delete args.attributes.points;
                //delete args.attributes.points_type;
                delete args.attributes.achievement;
                delete args.attributes.achievement_type;
                delete args.attributes.achievement_count;
                delete args.attributes.rank;
                break;
            case 'achievement':
                // Remove non-required attributes
                delete args.attributes.points;
                delete args.attributes.points_type;
                //delete args.attributes.achievement;
                delete args.attributes.achievement_type;
                delete args.attributes.achievement_count;
                delete args.attributes.rank;
                break;
            case 'achievement_type':
                delete args.attributes.points;
                delete args.attributes.points_type;
                delete args.attributes.achievement;
                //delete args.attributes.achievement_type;
                //delete args.attributes.achievement_count;
                delete args.attributes.rank;
                break;
            case 'all_achievement_type':
                delete args.attributes.points;
                delete args.attributes.points_type;
                delete args.attributes.achievement;
                //delete args.attributes.achievement_type;
                delete args.attributes.achievement_count;
                delete args.attributes.rank;
                break;
            case 'rank':
                delete args.attributes.points;
                delete args.attributes.points_type;
                delete args.attributes.achievement;
                delete args.attributes.achievement_type;
                delete args.attributes.achievement_count;
                //delete args.attributes.rank;
                break;
        }

    } );

})( jQuery );