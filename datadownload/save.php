<?php
/**
 * -----------------------------------------------------------------------------
 * Saved a Download Set
 * -----------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');

$db = new db($config);

if(count($_POST)) {
    $query = '
    INSERT INTO  SavedDownloads
        (
            UserID,
            SysID,
            Name,
            Fields
        )
    VALUES
        (
            :UserID,
            :SysID,
            :Name,
            :Fields
        )
    ';

    $bind[':UserID'] = $_SESSION['userID'];
    $bind[':SysID'] = $_SESSION['SysID'];
    $bind[':Name'] = $_POST['Name'];
    $bind[':Fields'] = $_POST['Fields'];

}

if($db->execute($query, $bind)) {
    echo $db->LastInsertID();
}

?>
