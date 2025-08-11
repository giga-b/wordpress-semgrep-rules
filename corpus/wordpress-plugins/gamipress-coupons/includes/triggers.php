<?php
/**
 * Triggers
 *
 * @package     GamiPress\Coupons\Triggers
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
function gamipress_coupons_activity_triggers( $activity_triggers ) {

    $activity_triggers[__( 'Coupons', 'gamipress-coupons' )] = array(
        'gamipress_coupons_redeem_coupon' 		                => __( 'Redeem a coupon', 'gamipress-coupons' ),
        'gamipress_coupons_redeem_points_coupon'  		        => __( 'Redeem a coupon with a minimum amount of points', 'gamipress-coupons' ),
        'gamipress_coupons_redeem_achievement_coupon'	        => __( 'Redeem a coupon with an achievement', 'gamipress-coupons' ),
        'gamipress_coupons_redeem_specific_achievement_coupon'	=> __( 'Redeem a coupon with a specific achievement', 'gamipress-coupons' ),
        'gamipress_coupons_redeem_rank_coupon'	                => __( 'Redeem a coupon with a rank', 'gamipress-coupons' ),
        'gamipress_coupons_redeem_specific_rank_coupon'	        => __( 'Redeem a coupon with a specific rank', 'gamipress-coupons' ),
    );

    return $activity_triggers;

}
add_filter( 'gamipress_activity_triggers', 'gamipress_coupons_activity_triggers' );

/**
 * Register specific activity triggers
 *
 * @since  1.0.0
 *
 * @param  array $specific_activity_triggers
 * @return array
 */
function gamipress_coupons_specific_activity_triggers( $specific_activity_triggers ) {

    $specific_activity_triggers['gamipress_coupons_redeem_specific_achievement_coupon'] = gamipress_get_achievement_types_slugs();
    $specific_activity_triggers['gamipress_coupons_redeem_specific_rank_coupon'] = gamipress_get_rank_types_slugs();

    return $specific_activity_triggers;
}
add_filter( 'gamipress_specific_activity_triggers', 'gamipress_coupons_specific_activity_triggers' );

/**
 * Build custom activity trigger label
 *
 * @since 1.0.0
 *
 * @param string    $title
 * @param integer   $requirement_id
 * @param array     $requirement
 *
 * @return string
 */
function gamipress_coupons_activity_trigger_label( $title, $requirement_id, $requirement ) {

    // Get our types
    $points_types = gamipress_get_points_types();
    $achievement_types = gamipress_get_achievement_types();
    $rank_types = gamipress_get_rank_types();

    switch( $requirement['trigger_type'] ) {

        // Points type label
        case 'gamipress_coupons_redeem_points_coupon':

            // Bail if points type not well configured
            if( ! isset( $points_types[$requirement['coupons_points_type']] ) ) {
                return $title;
            }

            $points_type = $points_types[$requirement['coupons_points_type']];
            $points_amount = absint( $requirement['coupons_points_amount'] );

            if( $points_amount > 0 ) {
                return sprintf( __( 'Redeem a coupon with a minimum of %d %s', 'gamipress-coupons' ), $points_amount, $points_type['plural_name'] );
            } else {
                return sprintf( __( 'Redeem a coupon with any amount of %s', 'gamipress-coupons' ), $points_type['plural_name'] );
            }

            break;

        // Achievement type label
        case 'gamipress_coupons_redeem_achievement_coupon':

            // Bail if achievement type not well configured
            if( ! isset( $achievement_types[$requirement['coupons_achievement_type']] ) ) {
                return $title;
            }

            $achievement_type = $achievement_types[$requirement['coupons_achievement_type']];

            return sprintf( __( 'Redeem a coupon with any %s', 'gamipress-coupons' ), $achievement_type['singular_name'] );

            break;
        case 'gamipress_coupons_redeem_specific_achievement_coupon':

            $achievement = get_post( $requirement['achievement_post'] );

            // Bail if achievement not exists
            if( ! $achievement ) {
                return $title;
            }

            // Bail if achievement type not well configured
            if( ! isset( $achievement_types[$achievement->post_type] ) ) {
                return $title;
            }

            $achievement_type = $achievement_types[$achievement->post_type];

            return sprintf( __( 'Redeem a coupon with the %s %s', 'gamipress-coupons' ), $achievement_type['singular_name'], $achievement->post_title );

            break;

        // Rank type label
        case 'gamipress_coupons_redeem_rank_coupon':

            // Bail if rank type not well configured
            if( ! isset( $rank_types[$requirement['coupons_rank_type']] ) ) {
                return $title;
            }

            $rank_type = $rank_types[$requirement['coupons_rank_type']];

            return sprintf( __( 'Redeem a coupon with any %s', 'gamipress-coupons' ), $rank_type['singular_name'] );

            break;
        case 'gamipress_coupons_redeem_specific_rank_coupon':

            $rank = get_post( $requirement['achievement_post'] );

            // Bail if rank not exists
            if( ! $rank ) {
                return $title;
            }

            // Bail if rank type not well configured
            if( ! isset( $rank_types[$rank->post_type] ) ) {
                return $title;
            }

            $rank_type = $rank_types[$rank->post_type];

            return sprintf( __( 'Redeem a coupon with the %s %s', 'gamipress-coupons' ), $rank_type['singular_name'], $rank->post_title );

            break;

    }

    return $title;
}
add_filter( 'gamipress_activity_trigger_label', 'gamipress_coupons_activity_trigger_label', 10, 3 );

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
function gamipress_coupons_trigger_get_user_id( $user_id, $trigger, $args ) {

    switch ( $trigger ) {
        case 'gamipress_coupons_redeem_coupon':
        case 'gamipress_coupons_redeem_points_coupon':
        case 'gamipress_coupons_redeem_achievement_coupon':
        case 'gamipress_coupons_redeem_specific_achievement_coupon':
        case 'gamipress_coupons_redeem_rank_coupon':
        case 'gamipress_coupons_redeem_specific_rank_coupon':
            $user_id = $args[1];
            break;
    }

    return $user_id;

}
add_filter( 'gamipress_trigger_get_user_id', 'gamipress_coupons_trigger_get_user_id', 10, 3 );

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
function gamipress_coupons_specific_trigger_get_id( $specific_id, $trigger = '', $args = array() ) {

    switch ( $trigger ) {
        case 'gamipress_coupons_redeem_specific_achievement_coupon':
        case 'gamipress_coupons_redeem_specific_rank_coupon':
            $specific_id = $args[3];
            break;
    }

    return $specific_id;
}
add_filter( 'gamipress_specific_trigger_get_id', 'gamipress_coupons_specific_trigger_get_id', 10, 3 );

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
function gamipress_coupons_log_event_trigger_meta_data( $log_meta, $user_id, $trigger, $site_id, $args ) {

    switch ( $trigger ) {
        case 'gamipress_coupons_redeem_coupon':
            // Add the coupon ID
            $log_meta['coupon_id'] = $args[2];
            break;
        case 'gamipress_coupons_redeem_points_coupon':
            // Add the coupon ID, points type ID and points couponred amount
            $log_meta['coupon_id'] = $args[2];
            $log_meta['points_type_id'] = $args[3];
            $log_meta['points_amount'] = $args[4];
            break;
        case 'gamipress_coupons_redeem_achievement_coupon':
        case 'gamipress_coupons_redeem_specific_achievement_coupon':
            // Add the coupon ID and the achievement ID
            $log_meta['coupon_id'] = $args[2];
            $log_meta['achievement_id'] = $args[3];
            break;
        case 'gamipress_coupons_redeem_rank_coupon':
        case 'gamipress_coupons_redeem_specific_rank_coupon':
            // Add the coupon ID and the rank ID
            $log_meta['coupon_id'] = $args[2];
            $log_meta['rank_id'] = $args[3];
            break;
    }

    return $log_meta;
}
add_filter( 'gamipress_log_event_trigger_meta_data', 'gamipress_coupons_log_event_trigger_meta_data', 10, 5 );