<?php
/**
 * Widgets
 *
 * @package     GamiPress\Coupons\Widgets
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// GamiPress Coupons Shortcodes
require_once GAMIPRESS_COUPONS_DIR . 'includes/widgets/redeem-coupon-widget.php';

// Register plugin widgets
function gamipress_coupons_register_widgets() {

    register_widget( 'gamipress_redeem_coupon_widget' );

}
add_action( 'widgets_init', 'gamipress_coupons_register_widgets' );