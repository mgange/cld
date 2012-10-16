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


     $db = new db($config);
     $SysID=$_SESSION["SysID"];
     // first get DAMID for this System from SysMap
     $query = "Select DAMID from SysMap where SysID=$SysID";
     $sysDAMID = $db -> fetchRow($query);
  //   echo $query;

//     $query = "SELECT * FROM SourceData0 WHERE DAMID='" . $sysDAMID['DAMID'] . "' order by Date Desc,Time Desc limit 1";
     $query = "SELECT * FROM SourceHeader, SourceData0 WHERE SourceHeader.RecNum = SourceData0.RecNum ORDER BY DateStamp Desc,TimeStamp Desc limit 1";
 //     echo $query;
     $sysStatus = $db -> fetchRow($query);

//     pprint($sysStatus);
 //   $sysstatus = $db -> fetchAll($query);


       $exchangermode = 0;
       $exchnimage="../status/image/WebBackGroundHeatingMode.png";
       $openloop=false;
       $openloopdw=false;
       $closedloop=true;


       $Pageelem=28;

       $LblA=array($Pageelem);
       $ValA=array($Pageelem);
     //  $LmtA=array(2,$Pageelem);
     //  $PosA= array(2,$Pageelem);
       $ShwA=array($Pageelem);

       $PosA = array(
            0 => array('xpos' => 550,
                       'ypos' => 100),


            1 => array('xpos' => 550,
                       'ypos' => 200),

            2 => array('xpos' => 650,
                       'ypos' => 100),


            3 => array('xpos' => 650,
                       'ypos' => 200),

            4 => array('xpos' => 750,
                       'ypos' => 100),


            5 => array('xpos' => 750,
                       'ypos' => 200),

            6 => array('xpos' => 100,
                       'ypos' => 100),


            7 => array('xpos' => 100,
                       'ypos' => 200),

            8 => array('xpos' => 100,
                       'ypos' => 300),

            9 => array('xpos' => 200,
                       'ypos' => 300),

            10 => array('xpos' => 300,
                       'ypos' => 300),


            11 => array('xpos' => 300,
                       'ypos' => 400),

            12 => array('xpos' => 300,
                       'ypos' => 500),


            13 => array('xpos' => 200,
                       'ypos' => 300),

            14 => array('xpos' => 500,
                       'ypos' => 300),


            15 => array('xpos' => 500,
                       'ypos' => 350),

            16 => array('xpos' => 500,
                       'ypos' => 400),


            17 => array('xpos' => 100,
                       'ypos' => 600),

            18 => array('xpos' => 150,
                       'ypos' => 600),


            19 => array('xpos' => 200,
                       'ypos' => 600),

            20 => array('xpos' => 250,
                       'ypos' => 600),


            21 => array('xpos' => 300,
                       'ypos' => 600),

            22 => array('xpos' => 350,
                       'ypos' => 600),


            23 => array('xpos' => 400,
                       'ypos' => 600),

            24 => array('xpos' => 450,
                       'ypos' => 600),


            25 => array('xpos' => 300,
                       'ypos' => 700),

            26 => array('xpos' => 500,
                       'ypos' => 400),


            27 => array('xpos' => 500,
                       'ypos' => 500)
           );
       echo $arr[0]['ypos'];




       $LmtA = array(
            0 => array('lolim' => 100,
                       'hilim' => 200),


            1 => array('lolim' => 100,
                       'hilim' => 200),

            2 => array('lolim' => 100,
                       'hilim' => 200),


            3 => array('lolim' => 100,
                       'hilim' => 200),

            4 => array('lolim' => 100,
                       'hilim' => 200),


            5 => array('lolim' => 100,
                       'hilim' => 200),

            6 => array('lolim' => 100,
                       'hilim' => 200),

            7 => array('lolim' => 100,
                       'hilim' => 200),

            8 => array('lolim' => 100,
                       'hilim' => 200),

            9 => array('lolim' => 100,
                       'hilim' => 200),

            10 => array('lolim' => 100,
                       'hilim' => 100),


            11 => array('lolim' => 100,
                       'hilim' => 100),

            12 => array('lolim' => 100,
                       'hilim' => 100),


            13 => array('lolim' => 200,
                       'hilim' => 300),

            14 => array('lolim' => 100,
                       'hilim' => 100),


            15 => array('lolim' => 100,
                       'hilim' => 100),

            16 => array('lolim' => 100,
                       'hilim' => 100),


            17 => array('lolim' => 100,
                       'hilim' => 100),

            18 => array('lolim' => 100,
                       'hilim' => 100),


            19 => array('lolim' => 200,
                       'hilim' => 300),

            20 => array('lolim' => 100,
                       'hilim' => 100),


            21 => array('lolim' => 100,
                       'hilim' => 100),

            22 => array('lolim' => 100,
                       'hilim' => 100),


            23 => array('lolim' => 100,
                       'hilim' => 100),

            24 => array('lolim' => 100,
                       'hilim' => 100),


            25 => array('lolim' => 100,
                       'hilim' => 100),

            26 => array('lolim' => 100,
                       'hilim' => 100),


            27 => array('lolim' => 100,
                       'hilim' => 100)
           );

       // Set up array here from web reference table
       // Values  Source0 to 4
       // Limits from Alarm and SensorMap
       // Configuration from SysConfig table

       $LblA[0]="Date Time";
       $LblA[1]="Outside Air F";
       $LblA[3]="Power KW";
       $LblA[5]="Energy KW";
       $LblA[7]="Air Out F";
       $LblA[9]="Air In F";
       $LblA[10]="Water In F";
       $LblA[11]="Water Out F";
       $LblA[12]="Pressure PSI";
       $LblA[13]="Flow GPM";
       $LblA[14]="GPM";
       $LblA[15]="GPM";
       $LblA[16]="GPM";
       $LblA[17]="Fan";
       $LblA[18]="Stage 1";
       $LblA[19]="Stage 2";
       $LblA[20]="Cooling";
       $LblA[21]="Aux H";
       $LblA[22]="V1";
       $LblA[23]="V2";
       $LblA[24]="V3";
       $LblA[25]="Aux H";
       $LblA[26]="System Information";
       $LblA[27]="System Description";

       $ValA[0]='10/17/12';
       $ValA[1]=70;
       $ValA[3]=150;
       $ValA[5]=250;
       $ValA[7]=150;
       $ValA[9]=150;
       $ValA[10]=150;
       $ValA[11]=150;
       $ValA[12]=150;
       $ValA[13]=150;
       $ValA[14]=150;
       $ValA[15]=150;
       $ValA[16]=150;
       $ValA[17]=150;
       $ValA[18]=150;
       $ValA[19]=150;
       $ValA[20]=150;
       $ValA[21]=150;
       $ValA[22]=150;
       $ValA[23]=150;
       $ValA[24]=150;
       $ValA[25]=150;
       $ValA[26]=150;
       $ValA[27]=150;

       $ShwA[0]=true;
       $ShwA[1]=true;
       $ShwA[3]=true;
       $ShwA[5]=true;
       $ShwA[7]=true;
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
       $ShwA[26]=true;
       $ShwA[27]=true;



       if ($exchangermode==1)
       {// open loop
            $exchnimage="../status/image/WebBackGroundHeatingMode.png";
       }

       function DisplayStatus($seqno,$label,$value,$xpos,$ypos,$lolimit,$uplimit,$size,$show)
       {
         // Hide display
        if ($show != true) {$EngHide="hidden";} else {$EndHide="";}
         // set backbround color
        $BackColor=lightgreen;


       $labypos=$ypos-20;  // set label position above value
       $labxpos=$xpos+15;
       echo "<p class='label-status ".$EngHide."' style='top: ".$labypos."px; left: ".$xpos."px;'>".$label."</p>";
       echo "<p class='value-status ".$EngHide."' style='top: ".$ypos."px; left: ".$labxpos."px; background-color: ".$BackColor.";'>".$value."</p>";




       }

       ?>
        <div class="row">
            <h1 class="span8 offset2">Status</h1>
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
               if ($EngStatRead != true) {$EngHide="hidden";} else {$EndHide="";}
               if ($EngStatColor == 0) {$EngColorDsp=LightGreen;} elseif ($EngStatColor == 1)  {$EngColorDsp=Yellow;} else {$EngColorDsp=LightRed;}
            //    pprint($EngColorDsp);
            //   pprint($EngStatColor);
               $EngColorDsp=red;
//               pprint($EngColor);

            //   echo "<p class='label-status ".$EngHide."'>".$EngStatLabel."</p>";
            //   echo "<p class='value-status ".$EngHide."' style='background-color:".$EngColorDsp."; top:200px;'>".$EngValue."</p>";

           for ($i=0;$i<28;$i=$i+1)
           {
                 DisplayStatus($i,$LblA[$i],$ValA[$i],$PosA[$i]['xpos'],$PosA[$i]['ypos'],$LmtA[$i]['lolim'],$LmtA[$i]['hilim'],1,$ShwA[$i]);

           }



                ?>
            </div>
        </div>

<?php
require_once('../includes/footer.php');
?>
