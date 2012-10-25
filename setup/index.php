<?php
/**
 *------------------------------------------------------------------------------
 * Maintenance Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');

checkSystemSet($config);

require_once('../includes/header.php');

 $SysID=$_SESSION["SysID"];

 ?>
        <div class="row">
            <h1 class="span8 offset2">System Setup</h1>
        </div>
        <div class="row">
            <h2 class="span8 offset3"><a href="information/">Building Information</a></h2>
        </div>
        <div class="row">
        <h2 class="span8 offset3"><a href="sensor_mapping?id=<?php echo $SysID; ?>">Sensor Mapping</a></h2>
        </div>
        <div class="row">
            <h2 class="span8 offset3">Web Chart Mapping</h2>
        </div>
        <div class="row">
            <h2 class="span8 offset3"><a href="alarm_limits/">Alarm Limits</a></h2>
        </div>
        <div class="row">
            <h2 class="span8 offset3">Maintenance</h2>
        </div>

<?php
require_once('../includes/footer.php');
?>
