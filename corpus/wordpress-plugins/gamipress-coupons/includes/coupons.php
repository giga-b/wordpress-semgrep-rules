<?php
/**
 * Coupons Functions
 *
 * @package     GamiPress\Coupons\Coupons_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the registered coupon statuses
 *
 * @since  1.0.0
 *
 * @return array Array of coupon statuses
 */
function gamipress_coupons_get_coupon_statuses() {

    return apply_filters( 'gamipress_coupons_get_coupon_statuses', array(
        'active' => __( 'Active', 'gamipress-coupons' ),
        'inactive' => __( 'Inactive', 'gamipress-coupons' ),
        'expired' => __( 'Expired', 'gamipress-coupons' ),
    ) );

}

/**
 * Update the coupon status
 *
 * @since  1.0.0
 *
 * @param integer   $coupon_id    The coupon ID
 * @param string    $new_status     The coupon new status
 *
 * @return bool                     True if status changes successfully, false if not
 */
function gamipress_coupons_update_coupon_status( $coupon_id, $new_status ) {

    // Check if new status is registered
    $coupon_statuses = gamipress_coupons_get_coupon_statuses();
    $coupon_statuses = array_keys( $coupon_statuses );

    if( ! in_array( $new_status, $coupon_statuses ) ) {
        return false;
    }

    // Setup the CT Table
    $ct_table = ct_setup_table( 'gamipress_coupons' );

    // Check the object
    $coupon = ct_get_object( $coupon_id );

    if( ! $coupon ) {
        return false;
    }


    // Prevent set the same status
    if( $coupon->status === $new_status ) {
        return false;
    }

    $old_status = $coupon->status;

    // Update the coupon status
    $ct_table->db->update(
        array( 'status' => $new_status ),
        array( 'coupon_id' => $coupon_id )
    );

    // Fire the coupon status transition hooks
    gamipress_coupons_transition_coupon_status( $new_status, $old_status, $coupon );

    return true;

}

/**
 * Fires hooks related to the coupon status
 *
 * @since  1.0.0
 *
 * @param string    $new_status     The coupon new status
 * @param string    $old_status     The coupon old status
 * @param object    $coupon       The coupon object
 */
function gamipress_coupons_transition_coupon_status( $new_status, $old_status, $coupon ) {

    // Trigger a common transition action to hook any change
    do_action( 'gamipress_coupons_transition_coupon_status', $new_status, $old_status, $coupon );

    // Trigger a specific transition action to hook a desired change
    do_action( "gamipress_coupons_{$old_status}_to_{$new_status}", $coupon );

    if( $new_status === 'active' && $old_status !== 'active' ) {
        // Trigger active coupon hook
        do_action( 'gamipress_coupons_active_coupon', $coupon );
    }

    if( $new_status === 'inactive' && $old_status !== 'inactive' ) {
        // Trigger inactive coupon hook
        do_action( 'gamipress_coupons_inactive_coupon', $coupon );
    }

    if( $new_status === 'expired' && $old_status !== 'expired' ) {
        // Trigger expired coupon hook
        do_action( 'gamipress_coupons_expired_coupon', $coupon );
    }

}

/**
 * Award the coupon rewards to the user
 *
 * @since  1.0.0
 *
 * @param int|stdClass  $coupon_id
 * @param int           $user_id
 *
 * @return bool
 */
function gamipress_coupons_award_coupon_rewards( $coupon_id = null, $user_id = null ) {

    ct_setup_table( 'gamipress_coupons' );

    $coupon = ct_get_object( $coupon_id );

    ct_reset_setup_table();

    // Can't register a not existent coupon
    if( ! $coupon )
        return false;

    // Set the current user ID if not passed
    if( $user_id === null )
        $user_id = get_current_user_id();

    if( $user_id === 0 )
        return false;

    // Get our types
    $points_types = gamipress_get_points_types();
    $points_types_slugs = gamipress_get_points_types_slugs();
    $achievement_types = gamipress_get_achievement_types();
    $achievement_types_slugs = gamipress_get_achievement_types_slugs();
    $rank_types = gamipress_get_rank_types();
    $rank_types_slugs = gamipress_get_rank_types_slugs();

    $coupon_rewards = gamipress_coupons_get_coupon_rewards( $coupon->coupon_id );
    $notes = array();

    // Loop all rewards to check reward types assigned
    foreach( $coupon_rewards as $coupon_reward ) {

        // Skip if not reward assigned
        if( absint( $coupon_reward->post_id ) === 0 ) {
            continue;
        }

        $post_type = gamipress_get_post_type( $coupon_reward->post_id );

        // Skip if can not get the type of this reward
        if( ! $post_type ) {
            continue;
        }

        // Setup table on each loop for the usage of ct_get_object_meta() and ct_update_object_meta()
        ct_setup_table( 'gamipress_coupon_rewards' );

        if( $post_type === 'points-type' && in_array( $coupon_reward->post_type, $points_types_slugs ) ) {
            // Is a points

            $quantity = absint( $coupon_reward->quantity );

            if( $quantity > 0 ) {

                $points_type = $coupon_reward->post_type;

                // Award points to the user
                gamipress_award_points_to_user( $user_id, $quantity, $points_type );

                $earning_text = sprintf( __( '%s for redeem the coupon %s', 'gamipress-coupons' ), gamipress_format_points( $quantity, $points_type ), $coupon->code );

                /**
                 * Filter available to override the points earning text
                 *
                 * @since 1.0.7
                 *
                 * @param string    $earning_text   The earning text
                 * @param int       $user_id        The user ID
                 * @param int       $points         The points amount
                 * @param string    $points_type    The points type
                 * @param stdClass  $coupon         The coupon object
                 * @param stdClass  $coupon_reward  The coupon reward object
                 *
                 * @return string
                 */
                $earning_text = apply_filters( 'gamipress_coupons_points_earning_text', $earning_text, $user_id, $quantity, $points_type, $coupon, $coupon_reward );

                // Insert the custom user earning for the points award
                gamipress_insert_user_earning( $user_id, array(
                    'title'	        => $earning_text,
                    'user_id'	    => $user_id,
                    'post_id'	    => gamipress_get_points_type_id( $points_type ),
                    'post_type' 	=> 'points-type',
                    'points'	    => $quantity,
                    'points_type'	=> $points_type,
                    'date'	        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
                ) );

                // Setup vars for the coupon note
                $points_type = $points_types[$points_type];

                // Add an informative note to let user know those points has been awarded
                $notes[] = sprintf( __( '%d %s', 'gamipress-coupons' ),
                    // X points
                    $quantity, _n( $points_type['singular_name'], $points_type['plural_name'], $quantity )
                );

            }

        } else if( in_array( $post_type, $achievement_types_slugs ) ) {
            // Is an achievement

            // Award achievement to user
            gamipress_award_achievement_to_user( $coupon_reward->post_id, $user_id );

            // Setup vars for the coupon note
            $achievement_type = $achievement_types[$coupon_reward->post_type];
            $achievement_title = get_post_field( 'post_title', $coupon_reward->post_id );

            // Add an informative note to let user know this achievement has been awarded
            $notes[] = sprintf( __( '%s %s', 'gamipress-coupons' ),
                // Achievement Title
                $achievement_type['singular_name'], $achievement_title
            );

        } else if( in_array( $post_type, $rank_types_slugs ) ) {
            // Is a rank

            // Award rank to the user
            gamipress_award_rank_to_user( $coupon_reward->post_id, $user_id );

            // Setup vars for the coupon note
            $rank_type = $rank_types[$coupon_reward->post_type];
            $rank_title = get_post_field( 'post_title', $coupon_reward->post_id );

            // Add an informative note to let user know this rank has been awarded
            $notes[] = sprintf( __( '%s %s', 'gamipress-coupons' ),
                // Rank Title
                $rank_type['singular_name'], $rank_title
            );

        }

    }

    $user = get_userdata( $user_id );

    // Add an informative note to know that user has redeemed this coupon and the rewards awarded
    gamipress_coupons_insert_coupon_note( $coupon->coupon_id,
        __( 'Coupon redemption', 'gamipress-coupons' ),
        sprintf( __( '%s has redeemed this coupon to earn:', 'gamipress-coupons' ),
            // User link
            $user->display_name . ' (' . $user->user_email . ')'
        )
        . ( count( $notes ) ? '<br>- ' . implode( '<br>- ', $notes ) : '' )
    );

    return true;

}

/**
 * Get the coupon rewards
 *
 * @since  1.0.0
 *
 * @param integer $coupon_id   The coupon ID
 *
 * @return array                Array of coupon rewards
 */
function gamipress_coupons_get_coupon_rewards( $coupon_id, $output = OBJECT ) {

    $cache = gamipress_get_cache( 'gamipress_coupon_rewards', array() );

    // If result already cached, return it
    if( isset( $cache[$coupon_id] ) ) {
        return $cache[$coupon_id];
    }

    ct_setup_table( 'gamipress_coupon_rewards' );

    $ct_query = new CT_Query( array(
        'coupon_id' => $coupon_id,
        'order' => 'ASC'
    ) );

    $coupon_rewards = $ct_query->get_results();

    if( $output === ARRAY_N || $output === ARRAY_A ) {

        // Turn array of objects into an array of arrays
        foreach( $coupon_rewards as $coupon_reward_index => $coupon_reward ) {
            $coupon_rewards[$coupon_reward_index] = (array) $coupon_reward;
        }

    }

    ct_reset_setup_table();

    // Cache results for next time
    $cache[$coupon_id] = $coupon_rewards;

    gamipress_set_cache( 'gamipress_coupon_rewards', $cache );

    return $coupon_rewards;

}

/**
 * Inset a coupon note
 *
 * @since  1.0.0
 *
 * @param integer   $coupon_id    The coupon ID
 * @param string    $title          The coupon note title
 * @param string    $description    The coupon note description
 * @param integer   $user_id        The user ID (-1 = GamiPress BOT, 0 = Guest)
 *
 * @return bool|integer             The coupon note ID or false
 */
function gamipress_coupons_insert_coupon_note( $coupon_id, $title, $description, $user_id = -1 ) {

    $ct_table = ct_setup_table( 'gamipress_coupon_notes' );

    $return = $ct_table->db->insert( array(
        'coupon_id' => $coupon_id,
        'title' => $title,
        'description' => $description,
        'user_id' => $user_id,
        'date' => date( 'Y-m-d H:i:s' ),
    ) );

    ct_reset_setup_table();

    return $return;

}

/**
 * Get the coupon notes
 *
 * @since  1.0.0
 *
 * @param integer $coupon_id  The coupon ID
 *
 * @return array                Array of coupon notes
 */
function gamipress_coupons_get_coupon_notes( $coupon_id, $output = OBJECT ) {

    ct_setup_table( 'gamipress_coupon_notes' );

    $ct_query = new CT_Query( array(
        'coupon_id' => $coupon_id,
        'order' => 'DESC'
    ) );

    $coupon_notes = $ct_query->get_results();

    if( $output === ARRAY_N || $output === ARRAY_A ) {

        // Turn array of objects into an array of arrays
        foreach( $coupon_notes as $coupon_note_index => $coupon_note ) {
            $coupon_notes[$coupon_note_index] = (array) $coupon_note;
        }

    }

    ct_reset_setup_table();

    return $coupon_notes;

}

/**
 * Get the coupon id querying it by the given field and desired field value
 *
 * @since  1.0.0
 *
 * @param string $field   The field to query
 * @param string $value   The field value to filter
 *
 * @return integer        The coupon ID
 */
function gamipress_coupons_get_coupon_id_by( $field, $value ) {

    global $wpdb;

    // Setup table
    $ct_table = ct_setup_table( 'gamipress_coupons' );

    $coupon_id = $wpdb->get_var( $wpdb->prepare( "SELECT {$ct_table->db->primary_key} FROM {$ct_table->db->table_name} WHERE {$field} = %s LIMIT 1", $value ) );

    ct_reset_setup_table();

    return absint( $coupon_id );

}