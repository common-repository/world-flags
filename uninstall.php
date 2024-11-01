<?php
 /**
 * handle uninstall and remove our table and options
 *
 * @package WorldFlags
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

require_once( 'includes/config.php' );

	/**
	 *	world_flags_delete_plugin function
	 *
	 *	handles deleting plugin database tables and options
	*/
	function world_flags_delete_plugin() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . FLAGS_DB_PREFIX . 'ip_list';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		
		delete_option( FLAGS_PREFIX . 'option' );
		
		if ( file_exists( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv" ) ) {
			unlink( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv" );
		}
		if ( file_exists( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv.gz" ) ) {
			unlink( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv.gz" );
		}
	}

world_flags_delete_plugin();

?>