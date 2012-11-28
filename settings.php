<?php
//----------------------------------------------------------------------
//  Apocalypse Meow settings
//----------------------------------------------------------------------
//display a form so authorized WP users can configure Apocalypse Meow
//and save the settings
//
// @since 1.0.0



//--------------------------------------------------
//Check permissions

//let's make sure this page is being accessed through WP
if (!function_exists('current_user_can'))
	die('Sorry');
//and let's make sure the current user has sufficient permissions
elseif(!current_user_can('manage_options'))
	wp_die(__('You do not have sufficient permissions to access this page.'));



//we'll need this later
$meowdata = array();



//--------------------------------------------------
//Process submitted data!

if(getenv("REQUEST_METHOD") === 'POST')
{
	//AAAAAARRRRGH DIE MAGIC QUOTES!!!!  Haha.
	$_POST = stripslashes_deep($_POST);

	//validate form data...
	$meowdata['meow_protect_login'] = intval($_POST['meow_protect_login']) === 1;
	$meowdata['meow_fail_limit'] = (int) $_POST['meow_fail_limit'];
		//silently correct invalid choice
		if($meowdata['meow_fail_limit'] < 1)
			$meow['meow_fail_limit'] = 5;
	$meowdata['meow_fail_window'] = (int) $_POST['meow_fail_window'];
		//silently correct invalid choice
		if($meowdata['meow_fail_window'] < 60)
			$meow['meow_fail_window'] = 43200;
	$meowdata['meow_fail_reset_on_success'] = intval($_POST['meow_fail_reset_on_success']) === 1;
	$meowdata['meow_ip_exempt'] = meow_sanitize_ips(explode("\n", $_POST['meow_ip_exempt']));
	$meowdata['meow_apocalypse_title'] = trim(strip_tags($_POST["meow_apocalypse_title"]));
	$meowdata['meow_apocalypse_content'] = trim($_POST['blurb']);
	$meowdata['meow_clean_database'] = intval($_POST['meow_clean_database']) === 1;
	$meowdata['meow_data_expiration'] = (int) $_POST['meow_data_expiration'];
		//silently correct bad data
		if($meowdata['meow_data_expiration'] < 3)
			$meowdata['meow_data_expiration'] = 90;

	$meowdata['meow_password_alpha'] = in_array($_POST['meow_password_alpha'], array('optional','required','required-both')) ? $_POST['meow_password_alpha'] : 'optional';
	$meowdata['meow_password_numeric'] = in_array($_POST['meow_password_numeric'], array('optional','required')) ? $_POST['meow_password_numeric'] : 'optional';
	$meowdata['meow_password_symbol'] = in_array($_POST['meow_password_symbol'], array('optional','required')) ? $_POST['meow_password_symbol'] : 'optional';
	$meowdata['meow_password_length'] = (double) $_POST['meow_password_length'];
		//silently correct bad data
		if($meowdata['meow_password_length'] < 1)
			$meowdata['meow_password_length'] = 5;

	$meowdata['meow_remove_generator_tag'] = intval($_POST['meow_remove_generator_tag']) === 1;

	//enable wp-content htaccess (only if it doesn't already exist)
	if(intval($_POST["meow_wpcontent_htaccess"]) === 1 && !meow_wpcontent_htaccess_exists())
	{
		if(false === meow_add_wpcontent_htaccess())
			echo '<div class="error fade"><p>WordPress could not automatically create <code>' . MEOW_HTACCESS_FILE . '</code>, the file containing the rules to prevent direct PHP script execution.  You\'ll have to roll up your sleeves and do it manually. Simply copy the following code into a text file named &quot;.htaccess&quot; and upload it to your wp-content/ directory:</p><p><code>' . nl2br(htmlspecialchars(MEOW_HTACCESS)) . '</code></p></div>';
		else
			echo '<div class="updated fade"><p>The file containing rules to prevent the direct execution of PHP scripts (<code>' . MEOW_HTACCESS_FILE . '</code>) has been successfully created!  Before grabbing yourself a celebratory beer:</p><ol><li>Try accessing the Apocalypse Meow settings page directly (you should get a 403 Forbidden error): <a href="' . plugins_url('settings.php', __FILE__) . '" target="_blank">' . plugins_url('settings.php', __FILE__) . '</a>  If instead you see &quot;Sorry&quot;, then your server is not recognizing the restriction (sorry!)</li><li>Take a thorough walkthrough of both the front- and backend of your site and make sure things still work as expected. If any plugins are caught by this trap, you\'ll need to replace them with better alternatives or live without this security lockdown.</li><li>That\'s it! Congratulations! :)</li></ol></div>';
	}
	//disable wp-content htaccess (only if it presently exists)
	elseif(intval($_POST["meow_wpcontent_htaccess"]) !== 1 && meow_wpcontent_htaccess_exists())
	{
		if(false === meow_remove_wpcontent_htaccess())
			echo '<div class="error fade"><p>WordPress was unable to delete <code>' . MEOW_HTACCESS_FILE . '</code>, the file containing the rules to prevent direct PHP script execution. Please manually delete this file.</div>';
		else
			echo '<div class="updated fade"><p>The rules preventing the direct execution of PHP scripts have been lifted.</p>';
	}

	//bad nonce, don't save
	if(!wp_verify_nonce($_POST['_wpnonce'],'meow-settings'))
		echo '<div class="error fade"><p>Sorry the form had expired.  Please try again.</p></div>';
	else
	{
		//update!
		foreach($meowdata AS $k=>$v)
			update_option($k, $v);

		//spread the joy
		echo '<div class="updated fade"><p>The apocalypse has been successfully updated.</p></div>';
	}
}

//--------------------------------------------------
//Grab saved or default settings
else
{
	$meowdata['meow_protect_login'] = meow_get_option('meow_protect_login');
	$meowdata['meow_fail_limit'] = meow_get_option('meow_fail_limit');
	$meowdata['meow_fail_window'] = meow_get_option('meow_fail_window');
	$meowdata['meow_fail_reset_on_success'] = meow_get_option('meow_fail_reset_on_success');
	$meowdata['meow_ip_exempt'] = meow_get_option('meow_ip_exempt');
	$meowdata['meow_apocalypse_content'] = meow_get_option('meow_apocalypse_content');
	$meowdata['meow_apocalypse_title'] = meow_get_option('meow_apocalypse_title');
	$meowdata['meow_clean_database'] = meow_get_option('meow_clean_database');
	$meowdata['meow_data_expiration'] = meow_get_option('meow_data_expiration');
	$meowdata['meow_password_alpha'] = meow_get_option('meow_password_alpha');
	$meowdata['meow_password_numeric'] = meow_get_option('meow_password_numeric');
	$meowdata['meow_password_symbol'] = meow_get_option('meow_password_symbol');
	$meowdata['meow_password_length'] = meow_get_option('meow_password_length');
	$meowdata['meow_remove_generator_tag'] = meow_get_option('meow_remove_generator_tag');
}

//--------------------------------------------------
//Output the form!
?>
<style type="text/css">
	.form-table {
		clear: left!important;
	}
</style>

<div class="wrap">

	<img src="<?php echo MEOW_IMAGE; ?>" style="width: 42px; float:left; margin-right: 10px; height: 42px; border: 0;" />
	<h2>Apocalypse Meow</h2>

	<div class="metabox-holder has-right-sidebar">

		<form id="form-meow-settings" method="post" action="<?php echo admin_url('options-general.php?page=meow-settings'); ?>">
		<?php wp_nonce_field('meow-settings'); ?>

		<div class="inner-sidebar">
			<!--start generator meta tag -->
			<div class="postbox">
				<h3 class="hndle">Remove the &quot;generator&quot; meta tag</h3>
				<div class="inside">
					<p>Most templates include the current WordPress version in the HTML &lt;head&gt;. While this information is largely innocuous (and discoverable elsewhere), it can help nogoodniks better target attacks against your site, particularly if you are running an out-of-date version of WordPress.</p>
					<p><label for="meow_remove_generator_tag">
						<input type="checkbox" name="meow_remove_generator_tag" id="meow_remove_generator_tag" value="1" <?php echo ($meowdata['meow_remove_generator_tag'] === true ? 'checked=checked' : ''); ?> />
						Check this box to remove the WP version information from your pages.
					</label></p>
				</div>
			</div>
			<!--end generator meta tag-->

			<!--start wp-content .htaccess-->
			<div class="postbox">
				<h3 class="hndle">Prevent direct script execution</h3>
				<div class="inside">
					<p>WordPress themes and plugins are made up of PHP scripts that *should* only be executed indirectly through the WordPress engine. Untargetted attacks generally involve sending robots around to poke at these scripts directly, looking for security weaknesses. Disallowing direct access to PHP files in wp-content renders such searches moot.</p>
					<p><label for="meow_wpcontent_htaccess">
						<input type="checkbox" name="meow_wpcontent_htaccess" id="meow_wpcontent_htaccess" value="1" <?php echo (meow_wpcontent_htaccess_exists() ? 'checked=checked' : ''); ?> />
						Check this box to disable the direct execution of PHP scripts stored inside wp-content/.
					</label></p>
					<p class="description">Note: This will only work if your server environment supports location-specific allow/deny rules in .htaccess.</p>
					<p class="description">Note #2: This might break things!  Some (lazy) plugins and themes foresake WP's engine and execute their scripts directly (and thus won't work if this option is enabled). If things break so badly you cannot even access this page to disable the option, simply delete <code><?php echo MEOW_HTACCESS_FILE; ?></code> via FTP.</p>
				</div>
			</div>
			<!--end wp-content .htaccess-->
		</div>
		<!--end sidebar-->

		<div id="post-body-content" class="has-sidebar">
			<div class="has-sidebar-content">

				<!--start log-in protection-->
				<div class="postbox">
					<h3 class="hndle">Log-in Protection</h3>
					<div class="inside">
						<p>Sometimes bad people use robots to cycle through zillions of possible log-in combinations.  If they magically guess a valid combination, your blog will magically become a Canadian pharmacy or Russian dating site, which is generally not desirable.  Luckily, we can mitigate the effectiveness of such an attack by limiting the number of failed log-in attempts allowed per person within a given time frame.  To keep things cheerful, we'll temporarily replace the log-in form with a kitty picture for people who exceed the specified limit.  We'll give 'em Apocalypse Meow!</p>

						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">Activate?</th>
									<td>
										<label for="meow_protect_login">
											<input type="checkbox" name="meow_protect_login" id="meow_protect_login" value="1" <?php echo ($meowdata['meow_protect_login'] === true ? 'checked=checked' : ''); ?> /> Enable log-in protection.  If unchecked, the rest of this section is ignored.
										</label>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Limitations</th>
									<td>
										<input type="number" step="1" min="1" id="meow_fail_limit" name="meow_fail_limit" value="<?php echo $meowdata['meow_fail_limit']; ?>" class="small-text" />
										<label for="meow_fail_limit">The maximum number of failed log-in attempts.</label>
										<br />

										<input type="number" step="60" min="60" id="meow_fail_window" name="meow_fail_window" value="<?php echo $meowdata['meow_fail_window']; ?>" class="small-text" />
										<label for="meow_fail_window">The time (in seconds) before a failed log-in attempt expires.</label>
										<br />

										<label for="meow_fail_reset_on_success"><input type="checkbox" name="meow_fail_reset_on_success" id="meow_fail_reset_on_success" value="1" <?php echo ($meowdata['meow_fail_reset_on_success'] === true ? 'checked=checked' : ''); ?> /> Reset fail count on successful log-in.</label>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Exempt IP(s), one per line</th>
									<td>
										<textarea name="meow_ip_exempt" rows="5" cols="50"><?php echo trim(implode("\n", $meowdata['meow_ip_exempt'])); ?></textarea>
										<p class="description">To avoid accidentally banning yourself, you might consider adding your IP address (<code><?php echo getenv('REMOTE_ADDR'); ?></code>) to the above list.</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Apocalypse Meow</th>
									<td>
										<input type="text" name="meow_apocalypse_title" id="meow_apocalypse_title" value="<?php echo htmlspecialchars($meowdata['meow_apocalypse_title']); ?>" class="regular-text" />
										<?php echo wp_editor( $meowdata['meow_apocalypse_content'], "blurb", $settings = array('teeny'=>true) ); ?>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Database maintenance</th>
									<td>
										<p><label for="meow_clean_database"><input type="checkbox" name="meow_clean_database" id="meow_clean_database" value="1" <?php echo ($meowdata['meow_clean_database'] === true ? 'checked=checked' : ''); ?> /> Check this box to enable database maintenance.</label></p>
										<p>Automatically purge log-in data older than <input type="number" step="1" min="3" id="meow_data_expiration" name="meow_data_expiration" value="<?php echo $meowdata['meow_data_expiration']; ?>" class="small-text" /> days.</p>
										<p class="description">Note: the maintenance routines are run after a successful log-in, so data might stick around longer than expected if you aren't frequently logging in.</p>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<!--end log-in protection-->



				<!--start password requirements-->
				<div class="postbox">
					<h3 class="hndle">Password Requirements</h3>
					<div class="inside">
						<p>Most people use horribly insecure passwords.  Tweak the following settings to enforce halfway decent choices from your users.<br />
						<span class="description">Note: These requirments are only applied to new (or updated) passwords; they have no effect on current passwords.</span></p>

						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">Letters...</th>
									<td>
										<p><input type="radio" name="meow_password_alpha" id="meow_password_alpha_optional" value="optional" <?php echo ($meowdata['meow_password_alpha'] === 'optional' ? 'checked=checked' : ''); ?> /> <label for="meow_password_alpha_optional">Letters are optional.</label><br />
										<input type="radio" name="meow_password_alpha" id="meow_password_alpha_required" value="required" <?php echo ($meowdata['meow_password_alpha'] === 'required' ? 'checked=checked' : ''); ?> /> <label for="meow_password_alpha_required">Passwords must contain at least one letter (case is unimportant).</label><br />
										<input type="radio" name="meow_password_alpha" id="meow_password_alpha_required_both" value="required-both" <?php echo ($meowdata['meow_password_alpha'] === 'required-both' ? 'checked=checked' : ''); ?> /> <label for="meow_password_alpha_required_both">Passwords must contain at least one uppercase letter and at least one lowercase letter.</label></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Numbers...</th>
									<td>
										<p><input type="radio" name="meow_password_numeric" id="meow_password_numeric_optional" value="optional" <?php echo ($meowdata['meow_password_numeric'] === 'optional' ? 'checked=checked' : ''); ?> /> <label for="meow_password_numeric_optional">Numbers are optional.</label><br />
										<input type="radio" name="meow_password_numeric" id="meow_password_numeric_required" value="required" <?php echo ($meowdata['meow_password_numeric'] === 'required' ? 'checked=checked' : ''); ?> /> <label for="meow_password_numeric_required">Passwords must contain at least one number.</label></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Symbols...</th>
									<td>
										<p><input type="radio" name="meow_password_symbol" id="meow_password_symbol_optional" value="optional" <?php echo ($meowdata['meow_password_symbol'] === 'optional' ? 'checked=checked' : ''); ?> /> <label for="meow_password_symbol_optional">Symbols are optional.</label><br />
										<input type="radio" name="meow_password_symbol" id="meow_password_symbol_required" value="required" <?php echo ($meowdata['meow_password_symbol'] === 'required' ? 'checked=checked' : ''); ?> /> <label for="meow_password_symbol_required">Passwords must contain at least one non-alphanumeric character, like a space or dash or something.</label></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">Minimum password length</th>
									<td>
										<p><input type="number" step="1" min="5" id="meow_password_length" name="meow_password_length" value="<?php echo $meowdata['meow_password_length']; ?>" class="small-text" /></p>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<!--end password requirements-->



			</div><!-- /has-sidebar-content -->
		</div><!-- /has-sidebar -->


		<p class="submit"><input type="submit" name="submit" value="Save" /></p>
		</form>

	</div><!-- /metabox-holder has-right-sidebar -->
</div><!-- /wrap -->