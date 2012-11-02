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
      $queryCalc = "SELECT * FROM SourceHeader, SensorCalc WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.RecNum = SensorCalc.RecNum AND SourceHeader.TimeStamp BETWEEN '" . $timeBefore . "' AND '" . $timeAfter . "' AND SourceHeader.DateStamp BETWEEN '" . $dateBefore . "' AND '" . $dateAfter . "' LIMIT 1";
      // Query for RSMs 1, 2, 3, or 5
      if(isset($zone)) $query1 = "SELECT * FROM SourceHeader, SourceData1 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData1.HeadID AND SourceHeader.SourceID = " . $zone . " AND SourceHeader.TimeStamp BETWEEN '" . $timeBefore . "' AND '" . $timeAfter . "' AND SourceHeader.DateStamp BETWEEN '" . $dateBefore . "' AND '" . $dateAfter . "' LIMIT 1";
    }else{
      $query0 = "SELECT * FROM SourceHeader, SourceData0 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData0.HeadID AND SourceHeader.SourceID = 0 ORDER BY SourceHeader.DateStamp DESC,SourceHeader.TimeStamp  DESC LIMIT 1";
      $query4 = "SELECT * FROM SourceHeader, SourceData4 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData4.HeadID AND SourceHeader.SourceID = 4  ORDER BY SourceHeader.DateStamp DESC,SourceHeader.TimeStamp  DESC LIMIT 5";
      $queryCalc = "SELECT * FROM SourceHeader, SensorCalc WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.RecNum = SensorCalc.RecNum ORDER BY DateStamp Desc,TimeStamp Desc limit 1";
      if(isset($zone)) $query1 = "SELECT * FROM SourceHeader, SourceData1 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData1.HeadID AND SourceHeader.SourceID = " . $zone . " ORDER BY SourceHeader.DateStamp DESC,SourceHeader.TimeStamp DESC LIMIT 1";
      }
//SourceData4.SysGroup=2 and
      $NumGrpsin4 = $db -> numRows($query4);
      $sysStatus4 = $db -> fetchAll($query4);
  //   echo("E".$NumGrpsin4."<BR>"."GRP-".$sysStatus4[SysGroup]."<BR>");


     $sysStatus0 = $db -> fetchRow($query0);
     if (isset($zone)) {$sysStatus1 = $db -> fetchRow($query1);}

 //    $sysStatus4 = $db -> fetchRow($query4);


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
       $ValB= array($Pageelem);


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

     // then get unique positions for RSM pages if any
     if ($zone >= 1) {

     }


     // Field to Data mappings
     // first get default values for Main Page
     $DeftMapquery="Select WebPagePosNo, SourceID, WebRefTable.SensorLabel, SensorColName,SensorAddress,
                       SensorUnits,AlarmLoLimit,AlarmUpLimit,SenAdjFactor,SenDBFactor,Format,Inhibit,SensorStatus from SysMap, WebRefTable
                       where SysMap.SensorRefName = WebRefTable.SensorName and
                        WebPageName='StatusDB' and SysMap.SysID=0 and SensorStatus=1 and (sourceID=0 or SourceID=4 )order by WebPagePosNo";

    $UnqiMapquery="Select WebPagePosNo, SourceID, WebRefTable.SensorLabel, SensorColName,SensorAddress,
                       SensorUnits,AlarmLoLimit,AlarmUpLimit,SenDbFactor,SenAdjFactor,Format,Inhibit,SensorStatus from SysMap, WebRefTable
                       where SysMap.SensorRefName = WebRefTable.SensorName and
                        WebPageName='StatusDB' and SysMap.SysID=".$SysID." order by WebPagePosNo";

     $MapMainDeft = $db -> fetchAll($DeftMapquery);
     $MapMainUniq = $db -> fetchAll($UnqiMapquery);
    // $UniqMain = $db -> fetchAll($UniqMapquery);
     // loop to define value maps
     // loop to define labels and unit, lo and up limits
     // Fixed Labels

     $lf="<br>";
   // fixed fields
     $systemInfo=$SysName." - ". $SysLocation;
     $systemDesc=$sysDAMID[SystemDescription].$lf.$sysDAMID[HeatExchangeUnit].$lf.
                 "Location-".$sysDAMID[LocationMainSystem].$lf."Main DAMID-".$sysDAMID[DAMID].$lf.
                 "RSMs-".$sysDAMID[NumofRSM];


     $LblA[0]="Date Time";
     $SDateTime=$sysStatus0[DateStamp]." ".$sysStatus0[TimeStamp];
     $SDateTime = date_create($SDateTime);
     $ValA[0]=date_format($SDateTime, 'm/d/Y g:i:s A');
     $ShwA[0] =true;
     $LblA[12]="System Information";
     $ValA[12]=$systemInfo;
     $ShwA[12]=true;
     $LblA[13]="System Description";
     $ShwA[13]=true;
     $ValA[13]=$systemDesc;
     $LblA[29]="ThermStat Mode";

     $LblA[38]="System Status";
     $LblA[40]="System COP";
     $LblA[46]="Heat Pump COP";

     $ShwA[29]=true;
     $ShwA[38]=true;
     $ShwA[40]=true;
     $ShwA[46]=true;

   $i=0;
   // default value loop for main
   for ($i=0;$i<2;$i++)
   {
      if($i==0)  {$Forvar=$MapMainDeft;} else {$Forvar=$MapMainUniq;}
     foreach($Forvar as $resultRow)
         {

            $SUnit=UnitLabel($resultRow[SensorUnits]);
            $SPos= $resultRow[WebPagePosNo];
            $LblA[$SPos]=$resultRow[SensorLabel]." ".$SUnit."<BR> ";
            $LolmtA[$SPos]=$resultRow[AlarmLoLimit];
            $UplmtA[$SPos]=$resultRow[AlarmUpLimit];
            $ShwA[$SPos]=(!$resultRow[Inhibit]  and $resultRow[SensorStatus]);
            echo(" B=".$i." -|".$ShwA[$SPos]."|".$resultRow[SensorLabel]."-".$resultRow[SensorStatus]."-- ");
            $ForA[$SPos]=$resultRow[Format];
            // get value and process
            $DBCol=   $resultRow[SensorColName];
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
                }
                    break;
            }
             // format calls here
             if ($ForA[$SPos]==0 )
                 {$ValA[$SPos]=number_format($GetValue*$resultRow[SenAdjFactor]/$resultRow[SenDBFactor],2);}
                 else {$ValA[$SPos]=$GetValue;}
       }

      // page and system unigue calls value loop
   }

  // Calculations
       //status logic

   if ($ValA[41]==5) {$EM=1;} else {$EM=0;}


   $ValA[22]=Emerglogic($ValA[22],$EM);
   $ValA[23]=Emerglogic($ValA[23],$EM);


   $ValA[24]=Emerglogic($ValA[24],$EM);

   if ($EM==1) {$ValA[25]=1;}
   $ValA[29]=Systemlogic($ValA[21],$ValA[22],$ValA[23],$ValA[24],$ValA[25],$EM);
   $ValA[38]=Systemlogic($ValA[30],$ValA[31],$ValA[32],$ValA[33],$ValA[34],false);
   $ValA[39]="";
    //COP replace with DB calls when ready
   $ValA[40]=COPCalc($ValA[15],$ValA[14],$ValA[17],$ValA[4],$ValA[43]);
   $ValA[46]=COPCalc($ValA[15],$ValA[14],$ValA[17],$ValA[4],0);









// code for system image selection here?
       $exchangermode = 0;
       $exchnimage="../status/image/WebBackGroundHeatingMode.png";



       // quick set up for query0  user else for query1 until mapping is complete

       if ($SysZNum == 0)  {
  // replaced by general mapping code above  replace else when completed
       }
       else{
       $SDateTime=$sysStatus0[DateStamp]." ".$sysStatus0[TimeStamp];
       $SDateTime = date_create($SDateTime);
       $ValA[0]=date_format($SDateTime, 'm/d/Y g:i:s A');
       $ValA[1]=number_format($sysStatus1[Senchan07]/100,2);
       $ValA[2]=number_format($sysStatus1[Senchan05]/100,2);
    // $ValA[3]=number_format($sysStatus4[Power02]/100,2);
    // $ValA[4]=number_format($sysStatus4[Power01]/100,2);
    // $ValA[5]=number_format($sysStatus4[Power03]/100,2);
    // $ValA[6]=number_format($sysStatus4[Power04]/100,2);
       $ValA[7]=number_format($sysStatus1[Senchan06]/100,2);
       $ValA[8]=number_format($sysStatus1[Senchan08]/100,2);
       $ValA[9]=number_format($sysStatus4[LCDTemp],2);
       $ValA[10]=number_format($sysStatus4[HeatingSetPoint],2);
       $ValA[11]=number_format($sysStatus4[CoolingTemp],2);
       $ValA[12]=$systemInfo;
       $ValA[13]=$systemDesc;
       $ValA[14]=number_format($sysStatus1[Senchan03]/100,2);
       $ValA[15]=number_format($sysStatus1[Senchan01]/100,2);
       $ValA[16]=number_format($sysStatus0[FlowPress02]/100,2);
       $ValA[17]=number_format($sysStatus0[FlowPress04]/100,2);
       $ValA[18]=$sysStatus0[DigIn06];
       $ValA[19]=$sysStatus0[DigIn07];
       $ValA[20]=$sysStatus0[DigIn08];

       $ValA[21]=$sysStatus4[BS01];
       $ValA[22]=$sysStatus4[BS04];
       $ValA[23]=$sysStatus4[BS02];
       $ValA[24]=$sysStatus4[BS03];
       $ValA[25]=$sysStatus4[BS05];
       $ValA[26]=$sysStatus4[BS06];
       $ValA[27]=$sysStatus4[BS07];
       $ValA[28]=$sysStatus4[BS08];
       $ValA[29]=Systemlogic($ValA[21],$ValA[22],$ValA[23],$ValA[24],$ValA[25]);

       $ValA[30]=$sysStatus0[DigIn04];
       $ValA[31]=$sysStatus0[DigIn01];
       $ValA[32]=$sysStatus0[DigIn02];
       $ValA[33]=$sysStatus0[DigIn03];
       $ValA[34]=$sysStatus0[DigIn05];
       $ValA[35]=$sysStatus0[DigIn06];
       $ValA[36]=$sysStatus0[DigIn07];
       $ValA[37]=$sysStatus0[DigIn08];
       $ValA[38]=Systemlogic($ValA[30],$ValA[31],$ValA[32],$ValA[33],$ValA[34]);
       $ValA[39]="";
       $ValA[40]="";

  //     $ShwA[0]=true;
 //      $ShwA[1]=true;
  //     $ShwA[2]=true;
   //    $ShwA[3]=false;
   //    $ShwA[4]=false;
   //    $ShwA[5]=false;
   //    $ShwA[6]=false;
  //     $ShwA[7]=true;
  //     $ShwA[8]=true;
  //     $ShwA[9]=true;
 //      $ShwA[10]=true;
 //      $ShwA[11]=true;
 //      $ShwA[12]=true;
 //      $ShwA[13]=true;
 //      $ShwA[14]=true;
  //     $ShwA[15]=true;
  //     $ShwA[16]=true;
  //     $ShwA[17]=true;
 //      $ShwA[18]=true;
  //     $ShwA[19]=true;
  //     $ShwA[20]=true;
   //    $ShwA[21]=false;
  //     $ShwA[22]=false;
  //     $ShwA[23]=false;
  //     $ShwA[24]=false;
  //     $ShwA[25]=false;
 //      $ShwA[26]=false;
  //     $ShwA[27]=false;
 //      $ShwA[28]=false;
 //      $ShwA[29]=false;
 //      $ShwA[30]=true;
  //     $ShwA[31]=true;
 //      $ShwA[32]=true;
  //     $ShwA[33]=true;
  //     $ShwA[34]=true;
  //     $ShwA[35]=true;
 //    $ShwA[36]=false;
  //     $ShwA[37]=false;
  //     $ShwA[38]=false;
  //     $ShwA[39]=false;
   //    $ShwA[40]=false;




       }




       $SizA[0]=1.2;
     //  $SizA[25]=2.0;
     //  $SizA[27]=0.5;



       if ($exchangermode==1)
       {// open loop
            $exchnimage="../status/image/WebBackGroundHeatingMode.png";
       }

       function DisplayStatus($seqno,$label,$value,$xpos,$ypos,$lolimit,$uplimit,$size,$show,$form)
       {
          $alertfactor=.15;   // alerts at 15% of limit
          $alertdelta=($uplimit-$lolimit)*$alertfactor;
         // Hide display
        if ($show != true or $show ==0) {$EngHide="hidden";} else {$EngHide="";}
         // set background color based on limits
        $BackColor=lightgreen;

//echo("H-".$seqno." ".$label."--".$EngHide."||");
        if (($lolimit=="")  and ($hilimit=="")) {$BackColor=lightblue;}
        // yellow alert
        if (($value < ($lolimit+$alertdelta)) or ($value > ($uplimit-$alertdelta)))
           { $BackColor=yellow; }
        // red alert
         if (($value < $lolimit) or ($value > $uplimit))
           { $BackColor=red;}

         // no alerts
        if (($lolimit=="")  and ($hilimit=="")) {$BackColor=lightblue;}
// need limit code here




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
       echo "<p class='value-status ".$EngHide."' style='top: ".$ypos."px; left: ".$labxpos."px;
                 background-color: ".$BackColor."; line-height: ".$size."em; '>".$value."</p>";



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
                // display energy
          //     if ($EngStatRead != true) {$EngHide="hidden";} else {$EndHide="";}
          //     if ($EngStatColor == 0) {$EngColorDsp=LightGreen;} elseif ($EngStatColor == 1)  {$EngColorDsp=Yellow;} else {$EngColorDsp=LightRed;}
            //    pprint($EngColorDsp);
            //   pprint($EngStatColor);
          //     $EngColorDsp=red;
//               pprint($EngColor);



           for ($i=0;$i<$Pageelem+1;$i++)
           {

              // DisplayStatus($i,$LblA[$i],$ValA[$i],$PosAX[$i],$PosAY[$i],$LmtA[$i]['lolim'],$LmtA[$i]['hilim'],$SizA[$i],$ShwA[$i],$ForA[$i]);
                 DisplayStatus($i,$LblA[$i],$ValA[$i],$PosAX[$i],$PosAY[$i],$LolmtA[$i],$UplmtA[$i],$SizA[$i],$ShwA[$i],$ForA[$i]);
                 // if($i == 0){echo "<pre style='border:2px solid red;position:fixed;top:0 !important;left:0 !important;z-index:99999;'><br>".$ValA[$i]."<br></pre>";}
           }



                ?>
            </div>

             <div class="link_RSM">

                <p class="align-center">
                    <?php
                      echo "<a href=''./?id=0>Current Status</a>";
                      echo "<BR>";
                      echo "<a href='";
                      echo $config['base_domain'] . $config['base_dir'];
                      echo "performance";
                      $dt = strtotime($ValA[0]);
                      echo '?date=' . date('Y-m-d', $dt);
                      echo '&time=' . date('H:i:s', $dt);
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
