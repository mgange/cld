<?php
/**
 *------------------------------------------------------------------------------
 * Dashboard Index File
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/header.php');

systemSwitch($config);
?>
<?
$db = new db($config);

if(!isset($_SESSION['buildingID']) || !isset($_SESSION['SysID'])) {

    // Get the building info for this user's customer account
    $query = 'SELECT * FROM buildings WHERE customerID = :customerID';
    $buildingsBind[':customerID'] = $_SESSION['customerID'];

    $buildings = $db -> fetchAll($query, $buildingsBind);
    pprint($buildings);
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

    $sysConfigs = $db -> fetchAll($query, $systemConfigBind);

    switch(count($sysConfigs)) {
        case 0:
            gtfo($config);
            break;
        case 1:
            $_SESSION['SysID'] = $sysConfigs[0]['SysID'];
            break;
        default:
            header('Location: buildings');
            break;
    }

}else{
    // Double check that the building/system belongs to their customer account
    $buildingResponse = $db -> numRows(
        'SELECT customerID FROM buildings WHERE buildingID = :buildingID',
        array(':buildingID' => intval($_SESSION['buildingID']))
        );
    $sysConfigResponse = $db -> numRows(
        'SELECT buildings.customerID
        FROM buildings LEFT JOIN SystemConfig
        ON buildings.buildingID = SystemConfig.buildingID
        WHERE SystemConfig.SysID = :SysID',
        array(':SysID' => intval($_SESSION['SysID']))
        );
    if(!$buildingResponse || !$sysConfigResponse) {
        gtfo($config);
    }else{
        // Content goes here
?>
      <div id="chart" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
<?php

        // pprint(
        //     $db->fetchRow('SELECT * FROM buildings WHERE buildingID = '.$_SESSION['buildingID'])
        // );
        // pprint(
        //     $db->fetchRow('SELECT * FROM SystemConfig WHERE SysID = '.$_SESSION['SysID'])
        //     );

        $query = 'SELECT SysMap.*, SourceData0.*
        FROM SysMap
        LEFT JOIN SourceData0 ON SysMap.DAMID = SourceData0.DAMID
        WHERE SysMap.SysID = :SysID
        AND SysMap.DAMID = :DAMID';
        $bind[':SysID'] = $_SESSION['SysID'];
        $bind[':DAMID'] = 1;
        // pprint($db->fetchRow($query, $bind));
    }

}

?>
<?php
require_once('../includes/footer.php');
?>
