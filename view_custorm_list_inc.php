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
<form name="bug_action" method="GET" action="custorm_define_list_page.php">


<table cellspacing="1" class="width100">
		<tbody>
		<tr class="row-category2">

			<td valign="top" class="small-caption" colspan="3">
				<div style="padding-left: 0px;">Project: 
					<select id="current_project" name="current_project" onchange="hideOtherUser(this.value)">
						<?php foreach ($projectList as $projectId=>$projectName):?>
						<option value="<?php echo $projectId;?>" <?php if($currentProjectId == $projectId) echo " selected ";?>><?php echo $projectName;?></option>
						<?php endforeach;?>
					</select>
				</div>
				<a id="handler_id_filter">Assigned To: <input type="checkbox" name="assigned_all" id="assigned_all" onclick="checkAssignedAll(this);">All</a>
			</td>
					</tr>

		<tr class="row-1">
			<td valign="top" id="reporter_id_filter_target" class="small-caption" colspan="3">
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
					<div style="width:100px;float:left" style="<?php $tempStyle;?>"><input onclick="unAssignedAll(this)" class="assigned_user" type="checkbox" value="<?php echo $row['id']?>" it="<?php echo $projectListIds;?>" name="reporter_id[]" <?php if(in_array($row['id'], $reporterIds)) echo " checked " ;?>/><?php echo $row['username']?></div>
					<?php	
					}
					?>
										</td>
			</tr>
		<tr class="">
			<td valign="top" class="small-caption" colspan="3">
				<a id="handler_id_filter">Worked For: <input type="checkbox" name="assigned_worked_all" id="assigned_worked_all" onclick="checkAssignedWorkedAll(this);">All</a>
			</td>
		</tr>
		<tr class="row-1">
			<td valign="top" id="reporter_id_filter_target" class="small-caption" colspan="3">
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
				<div style="width:100px;float:left" style="<?php $tempStyle;?>"><input onclick="unAssignedWorkedAll(this)" class="assigned_worked_user" type="checkbox" value="<?php echo $row['id']?>" it="<?php echo $projectListIds;?>" name="reporter_worked_id[]" <?php if(in_array($row['id'], $reporterWorkedIds)) echo " checked " ;?>/><?php echo $row['username']?></div>
				<?php	
				}
				?>
									</td>
		</tr>
			
			
		<tr class="row-category2">
			<td>Start Date <input type="radio" id="search_type" name="search_type" value="0" <?php if($searchType == "0") echo " checked ";?> onclick="setSearchType('0');" /></td>
			<td></td>
			<td>Start Id <input type="radio" id="search_type" name="search_type" value="1" <?php if($searchType == "1") echo " checked ";?> onclick="setSearchType('1');"/></td>
		</tr>
		<tr class="row-1">
			<td style="width:45%">
				<table cellspacing="0" cellpadding="0">
				<tbody>
		
		<!-- Start date -->
		<tr>
			<td >
			From:
			</td>
			<td nowrap="nowrap">
			<select name="start_year" class="mantis_time">
				<?php foreach ($rangeYears as $itemYear):?>
					<option value="<?php echo $itemYear;?>" <?php if($defaultYearStart == $itemYear) echo " selected ";?>> <?php echo $itemYear;?> </option>
				<?php endforeach;?>
			</select>
			<select class="mantis_time" name="start_month">
			<?php foreach ($rangeMonths as $itemKey=>$itemMonth):?>
				<option value="<?php echo $itemKey;?>" <?php if($defaultMonthStart == $itemKey) echo " selected ";?>><?php echo $itemMonth;?></option>
			<?php endforeach;?>
			</select>
			
			<select class="mantis_time" name="start_day">
				<?php foreach ($rangeDays as $itemDay):?>
					<option value="<?php echo $itemDay?>" <?php if($defaultDayStart == $itemDay) echo " selected ";?>> <?php echo $itemDay?> </option>
				<?php endforeach;?>
			 </select>
			</td>
		</tr>
		<!-- End date -->
		<tr>
			<td>
			To:
			</td>
			<td>
			<select class="mantis_time" name="end_year">
				<?php foreach ($rangeYears as $itemYear):?>
					<option value="<?php echo $itemYear;?>" <?php if($defaultYearEnd == $itemYear) echo " selected ";?>> <?php echo $itemYear;?> </option>
				<?php endforeach;?>
			</select>
			<select class="mantis_time" name="end_month">
				<?php foreach ($rangeMonths as $itemKey=>$itemMonth):?>
				<option value="<?php echo $itemKey;?>" <?php if($defaultMonthEnd == $itemKey) echo " selected ";?>><?php echo $itemMonth;?></option>
				<?php endforeach;?>
			</select>
			<select class="mantis_time" name="end_day">
				<?php foreach ($rangeDays as $itemDay):?>
					<option value="<?php echo $itemDay?>" <?php if($defaultDayEnd == $itemDay) echo " selected ";?>> <?php echo $itemDay?> </option>
				<?php endforeach;?>
			</select>
			</td>
		</tr>
		</tbody></table>
			</td>
			
			<td style="text-align:center"> OR </td>
			<td style="width:45%">From: <input name="mantis_fromId" id="mantis_formId" class="mantis_id" value="<?php echo $fromId;?>">   To: <input name="mantis_toId" id="mantis_toId" value="<?php echo $toId;?>" class="mantis_id"></td>
		</tr>
		
	<tr class="row-category2">

			<td valign="top" class="small-caption" colspan="3">
				Includes Resolved: <input type="checkbox" name="mantis_status[]" id="mantis_status" value="80" <?php if(in_array(80, $mantisStatus)) echo " checked "?>> &nbsp;
				Includes Closed: <input type="checkbox" name="mantis_status[]" id="mantis_status" value="90" <?php if(in_array(90, $mantisStatus)) echo " checked "?>>&nbsp;
				<input type="submit" value="Search" class="button-small" name="search">
			</td>
	</tr>	
	
		</tbody></table>

</form>

<table cellspacing="1" class="width100" id="buglist">
<tbody><tr>
	<td colspan="10" class="form-title">
		<span class="floatleft"> Viewing Issues (<?php echo $countNumber?>) </span>
		<span class="floatleft small">&nbsp;<span class="bracket-link">[&nbsp;<a href="<?php echo $csvExportLink;?>">CSV Export</a>&nbsp;]</span> </span>

		<span class="floatright small"> </span>
	</td>
</tr>
<tr class="row-category">
	<td>ID</td>
	<td>Summary</td>
	<td>Status</td>
	<td>Assigned</td>
	<td>Hours</td>
	<td>Updated</td>
	<td>SWP</td>
</tr>

<tr class="spacer">
	<td colspan="10"></td>
</tr>
<?php 
if($countNumber > 0):
while ($row = mysql_fetch_assoc($resultList)):
$tempStatus = string_display_line( get_enum_element( 'status', $row['status'] ) );
switch ($row['status']){
	case "10":
		$borderColor = "#fcbdbd";
		break;
	case "20":
		$borderColor = "#e3b7eb";
		break;
	case "30":
		$borderColor = "#ffcd85";
		break;
	case "40":
		$borderColor = "#fff494";
		break;
	case "50":
		$borderColor = "#c2dfff";
		break;
	case "80":
		$borderColor = "#d2f5b0";
		break;
	case "90":
		$borderColor = "#c9ccc4";
		break;
	default:
		$borderColor = "";
		break;
}
?>
<tr bgcolor="<?php echo $borderColor;?>" valign="top" border="1">
	<td><a href="/view.php?id=<?php echo $row['id'];?>"><?php printf("[%07s]\n",$row['id']) ?></a></td>
	<td class="left"><?php echo $row['summary']?></td>
	<td class="center"><?php echo $tempStatus;?></td>
	<td class="center"><a href="/view_user_page.php?id=<?php echo $row['handler_id']?>"><?php echo $row['username']?></a></td>
	<td class="center"><?php echo round($row['totalMinutes']/60,1)?></td>
	<td class="center"><?php echo date("Y-m-d", $row['last_updated'])?></td>
	<td class="center"></td>
</tr>
<?php endwhile;?>	
<?php endif;?>	
</tbody>
</table>

<?php

	if ( $t_status_legend_position == STATUS_LEGEND_POSITION_BOTTOM || $t_status_legend_position == STATUS_LEGEND_POSITION_BOTH ) {
		html_status_legend();
	}

	# -- ====================== FILTER FORM ========================= --
	if ( ( $t_filter_position & FILTER_POSITION_BOTTOM ) == FILTER_POSITION_BOTTOM ) {
		filter_draw_selection_area( $f_page_number );
	}
	# -- ====================== end of FILTER FORM ================== --
	
	?>
<script>
function checkAssignedAll(obj){
	if($(obj).attr("checked") == true){
		$(".assigned_user").attr("checked", true);
	}else{
		$(".assigned_user").attr("checked", false);
	}
}

function checkAssignedWorkedAll(obj){
	if($(obj).attr("checked") == true){
		$(".assigned_worked_user").attr("checked", true);
	}else{
		$(".assigned_worked_user").attr("checked", false);
	}
}

function unAssignedAll(obj){
	if($(obj).attr("checked") == false){
		$("#assigned_all").attr("checked", false);
	}
}

function unAssignedWorkedAll(obj){
	if($(obj).attr("checked") == false){
		$("#assigned_worked_all").attr("checked", false);
	}
	
}
function setSearchType(value){
	if(value == "0"){
		$('.mantis_time').attr("disabled", false);
		$('.mantis_id').attr("disabled", true);
	}else{
		$('.mantis_id').attr("disabled", false);
		$('.mantis_time').attr("disabled", true);
	}
}

function hideOtherUser(currentProjectId){
		$('.assigned_user').each(
			function(){
				var tempArray = $(this).attr("it").split("_");
				var checkInProject = jQuery.inArray(currentProjectId, tempArray);
				if(currentProjectId != "0"){
					if(checkInProject == "-1"){
						$(this).parent().hide();
						$(this).attr("checked", false);
					}else{
						$(this).parent().show();
					}
				}else{
					$(this).parent().show();
				}
				
			}
		);
		
		$('.assigned_worked_user').each(
			function(){
				var tempArray = $(this).attr("it").split("_");
				var checkInProject = jQuery.inArray(currentProjectId, tempArray);
				if(currentProjectId != "0"){
					if(checkInProject == "-1"){
						$(this).parent().hide();
						$(this).attr("checked", false);
					}else{
						$(this).parent().show();
					}
				}else{
					$(this).parent().show();
				}
				
			}
		);
}
$(function(){
	setSearchType('<?php echo $searchType;?>');
	var currentProjectId = '<?php echo $currentProjectId?>';
	if(currentProjectId){
		hideOtherUser(currentProjectId);
	}
	
	<?php if($search != "Search"):?>
		$('#assigned_all').click();
	<?php endif;?>
});
</script>
