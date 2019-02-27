<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_eduNEXT_Marketing_Site {

	/**
	 * The single instance of WP_eduNEXT_Marketing_Site.
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
		$this->_token = 'wp-edunext-marketing-site';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load shortcodes
		add_action( 'wp_enqueue_scripts', array( $this, 'enroll_integration_scripts' ), 10 );
		add_shortcode( 'edunext_enroll_button', array( $this, 'edunext_enroll_button' ) );

		// Helpful tips for users using the shortcode
		add_action( 'add_meta_boxes', array( $this, 'add_shortcode_help_meta_box') );

		// Add meta box for menus
		$this->menu = new WP_eduNEXT_Marketing_Site_Menu();

		// Add attributes for menus items
		add_action( 'init', array( 'Edx_Walker_Nav_Menu_Edit', 'setup' ) );

		if ( get_option('wpt_enable_woocommerce_integration') ) {
			$this->woocommerce = new WP_eduNEXT_Woocommerce_Integration();
		}

		if ( is_admin() ) {
			// Load API for generic admin functions
			$this->admin = new WP_eduNEXT_Marketing_Site_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
	} // End __construct ()

	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new WP_eduNEXT_Marketing_Site_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new WP_eduNEXT_Marketing_Site_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
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
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
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
	 * Register scripts to be called at the shortcodes.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enroll_integration_scripts ( $hook = '' ) {
		wp_register_script( 'edunext_enroll_button', esc_url( $this->assets_url ) . 'js/edunextEnrollButton' . $this->script_suffix . '.js' , array( 'jquery', 'edunext_commons' ), $this->_version );
	} // End enroll_integration_scripts ()

	/**
	 * Register commons.js needed shortcode button and for admin.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_commons_script($value='')
	{
		wp_register_script( 'edunext_commons', esc_url( $this->assets_url ) . 'js/commons' . $this->script_suffix . '.js' , array( 'jquery' ), $this->_version );
		wp_localize_script( 'edunext_commons', 'ENEXT_SRV', array(
				'user_info_cookie_name' => get_option('wpt_user_info_cookie_name'),
				'is_loggedin_cookie_name' => get_option('wpt_is_logged_in_cookie_name'),
				'lms_base_url' => get_option('wpt_lms_base_url'),
				'enrollment_api_location' => get_option('wpt_enrollment_api_location', '/api/enrollment/v1/'),
				'user_enrollment_url' => get_option('wpt_user_enrollment_url', '/register?course_id=%course_id%&enrollment_action=enroll'),
				'course_has_not_started_url' => get_option('wpt_course_has_not_started_url', '/dashboard'),
		));
		wp_enqueue_script( 'edunext_commons' );
	}
	/**
	 * Load shortcodes.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function edunext_enroll_button ( $atts ) {

		// TODO: move this function to its own class under lib/..
		// How to use: [edunext_enroll_button course_id="course-v1:edX+Demo+demo_course"]

		// Attributes
		STATIC $unique_id = 1;
		$short_id ='EdnxEnrollButton' . $unique_id++;

		$atts = shortcode_atts(
			array(
				'course_id' => '',
				// ----------
				'button_class_generic' => get_option('wpt_button_class_generic'),
				'container_class_generic' => get_option('wpt_container_class_generic'),
				'color_class_generic' => get_option('wpt_color_class_generic'),
				// ----------
				'label_enroll' => get_option('wpt_label_enroll', 'Enroll'),
				'button_class_enroll' => get_option('wpt_button_class_enroll'),
				'container_class_enroll' => get_option('wpt_container_class_enroll'),
				'color_class_enroll' => get_option('wpt_color_class_enroll'),
				// ----------
				'label_go_to_course' => get_option('wpt_label_go_to_course', 'Go to the course'),
				'button_class_go_to_course' => get_option('wpt_button_class_go_to_course'),
				'container_class_go_to_course' => get_option('wpt_container_class_go_to_course'),
				'color_class_go_to_course' => get_option('wpt_color_class_go_to_course'),
				// ----------
				'label_course_has_not_started' => get_option('wpt_label_course_has_not_started', 'The course has not yet started'),
				'button_class_course_has_not_started' => get_option('wpt_button_class_course_has_not_started'),
				'container_class_course_has_not_started' => get_option('wpt_container_class_course_has_not_started'),
				'color_class_course_has_not_started' => get_option('wpt_color_class_course_has_not_started'),
				// ----------
				'label_invitation_only' => get_option('wpt_label_invitation_only', 'Invitation only'),
				'button_class_invitation_only' => get_option('wpt_button_class_invitation_only'),
				'container_class_invitation_only' => get_option('wpt_container_class_invitation_only'),
				'color_class_invitation_only' => get_option('wpt_color_class_invitation_only'),
				// ----------
				'label_enrollment_closed' => get_option('wpt_label_enrollment_closed', 'Registration is closed'),
				'button_class_enrollment_closed' => get_option('wpt_button_class_enrollment_closed'),
				'container_class_enrollment_closed' => get_option('wpt_container_class_enrollment_closed'),
				'color_class_enrollment_closed' => get_option('wpt_color_class_enrollment_closed'),
				'hide_if' => '',
			),
			$atts,
			'edunext_enroll_button'
		);

		$this->enqueue_commons_script();
		wp_enqueue_script( 'edunext_enroll_button' );
		wp_localize_script( 'edunext_enroll_button', $short_id, $atts );

		$course_id = $atts['course_id'];

		return "<div class=\"ednx-enroll-button-js\" style=\"display:none\" data-course-id=\"${course_id}\" data-settings=\"${short_id}\"><span>" . $course_id . "</span></div>";

	} // End edunext_enroll_button ()

	public function add_shortcode_help_meta_box()
	{
		$screens = array( 'post', 'page' );

		foreach ( $screens as $screen ) {
		    add_meta_box(
		        'exo-shortcode-help',
		        __( 'Edunext Enroll Button Shortcode Help', 'wp-edunext-marketing-site' ),
		        array($this, 'show_shortcode_help_meta_box'),
		        $screen
		    );
		}
	}

	public function show_shortcode_help_meta_box()
	{
		include('templates/shortcode_help_meta_box.php');
	}

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'wp-edunext-marketing-site', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'wp-edunext-marketing-site';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main WP_eduNEXT_Marketing_Site Instance
	 *
	 * Ensures only one instance of WP_eduNEXT_Marketing_Site is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WP_eduNEXT_Marketing_Site()
	 * @return Main WP_eduNEXT_Marketing_Site instance
	 */
	public static function instance ( $file = '', $version = '1.0.1' ) {
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
