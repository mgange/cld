<?php
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