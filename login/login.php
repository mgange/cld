<?php
/**
 *------------------------------------------------------------------------------
 * Login File
 *------------------------------------------------------------------------------
 *
 * The login file should receive a username and password POSTed from a signin
 * form. The password will be hashed and the POST values checked against the
 * database in order to get the users information and set in in the $_SESSION
 * array. Successful login will return the user to tthe sites homepage.
 *
 */
session_start();
require_once('../config/config.php');
require_once('../general/util.php');

if(isset($_POST['submit'])){
    /* Bind values to be used in the prepared statement. */
    $bind = array(
        ":username" => $_POST['username'],
        ":password" => hashPassword($config, $_POST['password'])
    );
    /* Execute the query and see if any results come back. */
    $query = "SELECT * FROM users WHERE username = :username AND password = :password";
    $db = new db($config);
    $results = $db -> fetchRow($query, $bind);

    if(count($results) > 1) {
        $_SESSION['userID']     = $results['userID'];
        $_SESSION['customerID'] = $results['customerID'];
        $_SESSION['username']   = $results['username'];
        $_SESSION['authLevel']  = $results['authLevel'];
        $_SESSION['email']      = $results['email'];
        $_SESSION['firstName']  = $results['firstName'];
        $_SESSION['lastName']   = $results['lastName'];
        $_SESSION['last_activity'] = time();
        $_SESSION['last_update']   = time();

        header('Location: ../');
    }else{
        header('Location: ../?a=nl'); // a = Alert  nl = No Login
    }



}

?>
