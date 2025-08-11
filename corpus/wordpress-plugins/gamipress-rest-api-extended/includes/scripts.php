<?php
/**
 * Scripts
 *
 * @package     GamiPress\Rest_API_Extended\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_rest_api_extended_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Scripts
    wp_register_script( 'gamipress-rest-api-extended-admin-js', GAMIPRESS_REST_API_EXTENDED_URL . 'assets/js/gamipress-rest-api-extended-admin' . $suffix . '.js', array( 'jquery', 'jquery-ui-sortable' ), GAMIPRESS_REST_API_EXTENDED_VER, true );

}
add_action( 'admin_init', 'gamipress_rest_api_extended_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_rest_api_extended_admin_enqueue_scripts( $hook ) {

    //Scripts
    wp_enqueue_script( 'gamipress-rest-api-extended-admin-js' );

}
add_action( 'admin_enqueue_scripts', 'gamipress_rest_api_extended_admin_enqueue_scripts', 100 );