<?php
/**
 * GamiPress Redeem Coupon Shortcode
 *
 * @package     GamiPress\Coupons\Shortcodes\Shortcode\GamiPress_Redeem_Coupon
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_redeem_coupon] shortcode.
 *
 * @since 1.0.0
 */
function gamipress_coupons_register_redeem_coupon_shortcode() {

    gamipress_register_shortcode( 'gamipress_redeem_coupon', array(
        'name'              => __( 'Redeem Coupon', 'gamipress-coupons' ),
        'description'       => __( 'Render a coupon redemption form.', 'gamipress-coupons' ),
        'output_callback'   => 'gamipress_coupons_redeem_coupon_shortcode',
        'icon'              => 'tickets-alt',
        'fields'            => array(
            'label' => array(
                'name'        => __( 'Label Text', 'gamipress-coupons' ),
                'description' => __( 'Code input label text.', 'gamipress-coupons' ),
                'type' 	=> 'text',
                'default' => __( 'Coupon', 'gamipress-coupons' )
            ),
            'placeholder' => array(
                'name'        => __( 'Placeholder Text', 'gamipress-coupons' ),
                'description' => __( 'Code input placeholder text.', 'gamipress-coupons' ),
                'type' 	=> 'text',
                'default' => __( 'Enter coupon code here', 'gamipress-coupons' )
            ),
            'button_text' => array(
                'name'        => __( 'Button Text', 'gamipress-coupons' ),
                'description' => __( 'Form button text.', 'gamipress-coupons' ),
                'type' 	=> 'text',
                'default' => __( 'Apply', 'gamipress-coupons' )
            ),
        ),
    ) );

}
add_action( 'init', 'gamipress_coupons_register_redeem_coupon_shortcode' );

/**
 * Redeem Coupon Shortcode.
 *
 * @since  1.0.0
 *
 * @param  array $atts Shortcode attributes.
 * @return string 	   HTML markup.
 */
function gamipress_coupons_redeem_coupon_shortcode( $atts = array() ) {

    global $gamipress_coupons_template_args;

    // Get the shortcode attributes
    $atts = shortcode_atts( array(

        'label'         => __( 'Coupon', 'gamipress-coupons' ),
        'placeholder'   => __( 'Enter coupon code here', 'gamipress-coupons' ),
        'button_text'   => __( 'Apply', 'gamipress-coupons' ),

    ), $atts, 'gamipress_redeem_coupon' );

    // Setup user id
    $user_id = get_current_user_id();

    if( $user_id === 0 ) {
        return sprintf( __( 'You need to <a href="%s">log in</a> to redeem a coupon.', 'gamipress-coupons' ), wp_login_url( get_permalink() ) );
    }

    $gamipress_coupons_template_args = $atts;

    // Enqueue assets
    gamipress_coupons_enqueue_scripts();

    ob_start();
    gamipress_get_template_part( 'redeem-coupon-form' );
    $output = ob_get_clean();

    // Return our rendered achievement coupon form
    return $output;
}
