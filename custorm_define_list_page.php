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
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'compress_api.php' );
	require_once( 'filter_api.php' );
	require_once( 'last_visited_api.php' );


	access_ensure_global_level( config_get( 'manage_user_threshold' ) );


	$f_page_number		= gpc_get_int( 'page_number', 1 );

	$t_per_page = null;
	$t_bug_count = null;
	$t_page_count = null;

	$rows = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, null, null, null, true );
	if ( $rows === false ) {
		print_header_redirect( 'view_all_set.php?type=0' );
	}

	$t_bugslist = Array();
	$t_users_handlers = Array();
	$t_project_ids  = Array();
	$t_row_count = count( $rows );
	for($i=0; $i < $t_row_count; $i++) {
		array_push($t_bugslist, $rows[$i]->id );
		$t_users_handlers[] = $rows[$i]->handler_id;
		$t_project_ids[] = $rows[$i]->project_id;
	}
	user_cache_array_rows( array_unique( $t_users_handlers ) );
	project_cache_array_rows( array_unique( $t_project_ids ) );
	
	gpc_set_cookie( config_get( 'bug_list_cookie' ), implode( ',', $t_bugslist ) );

	compress_enable();

	# don't index view issues pages
	html_robots_noindex();

	html_page_top1( lang_get( 'view_bugs_link' ) );

	if ( current_user_get_pref( 'refresh_delay' ) > 0 ) {
		html_meta_redirect( 'view_all_bug_page.php?page_number='.$f_page_number, current_user_get_pref( 'refresh_delay' )*60 );
	}


	//THIS FUNCTION IS JUST FOR CUSTORM LIST
	function getRangeYear(){
		$years = array();
		$from = 2000;
		$to = date("Y");
		for ($i=$from; $i<=$to; $i++) {
			$years[] = $i; 
		}
		rsort($years);
		return $years;
	}
	$rangeYears = getRangeYear();
	$rangeMonths = array("1"=>"January", "2"=>"February", "3"=>"March", "4"=>"April", "5"=>"May", "6"=>"June", "7"=>"July", "8"=>"August", "9"=>"September", "10"=>"October", "11"=>"November", "12"=>"December");
	function getRangeDay(){
		$days = array();
		$from = 1;
		$to = 31;
		for ($i=$from; $i<=$to; $i++) {
			$days[] = $i; 
		}
		return $days;
	}
	
	function getProjectList(){
		$projectList = array();
		$sql = "SELECT * FROM mantis_project_table WHERE enabled = 1 ORDER BY name ASC";
		$resultProject = mysql_query($sql);
		$projectList[] = "All Projects";
		while ($row = mysql_fetch_assoc($resultProject)) {
			$projectList[$row['id']] = $row['name'];
		}
		return $projectList;
	}
	$projectList = getProjectList();
	$rangeDays = getRangeDay();
	
	$defaultYearStart = $_REQUEST['start_year'] ? $_REQUEST['start_year'] : date("Y");
	$defaultMonthStart = $_REQUEST['start_month'] ? $_REQUEST['start_month'] : date("m");
	$defaultDayStart = $_REQUEST['start_day'] ? $_REQUEST['start_day'] : date("d");
	
	$defaultYearEnd = $_REQUEST['end_year'] ? $_REQUEST['end_year'] : date("Y");
	$defaultMonthEnd = $_REQUEST['end_month'] ? $_REQUEST['end_month'] : date("m");
	$defaultDayEnd = $_REQUEST['end_day'] ? $_REQUEST['end_day'] : date("d");
	
	$reporterIds = $_REQUEST['reporter_id'] ? $_REQUEST['reporter_id'] : array() ;
	$reporterWorkedIds = $_REQUEST['reporter_worked_id'] ? $_REQUEST['reporter_worked_id'] : array() ;
	$searchType = $_REQUEST['search_type'] ? $_REQUEST['search_type'] : 0 ;
	
	$fromId = $_REQUEST['mantis_fromId'] ? $_REQUEST['mantis_fromId'] : "";
	$toId = $_REQUEST['mantis_toId'] ? $_REQUEST['mantis_toId'] : "";
	
	$search = $_REQUEST['search'];
	$countNumber = 0;
	$mantisStatus = $_REQUEST['mantis_status'] ? $_REQUEST['mantis_status'] : array();

	//$currentProjectId = $_REQUEST['current_project'] ? $_REQUEST['current_project'] : join( ';', helper_get_current_project_trace());
	$currentProjectId = $_REQUEST['current_project'] ? $_REQUEST['current_project'] : "";
	if($search == "Search"){
		$andSearch = "";
		if($currentProjectId){
			$andSearch .= " AND project_id = '$currentProjectId'";
		}
		
		if($reporterIds){
			$reporterIdString = implode(",", $reporterIds);
			$andSearch .= " AND handler_id IN ($reporterIdString)";
		}
		
		if($reporterWorkedIds){
			$reporterWorkedIdString = implode(",", $reporterWorkedIds);
			$andSearch .= " AND mantis_bugnote_table.reporter_id IN ($reporterWorkedIdString)";
		}
		
		if ($searchType){
			$fromId = (int)$fromId;
			$toId = (int)$toId;
			if($fromId){
				$andSearch .= " AND mantis_bug_table.id >= '$fromId' ";
			}
			if($toId){
				$andSearch .= " AND mantis_bug_table.id <= '$toId' ";
			}
		}else{
			
			$fromTime = strtotime("$defaultYearStart-$defaultMonthStart-$defaultDayStart");
			$toTime = strtotime("$defaultYearEnd-$defaultMonthEnd-$defaultDayEnd 23:59:59");
			$andSearch .= " AND mantis_bug_table.last_updated >= '$fromTime' AND mantis_bug_table.last_updated <= '$toTime' ";
		}
		
		if($mantisStatus){
			$tempMantisStatus = array_diff(array(80, 90), $mantisStatus);
			$mantisStatuString = implode(",", $tempMantisStatus);
			if($mantisStatuString){
				$andSearch .= " AND status NOT IN ($mantisStatuString)";
			}
		}else{
			$andSearch .= " AND status NOT IN (80, 90)";
		}
		$sql = "SELECT mantis_bug_table.*, mantis_user_table.username, sum(time_tracking) as totalMinutes  FROM mantis_bug_table 
		LEFT JOIN mantis_user_table ON mantis_bug_table.handler_id = mantis_user_table.id 
		LEFT JOIN `mantis_bugnote_table` on mantis_bug_table.id = mantis_bugnote_table.bug_id
		WHERE 1 $andSearch group by mantis_bug_table.id ORDER BY ID DESC";
		$resultList = mysql_query($sql);	
		$countNumber = mysql_num_rows($resultList);	
		//echo $sql;
	}

	$csvExportLink =  $_SERVER['PHP_SELF'] ."?".$_SERVER['QUERY_STRING'] . "&method=save_as_csv";

	$method = $_REQUEST['method'];
	if($method == "save_as_csv"){
		$tempProjectName = $projectList[$currentProjectId];
		$t_filename = date("Ymd")."_".$tempProjectName.".csv";
		$csvHandle = fopen($t_filename, "w+");
		
		if($countNumber > 0){
			$menu = array("ID", "Summary", "Status", "Assigned", "Hours", "Updated", "SWP");	
			fputcsv($csvHandle, $menu);
			while ($row = mysql_fetch_assoc($resultList)) {
				$tempStatus = string_display_line( get_enum_element( 'status', $row['status'] ) );
				$tempData = array($row['id'], $row['summary'], $tempStatus, $row['username'], round($row['totalMinutes']/60,1), date("Y-m-d", $row['last_updated']), "");
				fputcsv($csvHandle, $tempData);
			}
		}else{
			$csvContent = "There is no recode.";
			fputcsv($csvHandle, array($csvContent));
		}
		fclose($csvHandle);
		
		# Make sure that IE can download the attachments under https.
		@ob_clean();
		$csvContent = file_get_contents($t_filename);
		header( "Cache-Control: public" );
		header( "Pragma: public" );
		header( "Content-type: text/csv" ) ;
		header( "Content-Disposition: attchment; filename={$t_filename}" ) ;
		header( "Content-Length: ". strlen( $csvContent ) );
		echo $csvContent;
		@unlink($t_filename);
		exit();
	}

	html_page_top2();

	print_recently_visited();

	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'view_custorm_list_inc.php' );

	html_page_bottom();
