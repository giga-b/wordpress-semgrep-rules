<?php
/**
 * Ranks Controller class
 *
 * @author GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gamil.com>
 *
 * @since 1.0.0
 */
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Ranks extended controller.
 *
 * @since 1.0.0
 *
 * @see GamiPress_Rest_API_Extended_Controller
 */
class GamiPress_Rest_API_Extended_Ranks_Controller extends GamiPress_Rest_API_Extended_Controller {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        parent::__construct( array(
            // Get current/next/previous rank
            'get-rank',
            'get-next-rank',
            'get-previous-rank',

            // Award/Revoke
            'award-rank',
            'revoke-rank',

            // Upgrade/Downgrade
            'upgrade-to-next-rank',
            'downgrade-to-previous-rank',
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
            // Get current/next/previous rank
            case 'get-rank':
            case 'get-next-rank':
            case 'get-previous-rank':
                return array(
                    'user' => array(
                        'description'        => __( 'User to retrieve rank. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User to retrieve rank (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'rank_type' => array(
                        'description'        => __( 'Rank type\'s slug of rank to retrieve.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'enum'               => gamipress_get_rank_types_slugs(),
                        'required'           => true,
                    ),
                );
                break;
            // Award/Revoke
            case 'award-rank':
                return array(
                    'user' => array(
                        'description'        => __( 'User who rank will be awarded. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User who rank will be awarded (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'rank_id' => array(
                        'description'        => __( 'Rank that will be awarded.', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => true,
                    ),
                );
                break;
            case 'revoke-rank':
                return array(
                    'user' => array(
                        'description'        => __( 'User who rank will be revoked. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User who rank will be revoked (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'rank_id' => array(
                        'description'        => __( 'Rank that will be revoked.', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => true,
                    ),
                    'new_rank_id' => array(
                        'description'        => __( 'New rank that will be assigned to the user (Optional). By default, previous rank of the same type will be assigned.', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                    ),
                );
                break;
            // Upgrade/Downgrade
            case 'upgrade-to-next-rank':
                return array(
                    'user' => array(
                        'description'        => __( 'User who rank will be upgraded. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User who rank will be upgraded (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'rank_type' => array(
                        'description'        => __( 'Rank type\'s slug of rank to upgrade.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'enum'               => gamipress_get_rank_types_slugs(),
                        'required'           => true,
                    ),
                );
                break;
            case 'downgrade-to-previous-rank':
                return array(
                    'user' => array(
                        'description'        => __( 'User who rank will be downgraded. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User who rank will be downgraded (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'rank_type' => array(
                        'description'        => __( 'Rank type\'s slug of rank to downgrade.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'enum'               => gamipress_get_rank_types_slugs(),
                        'required'           => true,
                    ),
                );
                break;
        }

        return array();
    }

    /**
     * /get-rank route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request   $request Full details about the request.
     * @param string            $which Accepts next|previous|current. Default current
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_rank( $request, $which = 'current' ) {

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

        // Rank Type
        $rank_type = $request['rank_type'];

        if( gamipress_get_rank_type( $rank_type ) === false )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid rank type.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before get user rank
         *
         * @since 1.0.2
         *
         * @param int               $user_id    User's ID
         * @param string            $rank_type  Rank type's slug
         * @param string            $which      Defines which rank should get returned. Accepts next|previous|current.
         * @param WP_REST_Request   $request    Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_get_rank', $user_id, $rank_type, $which, $request );

        switch( $which ) {
            case 'next':
                // Get the next user rank
                $rank_id = gamipress_get_next_user_rank_id( $user_id, $rank_type );
                break;
            case 'previous':
                // Get the previous user rank
                $rank_id = gamipress_get_prev_user_rank_id( $user_id, $rank_type );
                break;
            default:
                // Get the user rank
                $rank_id = gamipress_get_user_rank_id( $user_id, $rank_type );
                break;
        }

        /**
         * After get user rank
         *
         * @since 1.0.2
         *
         * @param int               $user_id    User's ID
         * @param string            $rank_type  Rank type's slug
         * @param string            $which      Defines which rank should get returned. Accepts next|previous|current.
         * @param WP_REST_Request   $request    Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_get_rank', $user_id, $rank_type, $which, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $post = get_post( $rank_id, ARRAY_A );

        // Setup the key id instead of ID
        $post['id'] = $post['ID'];

        unset( $post['ID'] );

        $response = rest_ensure_response( $post );

        return $response;

    }

    /**
     * /get-next-rank route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request   $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_next_rank( $request ) {
        return $this->get_rank( $request, 'next' );
    }

    /**
     * /get-previous-rank route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request   $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_previous_rank( $request ) {
        return $this->get_rank( $request, 'previous' );
    }

    /**
     * /award-rank route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function award_rank( $request ) {

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

        // Rank ID
        $rank_id = absint( $request['rank_id'] );

        if( ! gamipress_is_rank( $rank_id ) )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid rank ID.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        /**
         * Filters award rank to allow custom conditional checks
         *
         * @since 1.0.2
         *
         * @param bool|WP_Error     $award      True to process or WP_Error to bail here
         * @param int               $user_id    User's ID
         * @param int               $rank_id    Rank's ID
         * @param WP_REST_Request   $request    Full details about the request
         *
         * @return bool|WP_Error                True to process or WP_Error to bail here
         */
        $award = apply_filters( 'gamipress_rest_api_extended_award_rank', true, $user_id, $rank_id, $request );

        if( is_wp_error( $award ))
            return $award;

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before award rank
         *
         * @since 1.0.2
         *
         * @param int               $user_id    User's ID
         * @param int               $rank_id    Rank's ID
         * @param WP_REST_Request   $request    Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_award_rank', $user_id, $rank_id, $request );

        // Award the rank to the user
        gamipress_award_rank_to_user( $rank_id, $user_id, array( 'admin_id' => get_current_user_id() ) );

        /**
         * After award rank
         *
         * @since 1.0.2
         *
         * @param int               $user_id    User's ID
         * @param int               $rank_id    Rank's ID
         * @param WP_REST_Request   $request    Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_award_rank', $user_id, $rank_id, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( array(
            'message' => __( 'Rank awarded to the user successfully.', 'gamipress-extended-rest-api' ),
            'success' => true
        ) );

        return $response;

    }

    /**
     * /revoke-rank route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function revoke_rank( $request ) {

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

        // Rank ID
        $rank_id = absint( $request['rank_id'] );

        if( ! gamipress_is_rank( $rank_id ) )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid rank ID.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        // New rank ID (optional)
        $new_rank_id = absint( $request['new_rank_id'] );

        if( $new_rank_id !== 0 ) {
            if( ! gamipress_is_rank( $new_rank_id ) )
                return new WP_Error( 'rest_invalid_field', __( 'Invalid new rank ID.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );
        }

        /**
         * Filters revoke rank to allow custom conditional checks
         *
         * @since 1.0.2
         *
         * @param bool|WP_Error     $revoke          True to process or WP_Error to bail here
         * @param int               $user_id        User's ID
         * @param int               $rank_id        Rank's ID
         * @param int               $new_rank_id    New rank's ID
         * @param WP_REST_Request   $request        Full details about the request
         *
         * @return bool|WP_Error                    True to process or WP_Error to bail here
         */
        $revoke = apply_filters( 'gamipress_rest_api_extended_revoke_rank', true, $user_id, $rank_id, $new_rank_id, $request );

        if( is_wp_error( $revoke ))
            return $revoke;

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before revoke rank
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param string            $rank_id        Rank's ID
         * @param int               $new_rank_id    New rank's ID
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_revoke_rank', $user_id, $rank_id, $new_rank_id, $request );

        // Revoke the rank to the user
        gamipress_revoke_rank_to_user( $user_id, $rank_id, $new_rank_id, array( 'admin_id' => get_current_user_id() ) );

        /**
         * After revoke rank
         *
         * @since 1.0.2
         *
         * @param int               $user_id        User's ID
         * @param string            $rank_id        Rank's ID
         * @param int               $new_rank_id    New rank's ID
         * @param WP_REST_Request   $request        Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_revoke_rank', $user_id, $rank_id, $new_rank_id, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( array(
            'message' => __( 'Rank revoked to the user successfully.', 'gamipress-extended-rest-api' ),
            'success' => true
        ) );

        return $response;

    }

    /**
     * /upgrade-to-next-rank route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function upgrade_to_next_rank( $request ) {

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

        // Rank Type
        $rank_type = $request['rank_type'];

        if( gamipress_get_rank_type( $rank_type ) === false )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid rank type.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        /**
         * Filters rank upgrade to allow custom conditional checks
         *
         * @since 1.0.2
         *
         * @param bool|WP_Error     $upgrade    True to process or WP_Error to bail here
         * @param int               $user_id    User's ID
         * @param string            $rank_type  Rank type's slug
         * @param WP_REST_Request   $request    Full details about the request
         *
         * @return bool|WP_Error                True to process or WP_Error to bail here
         */
        $upgrade = apply_filters( 'gamipress_rest_api_extended_upgrade_to_next_rank', true, $user_id, $rank_type, $request );

        if( is_wp_error( $upgrade ))
            return $upgrade;

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before upgrade user to next rank
         *
         * @since 1.0.2
         *
         * @param int               $user_id    User's ID
         * @param string            $rank_type  Rank type's slug
         * @param WP_REST_Request   $request    Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_upgrade_to_next_rank', $user_id, $rank_type, $request );

        // Get current user rank (the old one after upgrade)
        $old_rank = get_post( gamipress_get_user_rank( $user_id, $rank_type ) );

        // Process the rank upgrade
        gamipress_upgrade_user_to_next_rank( $user_id, $rank_type );

        // Get current user rank (the new one after upgrade)
        $new_rank = get_post( gamipress_get_user_rank( $user_id, $rank_type ) );

        /**
         * After upgrade user to next rank
         *
         * @since 1.0.2
         *
         * @param int               $user_id    User's ID
         * @param string            $rank_type  Rank type's slug
         * @param WP_REST_Request   $request    Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_upgrade_to_next_rank', $user_id, $rank_type, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( array(
            'message' => sprintf(
                __( 'User %s has been upgraded from %s to %s successfully.', 'gamipress-extended-rest-api' ),
                strtolower( gamipress_get_rank_type_singular( $rank_type ) ),
                $old_rank->post_title,
                $new_rank->post_title
            ),
            'success' => true
        ) );

        return $response;

    }

    /**
     * /downgrade-to-previous-rank route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function downgrade_to_previous_rank( $request ) {

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

        // Rank Type
        $rank_type = $request['rank_type'];

        if( gamipress_get_rank_type( $rank_type ) === false )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid rank type.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        /**
         * Filters rank downgrade to allow custom conditional checks
         *
         * @since 1.0.2
         *
         * @param bool|WP_Error     $downgrade  True to process or WP_Error to bail here
         * @param int               $user_id    User's ID
         * @param string            $rank_type  Rank type's slug
         * @param WP_REST_Request   $request    Full details about the request
         *
         * @return bool|WP_Error                True to process or WP_Error to bail here
         */
        $downgrade = apply_filters( 'gamipress_rest_api_extended_downgrade_to_previous_rank', true, $user_id, $rank_type, $request );

        if( is_wp_error( $downgrade ))
            return $downgrade;

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before downgrade user to previous rank
         *
         * @since 1.0.2
         *
         * @param int               $user_id    User's ID
         * @param string            $rank_type  Rank type's slug
         * @param WP_REST_Request   $request    Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_downgrade_to_previous_rank', $user_id, $rank_type, $request );

        // Get current user rank (the old one after downgrade)
        $old_rank = get_post( gamipress_get_user_rank( $user_id, $rank_type ) );

        // Process the rank downgrade
        gamipress_downgrade_user_to_prev_rank( $user_id, $rank_type );

        // Get current user rank (the new one after downgrade)
        $new_rank = get_post( gamipress_get_user_rank( $user_id, $rank_type ) );

        /**
         * After downgrade user to previous rank
         *
         * @since 1.0.2
         *
         * @param int               $user_id    User's ID
         * @param string            $rank_type  Rank type's slug
         * @param WP_REST_Request   $request    Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_downgrade_to_previous_rank', $user_id, $rank_type, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( array(
            'message' => sprintf(
                __( 'User %s has been downgraded from %s to %s successfully.', 'gamipress-extended-rest-api' ),
                strtolower( gamipress_get_rank_type_singular( $rank_type ) ),
                $old_rank->post_title,
                $new_rank->post_title
            ),
            'success' => true
        ) );

        return $response;

    }

}
