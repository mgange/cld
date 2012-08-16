<?php
/**
 *------------------------------------------------------------------------------
 * LogoutFile
 *------------------------------------------------------------------------------
 *
 * The logout file will destroy the current session and return the user to the 
 * sites homepage.
 *
 */
session_start();

$home = $_SESSION['base_dir'];

$_SESSION = null;

session_destroy();

header('Location: /' . $home);

?>