<?php
/**
 *------------------------------------------------------------------------------
 * Alarm Limits Index Page
 *------------------------------------------------------------------------------
 *
 */
//require_once('../../includes/pageStart.php');

checkSystemSet($config);

//if($_SESSION['authLevel'] < 3) {
  //  gtfo($config);
//}

//$db = new db($config);
$SysId = $_SESSION['SysID'];
$BuildingID = $_SESSION['buildingID'];

$query = "SELECT * FROM SystemConfig WHERE SysId = $SysId AND BuildingID = $BuildingID";
$systemInfo = $db -> fetchRow($query);
$query = "SELECT * FROM buildings WHERE buildingID = $BuildingID";
$buildingInfo = $db -> fetchRow($query);
$query = "SELECT * FROM customers WHERE customerID = $buildingInfo[CustomerID]";
$customerInfo = $db -> fetchRow($query);

if(isset($_GET['id'])) $id = $_GET['id'];
if(isset($id)){
    if($id == 0) $sourceID = 4; //id of 0 for therm/power
    else if($id == 4) $sourceID = 5; //id of 4 for RSM4 is sourceid 5
    else $sourceID = $id;
}else $sourceID = 0;    //no id passed for main */

$query = "SELECT AnalogMuxEnabled FROM SystemConfig WHERE SysID = " . $SysId;
$result = $db -> fetchRow($query);
$AnalogMuxEnabled = $result['AnalogMuxEnabled'];

$query = "SELECT Recnum,SysGroup,SensorModel,SensorName,SensorType,SensorColName,SensorUnits,SensorActive,SensorAddress,AlarmUpLimit,AlarmLoLimit,AlertPercent,AlarmTrigger
        FROM SysMap WHERE SensorColName NOT LIKE CONVERT( _utf8 'bs%' USING latin1 )
        AND SensorColName NOT LIKE CONVERT( _utf8 'thermstat%' USING latin1 )
        AND SysID = 0 AND SourceID = " . $sourceID . " ORDER BY SensorType ASC, SysGroup ASC, SensorColName ASC";
$sysMap = $db -> fetchAll($query);

$query = "SELECT Recnum,SysGroup,SensorModel,SensorName,SensorType,SensorColName,SensorUnits,SensorActive,SensorAddress,AlarmUpLimit,AlarmLoLimit,AlertPercent,AlarmTrigger
        FROM SysMap WHERE SensorColName NOT LIKE CONVERT( _utf8 'bs%' USING latin1 )
        AND SensorColName NOT LIKE CONVERT( _utf8 'thermstat%' USING latin1 )
        AND SysID = " . $SysId . " AND SourceID = " . $sourceID . " ORDER BY SensorType ASC, SysGroup ASC, SensorColName ASC";
$sysMapUnique = $db -> fetchAll($query);

//require_once('../../includes/header.php');

?>

<script type="text/javascript">
    function isNumeric(Name,sourceID,Recnum){
        //flag change to check on submit
        var mydiv = document.getElementsByName("sensorMapping" + sourceID + "onChange")[0];
        var newcontent = document.createElement('div');
        newcontent.innerHTML = "<input type=\"hidden\" name=\"Change" + Recnum + "\" value=\"true\">";
        while (newcontent.firstChild) {
            mydiv.appendChild(newcontent.firstChild);
        }

        var element = document.forms["sensorMapping" + sourceID][Name + Recnum];
        var elementSpan = document.getElementsByName(Name + Recnum + "Span")[0];
        var elementSpan2 = document.getElementsByName(Name + Recnum + "Span2")[0];
        var valid = (!isNaN(element.value) && parseInt(element.value));// || ((element.value.length == 1) && (element.value != " "));
        if(element.value < 0) valid = false;
        if(!valid){
            element.style.border = "red solid 2px";
            elementSpan.style.visibility = "visible";
            elementSpan.style.fontSize = "14px";
            elementSpan2.style.visibility = "hidden";
            elementSpan2.style.fontSize = "0px";
        }else{
            element.style.border = "";
            elementSpan.style.visibility = "hidden";
            elementSpan.style.fontSize = "0px";
            if(element.value < 5){
                element.style.border = "red solid 2px";
                elementSpan2.style.visibility = "visible";
                elementSpan2.style.fontSize = "14px";
            }
        }
    }
    function duplicateCheck(sourceID,Recnum){
        //flag change to check on submit
        var mydiv = document.getElementsByName("sensorMapping" + sourceID + "onChange")[0];
        var newcontent = document.createElement('div');
        newcontent.innerHTML = "<input type=\"hidden\" name=\"Change" + Recnum +"\" value=\"true\">";
        while (newcontent.firstChild) {
            mydiv.appendChild(newcontent.firstChild);
        }

        var all = document.forms["sensorMapping" + sourceID].getElementsByTagName("select");
        var allSpan = document.getElementsByName("selectSpan" + sourceID);
        var duplicate = 0;
        for(var i=0;i<all.length;i++){
            all[i].style.border = "";
            allSpan[i].style.marginLeft = "0px";
            allSpan[i].style.visibility = "hidden";
            allSpan[i].style.fontSize = "0px";
            duplicate = 0;
            for(var j=0;j<all.length;j++){
                if(all[i].value == all[j].value) duplicate++;
                if(duplicate > 1){
                    all[i].style.border = "red solid 2px";
                    allSpan[i].style.marginLeft = "20px";
                    allSpan[i].style.visibility = "visible";
                    allSpan[i].style.fontSize = "14px";
                }
            }
        }
    }
    function noErrors(formName){
        var all = document.forms[formName];
        for(var i=0;i<all.length;i++){
            if(all[i].style.border != ""){
                alert("Please fix errors before submitting");
                return false;
            }
        }
    }
    function checkboxChange(sourceID,Recnum,type,group){
        //new value is same as next inactives
        if(sourceID == 4){  //only for power/therm
            var all = document.forms["sensorMapping" + sourceID].getElementsByTagName("input");
            var name = "Active" + Recnum;
            for(var i=0;i<all.length;i++){
                if(all[i].name == name){    //get index of the given checkbox
                    for(var j=i+1;j<all.length;j++){
                        if(all[j].type == "checkbox"){  //disable/enable for next checkboxes
                            if(all[j].disabled == false) break; //stop loop once done with all inactive checkboxes
                            all[j].checked = all[i].checked;
                        }
                    }
                    break;
                }
            }
            $('[id^=CheckboxTypeGroup' + type + group + ']').each(function(){
                $(this).after("<input type=\"hidden\" name=\"Change" + $(this).attr('name').substr(6,$(this).attr('name').length) +"\" value=\"true\">");
                if($('[name=sensorMapping' + sourceID + ']').find('[name=' + $(this).attr('name') + ']:hidden').length){
                    if($(this).attr('checked') == "checked") $('[name=sensorMapping' + sourceID + ']').find('[name=' + $(this).attr('name') + ']').val('1');
                    else $('[name=sensorMapping' + sourceID + ']').find('[name=' + $(this).attr('name') + ']').val('0');
                    console.log($(this).attr('name'));
                }else{
                    if($(this).attr('checked') == "checked") $(this).after("<input type=\"hidden\" name=\"" + $(this).attr('name') +"\" value=\"1\">");
                    else $(this).after("<input type=\"hidden\" name=\"" + $(this).attr('name') +"\" value=\"0\">");
                }
            });
        }
        //flag change to check on submit
        $('[name=sensorMapping' + sourceID + 'onChange]').after("<input type=\"hidden\" name=\"Change" + Recnum +"\" value=\"true\">");
    }
    function ChangeAddress(Recnum,sourceID,type,group,value){
        //change address for next ones in group
        $('[id^=AddressTypeGroup' + type + group + ']').val(value);
        //flag change to check on submit
        $('[id^=AddressTypeGroup' + type + group + ']').each(function(){
            $(this).after("<input type=\"hidden\" name=\"Change" + $(this).attr('name').substr(7,$(this).attr('name').length) +"\" value=\"true\">");
        });
    }
</script>

<form name="sensorMapping<?=$sourceID?>" action="./" method="post" onsubmit="return noErrors('sensorMapping<?=$sourceID?>')">
    <div class="row">
        <h4 class="span2" style="width:130px">Sensor</h4>
        <?php if($sourceID == 4){ ?>
            <h4 class="span1">Model</h4>
            <h4 class="span1">Address</h4>
        <?php } ?>
        <h4 class="span2">Channel</h4>
        <h4 class="span2" style="width:100px">Low Limit</h4>
        <h4 class="span2" style="width:100px">High Limit</h4>
        <h4 <?=($sourceID == 4) ? "class=\"span1\" style=\"width:70px\"" : "class=\"span2\""?>>Percent Threshold</h4>
        <h4 <?=($sourceID == 4) ? "class=\"span1\" style=\"width:100px\"" : "class=\"span2\""?>>Trigger Alarm After</h4>
        <h4 class="span1" style="width:10px;text-align:center">Active</h4>
    </div>
    <hr>
<?php
    $sysGroup = 0;
    foreach ($sysMap as $resultRow){
        //check for uniques and use if necessary
        foreach($sysMapUnique as $uniqueResult){
                if((!strcasecmp($uniqueResult['SensorColName'],$resultRow['SensorColName']))
                    && (!strcasecmp($uniqueResult['SysGroup'],$resultRow['SysGroup']))){
                    $resultRow = $uniqueResult;
                }
        }
        if((!strncasecmp($resultRow['SensorColName'],"power",5)) || ($resultRow['SensorType'] == 7)){
            if($sysGroup != $resultRow['SysGroup']){
                if($sysGroup != 0) echo "<hr>";
                $sysGroup = $resultRow['SysGroup'];
?>
    <div class="row">
        <p class="span2" style="font-size:16px;font-weight:bold;width:130px;margin-top:10px"><strong><?=($resultRow['SensorType'] == 7) ? "Thermostat " . $resultRow['SysGroup'] : substr($resultRow['SensorName'],0,2)?></strong></p>
        <?php if($sourceID == 4){ ?>
            <p class="span1" style="margin-top:10px;text-align:absolute">
                <?php
                    if(isset($resultRow['SensorModel'])){
                        $query = "SELECT * FROM SysConfigDefaults WHERE ConfigSubGroup = 'Therm' OR ConfigSubGroup = 'Power'";
                        MySQL_Pull_Down($config,$query,"Model" . $resultRow['Recnum'],"ItemName","AssignedValue",$resultRow['SensorModel'],NULL,"span1");
                    }
                ?>
            </p>
            <p class="span1" style="margin-top:10px;text-align:absolute"><?php if(isset($resultRow['SensorAddress'])){ ?><input type="text" style="max-width:50%" name="Address<?=$resultRow['Recnum']?>" id="Address<?=$resultRow['Recnum']?>" value="<?=$resultRow['SensorAddress']?>" onchange="ChangeAddress(<?=$resultRow['Recnum']?>,<?=$sourceID?>,<?=$resultRow['SensorType']?>,<?=$resultRow['SysGroup']?>,this.value)"><?php } ?></p>
        <?php } ?>
        <p class="span2">&nbsp;</p>
        <p class="span2" style="width:100px">&nbsp;</p>
        <p class="span2" style="width:100px">&nbsp;</p>
        <p <?=($sourceID == 4) ? "class=\"span1\" style=\"width:70px\"" : "class=\"span2\""?>>&nbsp;</p>
        <p <?=($sourceID == 4) ? "class=\"span1\" style=\"width:100px\"" : "class=\"span2\""?>>&nbsp;</p>
        <p class="span1" style="width:10px;text-align:center"><input type="checkbox" name="Active<?=$resultRow['Recnum']?>"<?=($resultRow['SensorActive']) ? " checked='checked'" : "" ?> onchange="checkboxChange(<?=$sourceID?>,<?=$resultRow['Recnum']?>,<?=$resultRow['SensorType']?>,<?=$resultRow['SysGroup']?>)"></p>
    </div>
    <hr>
<?php
            }
        }else{
            if($sysGroup != 0 ){
                echo "<hr>";
                $sysGroup = 0;
            }
        }
        if((!strncasecmp($resultRow['SensorColName'],"angmux",6)) && (!$AnalogMuxEnabled)) continue;
        if(isset($resultRow['AlarmUpLimit']) | isset($resultRow['AlarmLoLimit']) | isset($resultRow['AlertPercent'])){
            switch($resultRow['SensorUnits']){
                case "dF":
                    $unit = "&degF";
                    break;
                case "dC":
                    $unit = "&degC";
                    break;
                default:
                    $unit = $resultRow['SensorUnits'];
                    break;
            }
?>
    <div class="row over">
        <p class="span2" style="width:130px;margin-top:10px"><strong><?=(!strncasecmp($resultRow['SensorColName'],"power",5)) ? substr($resultRow['SensorName'],2) : $resultRow['SensorName']?></strong></p>
        <?php if($sourceID == 4){ ?>
            <p class="span1" style="margin-top:10px;text-align:absolute">
            <?php
                if(isset($resultRow['SensorModel'])){
                    if(strncasecmp($resultRow['SensorColName'],"power",5) && ($resultRow['SensorType'] != 7)) $inputType = "text";
                    else $inputType = "hidden";
                    echo "<input type=\"" . $inputType . "\" style=\"max-width:50%\" name=\"Model" . $resultRow['Recnum'] . "\" value=\"" . $resultRow['SensorModel'] . "\">";
                }
            ?>
            </p>
            <p class="span1" style="margin-top:10px;text-align:absolute">
            <?php
                if(isset($resultRow['SensorAddress'])){
                    if(strncasecmp($resultRow['SensorColName'],"power",5) && ($resultRow['SensorType'] != 7)) $inputType = "text";
                    else $inputType = "hidden";
                    echo "<input type=\"" . $inputType . "\" style=\"max-width:50%\" name=\"Address" . $resultRow['Recnum'] . "\" id=\"AddressTypeGroup" . $resultRow['SensorType'] . $resultRow['SysGroup'] . "\" value=\"" . $resultRow['SensorAddress'] . "\">";
                }
            ?>
            </p>
        <?php } ?>
        <p class="span2" style="margin-top:10px"><?php
            //Sensor Channel
            if(!strncasecmp($resultRow['SensorColName'],"senchan",7)){
                echo "<select name='" . $resultRow['SensorColName'] . "' style='max-width:90%' onchange='duplicateCheck(" . $sourceID . "," . $resultRow['Recnum'] . ")'>";
                $query = "SELECT SensorName,SensorColName,SysGroup FROM SysMap WHERE SourceID = " . $sourceID . " AND SysID = 0 AND SensorColName LIKE CONVERT(_utf8 'senchan%' USING latin1) COLLATE latin1_swedish_ci";
                $sensorChan = $db -> fetchAll($query);
                foreach ($sensorChan as $result){
                    $value = substr($result['SensorColName'],7);
                    if($value < 10) $value = substr($value,1);
                    if(($result['SensorColName'] == $resultRow['SensorColName']) & ($result['SysGroup'] == $resultRow['SysGroup'])) echo "<option selected='selected' value='" . $result['SensorName'] . "'>Sensor " . $value . "</option>";
                    else echo "<option value='" . $result['SensorName'] . "'>Sensor " . $value . "</option>";
                }
                echo "</select><span name='selectSpan" . $sourceID . "' style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Duplicated</span>";
            //Digital Input
            }else if(!strncasecmp($resultRow['SensorColName'],"digin",5)){
                echo "<select name='" . $resultRow['SensorColName'] . "' style='max-width:90%' onchange='duplicateCheck(" . $sourceID . "," . $resultRow['Recnum'] . ")'>";
                $query = "SELECT SensorName,SensorColName,SysGroup FROM SysMap WHERE SourceID = " . $sourceID . " AND SysID = 0 AND SensorColName LIKE CONVERT(_utf8 'digin%' USING latin1) COLLATE latin1_swedish_ci";
                $sensorChan = $db -> fetchAll($query);
                foreach ($sensorChan as $result){
                    $value = substr($result['SensorColName'],5);
                    if($value < 10) $value = substr($value,1);
                    if(($result['SensorColName'] == $resultRow['SensorColName']) & ($result['SysGroup'] == $resultRow['SysGroup'])) echo "<option selected='selected' value='" . $result['SensorName'] . "'>Digital In " . $value . "</option>";
                    else echo "<option value='" . $result['SensorName'] . "'>Digital In " . $value . "</option>";
                }
                echo "</select><span name='selectSpan" . $sourceID . "' style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Duplicated</span>";
            //Digital Output
            }else if(!strncasecmp($resultRow['SensorColName'],"digout",6)){
                echo "<select name='" . $resultRow['SensorColName'] . "' style='max-width:90%' onchange='duplicateCheck(" . $sourceID . "," . $resultRow['Recnum'] . ")'>";
                $query = "SELECT SensorName,SensorColName,SysGroup FROM SysMap WHERE SourceID = " . $sourceID . " AND SysID = 0 AND SensorColName LIKE CONVERT(_utf8 'digout%' USING latin1) COLLATE latin1_swedish_ci";
                $sensorChan = $db -> fetchAll($query);
                foreach ($sensorChan as $result){
                    $value = substr($result['SensorColName'],6);
                    if($value < 10) $value = substr($value,1);
                    if(($result['SensorColName'] == $resultRow['SensorColName']) & ($result['SysGroup'] == $resultRow['SysGroup'])) echo "<option selected='selected' value='" . $result['SensorName'] . "'>Digital Out " . $value . "</option>";
                    else echo "<option value='" . $result['SensorName'] . "'>Digital Out " . $value . "</option>";
                }
                echo "</select><span name='selectSpan" . $sourceID . "' style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Duplicated</span>";
            //Flow/Pressure
            }else if(!strncasecmp($resultRow['SensorColName'],"flowpress",9)){
                echo "<select name='" . $resultRow['SensorColName'] . "' style='max-width:90%' onchange='duplicateCheck(" . $sourceID . "," . $resultRow['Recnum'] . ")'>";
                $query = "SELECT SensorName,SensorColName,SysGroup FROM SysMap WHERE SourceID = " . $sourceID . " AND SysID = 0 AND SensorColName LIKE CONVERT(_utf8 'flowpress%' USING latin1) COLLATE latin1_swedish_ci";
                $sensorChan = $db -> fetchAll($query);
                foreach ($sensorChan as $result){
                    $value = substr($result['SensorColName'],9);
                    if($value < 10) $value = substr($value,1);
                    if(($result['SensorColName'] == $resultRow['SensorColName']) & ($result['SysGroup'] == $resultRow['SysGroup'])) echo "<option selected='selected' value='" . $result['SensorName'] . "'>Flow/Pressure " . $value . "</option>";
                    else echo "<option value='" . $result['SensorName'] . "'>Flow/Pressure " . $value . "</option>";
                }
                echo "</select><span name='selectSpan" . $sourceID . "' style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Duplicated</span>";
            //Analog MUX
            }else if(!strncasecmp($resultRow['SensorColName'],"angmux",6)){
                echo "<select name='" . $resultRow['SensorColName'] . "' style='max-width:90%' onchange='duplicateCheck(" . $sourceID . "," . $resultRow['Recnum'] . ")'>";
                $query = "SELECT SensorName,SensorColName,SysGroup FROM SysMap WHERE SourceID = " . $sourceID . " AND SysID = 0 AND SensorColName LIKE CONVERT(_utf8 'angmux%' USING latin1) COLLATE latin1_swedish_ci";
                $sensorChan = $db -> fetchAll($query);
                foreach ($sensorChan as $result){
                    $value = substr($result['SensorColName'],6);
                    if($value < 10) $value = substr($value,1);
                    if(($result['SensorColName'] == $resultRow['SensorColName']) & ($result['SysGroup'] == $resultRow['SysGroup'])) echo "<option selected='selected' value='" . $result['SensorName'] . "'>Analog Mux " . $value . "</option>";
                    else echo "<option value='" . $result['SensorName'] . "'>Analog Mux " . $value . "</option>";
                }
                echo "</select><span name='selectSpan" . $sourceID . "' style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Duplicated</span>";
            //Power Meter
            }else if(!strncasecmp($resultRow['SensorColName'],"power",5)){
                $query = "SELECT SensorName,SensorColName,SysGroup FROM SysMap WHERE SourceID = " . $sourceID . " AND SysID = 0 AND SensorColName LIKE CONVERT(_utf8 'power%' USING latin1) COLLATE latin1_swedish_ci AND SysGroup = " . $sysGroup;
                $sensorChan = $db -> fetchAll($query);
                echo "<select name='" . $resultRow['SensorColName'] . "' style='max-width:90%' onchange='duplicateCheck(" . $sourceID . "," . $resultRow['Recnum'] . ")'>";
                foreach ($sensorChan as $result){
                    $value = substr($result['SensorColName'],5);
                    if($value < 10) $value = substr($value,1);
                    if(($result['SensorColName'] == $resultRow['SensorColName']) & ($result['SysGroup'] == $resultRow['SysGroup'])) echo "<option selected='selected' value='" . $result['SensorName'] . "'>Power " . $value . "</option>";
                    else echo "<option value='" . $result['SensorName'] . "'>Power " . $value . "</option>";
                }
                echo "</select><span name='selectSpan" . $sourceID . "' style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Duplicated</span>";
            }
        ?></p>
        <?php
            $loValue = "Lo" . $resultRow['Recnum'];
            $hiValue = "Hi" . $resultRow['Recnum'];
            $percentValue = "Percent" . $resultRow['Recnum'];
            $triggerValue = "Trigger" . $resultRow['Recnum'];
        ?>
        <p class="span2" style="width:100px;margin-top:10px;text-align:absolute"><?php if(isset($resultRow['AlarmLoLimit'])) { ?><input name="Lo<?=$resultRow['Recnum']?>" type="text" class="span1" onkeyup="isNumeric('Lo',<?=$sourceID?>,<?=$resultRow['Recnum']?>)" style="max-width:60%;text-align:right;<?=(isset($loErrFlag[$resultRow['Recnum']])) ? "border:red solid 2px" : ""?>" value="<?=(isset($_POST[$loValue])) ? $_POST[$loValue] : $resultRow['AlarmLoLimit']?>"> <?php echo $unit; } ?>
            <span name="Lo<?=$resultRow['Recnum']?>Span" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
        </p>
        <p class="span2" style="width:100px;margin-top:10px;text-align:absolute"><?php if(isset($resultRow['AlarmUpLimit'])) { ?><input name="Hi<?=$resultRow['Recnum']?>" type="text" class="span1" onkeyup="isNumeric('Hi',<?=$sourceID?>,<?=$resultRow['Recnum']?>)" style="max-width:60%;text-align:right;<?=(isset($hiErrFlag[$resultRow['Recnum']])) ? "border:red solid 2px" : ""?>" value="<?=(isset($_POST[$hiValue])) ? $_POST[$hiValue] : $resultRow['AlarmUpLimit']?>"> <?php echo $unit; } ?>
            <span name="Hi<?=$resultRow['Recnum']?>Span" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
        </p>
        <p <?=($sourceID == 4) ? "class=\"span1\" style=\"width:70px;" : "class=\"span2\" style=\""?>margin-top:10px;text-align:absolute"><?php if(isset($resultRow['AlertPercent'])) { ?><input name="Percent<?=$resultRow['Recnum']?>" type="text" class="span1" onkeyup="isNumeric('Percent',<?=$sourceID?>,<?=$resultRow['Recnum']?>)" style="max-width:50%;text-align:right;<?=(isset($percentErrFlag[$resultRow['Recnum']])) ? "border:red solid 2px" : ""?>" value="<?=(isset($_POST[$percentValue])) ? $_POST[$percentValue] : $resultRow['AlertPercent']?>"> %<?php } ?>
            <span name="Percent<?=$resultRow['Recnum']?>Span" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
        </p>
        <p <?=($sourceID == 4) ? "class=\"span1\" style=\"width:100px\"" : "class=\"span2\""?>margin-top:10px;text-align:absolute"><input name="Trigger<?=$resultRow['Recnum']?>" type="text" class="span1" onkeyup="isNumeric('Trigger',<?=$sourceID?>,<?=$resultRow['Recnum']?>)" style="max-width:50%;text-align:right;<?=(isset($triggerErrFlag[$resultRow['Recnum']])) ? "border:red solid 2px" : ""?>" value="<?=(isset($_POST[$triggerValue])) ? $_POST[$triggerValue] : $resultRow['AlarmTrigger']?>"> mins
            <span name="Trigger<?=$resultRow['Recnum']?>Span" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
            <span name="Trigger<?=$resultRow['Recnum']?>Span2" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Must be Greater than 5</span>
        </p>
        <p class="span1" style="width:10px;margin-top:10px;text-align:absolute"><?php
            if((!strncasecmp($resultRow['SensorColName'],"power",5)) || ($resultRow['SensorType'] == 7)){?>
                <input type="checkbox" name="Active<?=$resultRow['Recnum']?>"<?=($resultRow['SensorActive']) ? " checked='checked'" : "" ?> id="CheckboxTypeGroup<?=$resultRow['SensorType'] . $resultRow['SysGroup']?>" onchange="checkboxChange(<?=$sourceID?>,<?=$resultRow['Recnum']?>)" disabled></p>
            <?php }else{ ?>
                <input type="checkbox" name="Active<?=$resultRow['Recnum']?>"<?=($resultRow['SensorActive']) ? " checked='checked'" : "" ?> value="1" onchange="checkboxChange(<?=$sourceID?>,<?=$resultRow['Recnum']?>)"></p>
            <?php } ?>
    </div>
<?php
        }
    }
?>
    <div class="row">
        <br>
        <div class="span10 offset1">
            <button type="submit" class="btn btn-success">
                <i class="icon-pencil icon-white"></i>
                Update
            </button>
            <a href="../" class="btn pull-right">
                <i class="icon-remove"></i>
                Cancel
            </a>
        </div>
    </div>
    <div name="sensorMapping<?=$sourceID?>onChange"></div>
    <input type="hidden" name="customerID" value="<?=$customerInfo['customerID']?>">
    <input type="hidden" name="sourceID" value="<?=$sourceID?>">
    <input type="hidden" name="submitSensorMap" value="true">
</form>
