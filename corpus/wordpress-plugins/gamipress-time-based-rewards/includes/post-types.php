<?php
/**
 * Post Types
 *
 * @package     GamiPress\Time_Based_Rewards\Post_Types
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Rewards Calendar CPT
 *
 * @since  1.0.0
 */
function gamipress_time_based_rewards_register_post_types() {

    $labels = gamipress_time_based_rewards_rewards_calendar_labels();

    $public = (bool) gamipress_time_based_rewards_get_option( 'public', false );
    $supports = gamipress_time_based_rewards_get_option( 'supports', array( 'title', 'editor', 'excerpt' ) );

    if( ! is_array( $supports ) ) {
        $supports =  array( 'title', 'editor', 'excerpt' );
    }

    // Register Time-based Rewards
    register_post_type( 'time-based-reward', array(
        'labels'             => array(
            'name'               => $labels['plural'],
            'singular_name'      => $labels['singular'],
            'add_new'            => __( 'Add New', 'gamipress-time-based-rewards' ),
            'add_new_item'       => sprintf( __( 'Add New %s', 'gamipress-time-based-rewards' ), $labels['singular'] ),
            'edit_item'          => sprintf( __( 'Edit %s', 'gamipress-time-based-rewards' ), $labels['singular'] ),
            'new_item'           => sprintf( __( 'New %s', 'gamipress-time-based-rewards' ), $labels['singular'] ),
            'all_items'          => $labels['plural'],
            'view_item'          => sprintf( __( 'View %s', 'gamipress-time-based-rewards' ), $labels['singular'] ),
            'search_items'       => sprintf( __( 'Search %s', 'gamipress-time-based-rewards' ), $labels['plural'] ),
            'not_found'          => sprintf( __( 'No %s found', 'gamipress-time-based-rewards' ), strtolower( $labels['plural'] ) ),
            'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'gamipress-time-based-rewards' ), strtolower( $labels['plural'] ) ),
            'parent_item_colon'  => '',
            'menu_name'          => $labels['plural'],
        ),
        'public'             => $public,
        'publicly_queryable' => $public,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'show_in_rest'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => gamipress_time_based_rewards_get_option( 'slug', 'time-based-rewards' ) ),
        'capability_type'    => 'page',
        'has_archive'        => $public,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => $supports
    ) );

}
add_action( 'init', 'gamipress_time_based_rewards_register_post_types', 11 );

/**
 * Rewards calendar labels
 *
 * @since  1.0.0
 * @return array
 */
function gamipress_time_based_rewards_rewards_calendar_labels() {

    return apply_filters( 'gamipress_time_based_rewards_rewards_calendar_labels' , array(
        'singular' => __( 'Time-based Reward', 'gamipress-time-based-rewards' ),
        'plural' => __( 'Time-based Rewards', 'gamipress-time-based-rewards' )
    ));

}