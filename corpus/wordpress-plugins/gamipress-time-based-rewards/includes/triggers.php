<?php
/**
 * Triggers
 *
 * @package     GamiPress\Time_Based_Rewards\Triggers
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin activity triggers
 *
 * @since 1.0.0
 *
 * @param array $activity_triggers
 *
 * @return mixed
 */
function gamipress_time_based_rewards_activity_triggers( $activity_triggers ) {

    $activity_triggers[__( 'Time-based Rewards', 'gamipress-time-based-rewards' )] = array(

        // Claim time-based reward
        'gamipress_time_based_rewards_claim_any' 		                    => __( 'Claim any time-based reward', 'gamipress-time-based-rewards' ),
        'gamipress_time_based_rewards_claim_specific' 		                => __( 'Claim a specific time-based reward', 'gamipress-time-based-rewards' ),
        // Earn reward on claim
        'gamipress_time_based_rewards_earn_reward_on_claim' 		        => __( 'Earn any reward of any type on claim a time-based reward', 'gamipress-time-based-rewards' ),
        'gamipress_time_based_rewards_earn_points_on_claim' 		        => __( 'Earn points on claim a time-based reward', 'gamipress-time-based-rewards' ),
        'gamipress_time_based_rewards_earn_specific_points_on_claim' 		=> __( 'Earn points of a specific type on claim a time-based reward', 'gamipress-time-based-rewards' ),
        'gamipress_time_based_rewards_earn_achievement_on_claim' 		    => __( 'Earn an achievement on claim a time-based reward', 'gamipress-time-based-rewards' ),
        'gamipress_time_based_rewards_earn_specific_achievement_on_claim' 	=> __( 'Earn a specific achievement on claim a time-based reward', 'gamipress-time-based-rewards' ),
        'gamipress_time_based_rewards_earn_rank_on_claim' 		            => __( 'Earn a rank on claim a time-based reward', 'gamipress-time-based-rewards' ),
        'gamipress_time_based_rewards_earn_specific_rank_on_claim' 		    => __( 'Earn a specific rank on claim a time-based reward', 'gamipress-time-based-rewards' ),
    );

    return $activity_triggers;

}
add_filter( 'gamipress_activity_triggers', 'gamipress_time_based_rewards_activity_triggers' );

/**
 * Register specific activity triggers
 *
 * @since  1.0.0
 *
 * @param  array $specific_activity_triggers
 * @return array
 */
function gamipress_time_based_rewards_specific_activity_triggers( $specific_activity_triggers ) {

    // Claim time-based reward
    $specific_activity_triggers['gamipress_time_based_rewards_claim_specific'] = array( 'time-based-reward' );
    // Earn reward on claim
    $specific_activity_triggers['gamipress_time_based_rewards_earn_points_on_claim'] = array( 'points-type' );
    $specific_activity_triggers['gamipress_time_based_rewards_earn_specific_achievement_on_claim'] = gamipress_get_achievement_types_slugs();
    $specific_activity_triggers['gamipress_time_based_rewards_earn_specific_rank_on_claim'] = gamipress_get_rank_types_slugs();

    return $specific_activity_triggers;
}
add_filter( 'gamipress_specific_activity_triggers', 'gamipress_time_based_rewards_specific_activity_triggers' );

/**
 * Register specific activity triggers labels
 *
 * @since  1.0.0
 *
 * @param  array $specific_activity_trigger_labels
 * @return array
 */
function gamipress_time_based_rewards_specific_activity_trigger_label( $specific_activity_trigger_labels ) {

    // Claim time-based reward
    $specific_activity_trigger_labels['gamipress_time_based_rewards_claim_specific'] = __( 'Claim the %s time-based reward', 'gamipress-time-based-rewards' );
    // Earn reward on claim
    $specific_activity_trigger_labels['gamipress_time_based_rewards_earn_points_on_claim'] = __( 'Earn %s on claim a time-based reward', 'gamipress-time-based-rewards' );
    $specific_activity_trigger_labels['gamipress_time_based_rewards_earn_specific_achievement_on_claim'] = __( 'Earn %s on claim a time-based reward', 'gamipress-time-based-rewards' );
    $specific_activity_trigger_labels['gamipress_time_based_rewards_earn_specific_rank_on_claim'] = __( 'Earn %s on claim a time-based reward', 'gamipress-time-based-rewards' );

    return $specific_activity_trigger_labels;
}
add_filter( 'gamipress_specific_activity_trigger_label', 'gamipress_time_based_rewards_specific_activity_trigger_label' );

/**
 * Get user for a given trigger action.
 *
 * @since  1.0.0
 *
 * @param  integer $user_id user ID to override.
 * @param  string  $trigger Trigger name.
 * @param  array   $args    Passed trigger args.
 *
 * @return integer          User ID.
 */
function gamipress_time_based_rewards_trigger_get_user_id( $user_id, $trigger, $args ) {

    switch ( $trigger ) {
        // Claim time-based reward
        case 'gamipress_time_based_rewards_claim_any':
        case 'gamipress_time_based_rewards_claim_specific':
        // Earn reward on claim
        case 'gamipress_time_based_rewards_earn_reward_on_claim':
        case 'gamipress_time_based_rewards_earn_points_on_claim':
        case 'gamipress_time_based_rewards_earn_specific_points_on_claim':
        case 'gamipress_time_based_rewards_earn_achievement_on_claim':
        case 'gamipress_time_based_rewards_earn_specific_achievement_on_claim':
        case 'gamipress_time_based_rewards_earn_rank_on_claim':
        case 'gamipress_time_based_rewards_earn_specific_rank_on_claim':
            $user_id = $args[1];
            break;
    }

    return $user_id;

}
add_filter( 'gamipress_trigger_get_user_id', 'gamipress_time_based_rewards_trigger_get_user_id', 10, 3 );

/**
 * Get the id for a given specific trigger action.
 *
 * @since  1.0.0
 *
 * @param integer $specific_id  Specific ID.
 * @param string  $trigger      Trigger name.
 * @param array   $args         Passed trigger args.
 *
 * @return integer          Specific ID.
 */
function gamipress_time_based_rewards_specific_trigger_get_id( $specific_id, $trigger = '', $args = array() ) {

    switch ( $trigger ) {
        // Claim time-based reward
        case 'gamipress_time_based_rewards_claim_specific':
        // Earn reward on claim
        case 'gamipress_time_based_rewards_earn_specific_points_on_claim':
        case 'gamipress_time_based_rewards_earn_specific_achievement_on_claim':
        case 'gamipress_time_based_rewards_earn_specific_rank_on_claim':
            $specific_id = $args[0];
            break;
    }

    return $specific_id;
}
add_filter( 'gamipress_specific_trigger_get_id', 'gamipress_time_based_rewards_specific_trigger_get_id', 10, 3 );

/**
 * Extended meta data for event trigger logging
 *
 * @since 1.0.0
 *
 * @param array 	$log_meta
 * @param integer 	$user_id
 * @param string 	$trigger
 * @param integer 	$site_id
 * @param array 	$args
 *
 * @return array
 */
function gamipress_time_based_rewards_log_event_trigger_meta_data( $log_meta, $user_id, $trigger, $site_id, $args ) {

    switch ( $trigger ) {

        // Claim time-based reward
        case 'gamipress_time_based_rewards_claim_any':
        case 'gamipress_time_based_rewards_claim_specific':
            // Add the time-based reward ID as post ID
            $log_meta['post_id'] = $args[0];
            $log_meta['time_based_reward_id'] = $args[0];
            $log_meta['rewards'] = $args[2];
            break;

        // Earn reward on claim
        case 'gamipress_time_based_rewards_earn_reward_on_claim':
            // Add the time-based reward ID as post ID
            $log_meta['post_id'] = $args[0];
            $log_meta['time_based_reward_id'] = $args[0];
            $log_meta['reward'] = $args[2];
            break;

        // Points
        case 'gamipress_time_based_rewards_earn_points_on_claim':
        case 'gamipress_time_based_rewards_earn_specific_points_on_claim':
            // Add the points type ID as post ID
            $log_meta['post_id'] = $args[0];
            $log_meta['time_based_reward_id'] = $args[2];
            $log_meta['reward'] = $args[3];
            break;

        // Achievement
        case 'gamipress_time_based_rewards_earn_achievement_on_claim':
        case 'gamipress_time_based_rewards_earn_specific_achievement_on_claim':
            // Add the achievement ID as post ID
            $log_meta['post_id'] = $args[0];
            $log_meta['time_based_reward_id'] = $args[2];
            $log_meta['reward'] = $args[3];
            break;

        // Rank
        case 'gamipress_time_based_rewards_earn_rank_on_claim':
        case 'gamipress_time_based_rewards_earn_specific_rank_on_claim':
            // Add the rank ID as post ID
            $log_meta['post_id'] = $args[0];
            $log_meta['time_based_reward_id'] = $args[2];
            $log_meta['reward'] = $args[3];
            break;
    }

    return $log_meta;
}
add_filter( 'gamipress_log_event_trigger_meta_data', 'gamipress_time_based_rewards_log_event_trigger_meta_data', 10, 5 );