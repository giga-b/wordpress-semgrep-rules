<?php
/**
 * Plugin Name:     GamiPress - Rest API Extended
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-rest-api-extended
 * Description:     New rest API endpoints to extend interaction between your gamification environment and external applications.
 * Version:         1.0.7
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-rest-api-extended
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\Rest_API_Extended
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_Rest_API_Extended {

    /**
     * @var         GamiPress_Rest_API_Extended $instance The one true GamiPress_Rest_API_Extended
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_Rest_API_Extended self::$instance The one true GamiPress_Rest_API_Extended
     */
    public static function instance() {

        if( !self::$instance ) {

            self::$instance = new GamiPress_Rest_API_Extended();
            self::$instance->constants();
            self::$instance->classes();
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
        define( 'GAMIPRESS_REST_API_EXTENDED_VER', '1.0.7' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_REST_API_EXTENDED_GAMIPRESS_MIN_VER', '3.0.0' );

        // Plugin file
        define( 'GAMIPRESS_REST_API_EXTENDED_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_REST_API_EXTENDED_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_REST_API_EXTENDED_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin classes
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function classes() {

        if( $this->meets_requirements() ) {

            // Base extended controller
            require_once GAMIPRESS_REST_API_EXTENDED_DIR . 'classes/class-extended-controller.php';

            // Controllers
            require_once GAMIPRESS_REST_API_EXTENDED_DIR . 'classes/class-extended-points-controller.php';
            require_once GAMIPRESS_REST_API_EXTENDED_DIR . 'classes/class-extended-achievements-controller.php';
            require_once GAMIPRESS_REST_API_EXTENDED_DIR . 'classes/class-extended-ranks-controller.php';
            require_once GAMIPRESS_REST_API_EXTENDED_DIR . 'classes/class-extended-requirements-controller.php';
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

            require_once GAMIPRESS_REST_API_EXTENDED_DIR . 'includes/admin.php';
            require_once GAMIPRESS_REST_API_EXTENDED_DIR . 'includes/api.php';
            require_once GAMIPRESS_REST_API_EXTENDED_DIR . 'includes/scripts.php';

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
                        __( 'GamiPress - Rest API requires %s (%s or higher) in order to work. Please install and activate it.', 'gamipress-rest-api-extended' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_REST_API_EXTENDED_GAMIPRESS_MIN_VER
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

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_REST_API_EXTENDED_GAMIPRESS_MIN_VER, '>=' ) ) {
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
        $lang_dir = GAMIPRESS_REST_API_EXTENDED_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_rest_api_extended_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-rest-api-extended' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-rest-api-extended', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-rest-api-extended/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress/ folder
            load_textdomain( 'gamipress-rest-api-extended', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress/languages/ folder
            load_textdomain( 'gamipress-rest-api-extended', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-rest-api-extended', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true GamiPress_Rest_API_Extended instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_Rest_API_Extended The one true GamiPress_Rest_API_Extended
 */
function GamiPress_Rest_API_Extended() {
    return GamiPress_Rest_API_Extended::instance();
}
add_action( 'plugins_loaded', 'GamiPress_Rest_API_Extended' );
