<?php
//----------------------------------------------------------------------
//  Apocalypse Meow log-in history
//----------------------------------------------------------------------
//display a table containing the log-in history we've collected, and
//also link to a CSV dump
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

?>
<style type="text/css">
	#meow-login-history th, #meow-login-history td {
		text-align: left;
		vertical-align: top;
		padding: 5px 10px 5px 10px;
	}
	#meow-login-history tr.alternate {
		background-color: #eee;
	}
	label[for='view-success'] {
		margin-right: 25px;
	}
	#meow-login-history tr.hidden {
		display: none;
	}
</style>
<div class="wrap">

	<img src="<?php echo MEOW_IMAGE; ?>" style="width: 42px; float:left; margin-right: 10px; height: 42px; border: 0;" />
	<h2>Apocalypse Meow: Log-in History</h2>
	<p>Click <a href="<?php echo get_bloginfo('url'); ?>/meow/login_history.csv" title="Download history in CSV format">here</a> to download a CSV dump of this information.</p>
	<p>FYI: <?php
	//this is a good place to let them know about the database maintenance setting
	$meow_clean_database = (bool) get_option('meow_clean_database', false);
	$meow_data_expiration = (int) get_option('meow_data_expiration', 90);
		//silently correct bad data
		if($meow_data_expiration < 3)
		{
			$meow_data_expiration = 90;
			update_option('meow_data_expiration', 90);
		}
	if($meow_clean_database)
		echo "Records older than $meow_data_expiration days are automatically purged from the system.";
	else
		echo "Log-in data is currently retained forever, which is a long time.  If you find the table below getting a touch unruly, you can have the system automatically purge records after a certain amount of time."
?>  Visit the <a href="<?php echo admin_url('options-general.php?page=meow-settings'); ?>" title="Apocalypse Meow settings">settings page</a> to change this behavior.</p>

	<p>Filter records by status: <label for="view-success"><input type="checkbox" id="view-success" data-status="record-success" checked=checked /> Success</label><label for="view-failure"><input type="checkbox" id="view-failure" data-status="record-failure" checked=checked /> Failed</label>

	<table id="meow-login-history" cellpadding=0 cellspacing=0>
		<thead>
			<tr>
				<th>#</th>
				<th>Date</th>
				<th>Status</th>
				<th>Username</th>
				<th>IP</th>
				<th>Browser</th>
			</tr>
		</thead>
		<tbody>
<?php
	global $wpdb;

	//grab the data, if any
	$dbResult = mysql_query("SELECT * FROM `{$wpdb->prefix}meow_log` ORDER BY `date` DESC");
	if(mysql_num_rows($dbResult))
	{
		$num = 0;
		while($Row = mysql_fetch_assoc($dbResult))
		{
			$num++;
			$status = (intval($Row["success"]) === 1 ? 'success' : 'failure');
?>
			<tr class="record-<?php echo $status; ?>">
				<td><?php echo $num; ?></td>
				<td><?php echo date("Y-m-d H:i:s", $Row["date"]); ?></td>
				<td><?php echo $status; ?></td>
				<td><?php echo htmlspecialchars($Row["username"]); ?></td>
				<td><?php echo $Row["ip"]; ?></td>
				<td><?php echo htmlspecialchars($Row["ua"]); ?></td>
			</tr>
<?php
		}
	}
	else
		echo '<tr><td colspan=5>There is no log-in history.</td></tr>';
?>
		</tbody>
	</table>

</div>

<script type="text/javascript">

	function color_rows(){
		jQuery("#meow-login-history tr.alternate").removeClass('alternate');
		jQuery("#meow-login-history tr:visible:odd").addClass('alternate');
	}
	color_rows();

	jQuery("input[type=checkbox]").click(function(){
		if(jQuery(this).is(":checked"))
			jQuery("#meow-login-history tr." + jQuery(this).attr('data-status')).removeClass('hidden');
		else
			jQuery("#meow-login-history tr." + jQuery(this).attr('data-status')).addClass('hidden');
		color_rows();
	});

</script>