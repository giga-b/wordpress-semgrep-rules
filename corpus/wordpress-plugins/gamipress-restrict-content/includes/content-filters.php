<?php
/**
 * Content Filters
 *
 * @package     GamiPress\Restrict_Content\Content_Filters
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check if user has access to queried post
 *
 * @since 1.0.0
 */
function gamipress_restrict_content_check_access() {

    global $post;

    $post_types = gamipress_restrict_content_post_types_slugs();

    if ( $post && in_array( $post->post_type, $post_types ) && is_singular( $post_types )       // Is an allowed post type
        && gamipress_restrict_content_is_restricted_access( $post->ID )                         // Is access restricted
        && ! gamipress_restrict_content_is_user_granted( $post->ID ) ) {                        // User is not granted

        $redirect_page = gamipress_restrict_content_get_redirect_page( $post->ID );

        if( $redirect_page === 0 ) {
            // WordPress error message
            wp_die( __( 'Access to this page restricted!', 'gamipress-restrict-content' ), 'Error' );
        } else {
            $redirect_url = get_permalink( $redirect_page );

            $redirect_url = add_query_arg( 'post', $post->ID, $redirect_url );

            // Custom page
            wp_redirect( $redirect_url );
            exit;
        }
    }

}
add_action( 'template_redirect', 'gamipress_restrict_content_check_access' );

/**
 * Pass queried post restrictions (content, links and images)
 *
 * @since 1.0.0
 *
 * @param string $content
 *
 * @return string
 */
function gamipress_restrict_content_pass_restrictions( $content ) {

    global $post;

    $post_types = gamipress_restrict_content_post_types_slugs();

    if ( $post && in_array( $post->post_type, $post_types ) && is_singular( $post_types )       // Is an allowed post type
        && is_main_query() && ! post_password_required()                                        // Is main query and not pass protected
        && gamipress_restrict_content_is_restricted( $post->ID )                                // Is restricted
        && ! gamipress_restrict_content_is_user_granted( $post->ID ) ) {                        // User is not granted

        // Restrict content
        if( gamipress_restrict_content_is_restricted_content( $post->ID ) ) {

            // First decide the content to show on restricted content page
            $content_replacement = gamipress_restrict_content_get_content_replacement( $post->ID );

            if( $content_replacement == 'read-more' ) {

                // Trim until read more tag
                $content_array = get_extended ( $post->post_content );
                
                if( isset( $content_array['main'] ) ) {
                    $content = $content_array['main'];
                }

            } else if( $content_replacement == 'excerpt' ) {

                // Excerpt instead of content
                $content = get_post_field( 'post_excerpt', $post->ID );

            } else if( $content_replacement == 'content' ) {

                // Trim content a static number of characters
                $content = wp_trim_words( $content, gamipress_restrict_content_get_content_length() );
                
            } else if( $content_replacement == 'full-content' ) {

                // Replace the full content
                $content = '';

            }

        }

        // Restrict links
        if( gamipress_restrict_content_is_restricted_links( $post->ID ) ) {

            // Setup links replacement text based on logged in status
            if( is_user_logged_in() ) {
                $links_replacement_text = gamipress_restrict_content_get_links_replacement_text( $post->ID );
            } else {
                $links_replacement_text = gamipress_restrict_content_get_guest_links_replacement_text( $post->ID );
            }

            /**
             * Filter to dynamically override links replacement text
             *
             * @since 1.0.0
             *
             * @var string  $links_replacement_text     Replacement text
             * @var WP_Post $post                       Current Post
             */
            $links_replacement_text = apply_filters( 'gamipress_restrict_content_links_replacement_text', $links_replacement_text, $post );
            
            if( ! empty( $links_replacement_text ) ) {

                // Parse tags
                $links_replacement_text = gamipress_restrict_content_parse_post_pattern( $links_replacement_text, $post->ID );

                // Replace all links with the desired replacement text
                $content = preg_replace('/<a.*?>.*?<\/a>/', $links_replacement_text, $content);

            }

        }

        // Restrict images
        if( gamipress_restrict_content_is_restricted_images( $post->ID ) ) {

            // Setup images replacement text based on logged in status
            if( is_user_logged_in() ) {
                $images_replacement_text = gamipress_restrict_content_get_images_replacement_text( $post->ID );
            } else {
                $images_replacement_text = gamipress_restrict_content_get_guest_images_replacement_text( $post->ID );
            }

            /**
             * Filter to dynamically override images replacement text
             *
             * @since 1.0.0
             *
             * @var string  $images_replacement_text    Replacement text
             * @var WP_Post $post                       Current Post
             */
            $images_replacement_text = apply_filters( 'gamipress_restrict_content_images_replacement_text', $images_replacement_text, $post );

            if( ! empty( $images_replacement_text ) ) {

                // Parse tags
                $images_replacement_text = gamipress_restrict_content_parse_post_pattern( $images_replacement_text, $post->ID );

                // Replace all images with the desired replacement text
                $content = preg_replace('/<img.*?>/', $images_replacement_text, $content);

            }

        }

        // After restricted content
        if( gamipress_restrict_content_is_restricted_content( $post->ID ) ) {

            // Content limiter (to meet where content has limit the output)
            $content .= '<div class="gamipress-restrict-content-limiter"></div>';

            // Append after_content_replacement_text option
            if( is_user_logged_in() ) {
                $after_content_replacement_text = gamipress_restrict_content_get_after_content_replacement_text( $post->ID );
            } else {
                $after_content_replacement_text = gamipress_restrict_content_get_guest_after_content_replacement_text( $post->ID );
            }

            /**
             * Filter to dynamically override after content replacement text
             *
             * @since 1.0.0
             *
             * @var string  $after_content_replacement_text Replacement text
             * @var WP_Post $post                           Current Post
             */
            $after_content_replacement_text = apply_filters( 'gamipress_restrict_content_after_content_replacement_text', $after_content_replacement_text, $post );

            if( ! empty( $after_content_replacement_text ) ) {

                // Parse tags
                $after_content_replacement_text = gamipress_restrict_content_parse_post_pattern( $after_content_replacement_text, $post->ID );

                // Append replacement text to content
                $content .= $after_content_replacement_text;

            }

        }

        // Adds the unlock with points markup
        $content .= gamipress_restrict_content_unlock_post_with_points_markup( $post->ID );

        $content = gamipress_restrict_content_apply_content_filters( $content );

    }

    return $content;
}
add_filter( 'the_content', 'gamipress_restrict_content_pass_restrictions', 99999 );

/**
 * Apply active WordPress filters to the given content
 *
 * @since 1.0.2
 *
 * @param string $content
 *
 * @return string
 */
function gamipress_restrict_content_apply_content_filters( $content = '' ) {

    /**
     * Filter available to exclude the filters to pass to the content
     *
     * @since 1.2.2
     *
     * @param array $excluded_filters
     *
     * @return array
     */
    $excluded_filters = apply_filters( 'gamipress_restrict_content_excluded_content_filters', array() );

    if ( has_filter( 'the_content', 'wptexturize' ) && ! in_array( 'wptexturize', $excluded_filters ) ) {
        $content = wptexturize( $content );
    }

    if ( has_filter( 'the_content', 'convert_smilies' ) && ! in_array( 'convert_smilies', $excluded_filters ) ) {
        $content = convert_smilies( $content );
    }

    if ( has_filter( 'the_content', 'convert_chars' ) && ! in_array( 'convert_chars', $excluded_filters ) ) {
        $content = convert_chars( $content );
    }

    if ( has_filter( 'the_content', 'wpautop' ) && ! in_array( 'wpautop', $excluded_filters ) ) {
        $content = wpautop( $content );
    }

    if ( has_filter( 'the_content', 'shortcode_unautop' ) && ! in_array( 'shortcode_unautop', $excluded_filters ) ) {
        $content = shortcode_unautop( $content );
    }

    if ( has_filter( 'the_content', 'prepend_attachment' ) && ! in_array( 'prepend_attachment', $excluded_filters ) ) {
        $content = prepend_attachment( $content );
    }

    if ( has_filter( 'the_content', 'capital_P_dangit' ) && ! in_array( 'capital_P_dangit', $excluded_filters ) ) {
        $content = capital_P_dangit( $content );
    }

    if ( ! in_array( 'do_shortcode', $excluded_filters ) ) {
        $content = do_shortcode( $content );
    }

    return $content;

}

function gamipress_restrict_content_apply_featured_image_filter( $html, $post_id ) {

    global $post;

    $post_types = gamipress_restrict_content_post_types_slugs();

    if ( $post && in_array( $post->post_type, $post_types ) && is_singular( $post_types )       // Is an allowed post type
        && is_main_query() && ! post_password_required()                                        // Is main query and not pass protected
        && gamipress_restrict_content_is_restricted( $post->ID )                                // Is restricted
        && ! gamipress_restrict_content_is_user_granted( $post->ID ) ) {

        // Restrict featured image
        if( gamipress_restrict_content_is_restricted_featured_image( $post->ID ) ) {
            $html = '';
        }
            
        }
    return $html;
            
}
add_filter( 'post_thumbnail_html', 'gamipress_restrict_content_apply_featured_image_filter', 99999, 2 );