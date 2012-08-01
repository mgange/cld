<?php
if(! include('config/config.php')) {
    die('No config file could not be loaded.');
}
$_SESSION['base_path'] = $config['base_path'];

require_once($_SESSION['bas_path'] . 'includes/header.php');


require_once($_SESSION['bas_path'] . 'includes/footer.php');
?>