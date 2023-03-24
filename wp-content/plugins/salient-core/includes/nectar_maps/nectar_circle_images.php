<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	"name" => esc_html__("Circle Images", "salient-core"),
	"base" => "nectar_circle_images",
    "icon" => "icon-wpb-circle-images",
    "content_element" => true,
	"category" => esc_html__('Media', 'salient-core'),
	"description" => esc_html__('Circular Images', 'salient-core'),
	"params" => array(

        array(
            'type' => 'attach_images',
            'heading' => esc_html__( 'Images', 'js_composer' ),
            'param_name' => 'images',
            'value' => '',
            'description' => esc_html__( 'Select images from media library.', 'js_composer' )
          ),
          array(
            "type" => "nectar_numerical",
            'edit_field_class' => 'zero-floor vc_col-xs-12',
            "heading" => esc_html__("Circle Sizing",'salient-core'),
            "value" => "",
            "admin_label" => true,
            "param_name" => "sizing",
            "description" => esc_html__( 'Enter a size for your circles. When left empty, a default of 50px will be used.', 'salient-core' )
         ),
         array(
            "type" => "dropdown",
            "heading" => esc_html__("Circle Positioning", "salient-core"),
            "param_name" => "positioning",
            'save_always' => true,
            "value" => array(
              esc_html__( "Overlapping", "salient-core") => "overlapping",
              esc_html__( "Standard", "salient-core") => "standard",      
            ),
            "description" => esc_html__("Select how to position the circles.", "salient-core")
          ),
          array(
            "type" => "dropdown",
            "class" => "",
            'save_always' => true,
            "dependency" => array('element' => "positioning", 'value' => 'overlapping'),
            "heading" => esc_html__("Stacking Order", "salient-core"),
            "param_name" => "stacking_order",
            "value" => array(
              "Left To Right" => "ltr",
              "Right to Left" => "rtl",
            ),
            "description" => '',
            'std' => 'ltr',
        ),
          array(
          "type" => "colorpicker",
          "class" => "",
          "heading" => esc_html__("Border Color", "salient-core"),
          "param_name" => "border_color",
          "value" => "",
          "description" => ""
        ),
        array(
            "type" => "dropdown",
            "heading" => esc_html__("Alignment", "salient-core"),
            "param_name" => "alignment",
            'save_always' => true,
            "value" => array(
              esc_html__( "Left", "salient-core") => "left",
              esc_html__( "Middle", "salient-core") => "middle",
              esc_html__( "Right", "salient-core") => "right",      
            ),
            "description" => '',
          ),
          array(
            "type" => "dropdown",
            "heading" => esc_html__("Entrance Animation", "salient-core"),
            "param_name" => "animation",
            'save_always' => true,
            "value" => array(
              esc_html__( "None", "salient-core") => "none",
              esc_html__( "Fade In", "salient-core") => "fade-in"   
            ),
            "description" => '',
          ),
          array(
			"type" => "textfield",
			"heading" => esc_html__("Animation Delay", "salient-core"),
			"param_name" => "delay",
			"description" => esc_html__("Enter delay (in milliseconds) if needed e.g. 150", "salient-core")
        ),
        array(
            "type" => "dropdown",
            "class" => "",
            'save_always' => true,
            "heading" => esc_html__("Image Size", "salient-core"),
            "param_name" => "image_size",
            "value" => array(
              esc_html__("Small",'salient-core') => 'nectar_small_square',
              esc_html__("Medium",'salient-core') => 'medium_featured',
              esc_html__("Large",'salient-core') => 'large',
            ), 
            'std' => 'nectar_small_square',
          ),
          
        array(
            "type" => "dropdown",
            "class" => "",
            'save_always' => true,
            "heading" => esc_html__("Image Loading", "salient-core"),
            "param_name" => "image_loading",
            "value" => array(
              "Default" => "default",
              "Skip Lazy Load" => "skip-lazy-load",
              "Lazy Load" => "lazy-load",
            ),
            "description" => esc_html__("Determine whether to load the images on page load or to use a lazy load method for higher performance.", "salient-core"),
            'std' => 'default',
          ),
          /*
         array(
          "type" => 'checkbox',
          "heading" => esc_html__("Numerical Last Circle", "salient-core"),
          "param_name" => "numerical_circle",
          'edit_field_class' => 'vc_col-xs-12 salient-fancy-checkbox',
          "description" => esc_html__("Checking this will add a circle showing a number as the circle in the group", "salient-core"),
          "value" => Array(esc_html__("Yes", "js_composer") => 'true')
        ),
        array(
          "type" => "colorpicker",
          "class" => "",
          "heading" => esc_html__("Numerical Circle Color", "salient-core"),
          "param_name" => "numerical_circle_color",
          "value" => "",
          "dependency" => array('element' => "post_type", 'value' => 'products'),
          "description" => ""
        ), */

	)
);

?>