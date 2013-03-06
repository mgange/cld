<?php
/**
 *------------------------------------------------------------------------------
 * Add Maintenance Index Page
 *------------------------------------------------------------------------------
 *
 */
?>

<form class="validate" name="maintainForm" action="./" method="post">
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
                    echo ($orderNum[1]);
                }else echo "0";
                echo "\" readonly=\"readonly\">";
            ?>
            </label>
            <label for="sysComponent">System Component
                <select class="span5" name="sysComponent" id="sysComponent">
                <?php
                    $query = "SELECT UnitName FROM SysComponents WHERE SysID = " . $systemID;
                    $components = $db -> fetchAll($query);
                    echo "<option>Select A Component...</option>";
                    foreach($components as $result){
                        echo "<option value=\"" . $result['UnitName'] . "\">" . $result['UnitName'] . "</option>";
                    }
                ?>
                </select>
            </label>
            <label for="requiredAction"><span style="color:red">*</span> Required Action
                <input class="span5 text" type="text" name="requiredAction">
            </label>
            <label for="dateRequired"><span style="color:red">*</span> Date Required<br>
                <input class="datepick date" type="text" name="dateRequired">
            </label>
            <label for="maintainCycle"><span style="color:red">*</span> Maintenance Cycle<br>
                <input class="span1 text" style="text-align:right" type="text" name="maintainCycle">&nbsp;days
            </label>
            <label for="autoSch">Auto Schedule<br>
                <select name="autoSch">
                    <option value="1">On</option>
                    <option value="0">Off</option>
                </select>
            </label>
        </div>
        <div class="span5" style="margin-left:50px">
            <label for="maintainerName"><span style="color:red">*</span> Maintainer Name<br>
                <select class="select" name="maintainerName" onchange="maintainerChange()">
                    <option value="" selected="selected">Select A Maintainer
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
                <input type="checkbox" name="notify" onclick="showNotifyName(this,'#whoToNotify')">
            </label>
            <label for="notifyName[]" id="whoToNotify" class="hide"><span style="color:red">*</span> Who to Notify<br>
                <select name="notifyName[]" multiple>
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
                <textarea class="span5" type="text" style="resize:none" name="description" rows="3"></textarea>
            </label>
            <label for="comments">Comments
                <textarea class="span5" type="text" style="resize:none" name="comments" rows="3"></textarea>
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
