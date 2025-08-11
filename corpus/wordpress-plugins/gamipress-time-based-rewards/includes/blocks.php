<?php
/**
 * Blocks
 *
 * @package     GamiPress\Time_Based_Rewards\Blocks
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Turn select2 fields into 'post' or 'user' field types
 *
 * @since 1.0.0
 *
 * @param array                 $fields
 * @param GamiPress_Shortcode   $shortcode
 *
 * @return array
 */
function gamipress_time_based_rewards_block_fields( $fields, $shortcode ) {

    switch ( $shortcode->slug ) {
        case 'gamipress_time_based_reward':
            // Time-based reward ID
            $fields['id']['type'] = 'post';
            $fields['id']['post_type'] = array( 'time-based-reward' );
            break;
    }

    return $fields;

}
add_filter( 'gamipress_get_block_fields', 'gamipress_time_based_rewards_block_fields', 11, 2 );
