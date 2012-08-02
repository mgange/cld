<?php  if(count(get_included_files()) ==1) exit("Direct access not permitted.");
/**
 * Configuration file for environmental variables.
 *
 */

/*
 *------------------------------------------------------------------------------
 * Base Site URL
 *------------------------------------------------------------------------------
 *
 * URL to this site. Typically this will be your base URL,
 * WITH a trailing slash:
 *
 * 'base_domain' This is the fully qualified domain name where the site is 
 *     hosted. Everything from the http to the .com  e.g. http://example.com/
 *
 * 'base_dir' If the site is in a subdirectory specify it here.  e.g. 'app/'
 *     This would also include a trailing slash unless it's empty.
 *
 */
$config['base_domain'] = '';
$config['base_dir']    = '';

/*
 *------------------------------------------------------------------------------
 * Database Credentials
 *------------------------------------------------------------------------------
 *
 * These values are needed for any data access.
 *
 * 'dbHost'     = Hostname where we'll look for the database server
 * 'dbName'     = Name of the database being used
 * 'dbUser'  = Database username
 * 'dbPass'   = Database pasword
 */
$config['dbHost'] = '';
$config['dbName'] = '';
$config['dbUser'] = '';
$config['dbPass'] = '';

/*
 *------------------------------------------------------------------------------
 * Site Info
 *------------------------------------------------------------------------------
 *
 * General info about this site used for presentation purposes.
 *
 * 'site_name'     = Name to appear in title tags
 */
$config['site_name'] = '';

/*
 *------------------------------------------------------------------------------
 * Admin Contact Info
 *------------------------------------------------------------------------------
 *
 * These values are needed for any data access.
 *
 * 'admin_name'     = website administrator's name
 * 'admin_email'     = website administrator's email address
 */
$config['admin_name'] = '';
$config['admin_email'] = '';

/*
 *------------------------------------------------------------------------------
 * Default Time Zone
 *------------------------------------------------------------------------------
 *
 * This determines the timezone used in any data calculations done by PHP.
 * Supported Timezones: http://php.net/manual/en/timezones.php
 *
 */
$config['time_zone'] = '';

/*
 *------------------------------------------------------------------------------
 * Google Analytics
 *------------------------------------------------------------------------------
 *
 * Enter the tracking code for this site in your Google Analytics account. If 
 * left blank the analytics script will be left out.
 *
 * e.g. UA-XXXXX-X
 */
$config['GAcode'] = '';

/*
 *------------------------------------------------------------------------------
 * Error Logging Threshold
 *------------------------------------------------------------------------------
 *
 * If you have enabled error logging, you can set an error threshold to
 * determine what gets logged. Threshold options are:
 * You can enable error logging by setting a threshold over zero. The
 * threshold determines what gets logged. Threshold options are:
 *
 * 0 = Disables logging, Error logging TURNED OFF
 * 1 = Error Messages (including PHP errors)
 * 2 = Debug Messages
 * 3 = Informational Messages
 * 4 = All Messages
 *
 * For a live site you'll usually only enable Errors (1) to be logged otherwise
 * your log files will fill up very fast.
 *
*/
$config['log_threshold'] = 0;

/*
 *------------------------------------------------------------------------------
 * Error Logging Directory Path
 *------------------------------------------------------------------------------
 *
 * Leave this BLANK unless you would like to set something other than the default
 * application/logs/ folder. Use a full server path with trailing slash.
 *
 */
$config['log_path'] = '';

/*
 *--------------------------------------------------------------------------
 * Date Format for Logs
 *--------------------------------------------------------------------------
 *
 * Each item that is logged has an associated date. You can use PHP date
 * codes to set your own date formatting
 *
 */
$config['log_date_format'] = 'Y-m-d H:i:s';

/*
 *------------------------------------------------------------------------------
 * Session Variables
 *------------------------------------------------------------------------------
 *
 * 'sess_expiration'     = the number of SECONDS you want the session to last.
 *   by default sessions last 7200 seconds (two hours).  Set to zero for no expiration.
 * 'sess_time_to_update'   = how many seconds between refreshing Session Information
 *
 */
$config['sess_expiration']     = 7200;
$config['sess_time_to_update']  = 300;