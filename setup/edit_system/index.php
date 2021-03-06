
<?php
/**
 *------------------------------------------------------------------------------
 * Edit System - Administrative Section
 *------------------------------------------------------------------------------
 *
 */

require_once('../../includes/pageStart.php');

$db = new db($config);

//if($_SESSION['authLevel'] != 3) {
//    gtfo($config);
//}
$PInhibitSys=0;
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

if (isset($_GET['Action']))
 // added ability ti inhibit and delete a system  rji 9/12/13      
{
     if ($_GET['Action']=="Activate")  
            
       { $query="Update SystemConfig set Active=1 where Sysid=".$_SESSION['SysID']; }
        
     if ($_GET['Action']=="Inhibit")  
            
       { $query="Update SystemConfig set Active=0 where Sysid=".$_SESSION['SysID']; }
            
     if ($_GET['Action']=="Remove")
           
       { $query="Update SystemConfig set BuildingID=0 where Sysid=".$_SESSION['SysID']; }
       
        if ($_GET['Action']=="RemoveBlg")
           
       { $query="delete from buildings where buildingid=".$_SESSION['buildingID'];
       
      $_SESSION['buildingID']="";
      $buildingID=NULL;
      $_POST['buildingID']=NULL;
       }
      //  print_r($query);
       $Ok=$db->execute($query); 
        
    if ($Ok==1)  echo("<font size='3' color='blue'><b>".$_GET['Action']." Action Completed</b></font>");
        
}




//Sensor Mapping
if(isset($_POST['submitSensorMap'])){
  $query = "SELECT Recnum,SysID,SensorColName,SensorName,SysGroup FROM SysMap WHERE SysID = 0 AND SourceID = " . $_POST['sourceID'];
  $sysMap = $db -> fetchAll($query);
 
  $query = "SELECT Recnum,SysID,SensorColName,SensorName,SysGroup FROM SysMap WHERE SysID = " . $_SESSION['SysID'] . " AND SourceID = " . $_POST['sourceID'];
  $sysMapUnique = $db -> fetchAll($query);
  
  foreach ($sysMap as $resultRow){       //check to see values are valid
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
    $triggerValue = "Trigger" . $resultRow['Recnum'];
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
    if(isset($_POST[$triggerValue]) && (!is_numeric($_POST[$triggerValue]))){
      $triggerErrFlag[$resultRow['Recnum']] = true;
      $mappingErr = true;
    }
  }
  if(!isset($mappingErr)){
    $updateBS = false;
    foreach ($sysMap as $resultRow){
        unset($bind);
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
        $triggerValue = "Trigger" . $resultRow['Recnum'];
        $activeValue = "Active" . $resultRow['Recnum'];
        $addressValue = "Address" . $resultRow['Recnum'];
        $modelValue = "Model" . $resultRow['Recnum'];
        $changeFlag = "Change" . $resultRow['Recnum'];
        if(!$_POST[$changeFlag]) continue;
        //check if entry already exists by changing it back
        $query = "SELECT Recnum FROM SysMap WHERE SysID = " . $resultRow['SysID' ] . " AND SensorColName = '" . $resultRow['SensorColName'] . "' AND SensorModel " . (isset($_POST[$modelValue]) ? "= " . $_POST[$modelValue] : "IS NULL") .
              " AND SensorAddress " . (isset($_POST[$addressValue]) ? "= '" . $_POST[$addressValue] . "'" : "IS NULL") . " AND SensorActive = " . ($_POST[$activeValue] == 1 ? "1" : "0") .
              " AND AlarmUpLimit " . (isset($_POST[$hiValue]) ? "= " . $_POST[$hiValue] : "IS NULL") . " AND AlarmLoLimit " . (isset($_POST[$loValue]) ? "= " . $_POST[$loValue] : "IS NULL") .
              " AND AlertPercent " . (isset($_POST[$percentValue]) ? "= " . $_POST[$percentValue] : "IS NULL") . " AND AlarmTrigger " . (isset($_POST[$triggerValue]) ? "= " . $_POST[$triggerValue] : "IS NULL")  .
              " AND SysGroup = " . $resultRow['SysGroup'] . " AND SourceID = " . $_POST['sourceID'] . " AND SensorName = '" . $_POST[$resultRow['SensorColName']] . "'";
        $exists = $db ->numRows($query);
       
        if($exists) continue;
        //check if unique value exists to determine update or insert
        $query = "SELECT Recnum FROM SysMap WHERE SysID = " . $_SESSION['SysID'] . " AND SensorColName = '" . $resultRow['SensorColName'] . "' AND SysGroup = " . $resultRow['SysGroup'] . " AND SourceID = " . $_POST['sourceID'];
        
        $recnum = $db -> fetchRow($query);
        if(!empty($recnum)) $query = "UPDATE SysMap SET AlarmUpLimit = :hiValue, AlarmLoLimit = :loValue, AlertPercent = :percent, AlarmTrigger = :trigger, SensorActive = :active, SensorAddress = :address, SensorModel = :model WHERE SysID = " . $_SESSION['SysID'] . " AND SensorColName = '" . $resultRow['SensorColName'] . "' AND SysGroup = " . $resultRow['SysGroup'] . " AND SourceID = " . $_POST['sourceID'];
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
          AlarmLoLimit = :loValue, AlertPercent = :percent, AlarmTrigger = :trigger, SensorActive = :active, SensorAddress = :address, SensorModel = :model WHERE Recnum = " . $lastinsert;
           echo("U0".$query);
          
          $bind[':sysID'] = $_SESSION['SysID'];
          $bind[':DAMID'] = $result['DAMID'];
          $bind[':platformID'] = $result['PlatformID'];
          $bind[':config'] = $result['Configuration'];
          
          
          
          
          
        }
        //$bind[':sensorName'] = $_POST[$resultRow['SensorColName']];
        $bind[':hiValue'] = $_POST[$hiValue];
        $bind[':loValue'] = $_POST[$loValue];
        $bind[':percent'] = $_POST[$percentValue];
        $bind[':trigger'] = $_POST[$triggerValue];
        $bind[':active'] = (!empty($_POST[$activeValue]) ? "1" : "0");
        $bind[':address'] = $_POST[$addressValue];
        $bind[':model'] = $_POST[$modelValue];
        $db -> execute($query, $bind);
         echo("U0".$query);
        //check and update BS0x
        if(!isset($lastinsert)) $lastinsert = $recnum['Recnum'];
        $query = "SELECT SensorType FROM SysMap WHERE Recnum = " . $lastinsert . " LIMIT 1";
        $result = $db -> fetchRow($query);
        if(($result['SensorType'] == 7) && ($updateBS == false)){
            unset($bind);
            $bind[':sysID'] = $_SESSION['SysID'];
            $bind[':active'] = (!empty($_POST[$activeValue]) ? "1" : "0");
            $bind[':address'] = $_POST[$addressValue];
            $bind[':model'] = $_POST[$modelValue];
            if($exists){
                $query = "UPDATE SysMap SET SensorAddress = :address, SensorActive = :active, SensorModel = :model
                        WHERE SysID = :sysID AND SensorColName LIKE 'bs%'";
                $db -> execute($query,$bind);
                echo("U1".$query);
            }else{
                for($i=1;$i<8;$i++){
                    //duplicate row first then update
                    $query = "SELECT * FROM SysMap WHERE SysID = " . $_SESSION['SysID'] . " AND SensorColName = 'BS0" . $i . "'";
                
                   try {
                    $sth = $db -> prepare($query);
                    $sth -> execute();
                    $result = $sth -> fetch(PDO::FETCH_NUM);         
                  
              
                   }
                    catch (Exception $e) {
                    $PErr=True;
                    echo '<BR>Caught exception: ',  $e->getMessage(), "\n";
                   }
                    $query = "INSERT INTO SysMap VALUES(NULL, ";
                    for($j=1;$j<count($result);$j++) {
                      $query .= (isset($result[$j]) ? "'" . $result[$j] . "', " : "NULL, ");
                 
                    }
                    //remove last , with )
                    $query = substr_replace($query,")",strlen($query) - 2);
                
                    
                    // if this query is null inhibit execute statement   rji 09/12/13  also added try statement above
                   
                   if ($result!=NULL) { if($db -> execute($query)) $lastinsert = $db -> lastInsertId(); }
                    //grab system info
                    $query = "SELECT DAMID,PlatformID,Configuration FROM SystemConfig WHERE SysID = " . $_SESSION['SysID'];
                    $result = $db -> fetchRow($query);
                    $bind[':DAMID'] = $result['DAMID'];
                    $bind[':platformID'] = $result['PlatformID'];
                    $bind[':config'] = $result['Configuration'];
                    $query = "UPDATE SysMap SET SysID = :sysID, DAMID = :DAMID, PlatformID = :platformID, ConfigID = :config,
                    SensorModel = :model, SensorActive = :active, SensorAddress = :address WHERE Recnum = " . $lastinsert;
                    $db -> execute($query,$bind);
                    
                    echo("U2".$query);
                }
            }
            $updateBS = true;
        }
    }
  }
}

//Get list of buildings
$nosysflag=False;  // added to inhibit listing if no systems are defined
$query = "SELECT buildingID, buildingName FROM buildings where buildingid<>0";
if($_SESSION['authLevel'] != 3) {
    $query .= " and  customerID = " . $_SESSION['customerID'];
}
$buildingList = $db -> fetchAll($query);

//Set Building ID
if(isset($_POST['buildingID'])){
    $buildingID = $_POST['buildingID'];
    $_SESSION['buildingID'] = $buildingID;
}else if(isset($_SESSION['buildingID'])) $buildingID = $_SESSION['buildingID'];

//get list of systems
if(isset($buildingID) and $buildingID!=NULL){
    $query = "SELECT buildingName FROM buildings WHERE buildingID = " . $buildingID . " LIMIT 1";
    $result = $db -> fetchRow($query);
    $_SESSION['buildingName'] = $result['buildingName'];
    $query = "SELECT * FROM SystemConfig WHERE buildingID = " . $buildingID;
    $systemList = $db -> fetchAll($query);
    if ($db->numrows($query)==0) {$nosysflag=True;} else {$nosysflag=False;}
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
                
                   echo "<option selected='selected'>Select A Building</option>";
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
        <?php if(isset($buildingID) and $buildingID!=NULL)     { ?>  
              <?php if ($nosysflag) { ?>
                <div class="row">
                 <form method="post" action="./">
                 <div class="span7" style="text-align:right"> 
                 <h4>No Systems Defined for this building</h4>
                 
                 </div>
                 <div  class="span2" style="text-align:right">                      
                 
                       <a class="btn btn-small" style="font-size:11px;" href="./?Action=RemoveBlg">
                           <i class="icon-remove"></i>
                           Remove Building
                            </a>  
                                
                </div>
  
                </div>
                  
              </form>
           
              <?php   } else  { ?> 
            <div class="row">
              <form method="post" action="./">
                <div class="span7" style="text-align:right">
                  <h4>Select System:&nbsp;&nbsp;
                    <select name="systemID" class="selectSubmit"><?php
                      if(!isset($systemID)) echo "<option selected='selected'>Select A System</option>";
                                          
                      foreach ($systemList as $value) {
                        if($systemID == $value['SysID']) {            
                            
                           $PInhibitSys= $value['Active'];                    
                            
                           echo "<option selected='selected' value='" . $value['SysID'] . "'>" . $value['SysName'] . "</option>"; }
                        
                        else  {echo "<option value='" . $value['SysID'] . "'>" . $value['SysName'] . "</option>";}
                      }
                      ?>
                    </select>
                    <input type="hidden" name="buildingID" value="<?=$buildingID?>">
                  </h4>
                   
                </div>
                <div  class="span2" style="text-align:right">      
                    
                    
                     <?php    if ($PInhibitSys==1) { ?>                    
                           <a class="btn btn-small" style="font-size:11px;" href="./?Action=Inhibit">
                           <i class="icon-minus"></i>
                           Inhibit System
                            </a>  
                     <?php } else {   ?>
                          <a class="btn btn-small" style="font-size:11px;" href="./?Action=Activate">
                           <i class="icon-plus"></i>
                           Activate System
                            </a>  
                         
                     <?php   }?>   
                   </div>    
                   <div  class="span2" style="text-align:right">                      
                 
                       <a class="btn btn-small" style="font-size:11px;" href="./?Action=Remove">
                           <i class="icon-remove"></i>
                           Remove System
                            </a>  
                                
                </div>
                  
              </form>
            </div>
        <?php } ?>   <!-- else no system id -->
    <?php } ?>   <!-- no buildingid -->    
  <!-- SYSTEM INFORMATION  
 
    <?php if(isset($systemID) and !$nosysflag) { ?>
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
    <?php if(isset($systemID) and !$nosysflag){ ?>
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
<!-- MAINTENANCE  -->
<?php if(isset($systemID) and !$nosysflag){ ?>
        <div class="accordion-group" style="border:0px">
            <div class="accordion-heading">
                <a class="accordion-toggle"
                href="../../maintenance/">
                    <div class="row">
                        <h2 class="span8 offset3">+ Maintenance</h2>
                    </div>
                </a>
            </div>
        </div>
        <div class="accordion-group" style="border:0px">
            <div class="accordion-heading">
                <a class="accordion-toggle"
                href="../dashboard/">
                    <div class="row">
                        <h2 class="span8 offset3">+ Dashboard</h2>
                    </div>
                </a>
            </div>
        </div>
<?php   } else { ?>
        <div class="row">
            <font color="grey">
                <h2 class="span8 offset3">&nbsp;&nbsp;Maintenance</h2>
            </font>
        </div>
        <div class="row">
            <font color="grey">
                <h2 class="span8 offset3">&nbsp;&nbsp;Dashboard</h2>
            </font>
        </div>



<?php    }
    require_once('../../includes/footer.php');
?>
