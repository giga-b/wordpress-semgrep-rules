<?php
/**
 * GamiPress Hide Content If Shortcode
 *
 * @package     GamiPress\Restrict_Content\Shortcodes\Shortcode\GamiPress_Hide_Content_If
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_hide_content_if] shortcode.
 *
 * @since 1.0.2
 */
function gamipress_register_hide_content_if_shortcode() {

    gamipress_register_shortcode( 'gamipress_hide_content_if', array(
        'name'              => __( 'Hide Content If', 'gamipress-restrict-content' ),
        'description'       => __( 'Hide a portion of content if user meets a specific condition.', 'gamipress-restrict-content' ),
        'icon'              => 'lock',
        'group'             => 'restrict_content',
        'output_callback'   => 'gamipress_hide_content_if_shortcode',
        'fields'      => array(

            'condition' => array(
                'name'        => __( 'Condition', 'gamipress-restrict-content' ),
                'description' => __( 'Choose the condition that user needs to meet to hide the portion of content.', 'gamipress-restrict-content' ),
                'type' 	=> 'select',
                'options' => gamipress_restrict_content_get_if_conditions(),
                'default' => 'points_greater'
            ),

            // Points fields

            'points' => array(
                'name'          => __( 'Points Amount', 'gamipress-restrict-content' ),
                'description'   => __( 'Points amount required to hide the portion of content.', 'gamipress-restrict-content' ),
                'type' 	        => 'text',
                'default'       => '100'
            ),
            'points_type' => array(
                'name'          => __( 'Points Type', 'gamipress-restrict-content' ),
                'description'   => __( 'The points amount points type required to hide the portion of content.', 'gamipress-restrict-content' ),
                'type' 	        => 'select',
                'options_cb'    => 'gamipress_options_cb_points_types',
                'option_all'    => false,
                'default'       => ''
            ),

            // Achievement fields

            'achievement' => array(
                'name'          => __( 'Achievement(s)', 'gamipress-restrict-content' ),
                'description'   => __( 'The achievement(s) required to hide the portion of content.', 'gamipress-restrict-content' ),
                'type'          => 'advanced_select',
                'multiple'      => true,
                'classes' 	    => 'gamipress-post-selector',
                'attributes' 	=> array(
                    'data-post-type' => implode( ',',  gamipress_get_achievement_types_slugs() ),
                    'data-placeholder' => __( 'Select achievements', 'gamipress-restrict-content' ),
                ),
                'options_cb'    => 'gamipress_options_cb_posts',
                'default'       => ''
            ),
            'achievement_type' => array(
                'name'          => __( 'Achievement Type', 'gamipress-restrict-content' ),
                'description'   => __( 'The achievement type required to hide the portion of content.', 'gamipress-restrict-content' ),
                'type' 	=> 'select',
                'options_cb'    => 'gamipress_options_cb_achievement_types',
                'option_all'    => false,
                'default'       => ''
            ),
            'achievement_count' => array(
                'name'          => __( 'Achievements required', 'gamipress-restrict-content' ),
                'description'   => __( 'Number of achievements required to hide the portion of content.', 'gamipress-restrict-content' ),
                'type' 	        => 'text',
                'attributes' 	=> array( 'type' => 'number' ),
                'default'       => '1'
            ),

            // Rank fields

            'rank' => array(
                'name'          => __( 'Rank(s)', 'gamipress-restrict-content' ),
                'description'   => __( 'The rank(s) required to hide the portion of content.', 'gamipress-restrict-content' ),
                'type'          => 'advanced_select',
                'multiple'      => true,
                'classes' 	    => 'gamipress-post-selector',
                'attributes' 	=> array(
                    'data-post-type' => implode( ',',  gamipress_get_rank_types_slugs() ),
                    'data-placeholder' => __( 'Select ranks', 'gamipress-restrict-content' ),
                ),
                'options_cb'    => 'gamipress_options_cb_posts',
                'default'       => ''
            ),

            // Grant fields

            'granted_roles' => array(
                'name'          => __( 'Granted Roles', 'gamipress-restrict-content' ),
                'desc'          => __( 'Manually grant access to this content to users by role.', 'gamipress-restrict-content' ),
                'type'          => 'advanced_select',
                'multiple'      => true,
                'classes' 	    => 'gamipress-selector',
                'attributes' 	=> array(
                    'data-placeholder' => __( 'Select Roles', 'gamipress-restrict-content' ),
                ),
                'default'       => '',
                'options_cb'    => 'gamipress_restrict_content_get_roles_options'
            ),
            'granted_users' => array(
                'name'          => __( 'Granted Users', 'gamipress-restrict-content' ),
                'desc'          => __( 'Manually grant access to this content to the users you want.', 'gamipress-restrict-content' ),
                'type'          => 'advanced_select',
                'multiple'      => true,
                'classes' 	    => 'gamipress-user-selector',
                'default'       => '',
                'options_cb'    => 'gamipress_options_cb_users'
            ),

            // Message fields

            'message' => array(
                'name'          => __( 'Message', 'gamipress-restrict-content' ),
                'desc'          => __( 'Text that is shown to users that meets the condition. Available tags:', 'gamipress-restrict-content' )
                    . gamipress_restrict_content_get_pattern_tags_html( 'content' ),
                'type'          => 'textarea',
                'default'       => '',
            ),
            'guest_message' => array(
                'name'          => __( 'Message For Guests', 'gamipress-restrict-content' ),
                'desc'          => __( 'Text that is shown to non logged in users. Available tags:', 'gamipress-restrict-content' )
                    . gamipress_restrict_content_get_pattern_tags_html( 'guest-content' ),
                'type'          => 'textarea',
                'default'       => '',
            ),
        ),
    ) );

}
add_action( 'init', 'gamipress_register_hide_content_if_shortcode' );

/**
 * Restrict Content Shortcode.
 *
 * @since  1.0.2
 *
 * @param  array    $atts       Shortcode attributes.
 * @param  string   $content    Shortcode content.
 *
 * @return string 	            HTML markup.
 */
function gamipress_hide_content_if_shortcode( $atts = array(), $content = '' ) {

    $atts = shortcode_atts( array(
        'condition'         => 'points_greater',

        'points'            => '0',
        'points_type'       => '',

        'achievement'       => '',
        'achievement_type'  => '',
        'achievement_count' => '',

        'rank'              => '0',

        'granted_roles'     => '',
        'granted_users'     => '',

        'message'           => '',
        'guest_message'     => '',

        // Support for blocks
        'content'           => '',
    ), $atts, 'gamipress_hide_content_if' );

    // Support for blocks
    if( empty( $content ) )
        $content = $atts['content'];

    // If not guest message provided, fallback to message field
    if( empty( $atts['guest_message'] ) )
        $atts['guest_message'] = $atts['message'];

    // Ensure points as integer
    $atts['points'] = absint( $atts['points'] );

    // Turn comma-separated user roles list to an array
    if( ! empty( $atts['granted_roles'] ) )
        $atts['granted_roles'] = explode( ',', $atts['granted_roles'] );
    else
        $atts['granted_roles'] = array();

    // Turn comma-separated users list to an array
    if( ! empty( $atts['granted_users'] ) )
        $atts['granted_users'] = explode( ',', $atts['granted_users'] );
    else
        $atts['granted_users'] = array();

    // Grab the current logged in user ID
    $user_id = get_current_user_id();

    // Check if user meets the condition
    if( gamipress_restrict_content_user_meets_condition( $atts['condition'], $user_id, $atts ) ) {

        // Setup the message based on logged in status
        $message = ( is_user_logged_in() ? $atts['message'] : $atts['guest_message'] );

        $output = gamipress_restrict_content_parse_content_pattern( $message, null, $user_id, $atts );

        /**
         * Filter to override shortcode output when user meets the conditions
         *
         * @since 1.1.4
         *
         * @param string    $output     Output when user is granted
         * @param array     $atts       Shortcode attributes
         * @param string    $content    Shortcode content
         */
        $output = apply_filters( 'gamipress_hide_content_if_shortcode_granted_output', $output, $atts, $content );

    } else {

        // if user doesn't meets the condition, the output will be the portion of content content
        $output = $content;

        /**
         * Filter to override shortcode output when user is restricted
         *
         * @since 1.1.4
         *
         * @param string    $output     Output when user is restricted
         * @param array     $atts       Shortcode attributes
         * @param string    $content    Shortcode content
         */
        $output = apply_filters( 'gamipress_hide_content_if_shortcode_restricted_output', $output, $atts, $content );

    }

    // Execute shortcodes in content
    $output = do_shortcode( $output );

    /**
     * Filter to override shortcode output
     *
     * @since 1.1.4
     *
     * @param string    $output     Final output
     * @param array     $atts       Shortcode attributes
     * @param string    $content    Shortcode content
     */
    return apply_filters( 'gamipress_hide_content_if_shortcode_output', $output, $atts, $content );
}
