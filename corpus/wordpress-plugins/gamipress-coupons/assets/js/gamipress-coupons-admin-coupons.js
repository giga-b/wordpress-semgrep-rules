(function( $ ) {

    // User roles select2
    $('#_gamipress_coupons_allowed_roles, #_gamipress_coupons_excluded_roles').gamipress_select2({
        theme: 'default gamipress-select2',
        placeholder: 'Select role(s)',
        allowClear: true,
        multiple: true
    });

    // User Ajax
    $('#_gamipress_coupons_allowed_users, #_gamipress_coupons_excluded_users').gamipress_select2({
        ajax: {
            url: ajaxurl,
            dataType: 'json',
            delay: 250,
            type: 'POST',
            data: function( params ) {
                return {
                    q: params.term,
                    action: 'gamipress_get_users',
                    nonce: gamipress_coupons_coupons.nonce
                };
            },
            processResults: gamipress_select2_users_process_results
        },
        escapeMarkup: function ( markup ) { return markup; }, // Let our custom formatter work
        templateResult: gamipress_select2_users_template_result,
        theme: 'default gamipress-select2',
        placeholder: 'Select an User',
        allowClear: true,
        multiple: true
    });

    // Allowed users visibility
    $('body').on('change', '#_gamipress_coupons_restrict_to_users', function() {

        var target = $('.cmb2-id--gamipress-coupons-allowed-users, .cmb2-id--gamipress-coupons-allowed-roles');
        var target_2 = $('.cmb2-id--gamipress-coupons-excluded-users, .cmb2-id--gamipress-coupons-excluded-roles');

        if( $(this).prop('checked') ) {
            target.slideDown();
            target_2.slideUp();
        } else {
            target.slideUp();
            target_2.slideDown();
        }

    });

    if( ! $('#_gamipress_coupons_restrict_to_users').prop('checked') ) {
        $('.cmb2-id--gamipress-coupons-allowed-users, .cmb2-id--gamipress-coupons-allowed-roles').hide();
    } else {
        $('.cmb2-id--gamipress-coupons-excluded-users, .cmb2-id--gamipress-coupons-excluded-roles').hide();
    }

    // Coupon rewards
    $('body').on('change', '.gamipress-coupons-coupon-reward-post-type select', function() {

        var post_type = $(this).val();
        var container = $(this).closest('.cmb-repeatable-grouping');
        var post_id_container = container.find('.gamipress-coupons-coupon-reward-post-id');
        var quantity_container = container.find('.gamipress-coupons-coupon-reward-quantity');
        var post_id_field = post_id_container.find('select');

        if( post_type in gamipress_coupons_coupons.points_types ) {
            // For points types, we have the IDs at localized vars
            var post_id = gamipress_coupons_coupons.points_types[post_type].ID;
            var plural_name = gamipress_coupons_coupons.points_types[post_type].plural_name;

            // Add a new option to the post ID field and set it as value
            post_id_field.html('<option value="' + post_id + '">' + plural_name + '</option>');
            post_id_field.val(post_id);

            // Hide the post ID container
            post_id_container.hide();

            // Show the quantity container
            quantity_container.slideDown();
        } else {

            var action = '';

            if( post_type in gamipress_coupons_coupons.achievement_types ) {
                action = 'gamipress_get_achievements_options_html';
                post_id_container.find('.cmb-th label').text(gamipress_coupons_coupons.strings.achievement);
            } else if( post_type in gamipress_coupons_coupons.rank_types ) {
                action = 'gamipress_get_ranks_options_html';
                post_id_container.find('.cmb-th label').text(gamipress_coupons_coupons.strings.rank);
            }

            if( ! post_id_container.find('.spinner').length ) {
                post_id_container.find('.cmb-td').prepend('<span class="spinner" style="float: none;"></span>');
            }

            var spinner = post_id_container.find('.spinner');

            // Hide the post id field
            post_id_field.slideUp();

            // Show the post id container
            post_id_container.slideDown();
            // Hide the quantity container
            quantity_container.slideUp();

            // Show the spinner
            spinner.addClass('is-active');

            $.ajax({
                url: ajaxurl,
                data: {
                    action: action,
                    nonce: gamipress_coupons_coupons.nonce,
                    post_type: post_type,
                    achievement_type: post_type, // Needle for gamipress_get_achievements_options_html action
                    selected: post_id_field.val()
                }, success: function( response ) {

                    // Hide the spinner
                    spinner.removeClass('is-active');

                    // Add the response and show the post id field
                    post_id_field.html( response );
                    post_id_field.slideDown();

                }
            });

        }

    });

    $('.gamipress-coupons-coupon-reward-post-type select').trigger('change');

    // Coupon notes

    $('#add-new-coupon-note').on('click', function(e) {
        e.preventDefault();

        // Toggle visibility
        $(this).parent().slideUp();
        $('#new-coupon-note-fieldset').slideDown();
    });

    // Save note

    $('#save-coupon-note').on('click', function(e) {
        e.preventDefault();

        var $this = $(this);

        if( $this.hasClass('disabled') ) {
            return;
        }

        // Disable the button
        $this.addClass('disabled');

        // Save the coupon note
        var title = $('#coupon-note-title').val();
        var description = $('#coupon-note-description').val();
        var notice = $('#new-coupon-note-submit .notice');

        if( title.length === 0 || description.length === 0 ) {
            notice.find('.error').html('Please, fill the form correctly');
            notice.removeClass('hidden');
            return;
        }

        if( ! notice.hasClass('hidden') ) {
            notice.addClass('hidden');
        }

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'gamipress_coupons_add_coupon_note',
                nonce: gamipress_coupons_coupons.nonce,
                coupon_id: $('#ct_edit_form input#object_id').val(),
                title: title,
                description: description
            },
            success: function( response ) {

                if( response.success ) {
                    // Add coupon note to the list of notes (at the top of the list!)
                    $('.coupon-notes-list tbody').prepend(response.data);

                    // Toggle visibility
                    $this.closest('#new-coupon-note-fieldset').slideUp();
                    $this.closest('#new-coupon-note-fieldset').prev().slideDown();

                    // Clear fields
                    $('#coupon-note-title').val('');
                    $('#coupon-note-description').val('');
                } else {
                    // Show error reported
                    notice.find('.error').html(response.data);
                    notice.removeClass('hidden');
                }

                // Restore the button
                $this.removeClass('disabled');
            }
        });
    });

    // Cancel add note

    $('#cancel-coupon-note').on('click', function(e) {
        e.preventDefault();

        // Toggle visibility
        $(this).closest('#new-coupon-note-fieldset').slideUp();
        $(this).closest('#new-coupon-note-fieldset').prev().slideDown();

        // Clear fields
        $('#coupon-note-title').val('');
        $('#coupon-note-description').val('');
    });

    // Delete note
    $('.coupon-note .row-actions .delete').on('click', function(e) {
        e.preventDefault();

        var confirmed = confirm('Do you want to remove this coupon note?');

        var $this = $(this);

        if ( confirmed ) {

            // Hide note
            $this.closest('.coupon-note').fadeOut();

            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'gamipress_coupons_delete_coupon_note',
                    nonce: gamipress_coupons_coupons.nonce,
                    coupon_note_id: $this.data('coupon-note-id'),
                },
                success: function( response ) {

                    if( response.success ) {

                        // Remove the note
                        $this.closest('.coupon-note').remove();

                    } else {
                        // TODO: Report error

                        // Show note again
                        $this.closest('.coupon-note').fadeIn();
                    }
                }
            });
        }
    });

})( jQuery );