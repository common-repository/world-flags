<?php 
 /**
 * handles installing plugin required tables and options
 *
 * @package WorldFlags
 */

	function world_flags_install() {
		global $wpdb;
		
		$options = get_option( FLAGS_PREFIX . 'option' );
		if ( ! $options ) {
			$options = array( 
							'enable_cron' => 0,
							'insert_method' => 'jquery',
							'enable_comments' => 1,
							'comments_size' => 24,
							'comments_text' => 0,
							'comments_insert_method' => 'before',
							'insert_size' => 24,
							'insert_text' => 1
						);
			update_option( FLAGS_PREFIX . 'option', $options );
		}
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		$table_name = $wpdb->prefix . FLAGS_DB_PREFIX . 'ip_list';
		if( $wpdb->get_var("show tables like '$table_name'" ) != $table_name ) {
			
			$sql = "CREATE TABLE " . $table_name . " (
				ip_from NUMERIC(10) NOT NULL,
				ip_to NUMERIC(10) NOT NULL,
				country_iso VARCHAR(2) NOT NULL,
				PRIMARY KEY  ip (ip_from, ip_to)
				) collate utf8_general_ci;";
			
			dbDelta( $sql );
			
			$options['db_version'] = FLAGS_DB_VERSION;
			update_option( FLAGS_PREFIX . 'option', $options );
			
			/* download database at fist activation
			$message = '';
			if ( world_flags_cron() ) {
				$rows = world_flags_save2db();
				if ( $rows > 0 ) {
					$message = ''; //no message here
				} else {
					$message = __( "IP list successfully downloaded but zero rows inserted into database.", 'world_flags' );
				}
			} else {
				$message = __( 'Failed to download and save the database. Please try again.', 'world_flags' );
			}
			if ( $message != '' ) {
				wp_die( $message );
			}*/
		}
		
		$db_ver = $options['db_version'];
		
		if( $db_ver != FLAGS_DB_VERSION ) {
		
			$sql = "CREATE TABLE " . $table_name . " (
				ip_from NUMERIC(10) NOT NULL,
				ip_to NUMERIC(10) NOT NULL,
				country_iso VARCHAR(2) NOT NULL,
				PRIMARY KEY  ip (ip_from, ip_to)
				) collate utf8_general_ci;";
			
			dbDelta( $sql );
			
			$options['db_version'] = FLAGS_DB_VERSION;
			update_option( FLAGS_PREFIX . 'option', $options );
		}
		
	}
	
?>