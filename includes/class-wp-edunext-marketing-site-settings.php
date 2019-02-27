<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_eduNEXT_Marketing_Site_Settings {

	/**
	 * The single instance of WP_eduNEXT_Marketing_Site_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	/**
	 * Available settings for plugin.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $active_tab = '';

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'wpt_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Open edX Wordpress Integrator', 'wp-edunext-marketing-site' ) , __( 'Open edX Wordpress Integrator', 'wp-edunext-marketing-site' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();
    	$this->parent->enqueue_commons_script();

    	wp_register_style( $this->parent->_token . '-settings-css', $this->parent->assets_url . 'css/settings.css', array(), '1.0.0' );
    	wp_enqueue_style($this->parent->_token . '-settings-css' );

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery', 'edunext_commons' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'wp-edunext-marketing-site' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$settings['general'] = array(
			'title'					=> __( 'General settings', 'wp-edunext-marketing-site' ),
			'description'			=> __( 'Basic settings to get your marketing site integrated.', 'wp-edunext-marketing-site' ),
			'fields'				=> array(
				array(
					'id' 			=> 'lms_base_url',
					'label'			=> __( 'Base domain for the open edX domain' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'The url where your courses are located.', 'wp-edunext-marketing-site' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'https://mylms.edunext.io', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'button_class_generic',
					'label'			=> __( 'CSS classes for the buttons ' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'You can override the specific buttons in the Enrollment tab' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'container_class_generic',
					'label'			=> __( 'CSS classes for the container of the buttons' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'You can override the specific buttons in the Enrollment tab' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'color_class_generic',
					'label'			=> __( 'CSS classes for the color of the buttons' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'You can override the specific buttons in the Enrollment tab' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 				=> 'enable_woocommerce_integration',
					'label'				=> __( 'Enable Eox-core Woocommerce integrations' , 'wp-edunext-marketing-site' ),
					'description'		=> __( 'Features: Checkout pre-filling', 'wp-edunext-marketing-site' ),
					'type'				=> 'checkbox',
					'default'			=> false,
					'placeholder'		=> __( '', 'wp-edunext-marketing-site' ),
					'advanced_setting' 	=> true
				),
				array(
					'id' 				=> 'enrollment_api_location',
					'label'				=> __( 'Enrollment API Location' , 'wp-edunext-marketing-site' ),
					'description'		=> __( 'Normally you don\'t need to change it.', 'wp-edunext-marketing-site' ),
					'type'				=> 'text',
					'default'			=> '/api/enrollment/v1/',
					'placeholder'		=> __( '', 'wp-edunext-marketing-site' ),
					'advanced_setting' 	=> true
				),
				array(
					'id' 				=> 'user_enrollment_url',
					'label'				=> __( 'Button URL for an user to enroll' , 'wp-edunext-marketing-site' ),
					'description'		=> __( 'Normally you don\'t need to change it.', 'wp-edunext-marketing-site' ),
					'type'				=> 'text',
					'default'			=> '/register?course_id=%course_id%&enrollment_action=enroll',
					'placeholder'		=> __( '', 'wp-edunext-marketing-site' ),
					'advanced_setting' 	=> true
				),
				array(
					'id' 				=> 'course_has_not_started_url',
					'label'				=> __( 'Button URL when course has not yet started' , 'wp-edunext-marketing-site' ),
					'description'		=> __( 'Normally you don\'t need to change it.', 'wp-edunext-marketing-site' ),
					'type'				=> 'text',
					'default'			=> '/dashboard',
					'placeholder'		=> __( '', 'wp-edunext-marketing-site' ),
					'advanced_setting' 	=> true
				),

			)
		);

		$settings['navigation'] = array(
			'title'					=> __( 'Navigation Menu Settings', 'wp-edunext-marketing-site' ),
			'description'			=> __( 'Configurations to get your nav menu working with the Marketing Site Integration from eduNEXT.', 'wp-edunext-marketing-site' ),
			'fields'				=> array(
				array(
					'id' 			=> 'is_logged_in_cookie_name',
					'label'			=> __( 'Name of the shared cookie that signals an open session' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'For standalone open edX installations usually `edxloggedin`' ),
					'type'			=> 'text',
					'default'		=> 'edunextloggedin',
					'placeholder'	=> __( 'edunextloggedin', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'user_info_cookie_name',
					'label'			=> __( 'Name of the shared cookie that holds the user info' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'For standalone open edX installations usually `edx-user-info`' ),
					'type'			=> 'text',
					'default'		=> 'edunext-user-info',
					'placeholder'	=> __( 'edunext-user-info', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'advanced_login_location',
					'label'			=> __( 'Login location (advanced)' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'Location of the login handler. Only change this if you know exactly what you are doing.' ),
					'type'			=> 'text',
					'default'		=> 'login',
					'placeholder'	=> __( 'login', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'advanced_registration_location',
					'label'			=> __( 'Registration location (advanced)' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'Location of the registration handler. Only change this if you know exactly what you are doing.' ),
					'type'			=> 'text',
					'default'		=> 'register',
					'placeholder'	=> __( 'register', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'advanced_dashboard_location',
					'label'			=> __( 'Dashboard location (advanced)' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'Location of the Dashboard handler. Only change this if you know exactly what you are doing.' ),
					'type'			=> 'text',
					'default'		=> 'dashboard',
					'placeholder'	=> __( 'dashboard', 'wp-edunext-marketing-site' )
				)

			)
		);

		$settings['enrollment'] = array(
			'title'					=> __( 'Enrollment buttons', 'wp-edunext-marketing-site' ),
			'description'			=> __( 'These settings modify the shortcodes for enrollment buttons.', 'wp-edunext-marketing-site' ),
			'fields'				=> array(
				// Button Enroll
				array(
					'id' 			=> 'label_enroll',
					'label'			=> __( 'Text for the button to enroll' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'It will be showed when the user is not yet registered but can register directly.', 'wp-edunext-marketing-site' ),
					'type'			=> 'text',
					'default'		=> 'Enroll',
					'placeholder'	=> __( 'Enroll', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'button_class_enroll',
					'label'			=> __( 'CSS classes for the enroll button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'container_class_enroll',
					'label'			=> __( 'CSS classes for the container to the enroll button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'color_class_enroll',
					'label'			=> __( 'CSS classes for the color of the enroll button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'separator_enroll',
					'label'			=> '-------------------------------------',
					'description'	=> '',
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> '',
				),

				// Button Go To Course
				array(
					'id' 			=> 'label_go_to_course',
					'label'			=> __( 'Text for the go to the course button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'It will be showed when the user is already registered and can access the course content inmediatly.', 'wp-edunext-marketing-site' ),
					'type'			=> 'text',
					'default'		=> 'Go to the course',
					'placeholder'	=> __( 'Go to the course', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'button_class_go_to_course',
					'label'			=> __( 'CSS classes for the go to the course button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'container_class_go_to_course',
					'label'			=> __( 'CSS classes for the container of the go to the course button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'color_class_go_to_course',
					'label'			=> __( 'CSS classes for the color in the go to the course button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'separator_go_to_course',
					'label'			=> '-------------------------------------',
					'description'	=> '',
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> '',
				),

				// Button Course Has Not started
				array(
					'id' 			=> 'label_course_has_not_started',
					'label'			=> __( 'Text for when the course has not started yet' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'It will be showed when the user is already registered but the course has not started yet.', 'wp-edunext-marketing-site' ),
					'type'			=> 'text',
					'default'		=> 'The course has not yet started',
					'placeholder'	=> __( 'The course has not yet started', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'button_class_course_has_not_started',
					'label'			=> __( 'CSS classes for when the course has not started yet' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'container_class_course_has_not_started',
					'label'			=> __( 'CSS classes for the container when the course has not started yet' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'color_class_course_has_not_started',
					'label'			=> __( 'CSS classes for the color when the course has not started yet' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'separator_course_has_not_started',
					'label'			=> '-------------------------------------',
					'description'	=> '',
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> '',
				),


				// Button Invitation Only
				array(
					'id' 			=> 'label_invitation_only',
					'label'			=> __( 'Text for the Invitation only button' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'It will be showed when the course is private and can be accessed only by invitation.', 'wp-edunext-marketing-site' ),
					'type'			=> 'text',
					'default'		=> 'Invitation only',
					'placeholder'	=> __( 'Invitation only', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'button_class_invitation_only',
					'label'			=> __( 'CSS classes for when the course is invitation only' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'container_class_invitation_only',
					'label'			=> __( 'CSS classes for the container when the course is invitation only' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'color_class_invitation_only',
					'label'			=> __( 'CSS classes for the color when the course is invitation only' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'separator_invitation_only',
					'label'			=> '-------------------------------------',
					'description'	=> '',
					'type'			=> '',
					'default'		=> '',
					'placeholder'	=> '',
				),

				// Button Enrollment Closed
				array(
					'id' 			=> 'label_enrollment_closed',
					'label'			=> __( 'Text for when the enrollment has ended' , 'wp-edunext-marketing-site' ),
					'description'	=> __( 'It will be showed when the enrollment end date has already passed.', 'wp-edunext-marketing-site' ),
					'type'			=> 'text',
					'default'		=> 'Registration is closed',
					'placeholder'	=> __( 'Registration is closed', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'button_class_enrollment_closed',
					'label'			=> __( 'CSS classes for when the enrollment has ended' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'container_class_enrollment_closed',
					'label'			=> __( 'CSS classes for the container when the enrollment has ended' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				),
				array(
					'id' 			=> 'color_class_enrollment_closed',
					'label'			=> __( 'CSS classes for the color when the enrollment has ended' , 'wp-edunext-marketing-site' ),
					'description'	=> __( '' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'wp-edunext-marketing-site' )
				)
			)
		);


		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				$this->active_tab = $section;
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field(
						$field['id'],
						$field['label'],
						array( $this->parent->admin, 'display_field' ),
						$this->parent->_token . '_settings',
						$section,
						array( 'field' => $field, 'prefix' => $this->base )
					);
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'Open edX Wordpress Integrator Settings | By eduNEXT' , 'wp-edunext-marketing-site' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";


				ob_start();
				// Get settings fields
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				if ($this->active_tab === 'general') {
					$this->parent->admin->show_advance_settings_toggle();
				}
				do_action($this->active_tab . '_after_settings_page_html');
				$html .= ob_get_clean();
				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'wp-edunext-marketing-site' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";

			$html .= '<a class="footer-logo edunext col-12" href="https://www.edunext.co" target="_self">
                      <img src="https://d1uwn6yupg8lfo.cloudfront.net/logos/logo-small.png" alt="eduNEXT - World class open edX services provider | www.edunext.co">
                      </a>' . "\n";


		$html .= '</div>' . "\n";

		echo $html;
	}

	/**
	 * Main WP_eduNEXT_Marketing_Site_Settings Instance
	 *
	 * Ensures only one instance of WP_eduNEXT_Marketing_Site_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WP_eduNEXT_Marketing_Site()
	 * @return Main WP_eduNEXT_Marketing_Site_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
