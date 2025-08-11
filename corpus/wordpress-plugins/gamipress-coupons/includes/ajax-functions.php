<?php
/**
 * Ajax Functions
 *
 * @package     GamiPress\Coupons\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function to process the coupon redemption
 *
 * @since 1.0.0
 */
function gamipress_coupons_ajax_redeem_coupon() {

    $prefix = '_gamipress_coupons_';

    $nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

    // Security check
    if ( ! wp_verify_nonce( $nonce, 'gamipress_coupons_redeem_coupon_form' ) )
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-coupons' ) );

    // Check the user ID
    $user_id = get_current_user_id();

    if( $user_id === 0 )
        wp_send_json_error( __( 'You need to log in to redeem a coupon.', 'gamipress-coupons' ) );

    /* ----------------------------
     * Check coupon code
     ---------------------------- */

    // Get the received coupon code
    $code = isset( $_POST['code'] ) ? $_POST['code'] : '';

    if( empty( $code ) )
        wp_send_json_error( __( 'Please, fill the coupon code field.', 'gamipress-coupons' ) );

    // Check if coupon exist
    $coupon_id = gamipress_coupons_get_coupon_id_by( 'code', $code );

    if( $coupon_id === 0 )
        wp_send_json_error( __( 'Invalid coupon code.', 'gamipress-coupons' ) );

    // Setup the CT Table
    ct_setup_table( 'gamipress_coupons' );

    $coupon = ct_get_object( $coupon_id );

    // Check if coupon is not expired
    if( $coupon->status !== 'active' )
        wp_send_json_error( __( 'Coupon has expired.', 'gamipress-coupons' ) );

    // Check if coupon if has passed the limit dates
    $current_time = current_time( 'timestamp' );

    // Start date
    if( $coupon->start_date !== '0000-00-00 00:00:00' ) {
        $start_date = strtotime( $coupon->start_date );

        if( $current_time < $start_date )
            wp_send_json_error( __( 'Coupon is not active yet', 'gamipress-coupons' ) );
    }

    // End date
    if( $coupon->end_date !== '0000-00-00 00:00:00' ) {
        $end_date = strtotime( $coupon->end_date );

        if( $current_time > $end_date ) {
            // Coupon expired by end date
            gamipress_coupons_update_coupon_status( $coupon_id, 'expired' );

            wp_send_json_error( __( 'Coupon has expired.', 'gamipress-coupons' ) );
        }
    }

    // Check if user exceed the uses
    $uses = absint( ct_get_object_meta( $coupon_id, $prefix . 'uses', true ) );
    $max_uses = absint( $coupon->max_uses );

    // Max uses
    if( $max_uses !== 0 && $uses >= $max_uses ) {
        // Coupon expired by max uses
        gamipress_coupons_update_coupon_status( $coupon_id, 'expired' );

        wp_send_json_error( __( 'Coupon has expired.', 'gamipress-coupons' ) );
    }

    $user_uses = gamipress_coupons_get_coupon_user_uses( $coupon_id, $user_id );
    $max_uses_per_user = absint( $coupon->max_uses_per_user );

    // Max user uses
    if( $max_uses_per_user !== 0 && $user_uses >= $max_uses_per_user )
        wp_send_json_error( __( 'You have already redeemed this coupon.', 'gamipress-coupons' ) );

    // Check if coupon is restricted to a group of users or users are being excluded for it's use
    $restricted = (bool) ct_get_object_meta( $coupon_id, $prefix . 'restrict_to_users', true );

    if( $restricted ) {

        $allowed = false;

        // Check if user is allowed by role
        $allowed_roles = ct_get_object_meta( $coupon_id, $prefix . 'allowed_roles', true );

        // Check if user role has been manually allowed
        if( is_array( $allowed_roles ) ) {

            foreach( $allowed_roles as $allowed_role ) {
                if( user_can( $user_id, $allowed_role ) ) {
                    $allowed = true;
                    break;
                }
            }

        }

        // If not allowed by role, check if allowed by ID
        if( ! $allowed ) {
            $allowed_users = ct_get_object_meta( $coupon_id, $prefix . 'allowed_users', true );

            // Check if user ID has been manually allowed
            if( is_array( $allowed_users ) ) {

                foreach( $allowed_users as $allowed_user ) {
                    if( $user_id === absint( $allowed_user ) ) {
                        $allowed = true;
                        break;
                    }
                }

            }
        }

        // If not allowed return
        if( ! $allowed ) {
            wp_send_json_error( __( 'You can\'t redeemed this coupon.', 'gamipress-coupons' ) );
        }
    } else {
        // Check if user is excluded

        $excluded = false;

        // Check if user is excluded by role
        $excluded_roles = ct_get_object_meta( $coupon_id, $prefix . 'excluded_roles', true );

        // Check if user role has been manually excluded
        if( is_array( $excluded_roles ) ) {

            foreach( $excluded_roles as $excluded_role ) {
                if( user_can( $user_id, $excluded_role ) ) {
                    $excluded = true;
                    break;
                }
            }

        }

        // If not excluded by role, check if excluded by ID
        if( ! $excluded ) {
            $excluded_users = ct_get_object_meta( $coupon_id, $prefix . 'excluded_users', true );

            // Check if user ID has been manually excluded
            if( is_array( $excluded_users ) ) {

                foreach( $excluded_users as $excluded_user ) {
                    if( $user_id === absint( $excluded_user ) ) {
                        $excluded = true;
                        break;
                    }
                }

            }
        }

        // If excluded return
        if( $excluded )
            wp_send_json_error( __( 'You can\'t redeemed this coupon.', 'gamipress-coupons' ) );

    }

    /* ----------------------------
     * Everything done, so process it!
     ---------------------------- */

    // Increase coupon uses
    ct_update_object_meta( $coupon_id, $prefix . 'uses', ( $uses + 1 ), $uses );

    // Log coupon redemption
    gamipress_coupons_log_coupon_redemption( $coupon_id, $user_id );

    // Award all items to the user
    gamipress_coupons_award_coupon_rewards( $coupon_id, $user_id );

    // Get the coupon rewards to pass it on next filter
    $coupon_rewards = gamipress_coupons_get_coupon_rewards( $coupon_id );

    // Force table setup to avoid issues on following actions and filters
    ct_setup_table( 'gamipress_coupons' );

    /**
     * User redeem coupon action
     *
     * @since 1.0.0
     *
     * @param int       $user_id        User that redeemed the coupon
     * @param stdClass  $coupon         Coupon stdClass object
     * @param array     $coupon_rewards Coupon rewards array
     */
    do_action( 'gamipress_coupons_user_redeem_coupon', $user_id, $coupon, $coupon_rewards );

    /* ----------------------------
     * Response processing
     ---------------------------- */

    $response = array(
        'success'       => true,
        'message'       => __( 'Coupon code has been completely redeemed.', 'gamipress-coupons' ),
    );

    /**
     * Let other functions process the coupon and get their response
     *
     * @since 1.0.0
     *
     * @param array     $response       Processing response
     * @param int       $user_id        User that redeemed the coupon
     * @param stdClass  $coupon         Coupon stdClass object
     * @param array     $coupon_rewards Coupon rewards array
     *
     * @return array    $response       Response
     */
    $response = apply_filters( "gamipress_coupons_process_redeem_coupon_response", $response, $user_id, $coupon, $coupon_rewards );

    if( $response['success'] === true )
        wp_send_json_success( $response );
    else
        wp_send_json_error( $response );

}
add_action( 'wp_ajax_gamipress_coupons_redeem_coupon', 'gamipress_coupons_ajax_redeem_coupon' );

/**
 * Ajax function to add a coupon note at backend
 *
 * @since 1.0.0
 */
function gamipress_coupons_ajax_add_coupon_note() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Security check
    if( ! current_user_can( gamipress_get_manager_capability() ) )
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-coupons' ) );

    // Setup vars
    $user_id = get_current_user_id();
    $coupon_id = absint( $_REQUEST['coupon_id'] );
    $title = sanitize_text_field( $_REQUEST['title'] );
    $description = sanitize_textarea_field( $_REQUEST['description'] );

    // Check all vars
    if( $coupon_id === 0 )
        wp_send_json_error( __( 'Wrong coupon ID.', 'gamipress-coupons' ) );

    if( empty( $title ) )
        wp_send_json_error( __( 'Please, fill the title.', 'gamipress-coupons' ) );

    if( empty( $description ) )
        wp_send_json_error( __( 'Please, fill the note.', 'gamipress-coupons' ) );

    // Setup the coupon notes table
    $ct_table = ct_setup_table( 'gamipress_coupon_notes' );

    // Insert the new coupon note
    $coupon_note_id = $ct_table->db->insert( array(
        'coupon_id' => $coupon_id,
        'title' => $title,
        'description' => $description,
        'user_id' => $user_id,
        'date' => date( 'Y-m-d H:i:s' )
    ) );

    // Get the coupon note object
    $coupon_note = ct_get_object( $coupon_note_id );

    // Setup the coupons table
    ct_setup_table( 'gamipress_coupons' );

    // Get the coupon object
    $coupon = ct_get_object( $coupon_id );

    // Get the coupon note html to return as response
    ob_start();

    gamipress_coupons_admin_render_coupon_note( $coupon_note, $coupon );

    $response = ob_get_clean();

    wp_send_json_success( $response );

}
add_action( 'wp_ajax_gamipress_coupons_add_coupon_note', 'gamipress_coupons_ajax_add_coupon_note' );

/**
 * Ajax function to delete a coupon note at backend
 *
 * @since 1.0.0
 */
function gamipress_coupons_ajax_delete_coupon_note() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Security check
    if( ! current_user_can( gamipress_get_manager_capability() ) )
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-coupons' ) );

    // Setup vars
    $coupon_note_id = absint( $_REQUEST['coupon_note_id'] );

    // Check all vars
    if( $coupon_note_id === 0 )
        wp_send_json_error( __( 'Wrong coupon note ID.', 'gamipress-coupons' ) );

    // Setup the coupon notes table
    $ct_table = ct_setup_table( 'gamipress_coupon_notes' );

    $result = $ct_table->db->delete( $coupon_note_id );

    wp_send_json_success( __( 'Coupon note deleted successfully.', 'gamipress-coupons' ) );

}
add_action( 'wp_ajax_gamipress_coupons_delete_coupon_note', 'gamipress_coupons_ajax_delete_coupon_note' );