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

/**
 * The base domain and directory are needed to build links to resources like
 * javascript and stylesheets. If they're not set then the user hasn't been to
 * the homepage(and therefore hasn't been to a login page). Since you can't
 * reliably know the homepage without these values we'll just dump them  back to
 * the domain root.
 */
if(!isset($_SESSION['base_domain']) || !isset($_SESSION['base_dir'])) {
    header("Location: /");
}

if(! include_once(__DIR__ . '/../config/config.php')){ die('Config file could not be loaded.'); }
if(! require_once(__DIR__ . '/../general/util.php')){ die('Core files could not be loaded.'); }

if(!isset($_SESSION['userID']) && $_SERVER['SCRIPT_NAME'] !== '/' . $config['base_dir'] . 'index.php'){
    header('Location: ' . $config['base_domain'] . $config['base_dir']);
}

if(isset($_SESSION['last_activity'])) {
    if(time() - $_SESSION['last_activity'] > $config['sess_expiration']) {
        header('Location: '
            . $config['base_domain']
            . $config['base_dir']
            . 'login/logout.php');
    }else{
        $_SESSION['last_activity'] = time();
    }
}
if(isset($_SESSION['last_update'])) {
    if(time()-$_SESSION['last_update'] > $config['sess_time_to_update']) {
        require_once(__DIR__ . '/../includes/sessionUpdate.php');
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
    /**
     * This will display the reversed path to the current page based on the url.
     * It is controlled in the config file at $config['path_in_title']
     */
    if($config['path_in_title'] > 0) {
        $crumbs = explode("/",preg_replace('/\?.*$/', '', $_SERVER["REQUEST_URI"]));
        array_shift($crumbs);
        array_pop($crumbs);
        if($config['path_in_title'] == 1) {
            echo ucfirst(end($crumbs)) . ' | ';
        }else{
            foreach(array_reverse($crumbs) as $crumb){
                if($crumb != '') {
                   echo ucfirst(str_replace(array(".php","_"),array(""," "),$crumb) . ' ') . ' | ';
                }
            }
        }
    }
    if(isset($config['site_name']) && $config['site_name'] !== '') {
        echo $config['site_name'];
    }
    ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>css/main.css">
    <link rel="stylesheet" href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>css/responsive.css">
    <script src="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>js/vendor/modernizr-2.6.1.min.js"></script>
</head>
<body>
    <!--[if lt IE 7]>
        <p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
    <![endif]-->

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="<?php echo $config['base_domain'] . $config['base_dir']; ?>"><?php
if(isset($config['site_name']) && $config['site_name'] !== '') {
    echo $config['site_name'];
}
    ?></a>
            <button type="button" class="btn btn-navbar pull-right" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
<?php
if(! isset($_SESSION['userID'])){
    include_once(__DIR__ . '/../includes/signIn.php');
}else{
    include_once(__DIR__ . '/../includes/nav.php');
}
?>

        </div>
      </div>
    </div>

    <div class="container page-content">
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
