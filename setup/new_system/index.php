<?php
/**
 *------------------------------------------------------------------------------
 * Edit Customer Info - Administrative Section
 *------------------------------------------------------------------------------
 *
 */

require_once('../../includes/pageStart.php');
require_once('../../includes/header.php');
?>
 <?php if (isset($_SESSION['authLevel']) && intval($_SESSION['authLevel']) == 3  )
  if (isset($_SESSION['SetStep'])) {$SetNum=$_SESSION['SetStep'];} else {$SetStep=1;}


  if (isset($_SESSION['SystemStart'])) {$SystemStrtFlag=$_SESSION['SystemStart'];} else { $SystemStrtFlag=false;}
  $_SESSION['SystemStart']=$SystemStrtFlag;
  if (isset($_SESSION['SystemComp'])) {$SystemStrtFlag=$_SESSION['SystemComp'];} else { $SystemCompFlag=false;}
  $_SESSION['SystemComp']=$SystemCompFlag;

  if (isset($_SESSION['SetUpSensMap'])) {$MappingFlag=$_SESSION['SetUpSensMap'];} else { $MappingFlag=false;}
  if (isset($_SESSION['SetUpAlarm'])) {$AlarmFlag=$_SESSION['SetUpAlarm'];} else { $AlarmFlag=false;}
  if (isset($_SESSION['SetUpStatus'])) {$StatusFlag=$_SESSION['SetUpStatus'];} else { $StatusFlag=false;}
  if (isset($_SESSION['SetUpComp'])) {$CompFlag=$_SESSION['SetUpComp'];} else { $CompFlag=false;}
  switch ($SetStep)
  {
      Case 1: $BldColor="accordion-curstep";
          break;
      default: $BldColor="accordion-nocolor";
  }
 // initializes Database
    $db = new db($config);

    if(isset($_POST['building'])){
      if($_POST['building'] != "new") $_SESSION['buildingID'] = $_POST['building'];
    }

    $query = "SELECT buildingID, buildingName FROM buildings";
    if($_SESSION['authLevel'] != 3) {
        $query .= " WHERE customerID = " . $_SESSION['customerID'];
    }

   $buildingList = $db -> fetchAll($query);

   { ?>

        <div class="row">
          <h2 class="span8 offset3">New System Setup</h2>

        </div>
<!-- SELECT BUILDING -->
          <div class="row">
          <form method="post" action="./">
            <div class="span7" style="text-align:right">
              <h4>Select Building:&nbsp;&nbsp;<select name="building"><?php
              foreach ($buildingList as $value) {
                if( (isset($_POST['building']))
                  & ($_POST['building'] != "new")
                  & ($_SESSION['buildingID'] == $value['buildingID'])){
                  echo "<option selected='selected' value='" . $value['buildingID'] . "'>" . $value['buildingName'] . "</option>";
                }else echo "<option value='" . $value['buildingID'] . "'>" . $value['buildingName'] . "</option>";
              }?>
                <option value="new" <?=($_POST['building'] == "new") ? "selected='selected'" : ""?>>+ Add New Building</option>
              </select></h4>
            </div>
            <div class="span5">
              <button type="submit" class="btn btn-success">
                <i class="icon-ok icon-white"></i>
                Select
              </button>
            </div>
          </form>
        </div>
  <!-- NEW BUILDING -->
        <div>
          <?php if($_POST['building'] == "new") include('../new_building/index.php'); ?>
        </div>
  <!-- SYSTEM INFORMATION  -->
         <div class="accordion-group" style="border:0px">
            <div class="accordion-heading">
                <a class="accordion-toggle" data-toggle="collapse"
                    <?php
                    // if($_SESSION['SystemStart']==false or $_SESSION['SystemComp']==true) {echo("data-toggle='collapse'");}
                    ?>
                    data-parent="#accordion2"
                    href="#collapse1">
                            <div class="row">
                                <h2 class="span8 offset3 <?php echo($BldColor);?>">+ System Information <?php echo($_SESSION['SetUpBuild']);?></h2>

                            </div>
                </a>
            </div>
            <div id="collapse1" class="accordion-body collapse<?php if(count($_POST)>1 and  $_SESSION['SystemStart']){echo ' in';} ?>">
                <div class="accordion-inner accordion-highlight">

                    <?php
                        $_SESSION['SetUpNew']=true;
                        $_SESSION['SystemStart']=true;
                        include('../../setup/information/index.php');
                    ?>
                    

                </div>
           </div>

         </div>
<!-- SENSOR MAPPING INFORMATION  -->
  <?php   if ($_SESSION['SystemComp'] == true) {    ?>
         <div class="accordion-group">
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
            <div id="collapse2" class="accordion-body collapse">
                <div class="accordion-inner">
                    <div class="row">
                        <div class="span5">
                             <h2 class="span8 offset3"><a href="information/">- Building Information2</a></h2>
                             <h2 class="span8 offset3"><a href="sensor_mapping?id=<?php echo $SysID; ?>">- Sensor Mapping2</a></h2>
                             <h2 class="span8 offset3"><a href="alarm_limits/">- Alarm Limits2</a></h2>
                             <h2 class="span8 offset3">- Maintenance2</h2>
                        </div>
                    </div>
                </div>
             </div>
          </div>
     <?php   } else {?>

            <div class="row"><font color="grey">
                <h2 class="span8 offset3">&nbsp;&nbsp;Sensor Mapping</h2>
               </font>
            </div>

     <?php } ?>
 <!-- ALARMS LIMITS INFORMATION  -->
    <?php   if ($buildingFlag == true && $sensorFlag== true) {    ?>
            <div class="accordion-group">
             <div class="accordion-heading">
                <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordion3"
                    href="#collapse3">
                            <div class="row">
                                <h2 class="span8 offset3">+ Alarms Limits</h2>
                            </div>
                </a>
            </div>
            <div id="collapse3" class="accordion-body collapse">
                <div class="accordion-inner">
                    <div class="row">
                        <div class="span5">
                             <h2 class="span8 offset3"><a href="information/">- Building Information2</a></h2>
                             <h2 class="span8 offset3"><a href="sensor_mapping?id=<?php echo $SysID; ?>">- Sensor Mapping2</a></h2>
                             <h2 class="span8 offset3"><a href="alarm_limits/">- Alarm Limits2</a></h2>
                             <h2 class="span8 offset3">- Maintenance2</h2>
                        </div>

                    </div>
                </div>
            </div>
           </div>
      <?php   } else {?>

              <div class="row"><font color="grey">
                                <h2 class="span8 offset3">&nbsp;&nbsp;Alarm Limits</h2>
                               </font>
                            </div>

     <?php } ?>





<!-- STATUS DASHBOARD MAPPING  -->

  <?php   if ($buildingFlag == true && $sensorFlag== true) {    ?>


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
                             <h2 class="span8 offset3"><a href="information/">- Building Information2</a></h2>
                             <h2 class="span8 offset3"><a href="sensor_mapping?id=<?php echo $SysID; ?>">- Sensor Mapping2</a></h2>
                             <h2 class="span8 offset3"><a href="alarm_limits/">- Alarm Limits2</a></h2>
                             <h2 class="span8 offset3">- Maintenance2</h2>
                        </div>

                    </div>




                    </div>

                </div>

            </div>

  <?php   } else {?>

              <div class="row"><font color="grey">
                                <h2 class="span8 offset3">&nbsp;&nbsp;Status Dashboard</h2>
                               </font>
                            </div>

     <?php } ?>


 <?php } ?>
<?php

require_once('../../includes/footer.php');
?>
