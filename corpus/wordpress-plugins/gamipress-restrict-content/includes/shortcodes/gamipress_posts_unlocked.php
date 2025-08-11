<?php
/**
 * GamiPress Posts Unlocked Shortcode
 *
 * @package     GamiPress\Restrict_Content\Shortcodes\Shortcode\GamiPress_Posts_Unlocked
 * @since       1.0.8
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register the [gamipress_posts_unlocked] shortcode.
 *
 * @since 1.0.8
 */
function gamipress_register_posts_unlocked_shortcode() {

    $post_types = array_merge( array( 'all' => __( 'All', 'gamipress-restrict-content' ) ), gamipress_restrict_content_post_types() );

    gamipress_register_shortcode( 'gamipress_posts_unlocked', array(
        'name'              => __( 'Posts Unlocked', 'gamipress-restrict-content' ),
        'description'       => __( 'Renders a list of posts that user got access.', 'gamipress-restrict-content' ),
        'icon'              => 'lock',
        'group'             => 'restrict_content',
        'output_callback'   => 'gamipress_posts_unlocked_shortcode',
        'fields'      => array(
            'type' => array(
                'name'              => __( 'Post Types', 'gamipress-restrict-content' ),
                'desc'              => __( 'Types of posts to list.', 'gamipress-restrict-content' ),
                'type'              => 'advanced_select',
                'multiple'          => true,
                'classes' 	        => 'gamipress-selector',
                'attributes' 	    => array(
                    'data-placeholder' => __( 'Select the type(s)', 'gamipress-restrict-content' ),
                ),
                'options'           => $post_types,
                'default'           => 'all',
            ),
            'current_user' => array(
                'name'        => __( 'Current User', 'gamipress-restrict-content' ),
                'description' => __( 'Show only posts unlocked by the current logged in user.', 'gamipress-restrict-content' ),
                'type' 		  => 'checkbox',
                'classes' 	  => 'gamipress-switch',
            ),
            'user_id' => array(
                'name'        => __( 'User', 'gamipress-restrict-content' ),
                'description' => __( 'Show only posts unlocked by a specific user.', 'gamipress-restrict-content' ),
                'type'        => 'select',
                'classes' 	  => 'gamipress-user-selector',
                'default'     => '',
                'options_cb'  => 'gamipress_options_cb_users'
            ),
            'orderby' => array(
                'name'        => __( 'Order By', 'gamipress-restrict-content' ),
                'description' => __( 'Parameter to use for sorting.', 'gamipress-restrict-content' ),
                'type'        => 'select',
                'options'      => array(
                    'id'         		=> __( 'ID', 'gamipress-restrict-content' ),
                    'title'      		=> __( 'Title', 'gamipress-restrict-content' ),
                    'menu_order' 		=> __( 'Menu order', 'gamipress-restrict-content' ),
                    'date'       		=> __( 'Published date', 'gamipress-restrict-content' ),
                    'modified'   		=> __( 'Last modified date', 'gamipress-restrict-content' ),
                    'rand'       		=> __( 'Random', 'gamipress-restrict-content' ),
                ),
                'default'     => 'id',
            ),
            'order' => array(
                'name'        => __( 'Order', 'gamipress-restrict-content' ),
                'description' => __( 'Sort order.', 'gamipress-restrict-content' ),
                'type'        => 'select',
                'options'      => array( 'asc' => __( 'Ascending', 'gamipress-restrict-content' ), 'desc' => __( 'Descending', 'gamipress-restrict-content' ) ),
                'default'     => 'asc',
            ),
            'exclude' => array(
                'name'              => __( 'Exclude', 'gamipress-restrict-content' ),
                'description'       => __( 'Comma-separated list of specific posts IDs to exclude.', 'gamipress-restrict-content' ),
                'type'              => 'advanced_select',
                'multiple'          => true,
                'classes' 	        => 'gamipress-post-selector',
                'attributes' 	    => array(
                    'data-post-type' => implode( ',',  gamipress_restrict_content_post_types_slugs() ),
                ),
                'default'           => '',
                'options_cb'        => 'gamipress_options_cb_posts'
            ),
        ),
    ) );

}
add_action( 'init', 'gamipress_register_posts_unlocked_shortcode' );

/**
 * Posts Restricted Shortcode.
 *
 * @since  1.0.8
 *
 * @param  array    $atts       Shortcode attributes.
 * @param  string   $content    Shortcode content.
 *
 * @return string 	            HTML markup.
 */
function gamipress_posts_unlocked_shortcode( $atts = array(), $content = '' ) {

    global $wpdb, $gamipress_restrict_content_template_args;

    $atts = shortcode_atts( array(
        'type'                  => 'all',
        'current_user'          => 'no',
        'user_id'               => '0',
        'orderby'               => 'id',
        'order'                 => 'asc',
        'exclude'               => '',
    ), $atts, 'gamipress_posts_unlocked' );

    $gamipress_restrict_content_template_args = $atts;

    // Force to set current user as user ID
    if( $atts['current_user'] === 'yes' )
        $atts['user_id'] = get_current_user_id();
    else if( absint( $atts['user_id'] ) === 0 )
        $atts['user_id'] = get_current_user_id();

    // Convert $type to properly support multiple achievement types
    if ( $atts['type'] === 'all')
        $types = gamipress_restrict_content_post_types_slugs();
    else
        $types = explode( ',', $atts['type'] );

    // Initialize $exclude array
    if ( ! is_array( $atts['exclude'] ) && empty( $atts['exclude'] ) )
        $exclude = array();
    else
        $exclude = $atts['exclude'];

    // Build $exclude array
    if ( ! is_array( $exclude ) )
        $exclude = explode( ',', $exclude );

    // Query args
    $query_args = array(
        'post_type'      	=> $types,
        'orderby'        	=> $atts['orderby'],
        'order'          	=> $atts['order'],
        'post_status'    	=> 'publish',
        'posts_per_page'    => -1,
        'post__in'   	    => gamipress_restrict_content_get_user_unlocked_posts( $atts['user_id'] ),
        'post__not_in'   	=> $exclude,
        'meta_query'        => array(
            'restrict' => array(
                'key' => '_gamipress_restrict_content_restrict',
                'value'   => 'on',
            )
        )
    );

    $query = new WP_Query( $query_args );

    $gamipress_restrict_content_template_args['query_args'] = $query_args;
    $gamipress_restrict_content_template_args['query'] = $query;

    ob_start();
    gamipress_get_template_part( 'posts-unlocked' );
    $output = ob_get_clean();

    /**
     * Filter to override shortcode output
     *
     * @since 1.0.8
     *
     * @param string    $output     Final output
     * @param array     $atts       Shortcode attributes
     * @param string    $content    Shortcode content
     */
    return apply_filters( 'gamipress_posts_unlocked_shortcode_output', $output, $atts, $content );
}
