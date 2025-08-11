<?php
/**
 * Listeners
 *
 * @package GamiPress\Time_Based_Rewards\Listeners
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Claim listener
 *
 * @since 1.0.0
 *
 * @param int   $time_based_reward_id   The time-based reward ID
 * @param int   $user_id                The user ID
 * @param array $rewards                Rewards user got
 */
function gamipress_time_based_rewards_claim_listener( $time_based_reward_id, $user_id, $rewards ) {

    // Trigger claim any time-based reward
    do_action( 'gamipress_time_based_rewards_claim_any', $time_based_reward_id, $user_id, $rewards );

    // Trigger claim specific time-based reward
    do_action( 'gamipress_time_based_rewards_claim_specific', $time_based_reward_id, $user_id, $rewards );

    foreach( $rewards as $reward ) {

        // Trigger claim any reward
        do_action( 'gamipress_time_based_rewards_earn_reward_on_claim', $time_based_reward_id, $user_id, $reward );

        if( in_array( $reward['post_type'], gamipress_get_points_types_slugs() ) ) {
            // Points type

            $points_type = gamipress_get_points_type( $reward['post_type'] );

            // Trigger claim any points reward
            do_action( 'gamipress_time_based_rewards_earn_points_on_claim', $points_type['ID'], $user_id, $time_based_reward_id, $reward );

            // Trigger claim specific points reward
            do_action( 'gamipress_time_based_rewards_earn_specific_points_on_claim', $points_type['ID'], $user_id, $time_based_reward_id, $reward );

        } else if( in_array( $reward['post_type'], gamipress_get_achievement_types_slugs() ) || $reward['post_type'] === 'random_achievement' ) {
            // Achievement type and random achievement

            if( absint( $reward['achievement_id'] ) !== 0 ) {

                // Trigger claim any achievement reward
                do_action( 'gamipress_time_based_rewards_earn_achievement_on_claim', $reward['achievement_id'], $user_id, $time_based_reward_id, $reward );

                // Trigger claim specific achievement reward
                do_action( 'gamipress_time_based_rewards_earn_specific_achievement_on_claim', $reward['achievement_id'], $user_id, $time_based_reward_id, $reward );

            }

        } else if( in_array( $reward['post_type'], gamipress_get_rank_types_slugs() ) ) {
            // Rank type

            if( absint( $reward['rank_id'] ) !== 0 ) {

                // Trigger claim any rank reward
                do_action( 'gamipress_time_based_rewards_earn_rank_on_claim', $reward['rank_id'], $user_id, $time_based_reward_id, $reward );

                // Trigger claim specific rank reward
                do_action( 'gamipress_time_based_rewards_earn_specific_rank_on_claim', $reward['rank_id'], $user_id, $time_based_reward_id, $reward );

            }

        }
    }

}
add_action( 'gamipress_time_based_reward_claimed', 'gamipress_time_based_rewards_claim_listener', 10, 3 );