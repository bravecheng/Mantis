<?php
	$g_hostname = 'localhost';
	$g_db_type = 'mysql';
	$g_database_name = 'giantsto_mantis';
	$g_db_username = 'root';
	$g_db_password = 'root';
    
    # --- Anonymous Access / Signup ---exit
    $g_allow_signup				= OFF;
    $g_allow_anonymous_login	= OFF;
    $g_anonymous_account		= '';

    # --- Email Configuration ---
    $g_phpMailer_method		= PHPMAILER_METHOD_SMTP; # or PHPMAILER_METHOD_SMTP, PHPMAILER_METHOD_SENDMAIL
    $g_smtp_host			= 'mail.expacta.com.cn';			# used with PHPMAILER_METHOD_SMTP
    $g_smtp_username		= 'mantis@expacta.com.cn';					# used with PHPMAILER_METHOD_SMTP
    $g_smtp_password		= 'init123';					# used with PHPMAILER_METHOD_SMTP
    $g_administrator_email  = 'john.mao@expacta.com.cn';
    $g_webmaster_email      = 'john.mao@expacta.com.cn';
    $g_from_name			= 'Mantis Bug Tracker';
    $g_from_email           = 'mantis@expacta.com.cn';	# the "From: " field in emails
    $g_return_path_email    = 'mantis@expacta.com.cn';	# the return address for bounced mail
    $g_email_receive_own	= OFF;
    $g_email_send_using_cronjob = OFF;

    # --- Attachments / File Uploads ---
    $g_allow_file_upload	= ON;
    $g_file_upload_method	= DATABASE; # or DISK
    $g_absolute_path_default_upload_folder = ''; # used with DISK, must contain trailing \ or /.
    $g_max_file_size		= 5000000;	# in bytes
    $g_preview_attachments_inline_max_size = 256 * 1024;
    $g_allowed_files		= '';		# extensions comma separated, e.g. 'php,html,java,exe,pl'
    $g_disallowed_files		= '';		# extensions comma separated

    # --- Branding ---
    $g_window_title			= 'MantisBT';
    $g_logo_image			= 'images/mantis_logo.gif';
    $g_favicon_image		= 'images/favicon.ico';

    # --- Real names ---
    $g_show_realname = OFF;
    $g_show_user_realname_threshold = NOBODY;	# Set to access level (e.g. VIEWER, REPORTER, DEVELOPER, MANAGER, etc)

    # --- Others ---
    $g_default_home_page = 'my_view_page.php';	# Set to name of page to go to after login
