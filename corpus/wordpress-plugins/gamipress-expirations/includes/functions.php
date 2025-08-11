<?php
/**
 * Functions
 *
 * @package GamiPress\Expirations\Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register custom cron schedules
 *
 * @since 1.0.0
 *
 * @param array $schedules
 *
 * @return array
 */
function gamipress_expirations_cron_schedules( $schedules ) {

    $schedules['five_minutes'] = array(
        'interval' => 300,
        'display'  => __( 'Every five minutes', 'gamipress-email-digests' ),
    );

    return $schedules;

}
add_filter( 'cron_schedules', 'gamipress_expirations_cron_schedules' );

/**
 * Checks earnings that expires today
 *
 * This function is only intended to be used by WordPress cron.
 *
 * To force the use of this function outside cron, set a code like:
 * define( 'DOING_CRON', true );
 * gamipress_expirations_check_expired_earnings();
 *
 * @since 1.0.0
 *
 * @param string $date A valid date in "Y-m-d H:i:s" format
 */
function gamipress_expirations_check_expired_earnings( $date = '' ) {

    global $wpdb;

    $prefix = '_gamipress_expirations_';

    // Check date
    if( empty( $date ) ) {
        $date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
    }

    $user_earnings 		= GamiPress()->db->user_earnings;
    $user_earnings_meta = GamiPress()->db->user_earnings_meta;

    // Get earnings that expires on this date
    $earnings = $wpdb->get_results( $wpdb->prepare(
        "SELECT ue.*
        FROM {$user_earnings} AS ue 
        LEFT JOIN {$user_earnings_meta} AS uem ON ( ue.user_earning_id = uem.user_earning_id ) 
        WHERE uem.meta_key = %s 
        AND uem.meta_value <= %s
        ORDER BY ue.date ASC",
        $prefix . 'expiration_date',
        date( 'Y-m-d H:i:s', strtotime( $date ) )
    ) );

    foreach( $earnings as $user_earning ) {

        // Setup vars
        $user_earning_id = absint( $user_earning->user_earning_id );
        $user_id = absint( $user_earning->user_id );
        $post_id = absint( $user_earning->post_id );

        // Check if element should expire
        ct_setup_table( 'gamipress_user_earnings' );
        $expiration_date = ct_get_object_meta( $user_earning_id, $prefix . 'expiration_date', true );
        ct_reset_setup_table();

        // Skip if empty expiration date
        if( empty( $expiration_date ) ) {
            continue;
        }

        // Skip if element should not expire right now
        if( strtotime( $date ) < strtotime( $expiration_date ) ) {
            continue;
        }

        // Skip earning if expiration aborted
        if( gamipress_expirations_maybe_abort_expiration( $user_earning ) ) {
            continue;
        }

        /**
         * Action triggered on earning expiration
         *
         * @since 1.0.0
         *
         * @param int       $user_id            The user ID
         * @param int       $post_id            The post ID
         * @param int       $user_earning_id    The user earning ID
         * @param stdClass  $user_earning       The user earning object
         */
        do_action( 'gamipress_expirations_earning_expired', $user_id, $post_id, $user_earning_id, $user_earning );

        // Revoke the achievement to the user
        gamipress_revoke_achievement_to_user( $post_id, $user_id, $user_earning_id );

    }

}
add_action( 'gamipress_expirations_five_minutes_cron', 'gamipress_expirations_check_expired_earnings' );
add_action( 'gamipress_expirations_hourly_cron', 'gamipress_expirations_check_expired_earnings' );

/**
 * Helper function to determine if expiration gets aborted for a specific item
 *
 * @since 1.0.0
 *
 * @param stdClass  $user_earning       The user earning object
 *
 * @return bool
 */
function gamipress_expirations_maybe_abort_expiration( $user_earning ) {

    // Setup vars
    $abort_expiration = false;
    $user_earning_id = absint( $user_earning->user_earning_id );
    $user_id = absint( $user_earning->user_id );
    $post_id = absint( $user_earning->post_id );

    if( in_array( $user_earning->post_type, array( 'step', 'rank-requirement' ) ) ) {
        // Step and rank requirements

        $parent_id = absint( gamipress_get_post_field( 'post_parent', $post_id ) );

        // Abort expiration if user has earned the achievement or rank
        if( gamipress_get_earnings_count( array(
                'user_id' => $user_id,
                'post_id' => $parent_id,
                'since' => strtotime( $user_earning->date ) - 1
            ) ) > 0 ) {
            $abort_expiration = true;
        }

    } else if( in_array( $user_earning->post_type, gamipress_get_rank_types_slugs() ) ) {
        // Ranks

        $user_rank_id = gamipress_get_user_rank_id( $user_id, $user_earning->post_type );

        // Abort expiration if user is in a different rank
        if( $user_rank_id !== $post_id ) {
            $abort_expiration = true;
        }

    }

    /**
     * Filter to override earning expiration calculation
     *
     * @since 1.0.0
     *
     * @param bool      $abort_expiration
     * @param int       $user_id            The user ID
     * @param int       $post_id            The post ID
     * @param int       $user_earning_id    The user earning ID
     * @param stdClass  $user_earning       The user earning object
     */
    $abort_expiration = apply_filters( 'gamipress_expirations_abort_expiration', $abort_expiration, $user_id, $post_id, $user_earning_id, $user_earning );

    return $abort_expiration;

}

/**
 * Checks if given date is a valid date in Y-m-d format
 *
 * @since 1.0.0
 *
 * @param string $date
 *
 * @return bool
 */
function gamipress_expirations_is_a_valid_date( $date,  $format = 'Y-m-d H:i:s' ) {

    return (bool) strtotime( $date ) && date( $format, strtotime( $date ) ) === $date;

}

/**
 * Get the last earning
 *
 * @since  1.0.0
 *
 * @param  array $query User earning query parameters
 *
 * @return stdClass       The last earning date
 */
function gamipress_expirations_get_last_earning( $query = array() ) {

    global $wpdb;

    $where = gamipress_get_earnings_where( $query );

    // Merge all wheres
    $where = implode( ' AND ', $where );

    $user_earnings = GamiPress()->db->user_earnings;

    return $wpdb->get_row( "SELECT ue.* FROM {$user_earnings} AS ue WHERE {$where} ORDER BY ue.date DESC LIMIT 1" );

}