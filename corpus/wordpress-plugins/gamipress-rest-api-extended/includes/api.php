<?php
/**
 * API
 *
 * @package GamiPress\Rest_API_Extended\API
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin rest API routes
 *
 * @since 1.0.0
 */
function gamipress_rest_api_extended_rest_api_init() {

    $classes = array(
        'GamiPress_Rest_API_Extended_Points_Controller',
        'GamiPress_Rest_API_Extended_Achievements_Controller',
        'GamiPress_Rest_API_Extended_Ranks_Controller',
        'GamiPress_Rest_API_Extended_Requirements_Controller',
    );

    foreach( $classes as $class ) {
        // Instance the controller
        $controller = new $class();

        // register the controller routes
        $controller->register_routes();
    }

}
add_action( 'rest_api_init', 'gamipress_rest_api_extended_rest_api_init' );