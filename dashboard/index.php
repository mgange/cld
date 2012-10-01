<?php
/**
 *------------------------------------------------------------------------------
 * Dashboard Index File
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/header.php');

$db = new db($config);

// Get the building info for this user's customer account
$query = 'SELECT buildingID FROM buildings WHERE customerID = customerID';
$buildingsBind[':customerID'] = $_SESSION['customerID'];

$buildings = $db -> fetchAll($query, $buildingsBind);

/**
 * If they have more than one building send them to a page where they can choose
 * what they want a dashboard for. Otherwise set buildingID as a session
 * variable.
 */
switch(count($buildings)) {
    case 0:
        gtfo($config);
        break;
    case 1:
        $_SESSION['buildingID'] = $buildings[0]['buildingID'];
        break;
    default:
        header('Location: buildings');
        break;
}

/**
 * If they have more than one system send them to a page where they can choose
 * what they want a dashboard for. Otherwise set sysID as a session
 * variable.
 */
$query = 'SELECT sysID FROM SystemConfig WHERE buildingID = :buildingID';
$systemConfigBind[':buildingID'] = $buildings[0]['buildingID'];

$sysConfigs = $db -> fetchAll($query, $systemConfigBind); /* made up results --> */$sysConfigs = array(0 => array('sysID' => 99));

switch(count($sysConfigs)) {
    case 0:
        gtfo($config);
        break;
    case 1:
        $_SESSION['sysID'] = $sysConfigs[0]['sysID'];
        break;
    default:
        header('Location: buildings');
        break;
}

?>
Dashboard<img src="../img/SysIll.png" alt="SysIll" width="628" height="469" />
<?php
require_once('../includes/footer.php');
?>
