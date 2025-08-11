<?php
/**
 * Posts Unlocked Widget
 *
 * @package     GamiPress\Restrict_Content\Widgets\Widget\Posts_Unlocked
 * @since       1.0.8
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_Posts_Unlocked_Widget extends GamiPress_Widget {

    /**
     * Shortcode for this widget.
     *
     * @var string
     */
    protected $shortcode = 'gamipress_posts_unlocked';

    public function __construct() {
        parent::__construct(
            $this->shortcode . '_widget',
            __( 'GamiPress: Posts Unlocked', 'gamipress-restrict-content' ),
            __( 'Display a list of posts that user got access.', 'gamipress-restrict-content' )
        );
    }

    public function get_fields() {
        return GamiPress()->shortcodes[$this->shortcode]->fields;
    }

    public function get_widget( $args, $instance ) {

        // Build shortcode attributes from widget instance
        $atts = gamipress_build_shortcode_atts( $this->shortcode, $instance );

        echo gamipress_do_shortcode( $this->shortcode, $atts );

    }

}