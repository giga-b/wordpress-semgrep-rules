<?php
/**
 * GamiPress Time-based Reward Shortcode
 *
 * @package     GamiPress\Time_Based_Rewards\Shortcodes\Shortcode\GamiPress_Time_Based_Reward
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_time_based_reward] shortcode.
 *
 * @since 1.0.0
 */
function gamipress_register_time_based_reward_shortcode() {
    gamipress_register_shortcode( 'gamipress_time_based_reward', array(
        'name'              => __( 'Single Time-based Reward', 'gamipress-time-based-rewards' ),
        'description'       => __( 'Render the desired time-based reward.', 'gamipress-time-based-rewards' ),
        'icon'              => 'clock',
        'group'             => 'time_based_rewards',
        'output_callback'   => 'gamipress_time_based_reward_shortcode',
        'fields'            => array(
            'id' => array(
                'name'              => __( 'Time-based Reward', 'gamipress-time-based-rewards' ),
                'description'       => __( 'The time-based reward to render.', 'gamipress-time-based-rewards' ),
                'shortcode_desc'    => __( 'The ID of the time-based reward to render.', 'gamipress-time-based-rewards' ),
                'type'              => 'select',
                'classes' 	        => 'gamipress-post-selector',
                'attributes' 	    => array(
                    'data-post-type' => 'time-based-reward',
                    'data-placeholder' => __( 'Select a time-based reward', 'gamipress-time-based-rewards' ),
                ),
                'default'           => '',
                'options_cb'        => 'gamipress_options_cb_posts'
            ),
            'title' => array(
                'name'              => __( 'Show Title', 'gamipress-time-based-rewards' ),
                'description'       => __( 'Display the time-based reward title.', 'gamipress-time-based-rewards' ),
                'type' 		        => 'checkbox',
                'classes' 	        => 'gamipress-switch',
                'default'           => 'yes'
            ),
            'link' => array(
                'name'        => __( 'Show Link', 'gamipress-time-based-rewards' ),
                'description' => __( 'Add a link on title to the time-based reward page.', 'gamipress-time-based-rewards' ),
                'type' 	        => 'checkbox',
                'classes' => 'gamipress-switch',
                'default' => 'yes'
            ),
            'thumbnail' => array(
                'name'        => __( 'Show Thumbnail', 'gamipress-time-based-rewards' ),
                'description' => __( 'Display the time-based reward featured image.', 'gamipress-time-based-rewards' ),
                'type' 	=> 'checkbox',
                'classes' => 'gamipress-switch',
                'default' => 'yes'
            ),
            'excerpt' => array(
                'name'        => __( 'Show Excerpt', 'gamipress-time-based-rewards' ),
                'description' => __( 'Display the time-based reward short description.', 'gamipress-time-based-rewards' ),
                'type' 	=> 'checkbox',
                'classes' => 'gamipress-switch',
                'default' => 'yes'
            ),
            'rewards' => array(
                'name'        => __( 'Show Rewards', 'gamipress-time-based-rewards' ),
                'description' => __( 'Display a list with the possible rewards.', 'gamipress-time-based-rewards' ),
                'type' 	=> 'checkbox',
                'classes' => 'gamipress-switch',
                'default' => 'yes'
            ),
        ),
    ) );
}
add_action( 'init', 'gamipress_register_time_based_reward_shortcode' );

/**
 * Rewards Calendar Shortcode.
 *
 * @since  1.0.0
 *
 * @param  array    $atts       Shortcode attributes.
 * @param  string   $content    Shortcode content
 *
 * @return string 	            HTML markup.
 */
function gamipress_time_based_reward_shortcode( $atts = array(), $content = '' ) {

    global $post, $gamipress_time_based_rewards_template_args;

    // Rewards calendar post vars
    $time_based_reward_id = isset( $atts['id'] ) && ! empty( $atts['id'] ) ? $atts['id'] : get_the_ID();
    $time_based_reward_post = gamipress_get_post( $time_based_reward_id );

    // Return if rewards calendar post does not exists
    if( ! $time_based_reward_post )
        return '';

    // Return if not is a rewards calendar
    if( $time_based_reward_post->post_type !== 'time-based-reward' )
        return '';

    // Return if rewards calendar was not published
    if( $time_based_reward_post->post_status !== 'publish' )
        return '';

    $atts = shortcode_atts( gamipress_time_based_reward_shortcode_defaults() , $atts, 'gamipress_time_based_reward' );

    // Initialize template args
    $gamipress_time_based_rewards_template_args = array();

    $gamipress_time_based_rewards_template_args = $atts;

    // Enqueue assets
    gamipress_time_based_rewards_enqueue_scripts();

    // On network wide active installs, we need to switch to main blog mostly for posts permalinks and thumbnails
    $blog_id = gamipress_switch_to_main_site_if_network_wide_active();

    $post = $time_based_reward_post;

    setup_postdata( $post );

    // Render the rewards calendar
    ob_start();
    gamipress_get_template_part( 'time-based-reward' );
    $output = ob_get_clean();

    wp_reset_postdata();

    // If switched to blog, return back to que current blog
    if( $blog_id !== get_current_blog_id() && is_multisite() ) {
        restore_current_blog();
    }

    /**
     * Filter to override shortcode output
     *
     * @since 1.0.0
     *
     * @param string    $output     Final output
     * @param array     $atts       Shortcode attributes
     * @param string    $content    Shortcode content
     */
    return apply_filters( 'gamipress_time_based_reward_shortcode_output', $output, $atts, $content );

}

/**
 * Single time-based reward shortcode defaults attributes values
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_time_based_reward_shortcode_defaults() {

    return apply_filters( 'gamipress_time_based_reward_shortcode_defaults', array(
        'id' 				=> get_the_ID(),
        'title' 			=> 'yes',
        'link' 				=> 'yes',
        'thumbnail' 		=> 'yes',
        'excerpt'	  		=> 'yes',
        'rewards' 	        => 'yes',
    ) );

}
