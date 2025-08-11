<?php
/**
 * Content Filters
 *
 * @package     GamiPress\Time_Based_Rewards\Content_Filters
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Filter rewards calendar content to add the calendar table
 *
 * @since  1.0.0
 *
 * @param  string $content The page content
 *
 * @return string          The page content after reformat
 */
function gamipress_time_based_rewards_reformat_entries( $content ) {

    // Filter, but only on the main loop!
    if ( ! gamipress_time_based_rewards_is_main_loop( get_the_ID() ) )
        return $content;

    // now that we're where we want to be, tell the filters to stop removing
    $GLOBALS['gamipress_time_based_rewards_reformat_content'] = true;

    global $gamipress_time_based_rewards_template_args;

    // Initialize template args global
    $gamipress_time_based_rewards_template_args = array();

    $gamipress_time_based_rewards_template_args['original_content'] = $content;

    ob_start();

    gamipress_get_template_part( 'single-time-based-reward' );

    $new_content = ob_get_clean();

    // Ok, we're done reformatting
    $GLOBALS['gamipress_time_based_rewards_reformat_content'] = false;

    return $new_content;
}
add_filter( 'the_content', 'gamipress_time_based_rewards_reformat_entries', 9 );

/**
 * Helper function tests that we're in the main loop
 *
 * @since  1.0.0
 * @param  bool|integer $id The page id
 * @return boolean     A boolean determining if the function is in the main loop
 */
function gamipress_time_based_rewards_is_main_loop( $id = false ) {

    // Only run our filters on time-based reward singular pages
    if ( is_admin() || ! is_singular( 'time-based-reward' ) )
        return false;
    // w/o id, we're only checking template context
    if ( ! $id )
        return true;

    // Checks several variables to be sure we're in the main loop (and won't effect things like post pagination titles)
    return ( ( $GLOBALS['post']->ID == $id ) && in_the_loop() && empty( $GLOBALS['gamipress_time_based_rewards_reformat_content'] ) );

}

/**
 * Generate markup for a time-based reward's rewards output
 *
 * @since  1.0.0
 *
 * @param  int 	    $time_based_reward_id   The given time-based reward's ID
 * @param  array 	$template_args          The given template parameters
 *
 * @return string                  The HTML markup for the rewards output
 */
function gamipress_time_based_rewards_get_rewards_markup( $time_based_reward_id, $template_args ) {

    $prefix = '_gamipress_time_based_rewards_';
    $rewards = gamipress_get_post_meta( $time_based_reward_id, $prefix . 'rewards' );
    $thousands_sep = gamipress_time_based_rewards_get_option( 'thousands_separator', ',' );

    ob_start(); ?>

    <h5 class="gamipress-time-based-reward-rewards-title"><?php _e( 'Rewards', 'gamipress-time-based-rewards' ); ?></h5>

    <ul class="gamipress-time-based-reward-rewards">

        <?php foreach( $rewards as $reward ) :

            $min = absint( $reward['min'] );
            $max = absint( $reward['max'] );

            // Check for ranks
            if( $min === 0 && $max === 0 && in_array( $reward['post_type'], gamipress_get_rank_types_slugs() ) ) {
                $min = 1;
                $max = 1;
            }

            // Skip not setup rewards
            if( $min === 0 && $max === 0 ) continue;

            $min = number_format( $min, 0, '.', $thousands_sep );
            $max = number_format( $max, 0, '.', $thousands_sep );

            if( $min === $max )
                $quantity = $max;
            else
                $quantity = $min . '-' . $max;
            ?>

            <li class="gamipress-time-based-reward-reward">
                <?php echo gamipress_time_based_rewards_parse_pattern_tags( $reward['label'], $quantity, $reward ); ?>
                <?php if( isset( $reward['always'] ) && $reward['always'] === 'on' ) : ?>
                    <?php _e( '(always included)', 'gamipress-time-based-rewards' ); ?>
                <?php endif; ?>
            </li>

        <?php endforeach; ?>

    </ul><!-- .gamipress-time-based-reward-rewards -->

    <?php $rewards_html = ob_get_clean();

    /**
     * Filters the rewards HTML markup
     *
     * @since 1.0.0
     *
     * @param  string 	$rewards_html           The HTML markup for the rewards output
     * @param  int 	    $time_based_reward_id   The given time-based reward's ID
     * @param  array 	$template_args          The given template parameters
     *
     * @return string
     */
    return apply_filters( 'gamipress_time_based_rewards_get_rewards_markup', $rewards_html, $time_based_reward_id, $template_args );

}