<?php
/**
 * Requirements
 *
 * @package GamiPress\Coupons\Requirements
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add custom fields to the requirement object
 *
 * @param $requirement
 * @param $requirement_id
 *
 * @return array
 */
function gamipress_coupons_requirement_object( $requirement, $requirement_id ) {

    if( isset( $requirement['trigger_type'] ) ) {

        // The points type fields
        if ( $requirement['trigger_type'] === 'gamipress_coupons_redeem_points_coupon' ) {
            $requirement['coupons_points_type'] = gamipress_get_post_meta( $requirement_id, '_gamipress_coupons_points_type', true );
            $requirement['coupons_points_amount'] = gamipress_get_post_meta( $requirement_id, '_gamipress_coupons_points_amount', true );
        }

        // The achievement type fields
        if ( $requirement['trigger_type'] === 'gamipress_coupons_redeem_achievement_coupon' ) {
            $requirement['coupons_achievement_type'] = gamipress_get_post_meta( $requirement_id, '_gamipress_coupons_achievement_type', true );
        }

        // The rank type fields
        if ( $requirement['trigger_type'] === 'gamipress_coupons_redeem_rank_coupon' ) {
            $requirement['coupons_rank_type'] = gamipress_get_post_meta( $requirement_id, '_gamipress_coupons_rank_type', true );
        }

    }

    return $requirement;
}
add_filter( 'gamipress_requirement_object', 'gamipress_coupons_requirement_object', 10, 2 );

/**
 * Custom fields on requirements UI
 *
 * @param $requirement_id
 * @param $post_id
 */
function gamipress_coupons_requirement_ui_fields( $requirement_id, $post_id ) {

    // Get our types
    $points_types = gamipress_get_points_types();
    $achievement_types = gamipress_get_achievement_types();
    $rank_types = gamipress_get_rank_types();

    // Setup vars
    $requirement = gamipress_get_requirement_object( $requirement_id );
    $points_type_selected = isset( $requirement['coupons_points_type'] ) ? $requirement['coupons_points_type'] : '';
    $achievement_type_selected = isset( $requirement['coupons_achievement_type'] ) ? $requirement['coupons_achievement_type'] : '';
    $rank_type_selected = isset( $requirement['coupons_rank_type'] ) ? $requirement['coupons_rank_type'] : '';
    ?>

    <?php // Points type fields ?>

    <select id="select-coupons-points-type-<?php echo $requirement_id; ?>" class="select-coupons-points-type">
        <?php foreach( $points_types as $slug => $data ) : ?>
            <option value="<?php echo $slug; ?>" <?php selected( $points_type_selected, $slug ); ?>><?php echo $data['plural_name']; ?></option>
        <?php endforeach; ?>
    </select>

    <input type="number" id="input-<?php echo $requirement_id; ?>-coupons-points-amount" class="input-coupons-points-amount" value="<?php echo ( isset( $requirement['coupons_points_amount'] ) ? absint( $requirement['coupons_points_amount'] ) : 0 ); ?>" />
    <span class="coupons-points-amount-text"><?php _e( '(0 for no minimum)', 'gamipress-purchaes' ); ?></span>
    <?php // Achievement type fields ?>

    <select id="select-coupons-achievement-type-<?php echo $requirement_id; ?>" class="select-coupons-achievement-type">
        <?php foreach( $achievement_types as $slug => $data ) : ?>
            <option value="<?php echo $slug; ?>" <?php selected( $achievement_type_selected, $slug ); ?>><?php echo $data['singular_name']; ?></option>
        <?php endforeach; ?>
    </select>

    <?php // Rank type fields ?>

    <select id="select-coupons-rank-type-<?php echo $requirement_id; ?>" class="select-coupons-rank-type">
        <?php foreach( $rank_types as $slug => $data ) : ?>
            <option value="<?php echo $slug; ?>" <?php selected( $rank_type_selected, $slug ); ?>><?php echo $data['singular_name']; ?></option>
        <?php endforeach; ?>
    </select>

    <?php
}
add_action( 'gamipress_requirement_ui_html_after_achievement_post', 'gamipress_coupons_requirement_ui_fields', 10, 2 );

/**
 * Custom handler to save custom fields on requirements UI
 *
 * @param $requirement_id
 * @param $requirement
 */
function gamipress_coupons_ajax_update_requirement( $requirement_id, $requirement ) {

    if( isset( $requirement['trigger_type'] ) ) {

        // The points type fields
        if ( $requirement['trigger_type'] === 'gamipress_coupons_redeem_points_coupon' ) {
            gamipress_update_post_meta( $requirement_id, '_gamipress_coupons_points_type', $requirement['coupons_points_type'] );
            gamipress_update_post_meta( $requirement_id, '_gamipress_coupons_points_amount', $requirement['coupons_points_amount'] );
        }

        // The achievement type fields
        if ( $requirement['trigger_type'] === 'gamipress_coupons_redeem_achievement_coupon' ) {
            gamipress_update_post_meta( $requirement_id, '_gamipress_coupons_achievement_type', $requirement['coupons_achievement_type'] );
        }

        // The rank type fields
        if ( $requirement['trigger_type'] === 'gamipress_coupons_redeem_rank_coupon' ) {
            gamipress_update_post_meta( $requirement_id, '_gamipress_coupons_rank_type', $requirement['coupons_rank_type'] );
        }

    }
}
add_action( 'gamipress_ajax_update_requirement', 'gamipress_coupons_ajax_update_requirement', 10, 2 );