<?php
/*
Plugin Name: World Flags
Plugin URI: http://bsurprised.com/2010/08/world-flags-wordpress-plugin/
Description: Add country flags anywhere in your Wordpress blog using simple shortcodes and/or widgets, or show visitor country flag based on their IP address.
Version: 1.1
Author: Behrooz Sangani
Author URI: http://bsurprised.com/
*/


/*  Copyright 2010 Behrooz Sangani (sangani at gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/** 
 * This is the main file for WorldFlags plugin.
 *
 * Handles loading all plugin parts
 *
 * @package WorldFlags
 */

// ---------------------------------------- includes
require_once( 'includes/config.php' );
require_once( 'includes/functions.php' );
require_once( 'includes/install.php' );
require_once( 'includes/shortcode.php' );
require_once( 'includes/widget.php' );
// ----------------------------------------

// ---------------------------------------- language domain
add_action( 'init', 'world_flags_load_plugin_textdomain' );
function world_flags_load_plugin_textdomain() {
	load_plugin_textdomain( 'world_flags', false, FLAGS_BASENAME . '/languages' );
}
// ---------------------------------------- 
		
// ---------------------------------------- initial installation
// function included in install.php
register_activation_hook( __FILE__, 'world_flags_install' );
// ----------------------------------------

// ---------------------------------------- enqueue require libraries for the plugin
if ( ! is_admin() ) {
	// require our scripts and style to be loaded as well as jquery if selected
	if ( $world_flags_options['insert_method'] == 'jquery' ) {
		wp_enqueue_script( FLAGS_PREFIX . 'js', FLAGS_WEBDIR . '/js/wf.jq.js', array('jquery'), FLAGS_VERSION );
	}
	wp_enqueue_style( FLAGS_PREFIX . 'css', FLAGS_WEBDIR . '/css/wf.css', '', FLAGS_VERSION, 'screen' );
}
// ----------------------------------------

// ---------------------------------------- Admin menu hook and options
// Add action hook
add_action( 'admin_menu', 'world_flags_menu' );
// Register plugin submenu
function world_flags_menu() {
	// Add a new submenu under Options
	add_options_page( __( 'World Flags', 'world_flags' ), __( 'World Flags', 'world_flags' ), 'manage_options', 'world_flags_extra', 'world_flags_extra_page' );
	// register our settings for variable data
	register_setting( FLAGS_PREFIX . 'options', FLAGS_PREFIX . 'option' );
}

function world_flags_extra_page() {
	if ( ! current_user_can( 'manage_options' ) )	{
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	// fetch and update the database
	$message = '';
	if ( isset( $_POST['get_database'] ) ) {
		if ( world_flags_cron() ) {
			$rows = world_flags_save2db();
			if ( $rows > 0 ) {
				$message = sprintf( __( "IP list successfully downloaded and %s were inserted into database.", 'world_flags' ), $rows );
			} else {
				$message = __( "IP list successfully downloaded but zero rows inserted into database.", 'world_flags' );
			}
		} else {
			$message = __( 'Failed to download and save the database. Please try again.', 'world_flags' );
		}
	}
	
	echo '<div class="wrap">';
	echo '<div id="icon-options-general" class="icon32"><br /></div>';
	echo '<h2>' . __( 'World Flags Options', 'world_flags' ) . '</h2>';
	echo '<p>' . __( 'Use this form to change options for the World Flags plugin, or fetch the latest IP database manually.', 'world_flags' ) . '</p>';
	if ( $message != '' ) {
		echo '<div id="message" class="updated"><p>' . $message . '</p></div>';
	}
	echo '<form method="post" action="options.php">';
	settings_fields( FLAGS_PREFIX . 'options' );
	$options = get_option( FLAGS_PREFIX . 'option' );
	
	echo '	<fieldset class="options"><legend><h3>' . __( 'General', 'world_flags' ) . '</h3></legend>';
	echo '		<table class="form-table">';
	echo '				<tr valign="top"><th scope="row">' . __( 'Insert Method', 'world_flags' ) . '</th>';
	echo '						<td><input name="world_flags_option[insert_method]" type="radio" value="normal"' . checked( 'normal', $options['insert_method'], false ) . ' /> ' . __( 'Normal (Simply includes HTML. This maynot work if you have caching enabled on your site)', 'world_flags' ) . '<br />';
	echo '							<input name="world_flags_option[insert_method]" type="radio" value="script"' . checked( 'script', $options['insert_method'], false ) . ' /> ' . __( 'Script (Writes the HTML using a tiny javascript)', 'world_flags' ) . '<br />';
	echo '							<input name="world_flags_option[insert_method]" type="radio" value="jquery"' . checked( 'jquery', $options['insert_method'], false ) . ' /> ' . __( 'jQuery (Neat way to insert the HTML, but jQuery library will also be included. If you have it already, then this is the best choice. <strong>If you have caching, this is the option for you!</strong>)', 'world_flags' ) . '</td>';
	echo '				</tr>';
	echo '				<tr valign="top"><td colspan="2">';
	echo '					<div style="text-align: center; width: 500px; margin: 15px auto; border-top: 1px solid gray">';
	echo '					</div>';
	echo '				</td></tr>';
	
	echo '				<tr valign="top"><td colspan="2">';
	echo '					<h4>' . __( 'Options for the World Flag in comments', 'world_flags' ) . '</h4>';
	echo '				</td></tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Visitor Comments', 'world_flags' ) . '</th>';
	echo '						<td><input name="world_flags_option[enable_comments]" type="checkbox" value="1"' . checked( '1', $options['enable_comments'], false ) . ' /> ' . __( 'Enable inserting country flag based on comment author IP address', 'world_flags' ) . '</td>';
	echo '				</tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Size', 'world_flags' ) . '</th>';
	echo '					<td><select name="world_flags_option[comments_size]">';
	echo '						<option value="16"' . ( $options['comments_size'] == '16' ? 'selected="selected"' : '' ) . '>' . __( 'Icon - 16', 'world_flags' ) . '</option>';
	echo '						<option value="24"' . ( $options['comments_size'] == '24' ? 'selected="selected"' : '' ) . '>' . __( 'Small - 24', 'world_flags' ) . '</option>';
	echo '						<option value="32"' . ( $options['comments_size'] == '32' ? 'selected="selected"' : '' ) . '>' . __( 'Medium - 32', 'world_flags' ) . '</option>';
	echo '						<option value="48"' . ( $options['comments_size'] == '48' ? 'selected="selected"' : '' ) . '>' . __( 'Large - 48', 'world_flags' ) . '</option>';
	echo '					</select></td>';
	echo '				</tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Country Name', 'world_flags' ) . '</th>';
	echo '						<td><input name="world_flags_option[comments_text]" type="checkbox" value="1"' . checked( '1', $options['comments_text'], false ) . ' /> ' . __( 'Include the country name beside the flag image', 'world_flags' ) . '</td>';
	echo '				</tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Exclude Admin', 'world_flags' ) . '</th>';
	echo '						<td><input name="world_flags_option[comments_exclude_admin]" type="checkbox" value="1"' . checked( '1', $options['comments_exclude_admin'], false ) . ' /> ' . __( 'Exclude admin <code>user_id= 1</code> comments when showing flags', 'world_flags' ) . '</td>';
	echo '				</tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Comments Insert Method', 'world_flags' ) . '</th>';
	echo '						<td><input name="world_flags_option[comments_insert_method]" type="radio" value="before"' . checked( 'before', $options['comments_insert_method'], false ) . ' /> ' . __( 'Before Comment Text (Adds the flag HTML before the visitor comment text)', 'world_flags' ) . '<br />';
	echo '							<input name="world_flags_option[comments_insert_method]" type="radio" value="after"' . checked( 'after', $options['comments_insert_method'], false ) . ' /> ' . __( 'After Comment Text (Adds the flag HTML after the visitor comment text)', 'world_flags' ) . '<br />';
	echo '							<input name="world_flags_option[comments_insert_method]" type="radio" value="manual"' . checked( 'manual', $options['comments_insert_method'], false ) . ' /> ' . __( 'Manual (If you have hooked the comments loop in your template you can select this option and use <code>&lt;?php if (function_exists("world_flags_comment_flag")) world_flags_comment_flag($comment); ?&gt;</code>)', 'world_flags' ) . '</td>';
	echo '				</tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Prepend Text', 'world_flags' ) . '</th>';
	echo '						<td><input type="text" name="world_flags_option[comments_prepend]" value="'. $options['comments_prepend'] . '" /> ' . __( 'Optional text to add before the flag HTML', 'world_flags' ) . '</td>';
	echo '				</tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Append Text', 'world_flags' ) . '</th>';
	echo '						<td><input type="text" name="world_flags_option[comments_append]" value="'. $options['comments_append'] . '" /> ' . __( 'Optional text to add after the flag HTML', 'world_flags' ) . '</td>';
	echo '				</tr>';
	echo '				<tr valign="top"><td colspan="2">';
	echo '					<div style="text-align: center; width: 500px; margin: 15px auto; border-top: 1px solid gray">';
	echo '					</div>';
	echo '				</td></tr>';
	
	echo '				<tr valign="top"><td colspan="2">';
	echo '					<h4>' . __( 'Options for the World Flag function in your template', 'world_flags' ) . '</h4>';
	echo '					<p>' . __( 'You can include World Flag in your template by inserting <code>&lt;?php if (function_exists("world_flags_insert_flag")) world_flags_insert_flag(); ?&gt;</code>', 'world_flags' ) . '</p>';
	echo '				</td></tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Size', 'world_flags' ) . '</th>';
	echo '					<td><select name="world_flags_option[insert_size]">';
	echo '						<option value="16"' . ( $options['insert_size'] == '16' ? 'selected="selected"' : '' ) . '>' . __( 'Icon - 16', 'world_flags' ) . '</option>';
	echo '						<option value="24"' . ( $options['insert_size'] == '24' ? 'selected="selected"' : '' ) . '>' . __( 'Small - 24', 'world_flags' ) . '</option>';
	echo '						<option value="32"' . ( $options['insert_size'] == '32' ? 'selected="selected"' : '' ) . '>' . __( 'Medium - 32', 'world_flags' ) . '</option>';
	echo '						<option value="48"' . ( $options['insert_size'] == '48' ? 'selected="selected"' : '' ) . '>' . __( 'Large - 48', 'world_flags' ) . '</option>';
	echo '					</select></td>';
	echo '				</tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Country Name', 'world_flags' ) . '</th>';
	echo '						<td><input name="world_flags_option[insert_text]" type="checkbox" value="1"' . checked( '1', $options['insert_text'], false ) . ' /> ' . __( 'Include the country name beside the flag image', 'world_flags' ) . '</td>';
	echo '				</tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Prepend Text', 'world_flags' ) . '</th>';
	echo '						<td><input type="text" name="world_flags_option[insert_prepend]" value="'. $options['insert_prepend'] . '" /> ' . __( 'Optional text to add before the flag HTML', 'world_flags' ) . '</td>';
	echo '				</tr>';
	echo '				<tr valign="top"><th scope="row">' . __( 'Append Text', 'world_flags' ) . '</th>';
	echo '						<td><input type="text" name="world_flags_option[insert_append]" value="'. $options['insert_append'] . '" /> ' . __( 'Optional text to add after the flag HTML', 'world_flags' ) . '</td>';
	echo '				</tr>';
	echo '				<tr valign="top"><td colspan="2">';
	echo '					<div style="text-align: center; width: 500px; margin: 15px auto; border-top: 1px solid gray">';
	echo '					</div>';
	echo '				</td></tr>';
	
	echo '		</table>';
	echo '	</fieldset>';
	echo '	<br />';
	
	echo '	<fieldset class="options"><legend><h3>' . __( 'IP Database', 'world_flags' ) . '</h3></legend>';
	echo '		<table class="form-table">'; 
	echo '				<tr valign="top"><th scope="row">' . __( 'Scheduled Updates', 'world_flags' ) . '</th>';
	echo '						<td><input name="world_flags_option[enable_cron]" type="checkbox" value="1"' . checked( '1', $options['enable_cron'], false ) . ' /> ' . __( 'Enable weekly automatic fetching of the database', 'world_flags' ) . '</td>';
	echo '				</tr>';
	echo '		</table>';
	echo '	</fieldset>';
	
	echo '		<p class="submit">';
	echo '		<input type="submit" class="button-primary" value="' . __( 'Save Changes' ) . '" />';
	echo '		</p>';
	echo '</form>';
	echo '	<br />';
	echo '<form method="post" action="'. $_SERVER['REQUEST_URI'].'">';
	settings_fields( FLAGS_PREFIX . 'options' );
	echo '	<fieldset class="options"><legend><h3>' . __( 'Update IP Database', 'world_flags' ) . '</h3></legend>';
	echo '		<table class="form-table">'; 
	echo '				<tr valign="top"><th scope="row">' . __( 'Manually Fetch', 'world_flags' ) . '</th>';
	echo '						<td>';
	echo '						<input type="submit" class="button-primary" name="get_database" value="' . __( 'Update Database Now!', 'world_flags' ) . '" />' . __( 'This may take some time. Please be patient!', 'world_flags' ) . '<br />';
	echo '						' . __( 'Do not use this button often. Once a week or even once a month should work if you have disabled the scheduled download. Please be aware that software77.net may ban your IP if you send many download requrests. Refer to the policy on their website for more information.', 'world_flags' ) . '<br />' . ( is_writable( ABSPATH . FLAGS_WEBDIR . "/data" ) ? '<span style="color: #060;">' . __( 'Data folder is writable', 'world_flags' ) . '</span>' : '<span style="color: red;">' . __( 'Data folder is NOT writable', 'world_flags' ) . '</span>' ) . '</td>';
	echo '				</tr>';
	echo '		</table>';
	echo '	</fieldset>';
	echo '</form>';
	echo '<div style="text-align: center; width: 500px; margin: 15px auto; border-top: 1px solid gray">';
	echo '	<p><a href="http://bsurprised.com/" title="World Flags Plugin by BSurprised"><img src="http://bsurprised.com/wp-content/themes/bsurprised/images/bsurprised-logo.jpg" alt="World Flags Plugin by BSurprised" /></a><br />';
	echo '	World Flags plugin by Behrooz Sangani - <a href="http://bsurprised.com/" title="BSurprised Website">BSurprised.com</a> | <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HMNUXVMGNXDR8" title="Donations are appreciated">Donate</a><br />';
	echo '	v' . FLAGS_VERSION ;
	echo '	</p></div>';
	echo '</div>';

}
// ---------------------------------------- 

// ---------------------------------------- handle cron job to fetch database
if ( $world_flags_options['enable_cron'] ) {
	// we cannot send many requests to fetch the database, and weekly seems like a nice interval
	function world_flags_weekly_cron() {
		return array(
				'weekly' => array( 'interval' => 604800, 'display' => 'Once Weekly' )
				);
	}
	add_filter( 'cron_schedules', 'world_flags_weekly_cron' );
	add_action( 'world_flags_weekly_cron', 'world_flags_cron' );
	if ( ! wp_next_scheduled( 'world_flags_cron_hook' ) ) {
		wp_schedule_event( time(), 'weekly', 'world_flags_cron_hook' );
	}
}
// ---------------------------------------- 

// ---------------------------------------- handle comments filter if selected
if ( ( $world_flags_options['enable_comments'] == '1' ) &&  ( $world_flags_options['comments_insert_method'] != 'manual' ) ) {
	add_filter( 'comment_text', 'world_flags_comment_flag_auto' );
}
// ---------------------------------------- 

// ---------------------------------------- handle inserting script to load flags if required on jquery mode
function world_flags_footer_script() {
	global $world_flags_footer_script;
	
	if ( $world_flags_footer_script[0] ) {
		echo "<script type=\"text/javascript\">\n";
		echo "\tvar wf_base_url = '" . get_settings( 'siteurl' ) . FLAGS_WEBDIR . "/images/flags/';\n";
	  	foreach ( $world_flags_footer_script as $script ) {
	  		echo "\t$script\n";
	  	}
		echo "</script>\n";
	}
}
add_action('wp_footer', 'world_flags_footer_script');
// ---------------------------------------- 

// ---------------------------------------- handle notice on first time that database must be downloaded
function world_flags_db_notice( $plugin ) {
	$db_exists = file_exists( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv.gz" ) && file_exists( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv" ) ;
 	if( $plugin == 'world-flags/world-flags.php' && !$db_exists && function_exists( "admin_url" ) )
		echo '<td colspan="5" class="plugin-update">' . sprintf( __( 'You must download the IP database for the automatic mode to work properly. Go to <a href="%s">World Flags Options</a> and manually fetch the database.', 'world_flags' ), admin_url( 'options-general.php?page=world_flags_extra' ) ) . '</td>';
}
function world_flags_admin_notice() {
	$db_exists = file_exists( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv.gz" ) && file_exists( ABSPATH . FLAGS_WEBDIR . "/data/IpToCountry.csv" ) ;
 	if( !$db_exists && function_exists( "admin_url" ) )
		echo '<div class="error"><p>' . sprintf( __( 'You must download the IP database for the automatic mode to work properly. Go to <a href="%s">World Flags Options</a> and manually fetch the database.', 'world_flags' ), admin_url( 'options-general.php?page=world_flags_extra' ) ) . '</p></div>';
}
add_action( 'after_plugin_row', 'world_flags_db_notice' );
add_action( 'admin_notices', 'world_flags_admin_notice' );
// ---------------------------------------- 
?>