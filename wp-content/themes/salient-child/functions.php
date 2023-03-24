<?php 

add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles', 100);

function salient_child_enqueue_styles() {
		
		$nectar_theme_version = nectar_get_theme_version();
		wp_enqueue_style( 'salient-child-style', get_stylesheet_directory_uri() . '/style.css', '', $nectar_theme_version );
		
    if ( is_rtl() ) {
   		wp_enqueue_style(  'salient-rtl',  get_template_directory_uri(). '/rtl.css', array(), '1', 'screen' );
		}
}





/* All code below is custom -----
*/


/* Add sticky class to body */

add_action('wp_enqueue_scripts','my_theme_scripts_function');
function my_theme_scripts_function() {
   
   wp_enqueue_script( 'myscript', get_stylesheet_directory_uri() . '/js/sticky_class.js');

   if ( is_singular('easy-photo-album') ) {
        wp_enqueue_script( 'masonry-js', get_stylesheet_directory_uri() . '/js/masonry.pkgd.min.js');
        wp_enqueue_script( 'masonry-scripts', get_stylesheet_directory_uri() . '/js/masonry-custom.js');
   }
}





/* Custom Shortcode for Creating Stripe Customer with ACH (via PLAID or CC info
* Depends  WP Simple Pay Pro For Stripe
*
*/

function zmd_stripe_new_customer_widget_function ($atts)
{

    require_once('zmd-stripe/classes/class_zmd_stripe.php');
    ZMD_Stripe_Plugin::get_instance();

    ZMD_Stripe_Plugin::new_customer_widget($atts);
    

}
add_shortcode( 'zmd_stripe_new_customer_widget', 'zmd_stripe_new_customer_widget_function' );

function zmd_stripe_customer_pay_widget_function ($atts)
{
    require_once('zmd-stripe/classes/class_zmd_stripe.php');
    ZMD_Stripe_Plugin::get_instance();
    ZMD_Stripe_Plugin::customer_pay_widget($atts);

}
add_shortcode( 'zmd_stripe_customer_pay_widget', 'zmd_stripe_customer_pay_widget_function' );


/*
* END CUSTOM Shortcode
*
*/
add_filter( 'gform_progressbar_start_at_zero', '__return_true' );

add_filter( 'gform_progress_bar', 'my_custom_function', 10, 3 );
function my_custom_function( $progress_bar, $form, $confirmation_message ) {
    //if you are using the filter gform_progressbar_start_at_zero, adjust the page number as needed
    $current_page = GFFormDisplay::get_current_page( $form['id'] );
    $page_count = GFFormDisplay::get_max_page_number( $form );
    $progress_bar = str_replace( 'Step ' . $current_page . ' of ' . $page_count, 'Level ' . $current_page . ' out of ' . $page_count . ' Level(s)', $progress_bar );
    return $progress_bar;
}




/** START  load recaptcha for Contact Form 7 only on pages where contact form exists */
add_action('wp_print_scripts', function () {
	global $post;
	if ( is_a( $post, 'WP_Post' ) && !has_shortcode( $post->post_content, 'contact-form-7') ) {
		wp_dequeue_script( 'google-recaptcha' );
		wp_dequeue_script( 'wpcf7-recaptcha' );
	}
});
/** END  oad recaptcha for Contact Form 7 only on pages where contact form exists */



?>







