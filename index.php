<?php
/*
Plugin Name: Apocalypse Meow
Plugin URI: http://wordpress.org/extend/plugins/apocalypse-meow/
Description: A simple, light-weight collection of tools to help protect wp-admin, including password strength requirements and brute-force log-in prevention.
Version: 1.3.2
Author: Josh Stoik
Author URI: http://www.blobfolio.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

	Copyright © 2012  Josh Stoik  (email: josh@blobfolio.com)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/



//----------------------------------------------------------------------
//  Constants, globals, and variable handling
//----------------------------------------------------------------------

//the database version
define('MEOW_DB', '1.0.0');

//the kitten image
define('MEOW_IMAGE', plugins_url('kitten.gif', __FILE__));

//password validation errors
global $meow_password_error;
$meow_password_error = false;

//htaccess contents for locked-down wp-content
define('MEOW_HTACCESS', "<FilesMatch \.(?i:php)$>\nOrder allow,deny\nDeny from all\n</FilesMatch>");

//htaccess filename for locked-down wp-content
define('MEOW_HTACCESS_FILE', ABSPATH . 'wp-content/.htaccess');

//--------------------------------------------------
//a get_option wrapper that deals with defaults and
//bad data
//
// @since 1.1.0
//
// @param $option option_name
// @return option_value or false
function meow_get_option($option){

	switch($option)
	{
		//is log-in protection enabled?
		case 'meow_protect_login':
			return (bool) get_option('meow_protect_login', true);
		//the maximum number of log-in failures allowed
		case 'meow_fail_limit':
			$tmp = (int) get_option('meow_fail_limit', 5);
			//silently correct bad entries
			if($tmp < 1)
			{
				$tmp = 5;
				update_option('meow_fail_limit', 5);
			}
			return $tmp;
		//the window in which to look for log-in failures
		case 'meow_fail_window':
			$tmp = (int) get_option('meow_fail_window', 43200);
			if($tmp < 60)
			{
				$tmp = 43200;
				update_option('meow_fail_window', 43200);
			}
			return $tmp;
		//whether or not a successful log-in resets the fail count
		case 'meow_fail_reset_on_success':
			return (bool) get_option('meow_fail_reset_on_success', true);
		//an array of IP addresses to ignore
		case 'meow_ip_exempt':
			return meow_sanitize_ips(get_option('meow_ip_exempt', array()));
		//the apocalypse page title
		case 'meow_apocalypse_title':
			return trim(strip_tags(get_option('meow_apocalypse_title', 'Nothing here just meow...')));
		//the apocalypse page content
		case 'meow_apocalypse_content':
			return get_option('meow_apocalypse_content', '<img src="' . MEOW_IMAGE . '" style="width: 64px; height: 64px; border: 0; margin-right: 10px;" align="left" />You have exceeded the maximum number of log-in attempts.<br>Sorry.');
		//whether or not to remove old log-in entries from the database
		case 'meow_clean_database':
			return (bool) get_option('meow_clean_database', false);
		//how long to keep old log-in entries in the database
		case 'meow_data_expiration':
			$tmp = (int) get_option('meow_data_expiration', 90);
			if($tmp < 3)
			{
				$tmp = 90;
				update_option('meow_data_expiration', 90);
			}
			return $tmp;
		//do passwords require letters?
		case 'meow_password_alpha':
			$tmp = get_option('meow_password_alpha','required');
			if(!in_array($tmp, array('optional','required','required-both')))
			{
				$tmp = 'required';
				update_option('meow_password_alpha', 'required');
			}
			return $tmp;
		//do passwords require numbers?
		case 'meow_password_numeric':
			$tmp = get_option('meow_password_numeric', 'required');
			if(!in_array($tmp, array('optional','required')))
			{
				$tmp = 'required';
				update_option('meow_password_numeric', 'required');
			}
			return $tmp;
		//do passwords require symbols?
		case 'meow_password_symbol':
			$tmp = get_option('meow_password_symbol', 'optional');
			if(!in_array($tmp, array('optional','required')))
			{
				$tmp = 'optional';
				update_option('meow_password_symbol', 'optional');
			}
			return $tmp;
		//minimum password length
		case 'meow_password_length':
			$tmp = (int) get_option('meow_password_length', 10);
			if($tmp < 1)
			{
				$tmp = 10;
				update_option('meow_password_length', 10);
			}
			return $tmp;
		//whether or not to remove the generator tag from <head>
		case 'meow_remove_generator_tag':
			return  (bool) get_option('meow_remove_generator_tag', true);
	}

	return get_option($option, false);
}

//----------------------------------------------------------------------  end variables



//----------------------------------------------------------------------
//  Apocalypse Meow WP backend
//----------------------------------------------------------------------
//functions relating to the wp-admin pages, e.g. settings

//--------------------------------------------------
//Create a Settings->Apocalypse Meow menu item
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_settings_menu(){
    add_options_page('Apocalypse Meow', 'Apocalypse Meow', 'manage_options', 'meow-settings', 'meow_settings');
    return true;
}
add_action('admin_menu', 'meow_settings_menu');

//--------------------------------------------------
//Create a plugin page link to the settings too.
//
//Not sure why it took so many releases to get
//around to this...
//
// @since 1.3.0
//
// @param $links
// @return $links + settings link
function meow_plugin_settings_link($links) {
  $links[] = '<a href="' . admin_url('options-general.php?page=meow-settings') . '">Settings</a>';
  return $links;
}
add_filter("plugin_action_links_" . plugin_basename(__FILE__), 'meow_plugin_settings_link' );

//--------------------------------------------------
//The Settings->Apocalypse Meow page
//
// this is an external file (settings.php)
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_settings(){
	require_once(dirname(__FILE__) . '/settings.php');
	return true;
}

//--------------------------------------------------
//Create a Users->Log-in History menu item
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_history_menu(){
    add_users_page('Log-in History', 'Log-in History', 'manage_options', 'meow-history', 'meow_history');
    return true;
}
add_action('admin_menu', 'meow_history_menu');

//--------------------------------------------------
//The Users->Log-in History page
//
// this is an external file (history.php)
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_history(){
	require_once(dirname(__FILE__) . '/history.php');
	return true;
}

//--------------------------------------------------
//Set up some fancy URLs
//
// add a rewrite rule for the log-in history CSV export file.
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_init() {
	add_rewrite_rule( '^meow/login_history\.csv$', 'index.php?meow_history=true', 'top' );
	return true;
}
add_action('init','meow_init');

//--------------------------------------------------
//Whitelist our query_vars
//
// @since 1.0.0
//
// @param $query_vars
// @return $query_vars
function meow_query_vars( $query_vars )
{
    $query_vars[] = 'meow_history';
    return $query_vars;
}
add_action('query_vars','meow_query_vars' );

//--------------------------------------------------
//Handle the fancy URLs we've set up
//
// @since 1.0.0
//
// @param $wp
// @return true or n/a
function meow_parse_request( &$wp )
{
    //create a CSV dump of log-in history
    if(array_key_exists('meow_history',$wp->query_vars))
    {
    	//this requires permission
    	if(!current_user_can('manage_options'))
			wp_die(__('You do not have sufficient permissions to access this file.'));

		global $wpdb;

		//set content-type headers for CSV
		header("Content-disposition: attachment; filename=login_history.csv");
		header('Content-type: text/csv');

		//throw this in an output buffer so the file downloads all at once
		ob_start();

		//CSV headers
		echo '"DATE","STATUS","USER","IP","BROWSER"';

		//pull all records from the database
		$dbResult = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}meow_log` ORDER BY `date` ASC", ARRAY_A);
		if($wpdb->num_rows > 0)
		{
			foreach($dbResult AS $Row)
			{
				if(intval($Row["success"]) === 1)
					$status = 'success';
				elseif(intval($Row["success"]) === -1)
					$status = 'apocalypse';
				else
					$status = 'failure';
				echo "\n\"" . implode('","', array(
					0 => date("Y-m-d H:i:s", $Row["date"]),
					1 => $status,
					2 => str_replace('"', '\"', $Row["username"]),
					3 => $Row["ip"],
					4 => str_replace('"', '\"', $Row["ua"])
					)) . '"';
			}
		}

		//send the buffer contents out into the world!
		echo ob_get_clean();

        exit();
    }

    return true;
}
add_action('parse_request','meow_parse_request' );

//--------------------------------------------------
//Set up permalink rules on activation
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_activate(){
	//meow_init registers the rewrite rule(s)
	meow_init();
	flush_rewrite_rules();
	return true;
}
register_activation_hook(__FILE__, 'meow_activate');

//--------------------------------------------------
//Remove permalink rules on de-activation
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_deactivate(){
	flush_rewrite_rules();
	return true;
}
register_deactivation_hook( __FILE__, 'meow_deactivate');

//----------------------------------------------------------------------  end WP backend stuff



//----------------------------------------------------------------------
//  Log-in protection
//----------------------------------------------------------------------
//functions relating to the log-in protection section

//--------------------------------------------------
//Create/update a table for log-in logging
//
// the table contains the following fields:
// `id` numeric primary key
// `ip` the logee's IP address
// `date` a timestamp
// `success` whether or not the log-in happend; 1 valid, 0 failed
// `ua` the logee's browser's reported user agent
// `username` the WP account being accessed
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_SQL(){
	global $wpdb;

	$sql = "CREATE TABLE {$wpdb->prefix}meow_log (
  id bigint(15) NOT NULL AUTO_INCREMENT,
  ip varchar(39) NOT NULL,
  date int(15) NOT NULL,
  success tinyint(1) NOT NULL,
  ua varchar(250) NOT NULL,
  username varchar(50) NOT NULL,
  PRIMARY KEY  (id),
  KEY ip (ip),
  KEY date (date),
  KEY success (success),
  KEY ua (ua),
  KEY username (username)
);";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);

	update_option("meow_db_version", MEOW_DB);

	return true;
}
register_activation_hook(__FILE__,'meow_SQL');

//--------------------------------------------------
//Check if a database update is required
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_db_update(){
    if(get_option('meow_db_version', '0.0.0') !== MEOW_DB)
        meow_SQL();

    return true;
}
add_action('plugins_loaded', 'meow_db_update');

//--------------------------------------------------
//Get and/or validate an IP address
//
// if no IP is passed, REMOTE_ADDR is used.  IP is returned so long as
// it is a valid address (and not private/reserved), otherwise false
//
// @since 1.0.0
//
// @param $ip (optional) an IP address to validate; otherwise REMOTE_ADDR
// @return string IP or false
function meow_get_IP($ip=null){
	//if not supplied, let's use REMOTE_ADDR
	if(is_null($ip))
		$ip = getenv('REMOTE_ADDR');

	//return the ip, unless it is invalid
	return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ? $ip : false;
}

//--------------------------------------------------
//Sanitize an array of IPs
//
// @since 1.0.0
//
// @param $ips an array of IPs or string containing a single array
// @return array valid IPs
function meow_sanitize_ips($ips){
	//even a single IP should be stuffed into an array
	if(!is_array($ips))
		$ips = array(0=>$ips);

	//store valid IPs
	$valid = array();

	if(count($ips))
	{
		foreach($ips AS $ip)
		{
			if(false !== meow_get_IP(trim($ip)))
				$valid[] = $ip;
		}
	}

	if(count($valid))
	{
		sort($valid);
		return array_unique($valid);
	}
	else
		return array();
}

//--------------------------------------------------
//Determine whether or not a log-in may proceed
//
// if log-in protection is enabled, we grab various user-defined limits
// and determine whether the logee may proceed or whether he/she should
// get the Apocalypse Meow screen instead.
//
// @since 1.0.0
//
// @param n/a
// @return true or output HTML and exit
function meow_check_IP(){
	global $wpdb;

	//if log-in protection is disabled, let's leave without wasting any more time
	if(!meow_get_option('meow_protect_login'))
		return true;

	//ignore the server's IP, and anything defined by the user
	$ignore = array_merge(array(getenv('SERVER_ADDR')), meow_get_option('meow_ip_exempt'));

	//further scrutinize only if the IP address is valid
	if(false !== ($ip = meow_get_IP()) && !in_array($ip, $ignore))
	{
		//user settings
		$meow_fail_limit = meow_get_option('meow_fail_limit');
		$meow_fail_window = meow_get_option('meow_fail_window');
		$meow_fail_reset_on_success = meow_get_option('meow_fail_reset_on_success');

		//if the fail count resets on success, we'll only look at failures since the last successful log-in (if any)
		//default is 0, which is fine since all log-in attempts come after the Unix epoch.  :)
		$meow_last_successful = $meow_fail_reset_on_success ? (int) $wpdb->get_var("SELECT MAX(`date`) FROM `{$wpdb->prefix}meow_log` WHERE `ip`='" . mysql_real_escape_string($ip) . "' AND `success`=1") : 0;

		//if the relevant failures are too great, trigger the apocalypse
		if($meow_fail_limit <= (int) $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `{$wpdb->prefix}meow_log` WHERE `ip`='" . mysql_real_escape_string($ip) . "' AND `success`=0 AND UNIX_TIMESTAMP()-`date` <= $meow_fail_window AND `date` > $meow_last_successful"))
		{
			//indicate in the logs that the apocalypse screen was shown:
			meow_login_log(-1, 'n/a');
			//try to set the 403 status header
			header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden',true,403);
			//print the page
			echo '<html><head><title>' . meow_get_option('meow_apocalypse_title') . '</title><link rel="stylesheet" href="' . get_bloginfo('stylesheet_url') . '" /></head><body>' . meow_get_option('meow_apocalypse_content') . '</body></html>';
			exit;
		}
	}

	return true;
}
add_action('login_init','meow_check_IP');

//--------------------------------------------------
//Log log-in attempts and successes
//
// @since 1.1.0
//
// @param $status -1 apocalypse; 0 fail; 1 success
// @param $username
// @return true
function meow_login_log($status=0, $username=''){
	global $wpdb;

	//get MySQL time (as this may not always be the same as PHP)
	$time = (int) $wpdb->get_var("SELECT UNIX_TIMESTAMP()");

	//this only works if we have a valid IP
	if(false !== ($ip = meow_get_IP()))
		$wpdb->insert("{$wpdb->prefix}meow_log", array("ip"=>$ip, "ua"=>getenv("HTTP_USER_AGENT"), "date"=>$time, "success"=>$status, "username"=>$username), array('%s', '%s', '%d', '%d', '%s'));

	return true;
}

//--------------------------------------------------
//Wrapper for meow_login_log on failure
//
// @since 1.1.0
//
// @param n/a
// @return true
function meow_login_error(){
	return meow_login_log(0, trim(strtolower(stripslashes_deep($_REQUEST["log"]))));
}
add_action('wp_login_failed','meow_login_error');

//--------------------------------------------------
//Wrapper for meow_login_log on success
//
// @since 1.1.0
//
// @param n/a
// @return true
function meow_login_success(){
	return meow_login_log(1, trim(strtolower(stripslashes_deep($_REQUEST["log"]))));
}
add_action('wp_login', 'meow_login_success');

//--------------------------------------------------
//Database maintenance
//
// purge old log-in logs after a successful log-in.
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_clean_database(){
	global $wpdb;

	//only purge old records if database maintenance is enabled
	if(meow_get_option('meow_clean_database'))
	{
		//get MySQL time (as this may not always be the same as PHP)
		$time = (int) $wpdb->get_var("SELECT UNIX_TIMESTAMP()");
		//clear old entries
		$meow_data_expiration = meow_get_option('meow_data_expiration');
		$wpdb->query("DELETE FROM `{$wpdb->prefix}meow_log` WHERE `date` < " . strtotime("-$meow_data_expiration days", $time));
	}

	return true;
}
add_action('wp_login','meow_clean_database');

//----------------------------------------------------------------------  end log-in protection



//----------------------------------------------------------------------
//  Password restrictions
//----------------------------------------------------------------------
//functions to ensure user passwords meet certain minimum safety
//standards

//--------------------------------------------------
//A wrapper function for meow_password_rules()
//
// @since 1.0.0
//
// @param $user WP user
// @param $pass1 password
// @param $pass2 password (again)
// @return true
function meow_password_rules_check($user, &$pass1, &$pass2){ meow_password_rules(&$pass1, &$pass2); }
add_action('check_passwords','meow_password_rules_check', 10, 3);

//--------------------------------------------------
//Enforce additional rules against user password choices
//
// based on user settings, passwords might be required to include at
// least one number, lowercase character, uppercase character, and/or
// symbol, and hit a certain overall length.
//
// @since 1.0.0
//
// @param $pass1 password
// @param $pass2 password (again)
// @return true/false
function meow_password_rules(&$pass1, &$pass2)
{
	global $meow_password_error;

	//WP can handle password mismatch or empty password errors
	if($pass1 !== $pass2 || !strlen($pass1))
		return false;

	//needs a letter
	if(meow_get_option('meow_password_alpha') === 'required' && !preg_match('/[a-z]/i', $pass1))
	{
		$meow_password_error = __('The password must contain at least one letter.');
		return false;
	}
	//needs both upper- and lowercase letters
	elseif(meow_get_option('meow_password_alpha') === 'required-both' && (!preg_match('/[a-z]/', $pass1) || !preg_match('/[A-Z]/', $pass1)))
	{
		$meow_password_error = __('The password must contain at least one uppercase and one lowercase letter.');
		return false;
	}

	//needs a number
	if(meow_get_option('meow_password_numeric') === 'required' && !preg_match('/\d/', $pass1))
	{
		$meow_password_error = __('The password must contain at least one number.');
		return false;
	}

	//needs a symbol
	if(meow_get_option('meow_password_symbol') === 'required' && !preg_match('/[^a-z0-9]/i', $pass1))
	{
		$meow_password_error = __('The password must contain at least one non-alphanumeric symbol.');
		return false;
	}

	//check password length
	$meow_password_length = meow_get_option('meow_password_length');
	if(strlen($pass1) < $meow_password_length)
	{
		$meow_password_error = __("The password must be at least $meow_password_length characters long.");
		return false;
	}

	return true;
}
add_action('password_rules','meow_password_rules', 10, 2);

//--------------------------------------------------
//Report password errors
//
// @since 1.0.0
//
// @param $errors array of errors
// @return true
function meow_password_rules_error($errors)
{
	global $meow_password_error;

	if(false !== $meow_password_error)
		$errors->add( 'pass', $meow_password_error, array( 'form-field' => 'pass1' ) );

	return true;
}
add_action('user_profile_update_errors','meow_password_rules_error', 10, 1);
add_action('password_rules_error','meow_password_rules_error', 10, 1);

//----------------------------------------------------------------------  end password restrictions



//----------------------------------------------------------------------
//  Miscellaneous security things
//----------------------------------------------------------------------
//other odds and ends that made it into this plugin

//--------------------------------------------------
//Remove "generator" <meta> tag
//
// only add the filter if specified by user
//
// @since 1.0.0
//
// @param n/a
// @return string (empty)
function meow_remove_wp_version(){ return ''; }
if(meow_get_option('meow_remove_generator_tag'))
	add_filter('the_generator', 'meow_remove_wp_version');

//--------------------------------------------------
//Determine whether .htaccess exists in wp-content
//
// @since 1.2.0
//
// @param n/a
// @return true/false
function meow_wpcontent_htaccess_exists(){
	//if the file doesn't exist, return false
	if(!file_exists(MEOW_HTACCESS_FILE))
		return false;

	//try to read the file
	if(false === ($htcontent = @file_get_contents(MEOW_HTACCESS_FILE)))
		return false;

	//finally, are the contents as expected (give or take some new lines)
	$htcontent = str_replace("\r\n", "\n", $htcontent);
	$htcontent = str_replace("\r", "\n", $htcontent);
	$htcontent = trim($htcontent);
	return $htcontent === MEOW_HTACCESS;
}

//--------------------------------------------------
//Add .htaccess to wp-content
//
// @since 1.2.0
//
// @param n/a
// @return true/false status
function meow_add_wpcontent_htaccess(){
	//if it already exists, we don't need to be here
	if(meow_wpcontent_htaccess_exists())
		return true;

	//try to write it
	@file_put_contents(MEOW_HTACCESS_FILE, MEOW_HTACCESS);

	//if the write worked, the file should exist, right?
	return meow_wpcontent_htaccess_exists();
}

//--------------------------------------------------
//Remove .htaccess from wp-content
//
// @since 1.2.0
//
// @param n/a
// @return true/false status
function meow_remove_wpcontent_htaccess(){
	//if the file doesn't exist, we're done
	if(!meow_wpcontent_htaccess_exists())
		return true;

	//try to delete it
	@unlink(MEOW_HTACCESS_FILE);

	//if the unlink worked, the file should be gone, right?
	return !meow_wpcontent_htaccess_exists();
}

//----------------------------------------------------------------------  end misc security