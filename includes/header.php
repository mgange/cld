<?php
/**
 *------------------------------------------------------------------------------
 * Global Header File
 *------------------------------------------------------------------------------
 *
 * This file should be included at the beginning of every page that is displayed
 * to the user. It will include the site-wide configuration and the utilities
 * file form the 'general' directory. Because the path to the included assets is
 * built from variables in the SESSION array visitors must arrive at the sites
 * homepage or they will get a 'need the path' error.
 *
 * This file also sets the <head> markup, the global navbar (although a signin 
 * form of nav links are imported from includes/signIn.php or includes/nav.php),
 * and alerts based on the $_GET['a'] array value.
 *
 * PHP version 5.3.0
 *
 */
session_start();

if(!isset($_SESSION['base_domain']) || !isset($_SESSION['base_dir'])) {
    die( 'need the path' );
}

if(! include_once(__DIR__ . '/../config/config.php')){ die('Config file could not be loaded.'); }
if(! require_once(__DIR__ . '/../general/util.php')){ die('Core files could not be loaded.'); }

if(!isset($_SESSION['userID']) && $_SERVER['SCRIPT_NAME'] !== '/' . $config['base_dir'] . 'index.php'){
    header('Location: ' . $config['base_domain'] . $config['base_dir']);
}

if(isset($_SESSION['last_activity'])) {
    if(time() - $_SESSION['last_activity'] > $config['sess_expiration']) {
        header('Location: ' . $config['base_domain'] . $config['base_dir'] . 'login/logout.php');
    }
    if(time()-$_SESSION['last_activity'] > $config['sess_time_to_update']) {
        $_SESSION['last_activity'] = time();
    }
}
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php
     if(isset($config['site_name']) && $config['site_name'] !== '') {
        echo $config['site_name'];
    }
    ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>css/main.css">
    <link rel="stylesheet" href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>css/bootstrap.css">
    <script src="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>js/vendor/modernizr-2.6.1.min.js"></script>
</head>
<body>
    <!--[if lt IE 7]>
        <p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
    <![endif]-->

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
<?php
if(! isset($_SESSION['userID'])){
    include_once(__DIR__ . '/../includes/signIn.php');
}else{
    include_once(__DIR__ . '/../includes/nav.php');
}
?>
          <a class="brand" href="<?php echo $config['base_domain'] . $config['base_dir']; ?>"><?php
if(isset($config['site_name']) && $config['site_name'] !== '') {
    echo $config['site_name'];
}
    ?></a>
        </div>
      </div>
    </div>

    <div class="container">
        <div class="row alerts">
<?php
/**
 * This switch statement will display alerts immediately below the navbar based
 * on the value of variable 'a' passed in the URL. By default no message is 
 * displayed just as if the variable didn't exist.
 */
if(isset($_GET['a'])) {
    switch($_GET['a']) {
        case 's': // s = Success
?>          <div class="alert alert-success span8 offset2">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Well done!</strong> You successfully updated your profile information.
            </div>
<?php        break;

        case 'pf': // pf = Password Failure
?>          <div class="alert alert-error span8 offset2">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Uh oh!</strong> Something went wrong while resetting your password.
                You'd better try that again.
            </div>
<?php        break;

        case 'pe': // pf = Profile Error
?>          <div class="alert alert-error span8 offset2">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Uh oh!</strong> Something went wrong while updating your profile information.
                You should probably try that again.
            </div>
<?php        break;

        case 'pm': // pm = Password Match
?>          <div class="alert alert-error span8 offset2">
                <button class="close" data-dismiss="alert">&times;</button>
                <strong>Whoa!</strong> Both passwords have to match.
                Otherwise who knows what might happen!!
            </div>
<?php        break;

        case 'ef': // ef = Empty Fields
?>          <div class="alert alert-error span8 offset2">
                <button class="close" data-dismiss="alert">&times;</button>
                <strong>Whoa!</strong> You have to put something in there!
            </div>
<?php        break;

        case 'ua': // ua = Unauthorized Access
?>          <div class="alert alert-error span8 offset2">
                <button class="close" data-dismiss="alert">&times;</button>
                <strong>Whoa!</strong> You tried to access a restricted area.
                You don't belong in there!
            </div>
<?php        break;

        default:
            break;
    }
}
/**
 * This switch statement will display alerts immediately below the navbar based
 * on the value of variable 'action' variable passed in the URL. By default no 
 * message is displayed just as if the variable didn't exist.
 */
if(isset($_GET['action'])) {
    switch($_GET['action']) {
        case 'password': // Always show this alert on password reset pages
?>          <div class="alert span8 offset2">
                <button class="close" data-dismiss="alert">&times;</button>
                <strong>Warning!</strong> If this works I'm gonna log you out.
                You better be ready for it!
            </div>
<?php       break;
        default:
            break;
    }
}
?>
        </div>