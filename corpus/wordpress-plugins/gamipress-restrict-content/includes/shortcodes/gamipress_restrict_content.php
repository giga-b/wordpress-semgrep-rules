<?php
/**
 * GamiPress Restrict Content Shortcode
 *
 * @package     GamiPress\Restrict_Content\Shortcodes\Shortcode\GamiPress_Restrict_Content
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_restrict_content] shortcode.
 *
 * @since 1.0.2
 */
function gamipress_register_restrict_content_shortcode() {

    gamipress_register_shortcode( 'gamipress_restrict_content', array(
        'name'              => __( 'Restrict Content', 'gamipress-restrict-content' ),
        'description'       => __( 'Restrict a portion of content.', 'gamipress-restrict-content' ),
        'icon'              => 'lock',
        'group'             => 'restrict_content',
        'output_callback'   => 'gamipress_restrict_content_shortcode',
        'fields'      => array(
            'id' => array(
                'name'          => __( 'ID', 'gamipress-restrict-content' ),
                'desc'          => __( 'Identifier for this portion of content.', 'gamipress-restrict-content' ),
                'type'          => 'text',
                'default'       => '',
            ),
            'unlock_by' => array(
                'name'        => __( 'Unlock By', 'gamipress-restrict-content' ),
                'description' => __( 'Choose how users can get access to this portion of content.', 'gamipress-restrict-content' ),
                'type' 	=> 'select',
                'options' => array(
                    'expend_points'         => __( 'Expending points', 'gamipress-restrict-content' ),
                    'points_balance'        => __( 'Reaching a points balance', 'gamipress-restrict-content' ),
                    'achievement'           => __( 'Unlocking achievements', 'gamipress-restrict-content' ),
                    'achievement_type'      => __( 'Unlocking any achievements of type', 'gamipress-restrict-content' ),
                    'all_achievement_type'  => __( 'Unlocking all achievements of type', 'gamipress-restrict-content' ),
                    'rank'                  => __( 'Reaching a rank', 'gamipress-restrict-content' ),
                ),
                'default' => 'expend_points'
            ),

            // Points fields

            'points' => array(
                'name'          => __( 'Points Amount', 'gamipress-restrict-content' ),
                'description'   => __( 'Points amount required to unlock this portion of content.', 'gamipress-restrict-content' ),
                'type' 	        => 'text',
                'default'       => '100'
            ),
            'points_type' => array(
                'name'          => __( 'Points Type', 'gamipress-restrict-content' ),
                'description'   => __( 'The points amount points type required to unlock this portion of content.', 'gamipress-restrict-content' ),
                'type' 	        => 'select',
                'options_cb'    => 'gamipress_options_cb_points_types',
                'option_all'    => false,
                'default'       => ''
            ),

            // Achievement fields

            'achievement' => array(
                'name'          => __( 'Achievement(s)', 'gamipress-restrict-content' ),
                'description'   => __( 'The achievement(s) required to unlock this portion of content.', 'gamipress-restrict-content' ),
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
                'description'   => __( 'The achievement type required to unlock this portion of content.', 'gamipress-restrict-content' ),
                'type' 	=> 'select',
                'options_cb'    => 'gamipress_options_cb_achievement_types',
                'option_all'    => false,
                'default'       => ''
            ),
            'achievement_count' => array(
                'name'          => __( 'Achievements required', 'gamipress-restrict-content' ),
                'description'   => __( 'Number of achievements required to unlock this portion of content.', 'gamipress-restrict-content' ),
                'type' 	        => 'text',
                'attributes' 	=> array( 'type' => 'number' ),
                'default'       => '1'
            ),

            // Rank fields

            'rank' => array(
                'name'          => __( 'Rank(s)', 'gamipress-restrict-content' ),
                'description'   => __( 'The rank(s) required to unlock this portion of content.', 'gamipress-restrict-content' ),
                'type'          => 'advanced_select',
                'multiple'      => true,
                'classes' 	    => 'gamipress-post-selector',
                'attributes' 	=> array(
                    'data-post-type' => implode( ',',  gamipress_get_rank_types_slugs() ),
                    'data-placeholder' => __( 'Select rank', 'gamipress-restrict-content' ),
                ),
                'options_cb'    => 'gamipress_options_cb_posts',
                'default'       => ''
            ),

            // Grant fields

            'granted_roles' => array(
                'name'          => __( 'Granted Roles', 'gamipress-restrict-content' ),
                'desc'          => __( 'Manually grant access to this portion of content to users by role.', 'gamipress-restrict-content' ),
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
                'desc'          => __( 'Manually grant access to this portion of content to the users you want.', 'gamipress-restrict-content' ),
                'type'          => 'advanced_select',
                'multiple'      => true,
                'classes' 	    => 'gamipress-user-selector',
                'default'       => '',
                'options_cb'    => 'gamipress_options_cb_users'
            ),

            // Message fields

            'message' => array(
                'name'          => __( 'Message', 'gamipress-restrict-content' ),
                'desc'          => __( 'Text that is shown to users that haven\'t unlocked this portion of content. Available tags:', 'gamipress-restrict-content' )
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
add_action( 'init', 'gamipress_register_restrict_content_shortcode' );

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
function gamipress_restrict_content_shortcode( $atts = array(), $content = '' ) {

    global $post, $gamipress_restrict_content_shortcode_ids;

    // Initialize shortcode IDs
    if( ! is_array( $gamipress_restrict_content_shortcode_ids ) ) {
        $gamipress_restrict_content_shortcode_ids = array();
    }

    $atts = shortcode_atts( array(
        'id'                => '',
        'unlock_by'         => 'expend_points',

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
    ), $atts, 'gamipress_restrict_content' );

    // If not ID provided, generate one
    if( empty( $atts['id'] ) )
        $atts['id'] = gamipress_restrict_content_generate_shortcode_id();

    // Support for blocks
    if( empty( $content ) )
        $content = $atts['content'];

    // If not guest message provided, fallback to message field
    if( empty( $atts['guest_message'] ) )
        $atts['guest_message'] = $atts['message'];

    // Add the content id to the already placed ids
    $gamipress_restrict_content_shortcode_ids[] = $atts['id'];

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

    // Update the content price on post
    if( $atts['unlock_by'] === 'expend_points' )
        gamipress_restrict_content_update_post_content_price( $post->ID, $atts['id'], $atts['points'], $atts['points_type'] );

    // Check if user has granted to this content
    if( gamipress_restrict_content_is_user_granted_to_content( $atts['id'], $user_id, $post->ID, $atts ) ) {

        // if is granted, the output will be the content itself
        $output = $content;

        /**
         * Filter to override shortcode output when user is granted
         *
         * @since 1.0.5
         *
         * @param string    $output     Output when user is granted
         * @param array     $atts       Shortcode attributes
         * @param string    $content    Shortcode content
         */
        $output = apply_filters( 'gamipress_restrict_content_shortcode_granted_output', $output, $atts, $content );

    } else {

        // Setup the message based on logged in status
        $message = ( is_user_logged_in() ? $atts['message'] : $atts['guest_message'] );

        $output = gamipress_restrict_content_parse_content_pattern( $message, $post->ID, $user_id, $atts );

        if( $atts['unlock_by'] === 'expend_points' ) {
            // Append the points markup
            $output .= gamipress_restrict_content_unlock_content_with_points_markup( $atts['id'], $user_id, $post->ID, $atts );
        }

        /**
         * Filter to override shortcode output when user is restricted
         *
         * @since 1.0.5
         *
         * @param string    $output     Output when user is restricted
         * @param array     $atts       Shortcode attributes
         * @param string    $content    Shortcode content
         */
        $output = apply_filters( 'gamipress_restrict_content_shortcode_restricted_output', $output, $atts, $content );

    }

    // Execute shortcodes in content
    $output = do_shortcode( $output );

    /**
     * Filter to override shortcode output
     *
     * @since 1.0.5
     *
     * @param string    $output     Final output
     * @param array     $atts       Shortcode attributes
     * @param string    $content    Shortcode content
     */
    return apply_filters( 'gamipress_restrict_content_shortcode_output', $output, $atts, $content );
}

/**
 * Generate an ID for content that not has one.
 *
 * @since  1.0.2
 *
 * @return string
 */
function gamipress_restrict_content_generate_shortcode_id() {

    global $post, $gamipress_restrict_content_shortcode_ids;

    $id_pattern = 'content-' . $post->ID . '-';
    $index = 1;

    // First ID
    $id = $id_pattern . $index;

    while( in_array( $id, $gamipress_restrict_content_shortcode_ids ) ) {

        $index++;

        $id = $id_pattern . $index;
    }

    return $id;

}
