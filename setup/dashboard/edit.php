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
    //check if there is an unique
    $query = "SELECT * FROM WebRefTable WHERE WebSensRefNum = " . $_POST['WebSensRefNum'] . " AND WebSubPageName = '" . $_POST['WebPage'] . "' AND SysID = " . $systemID;
    if($db -> numRows($query) == 0){
        //no unique, check if default changed
        $query = "SELECT * FROM WebRefTable WHERE PageLocX = " . $_POST['xpos'] . " AND PageLocY = " . $_POST['ypos'] . " AND WebSensRefNum = " . $_POST['WebSensRefNum'] . " AND WebSubPageName = '" . $_POST['WebPage'] . "' AND SysID = 0";
        if($db -> numRows($query) == 0){
            //insert unique
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
            $query = "UPDATE WebRefTable SET SysID = " . $systemID . ", PageLocX = " . $_POST['xpos'] . ", PageLocY = " . $_POST['ypos'] . " WHERE WebSubPageName = '" . $_POST['WebPage'] . "' AND Recnum = " . $lastinsert;
            $db -> execute($query);
        }
    }else{
        //update unique
        $query = "UPDATE WebRefTable SET PageLocX = " . $_POST['xpos'] . ", PageLocY = " . $_POST['ypos'] . " WHERE WebSubPageName = '" . $_POST['WebPage'] . "' AND WebSensRefNum = " . $_POST['WebSensRefNum'] . " AND SysID = ". $systemID;
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
    if($db -> numRows($query) == 0){
        //check if already an unique
        $query = "SELECT * FROM SysMap WHERE WebSensRefNum = " . $_POST['WebSensRefNum'] . " AND SourceID = " . (isset($_POST['sourceID']) ? $_POST['sourceID'] : "4") . " AND SysID = " . $systemID;
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
                     . ", SensorStatus = " . $_POST['sensorStatus']
                     . " WHERE Recnum = " . $lastinsert;
            $db -> execute($query);
        }else{
            //update
            $query = "UPDATE SysMap SET SourceID = " . (isset($_POST['sourceID']) ? $_POST['sourceID'] : "4")
                     . ", AlarmUpLimit = " . (($_POST['highLimit'] == "") ? "NULL" : $_POST['highLimit'])
                     . ", AlarmLoLimit = " . (($_POST['lowLimit'] == "") ? "NULL" : $_POST['lowLimit'])
                     . ", AlertPercent = " . $_POST['percentWarn'] . ", AlarmTrigger = " . $_POST['alarmTrigger']
                     . ", SensorStatus = " . $_POST['sensorStatus']
                     . " WHERE SysID = " . $systemID . " AND WebSensRefNum = " . $_POST['WebSensRefNum'];
            $db -> execute($query);
        }
    }
    //if changed check if uniqe value is there and update or insert

    echo "<script type=\"text/javascript\">opener.location.reload(true);window.close();</script>";
}

if((!isset($_GET['id'])) || (!isset($_GET['sys']))) header("Location: ../../");
$query = "SELECT * FROM SystemConfig WHERE SysID = " . $_GET['sys'];
$systemInfo = $db -> fetchRow($query);


//query WebRef information
$queryTemp = "SELECT WebSensRefNum,SensorLabel,PageLocX,PageLocY
            FROM WebRefTable
            WHERE WebSensRefNum = " . $_GET['id'];
if(!isset($_GET['rsm'])) $queryTemp .= " AND WebSubPageName = 'Main'";
else $queryTemp .= " AND WebSubPageName = 'RSM'";
$query = $queryTemp . " AND SysID = " . $systemID;
//if no uniques, use default
if($db -> numRows($query) == 0) $query = $queryTemp . " AND SysID = 0";
$WebRefResult = $db -> fetchRow($query);


//query SysMap information
$queryTemp = "SELECT SysGroup,SourceID,SensorType,SensorStatus,
            AlarmUpLimit,AlarmLoLimit,AlertPercent,AlarmTrigger
            FROM SysMap
            WHERE WebSensRefNum = " . $_GET['id'];
$query = $queryTemp . " AND SysID = " . $systemID;
//if no uniques, use default
if($db -> numRows($query) == 0) $query = $queryTemp . " AND SysID = 0";
$SysMapResult = $db -> fetchRow($query);

$sensorStatus = $SysMapResult['SensorStatus'];

//require_once('../../includes/header.php');
?>
<link rel="stylesheet" href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>css/main.css">

<script type="text/javascript">
    function isNumeric(Name){
        var element = document.getElementsByName(Name)[0];
        var elementSpan = document.getElementsByName(Name + "Span")[0];
        element.value = element.value.replace(/\s/g,"");    //get rid of spaces
        var valid;
        //low and high limits can be null
        if((element.name == "lowLimit") || (element.name == "highLimit")) valid = !isNaN(element.value);
        else valid = (!isNaN(element.value) && parseInt(element.value));

        if(element.value < 0) valid = false;
        if(!valid){
            element.style.border = "red solid 2px";
            element.style.display = "block";
            elementSpan.style.visibility = "visible";
            elementSpan.style.fontSize = "14px";
        }else{
            element.style.border = "";
            element.style.display = "";
            elementSpan.style.visibility = "hidden";
            elementSpan.style.fontSize = "0px";
            if((element.name == "lowLimit") || (element.name == "highLimit")){
                //disable or enable sensor alarm option depending on limit values
                if(element.value != ""){
                    document.getElementsByName("sensorStatus")[0].disabled = "";
                    document.getElementsByName("sensorStatus")[0].options["hide"].style.display = "none";
                    document.getElementsByName("sensorStatus")[0].value = 1;
                }else{
                    document.getElementsByName("sensorStatus")[0].disabled = "disabled";
                    document.getElementsByName("sensorStatus")[0].options["hide"].style.display = "";
                    document.getElementsByName("sensorStatus")[0].value = 2;
                }
            }else if(element.name == "alarmTrigger"){
                //Alarm trigger cannot be less than 5
                if(element.value < 5){
                    document.getElementsByName(Name + "Span2")[0].style.visibility = "visible";
                    document.getElementsByName(Name + "Span2")[0].style.fontSize = "14px";
                    document.getElementsByName(Name + "Span2")[0].style.display = "block";
                }else{
                    document.getElementsByName(Name + "Span2")[0].style.visibility = "";
                    document.getElementsByName(Name + "Span2")[0].style.fontSize = "0px";
                    document.getElementsByName(Name + "Span2")[0].style.display = "";
                }
            }
        }
    }
    function noErrors(){
        var all = document.forms[0];
        for(var i=0;i<all.length;i++){
            if(all[i].style.border != ""){
                alert("Please fix errors before submitting");
                return false;
            }
        }
    }
</script>

<form action="" method="post" onsubmit="return noErrors()">
    <h2 class="offset1"><?=$WebRefResult['SensorLabel']?></h2>
    <div class="row">
        <div class="span5 offset1">
            <label for="sourceID">Source ID<br>
                <?php
                    if(($SysMapResult['SensorType'] == 6) || ($SysMapResult['SensorType'] == 7)){
                        echo "<select name=\"sourceID\" disabled=\"disabled\">";
                        echo "<option value=\"4\">4</option>";
                    }else{
                        echo "<select name=\"sourceID\">";
                        for($i=0;$i<=$systemInfo['NumofRSM'];$i++){
                            $select = "";
                            if($i == $SysMapResult['SourceID']) $select = " selected=\"selected\"";
                            if($i >= 4){
                                echo "<option value=\"" . ($i + 1) . "\"" . $select . ">" . ($i + 1) . "</option>";
                            }else echo "<option value=\"" . $i . "\"" . $select . ">" . $i . "</option>";
                        }
                    }
                    echo "</select>";
                ?>
            </label>
            <?php if($SysMapResult['SourceID'] == 4){ ?>
                <label for="sysGroup"><?php
                        if($SysMapResult['SensorType'] == 6){
                            $max = $systemInfo['NumofPowers'];   //power meter
                            echo "Power Meter<br>";
                        }elseif($SysMapResult['SensorType'] == 7){
                            $max = $systemInfo['NumofTherms'];   //therm meter
                            echo "Thermostat<br>";
                        }
                    ?>
                    <select name="sysGroup">
                        <?php
                            for($i=1;$i<=$max;$i++){
                                $select = "";
                                if($i == $SysMapResult['SysGroup']) $select = " selected=\"selected\"";
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
                <input type="text" class="span2" style="height:30px" name="xpos" value="<?=$WebRefResult['PageLocX']?>" onkeyup="isNumeric(this.name)">
                <span name="xposSpan" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
            </label>
            <label for="ypos">Y-Position<br>
                <input type="text" class="span2" style="height:30px" name="ypos" value="<?=$WebRefResult['PageLocY']?>" onkeyup="isNumeric(this.name)">
                <span name="yposSpan" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
            </label>
        </div>
        <div class="span5">
            <label for="highLimit">Alarm High Limit<br>
                <input type="text" class="span2" style="height:30px" name="highLimit" value="<?=$SysMapResult['AlarmUpLimit']?>" onkeyup="isNumeric(this.name)">
                <span name="highLimitSpan" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
            </label>
            <label for="lowLimit">Alarm Low Limit<br>
                <input type="text" class="span2" style="height:30px" name="lowLimit" value="<?=$SysMapResult['AlarmLoLimit']?>" onkeyup="isNumeric(this.name)">
                <span name="lowLimitSpan" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
            </label>
            <label for="percentWarn">Alarm Warning Percentage<br>
                <input type="text" class="span2" style="height:30px" name="percentWarn" value="<?=$SysMapResult['AlertPercent']?>" onkeyup="isNumeric(this.name)">
                <span name="percentWarnSpan" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
            </label>
            <label for="alarmTrigger">Alarm After<br>
                <input type="text" class="span2" style="height:30px" name="alarmTrigger" value="<?=$SysMapResult['AlarmTrigger']?>" onkeyup="isNumeric(this.name)"> minutes
                <span name="alarmTriggerSpan" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
                <span name="alarmTriggerSpan2" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Must be Greater than 5</span>
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
        <input type="hidden" name="WebSensRefNum" value="<?=$WebRefResult['WebSensRefNum']?>">
        <input type="hidden" name="WebPage" value="<?=isset($_GET['rsm']) ? 'RSM' : 'Main'?>">
    </div>
</form>