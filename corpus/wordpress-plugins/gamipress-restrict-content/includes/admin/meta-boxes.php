<?php
/**
 * Meta Boxes
 *
 * @package     GamiPress\Restrict_Content\Admin\Meta_Boxes
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function gamipress_restrict_content_meta_boxes() {

    $prefix = '_gamipress_restrict_content_';

    // Grab our points types as an array
    $points_types_options = array(
        '' => 'Default'
    );

    foreach( gamipress_get_points_types() as $slug => $data ) {
        $points_types_options[$slug] = $data['plural_name'];
    }

    // Setup the achievement types options
    $achievement_types_options = array(
        '' => __( 'Choose an achievement type', 'gamipress-restrict-content' )
    );

    foreach( gamipress_get_achievement_types() as $slug => $data ) {
        $achievement_types_options[$slug] = $data['plural_name'];
    }

    // Setup the rank types options
    $rank_types_options = array(
        '' => __( 'Choose a rank type', 'gamipress-restrict-content' )
    );

    foreach( gamipress_get_rank_types() as $slug => $data ) {
        $rank_types_options[$slug] = $data['singular_name'];
    }

    $post_types = gamipress_restrict_content_post_types_slugs();

    gamipress_add_meta_box(
        'gamipress-restrict-content',
        __( 'GamiPress - Restrict Content', 'gamipress-restrict-content' ),
        $post_types,
        array(

            // Restrictions

            $prefix . 'restrict' => array(
                'name' => __( 'Restrict', 'gamipress-restrict-content' ),
                'desc' => __( 'Check this option to restrict this post.', 'gamipress-restrict-content' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'unlock_by' => array(
                'name' => __( 'Unlock access by', 'gamipress-restrict-content' ),
                'desc' => __( 'Choose how users can get access to this post.', 'gamipress-restrict-content' ),
                'type' => 'select',
                'options' => apply_filters( 'gamipress_restrict_content_post_unlock_by_options', array(
                    'complete-restrictions' => __( 'Completing requirements', 'gamipress-restrict-content' ),
                    'expend-points' => __( 'Expending Points', 'gamipress-restrict-content' ),
                ) )
            ),
            $prefix . 'access_with_points' => array(
                'name' => __( 'Allow get access expending points', 'gamipress-restrict-content' ),
                'desc' => __( 'Check this option to allow users to optionally get access without completing the requirements by expending an amount of points.', 'gamipress-restrict-content' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch'
            ),
            $prefix . 'points_to_access' => array(
                'name' => __( 'Points to get access', 'gamipress-restrict-content' ),
                'desc' => __( 'Amount of points needed to get access to this post.', 'gamipress-restrict-content' ),
                'type' => 'gamipress_points',
                'points_type_key' => $prefix . 'points_type_to_access',
                'default' => '0',
            ),
            $prefix . 'restrictions' => array(
                'name' => __( 'Requirements', 'gamipress-restrict-content' ),
                'desc' => __( 'Configure the requirements that users need to meet to unlock the access to this post.', 'gamipress-restrict-content' ),
                'type' => 'group',
                'options'     => array(
                    'add_button'    => __( 'Add Requirement', 'gamipress-restrict-content' ),
                    'remove_button' => __( 'Remove Requirement', 'gamipress-restrict-content' ),
                ),
                'fields' => array(

                    // Restriction type

                    $prefix . 'type' => array(
                        'name' => __( 'When:', 'gamipress-restrict-content' ),
                        'type' => 'select',
                        'options' => array(
                            'earn-points'           => __( 'Earn an amount of points', 'gamipress-restrict-content' ),
                            'earn-rank'             => __( 'Reach a specific rank', 'gamipress-restrict-content' ),
                            'specific-achievement'  => __( 'Unlock a specific achievement', 'gamipress-restrict-content' ),
                            'any-achievement'       => __( 'Unlock any achievement of type', 'gamipress-restrict-content' ),
                            'all-achievements'     	=> __( 'Unlock all achievements of type', 'gamipress-restrict-content' ),
                        ),
                        'classes' => 'gamipress-restrict-content-type'
                    ),

                    // Restriction points

                    $prefix . 'points' => array(
                        'name' => __( 'Points:', 'gamipress-restrict-content' ),
                        'type' => 'text',
                        'attributes' => array(
                            'type' => 'number',
                            'min' => '1'
                        ),
                        'default' => 1,
                        'classes' => 'gamipress-restrict-content-points'
                    ),
                    $prefix . 'points_type' => array(
                        'type' => 'select',
                        'options' => $points_types_options,
                        'classes' => 'gamipress-restrict-content-points-type'
                    ),

                    // Restriction rank

                    $prefix . 'rank_type' => array(
                        'name' => __( 'Rank:', 'gamipress-restrict-content' ),
                        'type' => 'select',
                        'options' => $rank_types_options,
                        'classes' => 'gamipress-restrict-content-rank-type'
                    ),
                    $prefix . 'rank_id' => array(
                        'type' => 'advanced_select',
                        'options_cb' => 'gamipress_options_cb_posts',
                        'classes' => 'gamipress-restrict-content-rank'
                    ),

                    // Restriction achievement

                    $prefix . 'achievement_type' => array(
                        'name' => __( 'Achievement:', 'gamipress-restrict-content' ),
                        'type' => 'select',
                        'options' => $achievement_types_options,
                        'classes' => 'gamipress-restrict-content-achievement-type'
                    ),
                    $prefix . 'achievement_id' => array(
                        'type' => 'advanced_select',
                        'options_cb' => 'gamipress_options_cb_posts',
                        'classes' => 'gamipress-restrict-content-achievement'
                    ),
                    $prefix . 'count' => array(
                        'desc' => __( 'time(s)', 'gamipress-restrict-content' ),
                        'type' => 'text',
                        'attributes' => array(
                            'type' => 'number',
                            'min' => '1'
                        ),
                        'default' => 1,
                        'classes' => 'gamipress-restrict-content-count'
                    ),

                    // Restriction label

                    $prefix . 'label' => array(
                        'name' => __( 'Label:', 'gamipress-restrict-content' ),
                        'type' => 'text',
                        'classes' => 'gamipress-restrict-content-label'
                    ),

                )
            ),

            // Access

            $prefix . 'restrict_access' => array(
                'name' => __( 'Restrict Access', 'gamipress-restrict-content' ),
                'desc' => __( 'Restrict access to users that don\'t meet restriction requirements.', 'gamipress-restrict-content' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'redirect_page' => array(
                'name' => __( 'Redirect Page', 'gamipress-restrict-content' ),
                'desc' => __( 'Page where users without access are sent when trying to access to this post.', 'gamipress-restrict-content' ),
                'type' => 'select',
                'classes' 	        => 'gamipress-post-selector',
                'attributes' 	    => array(
                    'data-post-type' => implode( ',',  gamipress_restrict_content_redirect_post_types_slugs() ),
                    'data-placeholder' => __( 'WordPress error page', 'gamipress-restrict-content' ),
                ),
                'default'           => '',
                'options_cb'        => 'gamipress_options_cb_posts'
            ),

            // Content

            $prefix . 'restrict_content' => array(
                'name' => __( 'Restrict Content', 'gamipress-restrict-content' ),
                'desc' => __( 'Restrict content to users that don\'t meet restriction requirements.', 'gamipress-restrict-content' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'content_replacement' => array(
                'name' => __( 'Content Replacement', 'gamipress-restrict-content' ),
                'desc' => __( 'Content to show to users that don\'t meet restriction requirements.', 'gamipress-restrict-content' ),
                'type' => 'select',
                'options' => array(
                    'read-more'     => __( 'Until read more tag', 'gamipress-restrict-content' ),
                    'excerpt'       => __( 'Replace content with excerpt', 'gamipress-restrict-content' ),
                    'content'       => __( 'Trimmed content', 'gamipress-restrict-content' ),
                    'full-content'  => __( 'Full content', 'gamipress-restrict-content' ),
                ),
                'default' => 'read-more'
            ),
            $prefix . 'content_length' => array(
                'name' => __( 'Content Length', 'gamipress-restrict-content' ),
                'desc' => __( 'Maximum number of letters to show to users that don\'t meet restriction requirements.', 'gamipress-restrict-content' ),
                'type' => 'text_small',
                'attributes' => array(
                    'type' => 'number',
                    'pattern' => '\d*',
                ),
                'default' => 500
            ),
            $prefix . 'after_content_replacement_text' => array(
                'name' => __( 'After Content Replacement Text', 'gamipress-restrict-content' ),
                'desc' => __( 'Text to show after replaced content. Available tags:', 'gamipress-restrict-content' )
                    . gamipress_restrict_content_get_pattern_tags_html( 'post' ),
                'type' => 'wysiwyg',
            ),
            $prefix . 'guest_after_content_replacement_text' => array(
                'name' => __( 'After Content Replacement Text For Guests', 'gamipress-restrict-content' ),
                'desc' => __( 'Text to show after replaced content for non logged in users. Available tags:', 'gamipress-restrict-content' )
                    . gamipress_restrict_content_get_pattern_tags_html( 'guest-post' ),
                'type' => 'wysiwyg',
            ),

            // Links

            $prefix . 'restrict_links' => array(
                'name' => __( 'Restrict Links', 'gamipress-restrict-content' ),
                'desc' => __( 'Restrict links to users that don\'t meet restriction requirements.', 'gamipress-restrict-content' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'links_replacement_text' => array(
                'name' => __( 'Links Replacement Text', 'gamipress-restrict-content' ),
                'desc' => __( 'Text to replace post links. Available tags:', 'gamipress-restrict-content' )
                    . gamipress_restrict_content_get_pattern_tags_html( 'post' ),
                'type' => 'wysiwyg',
            ),
            $prefix . 'guest_links_replacement_text' => array(
                'name' => __( 'Links Replacement Text For Guests', 'gamipress-restrict-content' ),
                'desc' => __( 'Text to replace post links for non logged in users. Available tags:', 'gamipress-restrict-content' )
                    . gamipress_restrict_content_get_pattern_tags_html( 'post' ),
                'type' => 'wysiwyg',
            ),

            // Images

            $prefix . 'restrict_featured_image' => array(
                'name' => __( 'Restrict Featured Image', 'gamipress-restrict-content' ),
                'desc' => __( 'Restrict featured image to users that don\'t meet restriction requirements.', 'gamipress-restrict-content' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'restrict_images' => array(
                'name' => __( 'Restrict Images', 'gamipress-restrict-content' ),
                'desc' => __( 'Restrict images to users that don\'t meet restriction requirements.', 'gamipress-restrict-content' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'images_replacement_text' => array(
                'name' => __( 'Images Replacement Text', 'gamipress-restrict-content' ),
                'desc' => __( 'Text to replace post images. Available tags:', 'gamipress-restrict-content' )
                    . gamipress_restrict_content_get_pattern_tags_html( 'post' ),
                'type' => 'wysiwyg',
            ),
            $prefix . 'guest_images_replacement_text' => array(
                'name' => __( 'Images Replacement Text For Guests', 'gamipress-restrict-content' ),
                'desc' => __( 'Text to replace post images for non logged in users. Available tags:', 'gamipress-restrict-content' )
                    . gamipress_restrict_content_get_pattern_tags_html( 'guest-post' ),
                'type' => 'wysiwyg',
            ),
            
            // Users

            $prefix . 'granted_roles' => array(
                'name'          => __( 'Granted Roles', 'gamipress-restrict-content' ),
                'desc'          => __( 'Manually grant access to this post to users by role.', 'gamipress-restrict-content' ),
                'type'          => 'advanced_select',
                'multiple'      => true,
                'classes' 	    => 'gamipress-selector',
                'options_cb'    => 'gamipress_restrict_content_get_roles_options',
            ),
            $prefix . 'granted_users' => array(
                'name'          => __( 'Granted Users', 'gamipress-restrict-content' ),
                'desc'          => __( 'Manually grant access to this post to the users you want.', 'gamipress-restrict-content' ),
                'type'          => 'advanced_select',
                'multiple'      => true,
                'classes' 	    => 'gamipress-user-selector',
                'options_cb'    => 'gamipress_options_cb_users',
            ),
        ),
        array(
            'vertical_tabs' => true,
            'tabs' => array(
                'restrictions' => array(
                    'icon' => 'dashicons-lock',
                    'title' => __( 'Restrictions', 'gamipress-restrict-content' ),
                    'fields' => array(
                        $prefix . 'restrict',
                        $prefix . 'unlock_by',
                        $prefix . 'access_with_points',
                        $prefix . 'points_to_access',
                        $prefix . 'restrictions',
                    ),
                ),
                'access' => array(
                    'icon' => 'dashicons-admin-network',
                    'title' => __( 'Access', 'gamipress-restrict-content' ),
                    'fields' => array(
                        $prefix . 'restrict_access',
                        $prefix . 'redirect_page',
                    ),
                ),
                'content' => array(
                    'icon' => 'dashicons-align-left',
                    'title' => __( 'Content', 'gamipress-restrict-content' ),
                    'fields' => array(
                        $prefix . 'restrict_content',
                        $prefix . 'content_replacement',
                        $prefix . 'content_length',
                        $prefix . 'after_content_replacement_text',
                        $prefix . 'guest_after_content_replacement_text',
                    ),
                ),
                'links' => array(
                    'icon' => 'dashicons-admin-links',
                    'title' => __( 'Links', 'gamipress-restrict-content' ),
                    'fields' => array(
                        $prefix . 'restrict_links',
                        $prefix . 'links_replacement_text',
                        $prefix . 'guest_links_replacement_text',
                    ),
                ),
                'images' => array(
                    'icon' => 'dashicons-format-image',
                    'title' => __( 'Images', 'gamipress-restrict-content' ),
                    'fields' => array(
                        $prefix . 'restrict_featured_image',
                        $prefix . 'restrict_images',
                        $prefix . 'images_replacement_text',
                        $prefix . 'guest_images_replacement_text',
                    ),
                ),
                'users' => array(
                    'icon' => 'dashicons-admin-users',
                    'title' => __( 'Users', 'gamipress-restrict-content' ),
                    'fields' => array(
                        $prefix . 'granted_roles',
                        $prefix . 'granted_users',
                    ),
                ),
            )
        )
    );

}
add_action( 'cmb2_admin_init', 'gamipress_restrict_content_meta_boxes' );