<?php

/**
 * Extends posts endpoint.
 *
 * @since NEXT
 */
class BP_Share_Posts_REST_API_Posts extends WP_REST_Posts_Controller {
    /**
     * @var BP_Share_Posts_REST_API_Post_Status_Field
     * @since NEXT
     */
    public $user_status_field;

    /**
     * BP_Share_Posts_REST_API_Posts constructor.
     */
    public function __construct() {
        parent::__construct( 'post' );

        $this->user_status_field = new BP_Share_Posts_REST_API_Post_Status_Field();

        // Register API endpoints
        $this->register_routes();
    }

    /**
     * Register routes.
     *
     * @since NEXT
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
                    'callback'            => [ $this, 'share_post' ],
                    'permission_callback' => [ $this, 'share_post_permissions_check' ],
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
     * @since NEXT
     *
     * @param WP_REST_Request $request
     * @return bool|WP_Error
     */
    public function share_post_permissions_check( $request ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        return $this->get_item_permissions_check( $request );
    }

    /**
     * Get related posts.
     *
     * @since NEXT
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function share_post( $request ) {
        $post_id = (int) $request->get_param( 'id' );
        $shared  = $request->get_param( 'status' ) === 'shared';

        if ( $shared ) {
            $result = $this->share( $post_id );
        } else {
            $result = $this->unshare( $post_id );
        }

        return rest_ensure_response( $result );
    }

    /**
     * Share a post.
     *
     * @since NEXT
     *
     * @param int $post_id
     * @return bool|int|WP_Error
     */
    protected function share( $post_id ) {
        $plugin = BP_Share_Posts();

        if ( $existing_id = $plugin->share_exists( get_current_user_id(), $post_id ) ) {
            return $existing_id;
        }

        return $plugin->share_post( $post_id );
    }

    /**
     * Unshare a post.
     *
     * @since NEXT
     *
     * @param int $post_id
     * @return bool|int|WP_Error
     */
    protected function unshare( $post_id ) {
        $plugin = BP_Share_Posts();

        return $plugin->unshare_post( $post_id );
    }
}
