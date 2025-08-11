<?php
/**
 * Requirements Controller class
 *
 * @author GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gamil.com>
 *
 * @since 1.0.0
 */
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Requirements extended controller.
 *
 * @since 1.0.0
 *
 * @see GamiPress_Rest_API_Extended_Controller
 */
class GamiPress_Rest_API_Extended_Requirements_Controller extends GamiPress_Rest_API_Extended_Controller {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        parent::__construct( array(
            'award-requirement',
            'revoke-requirement',
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
            case 'award-requirement':
                return array(
                    'user' => array(
                        'description'        => __( 'User who requirement will be awarded. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User who requirement will be awarded (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'requirement_id' => array(
                        'description'        => __( 'Requirement that will be awarded.', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => true,
                    ),
                );
                break;
            case 'revoke-requirement':
                return array(
                    'user' => array(
                        'description'        => __( 'User who requirement will be revoked. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User who requirement will be revoked (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'requirement_id' => array(
                        'description'        => __( 'Requirement that will be revoked.', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => true,
                    ),
                );
                break;
        }

        return array();
    }

    /**
     * /award-requirement route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function award_requirement( $request ) {

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

        // Requirement ID
        $requirement_id = absint( $request['requirement_id'] );

        if( ! gamipress_is_requirement( $requirement_id ) )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid requirement ID.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        /**
         * Filters award requirement to allow custom conditional checks
         *
         * @since 1.0.2
         *
         * @param bool|WP_Error     $award              True to process or WP_Error to bail here
         * @param int               $user_id            User's ID
         * @param int               $requirement_id     Requirement's ID
         * @param WP_REST_Request   $request            Full details about the request
         *
         * @return bool|WP_Error                        True to process or WP_Error to bail here
         */
        $award = apply_filters( 'gamipress_rest_api_extended_award_requirement', true, $user_id, $requirement_id, $request );

        if( is_wp_error( $award ))
            return $award;

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before award requirement
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param int               $requirement_id Requirement's ID
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_award_requirement', $user_id, $requirement_id, $request );

        // Award the requirement to the user
        gamipress_award_achievement_to_user( $requirement_id, $user_id, get_current_user_id() );

        /**
         * After award requirement
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param int               $requirement_id Requirement's ID
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_award_requirement', $user_id, $requirement_id, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( array(
            'message' => __( 'Requirement awarded to the user successfully.', 'gamipress-extended-rest-api' ),
            'success' => true
        ) );

        return $response;

    }

    /**
     * /revoke-requirement route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function revoke_requirement( $request ) {

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

        // Achievement ID
        $requirement_id = absint( $request['requirement_id'] );

        if( ! gamipress_is_requirement( $requirement_id ) )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid requirement ID.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        /**
         * Filters revoke requirement to allow custom conditional checks
         *
         * @since 1.0.2
         *
         * @param bool|WP_Error     $revoke              True to process or WP_Error to bail here
         * @param int               $user_id            User's ID
         * @param int               $requirement_id     Requirement's ID
         * @param WP_REST_Request   $request            Full details about the request
         *
         * @return bool|WP_Error                        True to process or WP_Error to bail here
         */
        $revoke = apply_filters( 'gamipress_rest_api_extended_revoke_requirement', true, $user_id, $requirement_id, $request );

        if( is_wp_error( $revoke ))
            return $revoke;

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before revoke requirement
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param int               $requirement_id Requirement's ID
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_revoke_requirement', $user_id, $requirement_id, $request );

        // Revoke the requirement to the user
        gamipress_revoke_achievement_to_user( $requirement_id, $user_id );

        /**
         * After revoke requirement
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param int               $requirement_id Requirement's ID
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_revoke_requirement', $user_id, $requirement_id, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( array(
            'message' => __( 'Requirement revoked to the user successfully.', 'gamipress-extended-rest-api' ),
            'success' => true
        ) );

        return $response;

    }

}
