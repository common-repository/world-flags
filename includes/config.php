<?php
	
	// require the wp bootstrap, this is for our php files that are not loaded by WP and are called by ajax, etc.
	require_once( dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php' );
	
	// ---------------------------------------- constants
	define ( 'FLAGS_BASENAME', dirname ( dirname( plugin_basename( __FILE__ ) ) ) );
	define ( 'FLAGS_WEBDIR', '/' . PLUGINDIR . '/' . FLAGS_BASENAME );
	define ( 'FLAGS_VERSION', '1.1' );
	define ( 'FLAGS_DB_VERSION', '1.0' );
	define ( 'FLAGS_PREFIX', 'world_flags_' );
	define ( 'FLAGS_DB_PREFIX', 'wf_' );
	define ( 'FLAGS_IPV4_DOWNLOAD_URL', 'http://software77.net/geo-ip/?DL=1' );
	define ( 'FLAGS_IPV4_MD5_URL', 'http://software77.net/geo-ip/?DL=3' );
	define ( 'FLAGS_COUNTRY_CODES_URL', 'http://software77.net/geo-ip/?DL=6' );
	
	// ---------------------------------------- 
	
	global $world_flags_options;
	$world_flags_options = get_option( FLAGS_PREFIX . 'option' );
	global $world_flags_footer_script;
	$world_flags_footer_script = array();
	
?>