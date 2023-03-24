<?php

/**
 * Created by PhpStorm.
 * User: Bugz
 * Date: 4/22/16
 * Time: 1:19 AM
 */

/**
 * Stripe Checkout Pro Helper Class
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) || !is_plugin_active('stripe-checkout-pro/stripe-checkout-pro.php') ) {
    exit;
}

if ( ! class_exists( 'ZMD_Stripe_Plugin' ) ) {

    class ZMD_Stripe_Plugin
    {


        private static $plaid_settings = [
            'client_id' => '57183d5966710877408d0063',
            'secret' => '1a501bb77986c1453c291284176cb6',
            'endpoint' => 'https://tartan.plaid.com/exchange_token',
            'env' => 'tartan',
            'key' => 'edf5cd19f406615069af41597b6b33'
        ];
        //endpoint = 'https://api.plaid.com/exchange_token'; //for production
        //endpoint = 'https://tartan.plaid.com/exchange_token'; //for test account
        //env = 'tartan' //for testing
        //env = 'production' //for production
        //key & secret.. use appropriate for production and testing


        protected static $instance = null;

        protected static $errors = [];
        protected static $messages = [];

        protected static $formHash;

        private static $base_folder = 'zmd-stripe';

        // Class constructor
        private function __construct() {

            Stripe_Checkout_Functions::set_key();
            self::clearErrors();
            self::clearMessages();
            self::load_scripts();

        }
        private static function objArraySearch($array, $index, $value=null)
        {
            if( !is_array($array) ) {
                return null;
            }

            if($value && $index) {
                foreach($array as $arrayInf) {
                    if($arrayInf->{$index} == $value) {
                        return $arrayInf;
                    }
                }
                return null;
            }

            foreach($array as $arrayInf) {
                if( $arrayInf->{$index} ) {
                    return $arrayInf;
                }
            }


            return null;
        }

        public static function clearErrors() {
            self::$errors = [];
        }
        public static function clearMessages() {
            self::$messages = [];
        }


        public static function checkForStripeCustomerByEmail($email)
        {
            $starting_after = null;
            $customerObj = null;

            while (true) {
                $results = \Stripe\Customer::all(['limit' => 100,'starting_after'=>$starting_after]);
                if (empty($results['data'])) {
                    break;
                }
                if( $customerObj = self::objArraySearch($results['data'],'email',$email) ) {
                    //customer exists based on email
                    break;
                }

                $starting_after = $results['data'][sizeof($results['data'])-1]['id'];
            }

            return $customerObj;

        }


        public static function load_scripts() {

            wp_enqueue_script('plaid_checkout','https://cdn.plaid.com/link/stable/link-initialize.js',[],null,true);
            wp_register_script('stripe_checkout','//checkout.stripe.com/checkout.js');

        }

        public static function checkForStripeCustomerById ($id)
        {

            $customerObj = null;

            try {
                $customerObj = \Stripe\Customer::retrieve($id);
            } catch (Exception $e) {
                $err = self::stripeErrorString($e);
                self::error_log("Oops, Something went wrong. Could not find customer using customer Id. The error message was: {$err}");
                return null;
            }

            return $customerObj;

        }


        public static function allowPostAction() {
            $allvals = '';
            foreach($_POST as $k=>$v){
                $allvals .= $v;
            }

            self::$formHash = sha1($allvals);

            $allowAction = true;
            if(isset($_SESSION['formHash'][$_POST['reload']]) && ($_SESSION['formHash'][$_POST['reload']] == self::$formHash)){
                $allowAction = false;
            }
            return $allowAction;
        }

        public static function setFormHash() {
            $_SESSION['formHash'][$_POST['reload']] = self::$formHash;
        }



        public static function customer_pay_widget($atts=array()) {

            global $sc_options;

            self::clearMessages();
            self::clearErrors();




            if(self::allowPostAction()) {

                if (isset($_POST['str_customer_id']) && !empty($_POST['str_customer_id'])) {
                    $customer_id = $_POST['str_customer_id'];
                }
                if (isset($_POST['qb_invoice_id']) && !empty($_POST['qb_invoice_id'])) {
                    $qb_invoice_id = $_POST['qb_invoice_id'];
                }
                if (isset($_POST['amount']) && !empty($_POST['amount'])) {

                    if (strpos($_POST['amount'], '$') !== false) {
                        $amount = trim(str_replace("$", "", $_POST['amount']));
                    }

                    $amount = (int)round(number_format((float)$amount * 100., 0, '.', '')); //always change to integer cents
                }
                if (isset($_POST['stripeEmail']) && !empty($_POST['stripeEmail'])) {
                    $receipt_email = $_POST['stripeEmail'];
                }


                if (wp_verify_nonce($_POST['zmd-stripe-nonce'], 'customer_pay') &&
                    (isset($_POST['stripeToken']) || (isset($_POST['plaid_token']) && isset($_POST['plaid_account_id'])))
                ) {

                    $token = null;

                    if (!empty($_POST['plaid_token']) && !empty($_POST['plaid_account_id'])) {
                        $plaid_token = $_POST['plaid_token'];
                        $plaid_account_id = $_POST['plaid_account_id'];
                        $token = self::plaid_to_stripe_authenticate($plaid_account_id, $plaid_token);

                    } elseif (!empty($_POST['stripeToken'])) {
                        $token = $_POST['stripeToken'];
                    }


                    $meta = array();
                    $meta = apply_filters('sc_meta_values', $meta);

                    $params = [
                        'customer' => $customer_id,
                        'source' => $token,
                        'description' => "QuickBooks Invoice: {$qb_invoice_id}",
                        'amount' => $amount,
                        'currency' => 'usd',
                        'statement_descriptor' => $qb_invoice_id,
                        'metadata' => [
                            'QuickBooks Invoice' => $qb_invoice_id,
                            'Receipt Email' => $receipt_email,
                        ],
                        'receipt_email' => $receipt_email
                    ];

                    self::chargeStripeCustomer($params, false);
                    self::setFormHash();

                }

            }


            //get appropriate stripe api key ?
            $test_mode = ( isset( $atts['test_mode'] ) ? 'true' : 'false' );
            $data_name =  ( isset( $atts['name'] ) ? $atts['name']: 'COOBO MEDIA' );
            $data_panel_label = ( isset( $atts['panel_label'] ) ? $atts['panel_label']:'Continue');
            $data_label = ( isset( $atts['label'] ) ? $atts['label']:'Pay With Credit Card');
            $data_image = ( isset( $atts['image'] ) ? $atts['image']:'//stripe.com/img/documentation/checkout/marketplace.png');
            $form_id = ( isset( $atts['form_id'] ) ? $atts['form_id']:'coobo_invoice_payment');

            // Check if in test mode or live mode
            if ( 0 == $sc_options->get_setting_value( 'enable_live_key' ) || 'true' == $test_mode ) {
                // Test mode
                if ( ! ( null === $sc_options->get_setting_value( 'test_publishable_key_temp' ) ) ) {
                    $data_key = $sc_options->get_setting_value( 'test_publishable_key_temp' );
                    $sc_options->delete_setting( 'test_publishable_key_temp' );
                } else {
                    $data_key = ( null !== $sc_options->get_setting_value( 'test_publish_key' ) ? $sc_options->get_setting_value( 'test_publish_key' ) : '' );
                }

                if ( null === $sc_options->get_setting_value( 'test_secret_key' ) && null === $sc_options->get_setting_value( 'test_publishable_key_temp' ) ) {
                    $data_key = '';
                }
            } else {
                // Live mode
                if ( ! ( null === $sc_options->get_setting_value( 'live_publishable_key_temp' ) ) ) {
                    $data_key = $sc_options->get_setting_value( 'live_publishable_key_temp' );
                    $sc_options->delete_setting( 'live_publishable_key_temp' );
                } else {
                    $data_key = ( null !== $sc_options->get_setting_value( 'live_publish_key' ) ? $sc_options->get_setting_value( 'live_publish_key' ) : '' );
                }

                if ( null === $sc_options->get_setting_value( 'live_secret_key' ) && null === $sc_options->get_setting_value( 'live_publishable_key_temp' ) ) {
                    $data_key = '';
                }
            }

            $form_params = [
                'data-key'=>$data_key,
                'data-name'=> $data_name,
                'data-panel-label'=> $data_panel_label,
                'data-label'=> $data_label,
                'data-image'=> $data_image,
                'form-id'=> $form_id,
                'plaid-key' => self::$plaid_settings['key'],
                'plaid-env'=> self::$plaid_settings['env']
            ];

            /*wp_enqueue_script('jquery_mask',
                get_stylesheet_directory_uri().'/'.self::$base_folder.'/scripts/jquery.mask.min.js', array('jquery'));*/


            wp_enqueue_script('jquery_inputmask_bundle',
                get_stylesheet_directory_uri().'/'.self::$base_folder.'/scripts/jquery.inputmask.bundle.js');


            wp_enqueue_script('jquery_modal',
                get_stylesheet_directory_uri().'/'.self::$base_folder.'/scripts/jquery.modal.js', array('jquery'));
            wp_enqueue_style( 'font-awesome-css','//maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css');

            wp_enqueue_style( 'jquery_modal_css',get_stylesheet_directory_uri().'/'.self::$base_folder.'/css/jquery.modal.css');
            wp_enqueue_script('zmod_stripe_pay_invoice_form',
                get_stylesheet_directory_uri().'/'.self::$base_folder.'/scripts/stripe_pay_invoice_form.js', array('jquery','jquery_modal','jquery_inputmask_bundle','stripe_checkout','plaid_checkout'));

            wp_localize_script( 'zmod_stripe_pay_invoice_form', 'stripe_form_params', $form_params );


            add_filter('the_content', function($content) use ($form_params)  {

                $content = self::notifications().$content;

                $content.='<form id="'.$form_params['form-id'].'" class="stripe-payment-form" action="" method="POST">

                    <div class="sc-form-group flex_column av_one_half first">
                        <label> Email <input value="" type="email" placeholder="Enter a Valid Email" id="sc-email" class="sc-cf-text" name="sc_form_dummy_email" required=""  data-parsley-errors-container="#sc_cf_email_error_2"></label>
                        <div id="sc_cf_email_error_2"></div>
                    </div>
                    <div class="sc-form-group flex_column av_one_half second">
                        <label> Customer ID <input value="" type="text" placeholder="Enter Customer ID" id="str_customer_id" class="sc-cf-text" name="str_customer_id" required=""  data-parsley-errors-container="#str_customer_id_error"></label>
                        <a href="#pay-invoice-helper" rel="modal:open">
                        <i style="background-color: #f8f8f8; color:#666666;" class="fa fa-2x fa-info-circle" aria-hidden="true"></i></a>
                        <div id="str_customer_id_error"></div>
                    </div>
                     <div class="sc-form-group flex_column av_one_half first">
                       <label> Invoice # <input value="" type="text" placeholder="Enter Invoice #" id="qb_invoice_id" class="sc-cf-text" name="qb_invoice_id" required=""  data-parsley-errors-container="#qb_invoice_id_error"></label>
                       <a href="#pay-invoice-helper" rel="modal:open">
                       <i style="background-color: #f8f8f8; color:#666666;"  class="fa fa-2x fa-info-circle" aria-hidden="true"></i></a>
                        <div id="qb_invoice_id_error"></div>
                    </div>
                    <div class="sc-form-group flex_column av_one_half second">
                       <label> Payment Amount <input value="" type="text" autocomplete="off" id="amount" class="sc-cf-text" name="amount" required=""
                       data-parsley-errors-container="#amount_error"></label>
                       <a href="#pay-invoice-helper" rel="modal:open">
                       <i style="background-color: #f8f8f8; color:#666666;" class="fa fa-2x fa-info-circle" aria-hidden="true"></i></a>
                        <div id="amount_error"></div>
                    </div>

                    <div class="sc-form-group">
                        <input value="" type="hidden" class="sc_stripeToken" name="stripeToken">
                        <input value="" type="hidden" class="sc_plaidToken" name="plaid_token">
                        <input value="" type="hidden" class="sc_stripeEmail" name="stripeEmail">
                        <input value="" type="hidden" class="sc_plaidAccountId" name="plaid_account_id">'.
                        '<input value="<?=microtime(true)*10000?>" type="hidden" name="reload">'.
                    wp_nonce_field( 'customer_pay', 'zmd-stripe-nonce', '', false ).'
                    </div>
                    <div class="sc-form-group flex_column">
                        <button id="stripe_pay_with_card" class="sc-payment-btn stripe-button-el stripe_signup"><span>'.$form_params['data-label'].'</span></button>
                        <button id="stripe_pay_with_ach" class="sc-payment-btn stripe-button-el stripe_signup"><span>Pay Using ACH</span></button>
                        <div class="stripe-logo" style="padding-top:10px;">
                            <a href="//stripe.com/" target="_blank" style="text-shadow: none;">
                                <img src="//coobomedia.com/wp-content/plugins/stripe-checkout-pro/assets/img/powered-by-stripe.png">
                            </a>
				        </div>
                    </div>
                    </form>
                    <div id="pay-invoice-helper" style="display:none;">
                        <p>This information may be found on your invoice.</p>
                        <p>Click <a href="#" rel="modal:close">Close</a> or press ESC</p>
                    </div>';

                //$content.= self::notifications();

                return $content;

            },1000);


        }





        public static function new_customer_widget($atts=array()) {


            global $sc_options;

            self::clearMessages();
            self::clearErrors();

            if(self::allowPostAction()) {

                if (wp_verify_nonce($_POST['zmd-stripe-nonce'], 'create_customer') &&
                    (isset($_POST['stripeToken']) || (isset($_POST['plaid_token']) && isset($_POST['plaid_account_id'])))
                ) {

                    $token = null;

                    if (!empty($_POST['plaid_token']) && !empty($_POST['plaid_account_id'])) {
                        $plaid_token = $_POST['plaid_token'];
                        $plaid_account_id = $_POST['plaid_account_id'];
                        $token = self::plaid_to_stripe_authenticate($plaid_account_id, $plaid_token);

                    } elseif (!empty($_POST['stripeToken'])) {
                        $token = $_POST['stripeToken'];
                    }

                    $meta = array();
                    $meta = apply_filters('sc_meta_values', $meta);

                    $params = [
                        'source' => $token,
                        'email' => $_POST['stripeEmail'],
                        'metadata' => $meta,
                        'description' => 'Coobo Media E-Web Hosting Signup'
                    ];

                    self::createStripeCustomer($params);
                    self::setFormHash();
                }
            }

            //get appropriate stripe api key ?
            $test_mode = ( isset( $atts['test_mode'] ) ? 'true' : 'false' );
            $data_name =  ( isset( $atts['name'] ) ? $atts['name']: 'COOBO MEDIA' );
            $data_description = ( isset( $atts['description'] ) ? $atts['description']: 'Web Hosting');
            $data_panel_label = ( isset( $atts['panel_label'] ) ? $atts['panel_label']:'Continue');
            $data_label = ( isset( $atts['label'] ) ? $atts['label']:'Signup With Credit Card');
            $data_image = ( isset( $atts['image'] ) ? $atts['image']:'//stripe.com/img/documentation/checkout/marketplace.png');
            $data_amount = ( isset( $atts['amount'] ) ? $atts['amount']:'0')*100;
            $form_id = ( isset( $atts['form_id'] ) ? $atts['form_id']:'coobo_hosting_sign_up');

            $show_plans = ( isset( $atts['show_plans'] ) ? $atts['show_plans']:'false');

            // Check if in test mode or live mode
            if ( 0 == $sc_options->get_setting_value( 'enable_live_key' ) || 'true' == $test_mode ) {
                // Test mode
                if ( ! ( null === $sc_options->get_setting_value( 'test_publishable_key_temp' ) ) ) {
                    $data_key = $sc_options->get_setting_value( 'test_publishable_key_temp' );
                    $sc_options->delete_setting( 'test_publishable_key_temp' );
                } else {
                    $data_key = ( null !== $sc_options->get_setting_value( 'test_publish_key' ) ? $sc_options->get_setting_value( 'test_publish_key' ) : '' );
                }

                if ( null === $sc_options->get_setting_value( 'test_secret_key' ) && null === $sc_options->get_setting_value( 'test_publishable_key_temp' ) ) {
                    $data_key = '';
                }
            } else {
                // Live mode
                if ( ! ( null === $sc_options->get_setting_value( 'live_publishable_key_temp' ) ) ) {
                    $data_key = $sc_options->get_setting_value( 'live_publishable_key_temp' );
                    $sc_options->delete_setting( 'live_publishable_key_temp' );
                } else {
                    $data_key = ( null !== $sc_options->get_setting_value( 'live_publish_key' ) ? $sc_options->get_setting_value( 'live_publish_key' ) : '' );
                }

                if ( null === $sc_options->get_setting_value( 'live_secret_key' ) && null === $sc_options->get_setting_value( 'live_publishable_key_temp' ) ) {
                    $data_key = '';
                }
            }

            $form_params = [
                'data-key'=>$data_key,
                'data-name'=> $data_name,
                'data-description'=> $data_description,
                'data-amount'=> $data_amount,
                'data-panel-label'=> $data_panel_label,
                'data-label'=> $data_label,
                'data-image'=> $data_image,
                'form-id'=> $form_id,
                'plaid-key' => self::$plaid_settings['key'],
                'plaid-env'=> self::$plaid_settings['env'],
                'show_plans' => $show_plans
            ];


            wp_enqueue_script('zmod_stripe_new_customer_form',
                get_stylesheet_directory_uri().'/'.self::$base_folder.'/scripts/stripe_new_customer_form.js', array('jquery','stripe_checkout','plaid_checkout'));
            wp_localize_script( 'zmod_stripe_new_customer_form', 'stripe_form_params', $form_params );




            add_filter('the_content', function($content) use ($form_params)  {

                $content = self::notifications().$content;


                $content.='<form id="'.$form_params['form-id'].'" class="stripe-signup-form" action="" method="POST">

                    <div class="sc-form-group">
                        <label> Email <input type="email" placeholder="Enter a Valid Email" id="sc-email" class="sc-cf-text" name="sc_form_dummy_email" required=""  data-parsley-errors-container="#sc_cf_email_error_2"></label>
                        <div id="sc_cf_email_error_2"></div>
                    </div>';

                if( $form_params['show_plans'] === 'true') {
                $content.= '<div class="sc-form-group">
                            <label> Hosting Plan </label>
                        <select id="plan_selection" class="sc-cf-select" name="sc_form_field[plan_selection]" required="" data-parsley-errors-container="#sc_cf_plan_selection_error">
                            <option value="WP Managed $60">WP Managed $60</option>
                            <option value="WP Regular $55">WP Regular $55</option>
                            <option value="HTML $55">HTML $55</option>
                            <option value="Splash Page $30">Splash Page $30</option>
                        </select>
                        <div id="sc_cf_plan_selection_error"></div>
                    </div>';
                }
                $content.='
                    <div class="sc-form-group">
                        <label>
                            <input type="checkbox" id="terms_checkbox" class="sc-cf-checkbox" name="sc_form_field[terms_checkbox]" required="" data-parsley-errors-container="#sc_cf_checkbox_error_2">By checking here,
                            I accept these terms and conditions and understand that this is for a recurring payment plan.</label>
                            <input type="hidden" id="terms_checkbox_hidden" class="sc-cf-checkbox-hidden" name="sc_form_field[terms_checkbox]" value="No">
                            <div id="sc_cf_checkbox_error_2"></div>
                    </div>
                    <div class="sc-form-group">
                        <input type="hidden" class="sc_stripeToken" name="stripeToken">
                        <input type="hidden" class="sc_stripeEmail" name="stripeEmail">
                        <input type="hidden" class="sc_plaidToken" name="plaid_token">
                        <input type="hidden" class="sc_plaidAccountId" name="plaid_account_id">'.
                        '<input value="<?=microtime(true)*10000?>" type="hidden" name="reload">'.
                        wp_nonce_field( 'create_customer', 'zmd-stripe-nonce', '', false ).'
                    </div>
                    <button id="stripe_signup_with_card" class="sc-payment-btn stripe-button-el stripe_signup"><span>'.$form_params['data-label'].'</span></button>
                    <button id="stripe_signup_with_ach" class="sc-payment-btn stripe-button-el stripe_signup"><span>Signup Using ACH</span></button>
                     <div class="stripe-logo" style="padding-top:10px;">
                        <a href="//stripe.com/" target="_blank" style="text-shadow: none;">
                            <img src="//coobomedia.com/wp-content/plugins/stripe-checkout-pro/assets/img/powered-by-stripe.png">
                        </a>
				    </div>
                    </form>';

                //$content.= self::notifications();

                return $content;

            },1000);


        }

        public static function notifications() {

            $notifications = '';

            foreach(self::$messages as $message) {
                $notifications.='<div class="avia_message_box avia-color-green
                    avia-size-small avia-icon_select-no avia-border-
                    avia-builder-el-0  el_before_av_textblock  avia-builder-el-first">';
                $notifications.='<div class="avia_message_box_content"><p>'.$message.'</p></div>';
                $notifications.='</div>';
            }
            foreach(self::$errors as $error_msg) {
                $notifications.='<div style="background-color:#ef3939; color:#ffffff;"
                class="avia_message_box avia-color-custom
                    avia-size-small avia-icon_select-no avia-border-
                    avia-builder-el-0  el_before_av_textblock  avia-builder-el-first">';
                $notifications.='<div class="avia_message_box_content"><p>'.$error_msg.'</p></div>';
                $notifications.='</div>';

            }

            return $notifications;
        }

        public static function msg_log($msg) {

            self::$messages[] = $msg;


        }
        public static function error_log($error) {
            self::$errors[] = $error;
        }

        public static function stripeErrorString($e) {
                $err = $e->getJsonBody();
                return $err['error']['type'].' '.$err['error']['message'];
        }

        public static function getTokenObject($token) {

            $tokenObj = null;

            try {
                $tokenObj = \Stripe\Token::retrieve($token);
            } catch (Exception $e) {
               $err = self::stripeErrorString($e);
                self::error_log("Oops, Something went wrong. Could not retrieve token object. The error message was: {$err}");
                return null;
            }

            if(!$tokenObj) {
                self::error_log("Oops, Something went wrong. Could not retrieve token object.");
                return null;
            }

            return $tokenObj;

        }

        public static function checkErrors() {
            return sizeof(self::$errors);
        }




        public static function updateStripeCustomer($customerId,$params,$set_as_default=false) {

            $customer = null;

            if( !$params['source']) {
                self::error_log("Oops, Something went wrong. A valid token is required to update a customer");
                return null;
            } //a valid token and email is required when creating Customer


            $defaultSource = self::checkIfFingerprintExistsOnCustomer($customerId,$params['source']);

            if(self::checkErrors()) {
                return null;
            }

            try {
                $customer = \Stripe\Customer::retrieve($customerId);
                if ( !$defaultSource ) {
                    //source doesn't exist so add the source and make default
                    $defaultSource = $customer->sources->create(['source'=>$params['source']]);
                }

                unset($params['source']); //unset the source since we are done with it.

                if( $set_as_default === true ) {
                    $customer->default_source = $defaultSource->id; //make this source the default as this is the last source provided by customer
                }

                foreach( $params as $key=>$value ) {
                    if($key=='metadata') {
                        $customer->{$key} = array_replace($customer->{$key},$value); //add new metadata when updating
                    } else {
                        $customer->{$key} = $value;
                    }
                }
                //$customer->description = $params['description'];
                //$customer->email = $params['email'];
                //$customer->metadata = array_replace($customer->metadata,$params['metadata']);

                $customer->save();
                $customerName = $customer->email;
                $sourceObjects = $customer->sources->all()->data;
                if( $foundObj = self::objArraySearch($sourceObjects,'name') ) {
                    $customerName = $foundObj->name;
                }
                self::msg_log("Thanks {$customerName}, We received your payment information.");


            } catch (Exception $e) {
                $err = self::stripeErrorString($e);
                self::error_log("Oops, Something went wrong. While trying to update the customer, the error message was: {$err}");
                return null;
            }

            return $customer;


        }



        public static function checkIfFingerprintExistsOnCustomer($customerId,$token) {

            $newSourceUpdates = [];

            if(!($tokenObj = self::getTokenObject($token))) {
                return null;
            }

            if($tokenObj->type == 'card') {
                $update_keys = [
                    'address_city',
                    'address_line1',
                    'address_line2',
                    'address_state',
                    'address_zip',
                    'address_country',
                    'name'
                ];
                $fingerprint = $tokenObj->card->fingerprint;
                foreach($update_keys as $update_key) {
                    $newSourceUpdates[$update_key] = $tokenObj->card->{$update_key};
                }

            } elseif($tokenObj->type =='bank_account') {
                $update_keys = ['account_holder_name'];
                $fingerprint = $tokenObj->bank_account->fingerprint;
                foreach($update_keys as $update_key) {
                    $newSourceUpdates[$update_key] = $tokenObj->bank_account->{$update_key};
                }
            } else {
                self::error_log("Oops, Something went wrong. Token type was not found when checking if fingerprint exists on customer.");
                return null;
            }

            $customer = \Stripe\Customer::retrieve($customerId);

            if(!$customer) {
                self::error_log("Oops, Something went wrong. Could not Check if fingerprint Exists. Customer Could not be found.");
                return null;
            }

            $sourceObjects = $customer->sources->all(['object' => $tokenObj->type])->data; //get all source objects

            $sourceObject = self::objArraySearch($sourceObjects,'fingerprint',$fingerprint);

            //update the source object appropriately if found with the new details
            if($sourceObject) {
                foreach($newSourceUpdates as $updateKey=>$updateVal) {
                    $sourceObject->{$updateKey} = $updateVal;
                }
                $sourceObject->save();
            }
            return $sourceObject;

        }

        public static function chargeStripeCustomer( $params=array(),$set_as_default=false ) {

            $customer = null;
            $charge = null;

            $required_fields = ['customer','source','description','amount','currency','statement_descriptor'];

            foreach($required_fields as $value) {
                if( !$params[$value] ) {
                    self::error_log("Oops, Something went wrong. A valid {$value} ID is required to charge a Customer.");
                    break;
                }
            }
            if(self::checkErrors()) {
                return null;
            }

            $customer = self::checkForStripeCustomerById($params['customer']);

            if( !$customer ) { //customer was not found
                return null;
            }

            $defaultSource = self::checkIfFingerprintExistsOnCustomer($customer->id,$params['source']);

            if(self::checkErrors()) {
                return null;
            }

            try {

                if ( !$defaultSource ) {
                    //source doesn't exist so add the source and make default if chosen
                    $defaultSource = $customer->sources->create(['source'=>$params['source']]);
                }

                if( $set_as_default === true ) {
                    $customer->default_source = $defaultSource->id; //make this source the default as this is the last source provided by customer
                    $customer->save();
                }

                //change the source from the token to the default source Id which we just established above
                $params['source'] = $defaultSource->id;

                if( $charge = \Stripe\Charge::create($params) ) {
                    //create the charge, default is to charge right away
                    $customerName = $customer->email;
                    $sourceObjects = $customer->sources->all()->data;
                    $confirmation = $charge->receipt_number;
                    if( $foundObj = self::objArraySearch($sourceObjects,'name') ) {
                        $customerName = $foundObj->name;
                    }
                    self::msg_log("Thanks {$customerName}, We received your payment. Your confirmation number is {$confirmation}");
                }

            } catch (Exception $e) {
                $err = self::stripeErrorString($e);
                self::error_log("Oops, Something went wrong. While trying to charge the customer, the error message was: {$err}");
                return null;
            }

            return $charge;


        }




        public static function createStripeCustomer($params=array()) {

            $customer = null;


            if(!$params['email'] || !$params['source']) {
                self::error_log("Oops, Something went wrong. A valid token and email is required to create Customer.");
                return null;

            } //a valid token and email is required when creating Customer

            if( $customer = self::checkForStripeCustomerByEmail($params['email']) ) {
                // if customer exists then update him
                return self::updateStripeCustomer($customer->id,$params,true);
            }

            try {
                $customer = \Stripe\Customer::create($params);
                $customerName = $customer->email;
                $sourceObjects = $customer->sources->all()->data;
                if( $foundObj = self::objArraySearch($sourceObjects,'name') ) {
                    $customerName = $foundObj->name;
                }
                self::msg_log("Thanks {$customerName}, We received your payment information.");


            } catch (Exception $e) {
                $err = self::stripeErrorString($e);
                self::error_log("Oops, Something went wrong. Could not create customer. The error message was: {$err}");
            }
            return $customer;

        }


        public static function plaid_to_stripe_authenticate($account_id=null,$public_token=null) {

            // Get cURL resource
            $stripe_token = null;

            $params = array(
                'client_id' => self::$plaid_settings['client_id'],
                'secret' => self::$plaid_settings['secret'],
                'public_token' => $public_token,
                'account_id' => $account_id,

            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, self::$plaid_settings['endpoint']);
            curl_setopt($ch, CURLOPT_PORT, 443);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($ch,CURLINFO_HEADER_OUT,true);
            //curl_setopt($ch, CURLOPT_FAILONERROR, true);

            // Send the request & save response to $resp
            $resp = curl_exec($ch);


            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_errno= curl_errno($ch);
            $result = json_decode($resp,true);

            if ($http_status==200 ||
                ( isset($result['stripe_bank_account_token']) && !empty($result['stripe_bank_account_token']) ) ) {
                $stripe_token = $result['stripe_bank_account_token'];
            } else {
               $msg = $result['code'].' : '.$result['message'];
                self::error_log("Oops, Something went wrong. Could not exchange token with Plaid. The error message was:
                {$msg}");

            }
            // Close request to clear up some resources
            curl_close($ch);

            return $stripe_token;


        }



        // Return instance of this class
        public static function get_instance() {

            // If the single instance hasn't been set, set it now.
            if ( null == self::$instance ) {
                self::$instance = new self;
            }

            return self::$instance;
        }



    }


    ZMD_Stripe_Plugin::get_instance();



}