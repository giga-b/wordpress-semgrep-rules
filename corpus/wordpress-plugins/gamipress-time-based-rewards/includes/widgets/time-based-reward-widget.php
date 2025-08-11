<?php
/**
 * Time-based Reward Widget
 *
 * @package     GamiPress\Daily_Login_Rewards\Widgets\Widget\Time_Based_Reward
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_Time_Based_Reward_Widget extends GamiPress_Widget {

    /**
     * Shortcode for this widget.
     *
     * @var string
     */
    protected $shortcode = 'gamipress_time_based_reward';

    public function __construct() {

        parent::__construct(
            $this->shortcode . '_widget',
            __( 'GamiPress: Time-based Reward', 'gamipress-time-based-rewards' ),
            __( 'Render a desired rewards calendar.', 'gamipress-time-based-rewards' )
        );

    }

    public function get_fields() {

        // Need to change field id to time_based_reward_id to avoid problems with GamiPress javascript selectors
        $fields = GamiPress()->shortcodes[$this->shortcode]->fields;

        // Get the fields keys
        $keys = array_keys( $fields );

        // Get the numeric index of the field 'id'
        $index = array_search( 'id', $keys );

        // Replace the 'id' key by 'rewards_calendar_id'
        $keys[$index] = 'time_based_reward_id';

        // Get the numeric index of the field 'title'
        $index = array_search( 'title', $keys );

        // Replace the 'title' key by 'show_title'
        $keys[$index] = 'show_title';

        // Combine new array with new keys with an array of values
        $fields = array_combine( $keys, array_values( $fields ) );

        return $fields;

    }

    public function get_widget( $args, $instance ) {

        // Get back replaced fields
        $instance['id'] = $instance['time_based_reward_id'];
        $instance['title'] = $instance['show_title'];

        // Build shortcode attributes from widget instance
        $atts = gamipress_build_shortcode_atts( $this->shortcode, $instance );

        echo gamipress_do_shortcode( $this->shortcode, $atts );

    }

}