<?php

/**
 * Features related to REST API.
 *
 * @since 1.1.0
 */
class BP_Share_Posts_REST_API {
    /**
     * @var BP_Share_Posts_REST_API_Posts
     * @since 1.1.0
     */
    public $posts_endpoint;

    /**
     * @var BP_Share_Posts_REST_API_Activity_Status_Field
     * @since 1.1.0
     */
    public $activities_endpoint;

    public function __construct() {
        $this->posts_endpoint = new BP_Share_Posts_REST_API_Posts();

        if ( class_exists( 'BP_REST_Activity_Endpoint' ) ) {
            require_once( 'class-bp-share-posts-rest-api-activities-endpoint.php' );

            if ( bp_is_active( 'activity' ) ) {
                $this->activities_endpoint = new BP_Share_Posts_REST_API_Activities();
            }
        }
    }
}
