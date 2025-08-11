<?php
/**
 * Scripts
 *
 * @package     GamiPress\Daily_Login_Rewards\Scripts
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
function gamipress_time_based_rewards_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-time-based-rewards-css', GAMIPRESS_TIME_BASED_REWARDS_URL . 'assets/css/gamipress-time-based-rewards' . $suffix . '.css', array( ), GAMIPRESS_TIME_BASED_REWARDS_VER, 'all' );

    // Scripts
    wp_register_script( 'gamipress-time-based-rewards-js', GAMIPRESS_TIME_BASED_REWARDS_URL . 'assets/js/gamipress-time-based-rewards' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_TIME_BASED_REWARDS_VER, true );

}
add_action( 'init', 'gamipress_time_based_rewards_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_time_based_rewards_enqueue_scripts( $hook = null ) {

    // Localize scripts
    wp_localize_script( 'gamipress-time-based-rewards-js', 'gamipress_time_based_rewards', array(
        'ajaxurl' => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
        'nonce' => wp_create_nonce( 'gamipress_time_based_rewards' ),
    ) );

    // Enqueue assets
    wp_enqueue_style( 'gamipress-time-based-rewards-css' );
    wp_enqueue_script( 'gamipress-time-based-rewards-js' );

}
add_action( 'wp_enqueue_scripts', 'gamipress_time_based_rewards_enqueue_scripts', 100 );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_time_based_rewards_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-time-based-rewards-admin-css', GAMIPRESS_TIME_BASED_REWARDS_URL . 'assets/css/gamipress-time-based-rewards-admin' . $suffix . '.css', array( ), GAMIPRESS_TIME_BASED_REWARDS_VER, 'all' );

    // Scripts
    wp_register_script( 'gamipress-time-based-rewards-admin-js', GAMIPRESS_TIME_BASED_REWARDS_URL . 'assets/js/gamipress-time-based-rewards-admin' . $suffix . '.js', array( 'jquery', 'jquery-ui-sortable' ), GAMIPRESS_TIME_BASED_REWARDS_VER, true );

}
add_action( 'admin_init', 'gamipress_time_based_rewards_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_time_based_rewards_admin_enqueue_scripts( $hook ) {

    //Stylesheets
    wp_enqueue_style( 'gamipress-time-based-rewards-admin-css' );

    //Scripts
    wp_localize_script( 'gamipress-time-based-rewards-admin-js', 'gamipress_time_based_rewards_admin', array(
        'points_types' => gamipress_get_points_types(),
        'achievement_types' => gamipress_get_achievement_types(),
        'rank_types' => gamipress_get_rank_types(),
        'points_type_label' => '{amount} {label}',
        'achievement_type_label' => '{amount} {title}',
        'rank_type_label' => '{amount} {title}',
    ) );

    wp_enqueue_script( 'gamipress-time-based-rewards-admin-js' );

}
add_action( 'admin_enqueue_scripts', 'gamipress_time_based_rewards_admin_enqueue_scripts', 100 );