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
	function getEmailTemplateContent($arrMainData, $template, $addtionalData=array()){
		ob_start();
		require ("emailtemplate/".$template);
		$contentHtml = ob_get_contents();
		ob_clean();
		$contentHtml = nl2br($contentHtml);
		return $contentHtml;
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
	$oTType = array(""=>"Select One", "1"=>"Working Day", "2"=>"Weekend", "3"=>"Legal Holiday");
	
	
	$defaultYearStart = $_REQUEST['start_year'] ? $_REQUEST['start_year'] : date("Y");
	$defaultMonthStart = $_REQUEST['start_month'] ? $_REQUEST['start_month'] : date("m");
	$defaultDayStart = $_REQUEST['start_day'] ? $_REQUEST['start_day'] : date("d");
	
	if($_POST['save_ot'] == "Save OT"){
		//INSERT INTO OT TABLE
		$employees = $_POST['employee_ids'];
		$otDates = $_POST['ot_dates'];
		$otTimes = $_POST['ot_times'];
		$otTypes = $_POST['ot_types'];
		$ot_subject = $_POST['ot_subject'];
		$sqlInsertOT = "INSERT INTO `mantis_ot_application` (`id` ,`user_id` ,`ot_subject` ,`ot_date` ,`ot_time` ,`ot_type` ,`created_at` ,`updated_at`) VALUES ";
		foreach ($employees as $key=>$employeeId){
			$ot_date = $otDates[$key];
			list($hours, $minute) = explode(":", $otTimes[$key]);
			$ot_time = $hours*60 + $minute;
			$ot_type = $otTypes[$key];
			$sqlInsertOT .= "(NULL , '$employeeId', '$ot_subject' , '$ot_date' , '$ot_time', '$ot_type', NOW() , NOW()),";
		}
		$sqlInsertOT = rtrim($sqlInsertOT, ",");
		mysql_query($sqlInsertOT);
		
		//send email
		$userIdString = implode(",", $employees);
		$sql = "SELECT id,username,email FROM mantis_user_table WHERE id IN ($userIdString)";
		$resultUser = mysql_query($sql);
		$userEmails = array();
		$userNames = array();
		while ($rowUser = mysql_fetch_assoc($resultUser)) {
			$userEmails[$rowUser['id']] = $rowUser['email'];
			$userNames[$rowUser['id']] = $rowUser['username']; 
		}
		
		
		//send email to HR
		$arrMainData = array();
		foreach ($employees as $key=>$employeeId){
			$arrMainData[] = array("employeeId"=>$employeeId, "username"=>$userNames[$employeeId], "email"=>$userEmails[$employeeId], "date"=>$otDates[$key], "time"=>$otTimes[$key], "type"=>$oTType[$otTypes[$key]]);
		}
		
		$emailBody = getEmailTemplateContent($arrMainData, "hr_ot_list.php");
		//$userEmails[] = 'windy.wang1@expacta.com.cn';
		$t_email_data = new EmailData;
		$t_email_data->email = 'elsie.gao@expacta.com.cn';
		//$t_email_data->email = 'windy.wang2@expacta.com.cn';
		$t_email_data->cc = array("gavin.cao@expacta.com.cn");
		//$t_email_data->cc = array("windy.wang@expacta.com.cn");
		$t_email_data->subject = $ot_subject ? $ot_subject : "Expacta OT";
		$t_email_data->body = $emailBody;
		$t_email_data->isHtml = true;
		$t_email_data->metadata['priority'] = config_get( 'mail_priority' );
		$t_email_data->metadata['charset'] = 'utf-8';
		$result = email_send( $t_email_data );
	
		//send email to user
		$lastEmployeeId = 0;
		foreach ($employees as $key=>$employeeId){
			if($employeeId == $lastEmployeeId) continue;
			$addtionalData = array("employeeId"=>$employeeId, 'username'=>$userNames[$employeeId]);
			$emailBody = getEmailTemplateContent($arrMainData, "user_ot_list.php", $addtionalData);
			//$userEmails[] = 'windy.wang1@expacta.com.cn';
			$t_email_data = new EmailData;
			$t_email_data->email = $userEmails[$employeeId];
			//$t_email_data->email = 'windy.wang2@expacta.com.cn';
			$t_email_data->subject = $ot_subject ? $ot_subject : "Expacta OT";
			$t_email_data->body = $emailBody;
			$t_email_data->isHtml = true;
			$t_email_data->metadata['priority'] = config_get( 'mail_priority' );
			$t_email_data->metadata['charset'] = 'utf-8';
			$result = email_send( $t_email_data );
			$lastEmployeeId = $employeeId;
		}
		
		
		if($result){
			header("Location: /ot_application_page.php?result=1");
		}else{
			header("Location: /ot_application_page.php?result=2");
		}
		/*
		$mail = new PHPMailer(true);
		$mail->Subject = $ot_subject ? $ot_subject : "Expacta OT";
		$mail->Body = $mailBody;
		$mail->AddAddress("elsie.gao@expacta.com.cn", "Elsie Gao");
		foreach ($userEmails as $email=>$recipient){
			$mail->AddCC($email, $recipient);
		}*/
				
	}

	
	html_page_top2();
	
	

	print_recently_visited();

	include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'view_ot_application_inc.php' );

	html_page_bottom();
