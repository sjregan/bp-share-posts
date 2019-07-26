<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BP_Share_Posts {

	/**
	 * The single instance of BP_Share_Posts.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

    /**
     * @var BP_Share_Posts_REST_API
     * @since NEXT
     */
	public $rest_api;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'bp_share_posts';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		// add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		// add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'bp_init', array( $this, 'intercept_share_action' ) );
		add_action( 'wp_ajax_bp_share_post', array( $this, 'ajax_action' ) );
        add_action( 'rest_api_init', array( $this, 'init_rest' ) );

		// Load API for generic admin functions
		// if ( is_admin() ) {
		// 	$this->admin = new BP_Share_Posts_Admin_API();
		// }

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
	} // End __construct ()

	/**
	 * Register required shortcodes
	 */
	public function register_shortcodes () {
		add_shortcode( 'bp-share-post-button', array( $this, 'shortcode_share_post' ) );
		add_shortcode( 'bp-share-activity-button', array( $this, 'shortcode_share_activity' ) );
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-share', esc_url( $this->assets_url ) . 'js/share' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
		wp_enqueue_script( $this->_token . '-share' );

		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
		wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'bp-share-posts', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'bp-share-posts';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	}

	/**
	 * Get button HTML.
     *
     * @since NEXT
     *
	 * @param array $atts
	 * @param string $content
	 * @return string
	 */
	public function shortcode_share_post ( $atts, $content ) {
		if ( ! get_current_user_id() ) {
			return;
		}

		if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'activity' ) ) {
			return;
		}

		if ( ! get_the_ID() ) {
		    return;
        }

        $status       = $this->user_share_post_status( get_the_ID() );
        $icon         = apply_filters( 'bp_share_posts_button_icon', '<i class="bp-share-posts-icon icon-export-alt"></i>', 'post' );
        $shared_icon  = apply_filters( 'bp_share_posts_button_shared_icon', '<i class="bp-share-posts-icon icon-ok-circled"></i>', 'post' );
        $label        = apply_filters( 'bp_share_posts_button_label', __( 'Share', 'bp-share-posts' ), 'post' );
        $shared_label = apply_filters( 'bp_share_posts_button_label_shared', __( 'Shared', 'bp-share-posts' ), 'post' );

		$this->enqueue_scripts();

		return $this->build_button( 'post', get_the_ID(), $icon, $shared_icon, $label, $shared_label, $status );
	}

    /**
     * Get button HTML.
     *
     * @since NEXT
     *
     * @param array $atts
     * @param string $content
     * @return string
     */
    public function shortcode_share_activity ( $atts, $content ) {
        if ( ! get_current_user_id() ) {
            return;
        }

        if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'activity' ) ) {
            return;
        }

        $activity_id = bp_get_activity_id();

        if ( ! $activity_id ) {
            return;
        }

        $status       = $this->user_share_activity_status( $activity_id );
        $icon         = apply_filters( 'bp_share_posts_button_icon', '<i class="bp-share-posts-icon icon-export-alt"></i>', 'activity' );
        $shared_icon  = apply_filters( 'bp_share_posts_button_shared_icon', '<i class="bp-share-posts-icon icon-ok-circled"></i>', 'activity' );
        $label        = apply_filters( 'bp_share_posts_button_label', __( 'Share', 'bp-share-posts' ), 'activity' );
        $shared_label = apply_filters( 'bp_share_posts_button_label_shared', __( 'Shared', 'bp-share-posts' ), 'activity' );

        $this->enqueue_scripts();

        return $this->build_button( 'activity', $activity_id, $icon, $shared_icon, $label, $shared_label, $status );
    }

    /**
     * Build a button.
     *
     * @since NEXT
     *
     * @param string $type 'post' or 'activity'
     * @param int    $item_id
     * @param string $share_icon
     * @param string $shared_icon
     * @param string $share_label
     * @param string $shared_label
     * @param string $status 'shared' or ''
     * @return string
     */
	public function build_button(
	    $type,
        $item_id,
        $share_icon,
        $shared_icon,
        $share_label,
        $shared_label,
        $status
    ) {
        $url   = add_query_arg( array(
            'type'        => $type,
            'status'      => $status == 'shared' ? '' : 'shared',
            'redirect_to' => $_SERVER['REQUEST_URI'],
        ), trailingslashit( bp_get_loggedin_user_link() . bp_get_activity_root_slug() . '/share/' . $item_id ) );

        $current_icon  = $status == 'shared' ? $shared_icon : $share_icon;
        $current_label = $status == 'shared' ? $shared_label : $share_label;

        $html = sprintf(
            '<a href="%1$s" class="bp-share-posts-button %8$s" data-type="%11$s" data-share-icon="%10$s" data-shared-icon="%2$s" data-share-label="%9$s" data-shared-label="%3$s" data-item-id="%4$s" data-status="%7$s" >%5$s <span class="bp-share-posts-label">%6$s</span></a>',
            esc_attr( $url ),
            esc_attr( $shared_icon ),
            esc_attr( $shared_label ),
            $item_id,
            $current_icon,
            $current_label,
            esc_attr( $status ),
            $status == 'shared' ? 'bp-share-posts-shared' : '',
            esc_attr( $share_label ),
            esc_attr( $share_icon ),
            esc_attr( $type )
        );

        return apply_filters( 'bp_share_posts_button', $html, $type, $status );
    }

	/**
	 * Intercept share action.
	 */
	public function intercept_share_action () {
		if ( ! bp_is_current_component( 'activity' ) || bp_current_action() != 'share' ) {
			return;
		}

        $type        = filter_input( INPUT_GET, 'type', FILTER_SANITIZE_STRING );
        $status      = filter_input( INPUT_GET, 'status', FILTER_SANITIZE_STRING );
        $redirect_to = urldecode( filter_input( INPUT_GET, 'redirect_to', FILTER_SANITIZE_STRING ) );
        $item_id     = (int) bp_action_variable( 0 );

        if ( ! $type || ! $item_id ) {
            return;
        }

		if ( ! $redirect_to ) {
			// No redirect defined, use activity component instead
			$redirect_to = trailingslashit( bp_get_loggedin_user_link() . bp_get_activity_root_slug() );
		}

		if ( $type == 'post' ) {
            if ( $status == 'shared' ) {
                // Share post
                $this->share_post( $item_id );
            } else {
                // Unshare
                $this->unshare_post( $item_id );
            }
        }

		// Redirect
		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Share the post on activity wall
	 * @param int $post_id
	 * @param bool Returns true if shared, otherwise false
     * @return bool|int|WP_Error
	 */
	public function share_post ( $post_id ) {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$user = wp_get_current_user();
		$post = get_post( $post_id );

		if ( ! $post ) {
		    return false;
        }

		if ( $this->share_exists( $user_id, $post_id ) ) {
			return true;
		}

		// Create activity
		$sharer_url = bp_core_get_userlink( $user_id, false, true );
		$sharer_username = bp_core_get_user_displayname( $user_id );
		$author_url = bp_core_get_userlink( $post->post_author, false, true );
		$author_username = bp_core_get_user_displayname( $post->post_author );
		$post_url = get_permalink( $post );
		
		$action = sprintf(
			__( '<a href="%1$s">%2$s</a> shared <a href="%3$s">%4$s\'s</a> post <a href="%5$s">%6$ss</a>', 'bp-share-posts' ),
			$sharer_url,
			$sharer_username,
			$author_url,
			$author_username,
			$post_url,
			$post->post_title
		);

		if ( empty( $post->post_excerpt ) ) {
			$excerpt = wp_kses_post( wp_trim_words( $post->post_content, 20 ) );
		} else {
			$excerpt = wp_kses_post( $post->post_excerpt ); 
		}

		$activity_id = bp_activity_add( array(
			'action' => apply_filters( 'bp_share_posts_activity_action', $action, $post, $user, 'post' ),
			'content' => apply_filters( 'bp_share_posts_activity_content', $excerpt, $post, $user, 'post' ),
			'component' => 'blogs',
			'type' => 'share',
			'primary_link' => $post_url,
			'item_id' => $post->ID,
			'secondary_item_id' => $post->post_author,
		) );

		if ( ! $activity_id || is_wp_error( $activity_id ) ) {
		    return $activity_id;
        }

		$this->set_user_as_shared_post( $user_id, $post_id );

		return $activity_id;
	}

    /**
     * Unshare a post.
     *
     * @since NEXT
     *
     * @param int $post_id
     * @return bool Returns true if unshared, otherwise false.
     */
	public function unshare_post( $post_id ) {
        $user_id = get_current_user_id();

        if ( ! $user_id ) {
            return false;
        }

        $activity_id = $this->share_exists( $user_id, $post_id );

        if ( ! $activity_id ) {
            return true;
        }

        if ( ! bp_activity_delete( [ 'id' => $activity_id ] ) ) {
            return false;
        }

        $this->remove_user_as_shared_post( $user_id, $post_id );

        return true;
    }

    /**
     * Share the activity on activity wall.
     *
     * @since NEXT
     *
     * @param int $item_id
     * @param bool Returns true if shared, otherwise false
     * @return bool|int|WP_Error
     */
    public function share_activity( $item_id ) {
        $user_id = get_current_user_id();

        if ( ! $user_id ) {
            return false;
        }

        $activity = new BP_Activity_Activity( $item_id );

        if ( ! $activity ) {
            return false;
        }

        if ( $this->shared_activity_exists( $item_id, $user_id ) ) {
            return true;
        }

        // Create activity
        $user            = wp_get_current_user();
        $sharer_url      = bp_core_get_userlink( $user_id, false, true );
        $sharer_username = bp_core_get_user_displayname( $user_id );
        $author_url      = bp_core_get_userlink( $activity->user_id, false, true );
        $author_username = bp_core_get_user_displayname( $activity->user_id );
        $activity_url    = bp_activity_get_permalink( $activity->id, $activity );

        $action = sprintf(
            __( '<a href="%1$s">%2$s</a> shared <a href="%3$s">%4$s\'s</a> <a href="%5$s">wall post</a>', 'bp-share-posts' ),
            $sharer_url,
            $sharer_username,
            $author_url,
            $author_username,
            $activity_url
        );

        $content = apply_filters_ref_array( 'bp_get_activity_content_body', array( $activity->content, $activity ) );
        $excerpt = wp_kses_post( wp_trim_words( $content, 20 ) );

        $activity_id = bp_activity_add( [
            'action'            => apply_filters( 'bp_share_posts_activity_action', $action, $activity, $user, 'activity' ),
            'content'           => apply_filters( 'bp_share_posts_activity_content', $excerpt, $activity, $user, 'activity' ),
            'component'         => 'activity',
            'type'              => 'share',
            'primary_link'      => $activity_url,
            'item_id'           => $activity->id,
            'secondary_item_id' => $activity->user_id,
        ] );

        if ( ! $activity_id || is_wp_error( $activity_id ) ) {
            return $activity_id;
        }

        $this->set_user_as_shared_activity( $user_id, $activity->id );

        return $activity_id;
    }

    /**
     * Unshare an activity.
     *
     * @since NEXT
     *
     * @param int $item_id
     * @return bool Returns true if unshared, otherwise false.
     */
    public function unshare_activity( $item_id ) {
        $user_id = get_current_user_id();

        if ( ! $user_id ) {
            return false;
        }

        $activity_id = $this->shared_activity_exists( $user_id, $item_id );

        if ( ! $activity_id ) {
            return true;
        }

        if ( ! bp_activity_delete( [ 'id' => $activity_id ] ) ) {
            return false;
        }

        $this->remove_user_as_shared_activity( $user_id, $item_id );

        return true;
    }

    /**
     * Get users who have shared post.
     *
     * @since NEXT
     *
     * @param int $post_id
     * @return array
     */
    public function get_users_shared_post( $post_id ) {
        $shared_by = get_post_meta( $post_id, 'bp_share_posts_by' );

        if ( ! is_array( $shared_by ) ) {
            if ( $shared_by ) {
                $shared_by = [ $shared_by ];
            } else {
                $shared_by = [];
            }
        }

        return array_map( 'intval', $shared_by );
    }

    /**
     * Get users who have shared activity.
     *
     * @since NEXT
     *
     * @param int $item_id
     * @return array
     */
    public function get_users_shared_activity( $item_id ) {
        $shared_by = bp_activity_get_meta( $item_id, 'bp_share_posts_by' );

        if ( ! is_array( $shared_by ) ) {
            if ( $shared_by ) {
                $shared_by = [ $shared_by ];
            } else {
                $shared_by = [];
            }
        }

        return array_map( 'intval', $shared_by );
    }

    /**
     * Set user as having shared a post.
     *
     * @since NEXT
     *
     * @param int $user_id
     * @param int $post_id
     */
    public function set_user_as_shared_post( $user_id, $post_id ) {
        $shared_by = $this->get_users_shared_post( $post_id );

        if ( in_array( $user_id, $shared_by ) ) {
            return;
        }

        add_post_meta( $post_id, 'bp_share_posts_by', $user_id );
    }

    /**
     * Remove user as having shared a post.
     *
     * @since NEXT
     *
     * @param int $user_id
     * @param int $post_id
     */
    public function remove_user_as_shared_post( $user_id, $post_id ) {
        delete_post_meta( $post_id, 'bp_share_posts_by', $user_id );
    }

    /**
     * Get if user has shared post.
     *
     * @since NEXT
     *
     * @param int      $post_id
     * @param int|null $user_id
     * @return string 'shared' or ''
     */
    public function user_share_post_status( $post_id, $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return '';
        }

        $shared_by = $this->get_users_shared_post( $post_id );

        return in_array( $user_id, $shared_by ) ? 'shared' : '';
    }

    /**
     * Set user as having shared an activity.
     *
     * @since NEXT
     *
     * @param int $user_id
     * @param int $item_id
     */
    public function set_user_as_shared_activity( $user_id, $item_id ) {
        $shared_by = $this->get_users_shared_activity( $item_id );

        if ( in_array( $user_id, $shared_by ) ) {
            return;
        }

        bp_activity_add_meta( $item_id, 'bp_share_posts_by', $user_id );
    }

    /**
     * Remove user as having shared an activity.
     *
     * @since NEXT
     *
     * @param int $user_id
     * @param int $item_id
     */
    public function remove_user_as_shared_activity( $user_id, $item_id ) {
        bp_activity_delete_meta( $item_id, 'bp_share_posts_by', $user_id );
    }

    /**
     * Get if user has shared activity.
     *
     * @since NEXT
     *
     * @param int      $post_id
     * @param int|null $user_id
     * @return string 'shared' or ''
     */
    public function user_share_activity_status( $post_id, $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return '';
        }

        $shared_by = $this->get_users_shared_activity( $post_id );

        return in_array( $user_id, $shared_by ) ? 'shared' : '';
    }

	/**
	 * Determine if user has already shared the post
	 *
	 * @param int $user_id
	 * @param int $post_id
	 * @return bool|int Returns activity ID if found.
	 */
	public function share_exists( $user_id, $post_id ) {
		$activity = bp_activity_get( array(
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
				array(
					'column' => 'user_id',
					'compare' => '=',
					'value' => $user_id,
				),
				array(
					'column' => 'item_id',
					'compare' => '=',
					'value' => $post_id,
				)
			)
		) );

		if ( empty( $activity['activities'] ) ) {
		    return false;
        }

		return $activity['activities'][0]->id;
	}

    /**
     * Determine if user has already shared the activity.
     *
     * @since NEXT
     *
     * @param int $user_id
     * @param int $post_id
     * @return bool|int Returns activity ID if found.
     */
    public function shared_activity_exists( $user_id, $post_id ) {
        $activity = bp_activity_get( [
            'filter_query' => [
                'relation' => 'AND',
                [
                    'column'  => 'component',
                    'compare' => '=',
                    'value'   => 'activity',
                ],
                [
                    'column'  => 'type',
                    'compare' => '=',
                    'value'   => 'share',
                ],
                [
                    'column'  => 'user_id',
                    'compare' => '=',
                    'value'   => $user_id,
                ],
                [
                    'column'  => 'item_id',
                    'compare' => '=',
                    'value'   => $post_id,
                ],
            ],
        ] );

        if ( empty( $activity['activities'] ) ) {
            return false;
        }

        return $activity['activities'][0]->id;
    }

    /**
     * Handle AJAX.
     */
    public function ajax_action() {
        $success = false;
        $type    = filter_input( INPUT_POST, 'type', FILTER_SANITIZE_STRING );
        $status  = filter_input( INPUT_POST, 'status', FILTER_SANITIZE_STRING );
        $item_id = (int) filter_input( INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT );

        if ( $item_id ) {
            if ( $type == 'post' ) {
                if ( $status == 'share' ) {
                    $success = $this->share_post( $item_id );
                } else {
                    $success = $this->unshare_post( $item_id );
                }
            } elseif ( $type == 'activity' ) {
                if ( $status == 'share' ) {
                    $success = $this->share_activity( $item_id );
                } else {
                    $success = $this->unshare_activity( $item_id );
                }
            }
        }

        echo json_encode( [
            'success' => $success,
        ] );

        wp_die();
    }

    /**
     * Init REST API.
     *
     * @since NEXT
     */
	public function init_rest() {
	    $this->rest_api = new BP_Share_Posts_REST_API();
    }

	/**
	 * Main BP_Share_Posts Instance
	 *
	 * Ensures only one instance of BP_Share_Posts is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see BP_Share_Posts()
	 * @return Main BP_Share_Posts instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
