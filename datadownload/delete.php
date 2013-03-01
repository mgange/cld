<?php
/**
 * -----------------------------------------------------------------------------
 * Delete a Saved Download Set
 * -----------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');

$db = new db($config);

/**
 * Look up the saved search we're handling first to be sure it belongs to the
 * current user.
 */
$query = '
    SELECT *
    FROM SavedDownloads
    WHERE UserID = :UserID
      AND SysID = :SysID
      AND Recnum = :id';

$bind[':UserID'] = $_SESSION['userID'];
$bind[':SysID'] = $_SESSION['SysID'];
$bind[':id'] = intval($_GET['id']);

$result = $db->numRows($query, $bind);

if($result) {
    $delQuery = 'DELETE FROM SavedDownloads WHERE Recnum = :id LIMIT 1';
    $delBind[':id'] = $bind[':id'];

    $db->execute($delQuery, $delBind);
}


?>
