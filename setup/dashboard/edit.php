<?php
/**
 *------------------------------------------------------------------------------
 * Edit Dashboard Items
 *------------------------------------------------------------------------------
 *
 */
require_once('../../includes/pageStart.php');

checkSystemSet($config);

$systemID = $_GET['sys'];
//$buildingID = $_SESSION['buildingID'];

$db = new db($config);

if(count($_POST)){
    //query to check if x y was changed
    $query = "SELECT * FROM WebRefTable WHERE PageLocX = " . $_POST['xpos'] . " AND PageLocY = " . $_POST['ypos'] . " AND WebSensRefNum = " . $_POST['WebSensRefNum'] . " AND WebSubPageName = '" . $_POST['WebPage'] . "'";
    //if changed add an unique to the webreftable
    if($db -> numRows($query) == 0){
        //duplicate row first then update
        $query = "SELECT * FROM WebRefTable WHERE WebSensRefNum = " . $_POST['WebSensRefNum'] . " AND WebSubPageName = '" . $_POST['WebPage'] . "'";
        $sth = $db -> prepare($query);
        $sth -> execute();
        $result = $sth -> fetch(PDO::FETCH_NUM);
        $query = "INSERT INTO WebRefTable VALUES(NULL, ";
        for($i=1;$i<count($result);$i++) $query .= (isset($result[$i]) ? "'" . $result[$i] . "', " : "NULL, ");
        //remove last , with )
        $query = substr_replace($query,")",strlen($query) - 2);
        $db -> execute($query);
        $lastinsert = $db -> lastInsertId();
        $query = "UPDATE WebRefTable SET SysID = " . $systemID . ", PageLocX = " . $_POST['xpos'] . ", PageLocY = " . $_POST['ypos'] . ", WebSubPageName = '" . $_POST['WebPage'] . "' WHERE Recnum = " . $lastinsert;
        $db -> execute($query);
    }

    //grab the websensrefnum either just inserted or one there
    //check if sysmap row was changed
    $query = "SELECT * FROM SysMap WHERE SourceID = " . (isset($_POST['sourceID']) ? $_POST['sourceID'] : "4")
            . " AND SensorStatus = " . (isset($_POST['sensorStatus']) ? $_POST['sensorStatus'] : "2")
            . " AND AlarmUpLimit " . (($_POST['highLimit'] != "") ? "= " . $_POST['highLimit'] : "IS NULL")
            . " AND AlarmLoLimit " . (($_POST['lowLimit'] != "") ? "= " . $_POST['lowLimit'] : "IS NULL")
            . " AND AlertPercent = " . $_POST['percentWarn'] . " AND AlarmTrigger = " . $_POST['alarmTrigger']
            . " AND WebSensRefNum = " . $_POST['WebSensRefNum'];
            echo $query . "<br>";
    if($db -> numRows($query) == 0){
        //check if already an unique
        $query = "SELECT * FROM SysMap WHERE WebSensRefNum = " . $_POST['WebSensRefNum'] . " AND SysID = " . $systemID;
        //die($query);
        if($db -> numRows($query) == 0){
            //add
            //duplicate row first then update
            $query = "SELECT * FROM SysMap WHERE WebSensRefNum = " . $_POST['WebSensRefNum'];
            $sth = $db -> prepare($query);
            $sth -> execute();
            $result = $sth -> fetch(PDO::FETCH_NUM);
            $query = "INSERT INTO SysMap VALUES(NULL, ";
            for($i=1;$i<count($result);$i++) $query .= (isset($result[$i]) ? "'" . $result[$i] . "', " : "NULL, ");
            //remove last , with )
            $query = substr_replace($query,")",strlen($query) - 2);
            $db -> execute($query);
            $lastinsert = $db -> lastInsertId();
            $query = "UPDATE SysMap SET DAMID = '" . $_POST['DAMID'] ."', SysID = " . $systemID . ", SourceID = " . (isset($_POST['sourceID']) ? $_POST['sourceID'] : "4")
                     . ", AlarmUpLimit = " . (($_POST['highLimit'] == "") ? "NULL" : $_POST['highLimit'])
                     . ", AlarmLoLimit = " . (($_POST['lowLimit'] == "") ? "NULL" : $_POST['lowLimit'])
                     . ", AlertPercent = " . $_POST['percentWarn'] . ", AlarmTrigger = " . $_POST['alarmTrigger']
                     . " WHERE Recnum = " . $lastinsert;
            $db -> execute($query);
        }else{
            //update
            $query = "UPDATE SysMap SET SourceID = " . (isset($_POST['sourceID']) ? $_POST['sourceID'] : "4")
                     . ", AlarmUpLimit = " . (($_POST['highLimit'] == "") ? "NULL" : $_POST['highLimit'])
                     . ", AlarmLoLimit = " . (($_POST['lowLimit'] == "") ? "NULL" : $_POST['lowLimit'])
                     . ", AlertPercent = " . $_POST['percentWarn'] . ", AlarmTrigger = " . $_POST['alarmTrigger']
                     . " WHERE SysID = " . $systemID . " AND WebSensRefNum = " . $_POST['WebSensRefNum'];
                     echo $query;
            $db -> execute($query);
        }
    }
    //if changed check if uniqe value is there and update or insert

    echo "<script type=\"text/javascript\">window.close()</script>";
}

if((!isset($_GET['id'])) || (!isset($_GET['sys']))) header("Location: ../../");
$query = "SELECT * FROM SystemConfig WHERE SysID = " . $_GET['sys'];
$systemInfo = $db -> fetchRow($query);

$queryTemp = "SELECT A.WebSensRefNum,A.SensorLabel,A.PageLocX,A.PageLocY,
            B.SysGroup,B.SourceID,B.SensorType,B.SensorStatus,
            B.AlarmUpLimit,B.AlarmLoLimit,B.AlertPercent,B.AlarmTrigger
            FROM WebRefTable AS A, SysMap AS B
            WHERE A.WebSensRefNum = " . $_GET['id'] . " AND B.WebSensRefNum = " . $_GET['id'];
if(!isset($_GET['rsm'])) $queryTemp .= " AND A.WebSubPageName = 'Main'";
else $queryTemp .= " AND A.WebSubPageName = 'RSM'";
$query = $queryTemp . " AND A.SysID = " . $systemID . " AND B.SysID = " . $systemID;

//if no uniques, use default
if($db -> numRows($query) == 0) $query = $queryTemp . " AND A.SysID = 0 AND B.SysID = 0";
$result = $db -> fetchRow($query);

$sensorStatus = $result['SensorStatus'];

//require_once('../../includes/header.php');
?>
<link rel="stylesheet" href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>css/main.css">

<script type="text/javascript">
    function SensorStatus(value){
        if(value != ""){
            document.getElementsByName("sensorStatus")[0].disabled = "";
            document.getElementsByName("sensorStatus")[0].options["hide"].style.display = "none";
            document.getElementsByName("sensorStatus")[0].value = 1;
        }else{
            highVal = document.getElementsByName("highLimit")[0].value;
            lowVal = document.getElementsByName("highLimit")[0].value;
            if((highVal == "") & (lowVal == "")){
                document.getElementsByName("sensorStatus")[0].disabled = "disabled";
                document.getElementsByName("sensorStatus")[0].options["hide"].style.display = "";
                document.getElementsByName("sensorStatus")[0].value = 2;
            }
        }
    }
</script>

<form action="" method="post">
    <h2 class="offset1"><?=$result['SensorLabel']?></h2>
    <div class="row">
        <div class="span5 offset1">
            <label for="sourceID">Source ID<br>
                <?php
                    if(($result['SensorType'] == 6) || ($result['SensorType'] == 7)){
                        echo "<select name=\"sourceID\" disabled=\"disabled\">";
                        echo "<option value=\"4\">4</option>";
                    }else{
                        echo "<select name=\"sourceID\">";
                        for($i=0;$i<=$systemInfo['NumofRSM'];$i++){
                            $select = "";
                            if($i == $result['SourceID']) $select = " selected=\"selected\"";
                            if($i >= 4){
                                echo "<option value=\"" . ($i + 1) . "\"" . $select . ">" . ($i + 1) . "</option>";
                            }else echo "<option value=\"" . $i . "\"" . $select . ">" . $i . "</option>";
                        }
                    }
                    echo "</select>";
                ?>
            </label>
            <?php if($result['SourceID'] == 4){ ?>
                <label for="sysGroup"><?php
                        if($result['SensorType'] == 6){
                            $max = $systemInfo['NumofPowers'];   //power meter
                            echo "Power Meter<br>";
                        }elseif($result['SensorType'] == 7){
                            $max = $systemInfo['NumofTherms'];   //therm meter
                            echo "Thermostat<br>";
                        }
                    ?>
                    <select name="sysGroup">
                        <?php
                            for($i=1;$i<=$max;$i++){
                                $select = "";
                                if($i == $result['SysGroup']) $select = " selected=\"selected\"";
                                echo "<option value=\"" . $i . "\"" . $select . ">" . $i . "</option>";
                            }
                        ?>
                    </select>
                </label>
            <?php } ?>
            <label for="sensorStatus">Sensor Alarm<br>
                <select name="sensorStatus"<?=($sensorStatus == 2) ? " disabled=\"disabled\"" : ""?>>
                    <?php $select = "selected=\"selected\""; ?>
                    <option value="0"<?=($sensorStatus == 0) ? $select : ""?>>Alarm Off & Hidden</option>
                    <option value="1"<?=($sensorStatus == 1) ? $select : ""?>>Alarm On</option>
                    <option name="hide" value="2"<?=($sensorStatus == 2) ? $select : ""?>>Never Alarmed</option>
                    <option value="3"<?=($sensorStatus == 3) ? $select : ""?>>Alarm Off</option>
                </select>
            </label>
            <label for="xpos">X-Position<br>
                <input type="text" class="span2" style="height:30px" name="xpos" value="<?=$result['PageLocX']?>">
            </label>
            <label for="ypos">Y-Position<br>
                <input type="text" class="span2" style="height:30px" name="ypos" value="<?=$result['PageLocY']?>">
            </label>
        </div>
        <div class="span5">
            <label for="highLimit">Alarm High Limit<br>
                <input type="text" class="span2" style="height:30px" name="highLimit" value="<?=$result['AlarmUpLimit']?>" onkeyup="SensorStatus(this.value)">
            </label>
            <label for="lowLimit">Alarm Low Limit<br>
                <input type="text" class="span2" style="height:30px" name="lowLimit" value="<?=$result['AlarmLoLimit']?>" onkeyup="SensorStatus(this.value)">
            </label>
            <label for="percentWarn">Alarm Warning Percentage<br>
                <input type="text" class="span2" style="height:30px" name="percentWarn" value="<?=$result['AlertPercent']?>">
            </label>
            <label for="alarmTrigger">Alarm After<br>
                <input type="text" class="span2" style="height:30px" name="alarmTrigger" value="<?=$result['AlarmTrigger']?>"> minutes
            </label>
        </div>
        <div class="row">
            <div class="span7 offset1">
                <br>
                <button type="submit" class="btn btn-success">
                    <i class="icon-ok icon-white"></i>
                    Save
                </button>
                <a class="btn pull-right" onclick="window.close();">
                    <i class="icon-remove"></i>
                    Cancel
                </a>
            </div>
        </div>
        <input type="hidden" name="DAMID" value="<?=$systemInfo['DAMID']?>">
        <input type="hidden" name="WebSensRefNum" value="<?=$result['WebSensRefNum']?>">
        <input type="hidden" name="WebPage" value="<?=isset($_GET['rsm']) ? 'RSM' : 'Main'?>">
    </div>
</form>
