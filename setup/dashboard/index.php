<?php
/**
 *------------------------------------------------------------------------------
 * Dashboard Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../../includes/pageStart.php');

checkSystemSet($config);

$systemID = $_SESSION['SysID'];
$buildingID = $_SESSION['buildingID'];

$db = new db($config);

$query = "SELECT SysName,Configuration,NumofTherms,NumofPowers,NumofRSM FROM SystemConfig WHERE SysID = " . $systemID;
$result = $db -> fetchRow($query);
$systemName = $result['SysName'];
$configuration = $result['Configuration'];
$numOfRSM = $result['NumofRSM'];
$numOfPowers = $result['NumofPowers'];
$numOfTherms = $result['NumofTherms'];

$zone = "Main";
$rsm = 0;
if(isset($_GET['z'])){
    $zone = "RSM" . $_GET['z'];
    $rsm = $_GET['z'];
}

require_once('../../includes/header.php');
?>

<script type="text/javascript">
    function selectSource(name,value){
        document.getElementById(name).innerHTML = "<input type=\"hidden\" name=\"" + name + "\" value=\"" + value + "\">" + value;
        alert(document.getElementById(name).innerHTML);
    }
</script>

<div class="row">
    <h1 class="span6 offset2">Dashboard - <span class="building-name">System - <?=$systemName." - ".$zone ?></span></h1>
</div>

<div class="row">
    <div class="status-container span10 offset1">
        <div class="status-Back map">
            <img src="../../status/image/WebBackGroundHeatingMode.png" alt="Heat Exchanger">
        </div>
        <?php
            echo "<div class=";
            if($configuration == 1){
                echo "\"status-OpenLoopDryWell\">";
                echo "<img src=\"../../status/image/WebOpenLoopDryWell.png\" alt=\"Open Loop Dry Well\">";
            }elseif($configuration == 2){
                echo "\"status-OpenLoop\">";
                echo "<img src=\"../../status/image/WebOpenLoop.png\" alt=\"Open Loop\">";
            }elseif($configuration == 3){
                echo "\"status-ClosedLoop\">";
                echo "<img src=\"../../status/image/WebClosedLoop.png\" alt=\"Closed Loop\">";
            }else echo "\"\">";
            echo "</div>";
        ?>
        <?php
            $query = "SELECT SensorLabel,PageLocX,PageLocY,WebSensRefNum
                    FROM WebRefTable
                    WHERE Inhibit = 0 AND SysID = 0";
            if(!strcasecmp($zone,"Main")) $query .= " AND WebSubPageName = 'Main'";
            else $query .= " AND WebSubPageName = 'RSM'";

            $ref = $db -> fetchAll($query);
            foreach($ref as $result){
                //check uniques and use if possible
                $query = "SELECT SensorLabel,PageLocX,PageLocY,WebSensRefNum
                        FROM WebRefTable
                        WHERE SensorLabel = '" . $result['SensorLabel'] . "' AND Inhibit = 0 AND SysID = " . $systemID;
                if(!strcasecmp($zone,"Main")) $query .= " AND WebSubPageName = 'Main'";
                else $query .= " AND WebSubPageName = 'RSM'";
                $unique = $db -> fetchRow($query);
                if($db -> numRows($query) != 0) $result = $unique;
                $xpos = $result['PageLocX'];
                $ypos = $result['PageLocY'];
                $label = $result['SensorLabel'];
                $id = $result['WebSensRefNum'];
        ?>
                <p class="label-status" style="top:<?=$ypos-20?>px;left:<?=$xpos+5?>px;"><?=$label?></p>
                <p class="value-status" style="top:<?=$ypos?>px;left:<?=$xpos?>px;line-height:1.2em">
                    <a href="javascript:void(0);" class="icon-pencil" title="Edit"
                        onclick="window.open(
                            'edit.php?sys=<?=$systemID?><?=($rsm != 0) ? "&rsm=" . $rsm : ""?>&id=<?=$id?>',
                            'Popup','width=800,height=450,dependent=yes,0,status=0,resizable=1');">
                    </a>
                </p>
        <?php
            }
        ?>
        <div class="span10" style="margin-left:50px">
            <p style="float:right">
            <?php
                if($zone != "Main") echo "<a href=\"./\">Main</a><br>";
                for($i=1;$i<=$numOfRSM;$i++){
                    if($zone == "RSM" . $i) continue;
                    echo "<a href=\"./?z=" . $i . "\">RSM" . $i . "</a><br>";
                }
            ?>
            </p>
        </div>
    </div>
</div>
<?php
    include_once('../../includes/footer.php');
?>
