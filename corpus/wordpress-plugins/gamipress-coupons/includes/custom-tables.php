<?php
/**
 * Custom Tables
 *
 * @package     GamiPress\Coupons\Custom_Tables
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

require_once GAMIPRESS_COUPONS_DIR . 'includes/custom-tables/coupons.php';
require_once GAMIPRESS_COUPONS_DIR . 'includes/custom-tables/coupon-rewards.php';
require_once GAMIPRESS_COUPONS_DIR . 'includes/custom-tables/coupon-notes.php';

/**
 * Register all plugin Custom DB Tables
 *
 * @since  1.0.0
 *
 * @return void
 */
function gamipress_coupons_register_custom_tables() {

    // Coupons Table
    ct_register_table( 'gamipress_coupons', array(
        'singular' => __( 'Coupon', 'gamipress-coupons' ),
        'plural' => __( 'Coupons', 'gamipress-coupons' ),
        'show_ui' => true,
        'version' => 1,
        'global' => gamipress_is_network_wide_active(),
        'capability' => gamipress_get_manager_capability(),
        'supports' => array( 'meta' ),
        'views' => array(
            'list' => array(
                'menu_title' => __( 'Coupons', 'gamipress-coupons' ),
                'parent_slug' => 'gamipress'
            ),
            'add' => array(
                'show_in_menu' => false,
            ),
            'edit' => array(
                'show_in_menu' => false,
            ),
        ),
        'schema' => array(
            'coupon_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'auto_increment' => true,
                'primary_key' => true,
            ),
            'title' => array(
                'type' => 'text',
            ),
            'code' => array(
                'type' => 'text',
            ),
            'status' => array(
                'type' => 'text',
            ),
            'start_date' => array(
                'type' => 'datetime',
                'default' => '0000-00-00 00:00:00'
            ),
            'end_date' => array(
                'type' => 'datetime',
                'default' => '0000-00-00 00:00:00'
            ),
            'max_uses' => array(
                'type' => 'bigint',
                'length' => '20',
            ),
            'max_uses_per_user' => array(
                'type' => 'bigint',
                'length' => '20',
            ),
        ),
    ) );

    // Coupon Rewards Table
    ct_register_table( 'gamipress_coupon_rewards', array(
        'singular' => __( 'Coupon Reward', 'gamipress-coupons' ),
        'plural' => __( 'Coupon Rewards', 'gamipress-coupons' ),
        'show_ui' => false,
        'version' => 1,
        'global' => gamipress_is_network_wide_active(),
        'capability' => gamipress_get_manager_capability(),
        'supports' => array( 'meta' ),
        'schema' => array(
            'coupon_reward_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'auto_increment' => true,
                'primary_key' => true,
            ),

            // Relationships

            'coupon_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'key' => true,
            ),
            'post_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'key' => true,
            ),
            'post_type' => array(
                'type' => 'varchar',
                'length' => '50',
            ),

            // Fields

            'quantity' => array(
                'type' => 'bigint',
            ),
        ),
    ) );

    // Coupon Notes Table
    ct_register_table( 'gamipress_coupon_notes', array(
        'singular' => __( 'Coupon Note', 'gamipress-coupons' ),
        'plural' => __( 'Coupon Notes', 'gamipress-coupons' ),
        'show_ui' => false,
        'version' => 1,
        'global' => gamipress_is_network_wide_active(),
        'capability' => gamipress_get_manager_capability(),
        'supports' => array( 'meta' ),
        'schema' => array(
            'coupon_note_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'auto_increment' => true,
                'primary_key' => true,
            ),
            'coupon_id' => array(
                'type' => 'bigint',
                'length' => '20',
                'key' => true,
            ),

            // Fields

            'title' => array(
                'type' => 'text',
            ),
            'description' => array(
                'type' => 'text',
            ),
            'user_id' => array(
                'type' => 'bigint',
                'length' => '20',
            ),
            'date' => array(
                'type' => 'datetime',
                'default' => '0000-00-00 00:00:00'
            ),
        ),
    ) );

}
add_action( 'ct_init', 'gamipress_coupons_register_custom_tables' );