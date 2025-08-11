(function( $ ) {

    // On change restrict content unlock by
    $( 'body').on('change', 'select[id^="widget-gamipress_restrict_content"][id$="[unlock_by]"]', function() {
        var unlock_by = $(this).val();
        var form = $(this).closest('form');

        // Get the widget number and use for field prefix
        var widget_number = $(this).closest('form').find('input[name="widget_number"]').val();
        var prefix = '.cmb2-id-widget-gamipress-restrict-content-widget' + widget_number;
        var tag_prefix = '.gamipress-restrict-content-pattern-tags-list #tag-';

        // Initialize fields visibility
        $(prefix + 'points').hide();
        $(prefix + 'points-type').hide();

        $(prefix + 'achievement').hide();
        $(prefix + 'achievement-type').hide();
        $(prefix + 'achievement-count').hide();

        $(prefix + 'rank').hide();

        // Initialize message tags visibility
        form.find(tag_prefix + 'points').hide();
        form.find(tag_prefix + 'points-type').hide();
        form.find(tag_prefix + 'points-balance').hide();

        form.find(tag_prefix + 'achievement').hide();
        form.find(tag_prefix + 'achievement-type').hide();
        form.find(tag_prefix + 'achievement-count').hide();

        form.find(tag_prefix + 'rank').hide();

        switch( unlock_by ) {
            case 'expend_points':
            case 'points_balance':
                // Show fields
                $(prefix + 'points').show();
                $(prefix + 'points-type').show();
                // Show tags
                form.find(tag_prefix + 'points').show();
                form.find(tag_prefix + 'points-type').show();
                form.find(tag_prefix + 'points-balance').show();
                break;
            case 'achievement':
                // Show fields
                $(prefix + 'achievement').show();
                // Show tags
                form.find(tag_prefix + 'achievement').show();
                break;
            case 'achievement_type':
                // Show fields
                $(prefix + 'achievement-type').show();
                $(prefix + 'achievement-count').show();
                // Show tags
                form.find(tag_prefix + 'achievement-type').show();
                form.find(tag_prefix + 'achievement-count').show();
                break;
            case 'all_achievement_type':
                // Show fields
                $(prefix + 'achievement-type').show();
                // Show tags
                form.find(tag_prefix + 'achievement-type').show();
                break;
            case 'rank':
                // Show fields
                $(prefix + 'rank').show();
                // Show tags
                form.find(tag_prefix + 'rank').show();
                break;
        }

    });

    $('select[id^="widget-gamipress_restrict_content"][id$="[unlock_by]"]').trigger('change');

    // On change show content if and hide content if condition
    $( 'body').on('change', 'select[id^="widget-gamipress_show_content_if"][id$="[condition]"], select[id^="widget-gamipress_hide_content_if"][id$="[condition]"]', function() {
        var condition = $(this).val();
        var form = $(this).closest('form');
        var group = ( $(this).attr('id').includes('gamipress_show_content_if') ? 'show' : 'hide' );

        // Get the widget number and use for field prefix
        var widget_number = $(this).closest('form').find('input[name="widget_number"]').val();
        var prefix = '.cmb2-id-widget-gamipress-' + group + '-content-if-widget' + widget_number;
        var tag_prefix = '.gamipress-restrict-content-pattern-tags-list #tag-';

        // Initialize fields visibility
        $(prefix + 'points').hide();
        $(prefix + 'points-type').hide();

        $(prefix + 'achievement').hide();
        $(prefix + 'achievement-type').hide();
        $(prefix + 'achievement-count').hide();

        $(prefix + 'rank').hide();

        // Initialize message tags visibility
        form.find(tag_prefix + 'points').hide();
        form.find(tag_prefix + 'points-type').hide();
        form.find(tag_prefix + 'points-balance').hide();

        form.find(tag_prefix + 'achievement').hide();
        form.find(tag_prefix + 'achievement-type').hide();
        form.find(tag_prefix + 'achievement-count').hide();

        form.find(tag_prefix + 'rank').hide();

        switch( condition ) {
            case 'points_greater':
            case 'points_lower':
                // Show fields
                $(prefix + 'points').show();
                $(prefix + 'points-type').show();
                // Show tags
                form.find(tag_prefix + 'points').show();
                form.find(tag_prefix + 'points-type').show();
                form.find(tag_prefix + 'points-balance').show();
                break;
            case 'achievement':
                // Show fields
                $(prefix + 'achievement').show();
                // Show tags
                form.find(tag_prefix + 'achievement').show();
                break;
            case 'achievement_type':
                // Show fields
                $(prefix + 'achievement-type').show();
                $(prefix + 'achievement-count').show();
                // Show tags
                form.find(tag_prefix + 'achievement-type').show();
                form.find(tag_prefix + 'achievement-count').show();
                break;
            case 'all_achievement_type':
                // Show fields
                $(prefix + 'achievement-type').show();
                // Show tags
                form.find(tag_prefix + 'achievement-type').show();
                break;
            case 'rank':
                // Show fields
                $(prefix + 'rank').show();
                // Show tags
                form.find(tag_prefix + 'rank').show();
                break;
        }

    });

    $('select[id^="widget-gamipress_show_content_if"][id$="[condition]"], select[id^="widget-gamipress_hide_content_if"][id$="[condition]"]').trigger('change');

})( jQuery );