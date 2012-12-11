<?php
/**
 *------------------------------------------------------------------------------
 * Add Maintenance Index Page
 *------------------------------------------------------------------------------
 *
 */
?>

<script type="text/javascript">
    function showNotifyName(index){
        if(document.getElementsByName("notify" + index)[0].checked){
            document.getElementsByName("notifyNameLabel" + index)[0].style.visibility = "visible";
            document.getElementsByName("notifyName" + index)[0].style.visibility = "visible";
        }else{
            document.getElementsByName("notifyNameLabel" + index)[0].style.visibility = "hidden";
            document.getElementsByName("notifyName" + index)[0].style.visibility = "hidden";
        }
    }
    function maintainerChange(index){
        var name = document.getElementsByName("maintainerName" + index)[0];
        var company = document.getElementsByName("maintainerCompany" + index)[0];
        if(name.value == "-") company.value = "";
        else company.value = document.getElementsByName(name.value)[0].value;
    }
    function isNumeric(cnt){
        var element = document.forms["maintainForm" + cnt]["maintainCycle"];
        var elementSpan = document.getElementsByName("maintainCycleSpan" + cnt)[0];
        var valid = (!isNaN(element.value) && parseInt(element.value));
        if(element.value < 0) valid = false;
        if(!valid){
            element.style.border = "red solid 2px";
            elementSpan.style.display = "block";
            elementSpan.style.visibility = "visible";
            elementSpan.style.fontSize = "14px";
        }else{
            element.style.border = "";
            elementSpan.style.display = "";
            elementSpan.style.visibility = "hidden";
            elementSpan.style.fontSize = "0px";
        }
    }
    function validate(){
        var form = document.forms["maintainForm0"];
        for(var i=0;i<form.length;i++){
            if((form[i].type != "hidden") && (form[i].type != "submit")){
                if(form[i].value == ""){
                    form[i].style.border = "red solid 2px";
                }
            }
        }
        return false;
        //document.forms["maintainForm0"].getElementsByTagName("input").length;
    }
</script>

<?php
    $query = "SELECT * FROM MaintainReference WHERE SysID = " . $systemID;
    $referenceList = $db -> fetchAll($query);
    $cnt = 0;
    if(count($referenceList) == 0) $referenceList = array(array());    //empty array to still pass through foreach
    foreach($referenceList as $default){
?>

<form name="maintainForm<?=$cnt?>" action="./" method="post" onsubmit="return validate()">
    <div class="row">
        <div class="span5" style="margin-left:50px">
            <label for="workOrder">Work Order #
            <?php
                echo "<input class=\"span5\" type=\"text\" name=\"workOrder\" value=\"" . $systemID . "-";
                $query = "SELECT MAX(Recnum) AS id FROM MaintainLog";
                $id = $db -> fetchRow($query);
                if($id['id']){
                    $id['id']++;
                    echo ($cnt + $id['id']);
                }else echo $cnt;
                echo "\" readonly=\"readonly\">";
            ?>
            </label>
            <label for="sysComponent">System Component
                <input class="span5" type="text" name="sysComponent" value="<?=$default['SystemComponent']?>" readonly="readonly">
            </label>
            <label for="actionRequired">Required Action
                <input class="span5" type="text" name="actionRequired" value="<?=$default['RequiredAction']?>">
            </label>
            <label for="dateRequired">Date Required<br>
                <input class="datepick" type="text" name="dateRequired">
            </label>
            <label for="maintainCycle">Maintain Cycle<br>
                <input class="span1" style="text-align:right" type="text" name="maintainCycle" value="<?=$default['maintainCycle']?>" onkeyup="isNumeric(<?=$cnt?>)">&nbsp;days
                <span name="maintainCycleSpan<?=$cnt?>" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
            </label>
            <label for="autoSch">Auto Schedule<br>
                <select name="autoSch">
                    <option value="1"<?=($default['AutoSchedule'] == 1) ? " selected=\"selected\"" : ""?>>On</option>
                    <option value="0"<?=($default['AutoSchedule'] == 0) ? " selected=\"selected\"" : ""?>>Off</option>
                </select>
            </label>
        </div>
        <div class="span5" style="margin-left:50px">
            <label for="maintainerName<?=$cnt?>">Maintainer Name<br>
                <select name="maintainerName<?=$cnt?>" onchange="maintainerChange(<?=$cnt?>)">
                    <option value="-" selected="selected">Select A Maintainer
                    <?php
                        $query="SELECT Name FROM MaintainResource WHERE Category = 'Maintainer'";
                        $maintainerList = $db -> fetchAll($query);
                        foreach($maintainerList as $result) echo "<option value=\"" . $result['Name'] . "\">" . $result['Name'] . "</option>";
                    ?>
                </select>
            </label>
            <label for="maintainerCompany<?=$cnt?>">Maintainer Company<br>
                <input type="text" name="maintainerCompany<?=$cnt?>" readonly="readonly">
                <?php
                    $query="SELECT Name,Company FROM MaintainResource WHERE Category = 'Maintainer'";
                    $maintainerList = $db -> fetchAll($query);
                    foreach($maintainerList as $result) echo "<input type=\"hidden\" name=\"" . $result['Name'] . "\" value=\"" . $result['Company'] . "\" style=\"visibility:hidden\">";
                ?>
            </label>
            <label for="notify<?=$cnt?>">Notify Upon Alarm?&nbsp;&nbsp;
                <input type="checkbox" name="notify<?=$cnt?>" onclick="showNotifyName(<?=$cnt?>)"<?=($default['Alarm'] == 1) ? " checked=\"checked\"" : ""?>>
            </label>
            <label for="nofityName<?=$cnt?>" name="notifyNameLabel<?=$cnt?>"<?=($default['Alarm'] == 0) ? " style=\"visibility:hidden\"" : ""?>>Who to Notify<br>
                <input class="span4"<?=($default['Alarm'] == 0) ? " style=\"visibility:hidden\"" : ""?> type="text" name="notifyName<?=$cnt?>">
            </label>
            <label for="description">Description
                <textarea class="span5" type="text" style="resize:none" name="description" rows="3"><?=$default['Description']?></textarea>
            </label>
            <label for="comments">Comments
                <textarea class="span5" type="text" style="resize:none" name="comments" rows="3"><?=$default['Comment']?></textarea>
            </label>
        </div>
    </div>
    <div class="row">
        <div class="span10 offset1">
            <button type="submit" class="btn btn-success">
                <i class="icon-ok icon-white"></i>
                Save
            </button>
            <a href="../" class="btn pull-right">
                <i class="icon-remove"></i>
                Cancel
            </a>
        </div>
    </div>
    <input type="hidden" name="customerID" value="<?=$customerInfo['customerID']?>">
</form>
<hr>
<br>

<?php
        $cnt++;
    }
?>
