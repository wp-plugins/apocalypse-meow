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

	label[for='view-success'], label[for='view-failure'] {
		margin-right: 25px;
	}

	#meow-login-history tr.hidden {
		display: none;
	}

	table.tablesorter thead tr .header, table.tablesorter thead tr .headerSortUp, table.tablesorter thead tr .headerSortDown {
		position: relative;
		cursor: pointer;
	}
	table.tablesorter thead tr .header:after, table.tablesorter thead tr .headerSortUp:after, table.tablesorter thead tr .headerSortDown:after {
		content: '';
		width: 7px;
		position: absolute;
		left: 7px;
		background: transparent url('<?php echo plugins_url('jquery.tablesorter.png', __FILE__); ?>') scroll 0 0 no-repeat;
	}
	table.tablesorter thead tr .header:after {
		height: 9px;
		top: 13px;
	}
	table.tablesorter thead tr .headerSortUp:after {
		height: 4px;
		top: 13px;
	}
	table.tablesorter thead tr .headerSortDown:after {
		height: 4px;
		background-position: 0 -5px;
		top: 18px;
	}

</style>
<div class="wrap">

	<?php echo meow_get_header(); ?>

	<p>Click <a href="<?php echo get_bloginfo('url'); ?>/meow/login_history.csv" title="Download history in CSV format">here</a> to download a CSV dump of this information.</p>
	<p>FYI: <?php
	//this is a good place to let them know about the database maintenance setting
	$meow_clean_database = meow_get_option('meow_clean_database');
	$meow_data_expiration = meow_get_option('meow_data_expiration');
	if($meow_clean_database)
		echo "Records older than $meow_data_expiration days are automatically purged from the system.";
	else
		echo "Log-in data is currently retained forever, which is a long time.  If you find the table below getting a touch unruly, you can have the system automatically purge records after a certain amount of time.";
?>  Visit the <a href="<?php echo admin_url('options-general.php?page=meow-settings'); ?>" title="Apocalypse Meow settings">settings page</a> to change this behavior.</p>

	<p>Filter records by status: <label for="view-success"><input type="checkbox" id="view-success" data-status="record-success" checked=checked /> Success</label><label for="view-failure"><input type="checkbox" id="view-failure" data-status="record-failure" checked=checked /> Failed</label><label for="view-apocalypse"><input type="checkbox" id="view-apocalypse" data-status="record-apocalypse" checked=checked /> Apocalypse</label>

	<table id="meow-login-history" class="widefat tablesorter">
		<thead>
			<tr>
				<th>#</th>
				<th>&nbsp;&nbsp;&nbsp;&nbsp;Date</th>
				<th>&nbsp;&nbsp;&nbsp;&nbsp;Status</th>
				<th>&nbsp;&nbsp;&nbsp;&nbsp;Username</th>
				<th>&nbsp;&nbsp;&nbsp;&nbsp;IP</th>
				<th>&nbsp;&nbsp;&nbsp;&nbsp;Browser</th>
			</tr>
		</thead>
		<tbody>
<?php
	global $wpdb;

	//grab the data, if any
	$dbResult = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}meow_log` ORDER BY `date` DESC", ARRAY_A);
	if($wpdb->num_rows)
	{
		$num = 0;
		foreach($dbResult AS $Row)
		{
			$num++;
			if(intval($Row["success"]) === 1)
				$status = 'success';
			elseif(intval($Row["success"]) === -1)
				$status = 'apocalypse';
			else
				$status = 'failure';
?>
			<tr class="record-<?php echo $status; ?>">
				<td class="meow-record-number"></td>
				<td><?php echo date("Y-m-d H:i:s", $Row["date"]); ?></td>
				<td><?php echo $status; ?></td>
				<td><?php echo esc_html($Row["username"]); ?></td>
				<td><?php echo esc_html($Row["ip"]); ?></td>
				<td><?php echo esc_html($Row["ua"]); ?></td>
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

	//filter the visible log-in entries by status
	jQuery("input[type=checkbox]").click(function(){
		if(jQuery(this).is(":checked"))
			jQuery("#meow-login-history tr." + jQuery(this).attr('data-status')).removeClass('hidden');
		else
			jQuery("#meow-login-history tr." + jQuery(this).attr('data-status')).addClass('hidden');
		jQuery("#meow-login-history").trigger("update");
		meow_number_records();
	});

	//dynamically number the rows
	function meow_number_records(){
		var num = 0;
		jQuery("#meow-login-history .meow-record-number:visible").each(function(k,v){
			num++;
			jQuery(this).html(num);
		});
	}
	meow_number_records();

	jQuery("#meow-login-history").tablesorter({sortList: [[1,1]], headers: { 0: { sorter: false} }});


</script>