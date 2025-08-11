<?php
/**
 * Ajax Functions
 *
 * @package     GamiPress\Restrict_Content\Ajax_Functions
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function to check and unlock a post by expending an amount of points
 *
 * @since 1.0.2
 *
 * @return void
 */
function gamipress_restrict_content_ajax_unlock_post_with_points() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_restrict_content', 'nonce' );

    $post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0;

    $post = get_post( $post_id );

    // Return if post not exists
    if( ! $post )
        wp_send_json_error( __( 'Post not found.', 'gamipress-restrict-content' ) );

    $user_id = get_current_user_id();

    // Guest not supported yet (basically because they has not points)
    if( $user_id === 0 )
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-restrict-content' ) );

    // Return if user already has got access to this post
    if( gamipress_restrict_content_user_has_unlocked_post( $post_id, $user_id ) ) {
        wp_send_json_error( __( 'You already unlocked this.', 'gamipress-restrict-content' ) );
    }

    // Check if post is unlocked by expending points or is allowed access by expending points
    if( gamipress_restrict_content_get_unlock_by( $post_id ) !== 'expend-points' && ! gamipress_restrict_content_allow_access_with_points( $post_id ) ) {
        wp_send_json_error( __( 'You are not allowed to unlock this.', 'gamipress-restrict-content' ) );
    }

    $points = gamipress_restrict_content_get_points_to_access( $post_id );

    // Return if no points configured
    if( $points === 0 )
        wp_send_json_error( __( 'You are not allowed to unlock this.', 'gamipress-restrict-content' ) );

    // Setup points type
    $points_types = gamipress_get_points_types();
    $points_type = gamipress_restrict_content_get_points_type_to_access( $post_id );

    // Default points label
    $points_label = __( 'Points', 'gamipress-restrict-content' );

    // Points type label
    if( isset( $points_types[$points_type] ) )
        $points_label = $points_types[$points_type]['plural_name'];

    // Setup user points
    $user_points = gamipress_get_user_points( $user_id, $points_type );

    if( $user_points < $points ) {

        $message = sprintf( __( 'Insufficient %s.', 'gamipress-restrict-content' ), $points_label );

        /**
         * Available filter to override the insufficient points text when unlock a restricted post with points
         *
         * @since   1.0.5
         *
         * @param string    $message        The insufficient points message
         * @param int       $post_id        The post ID
         * @param int       $user_id        The current logged in user ID
         * @param int       $points         The required amount of points
         * @param string    $points_type    The required amount points type
         */
        $message = apply_filters( 'gamipress_restrict_content_insufficient_points_message_to_unlock_post', $message, $post_id, $user_id, $points, $points_type );

        wp_send_json_error( $message );
    }

    // Deduct points to user
    gamipress_deduct_points_to_user( $user_id, $points, $points_type, array(
        'log_type' => 'points_expend',
        'reason' => sprintf( __( '{user} expended {points} {points_type} to get access to "%s" for a new total of {total_points} {points_type}', 'gamipress-restrict-content' ), $post->post_title )
    ) );

    if( absint( $post->post_author ) !== 0 ) {

        // Setup the user data to get his display_name
        $user = get_userdata( $user_id );

        // Award points to the author
        gamipress_award_points_to_user( $post->post_author, $points, $points_type, array(
            'reason' => sprintf( __( '{user} earned {points} {points_type} since %s got access to "%s" for a new total of {total_points} {points_type}', 'gamipress-restrict-content' ), $user->display_name, $post->post_title )
        ) );

    }

    // Register the content unlock on logs
    gamipress_restrict_content_log_post_unlock( $post_id, $user_id );

    $congratulations = sprintf( __( 'Congratulations! You got access to %s, redirecting...', 'gamipress-restrict-content' ), $post->post_title );

    // Filter to change congratulations message
    $congratulations = apply_filters( 'gamipress_restrict_content_post_unlocked_with_points_congratulations', $congratulations, $post_id, $user_id, $points, $points_type );

    /**
     * Post unlocked with points action
     *
     * @since 1.0.2
     *
     * @param int       $post_id 	    The post unlocked ID
     * @param int       $user_id 	    The user ID
     * @param int       $points 	    The amount of points expended
     * @param string    $points_type    The points type of the amount of points expended
     */
    do_action( 'gamipress_restrict_content_post_unlocked_with_points', $post_id, $user_id, $points, $points_type );

    wp_send_json_success( array(
        'message' => $congratulations,
        'redirect' => get_permalink( $post_id ),
    ) );

}
add_action( 'wp_ajax_gamipress_restrict_content_unlock_post_with_points', 'gamipress_restrict_content_ajax_unlock_post_with_points' );

/**
 * Ajax function to check and unlock a portion of content by expending an amount of points
 *
 * @since 1.0.2
 *
 * @return void
 */
function gamipress_restrict_content_ajax_unlock_content_with_points() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_restrict_content', 'nonce' );

    $content_id = isset( $_POST['content_id'] ) ? $_POST['content_id'] : '';

    // Return if content ID not given
    if( empty( $content_id ) )
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-restrict-content' ) );

    $post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0;

    $post = get_post( $post_id );

    // Return if post not exists
    if( ! $post )
        wp_send_json_error( __( 'Post not found.', 'gamipress-restrict-content' ) );

    $user_id = get_current_user_id();

    // Guest not supported yet (basically because they has not points)
    if( $user_id === 0 )
        wp_send_json_error( __( 'You are not allowed to perform this action.', 'gamipress-restrict-content' ) );

    // Return if user already has got access to this post
    if( gamipress_restrict_content_user_has_unlocked_content( $content_id, $user_id ) ) {
        wp_send_json_error( __( 'You already unlocked this.', 'gamipress-restrict-content' ) );
    }

    $content_price = gamipress_restrict_content_get_post_content_price( $post_id, $content_id );

    $points = $content_price['points'];

    // Return if no points configured
    if( $points === 0 )
        wp_send_json_error( __( 'You are not allowed to unlock this.', 'gamipress-restrict-content' ) );

    // Setup points type
    $points_types = gamipress_get_points_types();
    $points_type = $content_price['points_type'];

    // Default points label
    $points_label = __( 'Points', 'gamipress-restrict-content' );

    // Points type label
    if( isset( $points_types[$points_type] ) )
        $points_label = $points_types[$points_type]['plural_name'];

    // Setup user points
    $user_points = gamipress_get_user_points( $user_id, $points_type );

    if( $user_points < $points ) {

        $message = sprintf( __( 'Insufficient %s.', 'gamipress-restrict-content' ), $points_label );

        /**
         * Available filter to override the insufficient points text when unlock a restricted content with points
         *
         * @since   1.0.5
         *
         * @param string    $message        The insufficient points message
         * @param string    $content_id     The content ID
         * @param int       $post_id        The post ID where restricted content is placed
         * @param int       $user_id        The current logged in user ID
         * @param int       $points         The required amount of points
         * @param string    $points_type    The required amount points type
         */
        $message = apply_filters( 'gamipress_restrict_content_insufficient_points_message_to_unlock_content', $message, $content_id, $post_id, $user_id, $points, $points_type );

        wp_send_json_error( $message );


    }

    $points_deduct_args = array(
        'log_type' => 'points_expend',
        'reason' => __( '{user} expended {points} {points_type} to get access to a portion of content for a new total of {total_points} {points_type}', 'gamipress-restrict-content' )
    );

    if( $post ) {
        $points_deduct_args['reason'] = sprintf( __( '{user} expended {points} {points_type} to get access to a portion of content from "%s" for a new total of {total_points} {points_type}', 'gamipress-restrict-content' ), $post->post_title );
    }

    // Deduct points to user
    gamipress_deduct_points_to_user( $user_id, $points, $points_type, $points_deduct_args );

    if( $post && absint( $post->post_author ) !== 0 ) {

        // Setup the user data to get his display_name
        $user = get_userdata( $user_id );

        // Award points to the author
        gamipress_award_points_to_user( $post->post_author, $points, $points_type, array(
            'reason' => sprintf( __( '{user} earned {points} {points_type} since %s got access to a portion of content from "%s" for a new total of {total_points} {points_type}', 'gamipress-restrict-content' ), $user->display_name, $post->post_title )
        ) );

    }

    // Register the content unlock on logs
    gamipress_restrict_content_log_content_unlock( $content_id, $user_id, $post_id );

    $congratulations = __( 'Congratulations! You got access to this content, redirecting...', 'gamipress-restrict-content' );

    // Filter to change congratulations message
    $congratulations = apply_filters( 'gamipress_restrict_content_content_unlocked_with_points_congratulations', $congratulations, $post_id, $user_id, $points, $points_type );

    /**
     * Content unlocked with points action
     *
     * @since 1.0.2
     *
     * @param string    $content_id 	The content unlocked ID
     * @param int       $user_id 	    The user ID
     * @param int       $post_id 	    The post ID where content is placed
     * @param int       $points 	    The amount of points expended
     * @param string    $points_type    The points type of the amount of points expended
     */
    do_action( 'gamipress_restrict_content_content_unlocked_with_points', $content_id, $user_id, $post_id, $points, $points_type );

    wp_send_json_success( $congratulations );

}
add_action( 'wp_ajax_gamipress_restrict_content_unlock_content_with_points', 'gamipress_restrict_content_ajax_unlock_content_with_points' );