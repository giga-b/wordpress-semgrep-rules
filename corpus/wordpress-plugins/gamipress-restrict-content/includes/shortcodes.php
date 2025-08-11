<?php
/**
 * Shortcodes
 *
 * @package     GamiPress\Restrict_Content\Shortcodes
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// GamiPress Restrict Content Shortcodes
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/shortcodes/gamipress_post_restrictions.php';
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/shortcodes/gamipress_posts_restricted.php';
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/shortcodes/gamipress_posts_unlocked.php';
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/shortcodes/gamipress_restrict_content.php';
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/shortcodes/gamipress_show_content_if.php';
require_once GAMIPRESS_RESTRICT_CONTENT_DIR . 'includes/shortcodes/gamipress_hide_content_if.php';

/**
 * Register plugin shortcode groups
 *
 * @since 1.0.0
 *
 * @param array $shortcode_groups
 *
 * @return array
 */
function gamipress_restrict_content_shortcodes_groups( $shortcode_groups ) {

    $shortcode_groups['restrict_content'] = __( 'Restrict Content', 'gamipress-restrict-content' );

    return $shortcode_groups;

}
add_filter( 'gamipress_shortcodes_groups', 'gamipress_restrict_content_shortcodes_groups' );