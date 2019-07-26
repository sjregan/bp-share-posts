<?php

/**
 * Features related to REST API.
 *
 * @since NEXT
 */
class BP_Share_Posts_REST_API {
    /**
     * @var BP_Share_Posts_REST_API_Posts
     * @since NEXT
     */
    public $posts_endpoint;

    /**
     * @var BP_Share_Posts_REST_API_Activity_Status_Field
     * @since NEXT
     */
    public $activity_status_field;

    public function __construct() {
        $this->posts_endpoint = new BP_Share_Posts_REST_API_Posts();

        if ( class_exists( 'BP_REST_Activity_Endpoint' ) ) {
            $this->activity_status_field = new BP_Share_Posts_REST_API_Activity_Status_Field();
        }
    }
}
