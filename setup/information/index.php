

<?php
/**
 *------------------------------------------------------------------------------
 *  Building Information Setup Index Page
 *------------------------------------------------------------------------------
 *
 *
**/
/*
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

  if($DefMess==true) {$dropdown .= "\r\n<option value='-'>Select a ".$InputName."</option>";}
foreach ($PDList as $row) {
  //  echo($row[$SelField]."||".$SelValue);
    $Sel= SelectPD($row[$SelField],$SelValue);
  //echo($Sel);
   $dropdown .= "\r\n<option value='".$row[$SelField]."' ".$Sel.">".$row[$DisplayField]."</option>";
    }

   $dropdown .= "\r\n</select>";

   echo $dropdown;

}
*/
ini_set('display_errors','0');
// goes to home page if trying to set up new system without proper authorization
//if($_SESSION['authLevel'] < 3 && $NewSystem==false) { //already in parent page
    //gtfo($config);
//}

if(!$update){
  //clear session valuables for RSM etc
  $_SESSION['SetupRSM']=0;
  $_SESSION['SetupPwr']=0;
  $_SESSION['SetupTherm']=0;

  //parameter feedback and error checker
  $DBUpdateok=true;
  for ($i=0;$i<15;$i++) $errflag[$i]=false;

  $SysNameSel=$_POST['SysName'];
  if ($SysNameSel=="") $errflag[0]=true;

  $ConfigSel=$_POST['Configuration'];
  if ($ConfigSel=="-") $errflag[1]=true;

  $SysDescrpSel=$_POST['SysDescription'];
  if ($SysDescrpSel=="") $errflag[2]=true;

  $InstallDateSel=$_POST['InstallDate'];
  //if ($InstallDateSel=="")   {$errflag[3]=true;} //else  {if (IsDate($mydate)==false)  {$errflag[3]=true;}}


  if ($InstallDateSel=="") $errflag[3]=true;
  else{
    $ValidDate=date($InstallDateSel);
    if ($ValidDate==false) $errflag[3]=true;
  }


  $MaintainerSel=$_POST['Maintainer'];
  if ($MaintainerSel=="-") $errflag[4]=true;

  $DAMIDSel=$_POST['DAMID'];
  if ($DAMIDSel=="" or strlen($DAMIDSel)!=12) $errflag[5]=true;

  $NumofThermsSel=$_POST['NumofTherms'];
  if ($NumofThermsSel=="") $errflag[6]=true;
  else{
    $NumofThermsSel = (int)($NumofThermsSel);
    if (($NumofThermsSel>=6) or ($NumofThermsSel<=-1)) $errflag[6]=true;
  }

  $SysTypeSel=$_POST['SysType'];
  if ($SysTypeSel=="-") $errflag[7]=true;

  $PlatformIDSel=$_POST['PlatformID'];
  if ($PlatformIDSel=="-") $errflag[8]=true;

  $HeatExchangerSel=$_POST['HeatExchanger'];
  if ($HeatExchangerSel=="-") $errflag[9]=true;

  $InstallerSel=$_POST['Installer'];
  if ($InstallerSel=="-") $errflag[10]=true;

  $LocofMainSel=$_POST['LocofMain'];
  if ($LocofMainSel=="") $errflag[11]=true;


  $NumofRSMSSel=$_POST['NumofRSMS'];
  if ($NumofRSMSSel=="") $errflag[12]=true;
  else{
    $NumofRSMSSel = (int)($NumofRSMSSel);
    if (($NumofRSMSSel>=5) or ($NumofRSMSSel<=-1)) $errflag[12]=true;
  }

  $NumofPowerSel=$_POST['NumofPower'];
  if ($NumofPowerSel=="") $errflag[13]=true;
  else{
    $NumofPowerSel = (int)($NumofPowerSel);
    if (($NumofPowerSel>=8) or ($NumofPowerSel<=-1)) $errflag[13]=true;
  }

  if(!isset($_POST['submitInfo'])) $PostFlag=false;
  else $PostFlag=true;
  // set cumulative error flag
  $CumErr=false;
  for ($i=0;$i<15;$i++){
    $CumErr=$CumErr || $errflag[$i];
  }
}else{  //update existing system
    $query = "SELECT * FROM SystemConfig WHERE SysID = " . $_SESSION['SysID'];
    $sysConfig = $db -> fetchRow($query);

    $SysNameSel = $sysConfig['SysName'];
    $ConfigSel = $sysConfig['Configuration'];
    $SysDescrpSel = $sysConfig['SystemDescription'];
    $InstallDateSel = $sysConfig['InstallDate'];
    $MaintainerSel = $sysConfig['Maintainer'];
    $DAMIDSel = $sysConfig['DAMID'];
    $NumofThermsSel = $sysConfig['NumofTherms'];
    $SysTypeSel = $sysConfig['Systype'];
    $PlatformIDSel = $sysConfig['PlatformID'];
    $HeatExchangerSel = $sysConfig['HeatExchanger'];
    $InstallerSel = $sysConfig['Installer'];
    $LocofMainSel = $sysConfig['LocationMainSystem'];
    $NumofRSMSSel = $sysConfig['NumofRSM'];
    $NumofPowerSel = $sysConfig['NumofPowers'];
}

if(isset($_POST['submitInfo'])){// and $CumErr==false) {
    foreach($_POST as $key => $value){
        if(preg_match("/unit/",$key)){
            if(!empty($key)){
                //insert new system component
                $index = substr($key,4);
                $bind[':sysID']         = $_SESSION['SysID'];
                $bind[':unit']          = $_POST['unit' . $index];
                $bind[':function']      = $_POST['desc' . $index];
                $bind[':manufacturer']  = $_POST['manufacturer' . $index];
                $bind[':model']         = $_POST['model' . $index];
                $bind[':serial']        = $_POST['serial' . $index];
                $bind[':dateCode']      = $_POST['dateCode' . $index];
                $query = "INSERT INTO SysComponents (
                            SysID,          UnitName,       UnitFunction,
                            Manufacturer,   Model,          SerialNumber,   DateCode
                        ) VALUES (
                            :sysID,         :unit,          :function,
                            :manufacturer,  :model,         :serial,        :dateCode
                        )";
                $db -> execute($query,$bind);
            }
        }
    }
  //Exists
  if(isset($_POST['systemID'])){
    $Upquery = "UPDATE SystemConfig SET
    SysName ='".$SysNameSel."',SystemDescription='".$SysDescrpSel."',BuildingID=".$buildingID.",PlatformID=".$PlatformIDSel.",DAMID='".$DAMIDSel."',
    Systype='".$SysTypeSel."',Configuration=".$ConfigSel.",HeatExchanger='".$HeatExchangerSel."',InstallDate='".$InstallDateSel."',Installer='".$InstallerSel."',
    Maintainer='".$MaintainerSel."',NumofTherms =".$NumofThermsSel.",NumofPowers =". $NumofPowerSel.",NumofRSM =". $NumofRSMSSel .
    " WHERE SysID =". $_SESSION['SysID'];

    try {
      $response = $db -> execute($Upquery);
      $DBUpdateok=$response;
      //echo "<script type=\"text/javascript\">window.location.reload()</script>";
    }catch (Exception $e){
      throw new Exception;
      echo  "Error = ",0,$e;
    }
  }else{ //New
    $Inquery = "INSERT INTO SystemConfig(
            SysID,SysName,SystemDescription,BuildingID,PlatformID,DAMID,
            Systype,Configuration,HeatExchanger,LocationMainSystem,
            InstallDate,Installer,Maintainer,NumofTherms,NumofPowers,NumofRSM
            )VALUES(
            :sysID,:sysName,:sysDescription,:buildingID,:platformID,:DAMID,
            :sysType,:config,:heatExchanger,:locMain,:install,:installer,
            :maintainer,:numOfTherms,:numOfPowers,:numOfRSM)";

    $bind[':sysID'] = $_SESSION['NewID'];
    $bind[':sysName'] = $SysNameSel;
    $bind[':sysDescription'] = $SysDescrpSel;
    $bind[':buildingID'] = $buildingID;
    $bind[':platformID'] = $PlatformIDSel;
    $bind[':DAMID'] = $DAMIDSel;
    $bind[':sysType'] = $SysTypeSel;
    $bind[':config'] = $ConfigSel;
    $bind[':heatExchanger'] = $HeatExchangerSel;
    $bind[':locMain'] = $LocofMainSel;
    $bind[':install'] = $InstallDateSel;
    $bind[':installer'] = $InstallerSel;
    $bind[':maintainer'] = $MaintainerSel;
    $bind[':numOfTherms'] = $NumofThermsSel;
    $bind[':numOfPowers'] = $NumofPowerSel;
    $bind[':numOfRSM'] = $NumofRSMSSel;

    try {
        $response = $db -> execute($Inquery,$bind);
        $DBUpdateok=$response;
        $_SESSION['SetupStep'] = 2;
        $_SESSION['SysID'] = $_SESSION['NewID'];
    }catch (Exception $e){
        throw new Exception;
        echo  " Error = ",0,$e;
    }
  }
}else{  // new system set up before a post only
    $Readonly=false;
    // first determine next available SYSID
    $SysIDQuery ="Select SysID from SystemConfig order by SysID desc limit 1";
    $NextID = $db -> fetchRow($SysIDQuery);
    $NewID=$NextID['SysID']+1;
    // if ($_SESSION['buildingID']=="") {$BuildName="<font color=red> Building Not Selected</font>";} else {$BuildName=" in Building - ".$_SESSION['buildingID'];}
    // $SysName="New SysID= ".$NewID." ".$BuildName;
    $_SESSION['NewID']=$NewID;
}

if ($_SESSION['buildingID']=="") {$BuildName="<font color=red> Building Not Selected</font>";} else {$BuildName=" in Building - ".$_SESSION['buildingName'];}
$SysName="New SysID= ".$_SESSION['NewID']." ".$BuildName;
  //  $_SESSION['NewID']=$NewID;
?>

<div class="row">
    <h3 class="span10 "> <?=($update) ? "System Information for " . $SysNameSel . $BuildName : "System Information for New System" . $BuildName?></h3>
</div>


<form action="./" method="POST">

    <div class="row" >
        <div class="span5">
            <label for="name" >System Name
                  <?php   if ($errflag[0]==true && $PostFlag==true)  {echo("<font color=red><b>Error - Enter a System Name</b></font>");}  ?>
                  <input name="SysName" type="text" class="span5" value="<?=$SysNameSel?>" maxlength="45">
            </label>
              <label for="Configuration">Configuration
                  <?php
                    if ($errflag[1]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select a Configuration</b></font>");}
                    $query="Select AssignedValue,ItemName from SysConfigDefaults where ConfigSubGroup='Configuration' order by AssignedValue";
                    MySQL_Pull_Down($config,$query,"Configuration","ItemName","AssignedValue",$ConfigSel,true,"span5","");
                  ?>
            </label>
            <label for="SysDescription">System Description
               <?php  if ($errflag[2]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Enter a System Description</b></font>");}  ?>
                <input name="SysDescription" type="text" class="span5" value="<?=$SysDescrpSel?>">
            </label>



            <label for="InstallDate">Install Date<br>
                 <?php  if ($errflag[3]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Enter a valid Install Date</b></font>");} ?>
                <input  name="InstallDate" type="text" class="span5 datepick" value="<?=$InstallDateSel?>" maxlength="10">
            </label>

            <label for="Maintainer">Maintainer
               <?php
                    if ($errflag[4]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select a Maintainer</b></font>");}
                    $query="Select Company from MaintainResource where Category='Maintainer'";
                    MySQL_Pull_Down($config,$query,"Maintainer","Company","Company",$MaintainerSel,true,"span5","");
                 ?>
            </label>
             <HR>
            <label for="DAMID">DAMID
                 <?php if ($errflag[5]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Enter a valid 12 Digit DAMID in Hex</b></font>");} ?>
                <input name="DAMID" type="text" class="span5" value="<?=$DAMIDSel?>" maxlength="12">
            </label>
            <label for="NumofTherms">Number of Thermostats
                 <?php if ($errflag[6]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Enter a valid Number of Thermostats between 0 and 5</b></font>");} ?>
                <input name="NumofTherms" type="text" class="span5" value="<?=$NumofThermsSel?>">
            </label>
        </div>


        <div class="row" >
           <div class="span5">
            <label for="SystemType">System Type
                <?php
                  if ($errflag[7]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select a System Type</b></font>");}
                  $query="Select ItemName,AssignedValue from SysConfigDefaults where ConfigSubGroup='Systemtype' order by AssignedValue";
                    MySQL_Pull_Down($config,$query,"SysType","ItemName","AssignedValue",$SysTypeSel,true,"span5","");
                 ?>
            </label>
            <label for="PlatformID">Platform ID
               <?php
                 if ($errflag[8]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select a Platform ID</b></font>");}
                  $query="Select ItemName,AssignedValue from SysConfigDefaults where ConfigSubGroup='PlatformID' order by AssignedValue";
                    MySQL_Pull_Down($config,$query,"PlatformID","ItemName","AssignedValue",$PlatformIDSel,true,"span5","");
                 ?>
            </label>
             <label for="HeatExchanger">Heat Exchange Unit
                 <?php
                  if ($errflag[9]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select a HeatExchanger</b></font>");}
                  $query="Select ItemName from SysConfigDefaults where ConfigSubGroup='HeatExchanger' order by AssignedValue";
                   MySQL_Pull_Down($config,$query,"HeatExchanger","ItemName","ItemName",$HeatExchangerSel,true,"span5","");
                 ?>
            </label>
             <label for="Installer">Installer
                <?php
                  if ($errflag[10]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select an Installer</b></font>");}
                  $query="Select Company from MaintainResource where Category='Installer' ";
                  MySQL_Pull_Down($config,$query,"Installer","Company","Company",$InstallerSel,true,"span5","");
                 ?>
            </label>

            <label for="LocofMain">Location of Main System
               <?php   if ($errflag[11]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Enter Location of Main System</b></font>");} ?>
                <input name="LocofMain" type="text" class="span5" value="<?=$LocofMainSel?>" maxlength="45">
            </label>


             <hr>

            <label for="NumofRSMS">Number of RSMs
               <?php    if ($errflag[12]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Enter Num. of RSMs between 0 and 4 </b></font>");} ?>
                <input name="NumofRSMS" type="text" class="span5" value="<?=$NumofRSMSSel?>">
            </label>

            <?php
             //   for($i=0;$i<$systemInfo['NumofRSM'];$i++){
            //        $rsmNum = "LocationRSM" . ($i + 1);
             //       echo "<label for=\"locationRSM . ($i+1) . \">Location of RSM " . ($i + 1) . "
             //           <input readonly=\"true\" type=\"text\" class=\"span6\" value=\"" . $systemInfo[$rsmNum] . "\">";
                    //echo $systemInfo['$rsmNum'];
         //       }
            ?>


             <label for="NumofPower">Number of Power Meters
               <?php    if ($errflag[13]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Enter Num. of Power Meters between 0 and 8</b></font>");} ?>
                <input  name="NumofPower" type="text" class="span5" value="<?=$NumofPowerSel?>">
            </label>

              <label for="AngMuxEn">Enable AnalogMux
               <?php    if ($errflag[14]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select Enable/Disable</b></font>");}
                    $query="Select AssignedValue,ItemName from SysConfigDefaults where ConfigSubGroup='AngMuxEnable' order by AssignedValue";
                    MySQL_Pull_Down($config,$query,"AngMuxEn","ItemName","AssignedValue",$AngMuxEnSel,false,"span5","");
               ?>
            </label>
            <input type="hidden" name="submitInfo" value="true">
        </div>
       </div>
    <div class="span12">
        <a class="btn btn-link" id="addSysComponent" onclick="AddSysComponent(this.id)">+Add System Component</a>
    </div>


    <div class="row">
        <div class="span10 offset1" style="margin-top:20px">
            <button type="submit" class="btn btn-success">
                <i class="icon-pencil icon-white"></i>
                Update
            </button>
                <?php  if ($PostFlag==true)

                    if($DBUpdateok==true and $CumErr==false)   {
                        //echo("<font color='blue'><b> Update Successful <BR> Close and Proceed to Sensor Mapping</b></font>");
                         $_SESSION['SetupRSM']=$NumofRSMSSel;
                         $_SESSION['SetupPwr']=$NumofPowerSel;
                         $_SESSION['SetupTherm']=$NumofThermsSel;
                    }
                    else
                    {
                         //echo("<font color='red'><b> Update Failed - Correct Errors and Resubmit</b></font>");
                         $_SESSION['SetupRSM']=0;
                         $_SESSION['SetupPwr']=0;
                         $_SESSION['SetupTherm']=0;
                    }
                // }
                ?>

            <a href="../" class="btn pull-right">
                <i class="icon-remove"></i>
                Cancel
            </a>
        </div>
    </div>
 </div>
    <input type="hidden" name="customerID" value="<?=$customerInfo['customerID']?>">
    <input type="hidden" name="buildingID" value="<?=$buildingID?>">
    <?php if($update){ ?><input type="hidden" name="systemID" value="<?=$_SESSION['SysID']?>"><?php } ?>
</form>

<?php
//require_once('../../includes/footer.php');
?>
