<?php
/**
 *------------------------------------------------------------------------------
 * Building Selector - Dashboard
 *------------------------------------------------------------------------------
 * If a user has access to more than one building/system they can be sent here
 * to decide which one they're trying to view/manage. Their choice is set in
 * $_SESSION and they'll be redirected someplace else.
 */

require_once('../../includes/header.php');

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
        header('Location: ../../' . $_GET['intent']);
    }else{
        header('Location: ../');
    }
}

$db = new db($config);

// Get all their buildings
$query = 'SELECT * FROM buildings WHERE customerID = :customerID';
$buildingsBind[':customerID'] = $_SESSION['customerID'];

$buildings = $db -> fetchAll($query, $buildingsBind);
?>

        <div class="row">
            <h1 class="span8 offset2">Your Systems</h1>
        </div>

<?php
// Get the systemms associated with each building
$numSystems = 0;
foreach($buildings as $building) {
    $query = 'SELECT * FROM SystemConfig WHERE buildingID = :buildingID';
    $bind['buildingID'] = $building['buildingID'];
    $configs = $db -> fetchAll($query, $bind);
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
    foreach($configs as $config) {
?>
            <div class="span8 offset2">
                <a href="./?buildingID=<?php
                echo $building['buildingID']
                ?>&SysID=<?php
                echo $config['SysID'];
                if(isset($_GET['intent'])) {
                    echo '&intent=' . $_GET['intent'];
                }
                ?>">
                    <?php echo $config['DAMID']; ?>

                </a>
            </div>
<?php
        $numSystems++;
    }
}
$_SESSION['numSystems'] = $numSystems;
?>
        </div>
<?php
require_once('../../includes/footer.php');
?>
