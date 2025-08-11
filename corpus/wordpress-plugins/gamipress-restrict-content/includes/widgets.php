<?php
/**
 * Widgets
 *
 * @package     GamiPress\Restrict_Content\Widgets
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// GamiPress Restrict Content Widgets
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/widgets/post-restrictions-widget.php';
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/widgets/posts-restricted-widget.php';
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/widgets/posts-unlocked-widget.php';
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/widgets/restrict-content-widget.php';
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/widgets/show-content-if-widget.php';
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/widgets/hide-content-if-widget.php';

// Register plugin widgets
function gamipress_restrict_content_register_widgets() {

    register_widget( 'gamipress_post_restrictions_widget' );
    register_widget( 'gamipress_posts_restricted_widget' );
    register_widget( 'gamipress_posts_unlocked_widget' );
    register_widget( 'gamipress_restrict_content_widget' );
    register_widget( 'gamipress_show_content_if_widget' );
    register_widget( 'gamipress_hide_content_if_widget' );

}
add_action( 'widgets_init', 'gamipress_restrict_content_register_widgets' );