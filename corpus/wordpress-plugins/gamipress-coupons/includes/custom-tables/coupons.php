<?php
/**
 * Coupons
 *
 * @package     GamiPress\Coupons\Custom_Tables\Coupons
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Parse query args for coupons
 *
 * @since  1.0.0
 *
 * @param string $where
 * @param CT_Query $ct_query
 *
 * @return string
 */
function gamipress_coupons_coupons_query_where( $where, $ct_query ) {

    global $ct_table;

    if( $ct_table->name !== 'gamipress_coupons' )
        return $where;

    $table_name = $ct_table->db->table_name;

    // Title
    if( isset( $ct_query->query_vars['title'] ) && absint( $ct_query->query_vars['title'] ) !== 0 ) {

        $title = $ct_query->query_vars['title'];

        if( is_array( $title ) ) {
            $title = implode( "', '", $title );

            $where .= " AND {$table_name}.title IN ( '{$title}' )";
        } else {
            $where .= " AND {$table_name}.title = '{$title}''";
        }
    }

    // Code
    if( isset( $ct_query->query_vars['code'] ) && absint( $ct_query->query_vars['code'] ) !== 0 ) {

        $code = $ct_query->query_vars['code'];

        if( is_array( $code ) ) {
            $code = implode( "', '", $code );

            $where .= " AND {$table_name}.code IN ( '{$code}' )";
        } else {
            $where .= " AND {$table_name}.code = '{$code}''";
        }
    }

    // Status
    if( isset( $ct_query->query_vars['status'] ) && absint( $ct_query->query_vars['status'] ) !== 0 ) {

        $status = $ct_query->query_vars['status'];

        if( is_array( $status ) ) {
            $status = implode( "', '", $status );

            $where .= " AND {$table_name}.status IN ( '{$status}' )";
        } else {
            $where .= " AND {$table_name}.status = '{$status}''";
        }
    }

    return $where;
}
add_filter( 'ct_query_where', 'gamipress_coupons_coupons_query_where', 10, 2 );

/**
 * Define the search fields for coupons
 *
 * @since 1.0.0
 *
 * @param array $search_fields
 *
 * @return array
 */
function gamipress_coupons_search_fields( $search_fields ) {

    $search_fields[] = 'title';
    $search_fields[] = 'code';
    $search_fields[] = 'status';

    return $search_fields;

}
add_filter( 'ct_query_gamipress_coupons_search_fields', 'gamipress_coupons_search_fields' );

/**
 * Columns for coupons list view
 *
 * @since  1.0.0
 *
 * @param array $columns
 *
 * @return array
 */
function gamipress_coupons_manage_coupons_columns( $columns = array() ) {

    $columns['coupon']      = __( 'Coupon', 'gamipress-coupons' );
    $columns['code']        = __( 'Code', 'gamipress-coupons' );
    $columns['max_uses']    = __( 'Max. Uses', 'gamipress-coupons' );
    $columns['date']        = __( 'Date Limits', 'gamipress-coupons' );
    $columns['status']      = __( 'Status', 'gamipress-coupons' );

    return $columns;
}
add_filter( 'manage_gamipress_coupons_columns', 'gamipress_coupons_manage_coupons_columns' );

/**
 * Sortable columns for coupons list view
 *
 * @since 1.0.0
 *
 * @param array $sortable_columns
 *
 * @return array
 */
function gamipress_coupons_manage_coupons_sortable_columns( $sortable_columns ) {

    $sortable_columns['coupon']     = array( 'title', false );
    $sortable_columns['code']       = array( 'code', false );
    $sortable_columns['max_uses']   = array( 'max_uses', false );
    $sortable_columns['status']     = array( 'status', false );

    return $sortable_columns;

}
add_filter( 'manage_gamipress_coupons_sortable_columns', 'gamipress_coupons_manage_coupons_sortable_columns' );

/**
 * Columns rendering for coupons list view
 *
 * @since  1.0.0
 *
 * @param string $column_name
 * @param integer $object_id
 */
function gamipress_coupons_manage_coupons_custom_column( $column_name, $object_id ) {

    // Setup vars
    $prefix = '_gamipress_coupons_';
    $coupon = ct_get_object( $object_id );

    switch( $column_name ) {
        case 'coupon':
            ?>

            <strong>
                <a href="<?php echo ct_get_edit_link( 'gamipress_coupons', $coupon->coupon_id ); ?>"><?php echo $coupon->title . ' (ID:' . $coupon->coupon_id . ')'; ?></a>
            </strong>

            <?php
            break;
        case 'code':
            ?>

            <?php echo $coupon->code; ?>

            <?php
            break;
        case 'max_uses':
            $uses = absint( ct_get_object_meta( $object_id, $prefix . 'uses', true ) );
            $max_uses = absint( $coupon->max_uses );
            $max_uses_per_user = absint( $coupon->max_uses_per_user );

            if( $max_uses === 0 )
                echo sprintf( __( 'Unlimited (%d uses)', 'gamipress-coupons' ), $uses );
            else
                echo $uses . '/' . $max_uses;

            if( $max_uses_per_user !== 0 )
                echo '<br><small>(' . $max_uses_per_user . ' per user)</small>';

            break;
        case 'date':

            if( $coupon->start_date !== '0000-00-00 00:00:00' && $coupon->end_date !== '0000-00-00 00:00:00' ) : ?>
                From: <abbr title="<?php echo date( 'Y/m/d g:i:s a', strtotime( $coupon->start_date ) ); ?>"><?php echo date( 'Y/m/d', strtotime( $coupon->start_date ) ); ?></abbr>
                <br>
                Until: <abbr title="<?php echo date( 'Y/m/d g:i:s a', strtotime( $coupon->end_date ) ); ?>"><?php echo date( 'Y/m/d', strtotime( $coupon->end_date ) ); ?></abbr>
            <?php elseif( $coupon->start_date !== '0000-00-00 00:00:00' && $coupon->end_date === '0000-00-00 00:00:00' ) : ?>
                Starts on <abbr title="<?php echo date( 'Y/m/d g:i:s a', strtotime( $coupon->start_date ) ); ?>"><?php echo date( 'Y/m/d', strtotime( $coupon->start_date ) ); ?></abbr>
            <?php elseif( $coupon->start_date === '0000-00-00 00:00:00' && $coupon->end_date !== '0000-00-00 00:00:00' ) : ?>
                Ends on <abbr title="<?php echo date( 'Y/m/d g:i:s a', strtotime( $coupon->end_date ) ); ?>"><?php echo date( 'Y/m/d', strtotime( $coupon->end_date ) ); ?></abbr>
            <?php endif;
            break;
        case 'status':
            $statuses = gamipress_coupons_get_coupon_statuses(); ?>

            <span class="gamipress-coupons-status gamipress-coupons-status-<?php echo $coupon->status; ?>"><?php echo ( isset( $statuses[$coupon->status] ) ? $statuses[$coupon->status] : $coupon->status ); ?></span>

            <?php
            break;
    }
}
add_action( 'manage_gamipress_coupons_custom_column', 'gamipress_coupons_manage_coupons_custom_column', 10, 2 );

/**
 * Turns array of date and time into a valid mysql date on update coupon data
 *
 * @since 1.0.0
 *
 * @param array $object_data
 * @param array $original_object_data
 *
 * @return array
 */
function gamipress_coupons_insert_coupon_data( $object_data, $original_object_data ) {

    global $ct_table;

    // If not is our coupon, return
    if( $ct_table->name !== 'gamipress_coupons' ) {
        return $object_data;
    }

    // Fix start date format
    if( isset( $object_data['start_date'] ) && ! empty( $object_data['start_date'] ) ) {
        $object_data['start_date'] = date( 'Y-m-d 00:00:00', strtotime( $object_data['start_date'] ) );
    }

    // Fix end date format
    if( isset( $object_data['end_date'] ) && ! empty( $object_data['end_date'] ) ) {
        $object_data['end_date'] = date( 'Y-m-d 23:59:59', strtotime( $object_data['end_date'] ) );
    }

    return $object_data;

}
add_filter( 'ct_insert_object_data', 'gamipress_coupons_insert_coupon_data', 10, 2 );

/**
 * Fire transition coupon status hooks on save coupon
 *
 * @since  1.0.0
 *
 * @param integer   $object_id
 * @param stdClass  $object_after
 * @param stdClass  $object_before
 */
function gamipress_coupons_on_save_coupon( $object_id, $object_after, $object_before ) {

    // If not is our coupon, return
    if( ! ( property_exists( $object_after, 'coupon_id' ) && property_exists( $object_after, 'coupon_key' ) ) )
        return;

    // Fire transition coupon status hooks
    gamipress_coupons_transition_coupon_status( $object_after->status, $object_before->status, $object_after );

}
add_action( 'ct_object_updated', 'gamipress_coupons_on_save_coupon', 10, 3 );

/**
 * Register custom coupons meta boxes
 *
 * @since  1.0.0
 */
function gamipress_coupons_add_coupons_meta_boxes() {

    add_meta_box( 'gamipress_coupons_actions', __( 'Actions', 'gamipress-coupons' ), 'gamipress_coupons_actions_meta_box', 'gamipress_coupons', 'side', 'core' );
    remove_meta_box( 'submitdiv', 'gamipress_coupons', 'side' );

}
add_action( 'add_meta_boxes', 'gamipress_coupons_add_coupons_meta_boxes' );

/**
 * Coupon actions meta box
 *
 * @since  1.0.0
 *
 * @param stdClass  $coupon
 */
function gamipress_coupons_actions_meta_box( $coupon ) {

    global $ct_table;

    ?>
    <div class="submitbox" id="submitpost" style="margin: -6px -12px -12px;">

        <div id="major-publishing-actions">

            <?php
            if ( current_user_can( $ct_table->cap->delete_item, $coupon->coupon_id ) ) {

                printf(
                    '<a href="%s" class="submitdelete deletion" onclick="%s" aria-label="%s">%s</a>',
                    ct_get_delete_link( $ct_table->name, $coupon->coupon_id ),
                    "return confirm('" .
                    esc_attr( __( "Are you sure you want to delete this item?\\n\\nClick \\'Cancel\\' to go back, \\'OK\\' to confirm the delete." ) ) .
                    "');",
                    esc_attr( __( 'Delete permanently' ) ),
                    __( 'Delete Permanently' )
                );

            } ?>

            <div id="publishing-action">
                <span class="spinner"></span>
                <?php submit_button( __( 'Save Changes' ), 'primary large', 'ct-save', false ); ?>
            </div>

            <div class="clear"></div>

        </div>

    </div>
    <?php
}

/**
 * Default data when creating a new item (similar to WP auto draft) see ct_insert_object()
 *
 * @since  1.0.0
 *
 * @param array $default_data
 *
 * @return array
 */
function gamipress_coupons_default_data( $default_data = array() ) {

    $default_data['status'] = 'active';
    $default_data['max_uses'] = 0;
    $default_data['max_uses_per_user'] = 1;

    return $default_data;
}
add_filter( 'ct_gamipress_coupons_default_data', 'gamipress_coupons_default_data' );

/**
 * Register custom coupons CMB2 meta boxes
 *
 * @since  1.0.0
 */
function gamipress_coupons_coupons_meta_boxes( ) {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_coupons_';

    // Coupon
    gamipress_add_meta_box(
        'gamipress-coupon',
        __( 'Coupon', 'gamipress-coupons' ),
        'gamipress_coupons',
        array(
            'title' => array(
                'name' 	=> __( 'Title', 'gamipress-coupons' ),
                'type' 	=> 'text',
            ),
            'code' => array(
                'name' 	=> __( 'Code', 'gamipress-coupons' ),
                'type' 	=> 'text',
            ),
        ),
        array(
            'priority' => 'core',
        )
    );

    // Coupon Details
    gamipress_add_meta_box(
        'gamipress-coupon-details',
        __( 'Coupon Details', 'gamipress-coupons' ),
        'gamipress_coupons',
        array(
            'status' => array(
                'name' 	=> __( 'Coupon Status', 'gamipress-coupons' ),
                'type' 	=> 'select',
                'options' => gamipress_coupons_get_coupon_statuses()
            ),
        ),
        array(
            'context' => 'side',
            'priority' => 'core',
        )
    );

    // Coupon Limits
    gamipress_add_meta_box(
        'gamipress-coupon-limits',
        __( 'Coupon Limits', 'gamipress-coupons' ),
        'gamipress_coupons',
        array(
            'start_date' => array(
                'name' 	=> __( 'Start Date', 'gamipress-coupons' ),
                'desc' 	=> __( 'Enter the start date (leave blank to no limit by a start date). If entered, the coupon can only by redeemed after or on this date.', 'gamipress-coupons' ),
                'type' 	=> 'text_date_timestamp',
                'escape_cb' => 'gamipress_coupons_date_field_escape_cb'
            ),
            'end_date' => array(
                'name' 	=> __( 'End Date', 'gamipress-coupons' ),
                'desc' 	=> __( 'Enter the end date (leave blank to no limit by a end date). If entered, the coupon can only by redeemed before or on this date.', 'gamipress-coupons' ),
                'type' 	=> 'text_date_timestamp',
                'escape_cb' => 'gamipress_coupons_date_field_escape_cb'
            ),
            'max_uses' => array(
                'name' 	=> __( 'Maximum Uses', 'gamipress-coupons' ),
                'desc' 	=> __( 'Maximum number of uses (set 0 for no maximum).', 'gamipress-coupons' ),
                'type' 	=> 'text',
                'attributes' => array(
                    'type' => 'number'
                ),
            ),
            'max_uses_per_user' => array(
                'name' 	=> __( 'Maximum Uses Per User', 'gamipress-coupons' ),
                'desc' 	=> __( 'Maximum number of uses per user (set 0 for no maximum).', 'gamipress-coupons' ),
                'type' 	=> 'text',
                'attributes' => array(
                    'type' => 'number'
                ),
            ),
        ),
        array(
            'context' => 'side',
            'priority' => 'core',
        )
    );

    // Coupon User Requirements
    gamipress_add_meta_box(
        'gamipress-coupon-user-requirements',
        __( 'User Requirements', 'gamipress-coupons' ),
        'gamipress_coupons',
        array(
            $prefix . 'restrict_to_users' => array(
                'name' 	=> __( 'Restrict to a group of users', 'gamipress-coupons' ),
                'desc' 	=> __( 'Limit the redemption of this coupon to a group of specific users.', 'gamipress-coupons' ),
                'type' 	=> 'checkbox',
                'classes' => 'gamipress-switch'
            ),
            $prefix . 'allowed_users' => array(
                'name' 	=> __( 'Allowed Users', 'gamipress-coupons' ),
                'type' 	=> 'advanced_select',
                'options_cb'  => 'gamipress_options_cb_users',
                // TODO: With multiple = true, CMB2 stores post metadata as multiple too, at this moment the unique way to avoid this issue
                //'multiple' => true,
                'attributes' => array(
                    'name' => $prefix . 'allowed_users[]',
                    'multiple' => 'true',
                )
            ),
            $prefix . 'allowed_roles' => array(
                'name' 	=> __( 'Allowed User Roles', 'gamipress-coupons' ),
                'type' 	=> 'advanced_select',
                'options_cb' => 'gamipress_coupons_get_roles_options',
                //'multiple' => true,
                'attributes' => array(
                    'name' => $prefix . 'allowed_roles[]',
                    'multiple' => 'true',
                )
            ),
            $prefix . 'excluded_users' => array(
                'name' 	=> __( 'Excluded Users', 'gamipress-coupons' ),
                'type' 	=> 'advanced_select',
                'options_cb'  => 'gamipress_options_cb_users',
                //'multiple' => true,
                'attributes' => array(
                    'name' => $prefix . 'excluded_users[]',
                    'multiple' => 'true',
                )
            ),
            $prefix . 'excluded_roles' => array(
                'name' 	=> __( 'Excluded User Roles', 'gamipress-coupons' ),
                'type' 	=> 'advanced_select',
                'options_cb' => 'gamipress_coupons_get_roles_options',
                //'multiple' => true,
                'attributes' => array(
                    'name' => $prefix . 'excluded_roles[]',
                    'multiple' => 'true',
                )
            ),
        ),
        array(
            'priority' => 'core',
        )
    );

    // Coupon rewards
    gamipress_add_meta_box(
        'gamipress-coupon-rewards-data',
        __( 'Coupon Rewards', 'gamipress-coupons' ),
        'gamipress_coupons',
        array(
            'coupon_rewards' => array(
                'type' 	=> 'group',
                'options'     => array(
                    'group_title'   => __( 'Reward {#}', 'gamipress-coupons' ),
                    'add_button'    => __( 'Add Reward', 'gamipress-coupons' ),
                    'remove_button' => '<i class="dashicons dashicons-no-alt"></i>',
                ),
                'fields' => apply_filters( 'gamipress_coupon_reward_fields', array(
                    'post_type' => array(
                        'name' 	=> __( 'Reward Type', 'gamipress-coupons' ),
                        'type' => 'advanced_select',
                        'options_cb' => 'gamipress_coupons_coupons_rewards_post_type_options_cb',
                        'classes' => 'gamipress-coupons-coupon-reward-post-type',
                    ),
                    'post_id' => array(
                        'name' 	=> __( 'Achievement', 'gamipress-coupons' ),
                        'type' => 'advanced_select',
                        'options_cb' => 'gamipress_options_cb_posts',
                        'classes' => 'gamipress-coupons-coupon-reward-post-id',
                    ),
                    'quantity' => array(
                        'name' 	=> __( 'Quantity', 'gamipress-coupons' ),
                        'type' => 'text',
                        'attributes' => array(
                            'type' => 'number',
                            'placeholder' => '0'
                        ),
                        'classes' => 'gamipress-coupons-coupon-reward-quantity',
                    ),

                    'coupon_reward_id' => array(
                        'type' => 'text',
                        'attributes' => array(
                            'type' => 'hidden'
                        ),
                    ),
                    'coupon_id' => array(
                        'type' => 'text',
                        'attributes' => array(
                            'type' => 'hidden'
                        ),
                    ),

                ) ),
            )
        ),
        array(
            'priority' => 'core',
        )
    );

    // Coupon notes
    gamipress_add_meta_box(
        'gamipress-coupon-notes-data',
        __( 'Coupon Notes', 'gamipress-coupons' ),
        'gamipress_coupons',
        array(
            'coupon_notes' => array(
                'content_cb' => 'gamipress_coupons_coupon_notes_table',
                'type' 	=> 'html',
            )
        )
    );

}
add_action( 'cmb2_admin_init', 'gamipress_coupons_coupons_meta_boxes' );

function gamipress_coupons_coupons_rewards_post_type_options_cb() {

    $options = array();

    $points_types = gamipress_get_points_types();
    $achievement_types = gamipress_get_achievement_types();
    $rank_types = gamipress_get_rank_types();

    // Points types
    if( ! empty( $points_types ) ) {

        $options['Points Types'] = array();

        foreach( $points_types as $slug => $data ) {
            $options['Points Types'][$slug] = $data['plural_name'];
        }

    }

    // Achievement types
    if( ! empty( $achievement_types ) ) {

        $options['Achievement Types'] = array();

        foreach( $achievement_types as $slug => $data ) {
            $options['Achievement Types'][$slug] = $data['singular_name'];
        }

    }

    // Rank types
    if( ! empty( $rank_types ) ) {

        $options['Rank Types'] = array();

        foreach( $rank_types as $slug => $data ) {
            $options['Rank Types'][$slug] = $data['singular_name'];
        }

    }

    return $options;

}

function gamipress_coupons_coupon_rewards_field_value( $value, $object_id, $args, $field ) {

    global $ct_registered_tables, $ct_table, $ct_cmb2_override;

    $original_ct_table = $ct_table;

    if( $ct_cmb2_override !== true )
        return $value;

    $coupon_rewards = gamipress_coupons_get_coupon_rewards( $object_id, ARRAY_N );

    $ct_table = $original_ct_table;

    return $coupon_rewards;

}
add_filter( 'cmb2_override_coupon_rewards_meta_value', 'gamipress_coupons_coupon_rewards_field_value', 10, 4 );

function gamipress_coupons_coupon_rewards_field_save( $check, $args, $field_args, $field ) {

    global $ct_registered_tables, $ct_table, $ct_cmb2_override;

    if( $ct_cmb2_override !== true )
        return $check;

    $original_ct_table = $ct_table;
    $ct_table = ct_setup_table( 'gamipress_coupon_rewards' );

    $coupon_rewards = gamipress_coupons_get_coupon_rewards( $args['id'], ARRAY_N );
    $received_rewards = $args['value'];

    foreach( $received_rewards as $reward_index => $reward_data ) {

        if( empty( $reward_data['coupon_reward_id'] ) ) {

            // New coupon reward
            unset( $reward_data['coupon_reward_id'] );

            $reward_data['coupon_id'] = $args['id'];


            $ct_table->db->insert( $reward_data );

        } else {

            // Already existent reward, so update
            $ct_table->db->update( $reward_data, array(
                'coupon_reward_id' => $reward_data['coupon_reward_id']
            ) );

        }

    }

    // Next, lets to check the removed rewards
    $coupon_rewards_ids = array_map( function( $coupon_reward ) {
        return $coupon_reward['coupon_reward_id'];
    }, $coupon_rewards );

    foreach( $received_rewards as $reward_index => $reward_data ) {

        if( empty( $reward_data['coupon_reward_id'] ) ) {
            continue;
        }

        if( ! in_array( $reward_data['coupon_reward_id'], $coupon_rewards_ids ) ) {

            // Delete the reward that has not been received
            $ct_table->db->delete( $reward_data['coupon_reward_id'] );

        }
    }

    $ct_table = $original_ct_table;

    return true;

}
add_filter( 'cmb2_override_coupon_rewards_meta_save', 'gamipress_coupons_coupon_rewards_field_save', 10, 4 );

function gamipress_coupons_coupon_notes_table( $field, $object_id, $object_type ) {

    ct_setup_table( 'gamipress_coupons' );

    $coupon = ct_get_object( $object_id );

    $coupon_notes = gamipress_coupons_get_coupon_notes( $object_id ); ?>

    <table class="widefat fixed striped comments wp-list-table comments-box coupon-notes-list">

        <tbody id="the-comment-list" data-wp-lists="list:comment">

        <?php foreach( $coupon_notes as $coupon_note ) :

            gamipress_coupons_admin_render_coupon_note( $coupon_note, $coupon );

        endforeach; ?>

        </tbody>

    </table>

    <div id="new-coupon-note-form">
        <p class="hide-if-no-js">
            <a id="add-new-coupon-note" class="button" href="#"><?php _e( 'Add Coupon Note', 'gamipress-coupons' ) ?></a>
        </p>

        <fieldset id="new-coupon-note-fieldset" style="display: none;">

            <div id="new-coupon-note-title-wrap">
                <input type="text" id="coupon-note-title" size="50" placeholder="<?php _e( 'Title', 'gamipress-coupons' ); ?>">
            </div>

            <div id="new-coupon-note-description-wrap">
                <textarea id="coupon-note-description" placeholder="<?php _e( 'Note', 'gamipress-coupons' ); ?>"></textarea>
            </div>

            <div id="new-coupon-note-submit" class="new-coupon-note-submit">
                <p>
                    <a href="#" id="save-coupon-note" class="save button button-primary alignright"><?php _e( 'Add Coupon Note', 'gamipress-coupons' ) ?></a>
                    <a href="#" id="cancel-coupon-note" class="cancel button alignleft"><?php _e( 'Cancel', 'gamipress-coupons' ) ?></a>
                    <span class="waiting spinner"></span>
                </p>
                <br class="clear">
                <div class="notice notice-error notice-alt inline hidden">
                    <p class="error"></p>
                </div>
            </div>

        </fieldset>
    </div>

    <?php
}

/**
 * Render the given coupon note
 *
 * @since 1.0.0
 *
 * @param stdClass $coupon_note
 * @param stdClass $coupon
 */
function gamipress_coupons_admin_render_coupon_note( $coupon_note, $coupon ) {

    if( $coupon_note->user_id === '-1' ) {
        // -1 is used for system notes
        $user_name = __( 'GamiPress Bot', 'gamipress-coupons' );

    } else if( $coupon_note->user_id === '0' ) {
        // Get the user details from the coupon
        $user_name = $coupon->first_name . ' ' . $coupon->last_name;
        $user_email =  $coupon->email;
    } else {
        // Get the user details from the user profile
        $user = new WP_User( $coupon_note->user_id );

        $user_name = $user->display_name . ' (' .  $user->user_login .')';
        $user_email =$user->user_email;
    }

    ?>

    <tr id="coupon-note-<?php echo $coupon_note->coupon_note_id ?>" class="comment coupon-note byuser comment-author-admin depth-1 approved">
        <td class="author column-author">
            <strong><?php echo $user_name; ?></strong>
            <?php if( isset( $user_email ) ) : ?>
                <br>
                <a href="mailto:<?php echo $user_email; ?>"><?php echo $user_email; ?></a>
            <?php endif; ?>
        </td>
        <td class="comment column-comment has-row-actions column-primary">
            <p>
                <strong class="coupon-note-title"><?php echo $coupon_note->title; ?></strong>
                <span class="coupon-note-date"><?php echo date( 'Y/m/d H:i', strtotime( $coupon_note->date ) ); ?></span>
                <br>
                <span class="coupon-note-description"><?php echo $coupon_note->description; ?></span>
            </p>

            <div class="row-actions">
                <span class="trash"><a href="#" class="delete vim-d vim-destructive" data-coupon-note-id="<?php echo $coupon_note->coupon_note_id; ?>" aria-label="<?php _e( 'Delete this coupon note', 'gamipress-coupons' ); ?>"><?php _e( 'Delete', 'gamipress-coupons' ); ?></a></span>
            </div>
        </td>
    </tr>

    <?php
}