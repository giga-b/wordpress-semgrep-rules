<?php
/**
 * Scripts
 *
 * @package     GamiPress\Expirations\Scripts
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
function gamipress_expirations_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-expirations-css', GAMIPRESS_EXPIRATIONS_URL . 'assets/css/gamipress-expirations' . $suffix . '.css', array( ), GAMIPRESS_EXPIRATIONS_VER, 'all' );

}
add_action( 'init', 'gamipress_expirations_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_expirations_enqueue_scripts( $hook = null ) {

    // Stylesheets
    wp_enqueue_style( 'gamipress-expirations-css' );

}
add_action( 'wp_enqueue_scripts', 'gamipress_expirations_enqueue_scripts', 100 );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_expirations_admin_register_scripts() {
    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-expirations-admin-css', GAMIPRESS_EXPIRATIONS_URL . 'assets/css/gamipress-expirations-admin' . $suffix . '.css', array( ), GAMIPRESS_EXPIRATIONS_VER, 'all' );


    // Scripts
    wp_register_script( 'gamipress-expirations-admin-js', GAMIPRESS_EXPIRATIONS_URL . 'assets/js/gamipress-expirations-admin' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_EXPIRATIONS_VER, true );

}
add_action( 'admin_init', 'gamipress_expirations_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_expirations_admin_enqueue_scripts( $hook ) {

    global $post_type;

    $allowed_post_types = array_merge( gamipress_get_achievement_types_slugs(), gamipress_get_rank_types_slugs() );

    // Stylesheets
    wp_enqueue_style( 'gamipress-expirations-admin-css' );

    // Scripts
    if ( $post_type === 'points-type'
        || in_array( $post_type, $allowed_post_types )
        || $hook === 'gamipress_page_gamipress_settings'
    ) {
        wp_localize_script( 'gamipress-expirations-admin-js', 'gamipress_expirations_admin', array(
            'labels'    => array(
                ''          => __( 'Never expires', 'gamipress-expirations' ),
                'hours'     => __( 'Hour(s)', 'gamipress-expirations' ),
                'days'      => __( 'Day(s)', 'gamipress-expirations' ),
                'weeks'     => __( 'Week(s)', 'gamipress-expirations' ),
                'months'    => __( 'Month(s)', 'gamipress-expirations' ),
                'years'     => __( 'Year(s)', 'gamipress-expirations' ),
                'date'      => __( 'Specific date', 'gamipress-expirations' ),
            )
        ) );

        wp_enqueue_script( 'gamipress-expirations-admin-js' );
    }

}
add_action( 'admin_enqueue_scripts', 'gamipress_expirations_admin_enqueue_scripts', 100 );