<?php

/**
 * Adds 'bp_share_posts_user_status' field to 'post' response objects
 *
 * @since NEXT
 */
class BP_Share_Posts_REST_API_Post_Status_Field {
    /**
     * BP_Share_Posts_REST_API_Post_Status_Field constructor.
     */
    public function __construct() {
        register_rest_field( 'post', 'bp_share_posts_user_status', [
            'get_callback' => [ $this, 'get_value' ],
            // Schema has to be null as BP-REST plugin does not include additional fields in get_item_schema()
            // 'schema'       => $this->get_field_schema(),
        ] );
    }

    /**
     * Retrieves whether the user has shared the post.
     *
     * @since NEXT
     *
     * @param array           $object  $object_id Object to fetch details for.
     * @param WP_REST_Request $request Full details about the request.
     * @return string
     */
    public function get_value( $object, $request ) {
        return BP_Share_Posts()->user_share_post_status( $object['id'] );
    }

    /**
     * Retrieves the object's comment count schema, conforming to JSON Schema.
     *
     * @since NEXT
     *
     * @return array Field schema data.
     */
    public function get_field_schema() {
        return [
            'description' => __( 'Indicates if user has shared post.', 'bp-share-posts' ),
            'type'        => 'string',
            'context'     => [ 'view' ],
        ];
    }
}
