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
        $crumbs = arrayRemoveEmpty(
            explode('/',
                preg_replace('/\?.*/','',
                    preg_replace('/' . preg_replace('/\//', '\/', $config['base_dir']) . '/','',$_SERVER['REQUEST_URI']))
                )
        );
        $crumbs = arrayRemoveEmpty($crumbs);
        if(count($crumbs) > 0) {
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
    }
    if(isset($config['site_name']) && $config['site_name'] !== '') {
        echo $config['site_name'];
    }
    ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>css/main.css">
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




<?php
include_once(__DIR__ . '/../includes/nav.php');
?>

        </div>
      </div>
    </div>

<?php
/**
 * This will display the path to the current page based on the url. The site's
 * base_dir will be ignored and if  0 or 1 breadcrumb links are found nothing
 * will be diplayed at all.
 * It is controlled in the config file at $config['breadcrumbs']
 */
if($config['breadcrumbs']) {

    $crumbs = arrayRemoveEmpty(
        explode('/',
            preg_replace('/\?.*/','',
                preg_replace('/' . preg_replace('/\//', '\/', $config['base_dir']) . '/','',$_SERVER['REQUEST_URI']))
            )
        );
    if(count($crumbs) >= $config['breadcrumbThreshold']) {
?>
    <div class="container">
        <ul class="breadcrumb">
<?
        $i = 1;
        foreach($crumbs as $crumb) {
            if($i != count($crumbs)) {
                $path = $config['base_domain'] . $config['base_dir'];
                foreach(array_slice($crumbs, 0, $i) as $peice) {
                    $path .= $peice . '/';
                }

?>
            <li><a href="<?php echo $path; ?>"><?php echo ucfirst($crumb); ?></a> <span class="divider">/</span></li>
<?php
            }else{
?>
            <li class="active"><?php echo ucfirst($crumb); ?></li>
<?php
            }
            $i++;
        }
?>
        </ul>
    </div>
<?php
    }
}
?>

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

        case 'e': // e = Error
?>          <div class="alert alert-error span8 offset2">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Stop Everything!</strong> Something has gone wrong. You
                had better go check that out and maybe try it again.
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

        case 'une': // une = Username Error
?>          <div class="alert alert-error span8 offset2">
                <button class="close" data-dismiss="alert">&times;</button>
                <strong>Whoa!</strong> That username is already taken! You'll
                have to pick another one
            </div>
<?php        break;

        case 'pm': // pm = Password Match
?>          <div class="alert alert-error span8 offset2">
                <button class="close" data-dismiss="alert">&times;</button>
                <strong>Whoa!</strong> Both passwords have to match.
                Otherwise who knows what might happen!!
            </div>
<?php        break;

        case 'pww': // pww = Password Warning
?>          <div class="alert span8 offset2">
                <button class="close" data-dismiss="alert">&times;</button>
                <strong>Warning!</strong> If this works I'm gonna log you out.
                You better be ready for it!
            </div>
<?php        break;

        case 'ef': // ef = Empty Fields
?>          <div class="alert alert-error span8 offset2">
                <button class="close" data-dismiss="alert">&times;</button>
                <strong>Whoa!</strong> You have to put something in there!
            </div>
<?php        break;

        case 'nl': // ef = No Login
?>          <div class="alert alert-error span8 offset2">
                <button class="close" data-dismiss="alert">&times;</button>
                <strong>Whoa!</strong> You've entered the wrong username or
                password.
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
?>
        </div>
