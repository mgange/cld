<?php
/**
 *------------------------------------------------------------------------------
 * Status Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');
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
       
       
       $EngStatRead=true;
       $EngValue=1.8;
       $EngHide="";
       $EngStatColor=2;
       $EngStatLabel="KW (Energy)";
       
       if ($exchangermode==1)     
       {// open loop
            $exchnimage="../status/image/WebBackGroundHeatingMode.png";
       }
       
       function DisplayStatus($seqno,$label,$value,$xpos,$ypos,$uplimit,$lolimit,$size)
       {
           
       }
       
       ?>
        <div class="row">
            <h1 class="span8 offset2">Status</h1>
        </div>

        <div class="row">
            <div class="status-container span10 offset1">
          <!--      <div class="status-Back map">
                   <img src="<?php echo $exchnimage ?>" alt="Heat Exchanger">
                </div>
                <div class="status-OpenLoop <?php if ($openloop != true) {echo "hidden";}?>">
                    <img src="../status/image/WebOpenLoop.png" alt="Open Loop ">
                </div>
                  <div class="status-OpenLoopDryWell <?php if ($openloopdw != true) {echo "hidden";}?>">
                    <img src="../status/image/WebOpenLoopDryWell.png" alt="Open Loop Dry Well">
                </div>
                <div class="status-ClosedLoop <?php if ($closedloop != true) {echo "hidden";}?>">
                    <img src="../status/image/WebClosedLoop.png" alt="Closed Loop"> -->
                </div>
                <?php
                // display energy
                if ($EngStatRead != true) {$EngHide="hidden";} else {$EndHide="";}                  
                if ($EngStatColor == 0) {$EngColorDsp=LightGreen;} elseif ($EngStatColor == 1)  {$EngColorDsp=Yellow;} else {$EngColorDsp=LightRed;}
                pprint($EngColorDsp);
                pprint($EngStatColor);
                echo "<p class='label-status ".$EngHide."'>".$EngStatLabel."</p>";
                echo "<p class='value-status ".$EngHide." background-color:".$EngColorDsp."'>".$EngValue."</p>";
                ?>
            </div> 
        </div>

<?php
require_once('../includes/footer.php');
?>
