<?php
require_once('../config/config.php');

session_start();
$_SESSION = array();
session_destroy();

header('Location: ' . $config['base_url']);

?>