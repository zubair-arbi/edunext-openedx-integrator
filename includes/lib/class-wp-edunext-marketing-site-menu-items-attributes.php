<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once ABSPATH . 'wp-admin/includes/nav-menu.php';

class Edx_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {

	public static $hide_if_not_logged_in = array();
	public static $hide_if_logged_in = array();

	function start_el(&$output, $item, $depth = 0, $args = Array(), $id = 0) {
		$item_output = '';
		parent::start_el($item_output, $item, $depth, $args);
		// Inject $new_fields before: <div class="menu-item-actions description-wide submitbox">
		if ( $new_fields = Edx_Nav_Menu_Item_Custom_Fields::get_field( $item, $depth, $args ) ) {
			$label = '<p class="edx-hide-if">Hide if Open edX user is:</p>';
			$new_html = '<div class="clear">' . $label . $new_fields . '</div>';
			$item_output = preg_replace('/(?=<div[^>]+class="[^"]*submitbox)/', $new_html , $item_output);
		}
		$output .= $item_output;
	}
	public static function additional_fields( $fields ) {

		$fields['hide_if_1'] = array(
			'name' => 'hide_if',
			'label' => __('Logged in', 'edx'),
			'input_type' => 'radio',
			'value' => 'logged in'
		);
		$fields['hide_if_2'] = array(
			'name' => 'hide_if',
			'label' => __('Not logged in', 'edx'),
			'input_type' => 'radio',
			'value' => 'not logged in'
		);
		$fields['hide_if_3'] = array(
			'name' => 'hide_if',
			'label' => __('(never)', 'edx'),
			'input_type' => 'radio',
			'value' => ''
		);
		return $fields;
	}

	public static function filter_out_hidden ($items, $menu, $args) {
		foreach ($items as $item) {
			$hide_if = get_post_meta($item->ID, Edx_Nav_Menu_Item_Custom_Fields::get_menu_item_postmeta_key('hide_if'), true);
			if ($hide_if === 'logged in') {
				array_push(self::$hide_if_logged_in, $item->ID);
			}
			if ($hide_if === 'not logged in') {
				array_push(self::$hide_if_not_logged_in, $item->ID);
			}
		}
	    return $items;
	}

	public static function setup () {
		if (is_admin()) {
			add_filter( 'edx_nav_menu_item_additional_fields', array('Edx_Walker_Nav_Menu_Edit', 'additional_fields'));
			Edx_Nav_Menu_Item_Custom_Fields::setup();
		} else {
			add_filter('wp_get_nav_menu_items', array(__CLASS__, 'filter_out_hidden'), 9, 3);
		}
	}
}


/**
 * Based on https://gist.github.com/kucrut/3804376
 */
class Edx_Nav_Menu_Item_Custom_Fields {
	static $options = array(
		'item_tpl' => '
			<p class="additional-menu-field-{name} description description-thin">
				<label>
					{label}
					<input
						type="{input_type}"
						id="edit-menu-item-{name}-{id}"
						class="widefat code edit-menu-item-{name}"
						name="menu-item-{name}[{id}]"
						{checked}
						value="{value}">
				</label>
			</p>
		',
	);

	static function setup() {
		if ( !is_admin() )
			return;
		$new_fields = apply_filters( 'edx_nav_menu_item_additional_fields', array() );
		if ( empty($new_fields) )
			return;
		self::$options['fields'] = self::get_fields_schema( $new_fields );
		add_filter( 'wp_edit_nav_menu_walker', function () {
			return 'Edx_Walker_Nav_Menu_Edit';
		});
		//add_filter( 'edx_nav_menu_item_additional_fields', array( __CLASS__, '_add_fields' ), 10, 5 );
		add_action( 'save_post', array( __CLASS__, '_save_post' ), 10, 2 );
	}

	static function get_fields_schema( $new_fields ) {
		$schema = array();
		foreach( $new_fields as $name => $field) {
			if (empty($field['name'])) {
				$field['name'] = $name;
			}
			$schema[] = $field;
		}
		return $schema;
	}
	static function get_menu_item_postmeta_key($name) {
		return '_menu_item_' . $name;
	}
	/**
	 * Inject the 
	 * @hook {action} save_post
	 */
	static function get_field( $item, $depth, $args ) {
		$new_fields = '';
		foreach( self::$options['fields'] as $field ) {
			$meta = get_post_meta($item->ID, self::get_menu_item_postmeta_key($field['name']), true);
			if ($field['input_type'] === 'radio') {
				if ($meta === $field['value']) {
					$field['checked'] = 'checked';
				}
				// $field['checked'] = $meta;
			} else {
				$field['value'] = $meta;
			}
			if (!isset($field['checked'])) {
				$field['checked'] = '';
			}
			$field['id'] = $item->ID;

			$new_fields .= str_replace(
				array_map(function($key){ return '{' . $key . '}'; }, array_keys($field)),
				array_values(array_map('esc_attr', $field)),
				self::$options['item_tpl']
			);
		}
		return $new_fields;
	}
	/**
	 * Save the newly submitted fields
	 * @hook {action} save_post
	 */
	static function _save_post($post_id, $post) {
		if ( $post->post_type !== 'nav_menu_item' ) {
			return $post_id; // prevent weird things from happening
		}
		foreach( self::$options['fields'] as $field_schema ) {
			$form_field_name = 'menu-item-' . $field_schema['name'];
			// @todo FALSE should always be used as the default $value, otherwise we wouldn't be able to clear checkboxes
			if (isset($_POST[$form_field_name][$post_id])) {
				$key = self::get_menu_item_postmeta_key($field_schema['name']);
				$value = stripslashes($_POST[$form_field_name][$post_id]);
				update_post_meta($post_id, $key, $value);
			}
		}
	}
}

