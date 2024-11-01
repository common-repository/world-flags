<?php
 /**
 * required functions are here
 *
 * @package WorldFlags
 */
	
	/**
	 *	world_flags_get_flag function
	 *	returns the path to the flag file based on the code and size
	*/
	function world_flags_get_flag( $code, $size, $no_url = false ) {
		$flag_src = get_settings( 'siteurl' ) . FLAGS_WEBDIR . "/images/flags/$size/";
		if ( file_exists( ABSPATH . FLAGS_WEBDIR . "/images/flags/$size/" . strtolower( $code ) . '.png' ) ) {
			return ( $no_url ? '' : $flag_src ) . strtolower( $code ) . '.png';
		} else {
			return ( $no_url ? '' : $flag_src ) . 'none.png';
		}
	} // end world_flags_get_flag
	
	/**
	 *	world_flags_country_codes function
	 *	Loades and returns the country codes text file into an array
	*/
	function world_flags_country_codes() {
		// get file content
		$lines = file( ABSPATH . FLAGS_WEBDIR . "/data/country-codes.txt" , FILE_SKIP_EMPTY_LINES ) or die ( "Unable to load data!" );
		
		$codes = array();
		$info = array();
		foreach ( $lines as $line ) {
			$info = explode( ",", $line );
			$codes[strtolower( $info[1] )] = ucwords( strtolower( $info[0] ) ) ;
		}
		return $codes;
	} // end world_flags_country_codes
	
	/**
	 *	world_flags_ip2country function
	 *
	 *	Loades the ip database .csv.gz file
	*/
	function world_flags_ip2country( $ip ) {
		// check if we have an uncompressed database
		global $wpdb;
		
		$ip_long = floatval( sprintf( "%u", ip2long( $ip ) ) );
		$table_name = $wpdb->prefix . FLAGS_DB_PREFIX . 'ip_list';
		$query = " SELECT country_iso FROM $table_name 
					WHERE ip_from<=$ip_long AND ip_to>=$ip_long 
					LIMIT 1; ";
		
		$county_iso = $wpdb->get_var( $wpdb->prepare( $query ) );
		
		if ( $county_iso ) {
			$county_codes = world_flags_country_codes();
			return array( 'ip' => $ip, 'code' =>  strtolower( $county_iso ), 'country' =>  ucwords( strtolower( $county_codes[$county_iso] ) ) );
		} else {
			return array( 'ip' => $ip, 'code' =>  'none', 'country' =>  '-' );
		}
	} // end world_flags_ip2country
	
	/**
	 * world_flags_build_html function
	 *
	 *	builds the html to be inserted based on the data 
	*/
	function world_flags_build_html( $size = 32, $text = true, $code_ip = 'auto', $prepend = '', $append = '', $ajax = true, $mode = '', $echo = false ) {
		global $world_flags_options, $world_flags_footer_script;
		
		$code_ip = strtolower( $code_ip );
		if ( $mode != '' ) $mode = '-' . $mode;
		
		if ( $code_ip == 'auto' ) {
			$cc = world_flags_ip2country( $_SERVER['REMOTE_ADDR'] );
		} else if ( strlen( $code_ip ) == 2 ) {
			$county_codes = world_flags_country_codes();
			$cc = array( 'ip' => '-', 'code' => $code_ip, 'country' =>  ucwords( strtolower( $county_codes[$code_ip] ) ) );
		} else {
			$cc = world_flags_ip2country( $code_ip );
		}
		$code = $cc['code'];
		$country = $cc['country'];
		$flag_src = world_flags_get_flag( $code, $size );
		
		$rnd_id = world_flags_rnd_id();
		
		$html = "<span class=\"world-flags\" $rnd_id><img class=\"wf-img\" src=\"$flag_src\" alt=\"$country\" title=\"$country\" />" . ( $text ? "<span class=\"wf-text\">$country</span>" : "") . "</span>";
		switch ( $world_flags_options['insert_method'] ) {
			case 'normal':
				if ( $echo ) echo "<span class=\"world-flags$mode\">" . $prepend . $html . $append . "</span>"; else return "<span class=\"world-flags$mode\">" . $prepend . $html . $append . "</span>";
				break;
			case 'script':
				if ( $echo ) echo "<span class=\"world-flags$mode\">" . $prepend . "<script type=\"text/javascript\">document.write('$html');</script>" . $append . "</span>"; else return "<span class=\"world-flags$mode\">" . $prepend . "<script type=\"text/javascript\">document.write('$html');</script>" . $append . "</span>";
				break;
			case 'jquery':
				$rnd_id = world_flags_rnd_id( false, false );
				$img = world_flags_get_flag( $code, $size, true );
				$ajax = ( $ajax ? 'true' : 'false' );
				$text = ( $text ? 'yes' : 'no' );
				$world_flags_footer_script[] = "insertFlag('#$rnd_id', '$img', '$size', '$country', '$text', $ajax)";
				if ( $echo ) echo "<span class=\"world-flags$mode\">" . $prepend . "<span class=\"world-flags\" id=\"$rnd_id\"></span>" . $append . "</span>"; else return "<span class=\"world-flags$mode\">" . $prepend . "<span class=\"world-flags\" id=\"$rnd_id\"></span>" . $append . "</span>";
				break;
		}	
	} // end world_flags_build_html
	
	
	/**
	 *	world_flags_save2db function
	 *
	 *	Saves the content of the csv file to database
	*/
	function world_flags_save2db( $csv_file = '', $type = '') {
		// ToDo: handle webhosting.info type as well and csv file var
			
		global $wpdb;
		
		$rows = 0;
		$table_name = $wpdb->prefix . FLAGS_DB_PREFIX . 'ip_list';
		
		// get file content
		if ( ( $handle = fopen( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv", "r" ) ) !== FALSE ) {
			// truncate old data
			$query = " TRUNCATE TABLE $table_name; ";
			$wpdb->query( $query );
			
			while ( ( $data = fgetcsv( $handle, 200, "," ) ) !== FALSE ) {
				if ( substr( $data[0], 0, 1 ) != '#' ) {
					$data[0] = floatval($data[0]);
					$data[1] = floatval($data[1]);
					
					$rows_affected = $wpdb->insert( $table_name, array( 'ip_from' => $data[0], 'ip_to' => $data[1], 'country_iso' => strtolower( $data[4] ) ) );
					$rows += $rows_affected;
				}
			}
			fclose( $handle );
			
			return $rows;
		}
	} // end world_flags_save2db
	
	/**
	 *	world_flags_uncompress_database function
	 *
	 *	uncompresses the database if not done yet
	*/
	function world_flags_uncompress_database() {
		$string = implode( "", gzfile( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv.gz" ) );
		$fp = fopen( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv", "w" );
		fwrite( $fp, $string, strlen( $string ) );
		fclose( $fp );
	}
	
	/**
	 *	world_flags_cron function
	 *
	 *	handles fetching database from software77.net/geo-ip
	*/
	function world_flags_cron() {
		// here's the link to the automated database downloads
		$result = file_get_contents( FLAGS_IPV4_DOWNLOAD_URL );
		if ( $result === false ) {
		    world_flags_log_msg( FLAGS_PREFIX . 'cron', "Failed to download the database. Please try again." );
		} else {
		    $fp = fopen( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv.gz", "w" );
			fwrite( $fp, $result, strlen( $result ) );
			fclose( $fp );
			
			world_flags_uncompress_database();
			
			// check the md5
			$hash = file_get_contents( FLAGS_IPV4_MD5_URL );
			if ( $result === false ) {
				world_flags_log_msg( FLAGS_PREFIX . 'cron', "Failed to get the MD5 hash data to validate the downloaded database. Please try again." );
			} else {
				$local_hash = md5_file( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv" );
				if ( $local_hash !== $hash ) {
    				world_flags_log_msg( FLAGS_PREFIX . 'cron', "The MD5 from the original data and the downloaded version don't match. We recommend that you download the file again.\n Original: $hash\n Ours: $local_hash" );
				} else {
					// well, we got it
					return true;
				}
			}
		} 
	}
	
	/**
	 * world_flags_log_msg function
	 *
	 *	logs error messages for cron jobs and all
	*/
	function world_flags_log_msg( $method, $data ) {
		
		$cur_date = date( "Y-m-d, H:i:s" );
		// save log
		$f = file_put_contents( ABSPATH . FLAGS_WEBDIR . '/' . FLAGS_PREFIX . 'log.txt' , "$cur_date | $method | $data\n", FILE_APPEND ) or die ( "Unable to save log data!" );
		
	} // end world_flags_log_msg
	
	/**
	 * world_flags_rnd_id function
	 *
	 *	generates a random id=wf-xxx for the html tags
	*/
	function world_flags_rnd_id( $attrib = true, $echo = false ) {
		
		$rnd_id = 'wf-' . rand(1, 999999);
		if ( $attrib ) $rnd_id = "id=\"$rnd_id\"";
		if ( $echo ) { 
			echo $rnd_id;
		} else {
			return $rnd_id;
		}
		
	} // end world_flags_rnd_id
	
	/**
	 * world_flags_rnd function
	 *
	 *	generates a random value to override request cache
	*/
	function world_flags_rnd( $attrib = true, $echo = false ) {
		
		$rnd = time() + rand(1, 99);
		if ( $attrib ) $rnd = ".rand=$rnd";
		if ( $echo ) { 
			echo $rnd;
		} else {
			return $rnd;
		}
		
	} // end world_flags_rnd
	
	/**
	 * world_flags_comment_flag function
	 *
	 *	manual mode. If put inside the comments loop, generates the flag based on comment author IP
	 *  $comment, the param that holds comment variables must be provided
	*/
	function world_flags_comment_flag( $comment ) {
		global $world_flags_options;
		
		if ( $world_flags_options['enable_comments'] == '1' &&  ( $world_flags_options['comments_insert_method'] == 'manual' ) ) {
			// get the comment
			//$comment = get_comment( $comment_id );
			
			// check to exclude admin flags
			if ( $world_flags_options['comments_exclude_admin'] == '1' && ( $comment->user_id == 1 ) ) return '';
			
			$size = $world_flags_options['comments_size']; 
			$text = ( $world_flags_options['comments_text'] == 1 );
			$prepend = $world_flags_options['comments_prepend'] . ' ';
			$append = ' ' . $world_flags_options['comments_append'];
			$before_after = $world_flags_options['comments_insert_method'];
			$ajax = false;
			$html = world_flags_build_html( $size, $text, $comment->comment_author_IP, $prepend, $append, $ajax, 'comment', false );
			echo $html;
		}
	} // end world_flags_comment_flag
	
	/**
	 * world_flags_comment_flag_auto function
	 *
	 *	auto mode. filters comment text and generates the flag based on comment author IP
	 *  can be added before or after comment text along with custom strings
	*/
	function world_flags_comment_flag_auto( $comment_text ) {
		global $world_flags_options, $comment;
		
		// check to exclude admin flags
		if ( $world_flags_options['comments_exclude_admin'] == '1' && ( $comment->user_id == 1 ) ) return $comment_text;
		
		$size = $world_flags_options['comments_size']; 
		$text = ( $world_flags_options['comments_text'] == 1 );
		$prepend = $world_flags_options['comments_prepend'] . ' ';
		$append = ' ' . $world_flags_options['comments_append'];
		$before_after = $world_flags_options['comments_insert_method'];
		$ajax = false;
		$html = world_flags_build_html( $size, $text, get_comment_author_IP(), $prepend, $append, $ajax, 'comment', false );
		switch ( $before_after ) {
			case 'before' :
				$html .=  ' ' . $comment_text;
				break;
			case 'after' :
				$html = $comment_text . ' ' . $html;
				break;
		}
		return $html;
	} // end world_flags_comment_flag_auto
	
	/**
	 * world_flags_insert_flag function
	 *
	 *	add the flag anywhere inside a template with this function
	*/
	function world_flags_insert_flag( $code = 'auto', $echo = true ) {
		global $world_flags_options;
		$size = $world_flags_options['insert_size']; 
		$text = ( $world_flags_options['insert_text'] == 1 ); 		
		$prepend = $world_flags_options['insert_prepend'] . ' ';
		$append = ' ' . $world_flags_options['insert_append'];
		$ajax = ( $code == 'auto' );
		return world_flags_build_html( $size, $text, $code, $prepend, $append, $ajax, 'template', $echo );
		
	} // end world_flags_insert_flag
?>