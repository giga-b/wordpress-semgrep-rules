<?php
/**
 * Plugin Name:     GamiPress - Time-based Rewards
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-time-based-rewards
 * Description:     Add time-based rewards to let your users coming back to claim them.
 * Version:         1.1.3
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-time-based-rewards
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\Time_Based_Rewards
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_Time_Based_Rewards {

    /**
     * @var         GamiPress_Time_Based_Rewards $instance The one true GamiPress_Time_Based_Rewards
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_Time_Based_Rewards self::$instance The one true GamiPress_Time_Based_Rewards
     */
    public static function instance() {

        if( !self::$instance ) {

            self::$instance = new GamiPress_Time_Based_Rewards();
            self::$instance->constants();
            self::$instance->libraries();
            self::$instance->includes();
            self::$instance->hooks();            

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
        define( 'GAMIPRESS_TIME_BASED_REWARDS_VER', '1.1.3' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_TIME_BASED_REWARDS_GAMIPRESS_MIN_VER', '3.0.0' );

        // Plugin file
        define( 'GAMIPRESS_TIME_BASED_REWARDS_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_TIME_BASED_REWARDS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_TIME_BASED_REWARDS_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin classes
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function libraries() {

        if( $this->meets_requirements() ) {

            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'libraries/time-field-type.php';

        }
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

            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/admin.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/ajax-functions.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/content-filters.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/functions.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/logs.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/listeners.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/post-types.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/blocks.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/shortcodes.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/template-functions.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/triggers.php';
            require_once GAMIPRESS_TIME_BASED_REWARDS_DIR . 'includes/widgets.php';

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
        // Setup our activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        add_action( 'init', array( $this, 'load_textdomain' ) );
    }

    /**
     * Activation hook for the plugin.
     *
     * @since  1.0.0
     */
    function activate() {

        if( $this->meets_requirements() ) {

        }

    }

    /**
     * Deactivation hook for the plugin.
     *
     * @since  1.0.0
     */
    function deactivate() {

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
                        __( 'GamiPress - Time-based Rewards requires %s (%s or higher) in order to work. Please install and activate it.', 'gamipress-time-based-rewards' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_TIME_BASED_REWARDS_GAMIPRESS_MIN_VER
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

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_TIME_BASED_REWARDS_GAMIPRESS_MIN_VER, '>=' ) ) {
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
        $lang_dir = GAMIPRESS_TIME_BASED_REWARDS_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_time_based_rewards_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-time-based-rewards' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-time-based-rewards', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-time-based-rewards/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress/ folder
            load_textdomain( 'gamipress-time-based-rewards', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress/languages/ folder
            load_textdomain( 'gamipress-time-based-rewards', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-time-based-rewards', false, $lang_dir );
        }
    }

}

/**
 * The main function responsible for returning the one true GamiPress_Time_Based_Rewards instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_Time_Based_Rewards The one true GamiPress_Time_Based_Rewards
 */
function GamiPress_Time_Based_Rewards() {
    return GamiPress_Time_Based_Rewards::instance();
}
add_action( 'plugins_loaded', 'GamiPress_Time_Based_Rewards' );
