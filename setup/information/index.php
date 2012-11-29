

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

// check to see if this is a new system setup
 if (isset($_SESSION['SetUpNew'])) { $NewSystem=$_SESSION['SetUpNew'];} else {$NewSystem=false;}
 // if not a new system set up check to see if a system has been selected if not refers to choose system option
if  (NewSystem==false) {checkSystemSet($config);}
// goes to home page if trying to set up new system without proper authorization
if($_SESSION['authLevel'] < 3 && $NewSystem==false) {
    gtfo($config);
}

 //$_SESSION['BuildStart']=true;
//clear session valuables for RSM etc
 $_SESSION['SetupRSM']=0;
 $_SESSION['SetupPwr']=0;
 $_SESSION['SetupTherm']=0;

//parameter feedback and error checker
 $DBUpdateok=true;
for ($i=0;$i<15;$i++){
      $errflag[$i]=false;
}

 $SysNameSel=$_POST['SysName'];
 if ($SysNameSel=="")          {$errflag[0]=true;} 

 $ConfigSel=$_POST['Configuration'];
 if ($ConfigSel=="-")       {$errflag[1]=true;}
 
 $SysDescrpSel=$_POST['SysDescription']; 
 if ($SysDescrpSel=="")    {$errflag[2]=true;}
 
 $InstallDateSel=$_POST['InstallDate'];
 //if ($InstallDateSel=="")   {$errflag[3]=true;} //else  {if (IsDate($mydate)==false)  {$errflag[3]=true;}}
 
 
 if ($InstallDateSel=="") 
     {$errflag[3]=true;} 
 else 
     { 
       $ValidDate=date($InstallDateSel);
     
       if ($ValidDate==false) {$errflag[3]=true;}
     }
  
 
 $MaintainerSel=$_POST['Maintainer'];
 if ($MaintainerSel=="-")    {$errflag[4]=true;} 
  
 $DAMIDSel=$_POST['DAMID'];
 if ($DAMIDSel=="" or strlen($DAMIDSel)!=12)  {$errflag[5]=true;} 
  
 $NumofThermsSel=$_POST['NumofTherms'];
 if ($NumofThermsSel=="") 
     {$errflag[6]=true;} 
 else 
     { $NumofThermsSel = (int)($NumofThermsSel);
       if (($NumofThermsSel>=6) or ($NumofThermsSel<=-1)) {$errflag[6]=true;}
     }
  
 $SysTypeSel=$_POST['SysType'];
 if ($SysTypeSel=="-")       {$errflag[7]=true;}
 
 $PlatformIDSel=$_POST['PlatformID'];
 if ($PlatformIDSel=="-")    {$errflag[8]=true;}
 
 $HeatExchangerSel=$_POST['HeatExchanger'];
 if ($HeatExchangerSel=="-") {$errflag[9]=true;}  
 
 $InstallerSel=$_POST['Installer'];
 if ($InstallerSel=="-")     {$errflag[10]=true;}
 
 $LocofMainSel=$_POST['LocofMain'];
 if ($LocofMainSel=="")      {$errflag[11]=true;}
 
 
 $NumofRSMSSel=$_POST['NumofRSMS'];
 if ($NumofRSMSSel=="") 
     {$errflag[12]=true;} 
 else 
     { $NumofRSMSSel = (int)($NumofRSMSSel);
       if (($NumofRSMSSel>=5) or ($NumofRSMSSel<=-1)) {$errflag[12]=true;}
     }  
 
$NumofPowerSel=$_POST['NumofPower'];
 if ($NumofPowerSel=="") 
     {$errflag[13]=true;} 
 else 
     { $NumofPowerSel = (int)($NumofPowerSel);
       if (($NumofPowerSel>=8) or ($NumofPowerSel<=-1)) {$errflag[13]=true;}
     }
 
 $AngMuxEnSel=$_POST['AngMuxEn'];
 if (AngMuxEnSel=="") {$errflag[14]=true;}
     
 
 // set post flag
  /*   
     if (isset($_SESSION['SETSUBMIT']))
         {   echo("SET") ;
            if ($_SESSION['SETSUBMIT']==true)
               {        
                    $_SESSION['SETSUBMIT']=false;  
                    $PostFlag=true;
                     echo("TRUE") ;

               } else {$PostFlag=false;    echo("F1") ;            }
     
         } else
         {         
          $_SESSION['SETSUBMIT']=false;
          $PostFlag=false;
                          echo("F2") ;   
         }
   * */
  
 if(!isset($_POST['submitInfo'])) {$PostFlag=false;} else {$PostFlag=true;} 
// set cumulative error flag
 $CumErr=false;
 for ($i=0;$i<15;$i++)
 {
   $CumErr=$CumErr || $errflag[$i];   
   
 }  


   if(count($_POST) > 0 and $CumErr==false) {
       //first look for existing record
        $query="Select SysID from SystemConfig where SysID=".$_SESSION['NewID'];
       
     
        //Exists
        if($db -> numRows($query) > 0) {
           
        $Upquery = "UPDATE SystemConfig SET
        SysName ='".$SysNameSel."',SystemDescription='".SysDescrpSel."',BuildingID=".$_SESSION['buildingID'].",PlatformID=".$PlatformIDSel.",DAMID='".$DAMIDSel."',
        Systype='".$SysTypeSel."',Configuration=".$ConfigSel.",HeatExchanger=".$HeatExchangerSel.",InstallDate='".$InstallDateSel."',Installer='".$InstallerSel."',
        Maintainer='".$MaintainerSel."',NumofTherms =".$NumofThermsSel.",NumofPowers =". $NumofPowerSel.",NumofRSM =". $NumofRSMSSel .
        " WHERE SysID =". $_SESSION['NewID'];
      // echo($Upquery) ;
     try {
         $response = $db -> execute($Upquery);
          $DBUpdateok=$response;    
            
         } 
          catch (Exception $e)
         {
          throw new Exception;
            echo  "Error = ",0,$e;
         }
        }
   else //New
        {
        $Inquery = "Insert into SystemConfig (SysID,SysName,SystemDescription,BuildingID,PlatformID,DAMID,Systype,Configuration,HeatExchanger,LocationMainSystem,InstallDate,Installer,
                  Maintainer,NumofTherms,NumofPowers,NumofRSM)
                  Values (".$_SESSION['NewID'].",'".$SysNameSel."','".$SysDescrpSel."',".$_SESSION['buildingID'].",".$PlatformIDSel.",'".$DAMIDSel."','".$SysTypeSel."',"
                           .$ConfigSel.",'".$HeatExchangerSel."','".$LocofMainSel."','".$InstallDateSel."','".$InstallerSel."','".$MaintainerSel."',"
                           .$NumofThermsSel.",".$NumofPowerSel.",".$NumofRSMSSel.")";

    try {
          $response = $db -> execute($Inquery);
          $DBUpdateok=$response;    
          
         } 
          catch (Exception $e)
         {
          throw new Exception;
            echo  " Error = ",0,$e;
         }
        }
        $_SESSION['SystemComp'] = true;
    }

else  // new system set up before a post only
{
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
    <h3 class="span10 "> System Information for <?php echo $SysName; ?></h3>
</div>


<form action="./" method="POST">

    <div class="row" >
        <div class="span5">
            <label for="name" >System Name             
                  <?php   if ($errflag[0]==true && $PostFlag==true)  {echo("<font color=red><b>Error - Enter a System Name</b></font>");}  ?>
                  <input name="SysName" type="text" class="span5" value="<?php echo $SysNameSel; ?>" maxlength="45">
            </label>
              <label for="Configuration">Configuration
                  <?php
                    if ($errflag[1]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select a Configuration</b></font>");}    
                    $query="Select DefaultValue,ItemName from SysConfigDefaults where ConfigGroup='Configuration' order by DefaultValue";
                    MySQL_Pull_Down($config,$query,"Configuration","ItemName","DefaultValue",$ConfigSel,true,"span5","");
                  ?>
            </label>
            <label for="SysDescription">System Description
               <?php  if ($errflag[2]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Enter a System Description</b></font>");}  ?>
                <input name="SysDescription" type="text" class="span5" value="<?php echo $SysDescrpSel; ?>">
            </label>



            <label for="InstallDate">Install Date
                 <?php  if ($errflag[3]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Enter a valid Install Date</b></font>");} ?>
                <input  name="InstallDate" type="text" class="span5" value="<?php echo $InstallDateSel; ?>" maxlength="10">
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
                <input name="DAMID" type="text" class="span5" value="<?php echo $DAMIDSel; ?>" maxlength="12">
            </label>
            <label for="NumofTherms">Number of Thermostats
                 <?php if ($errflag[6]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Enter a valid Number of Thermostats between 0 and 5</b></font>");} ?>
                <input name="NumofTherms" type="text" class="span5" value="<?php echo $NumofThermsSel; ?>">
            </label>
        </div>



        <div class="row" >
           <div class="span5">
            <label for="SystemType">System Type
                <?php
                  if ($errflag[7]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select a System Type</b></font>");} 
                  $query="Select ItemName,DefaultValue from SysConfigDefaults where ConfigGroup='Systemtype' order by DefaultValue";
                    MySQL_Pull_Down($config,$query,"SysType","ItemName","DefaultValue",$SysTypeSel,true,"span5","");
                 ?>
            </label>
            <label for="PlatformID">Platform ID
               <?php
                 if ($errflag[8]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select a Platform ID</b></font>");} 
                  $query="Select ItemName,DefaultValue from SysConfigDefaults where ConfigGroup='PlatformID' order by DefaultValue";
                    MySQL_Pull_Down($config,$query,"PlatformID","ItemName","DefaultValue",$PlatformIDSel,true,"span5","");
                 ?>
            </label>
             <label for="HeatExchanger">Heat Exchange Unit
                 <?php
                  if ($errflag[9]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select a HeatExchanger</b></font>");} 
                  $query="Select ItemName,DefaultValue from SysConfigDefaults where ConfigGroup='HeatExchanger' order by DefaultValue";
                   MySQL_Pull_Down($config,$query,"HeatExchanger","ItemName","DefaultValue",$HeatExchangerSel,true,"span5","");
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
                <input name="LocofMain" type="text" class="span5" value="<?php echo $LocofMainSel; ?>" maxlength="45">
            </label>


             <hr>

            <label for="NumofRSMS">Number of RSMs            
               <?php    if ($errflag[12]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Enter Num. of RSMs between 0 and 4 </b></font>");} ?>
                <input name="NumofRSMS" type="text" class="span5" value="<?php echo $NumofRSMSSel; ?>">
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
                <input  name="NumofPower" type="text" class="span5" value="<?php echo $NumofPowerSel; ?>">
            </label>
             
              <label for="AngMuxEn">Enable AnalogMux 
               <?php    if ($errflag[14]==true and $PostFlag==true)  {echo("<font color=red><b>Error - Select Enable/Disable</b></font>");} 
                    $query="Select DefaultValue,ItemName from SysConfigDefaults where ConfigGroup='AngMuxEnable' order by DefaultValue";
                    MySQL_Pull_Down($config,$query,"AngMuxEn","ItemName","DefaultValue",$AngMuxEnSel,false,"span5","");
               ?>
            </label>
            <input type="hidden" name="submitInfo" value="true">
        </div>
       </div>


    <div class="row">
        <div class="span10 offset1">
            <button type="submit" class="btn btn-success">
                <i class="icon-pencil icon-white"></i>
                Update
            </button>
            <div class="offset3">
                <?php  if ($PostFlag==true)
                
                    if($DBUpdateok==true and $CumErr==false)   {                     
                        echo("<font color='blue'><b> Update Successful <BR> Close and Proceed to Sensor Mapping</b></font>");
                         $_SESSION['SystemComp']=true;
                         $_SESSION['SetupRSM']=$NumofRSMSSel;
                         $_SESSION['SetupPwr']=$NumofPowerSel;
                         $_SESSION['SetupTherm']=$NumofThermsSel;
                         $_SESSION['SETSUBMIT']=true;
                    }
                    else
                    {
                         echo("<font color='red'><b> Update Failed - Correct Errors and Resubmit</b></font>");
                         $_SESSION['SystemComp']=false;
                         $_SESSION['SetupRSM']=0;
                         $_SESSION['SetupPwr']=0;
                         $_SESSION['SetupTherm']=0;
                    }
                // }  
                ?>
            
            <a href="../" class="btn pull-right">
                <i class="icon-remove"></i>
                Cancel
            </a></div>
        </div>
    </div>
 </div>
    <input type="hidden" name="customerID" value="<?=$customerInfo['customerID']?>">
</form>

<?php
//require_once('../../includes/footer.php');
?>
