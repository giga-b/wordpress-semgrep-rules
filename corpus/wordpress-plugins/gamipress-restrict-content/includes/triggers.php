<?php
/**
 * Triggers
 *
 * @package     GamiPress\Restrict_Content\Triggers
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin specific triggers
 *
 * @since 1.0.2
 *
 * @param array $triggers
 *
 * @return array
 */
function gamipress_restrict_content_activity_triggers( $triggers ) {

    $triggers[__( 'Restrict Content', 'gamipress-restrict-content' )] = array(

        // Unlock post by any way
        'gamipress_restrict_content_unlock_post' => __( 'Get access to a post', 'gamipress-restrict-content' ),
        'gamipress_restrict_content_unlock_specific_post' => __( 'Get access to a specific post', 'gamipress-restrict-content' ),

        // Unlock post by meet the requirements
        'gamipress_restrict_content_unlock_post_by_requirements' => __( 'Get access to a post by meeting all requirements', 'gamipress-restrict-content' ),
        'gamipress_restrict_content_unlock_specific_post_by_requirements' => __( 'Get access to a specific post by meeting all requirements', 'gamipress-restrict-content' ),

        // Unlock post with points
        'gamipress_restrict_content_unlock_post_with_points' => __( 'Get access to a post using points', 'gamipress-restrict-content' ),
        'gamipress_restrict_content_unlock_specific_post_with_points' => __( 'Get access to a specific post using points', 'gamipress-restrict-content' ),

        // Unlock content
        'gamipress_restrict_content_unlock_content' => __( 'Get access to a portion of content', 'gamipress-restrict-content' ),
        'gamipress_restrict_content_unlock_specific_content' => __( 'Get access to a specific portion of content', 'gamipress-restrict-content' ),
        'gamipress_restrict_content_unlock_content_specific_post' => __( 'Get access to a portion of content on a specific post', 'gamipress-restrict-content' ),
        'gamipress_restrict_content_unlock_specific_content_specific_post' => __( 'Get access to a specific portion of content on a specific post', 'gamipress-restrict-content' ),
    );

    return $triggers;

}
add_filter( 'gamipress_activity_triggers', 'gamipress_restrict_content_activity_triggers' );

/**
 * Register plugin specific activity triggers
 *
 * @since 1.0.2
 *
 * @param  array $specific_activity_triggers
 * @return array
 */
function gamipress_restrict_content_specific_activity_triggers( $specific_activity_triggers ) {

    $post_types = gamipress_restrict_content_post_types();

    // Turn array( 'post-type' => 'Label' ) to array( 'post-type' )
    $post_types = array_keys( $post_types );

    // Unlock post
    $specific_activity_triggers['gamipress_restrict_content_unlock_specific_post'] = $post_types;
    $specific_activity_triggers['gamipress_restrict_content_unlock_specific_post_by_requirements'] = $post_types;
    $specific_activity_triggers['gamipress_restrict_content_unlock_specific_post_with_points'] = $post_types;

    // Unlock content
    $specific_activity_triggers['gamipress_restrict_content_unlock_content_specific_post'] = $post_types;
    $specific_activity_triggers['gamipress_restrict_content_unlock_specific_content_specific_post'] = $post_types;

    return $specific_activity_triggers;

}
add_filter( 'gamipress_specific_activity_triggers', 'gamipress_restrict_content_specific_activity_triggers' );

/**
 * Build custom activity trigger label
 *
 * @since 1.0.2
 *
 * @param string    $title
 * @param integer   $requirement_id
 * @param array     $requirement
 *
 * @return string
 */
function gamipress_restrict_content_activity_trigger_label( $title, $requirement_id, $requirement ) {

    $content_id = ( isset( $requirement['content_id'] ) ) ? $requirement['content_id'] : '';

    switch( $requirement['trigger_type'] ) {

        case 'gamipress_restrict_content_unlock_specific_content':
            return sprintf( __( 'Get access to the %s portion of content', 'gamipress-restrict-content' ), $content_id );
            break;
        case 'gamipress_restrict_content_unlock_specific_content_specific_post':
            $achievement_post_id = absint( $requirement['achievement_post'] );
            return sprintf( __( 'Get access to the %s portion of content on %s', 'gamipress-restrict-content' ), $content_id, get_the_title( $achievement_post_id ) );
            break;

    }

    return $title;
}
add_filter( 'gamipress_activity_trigger_label', 'gamipress_restrict_content_activity_trigger_label', 10, 3 );

/**
 * Register plugin specific activity triggers labels
 *
 * @since 1.0.2
 *
 * @param  array $specific_activity_trigger_labels
 * @return array
 */
function gamipress_restrict_content_specific_activity_trigger_label( $specific_activity_trigger_labels ) {

    // Unlock post
    $specific_activity_trigger_labels['gamipress_restrict_content_unlock_specific_post'] = __( 'Get access to %s', 'gamipress-restrict-content' );
    $specific_activity_trigger_labels['gamipress_restrict_content_unlock_specific_post_by_requirements'] = __( 'Get access to %s by completing all requirements', 'gamipress-restrict-content' );
    $specific_activity_trigger_labels['gamipress_restrict_content_unlock_specific_post_with_points'] = __( 'Get access to %s using points', 'gamipress-restrict-content' );

    // Unlock content
    $specific_activity_trigger_labels['gamipress_restrict_content_unlock_content_specific_post'] = __( 'Get access to a portion of content on %s', 'gamipress-restrict-content' );
    $specific_activity_trigger_labels['gamipress_restrict_content_unlock_specific_content_specific_post'] = __( 'Get access to a portion of content on %s', 'gamipress-restrict-content' );

    return $specific_activity_trigger_labels;

}
add_filter( 'gamipress_specific_activity_trigger_label', 'gamipress_restrict_content_specific_activity_trigger_label' );

/**
 * Get user for a given trigger action.
 *
 * @since 1.0.2
 *
 * @param  integer $user_id user ID to override.
 * @param  string  $trigger Trigger name.
 * @param  array   $args    Passed trigger args.
 *
 * @return integer          User ID.
 */
function gamipress_restrict_content_trigger_get_user_id( $user_id, $trigger, $args ) {

    switch ( $trigger ) {

        // Unlock post by any way
        case 'gamipress_restrict_content_unlock_post':
        case 'gamipress_restrict_content_unlock_specific_post':
        case 'gamipress_restrict_content_unlock_post_by_requirements':
        case 'gamipress_restrict_content_unlock_specific_post_by_requirements':
        case 'gamipress_restrict_content_unlock_post_with_points':
        case 'gamipress_restrict_content_unlock_specific_post_with_points':
        // Unlock content
        case 'gamipress_restrict_content_unlock_content':
        case 'gamipress_restrict_content_unlock_specific_content':
        case 'gamipress_restrict_content_unlock_content_specific_post':
        case 'gamipress_restrict_content_unlock_specific_content_specific_post':
            $user_id = $args[1];
            break;
    }

    return $user_id;

}
add_filter( 'gamipress_trigger_get_user_id', 'gamipress_restrict_content_trigger_get_user_id', 10, 3 );

/**
 * Get the id for a given specific trigger action.
 *
 * @since 1.0.2
 *
 * @param  integer  $specific_id Specific ID.
 * @param  string  $trigger Trigger name.
 * @param  array   $args    Passed trigger args.
 *
 * @return integer          Specific ID.
 */
function gamipress_restrict_content_specific_trigger_get_id( $specific_id, $trigger = '', $args = array() ) {

    switch ( $trigger ) {

        // Unlock post
        case 'gamipress_restrict_content_unlock_specific_post':
        case 'gamipress_restrict_content_unlock_specific_post_by_requirements':
        case 'gamipress_restrict_content_unlock_specific_post_with_points':
            $specific_id = $args[0];
            break;

        // Unlock content
        case 'gamipress_restrict_content_unlock_content_specific_post':
        case 'gamipress_restrict_content_unlock_specific_content_specific_post':
            $specific_id = $args[2];
            break;
    }

    return $specific_id;

}
add_filter( 'gamipress_specific_trigger_get_id', 'gamipress_restrict_content_specific_trigger_get_id', 10, 3 );

/**
 * Extended meta data for event trigger logging
 *
 * @since 1.0.2
 *
 * @param array 	$log_meta
 * @param integer 	$user_id
 * @param string 	$trigger
 * @param integer 	$site_id
 * @param array 	$args
 *
 * @return array
 */
function gamipress_restrict_content_log_event_trigger_meta_data( $log_meta, $user_id, $trigger, $site_id, $args ) {

    switch ( $trigger ) {

        // Unlock post
        case 'gamipress_restrict_content_unlock_post':
        case 'gamipress_restrict_content_unlock_specific_post':
        case 'gamipress_restrict_content_unlock_post_by_requirements':
        case 'gamipress_restrict_content_unlock_specific_post_by_requirements':
        case 'gamipress_restrict_content_unlock_post_with_points':
        case 'gamipress_restrict_content_unlock_specific_post_with_points':
            // Add the post ID
            $log_meta['post_id'] = $args[0];
            break;

            // Unlock content
        case 'gamipress_restrict_content_unlock_content':
        case 'gamipress_restrict_content_unlock_specific_content':
        case 'gamipress_restrict_content_unlock_content_specific_post':
        case 'gamipress_restrict_content_unlock_specific_content_specific_post':
            // Add the content and post IDs
            $log_meta['content_id'] = $args[0];
            $log_meta['post_id'] = $args[2];
            break;
    }

    return $log_meta;
}
add_filter( 'gamipress_log_event_trigger_meta_data', 'gamipress_restrict_content_log_event_trigger_meta_data', 10, 5 );