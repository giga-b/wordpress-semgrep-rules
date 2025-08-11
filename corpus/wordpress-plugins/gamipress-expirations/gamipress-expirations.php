<?php
/**
 * Plugin Name:     GamiPress - Expirations
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-expirations
 * Description:     Set an expiration date to your points, achievements and ranks.
 * Version:         1.0.8
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-expirations
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\Expirations
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_Expirations {

    /**
     * @var         GamiPress_Expirations $instance The one true GamiPress_Expirations
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_Expirations self::$instance The one true GamiPress_Expirations
     */
    public static function instance() {
        if( !self::$instance ) {
            self::$instance = new GamiPress_Expirations();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function constants() {
        // Plugin version
        define( 'GAMIPRESS_EXPIRATIONS_VER', '1.0.8' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_EXPIRATIONS_GAMIPRESS_MIN_VER', '3.0.0' );

        // Plugin file
        define( 'GAMIPRESS_EXPIRATIONS_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_EXPIRATIONS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_EXPIRATIONS_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if( $this->meets_requirements() ) {

            require_once GAMIPRESS_EXPIRATIONS_DIR . 'includes/admin.php';
            require_once GAMIPRESS_EXPIRATIONS_DIR . 'includes/emails.php';
            require_once GAMIPRESS_EXPIRATIONS_DIR . 'includes/functions.php';
            require_once GAMIPRESS_EXPIRATIONS_DIR . 'includes/filters.php';
            require_once GAMIPRESS_EXPIRATIONS_DIR . 'includes/requirements.php';
            require_once GAMIPRESS_EXPIRATIONS_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_EXPIRATIONS_DIR . 'includes/template-functions.php';

        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * Activation hook for the plugin.
     *
     * @since  1.0.0
     */
    public static function activate() {

        GamiPress_Expirations::instance();

        // Setup the 5 minutes cron event
        if ( ! wp_next_scheduled( 'gamipress_expirations_five_minutes_cron' ) ) {
            wp_schedule_event( time(), 'five_minutes', 'gamipress_expirations_five_minutes_cron' );
        }

        // Setup the daily cron event
        if ( ! wp_next_scheduled( 'gamipress_expirations_hourly_cron' ) ) {
            wp_schedule_event( time(), 'hourly', 'gamipress_expirations_hourly_cron' );
        }

        // Get stored version
        if( gamipress_is_network_wide_active() ) {
            $stored_version = get_site_option( 'gamipress_settings', '1.0.0' );
        } else {
            $stored_version = get_option( 'gamipress_expirations_version', '1.0.0' );
        }

        // Get GamiPress options
        if( gamipress_is_network_wide_active() ) {
            $gamipress_settings = get_site_option( 'gamipress_settings', array() );
        } else {
            $gamipress_settings = get_option( 'gamipress_settings', array() );
        }

        // Initialize default settings
        $default_settings = array(
            // Achievements
            'before_achievement_subject' => __( 'The {achievement_type} {title} expires soon', 'gamipress-expirations' ),
            'before_achievement_content' => __( 'The {achievement_type} {link} expires in 15 days', 'gamipress-expirations' ),
            'after_achievement_subject' => __( 'The {achievement_type} {title} has expired', 'gamipress-expirations' ),
            'after_achievement_content' => __( 'The {achievement_type} {link} has expired, you need to complete its requirements to unlock it again', 'gamipress-expirations' ),
            // Steps
            'before_step_subject' => __( 'The requirement "{label}" of the {achievement_type} {achievement_title} expires soon', 'gamipress-expirations' ),
            'before_step_content' => __( 'The requirement "{label}" of the {achievement_type} {achievement_link} expires in 15 days', 'gamipress-expirations' ),
            'after_step_subject' => __( 'The requirement "{label}" of the {achievement_type} {achievement_title} has expired', 'gamipress-expirations' ),
            'after_step_content' => __( 'The requirement "{label}" of the {achievement_type} {achievement_link} has expired, you need to complete this requirement again', 'gamipress-expirations' ),
            // Points awards
            'before_points_award_subject' => __( '{points} {points_label} expires soon', 'gamipress-expirations' ),
            'before_points_award_content' => __( '{points} {points_label} for completing "{label}" expires in 15 days', 'gamipress-expirations' ),
            'after_points_award_subject' => __( '{points} {points_label} has expired', 'gamipress-expirations' ),
            'after_points_award_content' => __( '{points} {points_label} for completing "{label}" has expired, you need to complete its requirements to unlock it again', 'gamipress-expirations' ),
            // Points deducts
            'before_points_deduct_subject' => __( '{points} {points_label} expires soon', 'gamipress-expirations' ),
            'before_points_deduct_content' => __( '{points} {points_label} for completing "{label}" expires in 15 days', 'gamipress-expirations' ),
            'after_points_deduct_subject' => __( '{points} {points_label} has expired', 'gamipress-expirations' ),
            'after_points_deduct_content' => __( '{points} {points_label} for completing "{label}" has expired, you need to complete its requirements to unlock it again', 'gamipress-expirations' ),
            // Ranks
            'before_rank_subject' => __( 'The {rank_type} {title} expires soon', 'gamipress-expirations' ),
            'before_rank_content' => __( 'The {rank_type} {link} expires in 15 days', 'gamipress-expirations' ),
            'after_rank_subject' => __( 'The {rank_type} {title} has expired', 'gamipress-expirations' ),
            'after_rank_content' => __( 'The {rank_type} {link} has expired, you need to complete its requirements to unlock it again', 'gamipress-expirations' ),
            // Rank requirements
            'before_rank_requirement_subject' => __( 'The requirement "{label}" of the {rank_type} {rank_title} expires soon', 'gamipress-expirations' ),
            'before_rank_requirement_content' => __( 'The requirement "{label}" of the {rank_type} {rank_link} expires in 15 days', 'gamipress-expirations' ),
            'after_rank_requirement_subject' => __( 'The requirement "{label}" of the {rank_type} {rank_title} has expired', 'gamipress-expirations' ),
            'after_rank_requirement_content' => __( 'The requirement "{label}" of the {rank_type} {rank_link} has expired, you need to complete this requirement again', 'gamipress-expirations' ),
        );

        // Add-on settings prefix
        $prefix = 'gamipress_expirations_';

        foreach( $default_settings as $setting => $value ) {

            // If setting not exists, update it
            if( ! isset( $gamipress_settings[$prefix . $setting] ) ) {
                $gamipress_settings[$prefix . $setting] = $value;
            }

        }

        // Update GamiPress options
        if( gamipress_is_network_wide_active() ) {
            update_site_option( 'gamipress_settings', $gamipress_settings );
        } else {
            update_option( 'gamipress_settings', $gamipress_settings );
        }

        // Updated stored version
        if( gamipress_is_network_wide_active() ) {
            update_site_option( 'gamipress_expirations_version', GAMIPRESS_EXPIRATIONS_VER );
        } else {
            update_option( 'gamipress_expirations_version', GAMIPRESS_EXPIRATIONS_VER );
        }

    }

    /**
     * Deactivation hook for the plugin.
     *
     * @since  1.0.0
     */
    public static function deactivate() {

        // Un-schedule cron jobs
        wp_clear_scheduled_hook( 'gamipress_expirations_five_minutes_cron' );
        wp_clear_scheduled_hook( 'gamipress_expirations_hourly_cron' );

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'GAMIPRESS_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'GamiPress - Expirations requires %s (%s or higher) in order to work. Please install and activate them.', 'gamipress-expirations' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_EXPIRATIONS_GAMIPRESS_MIN_VER
                    ); ?>
                </p>
            </div>

            <?php define( 'GAMIPRESS_ADMIN_NOTICES', true ); ?>

        <?php endif;

    }

    /**
     * Check if there are all plugin requirements
     *
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements() {

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_EXPIRATIONS_GAMIPRESS_MIN_VER, '>=' ) ) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Internationalization
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

        // Set filter for language directory
        $lang_dir = GAMIPRESS_EXPIRATIONS_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_expirations_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-expirations' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-expirations', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-expirations/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress/ folder
            load_textdomain( 'gamipress-expirations', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress/languages/ folder
            load_textdomain( 'gamipress-expirations', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-expirations', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true GamiPress_Expirations instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_Expirations The one true GamiPress_Expirations
 */
function GamiPress_Expirations() {
    return GamiPress_Expirations::instance();
}
add_action( 'plugins_loaded', 'GamiPress_Expirations' );

// Setup our activation and deactivation hooks
register_activation_hook( __FILE__, array( 'GamiPress_Expirations', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'GamiPress_Expirations', 'deactivate' ) );
