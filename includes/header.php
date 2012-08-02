<?php
session_start();

if(!isset($_SESSION['base_domain']) || !isset($_SESSION['base_dir'])) {
    die( 'need the path' );
}

if(! include_once(__DIR__ . '/../config/config.php')){ die('Config file could not be loaded.'); }
if(! require_once(__DIR__ . '/../general/util.php')){ die('Core files could not be loaded.'); }

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