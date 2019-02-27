<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once("vendor/parser.php");

class WP_eduNEXT_Woocommerce_Integration {

    /**
     * Userinfo cache
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $eox_user_info;

    /**
     * Constructor function.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function __construct($value='')
    {
        add_action( 'woocommerce_checkout_get_value', array( $this, 'prefill_with_eox_core_data' ), 20, 2 );
    }

    /**
     * Callback to pre-fill 
     * @access public
     * @since  1.0.0
     * @param  string $value The value to show of the HTML input
     * @param  string $input Description of post type
     * @return string
     */
    public function prefill_with_eox_core_data( $value, $input ) {

        $current_user = wp_get_current_user();
        $fields = array(
            /* $woocommerce_name => $edx_name */
            'email' => 'email',
            'billing_country' => 'country',
            'billing_address_1' => 'mailing_address',
        );
        $extra_fields_mapped = array(
            /* $woocommerce_name => $edx_name */
            'billing_company' => 'company',
            'billing_state' => 'state',
            'billing_first_name' => 'first_name',
            'billing_last_name' => 'last_name',
            'billing_city' => 'city',
            'billing_postcode' => 'zip',
        );

        $mappings = get_option('wpt_eox_client_wc_field_mappings', '');
        $json_mappings = json_decode($mappings, true);
        if ($json_mappings) {
            $extra_fields_mapped = array_merge($json_mappings, $extra_fields_mapped);
        }


        if ( $current_user->ID !== 0 && empty($value) ) {        
            if (empty($this->eox_user_info)) {
                $this->eox_user_info = WP_EoxCoreApi()->get_user_info(['email' => $current_user->user_email]);
                if ( is_wp_error( $this->eox_user_info ) ) {
                   echo '<div id="message" class="error"><p>' . $this->eox_user_info->get_error_message() . '</p></div>';
                   return $value;
                }
            }

            foreach ($fields as $woocommerce_name => $edx_name) {
                if ($woocommerce_name === $input && !empty($this->eox_user_info->$edx_name)) {
                    $value = $this->eox_user_info->$edx_name;
                }
            }

            foreach ($this->eox_user_info->extended_profile as $attr) {
                foreach ($extra_fields_mapped as $woocommerce_name => $edx_name) {
                    if ($attr->field_name === $edx_name && $input === $woocommerce_name) {
                        $value = esc_attr( $attr->field_value );
                    }
                }
            }

            $processing_name_part = $input === 'billing_first_name' || $input === 'billing_last_name';
            
            if ($processing_name_part && empty($value) && !empty($this->eox_user_info->name)) {
                $parser = new FullNameParser();
                $parsed = $parser->parse_name($this->eox_user_info->name);
                $key = $input === 'billing_first_name' ? 'fname' : 'lname';
                if (!empty($parsed[$key])) {
                    $value = $parsed[$key];
                }
            }

        }
        return $value;
    }
}