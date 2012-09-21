<?php
/**
 *------------------------------------------------------------------------------
 * Remove User - Administrative Section
 *------------------------------------------------------------------------------
 *
 */

require_once('../../../includes/header.php');

$db = new db($config);

$query = 'SELECT userID, customerID, authLevel FROM users WHERE userID = :userID';
$bind[':userID'] = intval($_GET['id']);
$user = $db -> fetchRow($query, $bind);

pprint($user);

switch($_SESSION['authLevel']) {
    case 1:
        // Users(authLevel = 1) don't belong here
        gtfo($config);
        break;

    case 2:
        /**
          * Managers(authLevel = 2) must stay within their customer account,
          * can't remove admins, can't edit older managers, and can't remove
          * themselves.
          */
        if($_SESSION['customerID'] != $user['customerID']
            || $user['authLevel'] > 2
            || ($user['authLevel'] == 2 && $user['userID'] < $_SESSION['userID'])
            || $user['userID'] == $_SESSION['userID']) {
            gtfo($config);
        }
        break;

    case 3:
        // Admins(authLevel = 3) can't remove older admins or themselves
        if(($user['authLevel'] == 3 && $user['userID'] < $_SESSION['userID'])
            || $user['userID'] == $_SESSION['userID']) {
            gtfo($config);
        }
        break;
}

$query = 'UPDATE users SET active = 0 WHERE userID = :userID';
$bind[':userID'] = intval($_GET['id']);

if($db -> execute($query, $bind)) {
    header('Location: ../../?a=s'); // a = Alert  s = Success(generic)
}else{
    header('Location: ../../?a=e'); // a = Alert  e = Error(generic)
}

require_once('../../../includes/footer.php');
?>
