<?php
/**
 * Shortcodes
 *
 * @package GamiPress\Time_Based_Rewards\Shortcodes
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// GamiPress Time-based Rewards Shortcodes
require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/shortcodes/gamipress_time_based_reward.php';

/**
 * Register plugin shortcode groups
 *
 * @since 1.0.0
 *
 * @param array $shortcode_groups
 *
 * @return array
 */
function gamipress_time_based_rewards_shortcodes_groups( $shortcode_groups ) {

    $shortcode_groups['time_based_rewards'] = __( 'Time-based Rewards', 'gamipress-time-based-rewards' );

    return $shortcode_groups;

}
add_filter( 'gamipress_shortcodes_groups', 'gamipress_time_based_rewards_shortcodes_groups' );