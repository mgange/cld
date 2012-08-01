<?php
session_start();
if(! include('config/config.php')) {
    die('No config file could not be loaded.');
}
$_SESSION['base_url'] = $config['base_url'];
echo $_SESSION['base_url'];
require_once('includes/header.php');


require_once('includes/footer.php');
?>