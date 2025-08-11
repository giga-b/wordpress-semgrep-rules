(function( $ ) {

    // On change restrict checkbox, toggle fields and tabs visibility
    $('#_gamipress_restrict_content_restrict').on('change', function() {
        // All fields selector
        var selector = '#gamipress-restrict-content-tab-access, '
            + '#gamipress-restrict-content-tab-content, '
            + '#gamipress-restrict-content-tab-links, '
            + '#gamipress-restrict-content-tab-images, '
            + '#gamipress-restrict-content-tab-users, '
            + '.cmb2-id--gamipress-restrict-content-unlock-by, '
            + '.cmb2-id--gamipress-restrict-content-access-with-points, '
            + '.cmb2-id--gamipress-restrict-content-points-to-access, '
            + '.cmb2-id--gamipress-restrict-content-restrictions';

        if( $(this).prop('checked') ) {
            // Selector when enabled
            selector = '#gamipress-restrict-content-tab-access, '
                + '#gamipress-restrict-content-tab-content, '
                + '#gamipress-restrict-content-tab-links, '
                + '#gamipress-restrict-content-tab-images, '
                + '#gamipress-restrict-content-tab-users, '
                + '.cmb2-id--gamipress-restrict-content-unlock-by';

            // Check if restrict access is also checked
            if( $('#_gamipress_restrict_content_restrict_access').prop('checked') ) {
                selector = '#gamipress-restrict-content-tab-access, '
                    + '#gamipress-restrict-content-tab-users, '
                    + '.cmb2-id--gamipress-restrict-content-unlock-by';
            }

            // Trigger change on unlock by select
            $('#_gamipress_restrict_content_unlock_by').trigger('change');

            $(selector).slideDown();
        } else {
            $(selector).slideUp();
        }
    });

    if( ! $('#_gamipress_restrict_content_restrict').prop('checked') ) {
        $(
            '#gamipress-restrict-content-tab-access, '
            + '#gamipress-restrict-content-tab-content, '
            + '#gamipress-restrict-content-tab-links, '
            + '#gamipress-restrict-content-tab-images, '
            + '#gamipress-restrict-content-tab-users, '
            + '.cmb2-id--gamipress-restrict-content-unlock-by, '
            + '.cmb2-id--gamipress-restrict-content-access-with-points, '
            + '.cmb2-id--gamipress-restrict-content-points-to-access, '
            + '.cmb2-id--gamipress-restrict-content-restrictions'
        ).hide();
    }

    // On change unlock by select, toggle fields visibility

    $('#_gamipress_restrict_content_unlock_by').on('change', function() {

        if( ! $('#_gamipress_restrict_content_restrict').prop('checked') ) {
            return;
        }

        var unlock_by = $(this).val();

        if( unlock_by === 'complete-restrictions' ) {
            $('.cmb2-id--gamipress-restrict-content-restrictions, .cmb2-id--gamipress-restrict-content-access-with-points').slideDown().removeClass('cmb2-tab-ignore');

            // Check if access with points is checked
            if( $('#_gamipress_restrict_content_access_with_points').prop('checked') ) {
                $('.cmb2-id--gamipress-restrict-content-points-to-access').slideDown().removeClass('cmb2-tab-ignore');
            } else {
                $('.cmb2-id--gamipress-restrict-content-points-to-access').slideUp().addClass('cmb2-tab-ignore');
            }

        } else if( unlock_by === 'expend-points' ) {
            $('.cmb2-id--gamipress-restrict-content-restrictions, .cmb2-id--gamipress-restrict-content-access-with-points').slideUp().addClass('cmb2-tab-ignore');
            $('.cmb2-id--gamipress-restrict-content-points-to-access').slideDown().removeClass('cmb2-tab-ignore');
        }
    });

    $('#_gamipress_restrict_content_unlock_by').trigger('change');

    // On change access with points checkbox, toggle fields visibility
    $('#_gamipress_restrict_content_access_with_points').on('change', function() {

        var target = $('.cmb2-id--gamipress-restrict-content-points-to-access');

        if( ! $('#_gamipress_restrict_content_restrict').prop('checked') ) {
            target.slideUp().addClass('cmb2-tab-ignore');
            return;
        }

        // Prevent to hide points input if unlock by is no set to complete restrictions
        if( $('#_gamipress_restrict_content_unlock_by').val() !== 'complete-restrictions' ) {
            return;
        }

        if( $(this).prop('checked') ) {
            target.slideDown().removeClass('cmb2-tab-ignore');
        } else {
            target.slideUp().addClass('cmb2-tab-ignore');
        }
    });

    if( ! $('#_gamipress_restrict_content_access_with_points').prop('checked') && $('#_gamipress_restrict_content_unlock_by').val() === 'complete-restrictions' ) {
        $('.cmb2-id--gamipress-restrict-content-points-to-access').hide().addClass('cmb2-tab-ignore');
    }

    // On change restrict access checkbox, toggle fields and tabs visibility
    $('#_gamipress_restrict_content_restrict_access').on('change', function() {
        var target = $(
            //'#gamipress-restrict-content-tab-content, ' // Since 1.0.2, some fields of this tab are hidden
            '#gamipress-restrict-content-tab-links, '
            + '#gamipress-restrict-content-tab-images'
        );

        if( $(this).prop('checked') ) {
            target.slideUp();

            // Add ignore class to restrict content and content replacement (not toggle visibility because this fields are on another tab)
            $('.cmb2-id--gamipress-restrict-content-restrict-content, .cmb2-id--gamipress-restrict-content-content-replacement').addClass('cmb2-tab-ignore');

            // Show redirect page select
            $('.cmb2-id--gamipress-restrict-content-redirect-page').slideDown().removeClass('cmb2-tab-ignore');
        } else {
            target.slideDown();

            // Remove ignore class to restrict content and content replacement (not toggle visibility because this fields are on another tab)
            $('.cmb2-id--gamipress-restrict-content-restrict-content, .cmb2-id--gamipress-restrict-content-content-replacement').removeClass('cmb2-tab-ignore');

            // Hide redirect page select
            $('.cmb2-id--gamipress-restrict-content-redirect-page').slideUp().addClass('cmb2-tab-ignore');
        }
    });

    if( $('#_gamipress_restrict_content_restrict_access').prop('checked') ) {

        $(
            //'#gamipress-restrict-content-tab-content, ' // Since 1.0.2, some fields of this tab are hidden
            '#gamipress-restrict-content-tab-links, '
            + '#gamipress-restrict-content-tab-images'
        ).hide();

        // Hide restrict content and content replacement
        $('.cmb2-id--gamipress-restrict-content-restrict-content, .cmb2-id--gamipress-restrict-content-content-replacement').hide().addClass('cmb2-tab-ignore');

    } else {

        // Hide redirect page select
        $('.cmb2-id--gamipress-restrict-content-redirect-page').hide().addClass('cmb2-tab-ignore');
    }

    // On change content replacement, change content length visibility
    $('#_gamipress_restrict_content_content_replacement').on('change', function() {
        var target = $('.cmb2-id--gamipress-restrict-content-content-length');

        if( $(this).val() === 'content' ) {
            target.slideDown().removeClass('cmb2-tab-ignore');
        } else {
            target.slideUp().addClass('cmb2-tab-ignore');
        }
    });

    if( $('#_gamipress_restrict_content_content_replacement').val() !== 'content' ) {
        $('.cmb2-id--gamipress-restrict-content-content-length').hide().addClass('cmb2-tab-ignore');
    }

    // On change restriction type, change fields visibility
    $('body').on('change', '.gamipress-restrict-content-type select', function() {

        var row = $(this).closest('.gamipress-restrict-content-type');
        var type = $(this).val();

        // Points fields
        var points = row.siblings('.gamipress-restrict-content-points');
        var points_type = row.siblings('.gamipress-restrict-content-points-type');

        // Rank fields
        var rank = row.siblings('.gamipress-restrict-content-rank');
        var rank_type = row.siblings('.gamipress-restrict-content-rank-type');

        // Achievement fields
        var achievement = row.siblings('.gamipress-restrict-content-achievement');
        var achievement_type = row.siblings('.gamipress-restrict-content-achievement-type');

        // The rest of fields
        var count = row.siblings('.gamipress-restrict-content-count');

        // Hide all
        points.hide();
        points_type.hide();
        rank.hide();
        rank_type.hide();
        achievement.hide();
        achievement_type.hide();
        count.hide();

        if( type === 'earn-points' ) {
            points.show();
            points_type.show();
        } else if( type === 'earn-rank' ) {
            rank_type.show();
            rank_type.find('select').trigger('change');
        } else if( type === 'specific-achievement' ) {
            achievement_type.show();
            achievement_type.find('select').trigger('change');

            if( count.find('input').val() === '' ) {
                count.find('input').val('1');
            }

            count.show();
        } else if(  type === 'any-achievement' ) {
            achievement_type.show();
            count.show();
        } else if( type === 'all-achievements' ) {
            achievement_type.show();
        }

        gamipress_restrict_content_generate_label( row.parent() );

    });

    // Before trigger change initialize already defined labels to avoid auto generation
    $('.gamipress-restrict-content-label input').each(function() {
        if( $(this).val() !== '' ) {
            $(this).attr( 'data-changed', 'true' );
        }
    });

    // On change restriction achievement type, change achievement field visibility
    $('body').on('change', '.gamipress-restrict-content-achievement-type select', function() {

        var $this = $(this);
        var row = $this.closest('.gamipress-restrict-content-achievement-type');
        var type = row.siblings('.gamipress-restrict-content-type').find('select').val();
        var achievement_type = $this.val();
        var achievement = row.siblings('.gamipress-restrict-content-achievement');
        var achievement_select = achievement.find('select');
        var achievement_select_val = achievement_select.val();

        if( achievement_type !== '' && type === 'specific-achievement' ) {

            $this.parent().append('<span class="spinner is-active" style="position: absolute; left: 310px;"></span>');

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: 'gamipress_requirement_achievement_post',
                    nonce: gamipress_restrict_content_admin.nonce,
                    achievement_type: achievement_type,
                },
                success: function( response ) {

                    $this.parent().find('.spinner').remove();

                    achievement_select.html( response );

                    // Need to force value for initial load
                    if( achievement_select.find('option[value="' + achievement_select_val + '"]').length ) {
                        achievement_select.val( achievement_select_val );
                    }

                    // Show the select again
                    achievement.show();

                    // Check if should generate the label
                    gamipress_restrict_content_generate_label( row.parent() );
                },
            });

        } else {
            achievement.hide();
        }

    });

    // On change restriction rank type, change rank field visibility
    $('body').on('change', '.gamipress-restrict-content-rank-type select', function() {

        var $this = $(this);
        var row = $this.closest('.gamipress-restrict-content-rank-type');
        var type = row.siblings('.gamipress-restrict-content-type').find('select').val();
        var rank_type = $this.val();
        var rank = row.siblings('.gamipress-restrict-content-rank');
        var rank_select = rank.find('select');
        var rank_select_val = rank_select.val();

        if( rank_type !== '' && type === 'earn-rank' ) {

            $this.parent().append('<span class="spinner is-active" style="position: absolute; left: 310px;"></span>');

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: 'gamipress_get_ranks_options_html',
                    nonce: gamipress_restrict_content_admin.nonce,
                    post_type: rank_type,
                    selected: rank_select.val()
                },
                success: function( response ) {

                    $this.parent().find('.spinner').remove();

                    rank_select.html( response );

                    // Need to force value for initial load
                    if( rank_select.find('option[value="' + rank_select_val + '"]').length ) {
                        rank_select.val( rank_select_val );
                    }

                    // Show the select again
                    rank.show();

                    // Check if should generate the label
                    gamipress_restrict_content_generate_label( row.parent() );
                },
            });
        } else {
            rank.hide();
        }

    });

    // Initial trigger of type change to start the important javascript functions
    $('.gamipress-restrict-content-type select').trigger('change');

    // Trigger label generation on change any input
    $('body').on('change', '.gamipress-restrict-content-points input, '
        + '.gamipress-restrict-content-points-type select, '
        + '.gamipress-restrict-content-achievement select, '
        + '.gamipress-restrict-content-rank select, '
        + '.gamipress-restrict-content-count input', function() {
        gamipress_restrict_content_generate_label( $(this).closest('.postbox') );
    });

    // If user manually changes the label, then respect user one
    $('body').on('change', '.gamipress-restrict-content-label input', function() {

        if( $(this).val() !== '' ) {
            $(this).attr( 'data-changed', 'true' );
        } else {
            $(this).attr( 'data-changed', 'false' );
            gamipress_restrict_content_generate_label( $(this).closest('.postbox') );
        }

    });

    // Adding a new group element
    $('body').on('click', '.cmb2-id--gamipress-restrict-content-restrictions .cmb-add-group-row', function() {

        var last = $(this).closest('.cmb-repeatable-grouping').last();

        // Trigger change on new group type select
        last.find('.gamipress-restrict-content-type select').trigger('change');

        // Reset change attr
        last.find('.gamipress-restrict-content-label input').attr( 'data-changed', 'false' );
    });

    function gamipress_restrict_content_generate_label( box ) {

        // Force regenerate on empty labels
        if(  box.find('.gamipress-restrict-content-label input').val() === '' ) {
            box.find('.gamipress-restrict-content-label input').attr( 'data-changed', 'false' );
        }

        if( box.find('.gamipress-restrict-content-label input').attr( 'data-changed' ) === 'true' ) {
            return;
        }

        var type = box.find('.gamipress-restrict-content-type select').val();

        var pattern = gamipress_restrict_content_admin.labels[type];

        if( pattern === undefined ) {
            return;
        }

        var parsed_pattern = '';

        if( type === 'earn-points' ) {

            var points_type = box.find('.gamipress-restrict-content-points-type select').val();
            var points_type_label = '';

            if( points_type !== '' ) {
                points_type_label = box.find('.gamipress-restrict-content-points-type select option[value="' + points_type + '"]').text();
            } else {
                points_type_label = 'Points';
            }

            parsed_pattern = pattern
                .replace( '{points}', box.find('.gamipress-restrict-content-points input').val() )
                .replace( '{points_type}', points_type_label );

        } else if( type === 'earn-rank' ) {

            var rank_type = box.find('.gamipress-restrict-content-rank-type select').val();
            var rank_type_label = box.find('.gamipress-restrict-content-rank-type select option[value="' + rank_type + '"]').text();

            if( rank_type === '' ) {
                box.find('.gamipress-restrict-content-label input').val( '' );
                return;
            }

            var rank_id = box.find('.gamipress-restrict-content-rank select').val();

            if( rank_id === '' ) {
                box.find('.gamipress-restrict-content-label input').val( '' );
                return;
            }

            var rank_label = box.find('.gamipress-restrict-content-rank select option[value="' + rank_id + '"]').text();

            parsed_pattern = pattern
                .replace( '{rank}', rank_label )
                .replace( '{rank_type}', rank_type_label );

        } else if( type === 'specific-achievement' ) {

            var achievement_type = box.find('.gamipress-restrict-content-achievement-type select').val();
            var achievement_type_label = box.find('.gamipress-restrict-content-achievement-type select option[value="' + achievement_type + '"]').text();

            if( achievement_type === '' ) {
                box.find('.gamipress-restrict-content-label input').val( '' );
                return;
            }

            var achievement_id = box.find('.gamipress-restrict-content-achievement select').val();

            if( achievement_id === '' ) {
                box.find('.gamipress-restrict-content-label input').val( '' );
                return;
            }

            var achievement_label = box.find('.gamipress-restrict-content-achievement select option[value="' + achievement_id + '"]').text();

            var count = box.find('.gamipress-restrict-content-count input').val();

            parsed_pattern = pattern
                .replace( '{achievement}', achievement_label )
                .replace( '{achievement_type}', achievement_type_label )
                .replace( '{count}', count + ' ' + ( count === 1 ? 'time' : 'times' ) ); // TODO: add time/times localization

        } else if(  type === 'any-achievement' ) {
            var achievement_type = box.find('.gamipress-restrict-content-achievement-type select').val();
            var achievement_type_label = box.find('.gamipress-restrict-content-achievement-type select option[value="' + achievement_type + '"]').text();

            if( achievement_type === '' ) {
                box.find('.gamipress-restrict-content-label input').val( '' );
                return;
            }

            var count = box.find('.gamipress-restrict-content-count input').val();

            parsed_pattern = pattern
                .replace( '{achievement_type}', achievement_type_label )
                .replace( '{count}', count + ' ' + ( count === 1 ? 'time' : 'times' ) ); // TODO: add time/times localization
        } else if( type === 'all-achievements' ) {
            var achievement_type = box.find('.gamipress-restrict-content-achievement-type select').val();
            var achievement_type_label = box.find('.gamipress-restrict-content-achievement-type select option[value="' + achievement_type + '"]').text();

            if( achievement_type === '' ) {
                box.find('.gamipress-restrict-content-label input').val( '' );
                return;
            }

            parsed_pattern = pattern
                .replace( '{achievement_type}', achievement_type_label );
        }

        box.find('.gamipress-restrict-content-label input').val( parsed_pattern );

    }

})( jQuery );