<?php
/**
 * Restrict Content Widget
 *
 * @package     GamiPress\Restrict_Content\Widgets\Widget\Restrict_Content
 * @since       1.0.2
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_Restrict_Content_Widget extends GamiPress_Widget {

    /**
     * Shortcode for this widget.
     *
     * @var string
     */
    protected $shortcode = 'gamipress_restrict_content';

    public function __construct() {
        parent::__construct(
            $this->shortcode . '_widget',
            __( 'GamiPress: Restrict Content', 'gamipress-restrict-content' ),
            __( 'Display a restricted portion of content.', 'gamipress-restrict-content' )
        );
    }

    public function get_fields() {

        // Need to change field id to content_id to avoid problems with GamiPress javascript selectors
        $fields = GamiPress()->shortcodes[$this->shortcode]->fields;

        // Get the fields keys
        $keys = array_keys( $fields );

        // Get the numeric index of the field 'id'
        $index = array_search( 'id', $keys );

        // Replace the 'id' key by 'content_id'
        $keys[$index] = 'content_id';

        // Combine new array with new keys with an array of values
        $fields = array_combine( $keys, array_values( $fields ) );

        // Change the message field type to a WordPress editor
        $fields['message']['type'] = 'wysiwyg';
        $fields['guest_message']['type'] = 'wysiwyg';

        $fields['content'] = array(
            'name'          => __( 'Content', 'gamipress-restrict-content' ),
            'desc'          => __( 'Content that is shown to users that have successfully unlocked this portion of content.', 'gamipress-restrict-content' ),
            'type'          => 'wysiwyg',
            'default'       => '',
        );

        return $fields;

    }

    public function get_widget( $args, $instance ) {

        // Get back replaced fields
        $instance['id'] = $instance['content_id'];

        // Build shortcode attributes from widget instance
        $atts = gamipress_build_shortcode_atts( $this->shortcode, $instance );

        echo gamipress_do_shortcode( $this->shortcode, $atts, $instance['content'] );

    }

}