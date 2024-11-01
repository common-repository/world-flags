<?php
 /**
 * handle world_flags shortcode params and return appropiate content
 *
 * @package WorldFlags
 */

// shortcode handler function
function world_flags_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'code' => 'auto',
		'size' => '32',
		'text' => 'no',
		), $atts ) );
		// [flag code="auto" size="32" text="no"] or [flag code="us" size="32" text="yes"]
		
		$code = strtolower( $code );
		$size = intval( $size );
		$text = ( strtolower( $text ) == 'yes' );
		$ajax = ( $code == 'auto' );
		return world_flags_build_html( $size, $text, $code, '', '', $ajax, 'shortcode', false );
}


// add the world_flags shortcode handler
add_shortcode('flag', 'world_flags_shortcode');
add_filter('widget_text', 'do_shortcode');
?>