<?php
session_start();
if(!isset($_SESSION['base_path'])) {
    if(include_once('config/config.php')){
        $_SESSION['base_path'] = $config['base_path'];
    }else{
        die('No config file available.');
    }
}

if(! include_once($_SESSION['base_path'] . 'config/config.php')){ die('No config file could not be loaded.'); }
if(! include_once($_SESSION['base_path'] . 'general/util.php')){ die('Core files could not be loaded.'); }

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

    <link rel="stylesheet" href="css/main.css">
    <script src="js/vendor/modernizr-2.6.1.min.js"></script>
</head>
<body>
    <!--[if lt IE 7]>
        <p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
    <![endif]-->
