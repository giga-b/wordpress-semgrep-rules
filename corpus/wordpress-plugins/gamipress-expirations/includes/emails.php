<?php
/**
 * Emails
 *
 * @package GamiPress\Expirations\Emails
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Checks for emails to send before expiration
 *
 * This function is only intended to be used by WordPress cron.
 *
 * To force the use of this function outside cron, set a code like:
 * define( 'DOING_CRON', true );
 * gamipress_expirations_check_expired_earnings();
 *
 * @since 1.0.0
 *
 * @param string $date A valid date in "Y-m-d H:i:s" format
 */
function gamipress_expirations_check_for_emails_before_expiration( $date = '' ) {

    global $wpdb;

    $prefix = '_gamipress_expirations_';

    if( empty( $date ) ) {
        $date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
    }

    $amount = absint( gamipress_expirations_get_option( 'before_amount', 15 ) );
    $period = gamipress_expirations_get_option( 'before_period', 'days' );

    $date = date( 'Y-m-d H:i:s', strtotime( "+{$amount}{$period}", strtotime( $date ) ) );

    $user_earnings 		= GamiPress()->db->user_earnings;
    $user_earnings_meta = GamiPress()->db->user_earnings_meta;

    // Get earnings that expires on this date
    $earnings = $wpdb->get_results( $wpdb->prepare(
        "SELECT ue.*
        FROM {$user_earnings} AS ue 
        LEFT JOIN {$user_earnings_meta} AS uem ON ( ue.user_earning_id = uem.user_earning_id ) 
        WHERE uem.meta_key = %s 
        AND uem.meta_value <= %s",
        $prefix . 'expiration_date',
        $date
    ) );

    foreach( $earnings as $user_earning ) {

        // Setup vars
        $user_earning_id = absint( $user_earning->user_earning_id );
        $user_id = absint( $user_earning->user_id );
        $post_id = absint( $user_earning->post_id );

        $disabled = false;

        /**
         * Filter to decide if email before expiration are disabled for this item
         *
         * @since 1.0.0
         *
         * @param bool  $disabled
         * @param int   $user_id
         * @param int   $post_id
         *
         * @return bool
         */
        $disabled = apply_filters( 'gamipress_expirations_email_before_expiration_disabled', $disabled, $user_id, $post_id );

        if( $disabled ) {
            return;
        }

        // Skip earning if expiration aborted
        if( gamipress_expirations_maybe_abort_expiration( $user_earning ) ) {
            continue;
        }

        // Update the user earning email sent meta
        ct_setup_table( 'gamipress_user_earnings' );
        $email_sent = ct_get_object_meta( $user_earning_id, $prefix . 'email_sent', true );
        ct_reset_setup_table();

        // Skip if email already sent
        if( (bool) $email_sent ) {
            continue;
        }

        $subject = '';
        $content = '';

        /**
         * Filter to decide the email before expiration subject
         *
         * @since 1.0.0
         *
         * @param string    $subject
         * @param int       $user_id
         * @param int       $post_id
         *
         * @return string
         */
        $subject = apply_filters( 'gamipress_expirations_email_before_expiration_subject', $subject, $user_id, $post_id );

        /**
         * Filter to decide the email before expiration content
         *
         * @since 1.0.0
         *
         * @param string    $content
         * @param int       $user_id
         * @param int       $post_id
         *
         * @return string
         */
        $content = apply_filters( 'gamipress_expirations_email_before_expiration_content', $content, $user_id, $post_id );

        $subject = do_shortcode( $subject );
        $content = do_shortcode( $content );

        // Skip if not subject or content provided
        if( empty( $subject ) || empty( $content ) ) {
            continue;
        }

        $user = get_userdata( $user_id );

        // Send the email to the user
        gamipress_send_email( $user->user_email, $subject, $content );

        // Update the user earning email sent meta
        ct_setup_table( 'gamipress_user_earnings' );
        ct_update_object_meta( $user_earning_id, $prefix . 'email_sent', '1' );
        ct_reset_setup_table();

    }

}
add_action( 'gamipress_expirations_five_minutes_cron', 'gamipress_expirations_check_for_emails_before_expiration' );
add_action( 'gamipress_expirations_hourly_cron', 'gamipress_expirations_check_for_emails_before_expiration' );

/**
 * Checks for emails to send after expiration
 *
 * @since 1.0.0
 *
 * @param int       $user_id            The user ID
 * @param int       $post_id            The post ID
 * @param int       $user_earning_id    The user earning ID
 * @param stdClass  $user_earning       The user earning object
 */
function gamipress_expirations_maybe_email_after_expiration( $user_id, $post_id, $user_earning_id, $user_earning ) {

    $disabled = false;

    /**
     * Filter to decide if email after expiration are disabled for this item
     *
     * @since 1.0.0
     *
     * @param bool  $disabled
     * @param int   $user_id
     * @param int   $post_id
     *
     * @return bool
     */
    $disabled = apply_filters( 'gamipress_expirations_email_after_expiration_disabled', $disabled, $user_id, $post_id );

    if( $disabled ) {
        return;
    }

    $subject = '';
    $content = '';

    /**
     * Filter to decide the email after expiration subject
     *
     * @since 1.0.0
     *
     * @param string    $subject
     * @param int       $user_id
     * @param int       $post_id
     *
     * @return string
     */
    $subject = apply_filters( 'gamipress_expirations_email_after_expiration_subject', $subject, $user_id, $post_id );

    /**
     * Filter to decide the email after expiration content
     *
     * @since 1.0.0
     *
     * @param string    $content
     * @param int       $user_id
     * @param int       $post_id
     *
     * @return string
     */
    $content = apply_filters( 'gamipress_expirations_email_after_expiration_content', $content, $user_id, $post_id );

    $subject = do_shortcode( $subject );
    $content = do_shortcode( $content );

    // Skip if not subject or content provided
    if( empty( $subject ) || empty( $content ) ) {
        return;
    }

    $user = get_userdata( $user_id );

    // Send the email to the user
    gamipress_send_email( $user->user_email, $subject, $content );

}
add_action( 'gamipress_expirations_earning_expired', 'gamipress_expirations_maybe_email_after_expiration', 10, 4 );

/**
 * Check if email is disabled for this item
 *
 * @since 1.0.0
 *
 * @param bool      $disabled
 * @param int       $user_id
 * @param int       $post_id
 *
 * @return bool
 */
function gamipress_expirations_is_emails_disabled( $disabled, $user_id, $post_id ) {

    $post_type = gamipress_get_post_type( $post_id );

    $prefix = ( strpos( current_filter(), 'before' ) ? 'before' : 'after' );
    $element = '';

    if( in_array( $post_type, gamipress_get_achievement_types_slugs() ) ) {
        $element = 'achievements';
    } else if( $post_type === 'step' ) {
        $element = 'steps';
    } else if( $post_type === 'points-award' ) {
        $element = 'points_awards';
    } else if( $post_type === 'points-deduct' ) {
        $element = 'points_deducts';
    } else if( in_array( $post_type, gamipress_get_rank_types_slugs() ) ) {
        $element = 'ranks';
    } else if( $post_type === 'rank-requirement' ) {
        $element = 'rank_requirements';
    }

    if( (bool) gamipress_expirations_get_option( $prefix . '_disable_' . $element ) ) {
        return true;
    }

    return $disabled;

}
add_filter( 'gamipress_expirations_email_before_expiration_disabled', 'gamipress_expirations_is_emails_disabled', 10, 3  );
add_filter( 'gamipress_expirations_email_after_expiration_disabled', 'gamipress_expirations_is_emails_disabled', 10, 3  );

/**
 * Filter the email subject and content
 *
 * @since 1.0.0
 *
 * @param string    $content
 * @param int       $user_id
 * @param int       $post_id
 *
 * @return string
 */
function gamipress_expirations_email_content( $content, $user_id, $post_id ) {

    $post_type = gamipress_get_post_type( $post_id );

    $prefix = ( strpos( current_filter(), 'before' ) ? 'before' : 'after' );
    $element = '';
    $suffix =  ( strpos( current_filter(), 'subject' ) ? 'subject' : 'content' );

    if( in_array( $post_type, gamipress_get_achievement_types_slugs() ) ) {
        $element = 'achievement';
    } else if( $post_type === 'step' ) {
        $element = 'step';
    } else if( $post_type === 'points-award' ) {
        $element = 'points_award';
    } else if( $post_type === 'points-deduct' ) {
        $element = 'points_deduct';
    } else if( in_array( $post_type, gamipress_get_rank_types_slugs() ) ) {
        $element = 'rank';
    } else if( $post_type === 'rank-requirement' ) {
        $element = 'rank_requirement';
    } else {
        return;
    }

    $content = gamipress_expirations_get_option( "{$prefix}_{$element}_{$suffix}" );

    $content = call_user_func_array( "gamipress_expirations_parse_{$element}_pattern", array( $content, $user_id, $post_id ) );

    return $content;

}
add_filter( 'gamipress_expirations_email_before_expiration_subject', 'gamipress_expirations_email_content', 10, 3  );
add_filter( 'gamipress_expirations_email_before_expiration_content', 'gamipress_expirations_email_content', 10, 3  );
add_filter( 'gamipress_expirations_email_after_expiration_subject', 'gamipress_expirations_email_content', 10, 3  );
add_filter( 'gamipress_expirations_email_after_expiration_content', 'gamipress_expirations_email_content', 10, 3  );