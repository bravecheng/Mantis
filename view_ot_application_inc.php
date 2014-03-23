<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */

	/**
	 * requires current_user_api
	 */
	require_once( 'current_user_api.php' );
	/**
	 * requires bug_api
	 */
	require_once( 'bug_api.php' );
	/**
	 * requires string_api
	 */
	require_once( 'string_api.php' );
	/**
	 * requires date_api
	 */
	require_once( 'date_api.php' );
	/**
	 * requires icon_api
	 */
	require_once( 'icon_api.php' );
	/**
	 * requires columns_api
	 */
	require_once( 'columns_api.php' );


	$t_filter = current_user_get_bug_filter();
	# NOTE: this check might be better placed in current_user_get_bug_filter()
	if ( $t_filter === false ) {
		$t_filter = filter_get_default();
	}

	list( $t_sort, ) = explode( ',', $t_filter['sort'] );
	list( $t_dir, ) = explode( ',', $t_filter['dir'] );

	$t_checkboxes_exist = false;

	$t_icon_path = config_get( 'icon_path' );
	$t_update_bug_threshold = config_get( 'update_bug_threshold' );

	# Improve performance by caching category data in one pass
	if ( helper_get_current_project() > 0 ) {
		category_get_all_rows( helper_get_current_project() );
	} else {
		$t_categories = array();
		foreach ($rows as $t_row) {
			$t_categories[] = $t_row->category_id;
		}
		category_cache_array_rows( array_unique( $t_categories ) );
	}
	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_VIEW_PAGE );

	$col_count = count( $t_columns );

	$t_filter_position = config_get( 'filter_position' );

	# -- ====================== FILTER FORM ========================= --
	if ( ( $t_filter_position & FILTER_POSITION_TOP ) == FILTER_POSITION_TOP ) {
		//filter_draw_selection_area( $f_page_number );
	}
	# -- ====================== end of FILTER FORM ================== --


	# -- ====================== BUG LIST ============================ --

	$t_status_legend_position = config_get( 'status_legend_position' );

	if ( $t_status_legend_position == STATUS_LEGEND_POSITION_TOP || $t_status_legend_position == STATUS_LEGEND_POSITION_BOTH ) {
		//html_status_legend();
	}

	/** @todo (thraxisp) this may want a browser check  ( MS IE >= 5.0, Mozilla >= 1.0, Safari >=1.2, ...) */
	if ( ( ON == config_get( 'dhtml_filters' ) ) && ( ON == config_get( 'use_javascript' ) ) ){
		?>
		<script type="text/javascript" language="JavaScript">
		<!--
			var string_loading = '<?php echo lang_get( 'loading' );?>';
		// -->
		</script>
		<?php
			html_javascript_link( 'xmlhttprequest.js');
			html_javascript_link( 'addLoadEvent.js');
			html_javascript_link( 'dynamic_filters.js');
			html_javascript_link( 'jquery.js');
	}

	?>
<br />
<div style="text-align:center;color:red;">
	<?php 
	if($_REQUEST['result'] == "1"){
		echo "The OT added successfully.";
	}else if($_REQUEST['result'] == "2"){
		echo "The OT added successfully. But the email do not send successfully. Please contact <a href='mailto:windy.wang@expacta.com.cn'>windy.wang@expacta.com.cn</a>";
	}
	?>
</div>
<form id="saveOT" action="" method="POST">					
<table cellspacing="1" class="width100" align="center">

	<!-- Headings -->
	<tbody>
	<tr>
		<td class="form-title">OT Application</td>
	</tr>
	<tr>
		<td style="padding-left: 0px;display:none" id="show_list">
			<table cellspacing="1" class="width50" align="" id="employee_list" style="border-bottom:0px;">
				<tr class="row-category">
					<td style="width:100px;">Employee</td>
					<td style="width:250px;">OT Date/hours</td>
					<td style="width:150px;">OT Type</td>
					<td></td>
				</tr>
			</table>
			<table cellspacing="1" class="width50" style="border-top:0px;">
				<tr>
					<td>OT Subject: <input type="text" id="ot_subject" name="ot_subject" style="width:400px;"/></td>
					<td style="text-align:right"><input type="submit" value="Save OT" name="save_ot"/></td>
				</tr>
			</table>
				
		</td>
	</tr>
	
</table>
</form>	
<form id="addOT" action="" method="POST">
<table cellspacing="1" class="width100" align="center">
	<!-- Username -->
	<tr class="row-1">
		<td width="25%" class="category">
			Employee
		</td>
		<td width="75%">
		<select id="employee" name="employee">
		<option value="">Select One</option>
		<?php 
					
					$sql = "SELECT mantis_user_table. * , group_concat( mantis_project_user_list_table.project_id) AS project_list_ids FROM `mantis_user_table`
								LEFT JOIN mantis_project_user_list_table ON mantis_user_table.id = mantis_project_user_list_table.user_id
							WHERE enabled =1 GROUP BY mantis_user_table.id";
					$result = mysql_query($sql);
					while ($row = mysql_fetch_assoc($result)) {
						$projectListIds = str_replace(",", "_", $row['project_list_ids']);
						$projectListIdArray = explode("_", $projectListIds);
						if(!$projectListIdArray){
							$projectListIdArray = array();
						}
						if($currentProjectId){
							if(in_array($currentProjectId, $projectListIdArray)){
								$tempStyle = "display:block";
							}else{
								$tempStyle = "display:none";
							}
						}else{
							$tempStyle = "display:block";
						}
					?>
					<option value="<?php echo $row['id']?>"><?php echo $row['username']?></option>
					<?php	
					}
					?>
		</select>
		<span id="employee_error" style="color:red;"></span>
		
		</td>
	</tr>

	<!-- Password -->
	<tr class="row-2">
		<td class="category">OT Date</td>
		<td>
			<select name="start_year" id="start_year"  class="mantis_time">
				<?php foreach ($rangeYears as $itemYear):?>
					<option value="<?php echo $itemYear;?>" <?php if($defaultYearStart == $itemYear) echo " selected ";?>> <?php echo $itemYear;?> </option>
				<?php endforeach;?>
			</select>
			<select class="mantis_time" name="start_month" id="start_month">
			<?php foreach ($rangeMonths as $itemKey=>$itemMonth):?>
				<option value="<?php echo $itemKey;?>" <?php if($defaultMonthStart == $itemKey) echo " selected ";?>><?php echo $itemMonth;?></option>
			<?php endforeach;?>
			</select>
			
			<select class="mantis_time" name="start_day" id="start_day">
				<?php foreach ($rangeDays as $itemDay):?>
					<option value="<?php echo $itemDay?>" <?php if($defaultDayStart == $itemDay) echo " selected ";?>> <?php echo $itemDay?> </option>
				<?php endforeach;?>
			 </select>
		</td>
	</tr>

	<!-- Password confirmation -->
	<tr class="row-1">
		<td class="category">OT Hours</td>
		<td>
			<input type="text" value="00:00" size="5" name="time_tracking" id="time_tracking">
			<span id="time_tracking_error" style="color:red;"></span>
		</td>
	</tr>

 <!-- Without LDAP Email -->

	<!-- Email -->
	<tr class="row-2">
		<td class="category">OT Type</td>
		<td>
			<select name="ot_type" id="ot_type">
				<?php foreach ($oTType as $otTypeId=>$otName):?>
				<option value="<?php echo $otTypeId;?>"><?php echo $otName?></option>
				<?php endforeach;?>
			</select>
			<span id="ot_type_error" style="color:red;"></span>
		</td>
	</tr>

	<tr>
		<td class="left">
					</td>
		<!-- Update Button -->
		<td>
			<input type="button" value="Add OT" class="button" onclick="addOT();">
		</td>
	</tr>
	</tbody>
</table>
</form>	


<script>
function addOT(){
	var checkAdd = true;
	$('#employee_error').html("");
	$('#ot_type_error').html("");
	$('#time_tracking_error').html("");
	if($('#employee').val() == ""){
		$('#employee_error').html("Please select one employee");
		checkAdd = false;
	}
	if($('#ot_type').val() == ""){
		$('#ot_type_error').html("Please select one type");
		checkAdd = false;
	}
	if ($('#time_tracking').val().match(/^[0-9]{1,3}:[0-9][0-9]$/i)) {
		if($('#time_tracking').val() == "00:00"){
			$('#time_tracking_error').html("Please input right hours");
			checkAdd = false;
		}
	}else{
		$('#time_tracking_error').html("Please input right hours");
		checkAdd = false;
	}
	if(checkAdd == true){
		$('#show_list').show();
		var userName = $('#employee').find("option:selected").text(); 
		var userId = $('#employee').val();
		var oTDateHours = $('#start_year').val() + "-" + $('#start_month').val() + '-' + $('#start_day').val() + " " + $('#time_tracking').val() + " Hour(s)";
		var oTdate = $('#start_year').val() + "-" + $('#start_month').val() + '-' + $('#start_day').val();
		var oTTime = $('#time_tracking').val();
		var oTTypeText = $('#ot_type').find("option:selected").text(); 
		var oTType = $('#ot_type').val(); 
		var trStr = '<tr class="row-1"><td align="left" style="text-align: center;" class="small-caption">'+userName+'<input name="employee_ids[]" value="'+userId+'" type="hidden"></td><td style="text-align: center;" class="small-caption">'+oTDateHours+'<input type="hidden" name="ot_dates[]" value="'+oTdate+'"><input type="hidden" name="ot_times[]" value="'+oTTime+'"></td><td align="center" style="text-align: center;" class="small-caption">'+oTTypeText+'<input type="hidden" name="ot_types[]" value="'+oTType+'"></td><td class="small-caption"><input type="button" value="Delete" onclick="removeOT(this)"></td></tr>';
		$('#employee_list').append(trStr);
		$('#employee').val("");
		//$('#addOT')[0].reset();
	}
	return false;
}

function removeOT(obj){
	$(obj).parent().parent().remove();
	console.log($(obj).parent());
}
$(function(){

});
</script>
