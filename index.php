<?php
/**
 *------------------------------------------------------------------------------
 * Site Index File
 *------------------------------------------------------------------------------
 *
 * This is the index file at the site's root. It basically just imports the 
 * appropriate homepage based on wether or not a userID is set in the $SESSION
 * array and sets some session values based on the $config array. The values 
 * needed to set up the sites path are set here because on this page you know 
 * where you are relative to the config file without any other code being 
 * written. This way we can avoid things like importing 
 * '../../../config/config.php' all over the site.
 *
 */
session_start();
if(! require_once('config/config.php')) {
    die('Config file could not be loaded by index.');
}
$_SESSION['base_domain'] = $config['base_domain'];
$_SESSION['base_dir'] = $config['base_dir'];

require_once('includes/header.php');

if(! isset($_SESSION['userID'])) {
    include_once('includes/noLoginHome.php');
}else{
    include_once('includes/loginHome.php');
}

require_once('includes/footer.php');
?>