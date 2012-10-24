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

 $SysID=$_Session["SysID"];     
        
 ?>
        <div class="row">
            <h1 class="span8 offset2">System Setup</h1>
        </div>
        <div class="row">
            <h2 class="span8 offset3"><a href="Information/">Building Information</a></h2>
        </div>
        <div class="row">
        <h2 class="span8 offset3"><a href="<?php echo("../setup/Sensor_Mapping/index.php?Sysid=".$SysID);?>">Sensor Mapping</a></h2>
        </div>
        <div class="row">
            <h2 class="span8 offset3">Web Chart Mapping</h2>
        </div>
        <div class="row">
            <h2 class="span8 offset3"><a href="Alarm_Limits/">Alarm Limits</a></h2>
        </div>
        <div class="row">
            <h2 class="span8 offset3">Maintenance</h2>
        </div>
        
<?php
require_once('../includes/footer.php');
?>
