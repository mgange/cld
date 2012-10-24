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
        case 4:
          $zone = 3;
          break;
        case 5:
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
      $query0 = "SELECT * FROM SourceHeader, SourceData0 WHERE SourceHeader.SysID = " . $SysID . " AND SourceData0.HeadID = SourceHeader.Recnum AND SourceHeader.TimeStamp BETWEEN '" . $timeBefore . "' AND '" . $timeAfter . "' AND SourceHeader.DateStamp BETWEEN '" . $dateBefore . "' AND '" . $dateAfter . "' LIMIT 1";
      $query4 = "SELECT * FROM SourceHeader, SourceData4 WHERE SourceHeader.SysID = " . $SysID . " AND SourceData4.HeadID = SourceHeader.Recnum AND SourceHeader.TimeStamp BETWEEN '" . $timeBefore . "' AND '" . $timeAfter . "' AND SourceHeader.DateStamp BETWEEN '" . $dateBefore . "' AND '" . $dateAfter . "' LIMIT 1";
      $queryCalc = "SELECT * FROM SourceHeader, SensorCalc WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.RecNum = SensorCalc.RecNum AND SourceHeader.TimeStamp BETWEEN '" . $timeBefore . "' AND '" . $timeAfter . "' AND SourceHeader.DateStamp BETWEEN '" . $dateBefore . "' AND '" . $dateAfter . "' LIMIT 1";
      // Querey for RSMs 1, 2, 3, or 5
      if(isset($zone)) $query1 = "SELECT * FROM SourceHeader, SourceData1 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData1.HeadID AND SourceHeader.TimeStamp BETWEEN '" . $timeBefore . "' AND '" . $timeAfter . "' AND SourceHeader.DateStamp BETWEEN '" . $dateBefore . "' AND '" . $dateAfter . "' LIMIT 1";
    }else{
      $query0 = "SELECT * FROM SourceHeader, SourceData0 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData0.HeadID AND SourceHeader.SourceID = 0 ORDER BY SourceHeader.DateStamp DESC,SourceHeader.TimeStamp  DESC LIMIT 1";
      $query4 = "SELECT * FROM SourceHeader, SourceData4 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData4.HeadID AND SourceHeader.SourceID = 4 ORDER BY SourceHeader.DateStamp DESC,SourceHeader.TimeStamp  DESC LIMIT 1";
      $queryCalc = "SELECT * FROM SourceHeader, SensorCalc WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.RecNum = SensorCalc.RecNum ORDER BY DateStamp Desc,TimeStamp Desc limit 1";
      if(isset($zone)) $query1 = "SELECT * FROM SourceHeader, SourceData1 WHERE SourceHeader.SysID = " . $SysID . " AND SourceHeader.Recnum = SourceData1.HeadID AND SourceHeader.SourceID = " . $zone . " ORDER BY SourceHeader.DateStamp DESC,SourceHeader.TimeStamp DESC LIMIT 1";
      }



     $sysStatus0 = $db -> fetchRow($query0);
     if (isset($zone)) {$sysStatus1 = $db -> fetchRow($query1);}

     $sysStatus4 = $db -> fetchRow($query4);
     // Not using Calculations for now
  //   $sysStatusCalc = $db -> fetchRow($queryCalc);

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
     if ($SysZNum >= 1) {$SysZone="RSM ".$SysZNum;}
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
       $Uplim = array($Pageelem);
       $Lolim = array($Pageelem);
       
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
      
     // then get positions for RSM pages
     if ($zone >= 1) {
         
     }
     
     
     // Field to Data mappings
     // first get default value for Main Page
     $DeftMapquery="Select SouceID, SensorColName from SysMap, WebRefTable 
                       join SysMap.SensorRefName = WebRefTable.SensorName 
                       where WebPageName='StatusDB' and SysMap.SysID=0";
     
     $UniqMapquery="Select SouceID, SensorColName from SysMap, WebRefTable 
                       join SysMap.SensorRefName = WebRefTable.SensorName 
                       where WebPageName=StatusDB and SysID=".$SysID;
     
    // $MapMain = $db -> fetchAll($DeftMapquery);
     
     $lf="<br>";

     $systemInfo=$SysName." - ". $SysLocation;
     $systemDesc=$sysDAMID[SystemDescription].$lf.$sysDAMID[HeatExchangeUnit].$lf.
                 "Location-".$sysDAMID[LocationMainSystem].$lf."Main DAMID-".$sysDAMID[DAMID].$lf.
                 "RSMs-".$sysDAMID[NumofRSM];



       $exchangermode = 0;
       $exchnimage="../status/image/WebBackGroundHeatingMode.png";
      

// Alert Limits array
       $LmtA = array(
            0 => array('lolim' => Null,
                       'hilim' => Null),


            1 => array('lolim' => Null,
                       'hilim' => Null),

            2 => array('lolim' => 32,
                       'hilim' => 100),


            3 => array('lolim' => 216,
                       'hilim' => 264),

            4 => array('lolim' => Null,
                       'hilim' => Null),


            5 => array('lolim' => Null,
                       'hilim' => Null),

            6 => array('lolim' => Null,
                       'hilim' => Null),

            7 => array('lolim' => 32,
                       'hilim' => 100),

            8 => array('lolim' => 55,
                       'hilim' => 85),

            9 => array('lolim' => 55,
                       'hilim' => 85),

            10 => array('lolim' => Null,
                       'hilim' => Null),


            11 => array('lolim' => Null,
                       'hilim' => Null),

            12 => array('lolim' => Null,
                       'hilim' => Null),


            13 => array('lolim' => Null,
                       'hilim' => Null),

            14 => array('lolim' => 40,
                       'hilim' => 70),


            15 => array('lolim' => 40,
                       'hilim' => 70),

            16 => array('lolim' => 0,
                       'hilim' => 70),


            17 => array('lolim' => 0,
                       'hilim' => 10),

            18 => array('lolim' => Null,
                       'hilim' => Null),


            19 => array('lolim' => Null,
                       'hilim' => Null),

            20 => array('lolim' => Null,
                       'hilim' => Null),


            21 => array('lolim' => Null,
                       'hilim' => Null),

            22 => array('lolim' => Null,
                       'hilim' => Null),


            23 => array('lolim' => Null,
                       'hilim' => Null),

            24 => array('lolim' => Null,
                       'hilim' => Null),


            25 => array('lolim' => Null,
                       'hilim' => Null),

            26 => array('lolim' => Null,
                       'hilim' => Null),


            27 => array('lolim' => Null,
                       'hilim' => Null),

            28 => array('lolim' => Null,
                       'hilim' => Null),

            29 => array('lolim' => Null,
                       'hilim' => Null),


            30 => array('lolim' => Null,
                       'hilim' => Null),

            31 => array('lolim' => Null,
                       'hilim' => Null),


            32 => array('lolim' => Null,
                       'hilim' => Null),

            33 => array('lolim' => Null,
                       'hilim' => Null),

            34 => array('lolim' => Null,
                       'hilim' => Null),

            35 => array('lolim' => Null,
                       'hilim' => Null),

            36 => array('lolim' => Null,
                       'hilim' => Null),


            37 => array('lolim' => Null,
                       'hilim' => Null),

            38 => array('lolim' => Null,
                       'hilim' => Null),


            39 => array('lolim' => Null,
                       'hilim' => Null),

            40 => array('lolim' => Null,
                       'hilim' => Null)

           );

       // Set up array here from web reference table
       // Values  Source0 to 4
       // Limits from Alarm and SensorMap
       // Configuration from SysConfig table
// Labels array fixed for now
       $deg=htmlentities(chr(176), ENT_QUOTES, 'cp1252');
       $LblA[0]="Date Time";
       $LblA[1]="Outside Air ".$deg."F";
       $LblA[2]="Air In ".$deg."F";
       $LblA[3]="Voltage V";
       $LblA[4]="Cntrl. Pwr.  KW";
       $LblA[5]="Sys. Pwr. KW";
       $LblA[6]="Sys. Energy KWhr";
       $LblA[7]="Air Out ".$deg."F";
       $LblA[8]="Mech RT ".$deg."F";
       $LblA[9]="LCD Temp ".$deg."F";
       $LblA[10]="Heat SP ".$deg."F";
       $LblA[11]="Cool SP ".$deg."F";
       $LblA[12]="System Information";
       $LblA[13]="System Description";
       $LblA[14]="Water Out ".$deg."F";
       $LblA[15]="Water In ".$deg."F";
       $LblA[16]="Pressure PSI";
       $LblA[17]="Flow GPM";
       $LblA[18]="";
       $LblA[19]="";
       $LblA[20]="";
       $LblA[21]="Fan";
       $LblA[22]="Stg 1";
       $LblA[23]="Stg 2";
       $LblA[24]="Cool";
       $LblA[25]="Aux H";
       $LblA[26]="V1";
       $LblA[27]="V2";
       $LblA[28]="V2";
       $LblA[29]="ThermStat Mode";
       $LblA[30]="Fan";
       $LblA[31]="Stg 1";
       $LblA[32]="Stg 2";
       $LblA[33]="Cool";
       $LblA[34]="Aux H";
       $LblA[35]="V1";
       $LblA[36]="V2";
       $LblA[37]="V3";
       $LblA[38]="System Status";
       $LblA[39]="Spare";
       $LblA[40]="Spare";

       // quick set up for query0  user else for query1 until mapping is complete

       if ($SysZNum == 0)  {
       $SDateTime=$sysStatus0[DateStamp]." ".$sysStatus0[TimeStamp];
       $SDateTime = date_create($SDateTime);
       $ValA[0]=date_format($SDateTime, 'm/d/Y g:i:s A');
       $ValA[1]=number_format($sysStatus0[Senchan07]/100,2);
       $ValA[2]=number_format($sysStatus0[Senchan05]/100,2);
       $ValA[3]=number_format($sysStatus4[Power02]/100,2);
       $ValA[4]=number_format($sysStatus4[Power01]/100,2);
       $ValA[5]=number_format($sysStatus4[Power03]/100,2);
       $ValA[6]=number_format($sysStatus4[Power04]/100,2);
       $ValA[7]=number_format($sysStatus0[Senchan06]/100,2);
       $ValA[8]=number_format($sysStatus0[Senchan08]/100,2);
       $ValA[9]=number_format($sysStatus4[LCDTemp],2);
       $ValA[10]=number_format($sysStatus4[HeatingSetPoint],2);
       $ValA[11]=number_format($sysStatus4[CoolingTemp],2);
       $ValA[12]=$systemInfo;
       $ValA[13]=$systemDesc;
       $ValA[14]=number_format($sysStatus0[Senchan03]/100,2);
       $ValA[15]=number_format($sysStatus0[Senchan01]/100,2);
       $ValA[16]=number_format($sysStatus0[FlowPress02]/100,2);
       $ValA[17]=number_format($sysStatus0[FlowPress01]/100,2);
       $ValA[18]=$sysStatus0[DigIn06];
       $ValA[19]=$sysStatus0[DigIn07];
       $ValA[20]=$sysStatus0[DigIn08];

       $ValA[21]=$sysStatus4[ThermStat01];
       $ValA[22]=$sysStatus4[ThermStat04];
       $ValA[23]=$sysStatus4[ThermStat02];
       $ValA[24]=$sysStatus4[ThermStat03];
       $ValA[25]=$sysStatus4[ThermStat05];
       $ValA[26]="";    //$sysStatus4[BS06];
       $ValA[27]="";    //$sysStatus4[BS07];
       $ValA[28]="";    //$sysStatus4[BS08];
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

       $ShwA[0]=true;
       $ShwA[1]=true;
       $ShwA[2]=true;
       $ShwA[3]=true;
       $ShwA[4]=true;
       $ShwA[5]=true;
       $ShwA[6]=true;
       $ShwA[7]=true;
       $ShwA[8]=true;
       $ShwA[9]=true;
       $ShwA[10]=true;
       $ShwA[11]=true;
       $ShwA[12]=true;
       $ShwA[13]=true;
       $ShwA[14]=true;
       $ShwA[15]=true;
       $ShwA[16]=true;
       $ShwA[17]=true;
       $ShwA[18]=true;
       $ShwA[19]=true;
       $ShwA[20]=true;
       $ShwA[21]=true;
       $ShwA[22]=true;
       $ShwA[23]=true;
       $ShwA[24]=true;
       $ShwA[25]=true;
       $ShwA[26]=false;
       $ShwA[27]=false;
       $ShwA[28]=false;
       $ShwA[29]=true;
       $ShwA[30]=true;
       $ShwA[31]=true;
       $ShwA[32]=true;
       $ShwA[33]=true;
       $ShwA[34]=true;
       $ShwA[35]=false;
       $ShwA[36]=false;
       $ShwA[37]=false;
       $ShwA[38]=true;
       $ShwA[39]=false;
       $ShwA[40]=false;
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

       $ShwA[0]=true;
       $ShwA[1]=true;
       $ShwA[2]=true;
       $ShwA[3]=false;
       $ShwA[4]=false;
       $ShwA[5]=false;
       $ShwA[6]=false;
       $ShwA[7]=true;
       $ShwA[8]=true;
       $ShwA[9]=true;
       $ShwA[10]=true;
       $ShwA[11]=true;
       $ShwA[12]=true;
       $ShwA[13]=true;
       $ShwA[14]=true;
       $ShwA[15]=true;
       $ShwA[16]=true;
       $ShwA[17]=true;
       $ShwA[18]=true;
       $ShwA[19]=true;
       $ShwA[20]=true;
       $ShwA[21]=false;
       $ShwA[22]=false;
       $ShwA[23]=false;
       $ShwA[24]=false;
       $ShwA[25]=false;
       $ShwA[26]=false;
       $ShwA[27]=false;
       $ShwA[28]=false;
       $ShwA[29]=false;
       $ShwA[30]=true;
       $ShwA[31]=true;
       $ShwA[32]=true;
       $ShwA[33]=true;
       $ShwA[34]=true;
       $ShwA[35]=true;
       $ShwA[36]=false;
       $ShwA[37]=false;
       $ShwA[38]=false;
       $ShwA[39]=false;
       $ShwA[40]=false;




       }
       $ForA[0]="";
       $ForA[1]="";
       $ForA[3]="";
       $ForA[5]="";
       $ForA[6]="";
       $ForA[9]="";
       $ForA[10]="";
       $ForA[11]="";
       $ForA[12]=2;
       $ForA[13]=2;
       $ForA[14]="";
       $ForA[15]="";
       $ForA[16]="";
       $ForA[17]="";
       $ForA[18]=1;
       $ForA[19]=1;
       $ForA[20]=1;
       $ForA[21]=1;
       $ForA[22]=1;
       $ForA[23]=1;
       $ForA[24]=1;
       $ForA[25]=1;
       $ForA[26]=1;
       $ForA[27]=1;
       $ForA[28]=1;
       $ForA[29]="";
       $ForA[30]=1;
       $ForA[31]=1;
       $ForA[32]=1;
       $ForA[33]=1;
       $ForA[34]=1;
       $ForA[35]=1;
       $ForA[36]=1;
       $ForA[37]=1;
       $ForA[38]="";
       $ForA[39]="";
       $ForA[40]="";



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
        if ($show != true) {$EngHide="hidden";} else {$EndHide="";}
         // set background color based on limits
        $BackColor=lightgreen;


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
       if ($form == 1)  //digital display
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
       $labxpos=$xpos;
       $label=str_pad($label,1," ",STR_PAD_LEFT);
       echo "<p class='label-status ".$EngHide."' style='top: ".$labypos."px; left: ".$xpos."px;'>".$label."</p>";
       echo "<p class='value-status ".$EngHide."' style='top: ".$ypos."px; left: ".$labxpos."px;
                 background-color: ".$BackColor."; line-height: ".$size."em; '>".$value."</p>";



       }
      function Systemlogic($G,$Y1,$Y2,$O,$W)
               {
                  $SState="Invalid State";

                  if (!$O and !$W and !$Y2 and  !$Y1 and !$G) {$SState="System Off";}
                  if (!$O and !$W and !$Y2 and  !$Y1 and  $G) {$SState="Fan Only";}
                  if (!$O and !$W and !$Y2 and   $Y1 and  $G) {$SState="Stage 1 Heat";}
                  if (!$O and !$W and  $Y2 and   $Y1 and  $G) {$SState="Stage 2 Heat";}
                  if (!$O and  $W and !$Y2 and  !$Y1 and  $G) {$SState="Emerg. Heat";}
                  if (!$O and  $W and  $Y2 and   $Y1 and  $G) {$SState="Stage 3 Heat";}
                  if ($O and !$W and !$Y2 and   $Y1 and  $G) {$SState="Stage 1 Cool";}
                  if ($O and !$W and  $Y2 and   $Y1 and  $G) {$SState="Stage 2 Cool";}

                  Return $SState;
               }

       ?>

        <div class="row">
           <h1 class="span6 offset2">Status &nbsp;&nbsp; <font color="blue">   System - <?php  echo $SysName." - ".$SysZone ?></font></h1>
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
                
                 DisplayStatus($i,$LblA[$i],$ValA[$i],$PosAX[$i],$PosAY[$i],$LmtA[$i]['lolim'],$LmtA[$i]['hilim'],$SizA[$i],$ShwA[$i],$ForA[$i]);
//echo $BackColor;
           }



                ?>
            </div>

             <div class="link_RSM">

                <p style="text-align:center">
                    <?php
                      for($i=0;$i<$NumRSM+1;$i++){
                      if($SysZNum != $i){
                          if($i == 0) $Zname="Main";
                          else $Zname = "RSM-" . $i;
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
