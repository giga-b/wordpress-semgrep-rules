<?php
/**
 * Functions
 *
 * @package     GamiPress\Restrict_Content\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Return an array of post types that Restrict Content is allowed to apply restrictions
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_restrict_content_post_types() {

    $post_types = array();

    $wp_post_types = get_post_types( array( 'public' => true ), 'objects' );

    // Excluded post types
    $excluded_post_types = array();

    /**
     * Filter post types to exclude to be restricted
     *
     * @since 1.0.0
     *
     * @pararm array $excluded_post_types By default, all public post types
     *
     * @return array
     */
    $excluded_post_types = apply_filters( 'gamipress_restrict_content_excluded_post_types', $excluded_post_types );

    // Loop public post types
    foreach( $wp_post_types as $post_type ) {

        // Skip excluded post types
        if( in_array( $post_type->name, $excluded_post_types ) ) {
            continue;
        }

        // Setup an array like: 'post' => 'Posts'
        $post_types[$post_type->name] = $post_type->label;
    }

    /**
     * Filter post types that can be restricted
     *
     * @since 1.0.0
     *
     * @pararm array $post_types
     *
     * @return array
     */
    return apply_filters( 'gamipress_restrict_content_post_types', $post_types );

}

/**
 * Return an array of post types slugs where GamiPress Restrict Content is allowed to apply restrictions
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_restrict_content_post_types_slugs() {

    $post_types = gamipress_restrict_content_post_types();

    return array_keys( $post_types );
}

/**
 * Return an array of post types that Restrict Content is allowed to redirect (used for the redirect page option)
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_restrict_content_redirect_post_types() {

    $post_types = array();

    $wp_post_types = get_post_types( array( 'public' => true ), 'objects' );

    // Excluded post types
    $excluded_post_types = array(
        'attachment'
    );

    /**
     * Filter post types to exclude to be used for the redirect option
     *
     * @since 1.0.0
     *
     * @pararm array $excluded_post_types By default, all public post types except attachments
     *
     * @return array
     */
    $excluded_post_types = apply_filters( 'gamipress_restrict_content_redirect_excluded_post_types', $excluded_post_types );

    // Loop public post types
    foreach( $wp_post_types as $post_type ) {

        // Skip excluded post types
        if( in_array( $post_type->name, $excluded_post_types ) ) {
            continue;
        }

        // Setup an array like: 'post' => 'Posts'
        $post_types[$post_type->name] = $post_type->label;
    }

    /**
     * Filter post types that can used for the redirect option
     *
     * @since 1.0.0
     *
     * @pararm array $post_types
     *
     * @return array
     */
    return apply_filters( 'gamipress_restrict_content_redirect_post_types', $post_types );

}

/**
 * Return an array of post types slugs where GamiPress Restrict Content is allowed to apply restrictions
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_restrict_content_redirect_post_types_slugs() {

    $post_types = gamipress_restrict_content_redirect_post_types();

    return array_keys( $post_types );
}

/**
 * Return an array of user roles as options to use on fields
 *
 * @since 1.0.2
 *
 * @return array
 */
function gamipress_restrict_content_get_roles_options() {

    $options = array();

    $editable_roles = array_reverse( get_editable_roles() );

    foreach ( $editable_roles as $role => $details ) {

        // Skip administrator roles
        if( $role === 'administrator' ) {
            continue;
        }

        $options[$role] = translate_user_role( $details['name'] );

    }

    return $options;
}

/**
 * Helper function to easily get a GamiPress Restrict Content meta
 *
 * @since 1.0.0
 *
 * @param integer   $post_id
 * @param string    $meta_key
 * @param bool      $single
 * @return mixed
 */
function gamipress_restrict_content_get_meta( $post_id, $meta_key, $single = true ) {

    if( $post_id === null ) {
        $post_id = get_the_ID();
    }

    $prefix = '_gamipress_restrict_content_';

    return get_post_meta( $post_id, $prefix . $meta_key, $single );

}

/**
 * Return true if post is restricted and has restrictions
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return bool
 */
function gamipress_restrict_content_is_restricted( $post_id = null ) {

    $restricted = gamipress_restrict_content_get_meta( $post_id, 'restrict' );
    $unlock_by = gamipress_restrict_content_get_unlock_by( $post_id );

    // If unlock by is setup to complete restrictions, then check restrictions to set if post is correctly restricted
    if( $unlock_by === 'complete-restrictions' ) {

        $restrictions = gamipress_restrict_content_get_meta( $post_id, 'restrictions' );

        // Post restricted if enabled and has restrictions
        return $restricted === 'on' && count( $restrictions ) > 0;

    }

    // Post restricted if enabled
    return $restricted === 'on';
}

/**
 * Return true if post is restricted and has access restricted
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return bool
 */
function gamipress_restrict_content_is_restricted_access( $post_id = null ) {

    if( ! gamipress_restrict_content_is_restricted( $post_id ) ) {
        return false;
    }

    $restricted = gamipress_restrict_content_get_meta( $post_id, 'restrict_access' );

    return $restricted === 'on';

}

/**
 * Return true if post is restricted and has content restricted
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return bool
 */
function gamipress_restrict_content_is_restricted_content( $post_id = null ) {

    if( ! gamipress_restrict_content_is_restricted( $post_id ) ) {
        return false;
    }

    $restricted = gamipress_restrict_content_get_meta( $post_id, 'restrict_content' );

    return $restricted === 'on';

}

/**
 * Return true if post is restricted and has links restricted
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return bool
 */
function gamipress_restrict_content_is_restricted_links( $post_id = null ) {

    if( ! gamipress_restrict_content_is_restricted( $post_id ) ) {
        return false;
    }

    $restricted = gamipress_restrict_content_get_meta( $post_id, 'restrict_links' );

    return $restricted === 'on';

}

/**
 * Return true if post is restricted and has images restricted
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return bool
 */
function gamipress_restrict_content_is_restricted_images( $post_id = null ) {

    if( ! gamipress_restrict_content_is_restricted( $post_id ) ) {
        return false;
    }

    $restricted = gamipress_restrict_content_get_meta( $post_id, 'restrict_images' );

    return $restricted === 'on';

}

/**
 * Return true if post is restricted and has featured image restricted
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return bool
 */
function gamipress_restrict_content_is_restricted_featured_image( $post_id = null ) {

    if( ! gamipress_restrict_content_is_restricted( $post_id ) ) {
        return false;
    }

    $restricted = gamipress_restrict_content_get_meta( $post_id, 'restrict_featured_image' );

    return $restricted === 'on';

}

/**
 * Returns the configured page to redirect, by default -1 (WordPress error page)
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return integer
 */
function gamipress_restrict_content_get_redirect_page( $post_id = null ) {

    $redirect_page = gamipress_restrict_content_get_meta( $post_id, 'redirect_page' );

    return absint( $redirect_page );

}

/**
 * Returns the configured content replacement, by default 'read-more'
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return string
 */
function gamipress_restrict_content_get_content_replacement( $post_id = null ) {

    $content_replacement = gamipress_restrict_content_get_meta( $post_id, 'content_replacement' );

    if( empty( $content_replacement ) ) {
        return 'read-more';
    }

    return $content_replacement;

}

/**
 * Returns the configured content length, by default 500
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return integer
 */
function gamipress_restrict_content_get_content_length( $post_id = null ) {

    $content_length = gamipress_restrict_content_get_meta( $post_id, 'content_length' );

    if( empty( $content_length ) ) {
        return 500;
    }

    return absint( $content_length );

}

/**
 * Returns the configured content replacement text, by default ''
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return string
 */
function gamipress_restrict_content_get_after_content_replacement_text( $post_id = null ) {

    return gamipress_restrict_content_get_meta( $post_id, 'after_content_replacement_text' );

}

/**
 * Returns the configured content replacement text for guests, by default ''
 *
 * @since 1.0.4
 *
 * @param integer $post_id
 *
 * @return string
 */
function gamipress_restrict_content_get_guest_after_content_replacement_text( $post_id = null ) {

    $replacement_text = gamipress_restrict_content_get_meta( $post_id, 'guest_after_content_replacement_text' );

    if( empty( $replacement_text ) ) {
        $replacement_text = gamipress_restrict_content_get_meta( $post_id, 'after_content_replacement_text' );
    }

    return $replacement_text;

}

/**
 * Returns the configured links replacement text, by default ''
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return string
 */
function gamipress_restrict_content_get_links_replacement_text( $post_id = null ) {

    return gamipress_restrict_content_get_meta( $post_id, 'links_replacement_text' );

}

/**
 * Returns the configured links replacement text for guests, by default ''
 *
 * @since 1.0.4
 *
 * @param integer $post_id
 *
 * @return string
 */
function gamipress_restrict_content_get_guest_links_replacement_text( $post_id = null ) {

    $replacement_text = gamipress_restrict_content_get_meta( $post_id, 'guest_links_replacement_text' );

    if( empty( $replacement_text ) ) {
        $replacement_text = gamipress_restrict_content_get_meta( $post_id, 'links_replacement_text' );
    }

    return $replacement_text;

}

/**
 * Returns the configured images replacement text, by default ''
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return string
 */
function gamipress_restrict_content_get_images_replacement_text( $post_id = null ) {

    return gamipress_restrict_content_get_meta( $post_id, 'images_replacement_text' );

}

/**
 * Returns the configured images replacement text for guests, by default ''
 *
 * @since 1.0.4
 *
 * @param integer $post_id
 *
 * @return string
 */
function gamipress_restrict_content_get_guest_images_replacement_text( $post_id = null ) {

    $replacement_text = gamipress_restrict_content_get_meta( $post_id, 'guest_images_replacement_text' );

    if( empty( $replacement_text ) ) {
        $replacement_text = gamipress_restrict_content_get_meta( $post_id, 'images_replacement_text' );
    }

    return $replacement_text;

}

/**
 * Returns the configured unlock by, by default 'complete-restrictions'
 *
 * @since 1.0.2
 *
 * @param integer $post_id
 *
 * @return string
 */
function gamipress_restrict_content_get_unlock_by( $post_id = null ) {

    $unlock_by = gamipress_restrict_content_get_meta( $post_id, 'unlock_by' );

    if( empty( $unlock_by ) ) {
        return 'complete-restrictions';
    }

    return $unlock_by;

}

/**
 * Return true if post is allowed to get accessed by expending points
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 *
 * @return bool
 */
function gamipress_restrict_content_allow_access_with_points( $post_id = null ) {

    if( ! gamipress_restrict_content_is_restricted( $post_id ) ) {
        return false;
    }

    $access_with_points = gamipress_restrict_content_get_meta( $post_id, 'access_with_points' );

    return $access_with_points === 'on';

}

/**
 * Returns the configured points to access, by default 0
 *
 * @since 1.0.2
 *
 * @param integer $post_id
 *
 * @return integer
 */
function gamipress_restrict_content_get_points_to_access( $post_id = null ) {

    $points_to_access = gamipress_restrict_content_get_meta( $post_id, 'points_to_access' );

    return absint( $points_to_access );

}

/**
 * Returns the configured points type to access, by default 0
 *
 * @since 1.0.2
 *
 * @param integer $post_id
 *
 * @return string
 */
function gamipress_restrict_content_get_points_type_to_access( $post_id = null ) {

    $points_type_to_access = gamipress_restrict_content_get_meta( $post_id, 'points_type_to_access' );

    return $points_type_to_access;

}

/**
 * Helper function to replace last occurrence in a string
 *
 * @since 1.0.8
 *
 * @param string $search
 * @param string $replace
 * @param string $string
 *
 * @return string
 */
function gamipress_restrict_content_replace_last( $search, $replace, $string ) {

    $pos = strrpos( $string, $search );

    if( $pos !== false ) {
        $string = substr_replace( $string, $replace, $pos, strlen( $search ) );
    }

    return $string;

}