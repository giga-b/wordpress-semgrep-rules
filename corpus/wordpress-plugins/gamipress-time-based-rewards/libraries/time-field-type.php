<?php
/**
 * CMB2 Time Field Type
 *
 * @package GamiPress|CMB2_Time_Field_Type
 */

if( ! function_exists( 'cmb2_render_time_field_type' ) ) :

    /**
     * Adds a custom field type for dimension times.
     *
     * @param  object       $field          The CMB2_Field type object.
     * @param  string       $value          The saved (and escaped) value.
     * @param  int          $object_id      The current post ID.
     * @param  string       $object_type    The current object type.
     * @param  CMB2_Types   $field_type     The CMB2_Types object.
     *
     * @return void
     */
    function cmb2_render_time_field_type( $field, $value, $object_id, $object_type, $field_type ) {

        // Make sure we specify each part of the value we need.
        $value = wp_parse_args( $value, array(
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0,
        ) ); ?>

        <div class="cmb-inline">
            <ul>
                <li>
                    <?php echo $field_type->input( array(
                        'name'  => $field_type->_name( '[hours]' ),
                        'id'    => $field_type->_id( '_hours' ),
                        'value' => $value['hours'],
                        'desc'  => '',
                        'type' => 'number',
                        'step' => 1,
                        'min' => 0,
                        'placeholder' => 'hh',
                        'class' => 'small-text',
                    ) ); ?>
                    <span class="time-field-desc"><?php _e('hours'); ?></span>
                </li>
                <li>
                    :
                    <?php echo $field_type->input( array(
                        'name'  => $field_type->_name( '[minutes]' ),
                        'id'    => $field_type->_id( '_minutes' ),
                        'value' => $value['minutes'],
                        'desc'  => '',
                        'type' => 'number',
                        'step' => 1,
                        'min' => 0,
                        'placeholder' => 'mm',
                        'class' => 'small-text',
                    ) ); ?>
                    <span class="time-field-desc"><?php _e('minutes'); ?></span>
                </li>
                <li>
                    :
                    <?php echo $field_type->input( array(
                        'name'  => $field_type->_name( '[seconds]' ),
                        'id'    => $field_type->_id( '_seconds' ),
                        'value' => $value['seconds'],
                        'desc'  => '',
                        'type' => 'number',
                        'step' => 1,
                        'min' => 0,
                        'placeholder' => 'ss',
                        'class' => 'small-text',
                    ) ); ?>
                    <span class="time-field-desc"><?php _e('seconds'); ?></span>
                </li>
            </ul>
        </div>
        <?php

        echo $field_type->_desc( true );
    }
    add_action( 'cmb2_render_time', 'cmb2_render_time_field_type', 10, 5 );


    /**
     * Sanitize the selected value.
     */
    function cmb2_sanitize_time_callback( $override_value, $value ) {

        if ( is_array( $value ) ) {

            foreach ( $value as $key => $saved_value ) {
                $value[$key] = sanitize_text_field( $saved_value );
            }

            return $value;
        }

        return $override_value;
    }
    add_filter( 'cmb2_sanitize_time', 'cmb2_sanitize_time_callback', 10, 2 );

endif;
