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
       $LmtA=array(2,$Pageelem);
       $PosA= array(2,$Pageelem);
       $ShwA=array($Pageelem);

       // Set up array here from web reference table
       // Values  Source0 to 4
       // Limits from Alarm and SensorMap
       // Configuration from SysConfig table

       $LblA[0]="Date Time";
       $LblA[1]="Outside Air F";
       $LblA[2]="Power KW";
       $LblA[3]="Energy KW";
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

       $ValA[0]="Date Time";
       $ValA[1]="Outside Air F";
       $ValA[2]="Power KW";
       $ValA[3]="Energy KW";
       $ValA[7]="Air Out F";
       $ValA[9]="Air In F";
       $ValA[10]="Water In F";
       $ValA[11]="Water Out F";
       $ValA[12]="Pressure PSI";
       $ValA[13]="Flow GPM";
       $ValA[14]="GPM";
       $ValA[15]="GPM";
       $ValA[16]="GPM";
       $ValA[17]="Fan";
       $ValA[18]="Stage 1";
       $ValA[19]="Stage 2";
       $ValA[20]="Cooling";
       $ValA[21]="Aux H";
       $ValA[22]="V1";
       $ValA[23]="V2";
       $ValA[24]="V3";
       $ValA[25]="Aux H";
       $ValA[26]="System Information";
       $ValA[27]="System Description";

       $ShwA[0]="Date Time";
       $ShwA[1]="Outside Air F";
       $ShwA[3]="Power KW";
       $ShwA[5]="Energy KW";
       $ShwA[7]="Air Out F";
       $ShwA[9]="Air In F";
       $ShwA[10]="Water In F";
       $ShwA[11]="Water Out F";
       $ShwA[12]="Pressure PSI";
       $ShwA[13]="Flow GPM";
       $ShwA[14]="GPM";
       $ShwA[15]="GPM";
       $ShwA[16]="GPM";
       $ShwA[17]="Fan";
       $ShwA[18]="Stage 1";
       $ShwA[19]="Stage 2";
       $ShwA[20]="Cooling";
       $ShwA[21]="Aux H";
       $ShwA[22]="V1";
       $ShwA[23]="V2";
       $ShwA[24]="V3";
       $ShwA[25]="Aux H";
       $ShwA[26]="System Information";
       $ShwA[27]="System Description";

       $PosA[1,1]=100;
       $PosA[1,2]="Outside Air F";
       $LblA[4]="Power KW";
       $LblA[6]="Energy KW";
       $LblA[8]="Air Out F";
       $LblA[10]="Air In F";
       $LblA[11]="Water In F";
       $LblA[12]="Water Out F";
       $LblA[13]="Pressure PSI";
       $LblA[14]="Flow GPM";
       $LblA[15]="GPM";
       $LblA[16]="GPM";
       $LblA[17]="GPM";
       $LblA[18]="Fan";
       $LblA[19]="Stage 1";
       $LblA[20]="Stage 2";
       $LblA[21]="Cooling";
       $LblA[22]="Aux H";
       $LblA[23]="V1";
       $LblA[24]="V2";
       $LblA[25]="V3";
       $LblA[26]="Aux H";
       $LblA[27]="System Information";
       $LblA[28]="System Description";






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

       if (($value>= ($uplimit*.8))  ||  ($value<= ($lolimit*1.2)))  {$BackColor=yellow;}
       if (($value>= $uplimit)  ||  ($value<= $lolimit))  {$BackColor=lightred;}
        $labypos=$ypos-20;  // set label position above value
        echo "<p class='label-status ".$EngHide."' style='top: ".$labypos."px; left: ".$xpos."px;'>".$label."</p>";
        echo "<p class='value-status ".$EngHide."' style='top: ".$ypos."px; left: ".$xpos."px; background-color: ".$BackColor.";'>".$value."</p>";



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
           for (i=1;i<29;i++)
           {
             DisplayStatus(i,$LblA(i),$ValA(i),$LmtA(1,i),$PosA(2,i),$PosA(1,i),$LmtA(2,i),1,$SwA(i));
           }



                ?>
            </div>
        </div>

<?php
require_once('../includes/footer.php');
?>
