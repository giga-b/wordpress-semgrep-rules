<?php
/**
 * If Functions
 *
 * @package     GamiPress\Restrict_Content\If_Functions
 * @since       1.1.4
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get registered if conditions (used on if shortcodes)
 *
 * @since 1.1.4
 *
 * @return array
 */
function gamipress_restrict_content_get_if_conditions() {

    /**
     * Filters the registered if conditions to allow register new ones
     *
     * @since 1.1.4
     *
     * @para array $if_conditions Already registered if conditions
     *
     * @return array
     */
    return apply_filters( 'gamipress_restrict_content_if_conditions', array(
        'points_greater'        => __( 'Reaching a points balance greater than', 'gamipress-restrict-content' ),
        'points_lower'          => __( 'Reaching a points balance lower than', 'gamipress-restrict-content' ),
        'achievement'           => __( 'Unlocking achievements', 'gamipress-restrict-content' ),
        'achievement_type'      => __( 'Unlocking any achievements of type', 'gamipress-restrict-content' ),
        'all_achievement_type'  => __( 'Unlocking all achievements of type', 'gamipress-restrict-content' ),
        'rank'                  => __( 'Reaching ranks', 'gamipress-restrict-content' ),
    ) );

}

/**
 * Check if user meets the condition to access to a portion of content
 *
 * Important! Administrator are not restricted to access
 *
 * @since 1.0.0
 *
 * @param string    $condition  The condition
 * @param int       $user_id    The user ID
 * @param array     $atts       Given args
 *
 * @return bool
 */
function gamipress_restrict_content_user_meets_condition( $condition = '', $user_id = null, $atts = array() ) {

    global $wpdb;

    if( $user_id === null )
        $user_id = get_current_user_id();

    $granted_roles = ( isset( $atts['granted_roles'] ) && ! empty( $atts['granted_roles'] ) ? $atts['granted_roles'] : array() );

    // Meets condition if user role has been manually granted
    if( is_array( $granted_roles ) ) {

        foreach( $granted_roles as $granted_role ) {
            if( user_can( $user_id, $granted_role ) ) {
                return true;
            }
        }

    }

    $granted_users = ( isset( $atts['granted_users'] ) && ! empty( $atts['granted_users'] ) ? $atts['granted_users'] : array() );

    // Meets condition if has been manually granted
    if( is_array( $granted_users ) ) {

        // Turn granted users IDs to int to ensure check
        $granted_users = array_map( 'intval', $granted_users );

        if( in_array( $user_id, $granted_users ) ) {
            return true;
        }
    }

    /**
     * Filter to custom check is user meets the condition to access to a portion of content
     *
     * @since 1.1.4
     *
     * @param bool      $meets_condition    Whatever if user meets condition
     * @param string    $condition          The condition
     * @param int       $user_id            The user ID
     * @param array     $args               Given args
     */
    if( apply_filters( 'gamipress_restrict_content_user_meets_condition', false, $condition, $user_id, $atts ) ) {
        return true;
    }

    // Temporally setup earnings table
    $ct_table = ct_setup_table( 'gamipress_user_earnings' );

    // Check if user gets access based on the unlock_by field
    switch( $condition ) {
        case 'points_greater':
            // Check if user points is greater than the points required
            if( gamipress_get_user_points( $user_id, $atts['points_type'] ) > $atts['points'] )
                return true;
            break;
        case 'points_lower':
            // Check if user points is lower than  the points required
            if( gamipress_get_user_points( $user_id, $atts['points_type'] ) < $atts['points'] )
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

            if( $all_per_type )
                return true;
            break;
        case 'rank':
            // Check if user has reached all required ranks

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
             * @since 1.1.4
             *
             * @param bool      $meets_condition    Whatever if user meets condition
             * @param int       $user_id            The user ID
             * @param array     $args               Given args
             */
            if( apply_filters( 'gamipress_restrict_content_user_meets_condition_' . $condition, false, $user_id, $atts ) )
                return true;
            break;
    }

    // Reset the CT setup that has been switched to earnings table
    ct_reset_setup_table();

    return false;

}