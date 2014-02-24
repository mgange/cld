<?php ini_set('max_execution_time', 60);
/**
 *------------------------------------------------------------------------------
 * Status Index Page
 * Displays system status for each unique system based on the systemmap  as
 * defined in the sysmap and webreftables
 * Sensor display- Title, Position, Format are controlled by values in these
 * tables. Page queries default system first and then replaces elements based
 * on unique rows defined for each system
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');
// Finds configuration information
checkSystemSet($config);
// sets page Header
require_once('../includes/header.php');
// Time function
    $now = "'" . date('Y-m-d', strtotime('-1 hour')) . "'";
    $dateTimeOffset = 5;
// Condigures Database
    $db = new db($config);
// defines system of interest to display
    $SysID=$_SESSION["SysID"];
    // set currflag if displaying most current status
    if (isset($_GET['id'])) {$CurrFlag=false;} else {$CurrFlag=true;}

    // first get DAMID for this System from SysMap
    $query = "
        SELECT
            SysName,            SystemDescription,
            address1,           address2,
            city,               state,
            Configuration,      NumofRSM,
            HeatExchanger,      LocationMainSystem,
            DAMID
        FROM SystemConfig, buildings
        WHERE buildings.buildingID = SystemConfig.BuildingID
          AND SystemConfig.SysID = " . $SysID;
    $sysDAMID = $db -> fetchRow($query);
    $SysConfig=$sysDAMID['Configuration'];


// zone is used to determine which sourcedata table from which to get the data

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

    if(isset($_POST['jump-date']) && isset($_POST['jump-time'])) {
        $query = "SELECT Recnum
                  FROM SourceHeader
                  WHERE SysID = :SysID
                    AND DateStamp = :date
                    AND TimeStamp >= :time
                  LIMIT 1";
        $bind = array(
            ':SysID' => $SysID,
            ':date' => date('Y-m-d', strtotime($_POST['jump-date'])),
            ':time' => date('H:i:s', strtotime($_POST['jump-date'].' '.$_POST['jump-time']))
        );

        $pointer = $db->fetchRow($query, $bind);
        if(count($pointer) && isset($pointer['Recnum'])) {
            header('Location: ./?id='.$pointer['Recnum']);
        }else{
            header('Location: ./');
        }
    }

    if(isset($_GET['id'])){
        //Get date and time for passed header id
        $query = "SELECT TIMESTAMPADD(SECOND," . $dateTimeOffset . ",TIMESTAMP(DateStamp,TimeStamp)) AS timestampAfter,
                TIMESTAMPADD(SECOND,-" . $dateTimeOffset . ",TIMESTAMP(DateStamp,TimeStamp)) AS timestampBefore
            FROM SourceHeader WHERE Recnum = " . $_GET['id'];
        $result = $db -> fetchRow($query);

        $timeBefore = date('H:i:s',strtotime($result['timestampBefore']));
        $dateBefore = date('Y-m-d',strtotime($result['timestampBefore']));

        $timeAfter = date('H:i:s',strtotime($result['timestampAfter']));
        $dateAfter = date('Y-m-d',strtotime($result['timestampAfter']));

        $andOr = "AND";
        if($dateAfter != $dateBefore) $andOr = "OR";

        $query0 = "
            SELECT *
            FROM SourceHeader, SourceData0
            WHERE SourceHeader.SysID = " . $SysID . "
                AND SourceData0.HeadID = SourceHeader.Recnum
                AND SourceHeader.DateStamp >= '" . $dateBefore . "'
                AND SourceHeader.DateStamp <= '" . $dateAfter . "'
                AND (
                    SourceHeader.TimeStamp >= '" . $timeBefore . "'
                    " . $andOr . " SourceHeader.TimeStamp <= '" . $timeAfter . "'
                )
            ORDER BY DateStamp DESC, TimeStamp DESC
            LIMIT 1";
        $query4 = "
            SELECT *
            FROM SourceHeader, SourceData4
            WHERE SourceHeader.SysID = " . $SysID . "
                AND SourceData4.HeadID = SourceHeader.Recnum
                AND SourceHeader.DateStamp >= '" . $dateBefore . "'
                AND SourceHeader.DateStamp <= '" . $dateAfter . "'
                AND (
                    SourceHeader.TimeStamp >= '" . $timeBefore . "'
                    " . $andOr . " SourceHeader.TimeStamp <= '" . $timeAfter . "'
                )
            ORDER BY DateStamp DESC, TimeStamp DESC
            LIMIT 5";
        $queryCalc = "
            SELECT *
            FROM SourceHeader, SensorCalc
            WHERE SourceHeader.SysID = " . $SysID . "
                AND SensorCalc.HeadID = SourceHeader.Recnum
                AND SourceHeader.DateStamp >= '" . $dateBefore . "'
                AND SourceHeader.DateStamp <= '" . $dateAfter . "'
                AND (
                    SourceHeader.TimeStamp >= '" . $timeBefore . "'
                    " . $andOr . " SourceHeader.TimeStamp <= '" . $timeAfter . "'
                )
            ORDER BY SourceHeader.DateStamp DESC, SourceHeader.TimeStamp DESC
            LIMIT 1";
        // Query for RSMs 1, 2, 3, or 5
        if(isset($zone)){
            $query1 = "
                SELECT *
                FROM SourceHeader, SourceData1
                WHERE SourceHeader.SysID = " . $SysID . "
                    AND SourceData1.HeadID = SourceHeader.Recnum
                    AND SourceData1.SourceID = " . $zone . "
                    AND SourceHeader.DateStamp >= '" . $dateBefore . "'
                    AND SourceHeader.DateStamp <= '" . $dateAfter . "'
                    AND (
                        SourceHeader.TimeStamp >= '" . $timeBefore . "'
                        " . $andOr . " SourceHeader.TimeStamp <= '" . $timeAfter . "'
                    )
                ORDER BY DateStamp DESC, TimeStamp DESC
                LIMIT 1";
        }
    }else{
        $query0 = "
            SELECT *
            FROM SourceHeader, SourceData0
            WHERE SourceHeader.SysID = " . $SysID . "
              AND SourceHeader.Recnum = SourceData0.HeadID
              AND SourceHeader.DateStamp >= " . $now . "
            ORDER BY SourceHeader.DateStamp DESC, SourceHeader.TimeStamp DESC
            LIMIT 1";
        $query4 = "
            SELECT *
            FROM SourceHeader, SourceData4
            WHERE SourceHeader.SysID = " . $SysID . "
              AND SourceHeader.Recnum = SourceData4.HeadID
              AND SourceHeader.DateStamp >= " . $now . "
            ORDER BY SourceHeader.DateStamp DESC,SourceHeader.TimeStamp  DESC
            LIMIT 5";
        $queryCalc = "
            SELECT *
            FROM SourceHeader, SensorCalc
            WHERE SourceHeader.SysID = " . $SysID . "
              AND SourceHeader.RecNum = SensorCalc.HeadID
              AND SourceHeader.DateStamp >= " . $now . "
            ORDER BY SourceHeader.DateStamp Desc, SourceHeader.TimeStamp Desc
            LIMIT 1";
        if(isset($zone)) $query1 = "
                SELECT *
                FROM SourceHeader, SourceData1
                WHERE SourceHeader.SysID = " . $SysID . "
                  AND SourceHeader.Recnum = SourceData1.HeadID
                  AND SourceData1.SourceID = " . $zone . "
              AND SourceHeader.DateStamp >= " . $now . "
                ORDER BY SourceHeader.DateStamp DESC,SourceHeader.TimeStamp DESC
                LIMIT 1";
    }
      $sysStatus4 = $db -> fetchAll($query4);

      // get first row only which contains instantantious COP
      $sysCalc = $db -> fetchRow($queryCalc);

     $sysStatus0 = $db -> fetchRow($query0);
     if (isset($zone)) {
         $sysStatus1 = $db -> fetchRow($query1);
         $Row = $db -> numRows($query1);

          }

     //get next and previous recnum's
    $query = "SELECT TIMESTAMP('" . $sysStatus0['DateStamp'] . "','" . $sysStatus0['TimeStamp'] . "') AS timeStamp";
    $result = $db -> fetchRow($query);
    $timestamp = $result['timeStamp'];

    for($i=1;$i<=120;$i++){  //query up to 5 days (120 hours) if no data available
        $queryPrev = "
            SELECT SourceHeader.Recnum
            FROM SourceHeader,SourceData0
            WHERE SourceHeader.SysID = " . $SysID . "
                AND SourceHeader.Recnum = SourceData0.HeadID
                AND
                (
                    (
                        SourceHeader.DateStamp = '" . $sysStatus0['DateStamp'] . "'
                    AND SourceHeader.TimeStamp < '" . $sysStatus0['TimeStamp'] . "'
                    )
                    OR
                        SourceHeader.DateStamp < '" . $sysStatus0['DateStamp'] . "'
                )
                AND
                (
                    (
                        SourceHeader.DateStamp = DATE(DATE_SUB('" . $timestamp . "',INTERVAL " . $i . " HOUR))
                    AND SourceHeader.TimeStamp >= TIME(DATE_SUB('" . $timestamp . "',INTERVAL " . $i . " HOUR))
                    )
                    OR
                        SourceHeader.DateStamp > DATE(DATE_SUB('" . $timestamp . "',INTERVAL " . $i . " HOUR))
                )
            ORDER BY DateStamp DESC,TimeStamp DESC
            LIMIT 1";
        $result = $db -> fetchRow($queryPrev);
        if(!empty($result)){
            $prev = $result['Recnum'];
            break;
        }
    }

    for($i=1;$i<=120;$i++){  //query up to 5 days (120 hours) if no data available
        $queryNext = "
            SELECT SourceHeader.Recnum
            FROM SourceHeader,SourceData0
            WHERE SourceHeader.SysID = " . $SysID . "
                AND SourceHeader.Recnum = SourceData0.HeadID
                AND
                (
                    (
                        SourceHeader.DateStamp = '" . $sysStatus0['DateStamp'] . "'
                    AND SourceHeader.TimeStamp > '" . $sysStatus0['TimeStamp'] . "'
                    )
                    OR
                        SourceHeader.DateStamp > '" . $sysStatus0['DateStamp'] . "'
                )
                AND
                (
                    (
                        SourceHeader.DateStamp = DATE(DATE_ADD('" . $timestamp . "',INTERVAL " . $i . " HOUR))
                    AND SourceHeader.TimeStamp <= TIME(DATE_ADD('" . $timestamp . "',INTERVAL " . $i . " HOUR))
                    )
                    OR
                        SourceHeader.DateStamp < DATE(DATE_ADD('" . $timestamp . "',INTERVAL " . $i . " HOUR))
                )
            ORDER BY DateStamp ASC,TimeStamp ASC
            LIMIT 1";
        $result = $db -> fetchRow($queryNext);
        if(!empty($result)){
            $next = $result['Recnum'];
            break;
        }
    }

     $SysName=$sysDAMID['SysName'];
     $SysLocation=$sysDAMID['address1']." ".$sysDAMID['address2']." ".$sysDAMID['city']." ".$sysDAMID['state'];

    // determine number of zones and which one is displayed
 if (isset($_GET['z']))  {$SysZNum=$_GET['z'];} else {$SysZNum=0;}
     $SysLocMain=$sysDAMID['LocationMainSystem'];
     if ($SysZNum==0) {$SysZone="Main";}
     if ($SysZNum >= 1) $SysZone="RSM " . (($SysZNum == 4) ? ($SysZNum + 1) : $SysZNum);
     $NumRSM=$sysDAMID['NumofRSM'];

// get positions and labels for this page from Web Reference table
     // first get total number of page positions
      $querypos="Select * from WebRefTable where WebPageName='StatusDB' and WebSubPageName='Main' order by SysID";

     // now get positions for main page
      $PosMain = $db -> fetchAll($querypos);
      $Pageelem = count($PosMain);

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
     $systemDesc=$sysDAMID['SystemDescription'].$lf."Heat Exchanger -".$sysDAMID['HeatExchanger'].$lf.
                 "Location-".$sysDAMID['LocationMainSystem'].$lf."Main DAMID-".$sysDAMID['DAMID'].$lf.
                 "RSMs-".$sysDAMID['NumofRSM'];


     $LblA[0]="Date Time";
     $SDateTime=$sysStatus0['DateStamp']." ".$sysStatus0['TimeStamp'];
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

   //  $LblA[40]="System COP";
   //  $LblA[46]="Heat Pump COP";

     $ShwA[29]=true;
     $SStatus[29]=1;
     $ShwA[38]=true;
     $SStatus[38]=1;
 //   $ShwA[40]=true;
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
     
     // for label changes default maps passes (1 and 3) added sysid's must be equal to where statement
     // when labels or positions are change, dashboard edit will add corresponding unique record in sysmap 
   if (isset($zone) && $zone>=1) {$imax=4;} else {$imax=2;}

   for ($i=0;$i<$imax;$i++)
   {
        $DeftMapqueryZ0="Select WebPagePosNo, SourceID, WebRefTable.SensorLabel, SensorColName,SensorAddress,SensorActive,SysMap.Recnum,WebRefTable.Recnum,
                       SensorUnits,AlarmLoLimit,AlarmUpLimit,AlertPercent,SenAdjFactor,SenDBFactor,Format,Inhibit,SensorStatus,SysMap.Recnum,WebSubPageName,WebRefTable.SensorName from SysMap, WebRefTable
                       where SysMap.SensorRefName = WebRefTable.SensorName and WebRefTable.WebSensRefNum=SysMap.WebSensRefNum and
                       SysMap.SysID=WebRefTable.SysID and
                       WebPageName='StatusDB' and SysMap.SysID=0 and SensorActive=1 and (SourceID=0 or SourceID=4 or SourceID=99) and WebSubPageName='Main' order by WebRefTable.SysID Asc,WebPagePosNo,SourceId";
        $DeftMapqueryZ1="Select WebPagePosNo, SourceID, WebRefTable.SensorLabel, SensorColName,SensorAddress,SensorActive,SysMap.Recnum,WebRefTable.Recnum,
                       SensorUnits,AlarmLoLimit,AlarmUpLimit,AlertPercent,SenAdjFactor,SenDBFactor,Format,Inhibit,SensorStatus,SysMap.Recnum,WebSubPageName,WebRefTable.SensorName from SysMap, WebRefTable
                       where SysMap.SensorRefName = WebRefTable.SensorName and  WebRefTable.WebSensRefNum=SysMap.WebSensRefNum and
                        SysMap.SysID=WebRefTable.SysID and
                        WebPageName='StatusDB' and SysMap.SysID=0 and SensorActive=1 and  (SourceID=0 or SourceID=1 or SourceID=4  or SourceID=99) and WebSubPageName='RSM' order by WebRefTable.SysID Asc,WebPagePosNo,SourceId
                        ";

      $UnqiMapqueryZ0="Select WebPagePosNo, SourceID, WebRefTable.SensorLabel, SensorColName,SensorAddress,SensorActive,SysMap.Recnum,WebRefTable.Recnum,
                       SensorUnits,AlarmLoLimit,AlarmUpLimit,AlertPercent,SenDBFactor,SenAdjFactor,Format,Inhibit,SensorStatus,WebSubPageName,WebRefTable.SensorName from SysMap, WebRefTable
                      
                        where SysMap.SensorRefName = WebRefTable.SensorName and WebRefTable.WebSensRefNum=SysMap.WebSensRefNum and
                        WebPageName='StatusDB' and SysMap.SysID=".$SysID." and (SourceID=0 or SourceID=4 or SourceID=99) and WebSubPageName='Main' order by WebPagePosNo";
    
     // pprint($DeftMapqueryZ0);
     // pprint($UnqiMapqueryZ0);
      //
      
      
      $UnqiMapqueryZ1="Select WebPagePosNo, SourceID, WebRefTable.SensorLabel, SensorColName,SensorAddress,SensorActive,SysMap.Recnum,WebRefTable.Recnum,
                       SensorUnits,AlarmLoLimit,AlarmUpLimit,AlertPercent,SenDBFactor,SenAdjFactor,Format,Inhibit,SensorStatus,WebSubPageName,WebRefTable.SensorName from SysMap, WebRefTable
                       where SysMap.SensorRefName = WebRefTable.SensorName and WebRefTable.WebSensRefNum=SysMap.WebSensRefNum and
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
         $SID=$resultRow['SourceID'];
//pprint($resultRow);
   //     if (($i<=1  and  ($SID==0 or $SID==4 or $SID==5)))
      //  {
            $GetValue="";

            $SUnit=UnitLabel($resultRow['SensorUnits']);
            $SPos= $resultRow['WebPagePosNo'];
            $LblA[$SPos]=$resultRow['SensorLabel']." ".$SUnit."<BR> ";
            $LolmtA[$SPos]=$resultRow['AlarmLoLimit'];
            $UplmtA[$SPos]=$resultRow['AlarmUpLimit'];
            $AlertFactor[$SPos]=$resultRow['AlertPercent'];
            $SStatus[$SPos]=$resultRow['SensorStatus'];
            $ShwA[$SPos]=((!$resultRow['Inhibit']) and ($resultRow['SensorActive']==1));


            $ForA[$SPos]=$resultRow['Format'];
            // get value and process
            $DBCol= $resultRow['SensorColName'];
          // if ($i==1) {echo($DBCol)."-".$ShwA[$SPos]."-".$resultRow[Inhibit]."-".$resultRow[SensorStatus]."<BR>";}
           if ($LolmtA[$SPos]!=NULL) {$TLlim="Lo Limit: ".$LolmtA[$SPos];} else {$TLlim="";}
            if ($UplmtA[$SPos]!=NULL) {$TUlim="Up Limit: ".$UplmtA[$SPos].$cr;} else {$TUlim="";}
            if ($resultRow['SourceID'] == 5) {$DataTable="SensorCalc";} else {$DataTable="SourceData".$resultRow['SourceID'];}
            if ($resultRow['SourceID']== 4) {$Address="ModBus Addr:".$resultRow['SensorAddress'].$cr;} else {$Address="";}

          switch ($resultRow['SensorStatus'])
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



            $Title[$SPos]=" Table: ".$DataTable.$cr."Field: ".$DBCol.$cr.$Address.$TUlim.$TLlim.$cr.$SStat;//.$cr.$R1.$cr.$R2."|";

            switch ($resultRow['SourceID'])
            {
                case 0: $GetValue=$sysStatus0[$DBCol];

                    break;
                case 1: $GetValue=$sysStatus1[$DBCol];

                    break;
                case 4:
                      foreach ($sysStatus4 as $modrow)
                {
                      if (($resultRow['SensorAddress']==$modrow['PwrSubAddress']) or  ($resultRow['SensorAddress']== $modrow['ThermSubAddress']))
                           {$GetValue=$modrow[$DBCol];}
                }
                    break;
                case 99: $GetValue=$sysCalc[$DBCol];
                    break;
                //default covers all RSMS
                default : $GetValue=$sysStatus1[$DBCol];  // gets data from all rsms
                    break;
            }



            // TEMP FIX for RSM mapping problem
            //
             // format calls here
             if ($ForA[$SPos]==0 )
             {

              // $ValA[$SPos]=number_format($GetValue*$resultRow[SenAdjFactor]/$resultRow[SenDBFactor],2);}  senadjfactor added to parser therefore not required here 12/8/12

                 if ($resultRow['SensorUnits']=="dF" or $resultRow['SensorUnits']=="dC") {$Prsc=0;} else {$Prsc=2;}
               $ValA[$SPos]=number_format($GetValue/$resultRow['SenDBFactor'],$Prsc);
             }
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
         $ValA[29]="No Thermostat ";
        }
 if ($ShwA[30] and $ShwA[31]and $ShwA[32] and $ShwA[33] and $ShwA[34] )
 {
   $ValA[38]=Systemlogic($ValA[30],$ValA[31],$ValA[32],$ValA[33],$ValA[34],false);
 }
 else
 {
     $ValA[38]="No Control Monitor on System";
 }
   $ValA[39]="";
 // cop reformat if null
 if ($ValA[40]=="") {$ValA[40]="--";}
 if ($ValA[46]=="") {$ValA[46]="--";}




// code for system image selection here
      // heat mode is default need logic here to switch graphic between modes
      // mode controlled by digin03 from source 0 $ValA[32])
 
 // add code to set configuration 
       $query="Select * from SysConfigDefaults where ConfigSubGroup='Configuration' and AssignedValue=".$SysConfig;
       $SelRow = $db -> fetchrow($query); 
      
       $ImgLoc="../status/image/";
       $exchnimage=$ImgLoc.$SelRow['SysImage1'];
       $Wellimage=$ImgLoc.$SelRow['LoopImage1'];
       $LoopTop=$SelRow['LoopPosTop'];
       $LoopLeft=$SelRow['LoopPosLeft'];   
      
       if ($ValA[33]==1) {$exchangermode = 1;}
       if (isset($exchangermode) && $exchangermode==1)  {       
         $exchnimage=$ImgLoc.$SelRow['SysImage2'];
         $Wellimage=$ImgLoc.$SelRow['LoopImage2'];
         }
  
        
       
       
       
       $SizA[0]=1.2;
     //  $SizA[25]=2.0;
     //  $SizA[27]=0.5;


       function DisplayStatus($seqno,$label,$value,$xpos,$ypos,$lolimit,$uplimit,$alertfactor,$size,$show,$form,$title,$colorovride,$mainalert)
       {
        //  $alertfactorc=$alertfactor*0.01;   // alerts at 15% of limit
          $alertdelta=($uplimit-$lolimit)*$alertfactor*0.01;
         // pprint($seqno."-".$alertdelta."--");
         // Hide display
        if ($show != true or $show ==0) {$EngHide="hidden";} else {$EngHide="";}

         // set background color based on limits
        $BackColor="lightgreen";

    //    if (($lolimit=="")  and ($uplimit=="")) {$BackColor=lightblue;}
        // yellow alert lo limit
        if ( $lolimit!="" and ($value < ($lolimit+$alertdelta)))
           { $BackColor="yellow"; }

        // yellow alert up limit
        //
         if ($uplimit!="" and ($value > ($uplimit-$alertdelta)))
           { $BackColor="yellow"; }

        // red alert lo limit
         if ($lolimit==""  and ($value < $lolimit))
           { $BackColor="red";}
          // red alert up limit
         if ($uplimit!="" and ($value > $uplimit))
           { $BackColor="red";}

         // no alerts

        if (($lolimit=="")  and ($uplimit=="")) {$BackColor="lightblue";}
        if ($colorovride!="") {$BackColor=$colorovride;}


        // set Fontcolor
        $FontColor="black";
        //Special formats
       if ($form == 3)  //digital display
           {

           if ($value == 1)
               {
                $BackColor="lightgreen";
               }
               else
               {
                $BackColor="white";
               }
            $value="&nbsp";
           }
         if ($form == 2)  // nobackground
              {
              $BackColor="white"."; border: 0px solid white";
              }



         if ($size == "") {$size=1.2;}

//    if (is_numeric($value)==true) {$value=number_format($value,0);}

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
<!-- Page structure  - define navigation arrows -->
        <div class="row">
           <h1 class="span6 offset2">Status - <span class="building-name">System - <?php  echo $SysName." - ".$SysZone ?></span></h1>
            <p class="span4" style="text-align:center"><?php
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
            <h4 class="span2 offset3 align-right">Jump to: </h4>
            <div class="span7">
                <form method="POST">
                <small>
                <label class="span2" for="date"><strong>Date</strong>
                    <input id="date" class="datepick span2" type="text" name="jump-date" value="<?=date('Y-m-d', strtotime($sysStatus0['DateStamp']))?>">
                </label>
                <label class="span2" for="time"><strong>Time</strong>
                    <input id="time" class="timepick span2" type="text" name="jump-time" value="<?=date('H:i A', strtotime($sysStatus0['TimeStamp']))?>" autocomplete="off">
                </label>
                <label class="span2" for="time"><br>
                    <button class="btn" type="submit">Go!!</button>
                </label>
                </small>
                </form>
            </div>
        </div>

       <div class="row">

<!-- Page structure  - system image -->
            <div class="status-container span10 offset1">

               <div class="status-Back map">
                   <img src="<?php echo $exchnimage ?>" alt="Heat Exchanger">
                </div>
                <div class="status-OpenLoop" style="left: <?=$LoopLeft?>px; top: <?=$LoopTop?>px">
                    <img src="<?php echo $Wellimage ?>" alt="Open Loop ">
                </div>
         
                <?php

           // state colors
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


           for ($i=0;$i<$Pageelem;$i++)
           {
               // system status color override

               if (isset($LblA[$i]) && ($LblA[$i]=="ThermStat Mode" or $LblA[$i]=="System Status"))
                     {
                         $colorovride= ColorOver($ValA[$i]);

                     }
                     else
                     {
                         $colorovride="";
                     }

 // determine current status of sensors display only when status page is on current status
                $alerttemp=false;
                if (isset($SStatus[$i]) && ($SStatus[$i]%3)==0 and $CurrFlag==true)  // display x on mode 0 and 3
                {
                    $alerttemp=true;
                }

// displays status fields
                if(!isset($LblA[$i]))           $LblA[$i] = "";
                if(!isset($ValA[$i]))           $ValA[$i] = "";
                if(!isset($PosAX[$i]))          $PosAX[$i] = "";
                if(!isset($PosAY[$i]))          $PosAY[$i] = "";
                if(!isset($LolmtA[$i]))         $LolmtA[$i] = "";
                if(!isset($UplmtA[$i]))         $UplmtA[$i] = "";
                if(!isset($AlertFactor[$i]))    $AlertFactor[$i] = "";
                if(!isset($SizA[$i]))           $SizA[$i] = "";
                if(!isset($ShwA[$i]))           $ShwA[$i] = "";
                if(!isset($ForA[$i]))           $ForA[$i] = "";
                if(!isset($Title[$i]))          $Title[$i] = "";
                DisplayStatus($i,$LblA[$i],$ValA[$i],$PosAX[$i],$PosAY[$i],$LolmtA[$i],$UplmtA[$i],$AlertFactor[$i],$SizA[$i],$ShwA[$i],$ForA[$i],$Title[$i],$colorovride,$alerttemp);

           }


                ?>
            </div>

             <div class="link_RSM">
<!-- Page structure  - define performance and RSM link definitions -->
                <p class="align-center">
                    <?php
                      if ($CurrFlag!=true) {
                        echo "<a href='./";
                        if(isset($_GET['z'])) {
                            echo "?z=" . $_GET['z'];
                        }
                        echo "'>Current Status</a>";
                      }
                      echo "<BR>";
                      echo "<a href='";
                      echo $config['base_domain'] . $config['base_dir'];
                      echo "performance";
                      if(isset($_GET['id'])) {
                        $dt = strtotime($ValA[0]);
                        echo '?date=' . date('Y-m-d', $dt);
                        echo '&time=' . date('H:i:s', $dt);
                        if(isset($_GET['z'])) echo "&z=".intval($_GET['z']);
                      }else if(isset($_GET['z'])) echo "?z=rsm";
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
