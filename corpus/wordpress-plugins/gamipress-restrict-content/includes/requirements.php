<?php
/**
 * Requirements
 *
 * @package GamiPress\Restrict_Content\Requirements
 * @since 1.0.2
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add the content ID field to the requirement object
 *
 * @since 1.0.2
 *
 * @param $requirement
 * @param $requirement_id
 *
 * @return array
 */
function gamipress_restrict_content_requirement_object( $requirement, $requirement_id ) {

    if( isset( $requirement['trigger_type'] )
        && ( $requirement['trigger_type'] === 'gamipress_restrict_content_unlock_specific_content'
            || $requirement['trigger_type'] === 'gamipress_restrict_content_unlock_specific_content_specific_post' ) ) {

        // Content ID
        $requirement['content_id'] = get_post_meta( $requirement_id, '_gamipress_restrict_content_content_id', true );

    }

    return $requirement;
}
add_filter( 'gamipress_requirement_object', 'gamipress_restrict_content_requirement_object', 10, 2 );

/**
 * Content ID field on requirements UI
 *
 * @since 1.0.2
 *
 * @param $requirement_id
 * @param $post_id
 */
function gamipress_restrict_content_requirement_ui_fields( $requirement_id, $post_id ) {

    $content_id = get_post_meta( $requirement_id, '_gamipress_restrict_content_content_id', true );
    ?>

    <span class="gamipress-restrict-content-content-id"><input type="text" value="<?php echo $content_id; ?>" size="3" maxlength="3" placeholder="100" />%</span>

    <?php
}
add_action( 'gamipress_requirement_ui_html_after_achievement_post', 'gamipress_restrict_content_requirement_ui_fields', 10, 2 );

/**
 * Custom handler to save the content ID on requirements UI
 *
 * @since 1.0.2
 *
 * @param $requirement_id
 * @param $requirement
 */
function gamipress_restrict_content_ajax_update_requirement( $requirement_id, $requirement ) {

    if( isset( $requirement['trigger_type'] )
        && ( $requirement['trigger_type'] === 'gamipress_restrict_content_unlock_specific_content'
            || $requirement['trigger_type'] === 'gamipress_restrict_content_unlock_specific_content_specific_post' ) ) {

        // Save the score field
        update_post_meta( $requirement_id, '_gamipress_restrict_content_content_id', $requirement['content_id'] );

    }
}
add_action( 'gamipress_ajax_update_requirement', 'gamipress_restrict_content_ajax_update_requirement', 10, 2 );