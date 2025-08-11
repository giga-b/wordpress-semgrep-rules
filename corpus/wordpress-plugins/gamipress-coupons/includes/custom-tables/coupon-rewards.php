<?php
/**
 * Coupon Rewards
 *
 * @package     GamiPress\Coupons\Custom_Tables\Coupon_Rewards
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Parse query args for coupon rewards
 *
 * @since  1.0.0
 *
 * @param string $where
 * @param CT_Query $ct_query
 *
 * @return string
 */
function gamipress_coupons_coupon_rewards_query_where( $where, $ct_query ) {

    global $ct_table;

    if( $ct_table->name !== 'gamipress_coupon_rewards' )
        return $where;

    $table_name = $ct_table->db->table_name;

    // Coupon ID
    if( isset( $ct_query->query_vars['coupon_id'] ) && absint( $ct_query->query_vars['coupon_id'] ) !== 0 ) {

        $coupon_id = $ct_query->query_vars['coupon_id'];

        if( is_array( $coupon_id ) ) {
            $coupon_id = implode( ", ", $coupon_id );

            $where .= " AND {$table_name}.coupon_id IN ( {$coupon_id} )";
        } else {
            $where .= " AND {$table_name}.coupon_id = {$coupon_id}";
        }
    }

    return $where;
}
add_filter( 'ct_query_where', 'gamipress_coupons_coupon_rewards_query_where', 10, 2 );