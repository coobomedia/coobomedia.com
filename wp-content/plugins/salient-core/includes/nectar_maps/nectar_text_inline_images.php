<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


return array(
	"name" => esc_html__("Text With Inline Media", "salient-core"),
	"base" => "nectar_text_inline_images",
	"icon" => "icon-wpb-split-line-heading",
	"allowed_container_element" => 'vc_row',
    "weight" => '1',
	"category" => esc_html__('Typography', 'salient-core'),
	"description" => esc_html__('Animated multi line heading', 'salient-core'),
	"params" => array(
      array(
        "type" => "dropdown",
        "class" => "",
        'save_always' => true,
        "heading" => esc_html__("Media Type", "salient-core"),
        "param_name" => "media_type",
        "value" => array(
          esc_html__("Images",'salient-core') => "images",
          esc_html__("Videos",'salient-core') => "videos",
        ),
        "description" => '',
        'std' => 'images',
      ),
      array(
        'type' => 'attach_images',
        'heading' => esc_html__( 'Images', 'js_composer' ),
        'param_name' => 'images',
        'value' => '',
        'dependency' => array(
          'element' => 'media_type',
          'value' => array('images'),
        ),
        'description' => esc_html__( 'Select images from media library. You can then use asterisks below in your text to mark where each image will display e.g. Lorem ipsum * dolor sit amet consectetur * adipiscing elit.', 'js_composer' )
      ),
      array(
        "type" => "nectar_attach_video",
        "class" => "",
        "heading" => esc_html__("Video #1 MP4 File URL", "salient-core"),
        "value" => "",
        "param_name" => "video_1_mp4",
        'dependency' => array(
          'element' => 'media_type',
          'value' => array('videos'),
        ),
        "description" => esc_html__("Enter the URL for your first video file here. You can then use asterisks below in your text to mark where each image will display e.g. Lorem ipsum * dolor sit amet consectetur * adipiscing elit.", "salient-core")
      ),
      array(
        "type" => "nectar_attach_video",
        "class" => "",
        "heading" => esc_html__("Video #2 MP4 File URL", "salient-core"),
        "value" => "",
        "param_name" => "video_2_mp4",
        'dependency' => array(
          'element' => 'video_1_mp4',
          'not_empty' => true,
        ),
        "description" => esc_html__("Enter the URL for your second mp4 video file here.", "salient-core")
      ),
      array(
        "type" => "nectar_attach_video",
        "class" => "",
        "heading" => esc_html__("Video #3 MP4 File URL", "salient-core"),
        "value" => "",
        "param_name" => "video_3_mp4",
        'dependency' => array(
          'element' => 'video_2_mp4',
          'not_empty' => true,
        ),
        "description" => esc_html__("Enter the URL for your third mp4 video file here.", "salient-core")
      ),
      array(
        "type" => "nectar_attach_video",
        "class" => "",
        "heading" => esc_html__("Video #4 MP4 File URL", "salient-core"),
        "value" => "",
        "param_name" => "video_4_mp4",
        'dependency' => array(
          'element' => 'video_3_mp4',
          'not_empty' => true,
        ),
        "description" => esc_html__("Enter the URL for your fourth mp4 video file here.", "salient-core")
      ),
		array(
			"type" => "textarea_html",
			"holder" => "div",
			"heading" => esc_html__("Text Content", "salient-core"),
			"param_name" => "content",
			"value" => '',
			"description" => '',
			"admin_label" => false,
			'dependency' => array(
				'element' => 'animation_type',
				'value' => array('default'),
			),
		),
    array(
      "type" => "dropdown",
      "class" => "",
      'save_always' => true,
      "heading" => esc_html__("Image Size", "salient-core"),
      "param_name" => "image_size",
      "value" => array(
        esc_html__("Small Uncropped",'salient-core') => "medium",
        esc_html__("Large Uncropped",'salient-core') => "large",
        esc_html__("Small Square",'salient-core') => 'nectar_small_square',
        esc_html__("Big Square",'salient-core') => 'medium_featured',
      ),
      'dependency' => array(
        'element' => 'media_type',
        'value' => array('images'),
      ),
			"description" => esc_html__('Square sizing is recommended for the "Circle Crop" Image effect','salient-core'),
      'std' => 'medium',
    ),
    array(
			"type" => "dropdown",
			"class" => "",
			'save_always' => true,
			"heading" => "Image Effect",
			"description" => '',
			"param_name" => "image_effect",
      'dependency' => array(
        'element' => 'media_type',
        'value' => array('images'),
      ),
			"value" => array(
				esc_html__("None",'salient-core') => "none",
				esc_html__("Circle Mask Reveal",'salient-core') => "circle_reveal",
				esc_html__("Circle Crop",'salient-core') => "circle_fade_in",
			)
		),

    array(
			"type" => 'checkbox',
			"heading" => esc_html__("Remove Animation On Mobile", "salient-core"),
			"param_name" => "image_effect_rm_mobile",
			'edit_field_class' => 'vc_col-xs-12 salient-fancy-checkbox',
			"description" => esc_html__("If selected, the divider line will animate in when scrolled to", "salient-core"),
			"value" => Array(esc_html__("Yes, please", "salient-core") => 'yes'),
			"dependency" => Array('element' => "image_effect", 'value' => array('circle_reveal','circle_fade_in')),
		),
		
		array(
			"type" => "colorpicker",
			"class" => "",
			"heading" => "Text Color",
			"param_name" => "text_color",
			"value" => "",
			"description" => esc_html__("Defaults to light or dark based on the current row text color.", "salient-core")
		),
		array(
			"type" => "textfield",
			"heading" => '<span class="group-title">' . esc_html__("Custom Font Size", "salient-core") . "</span>",
      		"edit_field_class" => "desktop font-size-device-group",
			"param_name" => "font_size_desktop",
		),
    array(
			"type" => "textfield",
			"heading" => '',
      "edit_field_class" => "tablet font-size-device-group",
			"param_name" => "font_size_tablet",
		),
    array(
			"type" => "textfield",
			"heading" => '',
      		"edit_field_class" => "phone font-size-device-group",
			"param_name" => "font_size_phone",
		),
		array(
			"type" => "textfield",
			"heading" =>  esc_html__("Custom Line Height", "salient-core"),
			"param_name" => "font_line_height",
		),
    array(
      "type" => "dropdown",
      "class" => "",
      'save_always' => true,
      "heading" => esc_html__("Media Loading", "salient-core"),
      "param_name" => "image_loading",
      "value" => array(
        "Default" => "default",
        "Skip Lazy Load" => "skip-lazy-load",
        "Lazy Load" => "lazy-load",
      ),
      "description" => esc_html__("Determine whether to load the media on page load or to use a lazy load method for higher performance.", "salient-core"),
      'std' => 'default',
    ),
		
    

	)
);
