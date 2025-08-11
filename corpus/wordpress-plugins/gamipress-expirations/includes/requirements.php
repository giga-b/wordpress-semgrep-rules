<?php
/**
 * Requirements
 *
 * @package GamiPress\Expirations\Requirements
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Add the score field to the requirement object
 *
 * @param $requirement
 * @param $requirement_id
 *
 * @return array
 */
function gamipress_expirations_requirement_object( $requirement, $requirement_id ) {

    // Expiration fields
    $requirement['expirations_expiration'] = gamipress_get_post_meta( $requirement_id, '_gamipress_expirations_expiration', true );
    $requirement['expirations_amount'] = absint( gamipress_get_post_meta( $requirement_id, '_gamipress_expirations_amount', true ) );
    $requirement['expirations_date'] = gamipress_get_post_meta( $requirement_id, '_gamipress_expirations_date', true );

    return $requirement;
}
add_filter( 'gamipress_requirement_object', 'gamipress_expirations_requirement_object', 10, 2 );

/**
 * Custom requirements UI fields
 *
 * @since 1.0.0
 *
 * @param integer $requirement_id
 * @param integer $post_id
 */
function gamipress_expirations_requirement_ui_fields( $requirement_id, $post_id ) {
    $expiration = gamipress_get_post_meta( $requirement_id, '_gamipress_expirations_expiration', true );
    $amount = absint( gamipress_get_post_meta( $requirement_id, '_gamipress_expirations_amount', true ) );
    $date = gamipress_get_post_meta( $requirement_id, '_gamipress_expirations_date', true );

    if( $amount === 0 ) {
        $amount = 1;
    }

    if( empty( $date ) ) {
        $date = date( 'Y-m-d', strtotime( '+1 year', current_time( 'timestamp' ) ) );
    }

    $options = array(
        ''          => __( 'Never expires', 'gamipress-expirations' ),
        'hours'     => __( 'Hours', 'gamipress-expirations' ),
        'days'      => __( 'Days', 'gamipress-expirations' ),
        'weeks'     => __( 'Weeks', 'gamipress-expirations' ),
        'months'    => __( 'Months', 'gamipress-expirations' ),
        'years'     => __( 'Years', 'gamipress-expirations' ),
        'date'      => __( 'Specific date', 'gamipress-expirations' ),
    );
    ?>
    <div class="gamipress-expirations-requirement-expiration">
        <label for="gamipress-expirations-requirement-expiration-<?php echo $requirement_id; ?>"><?php _e( 'Expires:', 'gamipress-button' ); ?></label>
        <div class="gamipress-expirations-expiration">
            <select id="gamipress-expirations-requirement-expiration-<?php echo $requirement_id; ?>">
                <?php foreach( $options as $value => $label ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $expiration, $value, true ); ?>><?php echo esc_attr( $label ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="gamipress-expirations-amount">
            <input type="number" step="1" min="1" id="gamipress-expirations-requirement-amount-<?php echo $requirement_id; ?>" value="<?php echo esc_attr( $amount ); ?>"/>
            <span class="cmb2-metabox-description"><?php echo sprintf( __( '%s later', 'gamipress-expirations' ), '<span></span>' ); ?></span>
        </div>
        <div class="gamipress-expirations-date">
            <input type="text" id="gamipress-expirations-requirement-date-<?php echo $requirement_id; ?>" value="<?php echo esc_attr( $date ); ?>"/>
            <span class="cmb2-metabox-description"><?php echo __( '(a date in format "year-month-day" like 2017-07-20)', 'gamipress-expirations' ); ?></span>
        </div>
    </div>
    <?php
}
add_action( 'gamipress_requirement_ui_html_after_requirement_title', 'gamipress_expirations_requirement_ui_fields', 10, 2 );

/**
 * Custom handler to save the score on requirements UI
 *
 * @param $requirement_id
 * @param $requirement
 */
function gamipress_expirations_ajax_update_requirement( $requirement_id, $requirement ) {

    // Save expiration fields field
    gamipress_update_post_meta( $requirement_id, '_gamipress_expirations_expiration', $requirement['expirations_expiration'] );
    gamipress_update_post_meta( $requirement_id, '_gamipress_expirations_amount', absint( $requirement['expirations_amount'] ) );
    gamipress_update_post_meta( $requirement_id, '_gamipress_expirations_date', $requirement['expirations_date'] );

}
add_action( 'gamipress_ajax_update_requirement', 'gamipress_expirations_ajax_update_requirement', 10, 2 );