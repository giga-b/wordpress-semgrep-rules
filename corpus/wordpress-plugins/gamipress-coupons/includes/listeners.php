<?php
/**
 * Listeners
 *
 * @package     GamiPress\Coupons\Listeners
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * New coupon redemption listener
 *
 * @param int       $user_id        User that redeemed the coupon
 * @param stdClass  $coupon         Coupon stdClass object
 * @param array     $coupon_rewards Coupon rewards array
 */
function gamipress_coupons_redeem_coupon_listener( $user_id, $coupon, $coupon_rewards ) {

    // Trigger redeem coupon action
    do_action( 'gamipress_coupons_redeem_coupon', $coupon, $user_id, $coupon->coupon_id, $coupon_rewards );

    // Get our types
    $points_types_slugs = gamipress_get_points_types_slugs();
    $achievement_types_slugs = gamipress_get_achievement_types_slugs();
    $rank_types_slugs = gamipress_get_rank_types_slugs();

    foreach( $coupon_rewards as $coupon_reward ) {

        // Skip if not item assigned
        if( absint( $coupon_reward->post_id ) === 0 ) {
            continue;
        }

        $post_type = gamipress_get_post_type( $coupon_reward->post_id );

        // Skip if can not get the type of this item
        if( ! $post_type ) {
            continue;
        }

        if( in_array( $post_type, $points_types_slugs ) ) {
            // Is a points

            // Amount of points awarded
            $quantity = absint( $coupon_reward->quantity );

            // Trigger redeem points coupon action
            do_action( 'gamipress_coupons_redeem_points_coupon', $coupon, $user_id, $coupon->coupon_id, absint( $coupon_reward->post_id ), $quantity, $coupon_reward, $coupon_reward->coupon_reward_id );

        } else if( in_array( $post_type, $achievement_types_slugs ) ) {
            // Is an achievement

            // Trigger redeem achievement coupon action
            do_action( 'gamipress_coupons_redeem_achievement_coupon', $coupon, $user_id, $coupon->coupon_id, absint( $coupon_reward->post_id ), $coupon_reward, $coupon_reward->coupon_reward_id );

            // Trigger redeem specific achievement coupon action
            do_action( 'gamipress_coupons_redeem_specific_achievement_coupon', $coupon, $user_id, $coupon->coupon_id, absint( $coupon_reward->post_id ), $coupon_reward, $coupon_reward->coupon_reward_id );

        } else if( in_array( $post_type, $rank_types_slugs ) ) {
            // Is a rank

            // Trigger redeem rank coupon action
            do_action( 'gamipress_coupons_redeem_rank_coupon', $coupon, $user_id, $coupon->coupon_id, absint( $coupon_reward->post_id ), $coupon_reward, $coupon_reward->coupon_reward_id );

            // Trigger redeem specific rank coupon action
            do_action( 'gamipress_coupons_redeem_specific_rank_coupon', $coupon, $user_id, $coupon->coupon_id, absint( $coupon_reward->post_id ), $coupon_reward, $coupon_reward->coupon_reward_id );

        }

    }
}
add_action( 'gamipress_coupons_user_redeem_coupon', 'gamipress_coupons_redeem_coupon_listener', 10, 3 );