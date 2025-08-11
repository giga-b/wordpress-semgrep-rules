<?php
/**
 * Listeners
 *
 * @package     GamiPress\Restrict_Content\Listeners
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Post unlocked with points listener
 *
 * @since 1.0.2
 *
 * @param int       $post_id 	    The post unlocked ID
 * @param int       $user_id 	    The user ID
 * @param int       $points 	    The amount of points expended
 * @param string    $points_type    The points type of the amount of points expended
 */
function gamipress_restrict_content_post_unlocked_with_points_listener( $post_id, $user_id, $points, $points_type ) {

    // Unlock post by any way
    do_action( 'gamipress_restrict_content_unlock_post', $post_id, $user_id );

    // Unlock specific post by any way
    do_action( 'gamipress_restrict_content_unlock_specific_post', $post_id, $user_id );

    // Unlock post with points
    do_action( 'gamipress_restrict_content_unlock_post_with_points', $post_id, $user_id, $points, $points_type );

    // Unlock specific post with points
    do_action( 'gamipress_restrict_content_unlock_specific_post_with_points', $post_id, $user_id, $points, $points_type );

}
add_action( 'gamipress_restrict_content_post_unlocked_with_points', 'gamipress_restrict_content_post_unlocked_with_points_listener', 10, 4 );

/**
 * Post unlocked meeting all restrictions listener
 *
 * @since 1.0.2
 *
 * @param int       $post_id 	    The post unlocked ID
 * @param int       $user_id 	    The user ID
 * @param array     $restrictions 	Post configured restrictions
 */
function gamipress_restrict_content_post_unlocked_listener( $post_id, $user_id, $restrictions ) {

    // Unlock post by any way
    do_action( 'gamipress_restrict_content_unlock_post', $post_id, $user_id );

    // Unlock specific post by any way
    do_action( 'gamipress_restrict_content_unlock_specific_post', $post_id, $user_id );

    // Unlock post by meeting all requirements
    do_action( 'gamipress_restrict_content_unlock_post_by_requirements', $post_id, $user_id, $restrictions );

    // Unlock specific post by meeting all requirements
    do_action( 'gamipress_restrict_content_unlock_specific_post_by_requirements', $post_id, $user_id, $restrictions );

}
add_action( 'gamipress_restrict_content_post_unlocked_meeting_all_restrictions', 'gamipress_restrict_content_post_unlocked_listener', 10, 3 );

/**
 * Content unlocked listener
 *
 * @since 1.0.2
 *
 * @param string    $content_id 	The content unlocked ID
 * @param int       $user_id 	    The user ID
 * @param int       $post_id 	    The post ID where content is placed
 * @param int       $points 	    The amount of points expended
 * @param string    $points_type    The points type of the amount of points expended
 */
function gamipress_restrict_content_content_unlocked_listener( $content_id, $user_id, $post_id, $points, $points_type ) {

    // Unlock content
    do_action( 'gamipress_restrict_content_unlock_content', $content_id, $user_id, $post_id, $points, $points_type );

    // Unlock specific content
    do_action( 'gamipress_restrict_content_unlock_specific_content', $content_id, $user_id, $post_id, $points, $points_type );

    // Unlock content on a specific post
    do_action( 'gamipress_restrict_content_unlock_content_specific_post', $content_id, $user_id, $post_id, $points, $points_type );

    // Unlock specific content on a specific post
    do_action( 'gamipress_restrict_content_unlock_specific_content_specific_post', $content_id, $user_id, $post_id, $points, $points_type );

}
add_action( 'gamipress_restrict_content_content_unlocked_with_points', 'gamipress_restrict_content_content_unlocked_listener', 10, 5 );