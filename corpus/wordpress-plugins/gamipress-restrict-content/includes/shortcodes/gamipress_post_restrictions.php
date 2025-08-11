<?php
/**
 * GamiPress Post Restrictions Shortcode
 *
 * @package     GamiPress\Restrict_Content\Shortcodes\Shortcode\GamiPress_Post_Restrictions
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_post_restrictions] shortcode.
 *
 * @since 1.0.2
 */
function gamipress_register_post_restrictions_shortcode() {

    gamipress_register_shortcode( 'gamipress_post_restrictions', array(
        'name'              => __( 'Post Restrictions', 'gamipress-restrict-content' ),
        'description'       => __( 'Renders a desired post restrictions and/or the button to get access to users without access.', 'gamipress-restrict-content' ),
        'icon'              => 'lock',
        'group'             => 'restrict_content',
        'output_callback'   => 'gamipress_post_restrictions_shortcode',
        'fields'      => array(
            'id' => array(
                'name'              => __( 'Post ID', 'gamipress-restrict-content' ),
                'desc'              => __( 'The ID of the post to render their restrictions and/or access button.', 'gamipress-restrict-content' ),
                'type'              => 'select',
                'classes' 	        => 'gamipress-post-selector',
                'attributes' 	    => array(
                    'data-post-type' => implode( ',',  gamipress_restrict_content_post_types_slugs() ),
                ),
                'default'           => '',
                'options_cb'        => 'gamipress_options_cb_posts'
            ),
            'after_content_text' => array(
                'name'        => __( 'Show After Content Text', 'gamipress-restrict-content' ),
                'description' => __( 'Display the configured text to be shown after replaced content.', 'gamipress-restrict-content' ),
                'type' 		  => 'checkbox',
                'classes' 	  => 'gamipress-switch',
                'default'     => 'yes'
            ),
            'access_button' => array(
                'name'        => __( 'Show Access Button', 'gamipress-restrict-content' ),
                'description' => __( 'Display the access button.', 'gamipress-restrict-content' ),
                'type' 		  => 'checkbox',
                'classes' 	  => 'gamipress-switch',
                'default'     => 'yes'
            ),
        ),
    ) );

}
add_action( 'init', 'gamipress_register_post_restrictions_shortcode' );

/**
 * Post Restrictions Shortcode.
 *
 * @since  1.0.2
 *
 * @param  array    $atts       Shortcode attributes.
 * @param  string   $content    Shortcode content.
 *
 * @return string 	            HTML markup.
 */
function gamipress_post_restrictions_shortcode( $atts = array(), $content = '' ) {

    $atts = shortcode_atts( array(
        'id'                    => '',
        'after_content_text' 	=> 'yes',
        'access_button' 	    => 'yes',
    ), $atts, 'gamipress_post_restrictions' );

    // If not id given but there is the post in url, then get from here
    if( empty( $atts['id'] ) && isset( $_GET['post'] ) )
        $atts['id'] = $_GET['post'];

    $post_id = absint( $atts['id'] );
    $post = get_post( $post_id );

    // Bail if post not exists
    if( ! $post )
        return '';

    // Bail if post not restricted
    if( ! gamipress_restrict_content_is_restricted( $post->ID ) )
        return '';

    // Bail if user is granted
    if( gamipress_restrict_content_is_user_granted( $post->ID ) )
        return '';

    $output = '';

    // After content text
    if( $atts['after_content_text'] === 'yes' ) {

        if( is_user_logged_in() ) {
            // Get the post after content replacement text
            $replacement_text = gamipress_restrict_content_get_after_content_replacement_text( $post->ID );
        } else {
            // Get the post after content replacement text for guests
            $replacement_text = gamipress_restrict_content_get_guest_after_content_replacement_text( $post->ID );
        }

        /**
         * Filter to dynamically override after content replacement text
         *
         * @since 1.0.0
         *
         * @var string  $replacement_text   Replacement text
         * @var WP_Post $post               Current Post
         */
        $replacement_text = apply_filters( 'gamipress_restrict_content_after_content_replacement_text', $replacement_text, $post );

        if( ! empty( $replacement_text ) ) {

            // Parse tags
            $replacement_text = gamipress_restrict_content_parse_post_pattern( $replacement_text, $post->ID );

            // Append replacement text to content
            $output .= $replacement_text;

        }

    }

    // Access button
    if( $atts['access_button'] === 'yes' ) {

        // Adds the unlock with points markup
        $output .= gamipress_restrict_content_unlock_post_with_points_markup( $post->ID );

    }

    /**
     * Filter to override shortcode output
     *
     * @since 1.0.8
     *
     * @param string    $output     Final output
     * @param array     $atts       Shortcode attributes
     * @param string    $content    Shortcode content
     */
    return apply_filters( 'gamipress_post_restrictions_shortcode', $output, $atts, $content );
}
