<?php
/**
 *------------------------------------------------------------------------------
 * Edit Maintenance Index Page
 *------------------------------------------------------------------------------
 *
 */
?>

<form class="validate" name="maintainFormEdit" action="./" method="post" id="validateForm" onsubmit="return validate(this.name)">
    <div class="row">
        <span style="margin-left:50px;color:red">*</span>&nbsp;Required Fields<br><br>
        <div class="span5" style="margin-left:50px">
            <label for="workOrder">Work Order #
                <input class="span5" type="text" name="workOrder" value="<?=$result['WorkOrder']?>" readonly="readonly">
            </label>
            <label for="sysComponent">System Component
                <input class="span5" type="text" name="sysComponent" value="<?=$result['SystemComponent']?>" readonly="readonly">
            </label>
            <label for="requiredAction"><span style="color:red">*</span>&nbsp;Required Action
                <input class="span5 text" type="text" name="requiredAction" value="<?=$result['RequiredAction']?>">
            </label>
            <label for="dateRequired"><span style="color:red">*</span>&nbsp;Date Required<br>
                <input class="datepick date" type="text" name="dateRequired" value="<?=$result['DateRequired']?>">
            </label>
            <label for="maintainCycle"><span style="color:red">*</span>&nbsp;Maintenance Cycle<br>
                <input class="span1 text" style="text-align:right" type="text" name="maintainCycle" value="<?=$result['MaintainCycle']?>" onkeyup="isNumeric('Edit<?=$result['Recnum']?>')">&nbsp;days
                <span id="maintainCycleSpan" style='visibility:hidden;font-weight:bold;font-size:0px;color:red'>Invalid Number</span>
            </label>
            <label for="autoSch">Auto Schedule<br>
                <select name="autoSch">
                    <option value="1"<?=($result['AutoSchedule'] == 1) ? " selected=\"selected\"" : ""?>>On</option>
                    <option value="0"<?=($result['AutoSchedule'] == 0) ? " selected=\"selected\"" : ""?>>Off</option>
                </select>
            </label>
            <label for="dateScheduled">Date Scheduled<br>
                <input class="datepick" type="text" name="dateScheduled" value="<?=$result['DateScheduled']?>">
            </label>
            <label for="complete">Completed?&nbsp;&nbsp;
                <input type="checkbox" name="complete">
            </label>
        </div>
        <div class="span5" style="margin-left:50px">
            <label for="maintainerName"><span style="color:red">*</span>&nbsp;Maintainer Name<br>
                <select class="select" name="maintainerName" onchange="maintainerChange()">
                    <?php
                        $query = "SELECT Name FROM MaintainResource WHERE Category = 'Maintainer'";
                        $maintainerList = $db -> fetchAll($query);
                        foreach($maintainerList as $list){
                            echo "<option value=\"" . $list['Name'] . "\"";
                            if(!strcasecmp($list['Name'],$result['MaintainerName'])) echo " selected=\"selected\"";
                            echo ">" . $list['Name'] . "</option>";
                        }
                    ?>
                </select>
            </label>
            <label for="maintainerCompany">Maintainer Company<br>
                <input type="text" name="maintainerCompany" value="<?=$result['MaintainerCompany']?>" readonly="readonly">
                <?php
                    $query = "SELECT Name,Company FROM MaintainResource WHERE Category = 'Maintainer'";
                    $maintainerList = $db -> fetchAll($query);
                    foreach($maintainerList as $list) echo "<input type=\"hidden\" name=\"" . $list['Name'] . "\" value=\"" . $list['Company'] . "\" style=\"visibility:hidden\">";
                ?>
            </label>
            <label for="notify">Notify Upon Alarm?&nbsp;&nbsp;
                <input type="checkbox" name="notify" onclick="showNotifyName(this,'#whoToNotifyEdit')"<?=($result['Alarm'] == 1) ? " checked=\"checked\"" : ""?>>
            </label>
            <label for="notifyName[]" id="whoToNotifyEdit"<?=($result['Alarm'] == 0) ? " style=\"visibility:hidden\"" : ""?>><span style="color:red">*</span>&nbsp;Who to Notify (Notification<?=$result['NotificationSent'] ? " " : " Not "?>Sent)<br>
                <select name="notifyName[]"<?=($result['Alarm'] == 0) ? " style=\"visibility:hidden\"" : ""?> multiple>
<?php
                $query = "SELECT CustomerID FROM buildings WHERE buildingID = " . $buildingID;
                $customer = $db -> fetchAll($query);
                foreach($customer as $list){
                    $query = "SELECT email1,email2 FROM customers WHERE customerID = " . $list['CustomerID'];
                    $email = $db -> fetchRow($query);
                    $emails = explode(";",$result['NotifiedName']);
                    if(isset($email['email1'])){
                        echo "<option value=\"" . $email['email1'] . "\"";
                        for($i=0;$i<count($emails);$i++){
                            if(!strcasecmp($email['email1'],$emails[$i])) echo " selected=\"selected\"";
                        }
                        echo ">" . $email['email1'] . "</option>";
                    }
                    if(isset($email['email2'])){
                        echo "<option value=\"" . $email['email2'] . "\"";
                        for($i=0;$i<count($emails);$i++){
                            if(!strcasecmp($email['email2'],$emails[$i])) echo " selected=\"selected\"";
                        }
                        echo ">" . $email['email2'] . "</option>";
                    }
                }
?>
                </select>
            </label>
            <label for="description">Description
                <textarea class="span5" type="text" style="resize:none" name="description" rows="3"><?=$result['Description']?></textarea>
            </label>
            <label for="comments">Comments
                <textarea class="span5" type="text" style="resize:none" name="comments" rows="3"><?=$result['Comment']?></textarea>
            </label>
        </div>
    </div>
    <br>
    <div class="row">
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
    <input type="hidden" name="buildingID" value="<?=$_POST['buildingID']?>">
    <input type="hidden" name="systemID" value="<?=$_POST['systemID']?>">
    <input type="hidden" name="recnum" value="<?=$result['Recnum']?>">
    <input type="hidden" name="maintainUpdate" value="true">
</form>
