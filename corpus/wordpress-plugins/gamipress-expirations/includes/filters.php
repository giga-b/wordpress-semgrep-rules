<?php
/**
 * Filters
 *
 * @package GamiPress\Expirations\Filters
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Check if user earning has assigned a post with the expiration metas
 *
 * @since  1.4.3
 *
 * @param  int      $user_earning_id  	The user earning ID
 * @param  array    $data               User earning data
 * @param  array    $meta               User earning meta data
 * @param  int      $user_id  	        The user ID
 */
function gamipress_expirations_on_insert_user_earning( $user_earning_id, $data, $meta, $user_id ) {

    $prefix = '_gamipress_expirations_';

    $post_id = $data['post_id'];

    // Bail if not post assigned
    if( $post_id === 0 ) {
        return;
    }

    $post_type = gamipress_get_post_type( $post_id );

    $allowed_post_types = array_merge( gamipress_get_achievement_types_slugs(), gamipress_get_rank_types_slugs() );
    $allowed_post_types[] = 'points-award';
    $allowed_post_types[] = 'points-deduct';
    $allowed_post_types[] = 'step';
    $allowed_post_types[] = 'rank-requirement';

    // Bail if the user earning post type is not allowed
    if( ! in_array( $post_type, $allowed_post_types ) ) {
        return;
    }

    $expiration = gamipress_get_post_meta( $post_id, $prefix . 'expiration', true );

    // Bail if item never expires
    if( $expiration === '' ) {
        return;
    }

    if( $expiration === 'date' ) {
        $date = gamipress_get_post_meta( $post_id, $prefix . 'date', true );

        if( ! gamipress_expirations_is_a_valid_date( $date, 'Y-m-d' ) ) {
            return;
        }

        $expiration_date = date( 'Y-m-d H:i:s', strtotime( $date ) );
    } else {
        $amount = absint( gamipress_get_post_meta( $post_id, $prefix . 'amount', true ) );

        $expiration_date = date( 'Y-m-d H:i:s', strtotime( "+{$amount}{$expiration}", current_time( 'timestamp' ) ) );
    }

    /**
     * Filter to override the user earning expiration date
     *
     * @since  1.4.3
     *
     * @param  string   $expiration_date  	The user earning expiration date
     * @param  int      $user_earning_id  	The user earning ID
     * @param  array    $data               User earning data
     * @param  array    $meta               User earning meta data
     * @param  int      $user_id  	        The user ID
     *
     * @return string                       The expiration date in Y-m-d H:i:s
     */
    $expiration_date = apply_filters( 'gamipress_expirations_insert_user_earning_expiration', $expiration_date, $user_earning_id, $data, $meta, $user_id );

    // Bail if expiration date has not been set
    if( ! gamipress_expirations_is_a_valid_date( $expiration_date ) ) {
        return;
    }

    // Update the user earning expiration
    ct_setup_table( 'gamipress_user_earnings' );
    ct_update_object_meta( $user_earning_id, $prefix . 'expiration_date', $expiration_date );
    ct_reset_setup_table();

}
add_action( 'gamipress_insert_user_earning', 'gamipress_expirations_on_insert_user_earning', 10, 4 );

/**
 * Columns rendering for user earnings list view on admin area
 *
 * @since  1.0.0
 *
 * @param string $column_name
 * @param integer $object_id
 */
function gamipress_expirations_user_earnings_custom_column( $column_name, $object_id ) {

    $prefix = '_gamipress_expirations_';

    if( $column_name !== 'date' ) {
        return;
    }

    ct_setup_table( 'gamipress_user_earnings' );

    // Setup vars
    $user_earning = ct_get_object( $object_id );

    // Update the user earning expiration
    $expiration_date = ct_get_object_meta( $object_id, $prefix . 'expiration_date', true );

    ct_reset_setup_table();

    if( empty( $expiration_date ) ) {
        return;
    }

    // Bail if expiration aborted
    if( gamipress_expirations_maybe_abort_expiration( $user_earning ) ) {
        return;
    }

    $expiration_time = strtotime( $expiration_date );

    ?>
    <br>
    <span><?php _e( 'Expires on', 'gamipress-expirations' ); ?> <strong><abbr title="<?php echo date( 'Y/m/d g:i:s a', $expiration_time ); ?>"><?php echo date( 'Y/m/d', $expiration_time ); ?></abbr></strong></span>
    <?php

}
add_action( 'manage_gamipress_user_earnings_custom_column', 'gamipress_expirations_user_earnings_custom_column', 10, 2 );

/**
 * Render earnings column at frontend
 *
 * @since 1.0.0
 *
 * @param string    $column_output  Default column output
 * @param string    $column_name    The column name
 * @param stdClass  $user_earning   The column name
 * @param array     $template_args  Template received arguments
 *
 * @return string
 */
function gamipress_expirations_earnings_render_column( $column_output, $column_name, $user_earning, $template_args ) {

    $prefix = '_gamipress_expirations_';

    if( $column_name !== 'date' ) {
        return $column_output;
    }

    $user_earning_id = $user_earning->user_earning_id;

    // Update the user earning expiration
    ct_setup_table( 'gamipress_user_earnings' );
    $expiration_date = ct_get_object_meta( $user_earning_id, $prefix . 'expiration_date', true );
    ct_reset_setup_table();

    if( empty( $expiration_date ) ) {
        return $column_output;
    }

    // Bail if expiration aborted
    if( gamipress_expirations_maybe_abort_expiration( $user_earning ) ) {
        return $column_output;
    }

    $expiration_time = strtotime( $expiration_date );

    ob_start(); ?>
    <br>
    <span><?php _e( 'Expires on', 'gamipress-expirations' ); ?> <strong><?php echo date_i18n( get_option( 'date_format' ), $expiration_time ); ?></strong></span>
    <?php $column_output .= ob_get_clean();

    return $column_output;

}
add_action( 'gamipress_earnings_render_column', 'gamipress_expirations_earnings_render_column', 10, 4 );

/**
 * Render expiration on achievements
 *
 * @since 1.0.0
 *
 * @param int       $achievement_id     The achievement ID
 * @param array     $template_args      Template arguments
 */
function gamipress_expirations_render_achievement_expiration( $achievement_id, $template_args ) {

    $prefix = '_gamipress_expirations_';

    // Determine the user to check
    if( isset( $template_args['user_id'] ) ) {
        $user_id = $template_args['user_id'];
    } else {
        $user_id = get_current_user_id();
    }

    if( $user_id === 0 ) {
        return;
    }

    $user_earning = gamipress_expirations_get_last_earning( array(
        'user_id' => absint( $user_id ),
        'post_id' => absint( $achievement_id ),
    ) );

    // Bail if user has not earned this item
    if( ! $user_earning ) {
        return;
    }

    ct_setup_table( 'gamipress_user_earnings' );
    $expiration_date = ct_get_object_meta( $user_earning->user_earning_id, $prefix . 'expiration_date', true );
    ct_reset_setup_table();

    // Bail if never expires
    if( empty( $expiration_date ) ) {
        return;
    }

    $expiration_time = strtotime( $expiration_date );

    ?>
    <div class="gamipress-expirations-expiration"><?php _e( 'Expires on', 'gamipress-expirations' ); ?> <strong><?php echo date_i18n( get_option( 'date_format' ), $expiration_time ); ?></strong></div>
    <?php

}
add_filter( 'gamipress_after_render_achievement', 'gamipress_expirations_render_achievement_expiration', 10, 2 );
add_filter( 'gamipress_after_single_achievement', 'gamipress_expirations_render_achievement_expiration', 10, 2 );

/**
 * Render expiration on ranks
 *
 * @since 1.0.0
 *
 * @param int       $rank_id            The rank ID
 * @param array     $template_args      Template arguments
 */
function gamipress_expirations_render_rank_expiration( $rank_id, $template_args ) {

    $prefix = '_gamipress_expirations_';

    // Determine the user to check
    if( isset( $template_args['user_id'] ) ) {
        $user_id = $template_args['user_id'];
    } else {
        $user_id = get_current_user_id();
    }

    if( $user_id === 0 ) {
        return;
    }

    $rank_type = gamipress_get_post_type( $rank_id );

    $user_rank_id = gamipress_get_user_rank_id( $user_id, $rank_type );

    // Bail if user is in a different rank
    if( $user_rank_id !== $rank_id ) {
        return;
    }

    $user_earning = gamipress_expirations_get_last_earning( array(
        'user_id' => absint( $user_id ),
        'post_id' => absint( $rank_id ),
    ) );

    // Bail if user has not earned this item
    if( ! $user_earning ) {
        return;
    }

    ct_setup_table( 'gamipress_user_earnings' );
    $expiration_date = ct_get_object_meta( $user_earning->user_earning_id, $prefix . 'expiration_date', true );
    ct_reset_setup_table();

    // Bail if never expires
    if( empty( $expiration_date ) ) {
        return;
    }

    $expiration_time = strtotime( $expiration_date );

    ?>
    <div class="gamipress-expirations-expiration"><?php _e( 'Expires on', 'gamipress-expirations' ); ?> <strong><?php echo date_i18n( get_option( 'date_format' ), $expiration_time ); ?></strong></div>
    <?php

}
add_filter( 'gamipress_after_render_rank', 'gamipress_expirations_render_rank_expiration', 10, 2 );
add_filter( 'gamipress_after_single_rank', 'gamipress_expirations_render_rank_expiration', 10, 2 );

/**
 * Filter to render expiration on requirements
 *
 * @since 1.0.0
 *
 * @param string    $title          Requirement title
 * @param WP_Post   $requirement    Requirement object
 * @param integer   $user_id        The user ID
 * @param array     $template_args  An array with the template args
 *
 * @return string
 */
function gamipress_expirations_requirement_title_display( $title, $requirement, $user_id, $template_args ) {

    $prefix = '_gamipress_expirations_';

    $user_earning = gamipress_expirations_get_last_earning( array(
        'user_id' => absint( $user_id ),
        'post_id' => absint( $requirement->ID ),
    ) );

    // Bail if user has not earned this item
    if( ! $user_earning ) {
        return $title;
    }

    if( gamipress_expirations_maybe_abort_expiration( $user_earning ) ) {
        return $title;
    }

    ct_setup_table( 'gamipress_user_earnings' );
    $expiration_date = ct_get_object_meta( $user_earning->user_earning_id, $prefix . 'expiration_date', true );
    ct_reset_setup_table();

    // Bail if never expires
    if( empty( $expiration_date ) ) {
        return $title;
    }

    $expiration_time = strtotime( $expiration_date );

    ob_start(); ?>
    <div class="gamipress-expirations-expiration"><?php _e( 'Expires on', 'gamipress-expirations' ); ?> <strong><?php echo date_i18n( get_option( 'date_format' ), $expiration_time ); ?></strong></div>
    <?php $title .= ob_get_clean();

    return $title;
}
add_filter( 'gamipress_points_award_title_display', 'gamipress_expirations_requirement_title_display', 10, 4 );
add_filter( 'gamipress_points_deduct_title_display', 'gamipress_expirations_requirement_title_display', 10, 4 );
add_filter( 'gamipress_step_title_display', 'gamipress_expirations_requirement_title_display', 10, 4 );
add_filter( 'gamipress_rank_requirement_title_display', 'gamipress_expirations_requirement_title_display', 10, 4 );

/**
 * Recalculate the expiration date for a user earning if option is checked
 *
 * @since 1.0.0
 *
 * @param int 	$user_id        The given user's ID
 * @param int 	$achievement_id The given achievement's post ID
 * @param int 	$earning_id     The user's earning ID
 */
function gamipress_expirations_recalculate_expiration_date( $user_id, $achievement_id, $earning_id ) {

    $prefix = '_gamipress_expirations_';

    $post_type = gamipress_get_post_type( $achievement_id );
    $is_rank = in_array( $post_type, gamipress_get_rank_types_slugs() );

    // Check if element revoked is a rank
    if( $is_rank ) {

        // Get the previous rank
        $prev_rank_id = gamipress_get_prev_rank_id( $achievement_id );

        // Bail if not previous rank found
        if( $prev_rank_id === 0 ) {
            return;
        }

        // Bail if previous rank does not have the option to recalculate
        if( ! (bool) gamipress_get_post_meta( $prev_rank_id, $prefix . 'recalculate', true ) ) {
            return;
        }

        ct_setup_table( 'gamipress_user_earnings' );

        // Query the last user earning for this rank
        $prev_rank_query = new CT_Query( array(
            'user_id' => $user_id,
            'post_id' => $prev_rank_id,
            'items_per_page' => 1
        ) );

        $prev_rank_earnings = $prev_rank_query->get_results();

        ct_reset_setup_table();

        // Bail if not previous earnings found
        if( count( $prev_rank_earnings ) === 0 ) {
            return;
        }

        $earning = $prev_rank_earnings[0];
        $user_earning_id = $earning->user_earning_id;
        $data = (array) $earning;
        $meta = array();

        // Force to recalculate the expiration date for this rank
        gamipress_expirations_on_insert_user_earning( $user_earning_id, $data, $meta, $user_id );

    }

}
add_action( 'gamipress_revoke_achievement_to_user', 'gamipress_expirations_recalculate_expiration_date', 10, 3 );

