<?php

/**
 * Commands for updating existing share data.
 */
class BP_Share_Posts_Commands extends \WP_CLI_Command {
    /**
     * Update BP share posts data.
     *
     * ## EXAMPLES
     *
     *     wp bp_share_posts fix
     *
     * @when  after_wp_load
     * @since 1.1.0
     */
    public function fix( $args, $assoc_args ) {
        $activities = bp_activity_get( array(
            'filter_query' => array(
                'relation' => 'AND',
                array(
                    'column' => 'component',
                    'compare' => '=',
                    'value' => 'blogs'
                ),
                array(
                    'column' => 'type',
                    'compare' => '=',
                    'value' => 'share'
                ),
            )
        ) );

        if ( empty( $activities['activities'] ) ) {
            WP_CLI::error( 'No activities found.' );
            return;
        }

        WP_CLI::success( sprintf( '%d activities found.', count( $activities['activities'] ) ) );

        foreach ( $activities['activities'] as $activity ) {
            $this->process_activity( $activity );
        }
    }

    /**
     * Update user as having shared activity.
     *
     * @since 1.1.0
     *
     * @param object $activity
     */
    private function process_activity( $activity ) {
        $plugin = BP_Share_Posts();

        $plugin->set_user_as_shared_post( $activity->user_id, $activity->item_id );
    }
}
