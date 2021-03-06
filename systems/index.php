<?php
/**
 *------------------------------------------------------------------------------
 * Building Selector - Dashboard
 *------------------------------------------------------------------------------
 * If a user has access to more than one building/system they can be sent here
 * to decide which one they're trying to view/manage. Their choice is set in
 * $_SESSION and they'll be redirected someplace else.
 */

require_once('../includes/pageStart.php');

/**
 * Handle links coming from this same page. Set the buildingID and SysID as
 * session variables. If an 'intent' is passed then redirect to it. Intent
 * should be the full path to the page you want. If no intent is sent then the
 * redirect defaults to the dashboard.
 */
if(isset($_GET['buildingID']) && isset($_GET['SysID'])) {
    $_SESSION['buildingID'] = intval($_GET['buildingID']);
    $_SESSION['SysID'] = intval($_GET['SysID']);
    if(isset($_GET['intent'])) {
        header('Location: ../' . $_GET['intent']);
    }else{
        header('Location: ../');
    }
}

require_once('../includes/header.php');

$db = new db($config);

$query = 'SELECT distinct(buildings.buildingID), address1, address2, city, state, zip FROM buildings join Alarm_Permissions on buildings.buildingID =
    Alarm_Permissions.buildingID ';

if($_SESSION['authLevel'] == 3) {
    $query .= 'WHERE 1 order by buildings.buildingID';
}else{
    $query .= 'WHERE customerID = ' . $_SESSION['customerID'].' or Alarm_Permissions.UserID = ' . $_SESSION['userID'];
}


$buildings = $db -> fetchAll($query);

?>

        <div class="row">
            <h1 class="span8 offset2">Systems</h1>
        </div>

<?php
// Get the systems associated with each building
$numSystems = 0;
foreach($buildings as $building) {
    $query = 'SELECT SysID, DAMID, SysName FROM SystemConfig WHERE Active=1 and buildingID = ' . $building['buildingID'];
    //If there's a system for this building ...
    if($db -> numRows($query) > 0) {
        $sysConfigs = $db -> fetchAll($query);
?>
        <div class="well clearfix">
            <div class="row">
                <h4 class="span5 offset1">
                    <?php echo $building['address1']; ?>
                    <br>
                    <?php echo $building['address2']; ?>
                </h4>
                <h5 class="span5">
                    <?php echo $building['city'];
                    if($building['state'] != '') {
                         echo ', ' . $building['state'];
                    }
                    ?>
                </h5>
            </div>
<?php
    foreach($sysConfigs as $sysConfig) {
        $redAlert    = $db -> numRows("SELECT * FROM Alarms_Active WHERE Alarm_Level = 1 AND WebEnabled = 1 AND SysID = " . $sysConfig['SysID']);
        $yellowAlert = $db -> numRows("SELECT * FROM Alarms_Active WHERE Alarm_Level = 2 AND WebEnabled = 1 AND SysID = " . $sysConfig['SysID']);
?>

            <div class="row clearfix">
                <div class="span2 offset1">
                    <h4 class="system-name"><?php echo $sysConfig['SysName']; ?></h4>

                </div>

                <a href="./?buildingID=<?php
                                   echo $building['buildingID'];
                                   ?>&SysID=<?php
                                    echo $sysConfig['SysID'];
                                    ?>&intent=information" class="systems-icon systems-info span2">
                    <img src="<?php echo $config['base_domain'] . $config['base_dir']?>img/buildingInfo.png" alt="Information">
                    <br>
                    Information
                </a>

                <a href="./?buildingID=<?php
                                   echo $building['buildingID'];
                                   ?>&SysID=<?php
                                    echo $sysConfig['SysID'];
                                    ?>&intent=alarms" class="systems-icon systems-alarms span2">
                    <img src="<?php echo $config['base_domain'] . $config['base_dir'];
                        if($redAlert){
                            echo 'img/alarmRed-60x60.png';
                        }else{
                            if($yellowAlert){
                                echo 'img/alarmYellow-60x60.png';
                            }else{
                                echo 'img/alarmGreen-60x60.png';
                            }
                        }
                    ?>" alt="Information">
                    <br>
                        Alarms
                    </a>

                <a href="./?buildingID=<?php
                                   echo $building['buildingID'];
                                   ?>&SysID=<?php
                                    echo $sysConfig['SysID'];
                                    ?>&intent=status" class="systems-icon systems-status span2">
                    <img src="<?php echo $config['base_domain'] . $config['base_dir']?>img/status.png" alt="Information">
                    <br>
                    Status
                </a>

                <a href="./?buildingID=<?php
                                   echo $building['buildingID'];
                                   ?>&SysID=<?php
                                    echo $sysConfig['SysID'];
                                    ?>&intent=performance" class="systems-icon systems-performance span2">
                    <img src="<?php echo $config['base_domain'] . $config['base_dir']?>img/performance.png" alt="Information">
                    <br>
                    Performance
                </a>
            </div>
            <div class="row">
                <br>
                <div class="span4 offset4">
                    <?php
                        /**
                         * Let's just look for a 'last update' in the last day
                         * or so, and if that doesn't work then we'll look back
                         * even longer.
                         */
                        $query = "
                            SELECT DateStamp,TimeStamp
                            FROM SourceHeader
                            WHERE SysID = :SysID
                              AND DateStamp >= :Date
                            ORDER BY DateStamp DESC,TimeStamp DESC
                            LIMIT 1";
                        $bind[':SysID'] = $sysConfig['SysID'];
                        $bind[':Date'] = date('Y-m-d');
                        $result = $db -> fetchRow($query, $bind);
                        if(empty($result)){
                            for($i=1;$i<=7;$i++){  //loop day by day for a week for valid data
                                $bind[':Date'] = date('Y-m-d', strtotime('-' . $i . ' day'));
                                $result = $db -> fetchRow($query, $bind);
                                if(!empty($result)) break;
                            }
                        }

                        if(!empty($result)){
                            $dateTime = new DateTime($result['DateStamp'] . $result['TimeStamp']);
                    ?>
                            <span style="color:red">Last Update: <?=date_format($dateTime,'F jS, Y @ h:i A')?></span>
                    <?php }else{ ?>
                            <span style="color:red"><b>SYSTEM IS INACTIVE</b></span>
                    <?php
                        }
                    ?>
                </div>
            </div>

<?php
        $numSystems++;
            if($numSystems < count($sysConfigs)) {
?>
            <br><hr><br>
<?php
            }
        }
?>
        </div>
<?php
    }
}
$_SESSION['numSystems'] = $numSystems;
?>
<?php
require_once('../includes/footer.php');
?>
