<?php
/**
 * Blocks
 *
 * @package     GamiPress\Restrict_Content\Blocks
 * @since       1.0.7
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Turn select2 fields into 'post' or 'user' field types
 *
 * @since 1.0.7
 *
 * @param array                 $fields
 * @param GamiPress_Shortcode   $shortcode
 *
 * @return array
 */
function gamipress_restrict_content_block_fields( $fields, $shortcode ) {

    switch ( $shortcode->slug ) {
        case 'gamipress_post_restrictions':
            // Post ID
            $fields['id']['type'] = 'post';
            $fields['id']['post_type'] = gamipress_restrict_content_post_types_slugs();
            break;
        case 'gamipress_posts_restricted':
        case 'gamipress_posts_unlocked':
            // Exclude
            $fields['exclude']['type'] = 'post';
            $fields['exclude']['post_type'] = gamipress_restrict_content_post_types_slugs();
            break;
        case 'gamipress_restrict_content':

            // Post selectors

            // Achievement ID
            $fields['achievement']['type'] = 'post';
            $fields['achievement']['post_type'] = gamipress_get_achievement_types_slugs();

            // Rank ID
            $fields['rank']['type'] = 'post';
            $fields['rank']['post_type'] = gamipress_get_rank_types_slugs();

            // Fields visibility

            // Points
            $fields['points']['conditions'] = array(
                'unlock_by' => array( 'expend_points', 'points_balance' ),
            );
            $fields['points_type']['conditions'] = array(
                'unlock_by' => array( 'expend_points', 'points_balance' ),
            );

            // Achievement
            $fields['achievement']['conditions'] = array(
                'unlock_by' => 'achievement',
            );
            $fields['achievement_type']['conditions'] = array(
                'unlock_by' => array( 'achievement_type', 'all_achievement_type' ),
            );
            $fields['achievement_count']['conditions'] = array(
                'unlock_by' => 'achievement_type',
            );

            // Rank
            $fields['rank']['conditions'] = array(
                'unlock_by' => 'rank',
            );

            // Content
            $fields['content'] = array(
                'name'          => __( 'Content', 'gamipress-restrict-content' ),
                'desc'          => __( 'Content that is shown to users that have successfully unlocked this portion of content.', 'gamipress-restrict-content' ),
                'type'          => 'textarea',
                'default'       => '',
            );
            break;
        case 'gamipress_show_content_if':
        case 'gamipress_hide_content_if':

            // Post selectors

            // Achievement ID
            $fields['achievement']['type'] = 'post';
            $fields['achievement']['post_type'] = gamipress_get_achievement_types_slugs();

            // Rank ID
            $fields['rank']['type'] = 'post';
            $fields['rank']['post_type'] = gamipress_get_rank_types_slugs();

            // Fields visibility

            // Points
            $fields['points']['conditions'] = array(
                'condition' => array( 'points_greater', 'points_lower' ),
            );
            $fields['points_type']['conditions'] = array(
                'condition' => array( 'points_greater', 'points_lower' ),
            );

            // Achievement
            $fields['achievement']['conditions'] = array(
                'condition' => 'achievement',
            );
            $fields['achievement_type']['conditions'] = array(
                'condition' => array( 'achievement_type', 'all_achievement_type' ),
            );
            $fields['achievement_count']['conditions'] = array(
                'condition' => 'achievement_type',
            );

            // Rank
            $fields['rank']['conditions'] = array(
                'condition' => 'rank',
            );

            // Content
            if( $shortcode->slug === 'gamipress_show_content_if' )
                $content_desc = __( 'Content that is shown to users that meets the condition.', 'gamipress-restrict-content' );
            else
                $content_desc = __( 'Content that is hidden to users that meets the condition.', 'gamipress-restrict-content' );

            $fields['content'] = array(
                'name'          => __( 'Content', 'gamipress-restrict-content' ),
                'desc'          => $content_desc,
                'type'          => 'textarea',
                'default'       => '',
            );
            break;
    }

    return $fields;

}
add_filter( 'gamipress_get_block_fields', 'gamipress_restrict_content_block_fields', 11, 2 );

/**
 * Add custom block attributes
 *
 * @since 1.1.7
 *
 * @param array                 $attributes
 * @param GamiPress_Shortcode   $shortcode
 *
 * @return array
 */
function gamipress_restrict_content_block_attributes( $attributes, $shortcode ) {

    switch ( $shortcode->slug ) {
        case 'gamipress_restrict_content':
        case 'gamipress_show_content_if':
        case 'gamipress_hide_content_if':
            // Content
            $attributes['content'] = array(
                'type' => 'string',
            );
            break;
    }

    return $attributes;

}
add_filter( 'gamipress_gutenberg_blocks_get_block_attributes', 'gamipress_restrict_content_block_attributes', 11, 2 );
