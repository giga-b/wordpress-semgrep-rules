<?php
/**
 * Post Functions
 *
 * @package     GamiPress\Restrict_Content\Post_Functions
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check if user has been granted to access to a given post
 *
 * Important! Administrator and authors are not restricted to access
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 * @param integer $user_id
 *
 * @return bool
 */
function gamipress_restrict_content_is_user_granted( $post_id = null, $user_id = null ) {

    if( $post_id === null ) {
        $post_id = get_the_ID();
    }

    if( $user_id === null ) {
        $user_id = get_current_user_id();
    }

    // Access granted if user is administrator
    if( user_can( $user_id, 'administrator' ) ) {
        return true;
    }

    $post = get_post( $post_id );

    // Access granted if user is the post author
    if( $post && absint( $post->post_author ) === $user_id ) {
        return true;
    }

    $granted_roles = gamipress_restrict_content_get_meta( $post_id, 'granted_roles', false );

    // Access granted if user role has been manually granted
    if( is_array( $granted_roles ) ) {

        foreach( $granted_roles as $granted_role ) {
            if( user_can( $user_id, $granted_role ) ) {
                return true;
            }
        }

    }

    $granted_users = gamipress_restrict_content_get_meta( $post_id, 'granted_users', false );

    // Access granted if has been manually granted
    if( is_array( $granted_users ) ) {

        // Turn granted users IDs to int to ensure check
        $granted_users = array_map( 'intval', $granted_users );

        if( in_array( $user_id, $granted_users ) ) {
            return true;
        }

    }

    /**
     * Filter to custom check is user has access to a post
     *
     * Note: To override if user meets requirements, check 'gamipress_restrict_content_user_meets_restrictions' filter
     *
     * @since 1.0.0
     *
     * @param bool  $access_granted Whatever is granted to access to this post or not
     * @param int   $post_id        The post ID
     * @param int   $user_id        The user ID
     */
    if( apply_filters( 'gamipress_restrict_content_is_user_granted', false, $post_id, $user_id ) ) {
        return true;
    }

    // Access granted if user has access to the post (by meeting all restrictions or by expending points)
    if( gamipress_restrict_content_user_has_unlocked_post( $post_id, $user_id ) ) {
        return true;
    }

    $unlock_by = gamipress_restrict_content_get_unlock_by( $post_id );

    // Access granted if post is unlocked by completing restrictions and user meets all of them
    if( $unlock_by === 'complete-restrictions' && gamipress_restrict_content_user_meets_all_restrictions( $post_id, $user_id ) ) {
        return true;
    }

    return false;

}

/**
 * Check if user already has unlocked the post
 *
 * @since 1.0.2
 *
 * @param integer $post_id
 * @param integer $user_id
 *
 * @return bool
 */
function gamipress_restrict_content_user_has_unlocked_post( $post_id = null, $user_id = null ) {

    // Guest has access restricted
    if( $user_id === 0 ) {
        return false;
    }

    $has_unlocked = gamipress_get_user_last_log( $user_id, array(
        'type' => 'content_unlock',
        'post_id' => $post_id,
    ) );

    // Check if user has unlocked the post
    return ( $has_unlocked !== false );

}

/**
 * Return a list of post IDs that user has got access
 *
 * @since 1.0.8
 *
 * @param integer $user_id
 *
 * @return array
 */
function gamipress_restrict_content_get_user_unlocked_posts( $user_id = null ) {

    global $wpdb;

    $user_id = absint( $user_id );

    // Guest has access restricted
    if( $user_id === 0 ) {
        return array();
    }

    $logs 		= GamiPress()->db->logs;
    $logs_meta 	= GamiPress()->db->logs_meta;

    $unlocked_posts = $wpdb->get_results(
        $wpdb->prepare( "SELECT DISTINCT lm.meta_value
            FROM {$logs} AS l
            LEFT JOIN {$logs_meta} AS lm ON ( l.log_id = lm.log_id AND lm.meta_key = '_gamipress_post_id' )
            WHERE l.type = 'content_unlock'
            AND l.user_id = %d
            AND lm.meta_value IS NOT NULL",
            $user_id
        ),
        ARRAY_A
    );

    return wp_list_pluck( $unlocked_posts, 'meta_value' );

}

/**
 * Check if user meets all restrictions of a given post
 *
 * @since 1.0.0
 *
 * @param integer $post_id
 * @param integer $user_id
 *
 * @return bool
 */
function gamipress_restrict_content_user_meets_all_restrictions( $post_id = null, $user_id = null ) {

    if( $post_id === null ) {
        $post_id = get_the_ID();
    }

    if( $user_id === null ) {
        $user_id = get_current_user_id();
    }

    $restrictions = gamipress_restrict_content_get_meta( $post_id, 'restrictions' );

    if( ! is_array( $restrictions ) ) {
        // Prevent to block access to everyone if restrictions are not configured yet
        return true;
    }

    $passed_restrictions = array();

    foreach( $restrictions as $restriction ) {

        if( gamipress_restrict_content_user_meets_restriction( $restriction, $user_id ) ) {
            $passed_restrictions[] = true;
        }

    }

    $meet_all_restrictions = count( $passed_restrictions ) >= count( $restrictions );

    /**
     * Filter to custom check is user meets all restrictions to access a post
     *
     * @since 1.0.0
     *
     * @param bool      $meet_all_restrictions
     * @param int       $post_id                Post ID
     * @param int       $user_id                User ID
     * @param array     $restrictions           Post configured restrictions
     * @param array     $passed_restrictions    User passed restrictions
     */
    $meet_all_restrictions = apply_filters( 'gamipress_restrict_content_user_meets_restrictions', $meet_all_restrictions, $post_id, $user_id, $restrictions, $passed_restrictions );

    // If user meets all restrictions but this is not registered in logs, then register and fire the action
    if( $meet_all_restrictions && ! gamipress_restrict_content_user_has_unlocked_post( $post_id, $user_id ) ) {
        // Register the content unlock on logs
        gamipress_restrict_content_log_post_unlock( $post_id, $user_id );

        /**
         * Post unlocked meeting all restrictions
         *
         * @since 1.0.2
         *
         * @param int       $post_id 	    The post unlocked ID
         * @param int       $user_id 	    The user ID
         * @param array     $restrictions 	Post configured restrictions
         */
        do_action( 'gamipress_restrict_content_post_unlocked_meeting_all_restrictions', $post_id, $user_id, $restrictions );
    }

    return $meet_all_restrictions;

}

/**
 * Check if user meets a given restriction
 *
 * @since 1.0.0
 *
 * @param array     $restriction
 * @param integer   $user_id
 *
 * @return bool
 */
function gamipress_restrict_content_user_meets_restriction( $restriction, $user_id = null ) {

    global $wpdb;

    if( $user_id === null ) {
        $user_id = get_current_user_id();
    }

    // Setup vars
    $prefix = '_gamipress_restrict_content_';
    $ct_table = ct_setup_table( 'gamipress_user_earnings' );
    
    if( $restriction[$prefix . 'type'] === 'earn-points' && isset( $restriction[$prefix . 'points_type'] ) ) {
        // Restriction based on current user points

        $required_points = absint( $restriction[$prefix . 'points'] );
        $required_points_type = $restriction[$prefix . 'points_type'];
        
        $user_points = gamipress_get_user_points( $user_id, $required_points_type );

        if( $user_points >= $required_points ) {
            return true;
        }

    } else if( $restriction[$prefix . 'type'] === 'earn-rank' ) {
        // Restriction based if user has earned a specific rank

        $rank_id = $restriction[$prefix . 'rank_id'];

        $earned = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*)
            FROM   {$ct_table->db->table_name} AS p
            WHERE p.user_id = %d
             AND p.post_id = %d
            LIMIT 1",
            $user_id,
            absint( $rank_id )
        ) );

        if( absint( $earned ) > 0 ) {
            return true;
        }

    } else if( $restriction[$prefix . 'type'] === 'specific-achievement' ) {
        // Restriction based if user has earned a specific achievement

        $achievement_id = $restriction[$prefix . 'achievement_id'];
        $required_times = $restriction[$prefix . 'count'];

        $earned_times = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*)
            FROM   {$ct_table->db->table_name} AS p
            WHERE p.user_id = %d
             AND p.post_id = %d",
            $user_id,
            absint( $achievement_id )
        ) );

        if( absint( $earned_times ) >= absint( $required_times ) ) {
            return true;
        }

    } else if( $restriction[$prefix . 'type'] === 'any-achievement' ) {

        $achievement_type = $restriction[$prefix . 'achievement_type'];
        $required_times = $restriction[$prefix . 'count'];

        $earned_times = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*)
            FROM   {$ct_table->db->table_name} AS p
            WHERE p.user_id = %d
             AND p.post_type = %s",
            $user_id,
            $achievement_type
        ) );

        if( absint( $earned_times ) >= absint( $required_times ) ) {
            return true;
        }

    } else if( $restriction[$prefix . 'type'] === 'all-achievements' ) {

        $achievement_type = $restriction[$prefix . 'achievement_type'];

        // Get all earned achievements of type
        $earned_achievements = $wpdb->get_results( $wpdb->prepare(
            "SELECT p.post_id
            FROM   {$ct_table->db->table_name} AS p
            WHERE p.user_id = %d
             AND p.post_type = %s
            LIMIT 1",
            $user_id,
            $achievement_type
        ) );

        // Bail if user has not earned no achievements of this type
        if( count( $earned_achievements ) === 0 ) {
            return false;
        }

        // Get all achievements of type
        $all_achievements_of_type = gamipress_get_achievements( array( 'post_type' => $achievement_type ) );

        // Bail if there are no achievements of this type
        if( ! is_array( $all_achievements_of_type ) ) {
            return true;
        }

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

    }

    /**
     * Filter to custom check is user meet a specific restriction (commonly used for custom restrictions)
     *
     * @since 1.0.2
     *
     * @param bool      $meet_restriction
     * @param int       $user_id                User ID
     * @param array     $restriction            The restriction
     */
    return apply_filters( 'gamipress_restrict_content_user_meets_restriction', false, $user_id, $restriction );

}