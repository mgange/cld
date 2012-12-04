<?php
/**
 *------------------------------------------------------------------------------
 * Status Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');

checkSystemSet($config);

require_once('../includes/header.php');

    $dateTimeOffset = 5;

    $db = new db($config);
    $SysID=$_SESSION["SysID"];
    // set currflag if displaying most current status
    if (isset($_GET['id'])) {$CurrFlag=false;} else {$CurrFlag=true;}

    // first get DAMID for this System from SysMap
    $query = "Select * from SystemConfig, buildings where
              buildings.buildingID=SystemConfig.BuildingID and SystemConfig.SysID=".$SysID;
    $sysDAMID = $db -> fetchRow($query);
    $SysConfig=$sysDAMID['Configuration'];
    $openloop   = false;
    $openloopdw = false;
    $closedloop = false;

    // selects which system diagram to display
   switch  ($SysConfig)
   {
    case 1:
      $openloopdw=true;
        break;
    case 2:
       $openloop=true;
        break;
    case 3:
       $closedloop=true;

   }




    if(isset($_GET['z'])){
      switch($_GET['z']){
        case 1:
          $zone = 1;
          break;
        case 2:
          $zone = 2;
          break;
        case 3:
          $zone = 3;
          break;
        case 4:
          $zone = 5;
          break;
      }
    }
      else {$zone=0;
    }

    if(isset($_GET['id'])){
      //Get date and time for passed header id
      $query = "SELECT TimeStamp, DateStamp FROM SourceHeader WHERE Recnum = " . $_GET['id'];
      $result = $db -> fetchRow($query);
      //convert date and time to unix
      $query = "SELECT UNIX_TIMESTAMP('" . $result['DateStamp'] . " " . $result['TimeStamp'] . "') AS UNIX";
      $result = $db -> fetchRow($query);
      $unixTime = $result['UNIX'];
      //Get date and time for x number of seconds after header date/time
      $query = "SELECT TIME(FROM_UNIXTIME(" . ($unixTime + $dateTimeOffset) . ")) AS time, DATE(FROM_UNIXTIME(" . ($unixTime + $dateTimeOffset) . ")) AS date";
      $result = $db -> fetchRow($query);
      $timeAfter = $result['time'];
      $dateAfter = $result['date'];
      //Get date and time for x number of seconds before header date/time
      $query = "SELECT TIME(FROM_UNIXTIME(" . ($unixTime - $dateTimeOffset) . ")) AS time, DATE(FROM_UNIXTIME(" . ($unixTime - $dateTimeOffset) . ")) AS date";
      $result = $db -> fetchRow($query);
      $timeBefore = $result['time'];
      $dateBefore = $result['date'];
      //Produce queries within a certain time frame
      //query4 limited to 5 groups of records based on max 4 RSMs and 1 DAM
      $query0 = "SELECT * FROM SourceHeader, SourceData0 WHERE SourceHeader.SysID = " . $SysID . " AND SourceData0.HeadID = SourceHeader.Recnum AND SourceHeader.TimeStamp BETWEEN '" . $timeBefore . "' AND '" . $timeAfter . "' AND SourceHeader.DateStamp BETWEEN '" . $dateBefore . "' AND '" . $dateAfter . "' LIMIT 1";
      $query4 = "SELECT * FROM SourceHeader, SourceData4 WHERE SourceHeader.SysID = " . $SysID . " AND SourceData4.HeadID = SourceHeader.Recnum AND SourceHeader.TimeStamp BETWEEN '" . $timeBefore . "' AND '" . $timeAfter . "' AND SourceHeader.DateStamp BETWEEN '" . $dateBefore . "' AND '" . $dateAfter . "' LIMIT 5";
      $queryCalc = "SELECT * FROM SourceHeader, SensorCalc WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.RecNum = SensorCalc.HeadID AND SourceHeader.TimeStamp BETWEEN '" . $timeBefore . "' AND '" . $timeAfter . "' AND SourceHeader.DateStamp BETWEEN '" . $dateBefore . "' AND '" . $dateAfter . "' LIMIT 1";
      // Query for RSMs 1, 2, 3, or 5
      if(isset($zone)) $query1 = "SELECT * FROM SourceHeader, SourceData1 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData1.HeadID AND SourceData1.SourceID = " . $zone . " AND SourceHeader.TimeStamp BETWEEN '" . $timeBefore . "' AND '" . $timeAfter . "' AND SourceHeader.DateStamp BETWEEN '" . $dateBefore . "' AND '" . $dateAfter . "' LIMIT 1";
    }else{
      $query0 = "SELECT * FROM SourceHeader, SourceData0 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData0.HeadID ORDER BY SourceHeader.DateStamp DESC,SourceHeader.TimeStamp  DESC LIMIT 1";
      $query4 = "SELECT * FROM SourceHeader, SourceData4 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData4.HeadID ORDER BY SourceHeader.DateStamp DESC,SourceHeader.TimeStamp  DESC LIMIT 5";
      $queryCalc = "SELECT * FROM SourceHeader, SensorCalc WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.RecNum = SensorCalc.HeadID ORDER BY SourceHeader.DateStamp Desc,SourceHeader.TimeStamp Desc limit 1";
      if(isset($zone)) $query1 = "SELECT * FROM SourceHeader, SourceData1 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData1.HeadID AND SourceData1.SourceID = " . $zone . " ORDER BY SourceHeader.DateStamp DESC,SourceHeader.TimeStamp DESC LIMIT 1";
      }
//SourceData4.SysGroup=2 and
      $NumGrpsin4 = $db -> numRows($query4);
    //  echo("ROW".$NumGrpsin4);
      $sysStatus4 = $db -> fetchAll($query4);
      $NumGrpscalc = $db -> numRows($queryCalc);
    //   echo("E".$NumGrpscalc." ".$queryCalc);
      // get first row only which contains instantantious COP
      $sysCalc = $db -> fetchRow($queryCalc);
  //   echo("E".$NumGrpsin4."<BR>"."GRP-".$sysStatus4[SysGroup]."<BR>");


     $sysStatus0 = $db -> fetchRow($query0);
    // pprint($sysStatus0);
     if (isset($zone)) {
         $sysStatus1 = $db -> fetchRow($query1);
         $Row = $db -> numRows($query1);
    
          }
 //    sStatus4 = $db -> fetchRow($query4);


     // Not using Calculations for now
  //   $sysStatusCalc = $db -> fetchRow($queryCalc);
  //  echo("0--".$sysStatus0[HeadID]);
  //   echo("<BR>4--".$sysStatus4[Recnum]);
     //get next and previous recnum's
      $queryPrev = "SELECT SourceHeader.Recnum FROM SourceHeader,SourceData0 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData0.HeadID AND SourceHeader.DateStamp <= '" . $sysStatus0['DateStamp'] . "' AND SourceHeader.TimeStamp < '" . $sysStatus0['TimeStamp'] . "' ORDER BY DateStamp DESC,TimeStamp DESC LIMIT 1";
      $queryNext = "SELECT Recnum FROM SourceHeader WHERE SysID = " . $SysID . " AND SourceHeader.DateStamp >= '" . $sysStatus0['DateStamp'] . "' AND SourceHeader.TimeStamp > '" . $sysStatus0['TimeStamp'] . "' ORDER BY DateStamp ASC,TimeStamp ASC LIMIT 1";
      $result = $db -> fetchRow($queryPrev);
      $prev = $result['Recnum'];
      $result = $db -> fetchRow($queryNext);
      $next = $result['Recnum'];


     $SysName=$sysDAMID[SysName];
     $SysLocation=$sysDAMID[address1]." ".$sysDAMID[address2]." ".$sysDAMID[city]." ".$sysDAMID[state];

    // determine number of zones and which one is displayed
 if (isset($_GET['z']))  {$SysZNum=$_GET['z'];} else {$SysZNum=0;}
     $SysLocMain=$sysDAMID[LocationMainSystem];
     if ($SysZNum==0) {$SysZone="Main";}
     if ($SysZNum >= 1) $SysZone="RSM " . (($SysZNum == 4) ? ($SysZNum + 1) : $SysZNum);
     $NumRSM=$sysDAMID[NumofRSM];

    // define arrays for data and labels and positions etc
       $LblA=array($Pageelem);
       $ValA=array($Pageelem);
       $ShwA=array($Pageelem);
       $ForA=array($Pageelem);
       $SizA=array($Pageelem);
       $PosAX=array($Pageelem);
       $PosAY=array($Pageelem);
       $MapA =array($Pageelem);
       $LolmtA = array($Pageelem);
       $UplmtA = array($Pageelem);
       $AlertFactor=array($Pageelem);
       $ValB= array($Pageelem);
       $Title= array($Pageelem);
       $SStatus= array($Pageelem);

// get positions and labels for this page from Web Reference table
     // first get total number of page positions
      $querypos="Select * from WebRefTable where WebPageName='StatusDB' and WebSubPageName='Main'";
      $Pageelem = $db -> numRows($querypos);
     // now get positions for main page
      $PosMain = $db -> fetchAll($querypos);
      // define pos Array
      $i=0;
      foreach($PosMain as $resultRow) {
          $i=$resultRow['WebPagePosNo'];
          $PosAX[$i]=$resultRow['PageLocX'];
          $PosAY[$i]=$resultRow['PageLocY'];
          $LblA[$i] =$resultRow['SensorName'];
          $ForA[$i] =$resultRow['Format'];

     }
    // constants for label formatting
     $lf="<br>";  
     $cr=chr(13);  // line feed for alternate titles
     
   // fixed fields and labels for this page
    
     $systemInfo=$SysName." - ". $SysLocation;
     $systemDesc=$sysDAMID[SystemDescription].$lf.$sysDAMID[HeatExchangeUnit].$lf.
                 "Location-".$sysDAMID[LocationMainSystem].$lf."Main DAMID-".$sysDAMID[DAMID].$lf.
                 "RSMs-".$sysDAMID[NumofRSM];


     $LblA[0]="Date Time";
     $SDateTime=$sysStatus0[DateStamp]." ".$sysStatus0[TimeStamp];
     $SDateTime = date_create($SDateTime);
     $ValA[0]=date_format($SDateTime, 'm/d/Y g:i:s A');
     $ShwA[0] =true;
     $SStatus[0]=1;
     $LblA[12]="System Information";
     $ValA[12]=$systemInfo;
     $ShwA[12]=true;
     $SStatus[12]=1;
     $LblA[13]="System Description";
     $ShwA[13]=true;
     $SStatus[13]=1;
     $ValA[13]=$systemDesc;
     $LblA[29]="ThermStat Mode";
     
     $LblA[38]="System Status";
     
  //   $LblA[40]="System COP";
   //  $LblA[46]="Heat Pump COP";

     $ShwA[29]=true;
     $SStatus[29]=1;
     $ShwA[38]=true;
     $SStatus[38]=1;
  //  $ShwA[40]=true;
 //  $ShwA[46]=true;
  // end of fixed fields for this page
   //     $i=0;
    // seven separate loop parms to get default and unique data with a maximum of 4 loopa congruently
    // loops 1 and 2 only for main page
    // Loop 0 - default data from main also covers Main and common values from main for RSM display
    // Loop 1 - unique data from Main for a given system
    // Loop 2 - RSM Defaults   field with RSM reference from source 1 and  source 0,4 in default system (ie Flow)
    // Loop 3 - RSM Uniques  fields for a given system 
    
    // first calc number of required loops   2 passes only for main 4 for all RSMs
   if ($zone>=1) {$imax=4;} else {$imax=2;}
 
   for ($i=0;$i<$imax;$i++)
   {
        $DeftMapqueryZ0="Select WebPagePosNo, SourceID, WebRefTable.SensorLabel, SensorColName,SensorAddress,SensorActive,
                       SensorUnits,AlarmLoLimit,AlarmUpLimit,AlertPercent,SenAdjFactor,SenDBFactor,Format,Inhibit,SensorStatus,SysMap.Recnum,WebSubPageName,WebRefTable.SensorName from SysMap, WebRefTable
                       where SysMap.SensorRefName = WebRefTable.SensorName and
                        WebPageName='StatusDB' and SysMap.SysID=0 and SensorActive=1 and (SourceID=0 or SourceID=4 or SourceID=5) and WebSubPageName='Main' order by WebPagePosNo,SourceId";
        $DeftMapqueryZ1="Select WebPagePosNo, SourceID, WebRefTable.SensorLabel, SensorColName,SensorAddress,SensorActive,
                       SensorUnits,AlarmLoLimit,AlarmUpLimit,AlertPercent,SenAdjFactor,SenDBFactor,Format,Inhibit,SensorStatus,SysMap.Recnum,WebSubPageName,WebRefTable.SensorName from SysMap, WebRefTable
                       where SysMap.SensorRefName = WebRefTable.SensorName and
                        WebPageName='StatusDB' and SysMap.SysID=0 and SensorActive=1 and  (SourceID= 0 or SourceID=1 or SourceID=4  or SourceID=5) and WebSubPageName='RSM' order by WebPagePosNo,SourceId";
   
      $UnqiMapqueryZ0="Select WebPagePosNo, SourceID, WebRefTable.SensorLabel, SensorColName,SensorAddress,SensorActive,
                       SensorUnits,AlarmLoLimit,AlarmUpLimit,AlertPercent,SenDBFactor,SenAdjFactor,Format,Inhibit,SensorStatus,WebSubPageName,WebRefTable.SensorName from SysMap, WebRefTable
                       where SysMap.SensorRefName = WebRefTable.SensorName and
                        WebPageName='StatusDB' and SysMap.SysID=".$SysID." and (SourceID=0 or SourceID=4 or SourceID=5) and WebSubPageName='Main' order by WebPagePosNo";
      $UnqiMapqueryZ1="Select WebPagePosNo, SourceID, WebRefTable.SensorLabel, SensorColName,SensorAddress,SensorActive,
                       SensorUnits,AlarmLoLimit,AlarmUpLimit,AlertPercent,SenDBFactor,SenAdjFactor,Format,Inhibit,SensorStatus,WebSubPageName,WebRefTable.SensorName from SysMap, WebRefTable
                       where SysMap.SensorRefName = WebRefTable.SensorName and
                        WebPageName='StatusDB' and SysMap.SysID=".$SysID." and WebSubPageName='RSM' order by WebPagePosNo";

  
      switch ($i)
      {
          case 0 : $Forvar= $db -> fetchAll($DeftMapqueryZ0);
              $rec0 = $db -> numRows($DeftMapqueryZ0);
                 
              break;
          case 1 : $Forvar= $db -> fetchAll($UnqiMapqueryZ0);
              $rec1 = $db -> numRows($UnqiMapqueryZ0);
        
              break;
          case 2 : $Forvar= $db -> fetchAll($DeftMapqueryZ1);
              $rec2 = $db -> numRows($DeftMapqueryZ1);
     
              break;
          case 3 : $Forvar= $db -> fetchAll($UnqiMapqueryZ1);
              $rec3 = $db -> numRows($UnqiMapqueryZ1);
       
              break;
      }
 
     
     foreach($Forvar as $resultRow)
         { 
         $SID=$resultRow[SourceID];
  
   //     if (($i<=1  and  ($SID==0 or $SID==4 or $SID==5))) 
      //  {
            $GetValue="";
           
            $SUnit=UnitLabel($resultRow[SensorUnits]);
            $SPos= $resultRow[WebPagePosNo];
            $LblA[$SPos]=$resultRow[SensorLabel]." ".$SUnit."<BR> ";
            $LolmtA[$SPos]=$resultRow[AlarmLoLimit];
            $UplmtA[$SPos]=$resultRow[AlarmUpLimit];
            $AlertFactor[$SPos]=$resultRow[AlertPercent];
            $SStatus[$SPos]=$resultRow[SensorStatus];
            $ShwA[$SPos]=((!$resultRow[Inhibit]) and ($resultRow[SensorActive]==1));
           
           
       //   echo($SPos."--".$resultRow[SensorStatus]."--".$ShwA[$SPos]."|");
            $ForA[$SPos]=$resultRow[Format];
            // get value and process
            $DBCol= $resultRow[SensorColName];
          // if ($i==1) {echo($DBCol)."-".$ShwA[$SPos]."-".$resultRow[Inhibit]."-".$resultRow[SensorStatus]."<BR>";}
           if ($LolmtA[$SPos]!=NULL) {$TLlim="Lo Limit: ".$LolmtA[$SPos];} else {$TLlim="";}
            if ($UplmtA[$SPos]!=NULL) {$TUlim="Up Limit: ".$UplmtA[$SPos].$cr;} else {$TUlim="";}
            if ($resultRow[SourceID] == 5) {$DataTable="SensorCalc";} else {$DataTable="SourceData".$resultRow[SourceID];}
            if ($resultRow[SourceID]== 4) {$Address="ModBus Addr:".$resultRow[SensorAddress].$cr;} else {$Address="";}
            
            
          switch ($resultRow[SensorStatus])
            {
                case 0: $SStat="Status: Inhibited";
                        break;
                case 1: $SStat="Status: Active Alarmed";
                        break;
                case 2: $SStat="Status: Active Not Alarmed";  
                        break;
                case 3: $SStat="Status: Active Alarm Off";  
                        break;    
                case 4: $SStat="Status: Inhibited For Maintenance";   
                        break;
                default : $SStat="Status: Undefined";
            }
            
       
            
            $Title[$SPos]=" Table: ".$DataTable.$cr."Field: ".$DBCol.$cr.$Address.$TUlim.$TLlim.$cr.$SStat;
           
            switch ($resultRow[SourceID])
            {   
                case 0: $GetValue=$sysStatus0[$DBCol];

                    break;
                case 1: $GetValue=$sysStatus1[$DBCol];
 
                    break;
                case 4:
                      foreach ($sysStatus4 as $modrow)
                {
                      if (($resultRow[SensorAddress]==$modrow[PwrSubAddress]) or  ($resultRow[SensorAddress]== $modrow[ThermSubAddress]))
                           {$GetValue=$modrow[$DBCol];}
 //echo("||||".$resultRow[SensorAddress]."=".$modrow[PwrSubAddress]."/".$modrow[ThermSubAddress]."/".$GetValue."<BR>");}
                }
                    break;
                case 5: $GetValue=$sysCalc[$DBCol];
                    
                    break;
            }
             // format calls here
             if ($ForA[$SPos]==0 )
             {

                 $ValA[$SPos]=number_format($GetValue*$resultRow[SenAdjFactor]/$resultRow[SenDBFactor],2);}
                 else {$ValA[$SPos]=$GetValue;}
           }         
       //  }
   }

  // Special Calculations
       //status logic
   // determine if in emgerency heat mode
   if ($ValA[41]==5) {$EM=1;} else {$EM=0;}


   $ValA[22]=Emerglogic($ValA[22],$EM);
   $ValA[23]=Emerglogic($ValA[23],$EM);
   $ValA[24]=Emerglogic($ValA[24],$EM);

   if ($EM==1) {$ValA[25]=1;}
   if ($ShwA[21] and $ShwA[22]and $ShwA[23] and $ShwA[24] and $ShwA[25] ) 
        { 
         $ValA[29]=Systemlogic($ValA[21],$ValA[22],$ValA[23],$ValA[24],$ValA[25],$EM);           
        } 
        else 
        {
         $ValA[29]="No Thermostat on RSM";
        }
   
   $ValA[38]=Systemlogic($ValA[30],$ValA[31],$ValA[32],$ValA[33],$ValA[34],false);
   $ValA[39]="";
 // cop reformat if null
 if ($ValA[40]=="") {$ValA[40]="--";} 
 if ($ValA[46]=="") {$ValA[46]="--";} 


    

// code for system image selection here?
       $exchangermode = 0;
       $exchnimage="../status/image/WebBackGroundHeatingMode.png";


       $SizA[0]=1.2;
     //  $SizA[25]=2.0;
     //  $SizA[27]=0.5;

       if ($exchangermode==1)
       {// open loop
            $exchnimage="../status/image/WebBackGroundHeatingMode.png";
       }

       function DisplayStatus($seqno,$label,$value,$xpos,$ypos,$lolimit,$uplimit,$alertfactor,$size,$show,$form,$title,$colorovride,$mainalert)
       {
        //  $alertfactorc=$alertfactor*0.01;   // alerts at 15% of limit
          $alertdelta=($uplimit-$lolimit)*$alertfactor*0.01;
         // pprint($seqno."-".$alertdelta."--");
         // Hide display
        if ($show != true or $show ==0) {$EngHide="hidden";} else {$EngHide="";}
     //echo($label."  ".$EngHide."<BR>");
               
         // set background color based on limits
        $BackColor=lightgreen;

//echo("H-".$seqno." ".$label."--".$EngHide."||");
    //    if (($lolimit=="")  and ($uplimit=="")) {$BackColor=lightblue;}
        // yellow alert lo limit
        if ( $lolimit!="" and ($value < ($lolimit+$alertdelta)))
           { $BackColor=yellow; }
           
        // yellow alert up limit
        // 
         if ($uplimit!="" and ($value > ($uplimit-$alertdelta)))
           { $BackColor=yellow; }
           
        // red alert lo limit
         if ($lolimit==""  and ($value < $lolimit)) 
           { $BackColor=red;}
          // red alert up limit 
         if ($uplimit!="" and ($value > $uplimit))
           { $BackColor=red;}  

         // no alerts
        
        if (($lolimit=="")  and ($uplimit=="")) {$BackColor=lightblue;}
        if ($colorovride!="") {$BackColor=$colorovride;}


        // set Fontcolor
        $FontColor=black;
        //Special formats
       if ($form == 3)  //digital display
           {

           if ($value == 1)
               {
                $BackColor=lightgreen;
               }
               else
               {
                $BackColor=white;
               }
            $value="&nbsp";
           }
         if ($form == 2)  // nobackground
              {
              $BackColor=white."; border: 0px solid white";
              }



         if ($size == "") {$size=1.2;}

   

       $labypos=$ypos-20;  // set label position above value
       $labxpos=$xpos+5;
       $label=str_pad($label,1," ",STR_PAD_LEFT);
       echo "<p class='label-status ".$EngHide."' style='top: ".$labypos."px; left: ".$xpos."px;'>".$label."</p>";
       echo "<p class='value-status ".$EngHide."' title='".$title."' style='top: ".$ypos."px; left: ".$labxpos."px;
                 background-color: ".$BackColor."; line-height: ".$size."em; '>".$value."</p>";
       if ($mainalert==true)
       {
          $altposx=$xpos+20;
          $altposy=$ypos+3;
          $altvalue="x";
          $altsize=0.5;
          $altBackColor="#FFA500";
          echo "<p class='value-status ".$EngHide."'  style='top: ".$altposy."px; left: ".$altposx."px;
                 background-color: ".$altBackColor."; line-height: ".$altsize."em; '>".$altvalue."</p>";
       }
 }



  function value_format ($Value,$Format,$SenAdj,$SenDBFact)
   {

      switch ($Format)
      {
          case 0:  //number
              $FormValue=number_format($Value*$SenAdj/$SenDBFact,2);
              break;
          case 1:  // Text w/back
              break;
          case 2:  // Text wo/back
              break;
          case 3:  // Dig
              break;
          case 4:  // Date Time
              break;
          case 5:  // Calc Syslogic
              break;
          case 6:  // Calc Energy
              break;
          default:

      }
      return $FormValue;
   }


       ?>

        <div class="row">
           <h1 class="span6 offset2">Status - <span class="building-name">System - <?php  echo $SysName." - ".$SysZone ?></span></h1>
            <p style="text-align:center"><?php
              if(isset($zone)){
                echo (isset($prev)) ? "<a href=\"./?id=" . $prev . "&z=" . $zone . "\"><img src=\"../img/backArrow.jpg\" /></a>&nbsp;" : "&nbsp;&nbsp;";
                echo (isset($next)) ? "<a href=\"./?id=" . $next . "&z=" . $zone . "\"><img src=\"../img/forwardArrow.jpg\" /></a>" : "";
              }else{
                echo (isset($prev)) ? "<a href=\"./?id=" . $prev . "\"><img src=\"../img/backArrow.jpg\" /></a>&nbsp;" : "&nbsp;&nbsp;";
                echo (isset($next)) ? "<a href=\"./?id=" . $next . "\"><img src=\"../img/forwardArrow.jpg\" /></a>" : "";
              }
            ?>
          </p>
        </div>
       <div class="row">


            <div class="status-container span10 offset1">

              <div class="status-Back map">
                <img src="<?php echo $exchnimage ?>" alt="Heat Exchanger">
                </div>
                <div class="status-OpenLoop <?php if ($openloop != true) {echo "hidden";}?>">
                    <img src="../status/image/WebOpenLoop.png" alt="Open Loop ">
                </div>
                  <div class="status-OpenLoopDryWell <?php if ($openloopdw != true) {echo "hidden";}?>">
                    <img src="../status/image/WebOpenLoopDryWell.png" alt="Open Loop Dry Well">
                </div>
                <div class="status-ClosedLoop <?php if ($closedloop != true) {echo "hidden";}?>">
                    <img src="../status/image/WebClosedLoop.png" alt="Closed Loop">
                </div>
                <?php
              

           function ColorOver($Stage)
                { 
                   $Color="#FF8888";
                   switch ($Stage)
                   {   case "System Off": $Color="#FFFFFF";
                          break;
                       case "Fan Only": $Color="#89FF5D";
                           break;
                       case "Stage 1 Heat": $Color="#E8C106";
                           break;
                       case "Stage 2 Heat": $Color="#FF8307";
                           break;
                       case "Emerg. Heat": $Color="#FF1111";
                           break;
                       case "Stage 3 Heat": $Color="#E85823";
                           break;
                       case "Stage 1 Cool": $Color="#1E9BFF";
                           break;
                       case "Stage 2 Cool": $Color="#0F48E8";
                           break;
                       case "Invalid State": $Color="#FF5555";
                           break;
                   }
             return $Color;
                }
                 
                

           for ($i=0;$i<$Pageelem+1;$i++)
           {
               // system status color override
          
               if ($LblA[$i]=="ThermStat Mode" or $LblA[$i]=="System Status")
                     {
                         $colorovride= ColorOver($ValA[$i]);
                        
                     }
                     else
                     { 
                         $colorovride="";
                     }  
                     
 // determine current status of sensors display only when status page is on current status
                $alerttemp=false;
                if (($SStatus[$i]%3)==0 and $CurrFlag==true)  // display x on mode 0 and 3
                {
                    $alerttemp=true;
                }
                     
                
                 DisplayStatus($i,$LblA[$i],$ValA[$i],$PosAX[$i],$PosAY[$i],$LolmtA[$i],$UplmtA[$i],$AlertFactor[$i],$SizA[$i],$ShwA[$i],$ForA[$i],$Title[$i],$colorovride,$alerttemp);
                
           }



                ?>
            </div>

             <div class="link_RSM">

                <p class="align-center">
                    <?php
                      if ($CurrFlag!=true) {echo "<a href=''./?id=0>Current Status</a>";}
                      echo "<BR>";
                      echo "<a href='";
                      echo $config['base_domain'] . $config['base_dir'];
                      echo "performance";
                      if(isset($_GET['id'])) {
                        $dt = strtotime($ValA[0]);
                        echo '?date=' . date('Y-m-d', $dt);
                        echo '&time=' . date('H:i:s', $dt);
                      }
                      echo "'>Performance</a>";
                      echo "<BR>";
                      for($i=0;$i<$NumRSM+1;$i++){
                      if($SysZNum != $i){
                          if($i == 0) $Zname="Main";
                          else $Zname = "RSM-" . (($i == 4) ? ($i + 1) : $i);
                          echo "<a href=\"./?id=";
                          if(isset($_GET['id'])) echo $_GET['id'];
                          else echo $sysStatus0['HeadID'];
                          if($i != 0) echo "&z=" . $i . "\">" . $Zname . "</a>";
                          else echo "\">" . $Zname . "</a>";
                          echo "<BR>";
                        }
                      }
                    ?>
                </p>
            </div>
        </div>


<?php
require_once('../includes/footer.php');
?>
