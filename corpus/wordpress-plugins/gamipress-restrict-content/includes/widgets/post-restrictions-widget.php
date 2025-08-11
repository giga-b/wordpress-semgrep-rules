<?php
/**
 * Post Restrictions Widget
 *
 * @package     GamiPress\Restrict_Content\Widgets\Widget\Post_Restrictions
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_Post_Restrictions_Widget extends GamiPress_Widget {

    /**
     * Shortcode for this widget.
     *
     * @var string
     */
    protected $shortcode = 'gamipress_post_restrictions';

    public function __construct() {
        parent::__construct(
            $this->shortcode . '_widget',
            __( 'GamiPress: Post Restrictions', 'gamipress-restrict-content' ),
            __( 'Display a desired post restrictions and/or the button to get access to users without access.', 'gamipress-restrict-content' )
        );
    }

    public function get_fields() {

        // Need to change field id to post_id to avoid problems with GamiPress javascript selectors
        $fields = GamiPress()->shortcodes[$this->shortcode]->fields;

        // Get the fields keys
        $keys = array_keys( $fields );

        // Get the numeric index of the field 'id'
        $index = array_search( 'id', $keys );

        // Replace the 'id' key by 'content_id'
        $keys[$index] = 'post_id';

        // Combine new array with new keys with an array of values
        $fields = array_combine( $keys, array_values( $fields ) );

        return $fields;

    }

    public function get_widget( $args, $instance ) {

        // Get back replaced fields
        $instance['id'] = $instance['post_id'];

        // Build shortcode attributes from widget instance
        $atts = gamipress_build_shortcode_atts( $this->shortcode, $instance );

        echo gamipress_do_shortcode( $this->shortcode, $atts );

    }

}