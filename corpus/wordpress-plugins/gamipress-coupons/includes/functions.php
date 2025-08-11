<?php
/**
 * Functions
 *
 * @package     GamiPress\Coupons\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Callback to retrieve user roles as select options
 *
 * @since  1.0.0
 *
 * @return array
 */
function gamipress_coupons_get_roles_options() {

    $options = array();

    $editable_roles = array_reverse( get_editable_roles() );

    foreach ( $editable_roles as $role => $details ) {

        $options[$role] = translate_user_role( $details['name'] );

    }

    return $options;

}