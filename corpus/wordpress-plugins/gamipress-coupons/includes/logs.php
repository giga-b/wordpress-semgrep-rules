<?php
/**
 * Logs
 *
 * @package     GamiPress\Coupons\Logs
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
function gamipress_coupons_logs_types( $gamipress_log_types ) {

    $gamipress_log_types['coupon_redemption'] = __( 'Coupon Redemption', 'gamipress-coupons' );

    return $gamipress_log_types;

}
add_filter( 'gamipress_logs_types', 'gamipress_coupons_logs_types' );

/**
 * Log coupon redemption on logs
 *
 * @since 1.0.0
 *
 * @param int|stdClass  $coupon_id
 * @param int           $user_id
 *
 * @return int|false
 */
function gamipress_coupons_log_coupon_redemption( $coupon_id = null, $user_id = null ) {

    ct_setup_table( 'gamipress_coupons' );

    $coupon = ct_get_object( $coupon_id );

    ct_reset_setup_table();

    // Can't register a not existent coupon
    if( ! $coupon ) {
        return false;
    }

    // Set the current user ID if not passed
    if( $user_id === null ) {
        $user_id = get_current_user_id();
    }

    // Log meta data
    $log_meta = array(
        'pattern' => sprintf( __( '{user} redeemed the coupon code "%s"', 'gamipress-coupons' ), $coupon->code ),
        'coupon_id' => $coupon->coupon_id,
        'coupon_code' => $coupon->code,
    );

    // Register the content unlock on logs
    return gamipress_insert_log( 'coupon_redemption', $user_id, 'private', '', $log_meta );

}

/**
 * Return the number of uses an user made of a specific coupon
 *
 * @since 1.0.0
 *
 * @param int|stdClass  $coupon_id
 * @param int           $user_id
 *
 * @return int
 */
function gamipress_coupons_get_coupon_user_uses( $coupon_id = null, $user_id = null ) {

    ct_setup_table( 'gamipress_coupons' );

    $coupon = ct_get_object( $coupon_id );

    ct_reset_setup_table();

    // Can't register a not existent coupon
    if( ! $coupon ) {
        return 0;
    }

    // Set the current user ID if not passed
    if( $user_id === null ) {
        $user_id = get_current_user_id();
    }

    return gamipress_get_user_log_count( $user_id, array(
        'type' => 'coupon_redemption',
        'coupon_id' => $coupon_id,
    ) );

}