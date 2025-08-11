<?php
/**
 * Post Types
 *
 * @package     GamiPress\Leaderboards\Post_Types
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Migrates posts with the type "leaderboard" to "gamipress_leaderboard"
 *
 * @since  1.3.8
 */
function gamipress_leaderboards_migrate_post_types() {

    global $wpdb;

    // Get the stored option
    if( gamipress_is_network_wide_active() ) {
        $already_migrated = ( $exists = get_site_option( 'gamipress_leaderboards_post_type_migration' ) ) ? $exists : '';
    } else {
        $already_migrated = ( $exists = get_option( 'gamipress_leaderboards_post_type_migration' ) ) ? $exists : '';
    }

    // Bail if migration has been already made
    if( (bool) $already_migrated ) {
        return;
    }

    $posts = GamiPress()->db->posts;

    $found = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$posts} WHERE post_type = 'leaderboard'" ) );

    if( $found ) {
        $wpdb->query( "UPDATE {$posts} SET post_type = 'gp_leaderboard' WHERE post_type = 'leaderboard'" );
    }

    // Update the stored option
    if( gamipress_is_network_wide_active() ) {
        update_site_option( 'gamipress_leaderboards_post_type_migration', '1' );
    } else {
        update_option( 'gamipress_leaderboards_post_type_migration', '1' );
    }
}
add_action( 'init', 'gamipress_leaderboards_migrate_post_types', 10 );

/**
 * Register Leaderboard CPT
 *
 * @since  1.0.0
 */
function gamipress_leaderboards_register_post_types() {

    $labels = gamipress_leaderboards_labels();

    $public_leaderboards = (bool) gamipress_leaderboards_get_option( 'public', false );
    $supports = gamipress_leaderboards_get_option( 'supports', array( 'title', 'editor', 'excerpt' ) );

    if( ! is_array( $supports ) ) {
        $supports =  array( 'title', 'editor', 'excerpt' );
    }

    // Register Leaderboard
    register_post_type( 'gp_leaderboard', array(
        'labels'             => array(
            'name'               => $labels['plural'],
            'singular_name'      => $labels['singular'],
            'add_new'            => __( 'Add New', 'gamipress-leaderboards' ),
            'add_new_item'       => sprintf( __( 'Add New %s', 'gamipress-leaderboards' ), $labels['singular'] ),
            'edit_item'          => sprintf( __( 'Edit %s', 'gamipress-leaderboards' ), $labels['singular'] ),
            'new_item'           => sprintf( __( 'New %s', 'gamipress-leaderboards' ), $labels['singular'] ),
            'all_items'          => $labels['plural'],
            'view_item'          => sprintf( __( 'View %s', 'gamipress-leaderboards' ), $labels['singular'] ),
            'search_items'       => sprintf( __( 'Search %s', 'gamipress-leaderboards' ), $labels['plural'] ),
            'not_found'          => sprintf( __( 'No %s found', 'gamipress-leaderboards' ), strtolower( $labels['plural'] ) ),
            'not_found_in_trash' => sprintf( __( 'No %s found in Trash', 'gamipress-leaderboards' ), strtolower( $labels['plural'] ) ),
            'parent_item_colon'  => '',
            'menu_name'          => $labels['plural'],
        ),
        'public'             => $public_leaderboards,
        'publicly_queryable' => $public_leaderboards,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'show_in_rest'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => gamipress_leaderboards_get_option( 'slug', 'leaderboards' ) ),
        'capability_type'    => 'page',
        'has_archive'        => $public_leaderboards,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => $supports
    ) );

}
add_action( 'init', 'gamipress_leaderboards_register_post_types', 11 );

/**
 * Leaderboards labels
 *
 * @since  1.0.0
 * @return array
 */
function gamipress_leaderboards_labels() {
    return apply_filters( 'gamipress_leaderboards_labels' , array(
        'singular' => __( 'Leaderboard', 'gamipress-leaderboards' ),
        'plural' => __( 'Leaderboards', 'gamipress-leaderboards' )
    ));
}
