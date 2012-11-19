

<?php
/**
 *------------------------------------------------------------------------------
 *  Building Information Setup Index Page
 *------------------------------------------------------------------------------
 *
 *
**/

function SelectPD($DBValue,$SelValue)
{
    if ($DBValue==$SelValue) { return "selected";} else {return "";}
}
function MySQL_Pull_Down($config,$query,$InputName,$DisplayField,$SelField,$SelValue,$DefMess,$Class,$Submit)
{
    // first get data
  $dbpd = new db($config);
  $pdrows= $dbpd -> numRows($query);
  $PDList = $dbpd -> fetchAll($query);
  $dropdown = "<select name='".$InputName."' class='".$Class."'>";
   
foreach ($PDList as $row) {
    echo($row[$SelField]."||".$SelValue);
    $Sel= SelectPD($row[$SelField],$SelValue);
  echo($Sel);
   $dropdown .= "\r\n<option value='".$row[$SelField]."' ".$Sel.">".$row[$DisplayField]."</option>";
    }

   $dropdown .= "\r\n</select>";

   echo $dropdown;

}

// check to see if this is a new system setup
 if (isset($_SESSION['SetUpNew'])) { $NewSystem=$_SESSION['SetUpNew'];} else {$NewSystem=false;}
 // if not a new system set up check to see if a system has been selected if not refers to choose system option
if  (NewSystem==false) {checkSystemSet($config);}
// goes to home page if trying to set up new system without proper authorization
if($_SESSION['authLevel'] < 3 && $NewSystem==false) {
    gtfo($config);
}

 //$_SESSION['BuildStart']=true;

// existing system modifications
if ($NewSystem==false)
{


    $SysId = $_SESSION['SysID'];
    $BuildingID = $_SESSION['buildingID'];
    $query = "SELECT * FROM SystemConfig WHERE SysId = $SysId AND BuildingID = $BuildingID";
    $systemInfo = $db -> fetchRow($query);
    $query = "SELECT * FROM buildings WHERE buildingID = $BuildingID";
    $buildingInfo = $db -> fetchRow($query);
    $query = "SELECT * FROM customers WHERE customerID = $buildingInfo[CustomerID]";
    $customerInfo = $db -> fetchRow($query);
    $SysName=" for ".$systemInfo['SysName'];
// posts updates for existing systems
    if(count($_POST) > 0) {
     //   $_SESSION['SystemStart']=false;
   //     $query = "UPDATE customers,SystemConfig SET customers.email1 = :email1, customers.email2 = :email2, SystemConfig.Maintainer = :maintainer WHERE customers.customerID = $customerInfo[customerID] AND SystemConfig.SysId = $SysId AND SystemConfig.BuildingID = $BuildingID";
   //     $bind[':email1'] = $_POST['email1'];
    //    $bind[':email2'] = $_POST['email2'];
    //    $bind[':maintainer'] = $_POST['maintainer'];

    //    if($db -> execute($query, $bind)) {
     //       header('Location: ../?a=ss'); //a = Alert  ss = Secondary Success(generic)
     //    }else{
     //       header('Location: ./?a=e'); //a = Alert  e = error(generic)
    //    }

 //   die(require_once('../../includes/footer.php'));
    }

//require_once('../../includes/header.php');
}
else  // new system set up
{
    $Readonly=false;
    // first determine next available SYSID
    $SysIDQuery ="Select SysID from SystemConfig order by SysID desc limit 1";
    $NextID = $db -> fetchRow($SysIDQuery);
    $NewID=$NextID['SysID']+1;
    $SysName="New SysID= ".$NewID;
}

//parameter feedback
 $BldSel=$_POST['Building'];
 $ConfigSel=$_POST['Configuration'];
 $SysDescrpSel=$_POST['SysDescription'];
 $SysTypeSel=$_POST['SysType'];
 $PlatformIDSel=$_POST['PlatformID'];
 $HeatExhangerSel=$_POST['HeatExchanger'];
 $InstallDateSel=$_POST['InstallDate'];
 $InstallerSel=$_POST['Installer'];
 $MaintainerSel=$_POST['Maintainer'];
 $LocofMainSel=$_POST['LocofMain'];
 $DAMIDSel=$_POST['DAMID'];
 $NoofRSMSSel=$_POST['NoofRSMS'];
 $NoofThermsSel=$_POST['NoofTherms'];
 $NoofPowerSel=$_POST['NoofPower'];
 
 echo("!!=".$ConfigSel);
?>

<div class="row">
    <h2 class="span8 offset2"> System Information for <?php echo $SysName; ?></h2>
</div>


<form action="./" method="POST">

    <div class="row" >
        <div class="span5">
            <label for="name" alt="test">Building ID and Location of System
                <?php
 
                    $query="Select buildingID,buildingName from buildings order by NumSysConfig,buildingID";
                    MySQL_Pull_Down($config,$query,"Building","buildingName","buildingID",$BldSel,"","span5","");
                ?>

            </label>
              <label for="Configuration">Configuration
                  <?php
echo($ConfigSel);
                    $query="Select DefaultValue,ItemName from SysConfigDefaults where ConfigGroup='Configuration' order by DefaultValue";
                    MySQL_Pull_Down($config,$query,"Configuration","ItemName","DefaultValue",$ConfigSel,"","span5","");
                  ?>
            </label>
            <label for="SysDescription">System Description
                <input name="SysDescription" type="text" class="span5" value="<?php echo $SysDescrpSel; ?>">
            </label>



            <label for="InstallDate">Install Date
                <input  name="InstallDate" type="text" class="span5" value="<?php echo $InstallDateSel; ?>">
            </label>

            <label for="Maintainer">Maintainer
               <?php
                    $query="Select Company from MaintainResource where Category='Maintainer'";
                    MySQL_Pull_Down($config,$query,"Maintainer","Company","Company",$MaintainerSel,"","span5","");
                 ?>
            </label>
             <HR>
            <label for="DAMID">DAMID
                <input name="DAMID" type="text" class="span5" value="<?php echo $DAMIDSel; ?>">
            </label>
            <label for="NoofTherms">Number of Thermostats
                <input name="NoofTherms" type="text" class="span5" value="<?php echo $NoofThermsSel; ?>">
            </label>
        </div>



        <div class="row" >
           <div class="span5">
            <label for="SystemType">System Type
                <?php
                  $query="Select ItemName,DefaultValue from SysConfigDefaults where ConfigGroup='Systemtype' order by DefaultValue";
                    MySQL_Pull_Down($config,$query,"Configuration","ItemName","DefaultValue",$ConfigSel,"","span5","");
                 ?>
            </label>
            <label for="PlatformID">Platform ID
               <?php
                  $query="Select ItemName,DefaultValue from SysConfigDefaults where ConfigGroup='PlatformID' order by DefaultValue";
                    MySQL_Pull_Down($config,$query,"PlatformID","ItemName","DefaultValue",$PlatformIDSel,"","span5","");
                 ?>
            </label>
             <label for="HeatExchange">Heat Exchange Unit
                 <?php
                  $query="Select ItemName,DefaultValue from SysConfigDefaults where ConfigGroup='HeatExchanger' order by DefaultValue";
                   MySQL_Pull_Down($config,$query,"Configuration","ItemName","DefaultValue",$HeatExchangeSel,"","span5","");
                 ?>
            </label>
             <label for="Installer">Installer
                <?php
                  $query="Select Company from MaintainResource where Category='Installer' ";
                  MySQL_Pull_Down($config,$query,"Company","Company","Company",$InstallerSel,"","span5","");
                 ?>
            </label>

            <label for="LocofMain">Location of Main System
                <input name="LocofMain" type="text" class="span5" value="<?php echo $LocofMainSel; ?>">
            </label>


             <hr>

            <label for="NoofRSMS">Number of RSMs
                <input name="NoofRSMS" type="text" class="span5" value="<?php echo $NoofRSMSSel; ?>">
            </label>

            <?php
             //   for($i=0;$i<$systemInfo['NumofRSM'];$i++){
            //        $rsmNum = "LocationRSM" . ($i + 1);
             //       echo "<label for=\"locationRSM . ($i+1) . \">Location of RSM " . ($i + 1) . "
             //           <input readonly=\"true\" type=\"text\" class=\"span6\" value=\"" . $systemInfo[$rsmNum] . "\">";
                    //echo $systemInfo['$rsmNum'];
         //       }
            ?>


             <label for="NoofPower">Number of Power Meters
                <input  name="NoofPower" type="text" class="span5" value="<?php echo $NoofPowerSel; ?>">
            </label>
        </div>
       </div>


    <div class="row">
        <div class="span10 offset1">
            <button type="submit" class="btn btn-success">
                <i class="icon-pencil icon-white"></i>
                Update
            </button>
            <a href="../" class="btn pull-right">
                <i class="icon-remove"></i>
                Cancel
            </a>
        </div>
    </div>
 </div>
    <input type="hidden" name="customerID" value="<?=$customerInfo['customerID']?>">
</form>

<?php
//require_once('../../includes/footer.php');
?>
