<?php
/**
 * Logs
 *
 * @package     GamiPress\Time_Based_Rewards\Logs
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin log types
 *
 * @since 1.0.0
 *
 * @param array $gamipress_log_types
 *
 * @return array
 */
function gamipress_time_based_reward_logs_types( $gamipress_log_types ) {

    $gamipress_log_types['time_based_reward'] = __( 'Time-based Reward Claim', 'gamipress-time-based-reward' );

    return $gamipress_log_types;

}
add_filter( 'gamipress_logs_types', 'gamipress_time_based_reward_logs_types' );

/**
 * Log time-based reward claim on logs
 *
 * @since 1.0.0
 *
 * @param int   $time_based_reward_id
 * @param int   $user_id
 * @param array $rewards
 *
 * @return int|false
 */
function gamipress_time_based_reward_log_claim( $time_based_reward_id, $user_id, $rewards ) {

    // Can't claim a not existent post
    if( ! gamipress_get_post( $time_based_reward_id ) )
        return false;

    // Log meta data
    $log_meta = array(
        'pattern' => sprintf( __( '{user} claimed "%s"', 'gamipress-time-based-reward' ), get_post_field( 'post_title', $time_based_reward_id ) ),
        'post_id' => $time_based_reward_id,
        'rewards' => $rewards,
    );

    // Register the content unlock on logs
    return gamipress_insert_log( 'time_based_reward', $user_id, 'private', $log_meta );

}

/**
 * Extra data fields for the claim log entry
 *
 * @since 1.0.0
 *
 * @param array     $fields
 * @param int       $log_id
 * @param string    $type
 *
 * @return array
 */
function gamipress_time_based_reward_claim_log_extra_data_fields( $fields, $log_id, $type ) {

    $prefix = '_gamipress_';

    if( $type !== 'time_based_reward' )
        return $fields;

    $fields[] = array(
        'name' 	            => __( 'Rewards', 'gamipress-time-based-rewards' ),
        'desc' 	            => __( 'Rewards user got on claim this time-based reward.', 'gamipress-time-based-rewards' ),
        'id'   	            => $prefix . 'rewards',
        'type' 	            => 'group',
        'fields'           => array(
            array(
                'name' 	            => __( 'Label', 'gamipress-time-based-rewards' ),
                'id'   	            => 'label_parsed',
                'type'   	        => 'text',
            )
        )
    );

    return $fields;

}
add_filter( 'gamipress_log_extra_data_fields', 'gamipress_time_based_reward_claim_log_extra_data_fields', 10, 3 );

/**
 * Extra data fields for the plugin triggers
 *
 * @since 1.0.0
 *
 * @param array     $fields
 * @param int       $log_id
 * @param string    $type
 *
 * @return array
 */
function gamipress_time_based_rewards_log_extra_data_fields( $fields, $log_id, $type ) {

    $prefix = '_gamipress_';

    $log = ct_get_object( $log_id );
    $trigger = $log->trigger_type;

    if( $type !== 'event_trigger' )
        return $fields;

    switch( $trigger ) {
        // Claim time-based reward
        case 'gamipress_time_based_rewards_claim_any':
        case 'gamipress_time_based_rewards_claim_specific':

            $fields[] = array(
                'name' 	            => __( 'Rewards', 'gamipress-time-based-rewards' ),
                'desc' 	            => __( 'Rewards user got on claim this time-based reward.', 'gamipress-time-based-rewards' ),
                'id'   	            => $prefix . 'rewards',
                'type' 	            => 'group',
                'fields'           => array(
                    array(
                        'name' 	            => __( 'Label', 'gamipress-time-based-rewards' ),
                        'id'   	            => 'label_parsed',
                        'type'   	        => 'text',
                    )
                )
            );
            break;
    }

    return $fields;

}
add_filter( 'gamipress_log_extra_data_fields', 'gamipress_time_based_rewards_log_extra_data_fields', 10, 3 );