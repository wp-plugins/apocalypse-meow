<?php
/*
Plugin Name: Apocalypse Meow - Log-in Protection
Plugin URI: http://www.blobfolio.com
Description: A simple, light-weight collection of tools to help protect wp-admin, including password strength requirements and brute-force log-in prevention.
Version: 1.0.0
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
//  Constants/globals
//----------------------------------------------------------------------

//the database version
define('MEOW_DB', '1.0.0');

//the kitten image (embedded so as to spare the server a separate request)
define('MEOW_IMAGE', 'data:image/gif;base64,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');

//password validation errors
global $meow_password_error;
$meow_password_error = false;

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
		$dbResult = mysql_query("SELECT * FROM `{$wpdb->prefix}meow_log` ORDER BY `date` ASC");
		if(mysql_num_rows($dbResult))
		{
			while($Row = mysql_fetch_assoc($dbResult))
			{
				echo "\n\"" . implode('","', array(
					0 => date("Y-m-d H:i:s", $Row["date"]),
					1 => (intval($Row["success"]) === 1 ? 'success' : 'failure'),
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
	if(1 !== intval(get_option('meow_protect_login', 1)))
		return true;

	//ignore the server's IP, and anything defined by the user
	$ignore = array_merge(array(getenv('SERVER_ADDR')), meow_sanitize_ips(get_option('meow_ip_exempt', array())));

	//further scrutinize only if the IP address is valid
	if(false !== ($ip = meow_get_IP()) && !in_array($ip, $ignore))
	{
		//user settings
		$meow_fail_limit = (int) get_option('meow_fail_limit', 5);
			//set invalid entry to default...
			if($meow_fail_limit < 1)
			{
				$meow_fail_limit = 5;
				update_option('meow_fail_limit', 5);
			}
		$meow_fail_window = (int) get_option('meow_fail_window', 43200);
			//set invalid entry to default...
			if($meow_fail_window < 60)
			{
				$meow_fail_window = 43200;
				update_option('meow_fail_window', 43200);
			}
		$meow_fail_reset_on_success = (bool) get_option('meow_fail_reset_on_success', true);

		//if the fail count resets on success, we'll only look at failures since the last successful log-in (if any)
		//default is 0, which is fine since all log-in attempts come after the Unix epoch.  :)
		$meow_last_successful = $meow_fail_reset_on_success ? (int) $wpdb->get_var("SELECT MAX(`date`) FROM `{$wpdb->prefix}meow_log` WHERE `ip`='" . mysql_real_escape_string($ip) . "' AND `success`=1") : 0;

		//if the relevant failures are too great, trigger the apocalypse
		if($meow_fail_limit <= (int) $wpdb->get_var("SELECT COUNT(*) AS `count` FROM `{$wpdb->prefix}meow_log` WHERE `ip`='" . mysql_real_escape_string($ip) . "' AND `success`=0 AND UNIX_TIMESTAMP()-`date` <= $meow_fail_window AND `date` > $meow_last_successful"))
		{
			//this page will be user-configurable next release
			echo '<html><head><title>apocalypse meow</title></head><body><img src="' . MEOW_IMAGE . '" style="width: 64px; height: 64px; border: 0; position: absolute; left: 50%; top: 50%; margin-left: -32px; margin-top: -32px;" /><div style="position:absolute; bottom:0; left:0; width: 100%; text-align:center;">You have exceeded the maximum number of log-in attempts.  Sorry.</div></body></html>';
			exit;
		}
	}

	return true;
}
add_action('login_init','meow_check_IP');

//--------------------------------------------------
//Log invalid log-in attempts
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_log_error(){
	global $wpdb;

	//what username did the logee submit?
	$username = trim(strtolower(stripslashes_deep($_REQUEST["log"])));

	//this only works if we have a valid IP
	if(false !== ($ip = meow_get_IP()))
		$wpdb->insert("{$wpdb->prefix}meow_log", array("ip"=>$ip, "ua"=>getenv("HTTP_USER_AGENT"), "date"=>time(), "success"=>0, "username"=>$username), array('%s', '%s', '%d', '%d', '%s'));

	return true;
}
add_action('wp_login_failed','meow_log_error');

//--------------------------------------------------
//Log successful log-ins
//
// @since 1.0.0
//
// @param n/a
// @return true
function meow_log_success(){
	global $wpdb;

	//what username did the logee submit?
	$username = trim(strtolower(stripslashes_deep($_REQUEST["log"])));

	//this only works if we have a valid IP
	if(false !== ($ip = meow_get_IP()))
		$wpdb->insert("{$wpdb->prefix}meow_log", array("ip"=>$ip, "ua"=>getenv("HTTP_USER_AGENT"), "date"=>time(), "success"=>1, "username"=>$username), array('%s', '%s', '%d', '%d', '%s'));

	return true;
}
add_action('wp_login','meow_log_success');

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

	$meow_data_expiration = (int) get_option('meow_data_expiration', 90);
		//silently correct bad data
		if($meow_data_expiration < 3)
		{
			$meow_data_expiration = 90;
			update_option('meow_data_expiration', 90);
		}

	//only purge old records if database maintenance is enabled
	if(1 === intval(get_option('meow_clean_database', false)))
		$wpdb->query("DELETE FROM `{$wpdb->prefix}meow_log` WHERE `date` < " . strtotime("-$meow_data_expiration days"));

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
	if(get_option('meow_password_alpha','optional') === 'required' && !preg_match('/[a-z]/i', $pass1))
	{
		$meow_password_error = __('The password must contain at least one letter.');
		return false;
	}
	//needs both upper- and lowercase letters
	elseif(get_option('meow_password_alpha','optional') === 'required-both' && (!preg_match('/[a-z]/', $pass1) || !preg_match('/[A-Z]/', $pass1)))
	{
		$meow_password_error = __('The password must contain at least one uppercase and one lowercase letter.');
		return false;
	}

	//needs a number
	if(get_option('meow_password_numeric', 'optional') === 'required' && !preg_match('/\d/', $pass1))
	{
		$meow_password_error = __('The password must contain at least one number.');
		return false;
	}

	//needs a symbol
	if(get_option('meow_password_symbol', 'optional') === 'required' && !preg_match('/[^a-z0-9]/i', $pass1))
	{
		$meow_password_error = __('The password must contain at least one non-alphanumeric symbol.');
		return false;
	}

	//check password length
	$meow_password_length = (double) get_option('meow_password_length',5);
		//silently correct bad user selection
		if($meow_password_length < 1)
		{
			$meow_password_length = 5;
			update_option('meow_password_length', 5);
		}
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
if(1 === intval(get_option('meow_remove_generator_tag', 1)))
	add_filter('the_generator', 'meow_remove_wp_version');

//----------------------------------------------------------------------  end misc security