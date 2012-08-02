<?php
session_start();

$home = $_SESSION['base_dir'];

$_SESSION = null;

session_destroy();

header('Location: /' . $home);

?>