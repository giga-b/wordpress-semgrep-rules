<?php
/**
 * Admin
 *
 * @package GamiPress\Time_Based_Rewards\Admin
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
function gamipress_time_based_rewards_get_option( $option_name, $default = false ) {

    $prefix = 'gamipress_time_based_rewards_';

    return gamipress_get_option( $prefix . $option_name, $default );
}

/**
 * Adds the admin menu
 *
 * @since 1.0.0
 */
function gamipress_time_based_rewards_admin_menu() {
    $minimum_role = gamipress_get_manager_capability();
    add_submenu_page( 'gamipress', __( 'Time-based Rewards', 'gamipress-time-based-rewards' ), __( 'Time-based Rewards', 'gamipress-time-based-rewards' ), $minimum_role, 'edit.php?post_type=time-based-reward', false );
}
add_action( 'admin_menu', 'gamipress_time_based_rewards_admin_menu', 20 );

/**
 * Admin bar menu
 *
 * @since 1.0.0
 *
 * @param WP_Admin_Bar $wp_admin_bar
 */
function gamipress_time_based_rewards_admin_bar_menu( $wp_admin_bar ) {

    // - Time-based Rewards
    $wp_admin_bar->add_node( array(
        'id'     => 'gamipress-time-based-rewards',
        'title'  => __( 'Time-based Rewards', 'gamipress-time-based-rewards' ),
        'parent' => 'gamipress-add-ons',
        'href'   => admin_url( 'edit.php?post_type=time-based-reward' )
    ) );

}
add_action( 'admin_bar_menu', 'gamipress_time_based_rewards_admin_bar_menu', 999 );

/**
 * Settings meta boxes
 *
 * @since  1.0.0
 *
 * @param array $meta_boxes
 *
 * @return array
 */
function gamipress_time_based_rewards_settings_meta_boxes( $meta_boxes ) {

    $prefix = 'gamipress_time_based_rewards_';

    $meta_boxes['gamipress-time-based-rewards-settings'] = array(
        'title' => gamipress_dashicon( 'clock' ) . __( 'Time-based Rewards', 'gamipress-time-based-rewards' ),
        'fields' => apply_filters( 'gamipress_time_based_rewards_settings_fields', array(

            // General

            $prefix . 'thousands_separator' => array(
                'name' => __( 'Thousands separator', 'gamipress-time-based-rewards' ),
                'desc' => __( 'The symbol (usually , or .) to separate thousands (used on rewards quantities).', 'gamipress-time-based-rewards' ),
                'type' => 'text_small',
                'default' => ','
            ),

            $prefix . 'claim_button_text' => array(
                'name' => __( 'Claim Button Text', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the default claim button text (by default: "Claim"). You can override this option on each time-based reward.', 'gamipress-time-based-rewards' ),
                'type' => 'text',
                'default' => __( 'Claim', 'gamipress-time-based-rewards' ),
            ),
            $prefix . 'next_reward_text' => array(
                'name' => __( 'Next Reward Text', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the default next reward text (by default: "Next reward in:"). You can override this option on each time-based reward.', 'gamipress-time-based-rewards' ),
                'type' => 'text',
                'default' => __( 'Next reward in:', 'gamipress-time-based-rewards' ),
            ),
            $prefix . 'guest_message' => array(
                'name' => __( 'Guest Message', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the default text for visitors commonly recommending to log in to claim the reward (by default: "Log in to claim"). You can override this option on each time-based reward.', 'gamipress-time-based-rewards' ),
                'type' => 'wysiwyg',
                'options' => array(
                    'textarea_rows' => 5,
                ),
                'default' => __( 'Log in to claim', 'gamipress-time-based-rewards' ),
            ),
            $prefix . 'popup_content' => array(
                'name' => __( 'Pop-up Content', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the default pop-up content (displayed when user claims a time-based reward). You can override this option on each time-based reward. Available tags:', 'gamipress-time-based-rewards' )
                . ' ' . gamipress_time_based_rewards_get_popup_pattern_tags_html(),
                'type' => 'wysiwyg',
                'options' => array(
                    'textarea_rows' => 5,
                ),
                'default' => __( '<h2>Congratulations {user}!</h2>', 'gamipress-time-based-rewards' ) . "\n"
                . __( 'You got:', 'gamipress-time-based-rewards' ) . "\n"
                . '{rewards}',
            ),
            $prefix . 'no_rewards_content' => array(
                'name' => __( 'No Rewards Content', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the default pop-up content when there is no rewards available (displayed when user claims a time-based reward). You can override this option on each time-based reward. Available tags:', 'gamipress-time-based-rewards' )
                    . ' ' . gamipress_time_based_rewards_get_popup_pattern_tags_html(),
                'type' => 'wysiwyg',
                'options' => array(
                    'textarea_rows' => 5,
                ),
                'default' => __( '<h2>Sorry {user}</h2>', 'gamipress-time-based-rewards' ) . "\n"
                    . __( 'There are no rewards available to earn', 'gamipress-time-based-rewards' ),
            ),
            $prefix . 'popup_button_text' => array(
                'name' => __( 'Pop-up Button Text', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the default pop-up button text (by default: "Ok!"). You can override this option on each time-based reward.', 'gamipress-time-based-rewards' ),
                'type' => 'text',
                'default' => __( 'Ok!', 'gamipress-time-based-rewards' ),
            ),

            // Post Type

            $prefix . 'post_type_title' => array(
                'name' => __( 'Time-based Reward Post Type', 'gamipress-time-based-rewards' ),
                'desc' => __( 'From this settings you can modify the default configuration of the time-based reward post type.', 'gamipress-time-based-rewards' ),
                'type' => 'title',
            ),
            $prefix . 'slug' => array(
                'name' => __( 'Slug', 'gamipress-time-based-rewards' ),
                'desc' => '<span class="gamipress-time-based-rewards-full-slug hide-if-no-js">' . site_url() . '/<strong class="gamipress-time-based-rewards-slug"></strong>/</span>',
                'type' => 'text',
                'default' => 'time-based-rewards',
            ),
            $prefix . 'public' => array(
                'name' => __( 'Public', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Check this option if you want to allow to your visitors access to a time-based reward as a page. Not checking this option will make time-based reward just visible through shortcodes or widgets.', 'gamipress-time-based-rewards' ),
                'type' => 'checkbox',
                'classes' => 'gamipress-switch',
            ),
            $prefix . 'supports' => array(
                'name' => __( 'Supports', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Check the features you want to add to the time-based reward post type.', 'gamipress-time-based-rewards' ),
                'type' => 'multicheck',
                'classes' => 'gamipress-switch',
                'options' => array(
                    'title'             => __( 'Title' ),
                    'editor'            => __( 'Editor' ),
                    'author'            => __( 'Author' ),
                    'thumbnail'         => __( 'Thumbnail' ) . ' (' . __( 'Featured Image' ) . ')',
                    'excerpt'           => __( 'Excerpt' ),
                    'trackbacks'        => __( 'Trackbacks' ),
                    'custom-fields'     => __( 'Custom Fields' ),
                    'comments'          => __( 'Comments' ),
                    'revisions'         => __( 'Revisions' ),
                    'page-attributes'   => __( 'Page Attributes' ),
                    'post-formats'      => __( 'Post Formats' ),
                ),
                'default' => array( 'title', 'editor', 'excerpt' )
            ),
        ) ),
        'tabs' => apply_filters( 'gamipress_time_based_rewards_settings_tabs', array(
            'general' => array(
                'icon' => 'dashicons-admin-generic',
                'title' => __( 'General', 'gamipress-time-based-rewards' ),
                'fields' => array(
                    $prefix . 'thousands_separator',
                    $prefix . 'claim_button_text',
                    $prefix . 'next_reward_text',
                    $prefix . 'guest_message',
                    $prefix . 'popup_content',
                    $prefix . 'no_rewards_content',
                    $prefix . 'popup_button_text',
                ),
            ),
            'post_type' => array(
                'icon' => 'dashicons-admin-post',
                'title' => __( 'Post Type', 'gamipress-time-based-rewards' ),
                'fields' => array(
                    $prefix . 'post_type_title',
                    $prefix . 'slug',
                    $prefix . 'public',
                    $prefix . 'supports'
                ),
            ),
        ) ),
        'vertical_tabs' => true
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_addons_meta_boxes', 'gamipress_time_based_rewards_settings_meta_boxes' );

/**
 * Licensing meta box
 *
 * @since  1.0.0
 *
 * @param $meta_boxes
 *
 * @return mixed
 */
function gamipress_time_based_rewards_licenses_meta_boxes( $meta_boxes ) {

    $meta_boxes['gamipress-time-based-rewards-license'] = array(
        'title' => __( 'Time-based Rewards', 'gamipress-time-based-rewards' ),
        'fields' => array(
            'gamipress_time_based_rewards_license' => array(
                'name' => __( 'License', 'gamipress-time-based-rewards' ),
                'type' => 'edd_license',
                'file' => GAMIPRESS_TIME_BASED_REWARDS_FILE,
                'item_name' => 'Time-based Rewards',
            ),
        )
    );

    return $meta_boxes;

}
add_filter( 'gamipress_settings_licenses_meta_boxes', 'gamipress_time_based_rewards_licenses_meta_boxes' );

/**
 * Register custom meta boxes
 *
 * @since  1.0.0
 */
function gamipress_time_based_rewards_meta_boxes() {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_gamipress_time_based_rewards_';

    // Configuration
    gamipress_add_meta_box(
        'time-based-reward-configuration',
        __( 'Configuration', 'gamipress-time-based-rewards' ),
        'time-based-reward',
        array(
            $prefix . 'recurrence' => array(
                'name' 	         => __( 'Recurrence', 'gamipress-time-based-rewards' ),
                'desc' 	         => __( 'Set the recurrence time in which the user can get this reward again.', 'gamipress-time-based-rewards' )
                . '<br>' . __( '<strong>Note:</strong> This field defines how much time an user needs to wait to earn it again, not defines an specific hour, for example, setting this field to 2:30:00 means that you want to allow to claim it every 2 hours and 30 minutes.', 'gamipress-time-based-rewards' )
                . '<br>' . __( '<strong>Note:</strong> First time is based on the time-based reward publication date.', 'gamipress-time-based-rewards' ),
                'type' 	         => 'time',
            ),
            $prefix . 'claim_button_text' => array(
                'name' => __( 'Claim Button Text', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the claim button text. Leave blank to use the text setup on settings.', 'gamipress-time-based-rewards' ),
                'type' => 'text',
            ),
            $prefix . 'next_reward_text' => array(
                'name' => __( 'Next Reward Text', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the next reward text. Leave blank to use the text setup on settings.', 'gamipress-time-based-rewards' ),
                'type' => 'text',
            ),
            $prefix . 'guest_message' => array(
                'name' => __( 'Guest Message', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the text for visitors commonly recommending to log in to claim the reward. Leave blank to use the text setup on settings.', 'gamipress-time-based-rewards' ),
                'type' => 'wysiwyg',
                'options' => array(
                    'textarea_rows' => 5,
                ),
            ),
            $prefix . 'popup_content' => array(
                'name' => __( 'Pop-up Content', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the pop-up content (displayed when user claims a time-based reward). Leave blank to use the text setup on settings. Available tags:', 'gamipress-time-based-rewards' )
                . ' ' . gamipress_time_based_rewards_get_popup_pattern_tags_html(),
                'type' => 'wysiwyg',
                'options' => array(
                    'textarea_rows' => 5,
                ),
            ),
            $prefix . 'no_rewards_content' => array(
                'name' => __( 'No Rewards Content', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the pop-up content when there is no rewards available (displayed when user claims a time-based reward). Leave blank to use the text setup on settings. Available tags:', 'gamipress-time-based-rewards' )
                    . ' ' . gamipress_time_based_rewards_get_popup_pattern_tags_html(),
                'type' => 'wysiwyg',
                'options' => array(
                    'textarea_rows' => 5,
                ),
            ),
            $prefix . 'popup_button_text' => array(
                'name' => __( 'Pop-up Button Text', 'gamipress-time-based-rewards' ),
                'desc' => __( 'Set the pop-up button text. Leave blank to use the text setup on settings.', 'gamipress-time-based-rewards' ),
                'type' => 'text',
            ),
        ),
        array( 'priority' => 'high', )
    );

    // Rewards
    gamipress_add_meta_box(
        'time-based-reward-rewards',
        __( 'Rewards', 'gamipress-time-based-rewards' ),
        'time-based-reward',
        array(
            $prefix . 'rewards' => array(
                'type' 	=> 'group',
                'desc' 	=> __( 'Available tags for the label field:', 'gamipress-time-based-rewards' )
                . ' ' . gamipress_time_based_rewards_get_pattern_tags_html(),
                'options'     => array(
                    'group_title'   => __( 'Reward {#}', 'gamipress-time-based-rewards' ),
                    'add_button'    => __( 'Add Reward', 'gamipress-time-based-rewards' ),
                    'remove_button' => __( 'Remove Reward', 'gamipress-time-based-rewards' ),
                ),
                'fields' => apply_filters( 'gamipress_time_based_rewards_reward_fields', array(
                    'post_type' => array(
                        'name' 	=> __( 'Reward Type', 'gamipress-time-based-rewards' ),
                        'type' => 'advanced_select',
                        'options_cb' => 'gamipress_time_based_rewards_reward_post_type_options_cb',
                    ),
                    'always' => array(
                        'name' 	=> __( 'Always included', 'gamipress-time-based-rewards' ),
                        'type' => 'checkbox',
                        'classes' => 'gamipress-switch',
                    ),
                    'min' => array(
                        'name' 	=> __( 'Min', 'gamipress-time-based-rewards' ),
                        'type' => 'text',
                        'attributes' => array(
                            'type' => 'number',
                            'placeholder' => '0'
                        ),
                        'default' => '0'
                    ),
                    'max' => array(
                        'name' 	=> __( 'Max', 'gamipress-time-based-rewards' ),
                        'type' => 'text',
                        'attributes' => array(
                            'type' => 'number',
                            'placeholder' => '0'
                        ),
                        'default' => '0'
                    ),
                    'achievement_id' => array(
                        'name' 	=> __( 'Achievement', 'gamipress-time-based-rewards' ),
                        'type' => 'advanced_select',
                        'classes' 	        => 'gamipress-post-selector',
                        'attributes' 	    => array(
                            'data-post-type' => implode( ',',  gamipress_get_achievement_types_slugs() ),
                            'data-placeholder' => __( 'Select an achievement', 'gamipress-time-based-rewards' ),
                        ),
                        'options_cb' => 'gamipress_options_cb_posts',
                    ),
                    'rank_id' => array(
                        'name' 	=> __( 'Rank', 'gamipress-time-based-rewards' ),
                        'desc' 	=> __( '<strong>Note:</strong> Ranks only can be awarded 1 time, for that there isn\'t min and max fields.', 'gamipress-time-based-rewards' )
                        . '<br>' . __( '<strong>Note:</strong> If user is on a higher priority rank never will be downgrade to a lower rank.', 'gamipress-time-based-rewards' ),
                        'type' => 'advanced_select',
                        'classes' 	        => 'gamipress-post-selector',
                        'attributes' 	    => array(
                            'data-post-type' => implode( ',',  gamipress_get_rank_types_slugs() ),
                            'data-placeholder' => __( 'Select a rank', 'gamipress-time-based-rewards' ),
                        ),
                        'options_cb' => 'gamipress_options_cb_posts',
                    ),
                    'achievement_type' => array(
                        'name' 	=> __( 'Achievement Type', 'gamipress-time-based-rewards' ),
                        'desc' 	=> __( 'Choose the achievement type you want to allow as a random achievement reward. By default, all.', 'gamipress-time-based-rewards' ),
                        'type' => 'advanced_select',
                        'classes' => 'gamipress-selector',
                        'options_cb' => 'gamipress_options_cb_achievement_types',
                    ),
                    'label' => array(
                        'name' 	=> __( 'Label', 'gamipress-time-based-rewards' ),
                        'type' => 'text',
                    ),

                ) ),
            ),
        ),
        array( 'priority' => 'high', )
    );

    // Time-based Reward Shortcode
    gamipress_add_meta_box(
        'time-based-reward-shortcode',
        __( 'Time-based Reward Shortcode', 'gamipress-time-based-rewards' ),
        'time-based-reward',
        array(
            $prefix . 'shortcode' => array(
                'desc' 	        => __( 'Place this shortcode anywhere to display this time-based reward.', 'gamipress-time-based-rewards' ),
                'type' 	        => 'text',
                'attributes'    => array(
                    'readonly'  => 'readonly',
                    'onclick'   => 'this.focus(); this.select();'
                ),
                'default_cb'    => 'gamipress_time_based_rewards_shortcode_field_default_cb'
            ),
        ),
        array(
            'context'  => 'side',
            'priority' => 'default'
        )
    );

}
add_action( 'cmb2_admin_init', 'gamipress_time_based_rewards_meta_boxes' );

/**
 * Automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_time_based_rewards_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress-time-based-rewards'] = __( 'Time-based Rewards', 'gamipress-time-based-rewards' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_time_based_rewards_automatic_updates' );

// Reward type options
function gamipress_time_based_rewards_reward_post_type_options_cb() {

    $options = array();

    $points_types = gamipress_get_points_types();
    $achievement_types = gamipress_get_achievement_types();
    $rank_types = gamipress_get_rank_types();

    // Points types
    if( ! empty( $points_types ) ) {

        $options['Points Types'] = array();

        foreach( $points_types as $slug => $data ) {
            $options['Points Types'][$slug] = $data['plural_name'];
        }

    }

    // Achievement types
    if( ! empty( $achievement_types ) ) {

        $options['Achievement Types'] = array();

        foreach( $achievement_types as $slug => $data ) {
            $options['Achievement Types'][$slug] = $data['singular_name'];
        }

    }

    // Rank types
    if( ! empty( $rank_types ) ) {

        $options['Rank Types'] = array();

        foreach( $rank_types as $slug => $data ) {
            $options['Rank Types'][$slug] = $data['singular_name'];
        }

    }

    $options['Others'] = array(
        'random_achievement' => __( 'Random Achievement', 'gamipress-time-based-rewards' )
    );

    return $options;

}

// Shortcode field default cb
function gamipress_time_based_rewards_shortcode_field_default_cb( $field_args, $field ) {
    return '[gamipress_time_based_reward id="' . $field->object_id . '"]';
}