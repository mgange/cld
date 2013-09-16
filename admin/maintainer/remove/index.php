<?php
/**
 *------------------------------------------------------------------------------
 * Remove a Maintenance Provider
 *------------------------------------------------------------------------------
 */
require_once('../../../includes/pageStart.php');

if($_SESSION['authLevel'] < 3) {
    gtfo($config);
}

$db = new db($config);

if(isset($_GET['id'])) {
    $query = "DELETE FROM MaintainResource WHERE Recnum = :id LIMIT 1";
    $bind = array(':id' => intval($_GET['id']) );

    $resp = $db-> execute($query, $bind);

    if($resp == true) {
        header('Location: ../../?a=s');
    }else{
        header('Location: ../../?a=e');
    }
}else{
    header('Location: ../../?a=e');
}
