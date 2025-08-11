<?php
/**
 * Content Functions
 *
 * @package     GamiPress\Restrict_Content\Content_Functions
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check if user has been granted to access to a portion of content
 *
 * Important! Administrator and authors are not restricted to access
 *
 * @since 1.0.0
 *
 * @param string    $content_id
 * @param int       $user_id
 * @param int       $post_id
 * @param array     $atts
 *
 * @return bool
 */
function gamipress_restrict_content_is_user_granted_to_content( $content_id = '', $user_id = null, $post_id = null, $atts = array() ) {

    global $wpdb;

    if( $post_id === null )
        $post_id = get_the_ID();

    if( $user_id === null )
        $user_id = get_current_user_id();

    // Access granted if user is administrator
    if( user_can( $user_id,  'administrator' ) )
        return true;

    $post = get_post( $post_id );

    // Access granted if user is the post author
    if( $post && absint( $post->post_author ) === $user_id )
        return true;

    $granted_roles = ( isset( $atts['granted_roles'] ) && ! empty( $atts['granted_roles'] ) ? $atts['granted_roles'] : array() );

    // Access granted if user role has been manually granted
    if( is_array( $granted_roles ) ) {

        foreach( $granted_roles as $granted_role ) {
            if( user_can( $user_id, $granted_role ) ) {
                return true;
            }
        }

    }

    $granted_users = ( isset( $atts['granted_users'] ) && ! empty( $atts['granted_users'] ) ? $atts['granted_users'] : array() );

    // Access granted if has been manually granted
    if( is_array( $granted_users ) ) {

        // Turn granted users IDs to int to ensure check
        $granted_users = array_map( 'intval', $granted_users );

        if( in_array( $user_id, $granted_users ) ) {
            return true;
        }
    }

    /**
     * Filter to custom check is user has access to a portion of content
     *
     * @since 1.0.2
     *
     * @param bool      $access_granted Whatever is granted to access to this post or not
     * @param string    $content_id     The content ID
     * @param int       $user_id        The user ID
     * @param int       $post_id        The post ID
     * @param array     $args           Given args
     */
    if( apply_filters( 'gamipress_restrict_content_is_user_granted_to_content', false, $content_id, $user_id, $post_id, $atts ) )
        return true;

    // Access granted if user has access to the content (by expending points)
    if( gamipress_restrict_content_user_has_unlocked_content( $content_id, $user_id ) )
        return true;

    // Temporally setup earnings table
    $ct_table = ct_setup_table( 'gamipress_user_earnings' );

    // Check if user gets access based on the unlock_by field
    switch( $atts['unlock_by'] ) {
        case 'expend_points':
            // expend points is handled through ajax
            break;
        case 'points_balance':
            // Check if user meets the points required
            if( gamipress_get_user_points( $user_id, $atts['points_type'] ) > $atts['points'] )
                return true;
            break;
        case 'achievement':
            // Check if user has earned all achievements required

            $achievements = explode( ',',  $atts['achievement'] );
            $earned_all = true;

            foreach( $achievements as $achievement ) {
                if( absint( $achievement ) === 0 )
                    continue;

                $earned_times = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*)
                    FROM   {$ct_table->db->table_name} AS p
                    WHERE p.user_id = %d
                     AND p.post_id = %d",
                    $user_id,
                    absint( $achievement )
                ) );

                if( absint( $earned_times ) === 0 )
                    $earned_all = false;
            }

            if( $earned_all )
                return true;
            break;
        case 'achievement_type':
            // Check if user has earned the number of achievements of this type required

            $earned_times = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*)
                FROM   {$ct_table->db->table_name} AS p
                WHERE p.user_id = %d
                 AND p.post_type = %s",
                $user_id,
                $atts['achievement_type']
            ) );

            if( absint( $earned_times ) >= absint( $atts['achievement_count'] ) )
                return true;
            break;
        case 'all_achievement_type':
            // Check if user has earned all achievements of this type required

            // Get all earned achievements of type
            $earned_achievements = $wpdb->get_results( $wpdb->prepare(
                "SELECT p.post_id
                FROM   {$ct_table->db->table_name} AS p
                WHERE p.user_id = %d
                 AND p.post_type = %s
                LIMIT 1",
                $user_id,
                $atts['achievement_type']
            ) );

            // Bail if user has not earned no achievements of this type
            if( count( $earned_achievements ) === 0 )
                return false;

            // Get all achievements of type
            $all_achievements_of_type = gamipress_get_achievements( array( 'post_type' => $atts['achievement_type'] ) );

            // Bail if there are no achievements of this type
            if( ! is_array( $all_achievements_of_type ) )
                return true;

            $all_per_type = true;

            foreach ( $all_achievements_of_type as $achievement ) {

                // Assume the user hasn't earned this achievement
                $found_achievement = false;

                // Loop through each earned achievement and see if we've earned it
                foreach ( $earned_achievements as $earned_achievement ) {
                    if ( $earned_achievement->post_id == $achievement->ID ) {
                        $found_achievement = true;
                        break;
                    }
                }

                // If we haven't earned this single achievement, we haven't earned them all
                if ( ! $found_achievement ) {
                    $all_per_type = false;
                    break;
                }

            }

            if( $all_per_type ) {
                return true;
            }
            break;
        case 'rank':
            // Check if user has reached the required rank

            $ranks = explode( ',',  $atts['rank'] );
            $earned_all = true;

            foreach( $ranks as $rank ) {
                if( absint( $rank ) === 0 )
                    continue;

                // If is lowest priority rank return true directly
                if( gamipress_is_lowest_priority_rank( $rank ) )
                    continue;

                $earned_times = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*)
                    FROM   {$ct_table->db->table_name} AS p
                    WHERE p.user_id = %d
                     AND p.post_id = %d",
                    $user_id,
                    absint( $rank )
                ) );

                if( absint( $earned_times ) === 0 )
                    $earned_all = false;
            }

            if( $earned_all )
                return true;
            break;
        default:
            /**
             * Filter to custom unlock_by attribute to check is user has access to a portion of content
             *
             * @since 1.0.2
             *
             * @param bool      $access_granted Whatever is granted to access to this post or not
             * @param string    $content_id     The content ID
             * @param int       $user_id        The user ID
             * @param int       $post_id        The post ID
             * @param array     $args           Given args
             */
            if( apply_filters( 'gamipress_restrict_content_is_user_granted_to_content_unlocked_by_' . $atts['unlock_by'], false, $content_id, $user_id, $post_id, $atts ) )
                return true;
            break;
    }

    // Reset the CT setup that has been switched to earnings table
    ct_reset_setup_table();

    return false;

}

/**
 * Check if user already has unlocked a portion of content
 *
 * @since 1.0.2
 *
 * @param string    $content_id
 * @param int       $user_id
 *
 * @return bool
 */
function gamipress_restrict_content_user_has_unlocked_content( $content_id = '', $user_id = null ) {

    // Guest has access restricted
    if( $user_id === 0 ) {
        return false;
    }

    $has_unlocked = gamipress_get_user_last_log( $user_id, array(
        'type' => 'content_unlock',
        'content_id' => $content_id,
    ) );

    // Check if user has unlocked the post
    return ( $has_unlocked !== false );

}

/**
 * Update a portion of content price stored on post where are placed
 *
 * @since 1.0.2
 *
 * @param int       $post_id
 * @param string    $content_id
 * @param int       $points
 * @param string    $points_type
 *
 * @return bool
 */
function gamipress_restrict_content_update_post_content_price( $post_id = null, $content_id = '', $points = 0, $points_type = '' ) {

    if( $post_id === null ) {
        $post_id = get_the_ID();
    }

    // Bail if no points amount
    if( $points === 0 ) {
        return false;
    }

    // Bail if no points type
    if( empty( $points_type ) ) {
        return false;
    }

    // Setup a security system storing all content prices on a post meta to be checked when user tries to unlock it

    // Get post content prices
    $content_prices = get_post_meta( $post_id, '_gamipress_restrict_content_content_prices', true );

    if( empty( $content_prices ) ) {
        $content_prices = array();
    }

    if( ! isset( $content_prices[$content_id] ) ) {

        // If content price not stored on post, add it
        $content_prices[$content_id] = array(
            'points' => $points,
            'points_type' => $points_type,
        );

        return update_post_meta( $post_id, '_gamipress_restrict_content_content_prices', $content_prices );

    } else if( $content_prices[$content_id]['points'] !== $points || $content_prices[$content_id]['points_type'] !== $points_type ) {

        // If content price has change, update it
        $content_prices[$content_id] = array(
            'points' => $points,
            'points_type' => $points_type,
        );

        return update_post_meta( $post_id, '_gamipress_restrict_content_content_prices', $content_prices );
    }

    // Nothing to change
    return false;

}

/**
 * Get a portion of content price stored on post where are placed
 *
 * @since 1.0.2
 *
 * @param int       $post_id
 * @param string    $content_id
 *
 * @return array|false          Return the configured content price as array( 'points' => 100, 'points_type' => '' )
 */
function gamipress_restrict_content_get_post_content_price( $post_id = null, $content_id = '' ) {

    if( $post_id === null ) {
        $post_id = get_the_ID();
    }

    // Get post content prices
    $content_prices = get_post_meta( $post_id, '_gamipress_restrict_content_content_prices', true );

    if( empty( $content_prices ) ) {
        $content_prices = array();
    }

    if( ! isset( $content_prices[$content_id] ) ) {
        return false;
    }

    return $content_prices[$content_id];

}