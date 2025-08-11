<?php
/**
 * Plugin Name:     GamiPress - Coupons
 * Plugin URI:      https://gamipress.com/add-ons/gamipress-coupons
 * Description:     Create coupons that users can redeem for points, achievements and/or ranks.
 * Version:         1.1.2
 * Author:          GamiPress
 * Author URI:      https://gamipress.com/
 * Text Domain:     gamipress-coupons
 * License:         GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package         GamiPress\Coupons
 * @author          GamiPress
 * @copyright       Copyright (c) GamiPress
 */

final class GamiPress_Coupons {

    /**
     * @var         GamiPress_Coupons $instance The one true GamiPress_Coupons
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      GamiPress_Coupons self::$instance The one true GamiPress_Coupons
     */
    public static function instance() {

        if( ! self::$instance ) {

            self::$instance = new GamiPress_Coupons();
            self::$instance->constants();
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
        define( 'GAMIPRESS_COUPONS_VER', '1.1.2' );

        // GamiPress minimum required version
        define( 'GAMIPRESS_COUPONS_GAMIPRESS_MIN_VER', '3.0.0' );

        // Plugin file
        define( 'GAMIPRESS_COUPONS_FILE', __FILE__ );

        // Plugin path
        define( 'GAMIPRESS_COUPONS_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_COUPONS_URL', plugin_dir_url( __FILE__ ) );

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

            require_once GAMIPRESS_COUPONS_DIR . 'includes/admin.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/ajax-functions.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/coupons.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/custom-tables.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/functions.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/listeners.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/requirements.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/rules-engine.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/logs.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/shortcodes.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/template-functions.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/triggers.php';
            require_once GAMIPRESS_COUPONS_DIR . 'includes/widgets.php';

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

        add_action( 'init', array( $this, 'load_textdomain' ) );

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
                        __( 'GamiPress - Coupons requires %s (%s or higher) in order to work. Please install and activate them.', 'gamipress-coupons' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        GAMIPRESS_COUPONS_GAMIPRESS_MIN_VER
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

        if ( class_exists( 'GamiPress' ) && version_compare( GAMIPRESS_VER, GAMIPRESS_COUPONS_GAMIPRESS_MIN_VER, '>=' ) ) {
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
        $lang_dir = GAMIPRESS_COUPONS_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_coupons_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-coupons' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-coupons', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-coupons/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress/ folder
            load_textdomain( 'gamipress-coupons', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress/languages/ folder
            load_textdomain( 'gamipress-coupons', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-coupons', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true GamiPress_Coupons instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_Coupons The one true GamiPress_Coupons
 */
function GamiPress_Coupons() {
    return GamiPress_Coupons::instance();
}
add_action( 'plugins_loaded', 'GamiPress_Coupons' );
