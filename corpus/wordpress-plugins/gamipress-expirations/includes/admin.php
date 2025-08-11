<?php
/**
 * Admin
 *
 * @package GamiPress\Expirations\Admin
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcut function to get plugin options
 *
 * @since  1.0.0
 *
 * @param string    $option_name
 * @param bool      $default
 *
 * @return mixed
 */
function gamipress_expirations_get_option( $option_name, $default = false ) {

    $prefix = 'gamipress_expirations_';

    return gamipress_get_option( $prefix . $option_name, $default );
}

/**
 * GamiPress Expirations Email Settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function gamipress_expirations_email_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'gamipress_expirations_';

    // Before expiration

    $meta_boxes['gamipress-expirations-emails-before-expiration-settings'] = array(
        'title' => gamipress_dashicon( 'clock' ) . __( 'Expirations: Emails before expiration', 'gamipress-expirations' ),
        'fields' => apply_filters( 'gamipress_expirations_emails_before_expiration_settings_fields', array(

            // General

            $prefix . 'before_amount' => array(
                'name' => __( 'Send emails before', 'gamipress-expirations' ),
                'type' => 'text',
                'default' => 15,
                'attributes' => array(
                    'type' => 'number',
                    'step' => '1',
                    'min' => '1',
                ),
            ),
            $prefix . 'before_period' => array(
                'name' => __( 'Period', 'gamipress-expirations' ),
                'type' => 'select',
                'options' => array(
                    'days'      => __( 'Days', 'gamipress-expirations' ),
                    'weeks'     => __( 'Weeks', 'gamipress-expirations' ),
                    'months'    => __( 'Months', 'gamipress-expirations' ),
                    'years'     => __( 'Years', 'gamipress-expirations' ),
                ),
                'default' => 'days'
            ),

            // Achievements

            $prefix . 'before_disable_achievements' => array(
                'name' => __( 'Disable before expiration emails for achievements', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not email users about achievements expiration.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'before_achievement_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'before_achievement_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_achievement_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Steps

            $prefix . 'before_disable_steps' => array(
                'name' => __( 'Disable before expiration emails for steps', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not email users about steps expiration.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'before_step_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'before_step_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_step_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Points awards

            $prefix . 'before_disable_points_awards' => array(
                'name' => __( 'Disable before expiration emails for points awards', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not notify users about points awards.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'before_points_award_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'before_points_award_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_points_award_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Points deducts

            $prefix . 'before_disable_points_deducts' => array(
                'name' => __( 'Disable before expiration emails for points deductions', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not notify users about points deductions.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'before_points_deduct_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'before_points_deduct_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_points_deduct_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Ranks

            $prefix . 'before_disable_ranks' => array(
                'name' => __( 'Disable before expiration emails for ranks', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not notify users about ranks reached.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'before_rank_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'before_rank_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_rank_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Rank Requirements

            $prefix . 'before_disable_rank_requirements' => array(
                'name' => __( 'Disable before expiration emails for rank requirements', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not notify to users about rank requirements completed.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'before_rank_requirement_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'before_rank_requirement_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_rank_requirement_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

        ) ),
        'tabs' => apply_filters( 'gamipress_expirations_emails_before_expiration_settings_tabs', array(
            'general' => array(
                'icon' => 'dashicons-admin-generic',
                'title' => __( 'General', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'before_amount',
                    $prefix . 'before_period',
                ),
            ),
            'achievement' => array(
                'icon' => 'dashicons-awards',
                'title' => __( 'Achievements', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'before_disable_achievements',
                    $prefix . 'before_achievement_subject',
                    $prefix . 'before_achievement_content',
                ),
            ),
            'steps' => array(
                'icon' => 'dashicons-editor-ol',
                'title' => __( 'Steps', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'before_disable_steps',
                    $prefix . 'before_step_subject',
                    $prefix . 'before_step_content',
                ),
            ),
            'points_awards' => array(
                'icon' => 'dashicons-star-filled',
                'title' => __( 'Points Awards', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'before_disable_points_awards',
                    $prefix . 'before_points_award_subject',
                    $prefix . 'before_points_award_content',
                ),
            ),
            'points_deducts' => array(
                'icon' => 'dashicons-star-empty',
                'title' => __( 'Points Deducts', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'before_disable_points_deducts',
                    $prefix . 'before_points_deduct_subject',
                    $prefix . 'before_points_deduct_content',
                ),
            ),
            'ranks' => array(
                'icon' => 'dashicons-rank',
                'title' => __( 'Ranks', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'before_disable_ranks',
                    $prefix . 'before_rank_subject',
                    $prefix . 'before_rank_content',
                ),
            ),
            'rank_requirements' => array(
                'icon' => 'dashicons-editor-ol',
                'title' => __( 'Rank Requirements', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'before_disable_rank_requirements',
                    $prefix . 'before_rank_requirement_subject',
                    $prefix . 'before_rank_requirement_content',
                ),
            ),
        ) ),
        'vertical_tabs' => true
    );

    // After expiration

    $meta_boxes['gamipress-expirations-emails-after-expiration-settings'] = array(
        'title' => gamipress_dashicon( 'clock' ) . __( 'Expirations: Emails after expiration', 'gamipress-expirations' ),
        'fields' => apply_filters( 'gamipress_expirations_emails_after_expiration_settings_fields', array(

            // Achievements

            $prefix . 'after_disable_achievements' => array(
                'name' => __( 'Disable after expiration emails for achievements', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not email users about achievements expiration.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'after_achievement_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'after_achievement_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_achievement_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Steps

            $prefix . 'after_disable_steps' => array(
                'name' => __( 'Disable after expiration emails for steps', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not email users about steps expiration.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'after_step_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'after_step_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_step_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Points awards

            $prefix . 'after_disable_points_awards' => array(
                'name' => __( 'Disable after expiration emails for points awards', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not notify users about points awards.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'after_points_award_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'after_points_award_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_points_award_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Points deducts

            $prefix . 'after_disable_points_deducts' => array(
                'name' => __( 'Disable after expiration emails for points deductions', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not notify users about points deductions.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'after_points_deduct_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'after_points_deduct_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_points_deduct_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Ranks

            $prefix . 'after_disable_ranks' => array(
                'name' => __( 'Disable after expiration emails for ranks', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not notify users about ranks reached.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'after_rank_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'after_rank_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_rank_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

            // Rank Requirements

            $prefix . 'after_disable_rank_requirements' => array(
                'name' => __( 'Disable after expiration emails for rank requirements', 'gamipress-expirations' ),
                'desc' => __( 'Check this option to do not notify to users about rank requirements completed.', 'gamipress-expirations' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'after_rank_requirement_subject' => array(
                'name' => __( 'Email subject', 'gamipress-expirations' ),
                'desc' => __( 'The email subject.', 'gamipress-expirations' ),
                'type' => 'text',
            ),
            $prefix . 'after_rank_requirement_content' => array(
                'name' => __( 'Email content', 'gamipress-expirations' ),
                'desc' => __( 'The email content. Available tags:', 'gamipress-expirations' )
                    . gamipress_expirations_get_rank_requirement_pattern_tags_html(),
                'type' => 'wysiwyg',
            ),

        ) ),
        'tabs' => apply_filters( 'gamipress_expirations_emails_after_expiration_settings_tabs', array(
            'achievement' => array(
                'icon' => 'dashicons-awards',
                'title' => __( 'Achievements', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'after_disable_achievements',
                    $prefix . 'after_achievement_subject',
                    $prefix . 'after_achievement_content',
                ),
            ),
            'steps' => array(
                'icon' => 'dashicons-editor-ol',
                'title' => __( 'Steps', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'after_disable_steps',
                    $prefix . 'after_step_subject',
                    $prefix . 'after_step_content',
                ),
            ),
            'points_awards' => array(
                'icon' => 'dashicons-star-filled',
                'title' => __( 'Points Awards', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'after_disable_points_awards',
                    $prefix . 'after_points_award_subject',
                    $prefix . 'after_points_award_content',
                ),
            ),
            'points_deducts' => array(
                'icon' => 'dashicons-star-empty',
                'title' => __( 'Points Deducts', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'after_disable_points_deducts',
                    $prefix . 'after_points_deduct_subject',
                    $prefix . 'after_points_deduct_content',
                ),
            ),
            'ranks' => array(
                'icon' => 'dashicons-rank',
                'title' => __( 'Ranks', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'after_disable_ranks',
                    $prefix . 'after_rank_subject',
                    $prefix . 'after_rank_content',
                ),
            ),
            'rank_requirements' => array(
                'icon' => 'dashicons-editor-ol',
                'title' => __( 'Rank Requirements', 'gamipress-expirations' ),
                'fields' => array(
                    $prefix . 'after_disable_rank_requirements',
                    $prefix . 'after_rank_requirement_subject',
                    $prefix . 'after_rank_requirement_content',
                ),
            ),
        ) ),
        'vertical_tabs' => true
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_email_meta_boxes', 'gamipress_expirations_email_settings_meta_boxes' );

/**
 * Register custom meta boxes used throughout GamiPress
 *
 * @since  1.0.0
 */
function gamipress_expirations_meta_boxes() {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_expirations_';

    // Achievements
    gamipress_add_meta_box(
        'gamipress-expirations-achievement-expiration',
        __( 'Expiration', 'gamipress-expirations' ),
        gamipress_get_achievement_types_slugs(),
        gamipress_expirations_get_expiration_fields( $prefix ),
        array(
            'context' => 'side',
        )
    );

    $rank_fields = gamipress_expirations_get_expiration_fields( $prefix );

    $rank_fields[$prefix . 'recalculate'] = array(
        'name' 	    => __( 'Recalculate', 'gamipress-expirations' ),
        'desc' 	    => __( 'Recalculate the expiration for this rank when the previous rank expires. For example, if the 4th rank expires or gets revoked, the expiration date for the 3rd rank will get recalculated at the moment the user gets assigned again to the 3rd rank.', 'gamipress-expirations' ),
        'type' 	    => 'checkbox',
        'classes' 	=> 'gamipress-switch gamipress-expirations-recalculate',
    );

    // Ranks
    gamipress_add_meta_box(
        'gamipress-expirations-rank-expiration',
        __( 'Expiration', 'gamipress-expirations' ),
        gamipress_get_rank_types_slugs(),
        $rank_fields,
        array(
            'context' => 'side',
        )
    );

}
add_action( 'cmb2_admin_init', 'gamipress_expirations_meta_boxes' );

/**
 * Helper to get the expiration fields
 *
 * @since  1.0.0
 *
 * @param string $prefix
 *
 * @return array
 */
function gamipress_expirations_get_expiration_fields( $prefix = '' ) {

    return array(
        $prefix . 'expiration' => array(
            'name' 	    => __( 'Expiration', 'gamipress-expirations' ),
            'type' 	    => 'select',
            'classes' 	=> 'gamipress-expirations-expiration',
            'options' 	=> apply_filters( 'gamipress_expirations_expiration_options', array(
                ''          => __( 'Never expires', 'gamipress-expirations' ),
                'hours'     => __( 'Hours', 'gamipress-expirations' ),
                'days'      => __( 'Days', 'gamipress-expirations' ),
                'weeks'     => __( 'Weeks', 'gamipress-expirations' ),
                'months'    => __( 'Months', 'gamipress-expirations' ),
                'years'     => __( 'Years', 'gamipress-expirations' ),
                'date'      => __( 'Specific date', 'gamipress-expirations' ),
            ) ),
        ),
        $prefix . 'amount' => array(
            'name' 	    => __( 'Amount', 'gamipress-expirations' ),
            'desc' 	    => sprintf( __( '%s later', 'gamipress-expirations' ), '<span></span>' ),
            'type' 	    => 'text_small',
            'classes' 	=> 'gamipress-expirations-amount',
            'attributes' => array(
                'type' => 'number',
                'step' => '1',
                'min' => '1',
            ),
            'default' => '1',
        ),
        $prefix . 'date' => array(
            'name' 	    => __( 'Expires on', 'gamipress-expirations' ),
            'desc' 	    => __( 'A date in format "year-month-day" like 2017-07-20.', 'gamipress-expirations' ),
            'type' 	    => 'text',
            'classes' 	=> 'gamipress-expirations-date',
            'default'   => date( 'Y-m-d', strtotime( '+1 year', current_time( 'timestamp' ) ) )
        ),
    );
}


/**
 * GamiPress Expirations Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_expirations_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-expirations-license'] = array(
        'title' => __( 'GamiPress Expirations', 'gamipress-expirations' ),
        'fields' => array(
            'gamipress_expirations_license' => array(
                'name' => __( 'License', 'gamipress-expirations' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_EXPIRATIONS_FILE,
                'item_name' => 'Expirations',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_expirations_licenses_meta_boxes' );

/**
 * GamiPress Expirations automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_expirations_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-expirations'] = __( 'Expirations', 'gamipress-expirations' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_expirations_automatic_updates' );