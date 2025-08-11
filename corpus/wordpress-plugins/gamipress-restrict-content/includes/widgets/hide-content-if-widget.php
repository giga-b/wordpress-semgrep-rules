<?php
/**
 * Hide Content If Widget
 *
 * @package     GamiPress\Restrict_Content\Widgets\Widget\Hide_Content_If
 * @since       1.1.4
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_Hide_Content_If_Widget extends GamiPress_Widget {

    /**
     * Shortcode for this widget.
     *
     * @var string
     */
    protected $shortcode = 'gamipress_hide_content_if';

    public function __construct() {
        parent::__construct(
            $this->shortcode . '_widget',
            __( 'GamiPress: Hide Content If', 'gamipress-restrict-content' ),
            __( 'Hide a portion of content if user meets a specific condition.', 'gamipress-restrict-content' )
        );
    }

    public function get_fields() {
        // Need to change field id to content_id to avoid problems with GamiPress javascript selectors
        $fields = GamiPress()->shortcodes[$this->shortcode]->fields;

        // Change the message field type to a WordPress editor
        $fields['message']['type'] = 'wysiwyg';
        $fields['guest_message']['type'] = 'wysiwyg';

        $fields['content'] = array(
            'name'          => __( 'Content', 'gamipress-restrict-content' ),
            'desc'          => __( 'Content that is hidden to users that meets the condition.', 'gamipress-restrict-content' ),
            'type'          => 'wysiwyg',
            'default'       => '',
        );

        return $fields;
    }

    public function get_widget( $args, $instance ) {
        // Build shortcode attributes from widget instance
        $atts = gamipress_build_shortcode_atts( $this->shortcode, $instance );

        echo gamipress_do_shortcode( $this->shortcode, $atts, $instance['content'] );
    }

}