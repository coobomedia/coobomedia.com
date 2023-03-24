<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

extract(shortcode_atts(array(
	"title" => 'Title',
	'heading_tag' => 'default',
	'color' => 'Accent-Color'), $atts));

	$typography_class = ( in_array($heading_tag, array('h2','h3','h4','h5','h6')) ) ? 'nectar-inherit-'.$heading_tag.' toggle-heading' : 'toggle-heading';

echo '<div class="toggle '.esc_attr(strtolower($color)).'" data-inner-wrap="true"><h3><a href="#" class="'.$typography_class.'"><i class="fa fa-plus-circle"></i>'. wp_kses_post($title) .'</a></h3><div><div class="inner-toggle-wrap">' . do_shortcode($content) . '</div></div></div>';

?>
