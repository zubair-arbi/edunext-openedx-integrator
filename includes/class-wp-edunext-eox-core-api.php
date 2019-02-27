<?php
/**
 * EOX Core API
 */
class WP_EoxCoreApi

{

	/**
	 * Class Constants
	 */
	const API_VERSION = 'v1';
	const PATH_USER_API = '/eox-core/api/' . self::API_VERSION . '/user/';
	const PATH_ENROLLMENT_API = '/eox-core/api/' . self::API_VERSION . '/enrollment/';
	const PATH_USERINFO_API = '/eox-core/api/' . self::API_VERSION . '/userinfo';

	/**
	 * Default values used to create a new edxapp user
	 */
	private $user_defaults = array(
		'email' => '',
		'username' => '',
		'password' => '',
		'fullname' => '',
		'is_active' => False,
		'activate_user' => False,
	);

	/**
	 * Default values used to create a new enrollemnt
	 */
	private $enroll_defaults = array(
		'username' => '',
		'mode' => '',
		'course_id' => '',
	);

	private static $_instance;
	/**
	 * Main WP_EoxCoreApi Instance
	 *
	 * Ensures only one instance of WP_EoxCoreApi is loaded or can be loaded.
	 *
	 * @since 1.2.0
	 * @static
	 * @see WP_EoxCoreApi()
	 * @return Main WP_EoxCoreApi instance
	 */
	public static function instance($file = '', $version = '1.2.0') {
		if (is_null(self::$_instance)) {
			self::$_instance = new self($file, $version);
		}

		return self::$_instance;
	} // End instance ()


	/**
	 *
	 */
	function __construct() {
		if ( is_admin() ) {
			add_filter('wp-edunext-marketing-site_settings_fields', array($this, 'add_admin_settings'));
			add_action('eoxapi_after_settings_page_html', array($this, 'eoxapi_settings_custom_html'));
			add_action('wp_ajax_save_users_ajax', array($this, 'save_users_ajax'));
			add_action('wp_ajax_get_users_ajax', array($this, 'get_users_ajax'));
			add_action('wp_ajax_get_userinfo_ajax', array($this, 'get_userinfo_ajax'));
			add_action('wp_ajax_save_enrollments_ajax', array($this, 'save_enrollments_ajax'));
		}
	}

	/**
	 * Hook to add a complete tab in the plugin setting's page
	 */
	public function add_admin_settings($settings) {
		$settings['eoxapi'] = array(
			'title' => __('EOX API', 'wp-edunext-marketing-site') ,
			'description' => __('These settings modify the way to interact with the eox api.', 'wp-edunext-marketing-site') ,
			'fields' => array(
				array(
					'id' => 'eox_client_id',
					'label' => __('Client id', 'wp-edunext-marketing-site') ,
					'description' => __('Client id of the open edX instance API.', 'wp-edunext-marketing-site') ,
					'type' => 'text',
					'default' => '',
					'placeholder' => ''
				) ,
				array(
					'id' => 'eox_client_secret',
					'label' => __('Client secret', 'wp-edunext-marketing-site') ,
					'description' => __('Client secret of the open edX instance API.', 'wp-edunext-marketing-site') ,
					'type' => 'text',
					'default' => '',
					'placeholder' => ''
				) ,
				array(
					'id' => 'eox_client_wc_field_mappings',
					'label' => __('User fields mappings', 'wp-edunext-marketing-site') ,
					'description' => __('Mapping of user fields for pre-filling, from Open-edX (extended_profile) to Woocommerce', 'wp-edunext-marketing-site', 'wp-edunext-marketing-site') ,
					'type' => 'text',
					'default' => '',
					'placeholder' => '{"wc_example": "example"}'
				)
			)
		);
		return $settings;
	}

	/**
	 * Renders the custom form in the admin page
	 */
	public function eoxapi_settings_custom_html() {
		include('templates/exoapi_settings_custom_html.php');
	}

	public function handle_ajax_json_input($input) {
		check_ajax_referer('eoxapi');
		$json = json_decode($input);
		if (is_null($json)) {
			$json = json_decode(stripslashes($input));
		}
		if (is_null($json)) {
			$this->add_notice('error', 'Cannot parse as JSON, make sure to enter a valid JSON');
		} else if (!is_array($json)) {
			$this->add_notice('error', 'An array is needed, got ' . gettype($json) . ' instead');
		} else {
			return $json;
		}
		return null;
	}

	/**
	 * Called with AJAX function to POST to enrollment API
	 */
	public function save_enrollments_ajax() {
		$new_enrollments = $this->handle_ajax_json_input($_POST['enrollments']);
		if ($new_enrollments) {
			foreach ($new_enrollments as $enrollment) {
				$this->create_enrollment($enrollment);
			}
		}
		$this->show_notices();
		wp_die();
	}

	/**
	 * Called with AJAX function to POST to users API
	 */
	public function save_users_ajax() {
		$new_users = $this->handle_ajax_json_input($_POST['users']);
		if ($new_users) {
			foreach ($new_users as $user) {
				$this->create_user($user);
			}
		}
		$this->show_notices();
		wp_die();
	}

	/**
	 * Called with AJAX function to POST to users API
	 */
	public function get_users_ajax() {
		$new_users = $this->handle_ajax_json_input($_POST['users']);
		$users_info = [];
		if ($new_users) {
			foreach ($new_users as $user) {
				$users_info[] = $this->get_user_info($user);
			}
		}
		$this->add_notice('user-info', '<pre>' . json_encode($users_info, JSON_PRETTY_PRINT) . '</pre>');
		$this->show_notices();
		wp_die();
	}

	/**
	 * Called with AJAX function to GET usesinfo
	 */
	public function get_userinfo_ajax() {
		$userinfo = $this->userinfo();
		if (is_wp_error($userinfo)) {
			wp_send_json_error($userinfo, 400);
		} else {
			$this->show_notices();
			wp_die();
		}
	}

	/**
	 * Produce an authentication token for the eox api using oauth 2.0
	 */
	public function get_access_token() {
		$token = get_option('wpt_eox_token', '');
		$last_checked = get_option('last_checked_working', 0);
		$five_min_ago = time() - 60 * 5;
		if ($last_checked  > $five_min_ago) {
			return $token;
		}
		$base_url = get_option('wpt_lms_base_url', '');
		if ($token !== '') {
			$url = $base_url . '/oauth2/access_token/' . $token . '/';
			$response = wp_remote_get($url);
			if (is_wp_error($response)) {
				$error_message = $response->get_error_message();
				$this->add_notice('error', $error_message);
				$error = new WP_Error('broke', $error_message, $response);
				return $error;
			}
			$json_reponse = json_decode($response['body']);
			if (!isset($json_reponse->error)) {
				// Cache the last time it was succesfully checked
				update_option('last_checked_working', time());
				// Cached token its still valid, return it
				return $token;
			}
		}

		$client_id = get_option('wpt_eox_client_id', '');
		$client_secret = get_option('wpt_eox_client_secret', '');
		$args = array(
			'body' => array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'grant_type' => 'client_credentials'
			)
		);
		$url = $base_url . '/oauth2/access_token/';
		$response = wp_remote_post($url, $args);
		$json_reponse = is_wp_error($response) ? False : json_decode($response['body']);
		if (is_wp_error($response) || isset($json_reponse->error)) {
			$error_message = is_wp_error($response) ? $response->get_error_message() : $json_reponse->error;
			$this->add_notice('error', $error_message);
			return new WP_Error('broke', __("Couldn't call the API to get a new", "eox-core-api") , $response);
		}
		$token = $json_reponse->access_token;
		update_option('wpt_eox_token', $token);
		return $token;
	}

	/**
	 * API calls to get current user info
	 */
	public function userinfo() {
		$data = array();
		$ref = '';
		$api_url = self::PATH_USERINFO_API;
		$success_message = 'Userinfo reading ok!';
		return $this->api_call($api_url, $data, $ref, $success_message);
	}

	/**
	 * Function to execute the API calls required to make a new enrollment
	 */
	public function create_enrollment($args) {
		$data = wp_parse_args($args, $this->enroll_defaults);
		$api_url = self::PATH_ENROLLMENT_API;
		$ref = $data['username'];
		$success_message = 'Enrollment success!';
		return $this->api_call($api_url, $data, $ref, $success_message);
	}

	/**
	 * Function to execute the API calls required to make a new edxapp user
	 */
	public function create_user($args) {
		$data = wp_parse_args($args, $this->user_defaults);
		$api_url = self::PATH_USER_API;
		$ref = $data['email'] ?: $data['username'] ?: $data['fullname'];
		$success_message = 'User creation success!';
		return $this->api_call($api_url, $data, $ref, $success_message);
	}

	/**
	 * Function to execute the API calls required to get an existing edxapp user
	 */
	public function get_user_info($args) {
		$args = (array)$args;
		$api_url = self::PATH_USER_API;
		$ref = $args['email'] ?: $args['username'] ?: '';
		$success_message = 'User fetching success!';
		$api_url .= '?' . http_build_query($args);
		return $this->api_call($api_url, NULL, $ref, $success_message);
	}

	/**
	 * Generic api call method
	 */
	public function api_call($api_url, $data, $ref, $success_message) {
		$token = $this->get_access_token();
		if (!is_wp_error($token)) {
			$url = get_option('wpt_lms_base_url', '') . $api_url;
			if (empty($data)) {
				$response = wp_remote_get($url, array(
					'headers' => 'Authorization: Bearer ' . $token
				));
			} else {
				$response = wp_remote_post($url, array(
					'headers' => 'Authorization: Bearer ' . $token,
					'body' => $data
				));
			}
			if (is_wp_error($response)) {
				return $response;
			}
			$errors = $this->get_response_errors($response, $ref);
			foreach ($errors as $err) {
				$this->add_notice('error', $err);
			}
			if (empty($errors)) {
				$this->add_notice('notice-success', $success_message . ' <i>(' . $ref . ')</i>');
				return json_decode($response['body']);
			} else {
				return new WP_Error('eox-api-error', implode(', ', $errors));
			}
		} else {
			return $token;
		}
	}

	public function get_response_errors($response, $ref)
	{
		$response_json = json_decode($response['body']);
		$errors = array();

		if (is_null($response_json) && $response['response']['code'] === 404) {
			$errors[] = '404 - eox-core is likely not installed on the remote server';
		}
		else if (is_null($response_json)) {
			$errors[] = 'non-json response, server returned status code ' . $response['response']['code'];
		}
		else if ($response['response']['code'] !== 200) {
			$errors = array_merge($errors, $this->handle_api_errors($response_json, $ref));
		}

		return $errors;
	}

	/**
	 *
	 */
	public function handle_api_errors($json, $ref) {
		$errors = [];
		if (isset($json->detail)) {
			$errors[] = $json->detail . ' (' . $ref . ')';
		}
		if (isset($json->non_field_errors)) {
			foreach ($json->non_field_errors as $value) {
				$errors[] = $value . ' (' . $ref . ')';
			}
		}
		$valid_error_keys = array_merge(array_keys($this->user_defaults), array_keys($this->enroll_defaults));
		foreach ($valid_error_keys as $key) {
			if (isset($json->$key)) {
				foreach ($json->$key as $value) {
					$errors[] = ucfirst($key) . ': ' . $value . ' <i>(' . $ref . ')</i>';
				}
			}
		}
		return $errors;
	}

	public function add_notice($type, $message) {
		if (isset(WP_eduNEXT_Marketing_Site()->admin)) {
			WP_eduNEXT_Marketing_Site()->admin->add_notice($type, $message);
		}
	}

	public function show_notices() {
		if (isset(WP_eduNEXT_Marketing_Site()->admin)) {
			WP_eduNEXT_Marketing_Site()->admin->show_notices();
		}
	}

	public function activate_woocommerce_integration() {
		add_action( 'woocommerce_order_status_processing', array($this, 'handle_payment_successful_result'), 10, 1 );
	}


	public function handle_payment_successful_result( $order_id ) {
		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		$user = wp_get_current_user();
		$course_items_count = 0;
		foreach ( $items as $item ) {
			$product = $item->get_product();
			$is_course_item = False;
			$mode = $product->get_attribute('mode');
			if (empty($mode)) {
				$mode = 'honor';
			}
			foreach (['course_id', 'bundle_id'] as $key) {
				$attr_course_id = $product->get_attribute($key);
				if (!$attr_course_id) {
					$attr_course_id = $product->get_attribute($key . 's');
				}
				if ($attr_course_id) {
					$is_course_item = True;
					$ids = explode('|', $attr_course_id);
					foreach ($ids as $id) {
						$response = WP_EoxCoreApi()->create_enrollment([
							'email' => $user->user_email,
							$key => trim($id),
							'mode' => $mode,
							'force' => True
						]);
						if (is_wp_error($response)) {
							error_log($response->get_error_message());
						}
					}
				}
			}
			if ($is_course_item) {
				$course_items_count++;
			}
		}
		if (count($items) == $course_items_count) {
			$order->update_status('completed');
		}
	}


}
