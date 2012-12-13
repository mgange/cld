<?php
/**
 *------------------------------------------------------------------------------
 * Add Maintenance Index Page
 *------------------------------------------------------------------------------
 *
 */
/*
function isValidDate($date){
    $date = new DateTime($date);
    $month = $date -> format('m');
    $day = $date -> format('d');
    $year = $date -> format('Y');
    if(checkdate($month, $day, $year)) return true;
    return false;
}
*/
?>

<?php
    $query = "SELECT * FROM MaintainReference WHERE SysID = " . $systemID;
    $referenceList = $db -> fetchAll($query);
    $cnt = 0;
    if(count($referenceList) == 0) $referenceList = array(array());    //empty array to still pass through foreach
    foreach($referenceList as $default){
?>

<form name="maintainForm<?=$cnt?>" action="./" method="post" id="validateForm" onsubmit="return validate(this.name)">
    <div class="row">
        <span style="margin-left:50px;color:red">*</span> Required Fields<br><br>
        <div class="span5" style="margin-left:50px">
            <label for="workOrder">Work Order #
            <?php
                echo "<input class=\"span5\" type=\"text\" name=\"workOrder\" value=\"" . $systemID . "-";
                $query = "SELECT MAX(WorkOrder) AS id FROM MaintainLog WHERE SysID = " . $systemID;
                $id = $db -> fetchRow($query);
                if($id['id']){
                    $orderNum = explode("-",$id['id']);
                    $orderNum[1]++;
                    echo ($cnt + $orderNum[1]);
                }else echo $cnt;
                echo "\" readonly=\"readonly\">";
            ?>
            </label>
            <label for="sysComponent">System Component
                <input class="span5" type="text" name="sysComponent" value="<?=$default['SystemComponent']?>" readonly="readonly">
            </label>
            <label for="requiredAction"><span style="color:red">*</span> Required Action
                <input class="span5" type="text" name="requiredAction" value="<?=$default['RequiredAction']?>">
            </label>
            <label for="dateRequired"><span style="color:red">*</span> Date Required<br>
                <input class="datepick" type="text" name="dateRequired">
            </label>
            <label for="maintainCycle"><span style="color:red">*</span> Maintenance Cycle<br>
                <input class="span1" style="text-align:right" type="text" name="maintainCycle" value="<?=$default['maintainCycle']?>" onkeyup="isNumeric(<?=$cnt?>)">&nbsp;days
                <span id="maintainCycleSpan" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
            </label>
            <label for="autoSch">Auto Schedule<br>
                <select name="autoSch">
                    <option value="1"<?=($default['AutoSchedule'] == 1) ? " selected=\"selected\"" : ""?>>On</option>
                    <option value="0"<?=($default['AutoSchedule'] == 0) ? " selected=\"selected\"" : ""?>>Off</option>
                </select>
            </label>
        </div>
        <div class="span5" style="margin-left:50px">
            <label for="maintainerName"><span style="color:red">*</span> Maintainer Name<br>
                <select name="maintainerName" onchange="maintainerChange(<?=$cnt?>)">
                    <option value="-" selected="selected">Select A Maintainer
                    <?php
                        $query="SELECT Name FROM MaintainResource WHERE Category = 'Maintainer'";
                        $maintainerList = $db -> fetchAll($query);
                        foreach($maintainerList as $result) echo "<option value=\"" . $result['Name'] . "\">" . $result['Name'] . "</option>";
                    ?>
                </select>
            </label>
            <label for="maintainerCompany">Maintainer Company<br>
                <input type="text" name="maintainerCompany" readonly="readonly">
                <?php
                    $query="SELECT Name,Company FROM MaintainResource WHERE Category = 'Maintainer'";
                    $maintainerList = $db -> fetchAll($query);
                    foreach($maintainerList as $result) echo "<input type=\"hidden\" name=\"" . $result['Name'] . "\" value=\"" . $result['Company'] . "\" style=\"visibility:hidden\">";
                ?>
            </label>
            <label for="notify">Notify Upon Alarm?&nbsp;&nbsp;
                <input type="checkbox" name="notify" onclick="showNotifyName(<?=$cnt?>)"<?=($default['Alarm'] == 1) ? " checked=\"checked\"" : ""?>>
            </label>
            <label for="notifyName[]"<?=($default['Alarm'] == 0) ? " style=\"visibility:hidden\"" : ""?>><span style="color:red">*</span> Who to Notify<br>
                <select name="notifyName[]"<?=($default['Alarm'] == 0) ? " style=\"visibility:hidden\"" : ""?> multiple>
<?php
                $query = "SELECT CustomerID FROM buildings WHERE buildingID = " . $buildingID;
                $customer = $db -> fetchAll($query);
                foreach($customer as $result){
                    $query = "SELECT email1,email2 FROM customers WHERE customerID = " . $result['CustomerID'];
                    $email = $db -> fetchRow($query);
                    if(isset($email['email1'])) echo "<option value=\"" . $email['email1'] . "\">" . $email['email1'] . "</option>";
                    if(isset($email['email2'])) echo "<option value=\"" . $email['email2'] . "\">" . $email['email2'] . "</option>";
                }
?>
                </select>
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
    <input type="hidden" name="buildingID" value="<?=$_POST['buildingID']?>">
    <input type="hidden" name="systemID" value="<?=$_POST['systemID']?>">
    <input type="hidden" name="maintainNew" value="true">
</form>
<hr>
<br>

<?php
        $cnt++;
    }
?>
