<?php
/**
 * Points Controller class
 *
 * @author GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gamil.com>
 *
 * @since 1.0.0
 */
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Points extended controller.
 *
 * @since 1.0.0
 *
 * @see GamiPress_Rest_API_Extended_Controller
 */
class GamiPress_Rest_API_Extended_Points_Controller extends GamiPress_Rest_API_Extended_Controller {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        parent::__construct( array(
            // Get points
            'get-points',

            // Award/Revoke
            'award-points',
            'deduct-points',
        ) );
    }

    /**
     * Routes args
     *
     * @since 1.0.0
     *
     * @param  string $route The route
     *
     * @return array
     */
    public function get_route_args( $route ) {

        switch( $route ) {
            case 'get-points':
                return array(
                    'user' => array(
                        'description'        => __( 'User to retrieve points balance. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User to retrieve points balance (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'points_type' => array(
                        'description'        => __( 'Points type\'s slug of points balance to retrieve.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'enum'               => gamipress_get_points_types_slugs(),
                        'required'           => true,
                    ),
                );
                break;
            case 'award-points':
                return array(
                    'user' => array(
                        'description'        => __( 'User who points will be awarded. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User who points will be awarded (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'points' => array(
                        'description'        => __( 'Points amount to award.', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => true,
                    ),
                    'points_type' => array(
                        'description'        => __( 'Points type\'s slug of points amount to award.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'enum'               => gamipress_get_points_types_slugs(),
                        'required'           => true,
                    ),
                    'reason' => array(
                        'description'        => __( 'Reason describing this points award (Optional). This text will appear on this points award log entry.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                    ),
                );
                break;
            case 'deduct-points':
                return array(
                    'user' => array(
                        'description'        => __( 'User who points will be deducted. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User who points will be deducted (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'points' => array(
                        'description'        => __( 'Points amount to deduct.', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => true,
                    ),
                    'points_type' => array(
                        'description'        => __( 'Points type\'s slug of points amount to deduct.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'enum'               => gamipress_get_points_types_slugs(),
                        'required'           => true,
                    ),
                    'reason' => array(
                        'description'        => __( 'Reason describing this points deduction (Optional). This text will appear on this points deduction log entry.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                    ),
                );
                break;
        }

        return array();
    }

    /**
     * /get-points route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_points( $request ) {

        // --------------------------------
        // Parameters sanitization
        // --------------------------------

        // User
        $user_id = $this->get_user_id( $request['user'] );

        if( $user_id === 0 ) {
            // Fallback to user ID
            $user_id = absint( $request['user_id'] );

            if( ! $this->is_valid_user( $user_id ) )
                return new WP_Error( 'rest_invalid_field', __( 'Invalid user.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );
        }

        // Points type
        $points_type = $request['points_type'];

        if ( gamipress_get_points_type( $points_type ) === false )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid points type.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before get user points
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param string            $points_type    Points type's slug
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_get_points', $user_id, $points_type, $request );

        // Get the user points
        $points = gamipress_get_user_points( $user_id, $points_type );

        /**
         * After get user points
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param string            $points_type    Points type's slug
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_get_points', $user_id, $points_type, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( array(
            'raw'       => $points,
            'formatted' => gamipress_format_points( $points, $points_type ),
        ) );

        return $response;

    }

    /**
     * /award-points route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function award_points( $request ) {

        // --------------------------------
        // Parameters sanitization
        // --------------------------------

        // User
        $user_id = $this->get_user_id( $request['user'] );

        if( $user_id === 0 ) {
            // Fallback to user ID
            $user_id = absint( $request['user_id'] );

            if( ! $this->is_valid_user( $user_id ) )
                return new WP_Error( 'rest_invalid_field', __( 'Invalid user.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );
        }

        // Points
        $points = absint( $request['points'] );

        if( $points <= 0 )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid points amount.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        // Points type
        $points_type = $request['points_type'];

        if ( gamipress_get_points_type( $points_type ) === false )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid points type.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        // Reason
        $reason = ( isset( $request['reason'] ) && ! empty( $request['reason'] ) ? $request['reason'] : '' );

        /**
         * Filters award points to allow custom conditional checks
         *
         * @since 1.0.2
         *
         * @param bool|WP_Error     $award          True to process or WP_Error to bail here
         * @param int               $user_id        User's ID
         * @param string            $points         Points's amount
         * @param string            $points_type    Points type's slug
         * @param string            $reason         Award's reason
         * @param WP_REST_Request   $request        Full details about the request
         *
         * @return bool|WP_Error                    True to process or WP_Error to bail here
         */
        $award = apply_filters( 'gamipress_rest_api_extended_award_points', true, $user_id, $points, $points_type, $reason, $request );

        if( is_wp_error( $award ) ) {
            return $award;
        }

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before award points
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param string            $points         Points's amount
         * @param string            $points_type    Points type's slug
         * @param string            $reason         Award's reason
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_award_points', $user_id, $points, $points_type, $reason, $request );

        // Initialize args
        $args = array(
            'admin_id' => get_current_user_id(),
            'reason' => $reason,
            'log_type' => ( ! empty( $reason ) ? 'points_award' : '' ),
        );

        // When awarding points passing an admin ID, we need to pass the full new amount
        $current_points = gamipress_get_user_points( $user_id, $points_type );

        // Award points to the user
        gamipress_award_points_to_user( $user_id, $current_points + $points, $points_type, $args );

        /**
         * Filter available to decide if register a user earning or not
         *
         * @since 1.0.4
         *
         * @param bool|WP_Error     $award          True to register or false to not
         * @param int               $user_id        User's ID
         * @param string            $points         Points's amount
         * @param string            $points_type    Points type's slug
         * @param string            $reason         Award's reason
         * @param WP_REST_Request   $request        Full details about the request
         *
         * @return bool|WP_Error                    True to register or false to not
         */
        $register_earning = apply_filters( 'gamipress_rest_api_extended_award_points_earning', true, $user_id, $points, $points_type, $reason, $request );

        if( $register_earning ) {

            // Register on the user earnings table
            gamipress_insert_user_earning( $user_id, array(
                'title'	        => $reason,
                'user_id'	    => $user_id,
                'post_id'	    => gamipress_get_points_type_id( $points_type ),
                'post_type' 	=> 'points-type',
                'points'	    => $points,
                'points_type'	=> $points_type,
                'date'	        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
            ) );

        }

        /**
         * After award points
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param string            $points         Points's amount
         * @param string            $points_type    Points type's slug
         * @param string            $reason         Award's reason
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_award_points', $user_id, $points, $points_type, $reason, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( array(
            'message' => __( 'Points awarded to the user successfully.', 'gamipress-extended-rest-api' ),
            'success' => true
        ) );

        return $response;

    }

    /**
     * /deduct-points route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function deduct_points( $request ) {

        // --------------------------------
        // Parameters sanitization
        // --------------------------------

        // User
        $user_id = $this->get_user_id( $request['user'] );

        if( $user_id === 0 ) {
            // Fallback to user ID
            $user_id = absint( $request['user_id'] );

            if( ! $this->is_valid_user( $user_id ) )
                return new WP_Error( 'rest_invalid_field', __( 'Invalid user.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );
        }

        // Points
        $points = absint( $request['points'] );

        if( $points <= 0 )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid points amount.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        // Points type
        $points_type = $request['points_type'];

        if ( gamipress_get_points_type( $points_type ) === false )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid points type.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        // Reason
        $reason = ( isset( $request['reason'] ) && ! empty( $request['reason'] ) ? $request['reason'] : '' );

        /**
         * Filters deduct points to allow custom conditional checks
         *
         * @since 1.0.2
         *
         * @param bool|WP_Error     $deduct         True to process or WP_Error to bail here
         * @param int               $user_id        User's ID
         * @param string            $points         Points's amount
         * @param string            $points_type    Points type's slug
         * @param string            $reason         Award's reason
         * @param WP_REST_Request   $request        Full details about the request
         *
         * @return bool|WP_Error                    True to process or WP_Error to bail here
         */
        $deduct = apply_filters( 'gamipress_rest_api_extended_deduct_points', true, $user_id, $points, $points_type, $reason, $request );

        if( is_wp_error( $deduct ) ) {
            return $deduct;
        }

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before deduct points
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param string            $points         Points's amount
         * @param string            $points_type    Points type's slug
         * @param string            $reason         Award's reason
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_deduct_points', $user_id, $points, $points_type, $reason, $request );

        // Initialize args
        $args = array(
            'admin_id' => get_current_user_id(),
            'reason' => $reason,
            'log_type' => ( ! empty( $reason ) ? 'points_deduct' : '' ),
        );

        // When deducting points passing an admin ID, we need to pass the full new amount
        $current_points = gamipress_get_user_points( $user_id, $points_type );

        /**
         * Filter available to decide if register a user earning or not
         *
         * @since 1.0.4
         *
         * @param bool|WP_Error     $award          True to register or false to not
         * @param int               $user_id        User's ID
         * @param string            $points         Points's amount
         * @param string            $points_type    Points type's slug
         * @param string            $reason         Deduct's reason
         * @param WP_REST_Request   $request        Full details about the request
         *
         * @return bool|WP_Error                    True to register or false to not
         */
        $register_earning = apply_filters( 'gamipress_rest_api_extended_deduct_points_earning', true, $user_id, $points, $points_type, $reason, $request );

        if( $register_earning ) {

            // Register on the user earnings table
            gamipress_insert_user_earning( $user_id, array(
                'title'	        => $reason,
                'user_id'	    => $user_id,
                'post_id'	    => gamipress_get_points_type_id( $points_type ),
                'post_type' 	=> 'points-type',
                'points'	    => $points * -1, // Register as a negative value
                'points_type'	=> $points_type,
                'date'	        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
            ) );

        }

        // Award points to the user
        gamipress_deduct_points_to_user( $user_id, $current_points - $points, $points_type, $args );

        /**
         * After deduct points
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param string            $points         Points's amount
         * @param string            $points_type    Points type's slug
         * @param string            $reason         Award's reason
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_deduct_points', $user_id, $points, $points_type, $reason, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( array(
            'message' => __( 'Points deducted to the user successfully.', 'gamipress-extended-rest-api' ),
            'success' => true
        ) );

        return $response;

    }

}
