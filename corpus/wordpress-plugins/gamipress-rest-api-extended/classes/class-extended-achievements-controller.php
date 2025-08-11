<?php
/**
 * Achievements Controller class
 *
 * @author GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gamil.com>
 *
 * @since 1.0.0
 */
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Achievements extended controller.
 *
 * @since 1.0.0
 *
 * @see GamiPress_Rest_API_Extended_Controller
 */
class GamiPress_Rest_API_Extended_Achievements_Controller extends GamiPress_Rest_API_Extended_Controller {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {

        parent::__construct( array(
            // Get achievements
            'get-achievements',
            // Award/Revoke
            'award-achievement',
            'revoke-achievement',
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
            // Get achievements
            case 'get-achievements':
                return array(
                    'user' => array(
                        'description'        => __( 'User to retrieve achievements. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User to retrieve achievements (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'achievement_type[]' => array(
                        'description'        => __( 'Achievement type\'s slug of achievements to retrieve. Accepts single type, array of types, "all" or empty for all achievement types.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'enum'               => gamipress_get_achievement_types_slugs(),
                        'required'           => false,
                    ),
                    
                );
                break;
            // Award/Revoke
            case 'award-achievement':
                return array(
                    'user' => array(
                        'description'        => __( 'User who achievement will be awarded. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User who achievement will be awarded (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'achievement_id' => array(
                        'description'        => __( 'Achievement that will be awarded.', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => true,
                    ),
                );
                break;
            case 'revoke-achievement':
                return array(
                    'user' => array(
                        'description'        => __( 'User who achievement will be revoked. Accepts the user username, email or ID.', 'gamipress-extended-rest-api' ),
                        'type'               => 'string',
                        'required'           => false,
                    ),
                    'user_id' => array(
                        'description'        => __( 'User who achievement will be revoked (deprecated, use "user" parameter instead).', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => false,
                    ),
                    'achievement_id' => array(
                        'description'        => __( 'Achievement that will be revoked.', 'gamipress-extended-rest-api' ),
                        'type'               => 'integer',
                        'required'           => true,
                    ),
                );
                break;
        }

        return array();
    }

    /**
     * /get-achievements route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_achievements( $request ) {

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

        // Achievement Type
        $achievement_type = $request['achievement_type'];

        if( empty( $achievement_type ) || $achievement_type === 'all' ) {
            $achievement_type = gamipress_get_achievement_types_slugs();
        }

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before get user achievements
         *
         * @since 1.0.2
         *
         * @param int               $user_id            User's ID
         * @param string            $achievement_type   Achievement type's slug
         * @param WP_REST_Request   $request            Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_get_achievements', $user_id, $achievement_type, $request );

        if ( ! is_array( $achievement_type ) ) {
            
            if( gamipress_get_achievement_type( $achievement_type ) === false )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid achievement type.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

            $achievements = gamipress_get_user_achievements( array(
                'user_id'          => $user_id,
                'achievement_type' => $achievement_type,
            ) );

            $data = array();

            foreach( $achievements as $achievement ) {
                $post = get_post( $achievement->ID, ARRAY_A );

                // Setup the key id instead of ID
                $post['id'] = $post['ID'];

                unset( $post['ID'] );

                $data[] = $post;
            }

        } else {
            
            if ( empty( $achievement_type ) ){
                $achievement_multi = gamipress_get_achievement_types();
            }
            
            if ( is_array ( $achievement_type ) ){

                foreach ( $achievement_type as $type_name) {
                    if( gamipress_get_achievement_type( $type_name ) === false )
                        return new WP_Error( 'rest_invalid_field', __( 'Invalid achievement type.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

                    $achievement_multi[$type_name] = $type_name;
                }
                
            }

            $data = array();

            foreach ( $achievement_multi as $achievement_type => $value ) {
                
                $achievements = gamipress_get_user_achievements( array(
                    'user_id'          => $user_id,
                    'achievement_type' => $achievement_type,
                ) );
    
                foreach( $achievements as $achievement ) {
                    $post = get_post( $achievement->ID, ARRAY_A );
    
                    // Setup the key id instead of ID
                    $post['id'] = $post['ID'];
    
                    unset( $post['ID'] );
    
                    $data[] = $post;
                }

            }

        }

        /**
         * After get user achievements
         *
         * @since 1.0.2
         *
         * @param int               $user_id            User's ID
         * @param string            $achievement_type   Achievement type's slug
         * @param WP_REST_Request   $request            Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_get_achievements', $user_id, $achievement_type, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( $data );

        return $response;

    }

    /**
     * /award-achievement route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function award_achievement( $request ) {

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
        $achievement_id = absint( $request['achievement_id'] );

        if( ! gamipress_is_achievement( $achievement_id ) )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid achievement ID.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        /**
         * Filters award achievement to allow custom conditional checks
         *
         * @since 1.0.2
         *
         * @param bool|WP_Error     $award              True to process or WP_Error to bail here
         * @param int               $user_id            User's ID
         * @param int               $achievement_id     Achievement's ID
         * @param WP_REST_Request   $request            Full details about the request
         *
         * @return bool|WP_Error                        True to process or WP_Error to bail here
         */
        $award = apply_filters( 'gamipress_rest_api_extended_award_achievement', true, $user_id, $achievement_id, $request );

        if( is_wp_error( $award ))
            return $award;

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before award achievement
         *
         * @since 1.0.2
         *
         * @param int               $user_id            User's ID
         * @param int               $achievement_id     Achievement's ID
         * @param WP_REST_Request   $request            Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_award_achievement', $user_id, $achievement_id, $request );

        // Award the achievement to the user
        gamipress_award_achievement_to_user( $achievement_id, $user_id, get_current_user_id() );

        /**
         * After award achievement
         *
         * @since 1.0.2
         *
         * @param int               $user_id            User's ID
         * @param int               $achievement_id     Achievement's ID
         * @param WP_REST_Request   $request            Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_award_achievement', $user_id, $achievement_id, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( array(
            'message' => __( 'Achievement awarded to the user successfully.', 'gamipress-extended-rest-api' ),
            'success' => true
        ) );

        return $response;

    }

    /**
     * /revoke-achievement route callback
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function revoke_achievement( $request ) {

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
        $achievement_id = absint( $request['achievement_id'] );

        if( ! gamipress_is_achievement( $achievement_id ) )
            return new WP_Error( 'rest_invalid_field', __( 'Invalid achievement ID.', 'gamipress-extended-rest-api' ), array( 'status' => 400 ) );

        /**
         * Filters revoke achievement to allow custom conditional checks
         *
         * @since 1.0.2
         *
         * @param bool|WP_Error     $revoke             True to process or WP_Error to bail here
         * @param int               $user_id            User's ID
         * @param int               $achievement_id     Achievement's ID
         * @param WP_REST_Request   $request            Full details about the request
         *
         * @return bool|WP_Error                        True to process or WP_Error to bail here
         */
        $revoke = apply_filters( 'gamipress_rest_api_extended_revoke_achievement', true, $user_id, $achievement_id, $request );

        if( is_wp_error( $revoke ))
            return $revoke;

        // --------------------------------
        // Route processing
        // --------------------------------

        /**
         * Before revoke achievement
         *
         * @since 1.0.2
         *
         * @param int               $user_id            User's ID
         * @param int               $achievement_id     Achievement's ID
         * @param WP_REST_Request   $request            Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_before_revoke_achievement', $user_id, $achievement_id, $request );

        // Revoke the achievement to the user
        gamipress_revoke_achievement_to_user( $achievement_id, $user_id );

        /**
         * After revoke achievement
         *
         * @since 1.0.2
         *
         * @param int               $user_id            User's ID
         * @param int               $achievement_id     Achievement's ID
         * @param WP_REST_Request   $request            Full details about the request
         */
        do_action( 'gamipress_rest_api_extended_after_revoke_achievement', $user_id, $achievement_id, $request );

        // --------------------------------
        // Response
        // --------------------------------

        $response = rest_ensure_response( array(
            'message' => __( 'Achievement revoked to the user successfully.', 'gamipress-extended-rest-api' ),
            'success' => true
        ) );

        return $response;

    }

}
