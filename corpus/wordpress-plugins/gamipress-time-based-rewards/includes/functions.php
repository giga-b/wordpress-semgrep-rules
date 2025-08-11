<?php
/**
 * Functions
 *
 * @package GamiPress\Time_Based_Rewards\Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check if user can claim a specific time-based reward
 *
 * @since 1.0.0
 *
 * @param int $time_based_reward_id
 * @param int $user_id
 *
 * @return bool
 */
function gamipress_time_based_rewards_user_can_claim( $time_based_reward_id, $user_id ) {

    // Bail if not time-based reward given
    if( $time_based_reward_id === 0 )
        return false;

    // Bail if not user given
    if( $user_id === 0 )
        return false;

    $can_claim = false;

    $next_claim_date = gamipress_time_based_rewards_get_next_claim_date( $time_based_reward_id, $user_id );

    if( $next_claim_date )
        $can_claim = current_time( 'timestamp' ) >= strtotime( $next_claim_date );

    /**
     * Filters the check if user can claim a specific time-based reward
     *
     * @since 1.0.0
     *
     * @param bool  $can_claim
     * @param int   $time_based_reward_id
     * @param int   $user_id
     *
     * @return bool
     */
    return apply_filters( 'gamipress_time_based_rewards_user_can_claim', $can_claim , $time_based_reward_id, $user_id );

}

/**
 * Get the date from user can claim a specific time-based reward again in human format (0H 0M 0S)
 *
 * @since 1.0.0
 *
 * @param int $time_based_reward_id
 * @param int $user_id
 *
 * @return string                       In format: 0H 0M 0S
 */
function gamipress_time_based_rewards_get_human_next_claim_date( $time_based_reward_id, $user_id ) {

    // Bail if not time-based reward given
    if( $time_based_reward_id === 0 )
        return '';

    // Bail if not user given
    if( $user_id === 0 )
        return '';

    $human_date = '';

    $next_claim_date = gamipress_time_based_rewards_get_next_claim_date( $time_based_reward_id, $user_id );

    if( $next_claim_date ) {

        if( current_time( 'timestamp' ) >= strtotime( $next_claim_date ) ) {
            // If is already available there is no need to calculate interval
            $human_date = '0H 0M 0S';
        } else {
            try {
                $date = new DateTime( date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );
                $next_date = new DateTime( $next_claim_date );

                $diff = $next_date->diff( $date );

                $d = ( $diff->d ? $diff->d : 0 );
                $h = ( $diff->h ? $diff->h : 0 );
                $m = ( $diff->i ? $diff->i : 0 );
                $s = ( $diff->s ? $diff->s : 0 );

                if( $d > 0 )
                    $human_date = "{$d}D {$h}H {$m}M {$s}S";
                else
                    $human_date = "{$h}H {$m}M {$s}S";

            } catch ( Exception $e ) {
                // In case of error return time to 0
                $human_date = '0H 0M 0S';
            }

        }

    }

    /**
     * Filters the date from user can claim a specific time-based reward again in human format (0H 0M 0S)
     *
     * @since 1.0.0
     *
     * @param string|false  $human_date             In format: 0H 0M 0S
     * @param int           $time_based_reward_id
     * @param int           $user_id
     *
     * @return string|false                         In format: 0H 0M 0S
     */
    return apply_filters( 'gamipress_time_based_rewards_get_human_next_claim_date', $human_date, $time_based_reward_id, $user_id );

}

/**
 * Get the date from user can claim a specific time-based reward again
 *
 * @since 1.0.0
 *
 * @param int $time_based_reward_id
 * @param int $user_id
 *
 * @return string|false                 In format: Y-m-d H:i:s
 */
function gamipress_time_based_rewards_get_next_claim_date( $time_based_reward_id, $user_id ) {

    // Bail if not time-based reward given
    if( $time_based_reward_id === 0 )
        return false;

    // Bail if not user given
    if( $user_id === 0 )
        return false;

    // Setup vars
    $prefix = '_gamipress_time_based_rewards_';

    $recurrence = gamipress_get_post_meta( $time_based_reward_id, $prefix . 'recurrence' );

    $hours = ( isset( $recurrence['hours'] ) ? absint( $recurrence['hours'] ) : 0 );
    $minutes = ( isset( $recurrence['minutes'] ) ? absint( $recurrence['minutes'] ) : 0 );
    $seconds = ( isset( $recurrence['seconds'] ) ? absint( $recurrence['seconds'] ) : 0 );

    // Bail if not recurrence properly setup
    if( ( $hours + $minutes + $seconds ) === 0 )
        return false;

    // Get last time user has claimed this time-based reward
    $last_claim = gamipress_time_based_rewards_get_last_claim_date( $time_based_reward_id, $user_id );

    // If user hasn't earned it yet, them base last claim on time-based reward date
    if( ! $last_claim ) {
        $last_claim = gamipress_get_post_date( $time_based_reward_id );
    }

    $last_claim = strtotime( $last_claim );

    $next_claim = strtotime("+{$hours} hours +{$minutes} minutes +{$seconds} seconds", $last_claim );

    $next_claim = date( 'Y-m-d H:i:s', $next_claim );

    /**
     * Filters the date from user can claim a specific time-based reward again
     *
     * @since 1.0.0
     *
     * @param string|false  $next_claim_date        In format: Y-m-d H:i:s
     * @param int           $time_based_reward_id
     * @param int           $user_id
     *
     * @return string|false                         In format: Y-m-d H:i:s
     */
    return apply_filters( 'gamipress_time_based_rewards_get_next_claim_date', $next_claim, $time_based_reward_id, $user_id );

}

/**
 * Get the date of the last time user has claimed a specific time-based reward
 *
 * @since 1.0.0
 *
 * @param int $time_based_reward_id
 * @param int $user_id
 *
 * @return string|false                 In format: Y-m-d H:i:s
 */
function gamipress_time_based_rewards_get_last_claim_date( $time_based_reward_id, $user_id ) {

    // Bail if not time-based reward given
    if( $time_based_reward_id === 0 )
        return false;

    // Bail if not user given
    if( $user_id === 0 )
        return false;

    $last_claim = gamipress_get_user_last_log( $user_id, array(
        'type'      => 'time_based_reward',
        'post_id'   => $time_based_reward_id,
    ) );

    $last_claim_date = ( $last_claim ? $last_claim->date : false );

    /**
     * Filters the date of the last time user has claimed a specific time-based reward
     *
     * @since 1.0.0
     *
     * @param string|false  $last_claim_date        In format: Y-m-d H:i:s
     * @param int           $time_based_reward_id
     * @param int           $user_id
     *
     * @return string|false                         In format: Y-m-d H:i:s
     */
    return apply_filters( 'gamipress_time_based_rewards_get_last_claim_date', $last_claim_date, $time_based_reward_id, $user_id );

}

/**
 * Get a random achievement of received type(s)
 *
 * @since 1.0.0
 *
 * @param string    $post_type  The achievement types to search
 * @param array     $exclude    Array of post IDs to exclude
 *
 * @return int
 */
function gamipress_time_based_rewards_get_random_achievement( $post_type, $exclude = array() ) {

    global $wpdb;

    if( empty( $post_type ) || $post_type === 'all' ) {
        $post_types = gamipress_get_achievement_types_slugs();
    } else {
        $post_types = array( $post_type );
    }

    $posts = GamiPress()->db->posts;

    // Get the random achievement ID
    $achievement_id = absint( $wpdb->get_var(
        "SELECT p.ID 
        FROM {$posts} AS p 
        WHERE p.post_status = 'publish'
        AND p.post_type IN ( '" . implode( "', '", $post_types ) . "' ) 
        " . ( count( $exclude ) ? "AND p.ID NOT IN ( " . implode( ", ", $exclude ) . " )" : "" ) . "
        ORDER BY RAND()
        LIMIT 1"
    ) );

    return $achievement_id;

}
