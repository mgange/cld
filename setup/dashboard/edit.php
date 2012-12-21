<?php
/**
 *------------------------------------------------------------------------------
 * Edit Dashboard Items
 *------------------------------------------------------------------------------
 *
 */
require_once('../../includes/pageStart.php');

checkSystemSet($config);

$systemID = $_SESSION['SysID'];
$buildingID = $_SESSION['buildingID'];

$db = new db($config);

if((!isset($_GET['id'])) || (!isset($_GET['sys']))) header("Location: ../../");
$query = "SELECT * FROM SystemConfig WHERE SysID = " . $_GET['sys'];
$systemInfo = $db -> fetchRow($query);

$query = "SELECT A.SensorLabel,A.PageLocX,A.PageLocY,
            B.Recnum,B.SysGroup,B.SourceID,B.SensorType,B.SensorStatus,
            B.AlarmUpLimit,B.AlarmLoLimit,B.AlertPercent,B.AlarmTrigger
            FROM WebRefTable AS A, SysMap AS B
            WHERE B.Recnum = " . $_GET['id'];
$result = $db -> fetchRow($query);

$sensorStatus = $result['SensorStatus'];

//require_once('../../includes/header.php');
?>
<link rel="stylesheet" href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>css/main.css">

<form>
    <h2 class="offset1"><?=$result['SensorLabel']?></h2>
    <div class="row">
        <div class="span5 offset1">
            <label for="sourceID">Source ID<br>
                <select name="sourceID"<?=($result['SourceID'] == 4) ? " disabled=\"disabled\"" : ""?>>
                    <?php
                        for($i=0;$i<$systemInfo['NumofRSM'];$i++){
                            $select = "";
                            if($i == $result['SourceID']) $select = " selected=\"selected\"";
                            if($i == 4){
                                echo "<option value=\"" . $i . "\">" . $i . "</option>";
                                echo "<option value=\"" . ($i + 1) . "\">" . ($i + 1) . "</option>";
                            }else{
                                if($i > 4) echo "<option value=\"" . ($i + 1) . "\">" . ($i + 1) . "</option>";
                                else echo "<option value=\"" . $i . "\"" . $select . ">" . $i . "</option>";
                            }
                        }
                        if($i <= 4){
                            if($result['SourceID'] == 4) echo "<option value=\"4\" selected=\"selected\">4</option>";
                            else echo "<option value=\"4\">4</option>";
                        }
                    ?>
                </select>
            </label>
            <?php if($result['SourceID'] == 4){ ?>
                <label for="sysGroup">Meter<br>
                    <select name="sysGroup">
                        <?php
                            if($result['SensorType'] == 6) $max = $systemInfo['NumofPowers'];   //power meter
                            elseif($result['SensorType'] == 7) $max = $systemInfo['NumofTherms'];   //therm meter
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
                <select name="sensorStatus">
                    <?php $select = "selected=\"selected\""; ?>
                    <option value="0"<?=($sensorStatus == 0) ? $select : ""?>>Inhibited</option>
                    <option value="1"<?=($sensorStatus == 1) ? $select : ""?>>Alarm On</option>
                    <option value="3"<?=($sensorStatus == 3) ? $select : ""?>>Alarm Off</option>
                </select>
            </label>
        </div>
        <div class="span5">
            <label for="highLimit">Alarm High Limit<br>
                <input type="text" class="span2" name="highLimit" value="<?=$result['AlarmUpLimit']?>">
            </label>
            <label for="lowLimit">Alarm Low Limit<br>
                <input type="text" class="span2" name="lowLimit" value="<?=$result['AlarmLoLimit']?>">
            </label>
            <label for="percentWarn">Alarm Warning Percentage<br>
                <input type="text" class="span2" name="percentWarn" value="<?=$result['AlertPercent']?>">
            </label>
            <label for="alarmTrigger">Alarm After<br>
                <input type="text" class="span2" name="alarmTrigger" value="<?=$result['AlarmTrigger']?>"> minutes
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
    </div>
</form>
