<?php
/**
 * Logs
 *
 * @package     GamiPress\Restrict_Content\Logs
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin log types
 *
 * @since 1.0.2
 *
 * @param array $gamipress_log_types
 *
 * @return array
 */
function gamipress_restrict_content_logs_types( $gamipress_log_types ) {

    $gamipress_log_types['content_unlock'] = __( 'Content Unlock', 'gamipress-restrict-content' );

    return $gamipress_log_types;

}
add_filter( 'gamipress_logs_types', 'gamipress_restrict_content_logs_types' );

/**
 * Log post unlock on logs
 *
 * @since 1.0.2
 *
 * @param int $post_id
 * @param int $user_id
 *
 * @return int|false
 */
function gamipress_restrict_content_log_post_unlock( $post_id = null, $user_id = null ) {

    // Can't unlock a not existent post
    if( ! get_post( $post_id ) ) {
        return false;
    }

    // Log meta data
    $log_meta = array(
        'pattern' => sprintf( __( '{user} got access to "%s"', 'gamipress-restrict-content' ), get_post_field( 'post_title', $post_id ) ),
        'post_id' => $post_id,
    );

    // Register the content unlock on logs
    return gamipress_insert_log( 'content_unlock', $user_id, 'private', $log_meta );

}

/**
 * Log content unlock on logs
 *
 * @since 1.0.2
 *
 * @param int $content_id
 * @param int $user_id
 * @param int $post_id      Post where content is placed
 *
 * @return int
 */
function gamipress_restrict_content_log_content_unlock( $content_id = null, $user_id = null, $post_id = null ) {

    global $post;

    // If not post ID given, try to get it from global post
    if( ! $post_id && $post && $post->ID ) {
        $post_id = $post->ID;
    }

    // Log meta data
    $log_meta = array(
        'pattern' => __( '{user} got access to a portion of content', 'gamipress-restrict-content' ),
        'content_id' => $content_id,
    );

    // Just log post data if post exists
    if( $post_id && get_post( $post_id ) ) {
        $log_meta['pattern'] = sprintf( __( '{user} got access to a portion of content from "%s"', 'gamipress-restrict-content' ), get_post_field( 'post_title', $post_id ) );
        $log_meta['content_post_id'] = $post_id;
    }



    // Register the content unlock on logs
    return gamipress_insert_log( 'content_unlock', $user_id, 'private', $log_meta );

}