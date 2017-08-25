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
		add_action( 'bp_init', array( $this, 'intercept_action' ) );
		add_action( 'wp_ajax_bp_share_post', array( $this, 'ajax_action' ) );

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
		add_shortcode( 'bp-share-post-button', array( $this, 'button_shortcode' ) );
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
	 * Get button HTML
	 * @param array $atts
	 * @param string $content
	 * @return string
	 */
	public function button_shortcode ( $atts, $content ) {
		if ( ! get_current_user_id() ) {
			return;
		}

		if ( ! function_exists( 'buddypress' ) || ! bp_is_active( 'activity' ) ) {
			return;
		}

		$icon        = apply_filters( 'bp_share_posts_button_icon', '<i class="bp-share-posts-icon icon-export-alt"></i>' );
		$shared_icon = apply_filters( 'bp_share_posts_button_shared_icon', '<i class="bp-share-posts-icon icon-ok-circled"></i>' );

		$label = apply_filters( 'bp_share_posts_button_label', __( 'Share', 'bp-share-posts' ) );
		$url   = add_query_arg( array(
			'redirect_to' => $_SERVER['REQUEST_URI']
		), trailingslashit( bp_get_loggedin_user_link() . bp_get_activity_root_slug() . '/share/' . get_the_ID() ) );

		$html = sprintf(
			'<a href="%s" class="bp-share-posts-button" data-shared-icon="%s" data-post-id="%s">%s %s</a>',
			esc_attr( $url ),
			esc_attr( $shared_icon ),
			get_the_ID(),
			$icon,
			$label
		);

		$this->enqueue_scripts();

		return apply_filters( 'bp_share_posts_button', $html );
	}

	/**
	 * Intercept any of our requests
	 */
	public function intercept_action () {
		if ( ! bp_is_current_component( 'activity' ) || bp_current_action() != 'share' ) {
			return;
		}

		// Get the post
		$post_id = (int) bp_action_variable( 0 );

		if ( ! $post_id ) {
			return;
		}

		// Get the redirect
		$redirect_to = isset( $_GET['redirect_to'] ) ? urldecode( $_GET['redirect_to'] ) : '';

		if ( ! $redirect_to ) {
			// No redirect defined, use activity component instead
			$redirect_to = trailingslashit( bp_get_loggedin_user_link() . bp_get_activity_root_slug() );
		}

		// Share post
		$this->share_post( $post_id );

		// Redirect
		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * Share the post on activity wall
	 * @param int $post_id
	 * @param bool Returns true if shared, otherwise false
	 */
	public function share_post ( $post_id ) {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$user = wp_get_current_user();
		$post = get_post( $post_id );

		if ( $this->share_exists( $user_id, $post ) ) {
			return true;
		}

		$author = get_user_by( 'id', $post->post_author );

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

		bp_activity_add( array(
			'action' => apply_filters( 'bp_share_posts_activity_action', $action, $post, $user ),
			'content' => apply_filters( 'bp_share_posts_activity_content', $excerpt, $post, $user ),
			'component' => 'blogs',
			'type' => 'share',
			'primary_link' => $post_url,
			'item_id' => $post->ID,
			'secondary_item_id' => $post->post_author,
		) );

		return true;
	}

	/**
	 * Determine if user has already shared the post
	 *
	 * @param int $user_id
	 * @param WP_Post $post
	 * @return bool
	 */
	public function share_exists( $user_id, $post ) {
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
					'value' => $post->ID,
				)
			)
		) );

		return isset( $activity['activities'], $activity['activities'][0] );
	}

	public function ajax_action () {
		$success = false;
		$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : null;

		if ( $post_id ) {
			$success = $this->share_post( $post_id );
		}

		echo json_encode( array(
			'success' => $success
		) );

		wp_die();
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
