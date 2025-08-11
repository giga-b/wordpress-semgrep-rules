<?php
/**
 * Scripts
 *
 * @package     GamiPress\Restrict_content\Scripts
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_restrict_content_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-restrict-content-css', GAMIPRESS_RESTRICT_CONTENT_URL . 'assets/css/gamipress-restrict-content' . $suffix . '.css', array( ), GAMIPRESS_RESTRICT_CONTENT_VER, 'all' );

    // Scripts
    wp_register_script( 'gamipress-restrict-content-js', GAMIPRESS_RESTRICT_CONTENT_URL . 'assets/js/gamipress-restrict-content' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_RESTRICT_CONTENT_VER, true );

}
add_action( 'init', 'gamipress_restrict_content_register_scripts' );

/**
 * Enqueue frontend scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_restrict_content_enqueue_scripts( $hook = null ) {

    // Stylesheets
    wp_enqueue_style( 'gamipress-restrict-content-css' );

    // Scripts
    wp_localize_script( 'gamipress-restrict-content-js', 'gamipress_restrict_content', array(
        'ajaxurl'   => esc_url( admin_url( 'admin-ajax.php', 'relative' ) ),
        'nonce'     => wp_create_nonce( 'gamipress_restrict_content' ),
    ) );

    wp_enqueue_script( 'gamipress-restrict-content-js' );

}
add_action( 'wp_enqueue_scripts', 'gamipress_restrict_content_enqueue_scripts', 100 );

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_restrict_content_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-restrict-content-admin-css', GAMIPRESS_RESTRICT_CONTENT_URL . 'assets/css/gamipress-restrict-content-admin' . $suffix . '.css', array( ), GAMIPRESS_RESTRICT_CONTENT_VER, 'all' );

    // Scripts
    wp_register_script( 'gamipress-restrict-content-admin-js', GAMIPRESS_RESTRICT_CONTENT_URL . 'assets/js/gamipress-restrict-content-admin' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_RESTRICT_CONTENT_VER, true );
    wp_register_script( 'gamipress-restrict-content-shortcodes-editor-js', GAMIPRESS_RESTRICT_CONTENT_URL . 'assets/js/gamipress-restrict-content-shortcodes-editor' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_RESTRICT_CONTENT_VER, true );
    wp_register_script( 'gamipress-restrict-content-widgets-js', GAMIPRESS_RESTRICT_CONTENT_URL . 'assets/js/gamipress-restrict-content-widgets' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_RESTRICT_CONTENT_VER, true );
    wp_register_script( 'gamipress-restrict-content-requirements-ui-js', GAMIPRESS_RESTRICT_CONTENT_URL . 'assets/js/gamipress-restrict-content-requirements-ui' . $suffix . '.js', array( 'jquery' ), GAMIPRESS_RESTRICT_CONTENT_VER, true );

}
add_action( 'admin_init', 'gamipress_restrict_content_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_restrict_content_admin_enqueue_scripts( $hook ) {

    global $post_type;

    // Stylesheets
    wp_enqueue_style( 'gamipress-restrict-content-admin-css' );

    $post_types_slugs = gamipress_restrict_content_post_types_slugs();

    if( in_array( $post_type, $post_types_slugs )
        && ( $hook === 'post.php' || $hook === 'post-new.php' ) ) {

        // Enqueue admin functions
        gamipress_enqueue_admin_functions_script();

        wp_localize_script( 'gamipress-restrict-content-admin-js', 'gamipress_restrict_content_admin', array(
            'nonce'     => gamipress_get_admin_nonce(),
            'labels'    => array(
                'earn-points'           => __( 'Earn {points} {points_type}', 'gamipress-restrict-content' ),
                'earn-rank'             => __( 'Reach the {rank_type} {rank}', 'gamipress-restrict-content' ),
                'specific-achievement'  => __( 'Unlock the {achievement_type} {achievement} {count}', 'gamipress-restrict-content' ),
                'any-achievement'       => __( 'Unlock any {achievement_type} {count}', 'gamipress-restrict-content' ),
                'all-achievements'     	=> __( 'Unlock all {achievement_type}', 'gamipress-restrict-content' ),
            )
        ) );

        // Scripts
        wp_enqueue_script( 'gamipress-restrict-content-admin-js' );

    }

    // Shortcode Editor
    if(
        ( in_array( $hook, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) && post_type_supports( $post_type, 'editor' ) 	// Add/edit views of post types that supports editor feature
        || $hook === 'gamipress_page_gamipress_settings'																						// GamiPress settings screen
    ) {

        wp_localize_script( 'gamipress-restrict-content-shortcodes-editor-js', 'gamipress_restrict_content_shortcodes_editor', array(
            'post_types' => $post_types_slugs
        ) );

        wp_enqueue_script( 'gamipress-restrict-content-shortcodes-editor-js' );

    }

    // Widgets scripts
    if( $hook === 'widgets.php' ) {

        wp_localize_script( 'gamipress-restrict-content-widgets-js', 'gamipress_restrict_content_widgets', array(
            'post_types' => $post_types_slugs
        ) );

        wp_enqueue_script( 'gamipress-restrict-content-widgets-js' );
    }

    // Requirements ui script
    if ( $post_type === 'points-type'
        || in_array( $post_type, gamipress_get_achievement_types_slugs() )
        || in_array( $post_type, gamipress_get_rank_types_slugs() ) ) {

        wp_enqueue_script( 'gamipress-restrict-content-requirements-ui-js' );

    }

}
add_action( 'admin_enqueue_scripts', 'gamipress_restrict_content_admin_enqueue_scripts', 100 );