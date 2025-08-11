<?php
/**
 * Extended Controller class
 *
 * @author GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gamil.com>
 *
 * @since 1.0.0
 */
// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Core class to for extended REST API.
 *
 * @since 1.0.0
 *
 * @see WP_REST_Controller
 */
class GamiPress_Rest_API_Extended_Controller extends WP_REST_Controller {

    /**
     * Routes of this controller.
     *
     * @since 1.0.0
     * @var string
     */
    protected $routes;

    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @param array $routes Routes to register
     */
    public function __construct( $routes ) {
        $this->namespace = 'wp/v2';
        $this->rest_base = gamipress_rest_api_extended_get_option( 'rest_base', 'gamipress' );
        $this->routes = $routes;
    }

    /**
     * Registers the routes for the objects of the controller.
     *
     * @since 1.0.0
     *
     * @see register_rest_route()
     */
    public function register_routes() {

        $server = rest_get_server();

        // Prevent to register the same route
        if( ! isset( $server->get_routes()[$this->namespace . '/' . $this->rest_base] ) ) {

            // Routes index
            register_rest_route( $this->namespace, '/' . $this->rest_base, array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array( $this, 'get_index' ),
                    'permission_callback' => array( $this, 'permission_check' ), // Common permission callback
                    'args'                => array()
                ),
                'schema' => array( $this, 'get_public_item_schema' ),
            ) );

        }

        foreach( $this->routes as $route ) {

            // Turn a route like award-points into award_points
            $_route = str_replace( '-', '_', $route );

            register_rest_route( $this->namespace, '/' . $this->rest_base . '/' . $route, array(
                array(
                    'methods'             => $this->get_route_method( $route ),
                    'callback'            => array( $this, $_route ),
                    'permission_callback' => array( $this, 'permission_check' ), // Common permission callback
                    'args'                => $this->get_route_args( $route )
                ),
                'schema' => array( $this, 'get_public_item_schema' ),
            ) );

        }

    }

    /**
     * Index of all routes under this namespace
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_index( $request ) {

        $server = rest_get_server();
        $namespace = $this->namespace . '/' . $this->rest_base;
        $routes = array();

        foreach( $server->get_routes() as $route => $handlers ) {

            // Skip routes outside this namespace
            if( strpos( $route, $namespace . '/' ) === FALSE ) {
                continue;
            }

            $routes[$route] = $handlers;
        }

        $data = array(
            'namespace' => $namespace,
            'routes'    => $server->get_data_for_routes( $routes, $request['context'] ),
        );

        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Checks user has access to the given request
     *
     * @since 1.0.0
     *
     * @param  WP_REST_Request $request Full details about the request.
     *
     * @return true|WP_Error True if user has access, WP_Error object otherwise.
     */
    public function permission_check( $request ) {

        $is_allowed = true;

        // Not allowed if isn't an administrator
        if( ! current_user_can( 'manage_options' ) ) {
            $is_allowed = false;
        }

        /**
         * Checks user has access to the given request
         *
         * @since 1.0.0
         *
         * @param bool              $is_allowed Whatever if user is allowed or not, by default permission is just granted to administrators
         * @param WP_REST_Request   $request    Full details about the request
         * @param string            $route      The current route, with a schema like $namespace/$rest_base/$route, example: /wp/v2/gamipress/award-points
         *
         * @return bool
         */
        $is_allowed = apply_filters( 'gamipress_rest_api_extended_permissions_check', $is_allowed, $request, $request->get_route() );

        if( $is_allowed ) {
            return true;
        } else {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to perform this action.', 'gamipress-extended-rest-api' ), array( 'status' => rest_authorization_required_code() ) );
        }


    }

    /**
     * Common function to get route method
     *
     * @since 1.0.0
     *
     * @param  string $route The route
     *
     * @return string
     */
    public function get_route_method( $route ) {
        $allowed_methods = array( 'POST' );

        // Check if allow GET parameters is checked
        if( (bool) gamipress_rest_api_extended_get_option( 'allow_get', false ) ) {
            $allowed_methods[] = 'GET';
        }

        return implode( ', ', $allowed_methods );
    }

    /**
     * Common function to get route args
     *
     * @since 1.0.0
     *
     * @param  string $route The route
     *
     * @return array
     */
    public function get_route_args( $route ) {
        return array();
    }

    // --------------------------------
    // Utility functions
    // --------------------------------

    /**
     * Checks if given user ID exists
     *
     * @param string $user
     *
     * @return int
     */
    function get_user_id( $user ) {

        $field = 'login';

        if( is_numeric( $user ) ) {
            $field = 'id';
        } else if( filter_var( $user, FILTER_VALIDATE_EMAIL ) ) {
            $field = 'email';
        }

        $user = get_user_by( $field, $user );

        if( $user ) {
            return $user->ID;
        } else {
            return 0;
        }

    }

    /**
     * Checks if given user ID exists
     *
     * @param integer $user_id
     *
     * @return bool
     */
    function is_valid_user( $user_id ) {

        $user_id = absint( $user_id );

        // Bail if ID is 0
        if( $user_id === 0 ) {
            return false;
        }

        // Bail if user doesn't exists
        if ( ! get_userdata( $user_id ) ) {
            return false;
        }

        return true;

    }
}