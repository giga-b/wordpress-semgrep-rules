<?php
/**
 * Admin
 *
 * @package     GamiPress\Restrict_Content\Admin
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/admin/custom-columns.php';
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/admin/meta-boxes.php';

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
function gamipress_restrict_content_get_option( $option_name, $default = false ) {

    $prefix = 'gamipress_restrict_content_';

    return gamipress_get_option( $prefix . $option_name, $default );
}

/**
 * GamiPress Restrict Content Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_restrict_content_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-restrict-content-license'] = array(
        'title' => __( 'GamiPress Restrict Content', 'gamipress-restrict-content' ),
        'fields' => array(
            'gamipress_restrict_content_license' => array(
                'name' => __( 'License', 'gamipress-restrict-content' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_RESTRICT_CONTENT_FILE,
                'item_name' => 'Restrict Content',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_restrict_content_licenses_meta_boxes' );

/**
 * GamiPress Restrict Content automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_restrict_content_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-restrict-content'] = __( 'Restrict Content', 'gamipress-restrict-content' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_restrict_content_automatic_updates' );