<?php
/**
 * Plugin Name: Active Campaign & Contact Form 7 
 * Description: Add Contact Form 7 Data to Active Campiagn Contact lists.
 * Author: WPoperation
 * Plugin URI: https://wordpress.org/plugins/wpop-accf
 * Author URI: https://wpoperation.com
 * Version: 1.1.8
 * Tested up to: 6.0
 * Text Domain: wpop-accf
 * Domain Path: /languages/
 **/
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;
if (!class_exists('ACCF7_Integration')) {
    class ACCF7_Integration
    {
        public function __construct(){
        
            /**
             * check for contact form 7
             */
            add_action('init', array($this,'accf7_plugin_dependencies'));
            add_action( 'admin_enqueue_scripts',array($this,'accf7_register_backend_assets') );
            add_action('init', array(&$this,'init'));
            add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this,'accf7_pro_plugin_action_links') );
        }
        public function init(){
            load_plugin_textdomain('wpop-accf', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }
        public function accf7_plugin_dependencies() {
            define("ACCF7_PATH", plugin_dir_path(__FILE__));
            define("ACCF7_URL", plugin_dir_url(__FILE__));
            if (!class_exists('WPCF7')) {
                add_action('admin_notices',  array($this, 'cf7s_admin_notices'));
            } else {
                /**
                 * include settings
                 */
                require_once( ACCF7_PATH . 'includes/accf7-settings.php' );

                /**
                 * contact form 7 Subscribe class
                 */
                require_once( ACCF7_PATH . 'includes/accf7-subscribe.php' );                
            }
        }
        //Registering of backend js and css
        public function accf7_register_backend_assets() {
            wp_enqueue_script( 'accf7-admin-js', ACCF7_URL.'assets/admin.js', array( 'jquery' ), '1.0', true );
            wp_enqueue_style( 'accf7-admin-css', ACCF7_URL.'assets/admin.css');   
        }

        public function cf7s_admin_notices() {
            $class = 'notice notice-error';
            $message = __('Active Campaign & Contact Form 7  requires Contact form 7 to be installed and active.', 'wpop-accf');
            printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
        }

        function accf7_pro_plugin_action_links( $links ) {
         
            $links[] = '<a href="https://wpoperation.com/plugins/active-campaign-contact-form-7-pro/" target="_blank" style="color:#05c305; font-weight:bold;">'.esc_html__('Go Pro','wpop-accf').'</a>';
            return $links;
        }
    }
    new ACCF7_Integration();
}
