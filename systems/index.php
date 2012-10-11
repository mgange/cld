<?php
/**
 *------------------------------------------------------------------------------
 * Building Selector - Dashboard
 *------------------------------------------------------------------------------
 * If a user has access to more than one building/system they can be sent here
 * to decide which one they're trying to view/manage. Their choice is set in
 * $_SESSION and they'll be redirected someplace else.
 */

session_start();

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

// Get all their buildings
$query = 'SELECT buildingID, address1, address2, city, state, zip FROM buildings WHERE customerID = :customerID';
$buildingsBind[':customerID'] = $_SESSION['customerID'];

$buildings = $db -> fetchAll($query, $buildingsBind);
?>

        <div class="row">
            <h1 class="span8 offset2">Your Systems</h1>
        </div>

<?php
// Get the systems associated with each building
$numSystems = 0;
foreach($buildings as $building) {
    $query = 'SELECT SysID, DAMID, SysName FROM SystemConfig WHERE buildingID = :buildingID';
    $bind['buildingID'] = $building['buildingID'];
    $sysConfigs = $db -> fetchAll($query, $bind);
?>
        <div class="well clearfix">
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
                <br>
                <?php echo $building['zip']; ?>
            </h5>
<?php
    foreach($sysConfigs as $sysConfig) {
?>
            <div class="span2 offset1">
                <?php echo $sysConfig['SysName']; ?>
            </div>

            <a href="./?buildingID=<?php
                               echo $building['buildingID'];
                               ?>&SysID=<?php
                                echo $sysConfig['SysID'];
                                ?>&intent=information" class="span2">Information</a>

            <a href="./?buildingID=<?php
                               echo $building['buildingID'];
                               ?>&SysID=<?php
                                echo $sysConfig['SysID'];
                                ?>&intent=alarms" class="span2">Alarms</a>

            <a href="./?buildingID=<?php
                               echo $building['buildingID'];
                               ?>&SysID=<?php
                                echo $sysConfig['SysID'];
                                ?>&intent=status" class="span2">Status</a>

            <a href="./?buildingID=<?php
                               echo $building['buildingID'];
                               ?>&SysID=<?php
                                echo $sysConfig['SysID'];
                                ?>&intent=performance" class="span2">Performance</a>

<?php
        $numSystems++;
    }
}
$_SESSION['numSystems'] = $numSystems;
?>
        </div>
<?php
require_once('../includes/footer.php');
?>
