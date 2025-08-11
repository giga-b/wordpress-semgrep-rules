<?php
/**
 * Template Functions
 *
 * @package     GamiPress\Coupons\Template_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin templates directory on GamiPress template engine
 *
 * @since 1.0.0
 *
 * @param array $file_paths
 *
 * @return array
 */
function gamipress_coupons_template_paths( $file_paths ) {

    $file_paths[] = trailingslashit( get_stylesheet_directory() ) . 'gamipress/coupons/';
    $file_paths[] = trailingslashit( get_template_directory() ) . 'gamipress/coupons/';
    $file_paths[] = GAMIPRESS_COUPONS_DIR . 'templates/';

    return $file_paths;

}
add_filter( 'gamipress_template_paths', 'gamipress_coupons_template_paths' );

/**
 * Function to report form error just if logged in user has permissions to manage GamiPress
 *
 * @param string $error_message
 * @return string
 */
function gamipress_coupons_notify_form_error( $error_message ) {

    if( current_user_can( gamipress_get_manager_capability() ) ) {
        // Notify to admins about the form error
        return $error_message;
    } else {
        // Do not output anything for non admins
        return '';
    }

}