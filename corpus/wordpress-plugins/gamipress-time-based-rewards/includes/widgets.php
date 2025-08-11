<?php
/**
 * Widgets
 *
 * @package     GamiPress\Time_Based_Rewards\Widgets
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once GAMIPRESS_TIME_BASED_REWARDS_DIR .'includes/widgets/time-based-reward-widget.php';

// Register plugin widgets
function gamipress_time_based_rewards_register_widgets() {

    register_widget( 'gamipress_time_based_reward_widget' );

}
add_action( 'widgets_init', 'gamipress_time_based_rewards_register_widgets' );