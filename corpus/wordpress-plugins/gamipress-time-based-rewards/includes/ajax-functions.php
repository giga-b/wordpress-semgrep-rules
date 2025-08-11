<?php
/**
 * Ajax Functions
 *
 * @package GamiPress\Time_Based_Rewards\Ajax_Functions
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function to check and claim the give time-based reward
 *
 * @since 1.0.0
 *
 * @return void
 */
function gamipress_time_based_rewards_ajax_claim() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_time_based_rewards', 'nonce' );

    global $wpdb;

    $prefix = '_gamipress_time_based_rewards_';

    $time_based_reward_id = isset( $_POST['time_based_reward_id'] ) ? $_POST['time_based_reward_id'] : 0;

    $time_based_reward = gamipress_get_post( $time_based_reward_id );

    // Bail if time-based reward not exists
    if( ! $time_based_reward )
        wp_send_json_error( __( 'Time-based reward not found.', 'gamipress-time-based-rewards' ) );

    // Bail if not yet published
    if( $time_based_reward->post_status !== 'publish' )
        wp_send_json_error( __( 'Invalid time-based reward.', 'gamipress-time-based-rewards' ) );

    $user_id = get_current_user_id();

    // Guest not supported yet (basically because they has not points)
    if( $user_id === 0 )
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-time-based-rewards' ) );

    // Bail if user can't claim this time-based reward for some reason
    if( ! gamipress_time_based_rewards_user_can_claim( $time_based_reward_id, $user_id ) )
        wp_send_json_error( __( 'You are not allowed to claim this yet.', 'gamipress-time-based-rewards' ) );

    // -------------------------------------------
    // Setup the possible rewards to the user
    // -------------------------------------------

    $rewards = gamipress_get_post_meta( $time_based_reward_id, $prefix . 'rewards' );
    $user_rewards = array();

    foreach( $rewards as $key => $reward ) {

        // Let's luck decide if this reward should be included with a 50% chance
        $include = (bool) ( rand( 0, 100 ) > 50 );

        // Force reward to be included if setup to being always included
        if( isset( $reward['always'] ) && $reward['always'] === 'on' )
            $include = true;

        if( $include ) {
            $min = absint( $reward['min'] );
            $max = absint( $reward['max'] );

            // If min is 0 there is a chance to don't get it
            if( isset( $reward['always'] ) && $reward['always'] === 'on' && $min === 0 )
                $min++;

            if( $max < $min )
                $max = $min;

            // If min and max are equal then set quantity to the min, else, setup a random amount limited by min and max
            if( $max === $min )
                $quantity = $min;
            else
                $quantity = rand( $min, $max );

            // Force ranks quantity to 1
            if( in_array( $reward['post_type'], gamipress_get_rank_types_slugs() ) )
                $quantity = 1;

            // Checks the random achievement type to see if is correct
            if( $reward['post_type'] === 'random_achievement' ) {

                // Check that achievement type is not the "all" option
                if( ! empty( $reward['achievement_type'] ) && $reward['achievement_type'] !== 'all' ) {

                    // If not is a registered achievement type force quantity to 0 and remove this reward from the possible rewards
                    if( ! gamipress_get_achievement_type( $reward['achievement_type'] ) ) {
                        $quantity = 0;

                        unset( $rewards[$key] );
                    }
                }
            }

            // Setup the reward
            if( $quantity > 0 ) {

                $user_rewards[] = array_merge( $reward, array(
                    'quantity'      => $quantity,
                    'label_parsed'  => gamipress_time_based_rewards_parse_pattern_tags( $reward['label'], $quantity, $reward ),
                ) );

            }
        }

    }

    // If not rewards given, force 1 randomly
    if( empty( $user_rewards ) ) {
        $reward = $rewards[rand(0, count($rewards) - 1)];

        $min = absint( $reward['min'] );
        $max = absint( $reward['max'] );

        if( $max === $min )
            $quantity = $min;
        else
            $quantity = rand( $min, $max );

        if( $quantity === 0 )
            $quantity = 1;

        // Force ranks quantity to 1
        if( in_array( $reward['post_type'], gamipress_get_rank_types_slugs() ) )
            $quantity = 1;

        $user_rewards[] = array_merge( $reward, array(
            'quantity'      => $quantity,
            'label_parsed'  => gamipress_time_based_rewards_parse_pattern_tags( $reward['label'], $quantity, $reward ),
        ) );
    }

    // -------------------------------------------
    // Apply the rewards to the user
    // -------------------------------------------

    $time_based_reward_title = get_post_field( 'post_title', $time_based_reward_id );

    foreach( $user_rewards as $key => $user_reward ) {

        if( in_array( $user_reward['post_type'], gamipress_get_points_types_slugs() ) ) {
            // Points type

            // Award points to the user
            gamipress_award_points_to_user( $user_id, $user_reward['quantity'], $user_reward['post_type'], array(
                'reason' => sprintf( __( '{user} got {points} {points_type} for claim "%s"', 'gamipress-time-based-reward' ), $time_based_reward_title ),
            ) );

            $points_type_data = gamipress_get_points_type( $user_reward['post_type'] );
            $reason = sprintf( __( '%s for claim "%s"', 'gamipress-time-based-reward' ), $user_reward['label_parsed'], $time_based_reward_title );

            // Insert the custom user earning for this claim
            gamipress_insert_user_earning( $user_id, array(
                'title'	        => $reason,
                'user_id'	    => $user_id,
                'post_id'	    => $points_type_data['ID'],
                'post_type' 	=> 'points-type',
                'points'	    => $user_reward['quantity'],
                'points_type'	=> $user_reward['post_type'],
                'date'	        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
            ) );

        } else if( $user_reward['post_type'] === 'random_achievement' ) {
            // Random achievement

            $found_achievements = array();

            do {

                // Get the random achievement ID
                $achievement_id = gamipress_time_based_rewards_get_random_achievement( $user_reward['achievement_type'], $found_achievements );

                // If get random achievement returns 0, it means there is not achievements available
                if( $achievement_id !== 0 ) {

                    // Prevent to award this if user exceeds the max earnings
                    if( gamipress_achievement_user_exceeded_max_earnings( $user_id, $achievement_id ) ) {
                        $found_achievements[] = $achievement_id;
                        $achievement_id = false;
                    }

                }

            } while( $achievement_id === false );

            if( $achievement_id !== 0 ) {

                // Award the random achievement to the user
                for( $i=0; $i < $user_reward['quantity']; $i++ ) {
                    gamipress_award_achievement_to_user( $achievement_id, $user_id );
                }

                // Set the achievement ID to parse the reward label with the new assigned achievement
                $user_reward['achievement_id'] = $achievement_id;
                $user_reward['label_parsed'] = gamipress_time_based_rewards_parse_pattern_tags( $user_reward['label'], $user_reward['quantity'], $user_reward );

                $user_rewards[$key] = $user_reward;
            } else {
                unset( $user_rewards[$key] );
            }

        } else if( in_array( $user_reward['post_type'], gamipress_get_achievement_types_slugs() ) ) {
            // Achievement type

            if( absint( $user_reward['achievement_id'] ) !== 0 ) {
                // Award achievement to the user
                for( $i=0; $i < $user_reward['quantity']; $i++ ) {

                    // Only award if user doesn't exceeds the max earnings
                    if( ! gamipress_achievement_user_exceeded_max_earnings( $user_id, $user_reward['achievement_id'] ) ) {
                        gamipress_award_achievement_to_user($user_reward['achievement_id'], $user_id);
                    }

                }
            }

        } else if( in_array( $user_reward['post_type'], gamipress_get_rank_types_slugs() ) ) {
            // Rank type

            if( absint( $user_reward['rank_id'] ) !== 0 ) {
                // Get the current user rank
                $user_rank_id = gamipress_get_user_rank_id( $user_id, $user_reward['post_type'] );

                // Just award the rank if user is in a lowest priority rank
                if( gamipress_get_rank_priority( $user_reward['rank_id'] ) > gamipress_get_rank_priority( $user_rank_id ) ) {
                    // Award rank to the user
                    gamipress_update_user_rank( $user_id, $user_reward['rank_id'] );
                }
            }

        }
    }

    // -------------------------------------------
    // Log claim
    // -------------------------------------------

    gamipress_time_based_reward_log_claim( $time_based_reward_id, $user_id, $user_rewards );

    // -------------------------------------------
    // Setup response
    // -------------------------------------------

    global $gamipress_time_based_rewards_template_args;

    $gamipress_time_based_rewards_template_args = array();

    $gamipress_time_based_rewards_template_args['time_based_reward_id'] = $time_based_reward_id;
    $gamipress_time_based_rewards_template_args['rewards'] = $user_rewards;

    // Render the rewards pop-up
    ob_start();
    gamipress_get_template_part( 'rewards-popup' );
    $rewards_popup_output = ob_get_clean();

    /**
     * Filters the rewards popup output
     *
     * @since 1.0.0
     *
     * @param string    $rewards_popup_output
     * @param array     $rewards
     * @param int       $time_based_reward_id   The time-based reward ID
     */
    $rewards_popup_output = apply_filters( 'gamipress_time_based_rewards_rewards_popup_output', $rewards_popup_output, $user_rewards, $time_based_reward_id );

    $rewards_popup_output = '<div class="gamipress-time-based-rewards-popup-wrapper" style="display: none;">' . $rewards_popup_output . '</div>';

    // Setup recurrence to reset the next claim counter
    $recurrence = gamipress_get_post_meta( $time_based_reward_id, $prefix . 'recurrence' );

    $h = ( isset( $recurrence['hours'] ) ? absint( $recurrence['hours'] ) : 0 );
    $m = ( isset( $recurrence['minutes'] ) ? absint( $recurrence['minutes'] ) : 0 );
    $s = ( isset( $recurrence['seconds'] ) ? absint( $recurrence['seconds'] ) : 0 );

    /**
     * Time-based reward claimed
     *
     * @since 1.0.0
     *
     * @param int   $time_based_reward_id   The time-based reward ID
     * @param int   $user_id                The user ID
     * @param array $rewards                Rewards user got
     */
    do_action( 'gamipress_time_based_reward_claimed', $time_based_reward_id, $user_id, $user_rewards );

    wp_send_json_success( array(
        'rewards_popup' => $rewards_popup_output,
        'rewards' => $user_rewards,
        'next_claim' => "{$h}H {$m}M {$s}S",
    ) );

}
add_action( 'wp_ajax_gamipress_time_based_rewards_claim', 'gamipress_time_based_rewards_ajax_claim' );
