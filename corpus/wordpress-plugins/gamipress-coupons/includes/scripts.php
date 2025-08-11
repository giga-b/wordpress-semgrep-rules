<?php
/**
 * Scripts
 *
 * @package     GamiPress\Coupons\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_coupons_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'gamipress-coupons-js', GAMIPRESS_COUPONS_URL . 'assets/js/gamipress-coupons' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_COUPONS_VER, true );

}
add_action( 'init', 'gamipress_coupons_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_coupons_enqueue_scripts( $hook = null ) {

    // Enqueue scripts
    if( ! wp_script_is('gamipress-coupons-js') ) {

        // Localize scripts
        wp_localize_script( 'gamipress-coupons-js', 'gamipress_coupons', array(
            'ajaxurl'                   => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
            'empty_code_error'          => __( 'Please, fill the coupon code field.', 'gamipress-coupons' ),
        ) );

        wp_enqueue_script( 'gamipress-coupons-js' );
    }

}
//add_action( 'wp_enqueue_scripts', 'gamipress_coupons_enqueue_scripts', 100 );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_coupons_admin_register_scripts( $hook ) {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-coupons-admin-coupons-css', GAMIPRESS_COUPONS_URL . 'assets/css/gamipress-coupons-admin-coupons' . $suffix . '.css', array( ), GAMIPRESS_COUPONS_VER, 'all' );

    // Scripts
    wp_register_script( 'gamipress-coupons-admin-coupons-js', GAMIPRESS_COUPONS_URL . 'assets/js/gamipress-coupons-admin-coupons' . $suffix . '.js', array( 'jquery', 'gamipress-admin-functions-js', 'gamipress-select2-js' ), GAMIPRESS_COUPONS_VER, true );
    wp_register_script( 'gamipress-coupons-requirements-ui-js', GAMIPRESS_COUPONS_URL . 'assets/js/gamipress-coupons-requirements-ui' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_COUPONS_VER, true );

}
add_action( 'admin_init', 'gamipress_coupons_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_coupons_admin_enqueue_scripts( $hook ) {

    global $post_type;

    //Scripts

    // Coupon add/edit screen
    if( $hook === 'gamipress_page_gamipress_coupons' || $hook === 'admin_page_edit_gamipress_coupons' ) {

        $points_types = gamipress_get_points_types();
        $achievement_types = gamipress_get_achievement_types();
        $rank_types = gamipress_get_rank_types();

        // Localize scripts
        wp_localize_script( 'gamipress-coupons-admin-coupons-js', 'gamipress_coupons_coupons', array(
            'nonce' => gamipress_get_admin_nonce(),
            'points_types' => $points_types,
            'achievement_types' => $achievement_types,
            'rank_types' => $rank_types,
            'admin_url' => admin_url(),
            'strings' => array(
                'achievement' => __( 'Achievement', 'gamipress-coupons' ),
                'rank' => __( 'Rank', 'gamipress-coupons' ),
            ),
        ) );

        //Stylesheets
        wp_enqueue_style( 'gamipress-coupons-admin-coupons-css' );
        wp_enqueue_style( 'gamipress-select2-css' );

        //Scripts
        wp_enqueue_script( 'gamipress-coupons-admin-coupons-js' );
    }

    // Requirements ui script
    if ( $post_type === 'points-type'
        || in_array( $post_type, gamipress_get_achievement_types_slugs() )
        || in_array( $post_type, gamipress_get_rank_types_slugs() ) ) {
        wp_enqueue_script( 'gamipress-coupons-requirements-ui-js' );
    }

}
add_action( 'admin_enqueue_scripts', 'gamipress_coupons_admin_enqueue_scripts', 100 );