<?php
/**
 * Rewards pop-up template
 *
 * This template can be overridden by copying it to yourtheme/gamipress/time_based_rewards/rewards-popup.php
 */
global $gamipress_time_based_rewards_template_args;

// Shorthand
$a = $gamipress_time_based_rewards_template_args;

// Setup vars
$prefix = '_gamipress_time_based_rewards_';
$time_based_reward_id = $a['time_based_reward_id'];
$user_id = get_current_user_id();
$rewards = $a['rewards']; ?>

<div id="gamipress-time-based-rewards-popup-<?php echo $time_based_reward_id; ?>" class="gamipress-time-based-rewards-popup">

    <?php

    if( count( $rewards ) > 0 ) {

        // Pop-up content
        $popup_content = gamipress_get_post_meta( $time_based_reward_id, $prefix . 'popup_content' );

        // If not setup on time-based reward get from settings
        if( empty( $popup_content ) ) {
            $popup_content = gamipress_time_based_rewards_get_option( 'popup_content', __( '<h2>Congratulations {user}!</h2>', 'gamipress-time-based-rewards' ) . "\n"
                . __( 'You got:', 'gamipress-time-based-rewards' ) . "\n"
                . '{rewards}' );
        }

    } else {

        // No rewards content
        $popup_content = gamipress_get_post_meta( $time_based_reward_id, $prefix . 'no_rewards_content' );

        // If not setup on time-based reward get from settings
        if( empty( $popup_content ) ) {
            $popup_content = gamipress_time_based_rewards_get_option( 'no_rewards_content', __( '<h2>Sorry {user}</h2>', 'gamipress-time-based-rewards' ) . "\n"
                . __( 'There are no rewards available to earn', 'gamipress-time-based-rewards' ) );
        }
    }

    if( ! empty( $popup_content ) ) :
        // Parse tags on pop-up content
        $popup_content = gamipress_time_based_rewards_parse_popup_pattern_tags( $popup_content, $time_based_reward_id, $user_id, $rewards );

        // Execute shortcodes on pop-up content
        $popup_content = do_shortcode( $popup_content ); ?>

        <div class="gamipress-time-based-reward-popup-content"><?php echo $popup_content; ?></div>

        <?php
        /**
         * After time-based reward pop-up content
         *
         * @param int   $time_based_reward_id   The time-based reward ID
         * @param int   $user_id                The user ID
         * @param array $rewards                Rewards user got
         */
        do_action( 'gamipress_after_time_based_reward_popup_content', $time_based_reward_id, $user_id, $rewards ); ?>
    <?php endif; ?>

    <?php // Pop-up button
    $popup_button_text = gamipress_get_post_meta( $time_based_reward_id, $prefix . 'popup_button_text' );

    // If not setup on time-based reward get from settings
    if( empty( $popup_button_text ) )
        $popup_button_text = gamipress_time_based_rewards_get_option( 'popup_button_text', __( 'Ok!', 'gamipress-time-based-rewards' ) ); ?>

    <div class="gamipress-time-based-reward-popup-button-wrapper">

        <button type="button" class="gamipress-time-based-reward-popup-button"><?php echo $popup_button_text; ?></button>

    </div>

    <?php
    /**
     * After time-based reward pop-up button
     *
     * @param int   $time_based_reward_id   The time-based reward ID
     * @param int   $user_id                The user ID
     * @param array $rewards                Rewards user got
     */
    do_action( 'gamipress_after_time_based_reward_popup_button', $time_based_reward_id, $user_id, $rewards ); ?>

</div>

<div class="gamipress-time-based-rewards-popup-overlay"></div>
