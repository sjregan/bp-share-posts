<?php

/**
 * Extends activities endpoint.
 *
 * @since 1.1.0
 */
class BP_Share_Posts_REST_API_Activities extends BP_REST_Activity_Endpoint {
    /**
     * @var BP_Share_Posts_REST_API_Activity_Status_Field
     * @since 1.1.0
     */
    public $activity_status_field;

    /**
     * BP_Share_Posts_REST_API_Posts constructor.
     */
    public function __construct() {
        parent::__construct( 'post' );

        $this->activity_status_field = new BP_Share_Posts_REST_API_Activity_Status_Field();

        // Register API endpoints
        $this->register_routes();
    }

    /**
     * Register routes.
     *
     * @since 1.1.0
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)/bp-share',
            [
                'args'   => [
                    'id' => [
                        'description' => __( 'Unique identifier for the object.' ),
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'share_activity' ],
                    'permission_callback' => [ $this, 'share_activity_permissions_check' ],
                    'args'                => [
                        'status' => [
                            'description' => __( 'New share status.' ),
                            'type'        => 'string',
                            'enum'        => [ '', 'shared' ],
                        ],
                    ],
                ],
                'schema' => [ $this, 'get_public_item_schema' ],
            ]
        );
    }

    /**
     * Check if user has permission to share post.
     *
     * @since 1.1.0
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function share_activity_permissions_check( $request ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        return $this->get_item_permissions_check( $request );
    }

    /**
     * Share activity.
     *
     * @since 1.1.0
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function share_activity( $request ) {
        $activity_id = (int) $request->get_param( 'id' );
        $shared      = $request->get_param( 'status' ) === 'shared';

        if ( $shared ) {
            $result = $this->share( $activity_id );
        } else {
            $result = $this->unshare( $activity_id );
        }

        return rest_ensure_response( $result );
    }

    /**
     * Share an activity.
     *
     * @since 1.1.0
     *
     * @param int $activity_id
     * @return bool|int|WP_Error
     */
    protected function share( $activity_id ) {
        $plugin = BP_Share_Posts();

        if ( $existing_id = $plugin->shared_activity_exists( $activity_id, get_current_user_id() ) ) {
            return $existing_id;
        }

        return $plugin->share_activity( $activity_id );
    }

    /**
     * Unshare an activity.
     *
     * @since 1.1.0
     *
     * @param int $activity_id
     * @return bool|int|WP_Error
     */
    protected function unshare( $activity_id ) {
        $plugin = BP_Share_Posts();

        return $plugin->unshare_activity( $activity_id );
    }
}
