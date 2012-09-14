<?php
/**
 *------------------------------------------------------------------------------
 * Update Session Variables
 *------------------------------------------------------------------------------
 * If session variables haven't been updated from the database recently enough
 * this file will be included. The frequency of the updates is set in
 * config/config.php by the $config['sess_time_to_update'] value.
 */

$query = "SELECT customerID, username, authLevel, email, firstName, lastName FROM users WHERE userID = :userID";
$bind[':userID'] = intval($_SESSION['userID']);
$db = new db($config);
$results = $db -> fetchRow($query, $bind);
if($results) {
    $_SESSION['customerID'] = $results['customerID'];
    $_SESSION['username']   = $results['username'];
    $_SESSION['authLevel']  = $results['authLevel'];
    $_SESSION['email']      = $results['email'];
    $_SESSION['firstName']  = $results['firstName'];
    $_SESSION['lastName']   = $results['lastName'];

    $_SESSION['last_update'] = time();
}
$bind = null;

?>
