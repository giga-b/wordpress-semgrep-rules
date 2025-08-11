<?php
/**
 * Admin
 *
 * @package GamiPress\Rest_API_Extended\Admin
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed
 */
function gamipress_rest_api_extended_get_option( $option_name, $default = false ) {

    $prefix = 'gamipress_rest_api_extended_';

    return gamipress_get_option( $prefix . $option_name, $default );
}

/**
 * GamiPress Leaderboards Settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function gamipress_rest_api_extended_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'gamipress_rest_api_extended_';

    $meta_boxes['gamipress-rest-api-extended-settings'] = array(
        'title' => gamipress_dashicon( 'cloud' ) . __( 'Rest API Extended', 'gamipress-rest-api-extended' ),
        'fields' => apply_filters( 'gamipress_rest_api_extended_settings_fields', array(
            $prefix . 'rest_base' => array(
                'name' => __( 'Base URL', 'gamipress-rest-api-extended' ),
                'desc' => __( 'Setup the base URL that all endpoints of this add-on will be accesible. By default, gamipress.', 'gamipress-rest-api-extended' )
                . '<br><span class="gamipress-rest-api-extended-full-rest-base hide-if-no-js">' . site_url() . '/wp/v2/<strong class="gamipress-rest-api-extended-rest-base"></strong>/</span>',
                'type' => 'text',
                'default_cb' => 'gamipress_rest_api_extended_rest_base_field_default_cb',
            ),
            $prefix . 'allow_get' => array(
                'name' => __( 'Allow GET parameters', 'gamipress-rest-api-extended' ),
                'desc' => __( 'By default, all routes accepts POST parameters only. Check this option to allow GET parameters too (useful for testing purposes).', 'gamipress-rest-api-extended' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
        ) )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_addons_meta_boxes', 'gamipress_rest_api_extended_settings_meta_boxes' );

// Since gamipress is a php function, CMB2 gets it as object
// For that the unique solution is pass it as callback
function gamipress_rest_api_extended_rest_base_field_default_cb() {
    return 'gamipress';
}

/**
 * Plugin Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_rest_api_extended_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-rest-api-extended-license'] = array(
        'title' => __( 'Rest API Extended', 'gamipress-rest-api-extended' ),
        'fields' => array(
            'gamipress_rest_api_extended_license' => array(
                'name' => __( 'License', 'gamipress-rest-api-extended' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_REST_API_EXTENDED_FILE,
                'item_name' => 'Rest API Extended',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_rest_api_extended_licenses_meta_boxes' );

/**
 * Plugin automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_rest_api_extended_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-rest-api-extended'] = __( 'Rest API Extended', 'gamipress-rest-api-extended' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_rest_api_extended_automatic_updates' );