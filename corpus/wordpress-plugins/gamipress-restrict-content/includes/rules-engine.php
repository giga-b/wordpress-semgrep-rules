<?php
/**
 * Rules Engine
 *
 * @package GamiPress\Restrict_content\Rules_Engine
 * @since 1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Checks if an user is allowed to work on a given requirement related to a specific content ID
 *
 * @since  1.0.2
 *
 * @param bool $return          The default return value
 * @param int $user_id          The given user's ID
 * @param int $requirement_id   The given requirement's post ID
 * @param string $trigger       The trigger triggered
 * @param int $site_id          The site id
 * @param array $args           Arguments of this trigger
 *
 * @return bool True if user has access to the requirement, false otherwise
 */
function gamipress_restrict_content_user_has_access_to_achievement( $return = false, $user_id = 0, $requirement_id = 0, $trigger = '', $site_id = 0, $args = array() ) {

    // If we're not working with a requirement, bail here
    if ( ! in_array( get_post_type( $requirement_id ), gamipress_get_requirement_types_slugs() ) )
        return $return;

    // If is specific content trigger, rules engine needs to check the given content ID
    if( $trigger === 'gamipress_restrict_content_unlock_specific_content'
        || $trigger === 'gamipress_restrict_content_unlock_specific_content_specific_post' ) {

        $content_id = $args[3];

        $required_content_id = get_post_meta( $requirement_id, '_gamipress_restrict_content_content_id', true );

        // Check if user meets the content ID unlocked
        $return = (bool) ( $content_id !== $required_content_id );
    }

    // Send back our eligibility
    return $return;

}
add_filter( 'user_has_access_to_achievement', 'gamipress_restrict_content_user_has_access_to_achievement', 10, 6 );