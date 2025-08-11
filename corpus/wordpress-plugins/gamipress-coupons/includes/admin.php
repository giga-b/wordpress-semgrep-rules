<?php
/**
 * Admin
 *
 * @package     GamiPress\Coupons\Admin
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add GamiPress Coupons admin bar menu
 *
 * @since 1.0.0
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function gamipress_coupons_admin_bar_menu( $wp_admin_bar ) {

    // - Coupons
    $wp_admin_bar->add_node( array(
        'id'     => 'gamipress-coupons',
        'title'  => __( 'Coupons', 'gamipress-coupons' ),
        'parent' => 'gamipress-add-ons',
        'href'   => admin_url( 'admin.php?page=gamipress_coupons' )
    ) );

}
add_action( 'admin_bar_menu', 'gamipress_coupons_admin_bar_menu', 999 );


/**
 * GamiPress Coupons Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_coupons_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-coupons-license'] = array(
        'title' => __( 'GamiPress Coupons', 'gamipress-coupons' ),
        'fields' => array(
            'gamipress_coupons_license' => array(
                'name' => __( 'License', 'gamipress-coupons' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_COUPONS_FILE,
                'item_name' => 'Coupons',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_coupons_licenses_meta_boxes' );

/**
 * GamiPress Coupons automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_coupons_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-coupons'] = __( 'Coupons', 'gamipress-coupons' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_coupons_automatic_updates' );

/**
 * Turn "0000-00-00 00:00:00" dates into an empty string.
 *
 * @param  mixed      $value      The unescaped value from the database.
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object
 *
 * @return mixed                  Escaped value to be displayed.
 */
function gamipress_coupons_date_field_escape_cb( $value, $field_args, $field ) {

    $escaped_value = esc_attr( $value );

    if( $value === '0000-00-00 00:00:00' )
        $escaped_value = '';

    return $escaped_value;
}