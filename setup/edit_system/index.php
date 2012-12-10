<?php
/**
 *------------------------------------------------------------------------------
 * Edit System - Administrative Section
 *------------------------------------------------------------------------------
 *
 */

require_once('../../includes/pageStart.php');

$db = new db($config);

if($_SESSION['authLevel'] != 3) {
    gtfo($config);
}

/** CHECK ERRORS **/
//System Information
if(isset($_POST['submitInfo'])){
  if(  ($_POST['SysName'] == "")
    || ($_POST['Configuration'] == "-")
    || ($_POST['SysDescription'] == "")
    || ($_POST['InstallDate'] == "")
    || ($_POST['Maintainer'] == "-")
    || ($_POST['DAMID'] == "") || (strlen($_POST['DAMID']) != 12)
    || ($_POST['NumofTherms'] == "") || ($_POST['NumofTherms'] < 0) || ($_POST['NumofTherms'] > 5)
    || ($_POST['SysType'] == "-")
    || ($_POST['PlatformID'] == "-")
    || ($_POST['HeatExchanger'] == "-")
    || ($_POST['Installer'] == "-")
    || ($_POST['LocofMain'] == "")
    || ($_POST['NumofRSMS'] == "") || ($_POST['NumofRSMS'] < 0) || ($_POST['NumofRSMS'] > 4)
    || ($_POST['NumofPower'] == "") || ($_POST['NumofPower'] < 0) || ($_POST['NumofPower'] > 7)){
    $infoErr = true;
  }
}

//Sensor Mapping
if(isset($_POST['submitSensorMap'])){
  $query = "SELECT Recnum,SysID,SensorColName,SensorName,SysGroup FROM SysMap WHERE SysID = 0 AND SourceID = " . $_POST['sourceID'];
  $sysMap = $db -> fetchAll($query);
  $query = "SELECT Recnum,SysID,SensorColName,SensorName,SysGroup FROM SysMap WHERE SysID = " . $_SESSION['SysID'] . " AND SourceID = " . $_POST['sourceID'];
  $sysMapUnique = $db -> fetchAll($query);
  foreach ($sysMap as $resultRow){       //check to see values are valied
    //check for uniques and use if necessary
    foreach($sysMapUnique as $uniqueResult){
            if((!strcasecmp($uniqueResult['SensorColName'],$resultRow['SensorColName']))
                && (!strcasecmp($uniqueResult['SysGroup'],$resultRow['SysGroup']))){
                $resultRow = $uniqueResult;
            }
    }
    $loValue = "Lo" . $resultRow['Recnum'];
    $hiValue = "Hi" . $resultRow['Recnum'];
    $percentValue = "Percent" . $resultRow['Recnum'];
    $activeValue = "Active" . $resultRow['Recnum'];
    $addressValue = "Address" . $resultRow['Recnum'];
    $modelValue = "Model" . $resultRow['Recnum'];
    if(isset($_POST[$loValue]) && (!is_numeric($_POST[$loValue]))){
      $loErrFlag[$resultRow['Recnum']] = true;
      $mappingErr = true;
    }
    if(isset($_POST[$hiValue]) && (!is_numeric($_POST[$hiValue]))){
      $hiErrFlag[$resultRow['Recnum']] = true;
      $mappingErr = true;
    }
    if(isset($_POST[$percentValue]) && (!is_numeric($_POST[$percentValue]))){
      $percentErrFlag[$resultRow['Recnum']] = true;
      $mappingErr = true;
    }
  }
  if(!isset($mappingErr)){
    foreach ($sysMap as $resultRow){
        //check for uniques and use if necessary
        foreach($sysMapUnique as $uniqueResult){
                if((!strcasecmp($uniqueResult['SensorColName'],$resultRow['SensorColName']))
                    && (!strcasecmp($uniqueResult['SysGroup'],$resultRow['SysGroup']))){
                    $resultRow = $uniqueResult;
                }
        }
        $loValue = "Lo" . $resultRow['Recnum'];
        $hiValue = "Hi" . $resultRow['Recnum'];
        $percentValue = "Percent" . $resultRow['Recnum'];
        $activeValue = "Active" . $resultRow['Recnum'];
        $addressValue = "Address" . $resultRow['Recnum'];
        $modelValue = "Model" . $resultRow['Recnum'];
        $changeFlag = "Change" . $resultRow['Recnum'];
        if(!$_POST[$changeFlag]) continue;
        //check if entry already exists by changing it back
        $query = "SELECT Recnum FROM SysMap WHERE SysID = " . $resultRow['SysID' ] . " AND SensorColName = '" . $resultRow['SensorColName'] . "' AND SensorModel " . (isset($_POST[$modelValue]) ? "= " . $_POST[$modelValue] : "IS NULL") .
              " AND SensorAddress " . (isset($_POST[$addressValue]) ? "= " . $_POST[$addressValue] : "IS NULL") . " AND SensorActive = " . ($_POST[$activeValue] == on ? "1" : "0") .
              " AND AlarmUpLimit " . (isset($_POST[$hiValue]) ? "= " . $_POST[$hiValue] : "IS NULL") . " AND AlarmLoLimit " . (isset($_POST[$loValue]) ? "= " . $_POST[$loValue] : "IS NULL") .
              " AND AlertPercent " . (isset($_POST[$percentValue]) ? "= " . $_POST[$percentValue] : "IS NULL") . " AND SysGroup = " . $resultRow['SysGroup'] . " AND SourceID = " . $_POST['sourceID'];
        $exists = $db ->numRows($query);
        if($exists) continue;
        //check if unique value exists to determine update or insert
        $query = "SELECT Recnum FROM SysMap WHERE SysID = " . $_SESSION['SysID'] . " AND SensorColName = '" . $resultRow['SensorColName'] . "' AND SysGroup = " . $resultRow['SysGroup'] . " AND SourceID = " . $_POST['sourceID'];
        $exists = $db -> numRows($query);
        if($exists) $query = "UPDATE SysMap SET AlarmUpLimit = :hiValue, AlarmLoLimit = :loValue, AlertPercent = :percent, SensorActive = :active, SensorAddress = :address, SensorModel = :model WHERE SysID = " . $_SESSION['SysID'] . " AND SensorColName = '" . $resultRow['SensorColName'] . "' AND SysGroup = " . $resultRow['SysGroup'] . " AND SourceID = " . $_POST['sourceID'];
        else{
          //duplicate row first then update
          $query = "SELECT * FROM SysMap WHERE Recnum = " . $resultRow['Recnum'];
          $sth = $db -> prepare($query);
          $sth -> execute();
          $result = $sth -> fetch(PDO::FETCH_NUM);
          $query = "INSERT INTO SysMap VALUES(NULL, ";
          for($i=1;$i<count($result);$i++) $query .= (isset($result[$i]) ? "'" . $result[$i] . "', " : "NULL, ");
          //remove last , with )
          $query = substr_replace($query,")",strlen($query) - 2);
          if($db -> execute($query, $bind)) $lastinsert = $db -> lastInsertId();
          //grab system info
          $query = "SELECT DAMID,PlatformID,Configuration FROM SystemConfig WHERE SysID = " . $_SESSION['SysID'];
          $result = $db -> fetchRow($query);
          $query = "UPDATE SysMap SET SysID = :sysID, DAMID = :DAMID, PlatformID = :platformID, ConfigID = :config, AlarmUpLimit = :hiValue,
          AlarmLoLimit = :loValue, AlertPercent = :percent, SensorActive = :active, SensorAddress = :address, SensorModel = :model WHERE Recnum = " . $lastinsert;
          $bind[':sysID'] = $_SESSION['SysID'];
          $bind[':DAMID'] = $result['DAMID'];
          $bind[':platformID'] = $result['PlatformID'];
          $bind[':config'] = $result['Configuration'];
        }
        $bind[':hiValue'] = $_POST[$hiValue];
        $bind[':loValue'] = $_POST[$loValue];
        $bind[':percent'] = $_POST[$percentValue];
        $bind[':active'] = (isset($_POST[$activeValue]) ? "1" : "0");
        $bind[':address'] = $_POST[$addressValue];
        $bind[':model'] = $_POST[$modelValue];
        $db -> execute($query, $bind);
    }
  }
}

//Get list of buildings
$query = "SELECT buildingID, buildingName FROM buildings";
if($_SESSION['authLevel'] != 3) {
    $query .= " WHERE customerID = " . $_SESSION['customerID'];
}
$buildingList = $db -> fetchAll($query);

//Set Building ID
if(isset($_POST['buildingID'])){
    $buildingID = $_POST['buildingID'];
    $_SESSION['buildingID'] = $buildingID;
}else if(isset($_SESSION['buildingID'])) $buildingID = $_SESSION['buildingID'];

//get list of systems
if(isset($buildingID)){
    $query = "SELECT buildingName FROM buildings WHERE buildingID = " . $buildingID . " LIMIT 1";
    $result = $db -> fetchRow($query);
    $_SESSION['buildingName'] = $result['buildingName'];
    $query = "SELECT * FROM SystemConfig WHERE buildingID = " . $buildingID;
    $systemList = $db -> fetchAll($query);
    if(!isset($_POST['systemID']) && (sizeof($systemList) == 1)) $_POST['systemID'] = $systemList[0]['SysID'];
}

//set system id
if(isset($_POST['systemID'])){
    $systemID = $_POST['systemID'];
    $_SESSION['SysID'] = $systemID;
}elseif(isset($_SESSION['SysID'])) $systemID = $_SESSION['SysID'];

require_once('../../includes/header.php');

if(isset($infoErr) || isset($buildingErr) || isset($mappingErr)){
  echo "<div class='alert alert-error span8 offset2'>
                <button type='button' class='close' data-dismiss='alert'>&times;</button>
                <strong>Stop Everything!</strong> Something has gone wrong. You
                had better go check that out and maybe try it again.
            </div>";
          }
?>

        <div class="row">
          <h2 class="span8 offset3">Modify Existing System</h2>

        </div>
<!-- SELECT BUILDING -->
        <div class="row">
          <form method="post" action="./">
            <div class="span7" style="text-align:right">
              <h4>Select Building:&nbsp;&nbsp;
                <select name="buildingID" class="selectSubmit"><?php
                  if(!isset($buildingID)) echo "<option selected='selected'>Select A Building</option>";
                  foreach ($buildingList as $value) {
                    if($buildingID == $value['buildingID']) echo "<option selected='selected' value='" . $value['buildingID'] . "'>" . $value['buildingName'] . "</option>";
                    else echo "<option value='" . $value['buildingID'] . "'>" . $value['buildingName'] . "</option>";
                  }?>
                </select>
              </h4>
            </div>
          </form>
        </div>
  <!-- SELECT SYSTEM -->
        <?php if(isset($buildingID)){ ?>
            <div class="row">
              <form method="post" action="./">
                <div class="span7" style="text-align:right">
                  <h4>Select System:&nbsp;&nbsp;
                    <select name="systemID" class="selectSubmit"><?php
                      if(!isset($systemID)) echo "<option selected='selected'>Select A System</option>";
                      foreach ($systemList as $value) {
                        if($systemID == $value['SysID']) echo "<option selected='selected' value='" . $value['SysID'] . "'>" . $value['SysName'] . "</option>";
                        else echo "<option value='" . $value['SysID'] . "'>" . $value['SysName'] . "</option>";
                      }?>
                    </select>
                    <input type="hidden" name="buildingID" value="<?=$buildingID?>">
                  </h4>
                </div>
              </form>
            </div>
        <?php } ?>
  <!-- SYSTEM INFORMATION  -->
    <?php if(isset($systemID)){ ?>
        <div class="accordion-group" style="border:0px">
            <div class="accordion-heading">
                <a class="accordion-toggle" data-toggle="collapse"
                    data-parent="#accordion2"
                    href="#collapse1">
                            <div class="row">
                                <h2 class="span8 offset3">+ System Information</h2>
                            </div>
                </a>
            </div>
            <div id="collapse1" class="accordion-body collapse<?=(isset($infoErr)) ? "in" : ""?>">
                <div class="accordion-inner accordion-highlight">

                    <?php
                        if(isset($buildingID)){
                            if(!isset($_POST['submitInfo'])) $update = true;
                            include('../information/index.php');
                        }else echo "<span style='color:red'>Please Select A Building</span>";
                    ?>

                </div>
            </div>
        </div>
    <?php }else{ ?>
        <div class="row">
            <font color="grey">
                <h2 class="span8 offset3">&nbsp;&nbsp;System Information</h2>
            </font>
        </div>
    <?php } ?>
<!-- SENSOR MAPPING INFORMATION  -->
    <?php if(isset($systemID)){ ?>
        <div class="accordion-group" style="border:0px">
            <div class="accordion-heading">
                <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordion2"
                    href="#collapse2">
                    <div class="row">
                        <h2 class="span8 offset3">+ Sensor Mapping</h2>
                    </div>
                </a>
            </div>
            <div id="collapse2" class="accordion-body collapse<?=($_POST['submitSensorMap']) ? "in" : ""?>">
                <div class="accordion-binner">
                  <div class="row">
                    <div class="row offset3">
                        <h2 class="span8"><a data-toggle="collapse" href="#collapse2A">- Main DAM Sensors</a></h2>
                    </div>
                    <div id="collapse2A" class="accordion-body collapse<?=(isset($mappingErr) && ($_POST['sourceID'] == 0)) ? "in" : ""?>">
                      <div class="accordion-inner accordion-highlight span12">
                        <?php
                            if(isset($buildingID)) include('../sensor_mapping/index.php');
                            else echo "<span style='color:red'>Please Select A Building</span>";
                        ?>
                      </div>
                    </div>
                    <?php
                      $query = "SELECT NumofRSM FROM SystemConfig WHERE SysID = " . $systemID;
                      $rsm = $db -> fetchRow($query);
                      for($i=1;$i<=$rsm['NumofRSM'];$i++){
                    ?>
                    <div class="row offset3">
                        <h2 class="span8"><a data-toggle="collapse" href="#collapse2B<?=$i?>">- RSM <?=$i?> Sensors</a></h2>
                    </div>
                    <div id="collapse2B<?=$i?>" class="accordion-body collapse<?=(isset($mappingErr) && ($_POST['sourceID'] == $i)) ? "in" : ""?>">
                        <div class="accordion-inner accordion-highlight span12">
                        <?php
                            $id = $i;
                            if(isset($buildingID)) include('../sensor_mapping/index.php');
                            else echo "<span style='color:red'>Please Select A Building</span>";
                        ?>
                        </div>
                    </div>
                    <?php
                      }
                    ?>
                    <div class="row offset3">
                      <h2 class="span8"><a data-toggle="collapse" href="#collapse2C">- Power Meters/Thermostats</a></h2>
                    </div>
                    <div id="collapse2C" class="accordion-body collapse<?=(isset($mappingErr) && ($_POST['sourceID'] == 4)) ? "in" : ""?>">
                      <div class="accordion-inner accordion-highlight span12">
                        <?php
                            $id = 0;
                            if(isset($buildingID)) include('../sensor_mapping/index.php');
                            else echo "<span style='color:red'>Please Select A Building</span>";
                        ?>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
        </div>
    <?php }else{ ?>
        <div class="row">
            <font color="grey">
                <h2 class="span8 offset3">&nbsp;&nbsp;Sensor Mapping</h2>
            </font>
        </div>
    <?php } ?>
<!-- STATUS DASHBOARD MAPPING  -->
<!-- Disabled
        <div class="accordion-group">
             <div class="accordion-heading">
                <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordion2"
                    href="#collapse4">
                            <div class="row">
                                <h2 class="span8 offset3">+ Status Dashboard</h2>
                            </div>
                </a>
            </div>
            <div id="collapse4" class="accordion-body collapse">
                <div class="accordion-inner">
                    <div class="row">
                        <div class="span5">
                             <h2 class="span8 offset3"><a href="information/">- Building Information</a></h2>
                             <h2 class="span8 offset3"><a href="sensor_mapping?id=<?//php echo $SysID; ?>">- Sensor Mapping</a></h2>
                             <h2 class="span8 offset3"><a href="alarm_limits/">- Alarm Limits</a></h2>
                             <h2 class="span8 offset3">- Maintenance</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <font color="grey">
                <h2 class="span8 offset3">&nbsp;&nbsp;Status Dashboard</h2>
            </font>
        </div>
    -->
<?php
  require_once('../../includes/footer.php');
?>
