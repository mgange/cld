<?php
/**
 *------------------------------------------------------------------------------
 * Maintenance Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');

$db = new db($config);

unset($_SESSION['buildingID']);
unset($_SESSION['SysID']);

/** Insert New Work Order **/
if(isset($_POST['maintainNew'])){
    if(isset($_POST['notify'])){
        $query = "INSERT INTO MaintainLog (SysID,WorkOrder,SystemComponent,RequiredAction,DateRequired,MaintainCycle,AutoSchedule,MaintainerName,MaintainerCompany,Alarm,NotificationSent,NotifiedName,Description,Comments)
                            VALUES(:systemID,:workOrder,:sysComponent,:requiredAction,:dateRequired,:maintainCycle,:autoSch,:maintainerName,:maintainerCompany,1,0,:notifyName,:description,:comments)";
        $bind[':notifyName'] = "";
        for($i=0;$i<count($_POST['notifyName']);$i++) $bind[':notifyName'] .= $_POST['notifyName'][$i] . ";";
        $bind[':notifyName'] = substr($bind[':notifyName'],0,strlen($bind[':notifyName']) - 1); //remove last ;
    }else{
        $query = "INSERT INTO MaintainLog (SysID,WorkOrder,SystemComponent,RequiredAction,DateRequired,MaintainCycle,AutoSchedule,MaintainerName,MaintainerCompany,Alarm,NotificationSent,Description,Comments)
                            VALUES(:systemID,:workOrder,:sysComponent,:requiredAction,:dateRequired,:maintainCycle,:autoSch,:maintainerName,:maintainerCompany,0,0,:description,:comments)";
    }
    $bind[':systemID']          = $_POST['systemID'];
    $bind[':workOrder']         = $_POST['workOrder'];
    $bind[':sysComponent']      = $_POST['sysComponent'];
    $bind[':requiredAction']    = $_POST['requiredAction'];
    $bind[':dateRequired']      = $_POST['dateRequired'];
    $bind[':maintainCycle']     = $_POST['maintainCycle'];
    $bind[':autoSch']           = $_POST['autoSch'];
    $bind[':maintainerName']    = $_POST['maintainerName'];
    $bind[':maintainerCompany'] = $_POST['maintainerCompany'];
    $bind[':comments']          = (!empty($_POST['comments']) ? $_POST['comments'] : NULL);
    $bind[':description']       = (!empty($_POST['description']) ? $_POST['description'] : NULL);
    $db -> execute($query, $bind);
}elseif(isset($_POST['maintainUpdate'])){
    $query = "UPDATE MaintainLog SET
                RequiredAction      = :requiredAction,
                DateRequired        = :dateRequired,
                DateScheduled       = :dateScheduled,
                DatePerformed       = :datePerformed,
                MaintainerCompany   = :maintainerCompany,
                MaintainerName      = :maintainerName,
                MaintainCycle       = :maintainCycle,
                AutoSchedule        = :autoSch,
                Alarm               = :notify,
                Comments            = :comments,
                Description         = :description,
                NotifiedName        = :notifyName
                WHERE Recnum        = " . $_POST['recnum'];
    if(isset($_POST['notify'])){
        $bind[':notify'] = 1;
        $bind[':notifyName'] = "";
        for($i=0;$i<count($_POST['notifyName']);$i++) $bind[':notifyName'] .= $_POST['notifyName'][$i] . ";";
        $bind[':notifyName'] = substr($bind[':notifyName'],0,strlen($bind[':notifyName']) - 1); //remove last ;
    }else{
        $bind[':notify'] = 0;
        $bind[':notifyName'] = NULL;
    }
    $bind[':requiredAction']    = $_POST['requiredAction'];
    $bind[':dateRequired']      = $_POST['dateRequired'];
    $bind[':dateScheduled']     = (!empty($_POST['dateScheduled']) ? $_POST['dateScheduled'] : NULL);
    $bind[':datePerformed']     = (isset($_POST['complete']) ? date("Y-m-d") : NULL);
    $bind[':maintainerCompany'] = $_POST['maintainerCompany'];
    $bind[':maintainerName']    = $_POST['maintainerName'];
    $bind[':maintainCycle']     = $_POST['maintainCycle'];
    $bind[':autoSch']           = $_POST['autoSch'];
    $bind[':comments']          = (!empty($_POST['comments']) ? $_POST['comments'] : NULL);
    $bind[':description']       = (!empty($_POST['description']) ? $_POST['description'] : NULL);
    $db -> execute($query, $bind);
}elseif(isset($_POST['delete'])){
    $query = "DELETE FROM MaintainLog WHERE Recnum = " . $_POST['delete'] . " LIMIT 1";
    $db -> execute($query);
}


//Get list of buildings
$query = "SELECT buildingID, buildingName FROM buildings";
if($_SESSION['authLevel'] != 3) {
    $query .= " WHERE customerID = " . $_SESSION['customerID'];
}
$buildingList = $db -> fetchAll($query);

//Set Building ID
if(isset($_POST['buildingID'])) $buildingID = $_POST['buildingID'];

//get list of systems
if(isset($buildingID)){
    $query = "SELECT buildingName FROM buildings WHERE buildingID = " . $buildingID . " LIMIT 1";
    $result = $db -> fetchRow($query);
    $buildingName = $result['buildingName'];
    $query = "SELECT * FROM SystemConfig WHERE buildingID = " . $buildingID;
    $systemList = $db -> fetchAll($query);
    if(!isset($_POST['systemID']) && (sizeof($systemList) == 1)) $_POST['systemID'] = $systemList[0]['SysID'];
}

//set system id
if(isset($_POST['systemID'])) $systemID = $_POST['systemID'];


require_once('../includes/header.php');

?>

<script type="text/javascript">
    function showNotifyName(cnt){
        var form = document.forms["maintainForm" + cnt];
        var label = document.forms["maintainForm" + cnt].getElementsByTagName("label");
        for(var i=0;i<label.length;i++){    //get index of associated label
            if(label[i].htmlFor == "notifyName[]") break;
        }
        if(form["notify"].checked){
            form["notifyName[]"].style.visibility = "visible";
            form["notifyName[]"].style.display = "";
            label[i].style.visibility = "visible";
            label[i].style.display = "";
        }else{
            form["notifyName[]"].style.visibility = "hidden";
            form["notifyName[]"].style.display = "none";
            label[i].style.visibility = "hidden";
            label[i].style.display = "none";
        }
    }
    function maintainerChange(cnt){
        var name = document.forms["maintainForm" + cnt]["maintainerName"];
        var company = document.forms["maintainForm" + cnt]["maintainerCompany"];
        if(name.value == "-") company.value = "";
        else company.value = document.getElementsByName(name.value)[0].value;
    }
    function isNumeric(cnt){
        var element = document.forms["maintainForm" + cnt]["maintainCycle"];
        var span = document.forms["maintainForm" + cnt].getElementsByTagName("span");
        for(var i=0;i<span.length;i++){    //get index of associated span
            if(span[i].id == "maintainCycleSpan") break;
        }

        var valid = (!isNaN(element.value) && parseInt(element.value));
        if(element.value < 0) valid = false;
        if(!valid){
            element.style.border = "red solid 2px";
            //span[i].innerHTML += "<br>test";
            span[i].style.display = "block";
            span[i].style.visibility = "visible";
            span[i].style.fontSize = "14px";
        }else{
            element.style.border = "";
            //span[i].innerHTML += span[i].innerHTML || "<br>test";
            span[i].style.display = "";
            span[i].style.visibility = "hidden";
            span[i].style.fontSize = "0px";
        }
    }
    function validate(formName){
        var form = document.forms[formName];
        var noError = true;
        for(var i=0;i<form.length;i++){
            if((form[i].name == "description") || (form[i].name == "comments")
            || (form[i].name == "dateScheduled")) continue;    //not required
            form[i].style.border = "";
            if((form[i].style.visibility != "hidden") && (form[i].type != "submit") && (!form[i].readOnly)){
                if((form[i].value == "") || (form[i].value == "-")){
                    form[i].style.border = "red solid 2px";
                    noError = false;
                }
            }
        }
        return noError;
    }
    function sortSubmit(title,order){
        document.forms["sortForm"].action = "?group=" + title + "&by=" + order;
        document.forms["sortForm"].submit();
    }
    function deleteRecord(workOrder,recnum){
        if(confirm("Are you sure you want to delete Work Order " + workOrder + "?")){
            document.forms["sortForm"].innerHTML += "<input type=\"hidden\" name=\"delete\" value=\"" + recnum + "\">";
            document.forms["sortForm"].submit();
        }
    }
</script>

<div class="row">
	<h1 class="span8 offset2">Maintenance</h1>
</div>
<div class="row">
	<form method="post" action="./">
		<div class="span7" style="text-align:right">
			<h4>Select Building:&nbsp;&nbsp;
				<select name="buildingID" class="selectSubmit"><?php
					if(!isset($buildingID)) echo "<option selected='selected'>Select A Building</option>";
					foreach ($buildingList as $value) {
					if($buildingID == $value['buildingID']) echo "<option selected='selected' value='" . $value['buildingID'] . "'>" . $value['buildingName'] . "</option>";
					else echo "<option value='" . $value['buildingID'] . "'>" . $value['buildingName'] . "</option>";
					}?>
				</select>
			</h4>
		</div>
	</form>
</div>
<!-- SELECT SYSTEM -->
<?php if(isset($buildingID)){ ?>
<div class="row">
	<form method="post" action="./">
		<div class="span7" style="text-align:right">
			<h4>Select System:&nbsp;&nbsp;
				<select name="systemID" class="selectSubmit"><?php
				if(!isset($systemID)) echo "<option selected='selected'>Select A System</option>";
				foreach ($systemList as $value) {
				if($systemID == $value['SysID']) echo "<option selected='selected' value='" . $value['SysID'] . "'>" . $value['SysName'] . "</option>";
				else echo "<option value='" . $value['SysID'] . "'>" . $value['SysName'] . "</option>";
				}?>
				</select>
				<input type="hidden" name="buildingID" value="<?=$buildingID?>">
			</h4>
		</div>
	</form>
</div>
<?php } ?>

<?php
	if(isset($systemID)){
        $arrow = "";
        if(isset($_GET['group']) && isset($_GET['by'])){
            switch ($_GET['group']){
                case "order":
                    $query = "SELECT * FROM MaintainLog WHERE SysID = " . $systemID . " ORDER BY WorkOrder " . $_GET['by'];
                    break;
                case "dateReq":
                    $query = "SELECT * FROM MaintainLog WHERE SysID = " . $systemID . " ORDER BY DateRequired " . $_GET['by'];
                    break;
                case "component":
                    $query = "SELECT * FROM MaintainLog WHERE SysID = " . $systemID . " ORDER BY SystemComponent " . $_GET['by'];
                    break;
                case "actionReq":
                    $query = "SELECT * FROM MaintainLog WHERE SysID = " . $systemID . " ORDER BY RequiredAction " . $_GET['by'];
                    break;
                case "mainName":
                    $query = "SELECT * FROM MaintainLog WHERE SysID = " . $systemID . " ORDER BY MaintainerName " . $_GET['by'];
                    break;
                case "mainComp":
                    $query = "SELECT * FROM MaintainLog WHERE SysID = " . $systemID . " ORDER BY MaintainerCompany " . $_GET['by'];
                    break;
            }
            if($_GET['by'] == "asc") $arrow = "&uarr;";
            else $arrow = "&darr;";
        }else $query = "SELECT * FROM MaintainLog WHERE SysID = " . $systemID;
		$logList = $db -> fetchAll($query);
		if(count($logList) == 0) echo "<br><h3 class=\"span6\" style=\"text-align:right\">No Maintenance Scheduled</h4><br><br>";
		else{
			//List all maintenance
?>
            <br>
            <div class="row" action="./">
                <form name="sortForm" method="post">
                    <h4 style="float:left;width:130px"><a type="submit" title="Sort By Work Order #" onclick="sortSubmit('order','<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "asc" : "desc"?>')">Work Order #</a>&nbsp;<?=($_GET['group'] == "order") ? $arrow : ""?></h4>
                    <h4 class="span2"><a type="submit" title="Sort By Date Required" onclick="sortSubmit('dateReq','<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "asc" : "desc"?>')">Date Required</a>&nbsp;<?=($_GET['group'] == "dateReq") ? $arrow : ""?></h4>
                    <h4 style="float:left;width:100px"><a type="submit" title="Sort By Component" onclick="sortSubmit('component','<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "asc" : "desc"?>')">System Component</a>&nbsp;<?=($_GET['group'] == "component") ? $arrow : ""?></h4>
                    <h4 class="span2"><a type="submit" title="Sort By Required Action" onclick="sortSubmit('actionReq','<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "asc" : "desc"?>')">Required Action</a>&nbsp;<?=($_GET['group'] == "actionReq") ? $arrow : ""?></h4>
                    <h4 class="span2"><a type="submit" title="Sort By Maintainer Name" onclick="sortSubmit('mainName','<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "asc" : "desc"?>')">Maintainer Name</a>&nbsp;<?=($_GET['group'] == "mainName") ? $arrow : ""?></h4>
                    <h4 class="span2"><a type="submit" title="Sort By Maintainer Company" onclick="sortSubmit('mainComp','<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "asc" : "desc"?>')">Maintainer Company</a>&nbsp;<?=($_GET['group'] == "mainComp") ? $arrow : ""?></h4>
                    <h4 style="float:left;width:40px">Action</h4>
                    <input type="hidden" name="buildingID" value="<?=$_POST['buildingID']?>">
                    <input type="hidden" name="systemID" value="<?=$_POST['systemID']?>">
                </form>
            </div>
            <hr class="row">
<?php
            foreach($logList as $result){
?>
                <div class="row over" onclick="document.getElementById('linkView<?=$result['Recnum']?>').click()">
                    <p style="float:left;width:130px"><?=$result['WorkOrder']?></p>
                    <p class="span2"><?=$result['DateRequired']?></p>
                    <p style="float:left;width:100px"><?=$result['SystemComponent']?></p>
                    <p class="span2"><?=$result['RequiredAction']?></p>
                    <p class="span2"><?=$result['MaintainerName']?></p>
                    <p class="span2"><?=$result['MaintainerCompany']?></p>
                    <p style="float:left;width:40px">
                        <a id="linkView<?=$result['Recnum']?>" class="accordion-toggle icon-pencil" data-toggle="collapse"
                            title="View/Edit" href="#collapseView<?=$result['Recnum']?>"></a>&nbsp;
                        <a class="accordion-toggle icon-remove" title="Delete"
                            onclick="deleteRecord('<?=$result['WorkOrder']?>','<?=$result['Recnum']?>')"></a>
                    </p>
                </div>
                <div id="collapseView<?=$result['Recnum']?>" class="accordion-body collapse">
                    <div class="accordion-inner" style="border:0px;background:#dddddd">
                        <?php
                            include('edit/index.php');
                        ?>
                    </div>
                </div>
<?php
            }
        }
?>
		<div class="accordion-group" style="border:0px">
			<div class="accordion-heading">
				<a class="accordion-toggle" data-toggle="collapse"
					data-parent="#accordion2"
					href="#collapse1">
					<div class="row">
						<h3 class="span8 offset3">+ Add Maintenance</h2>
					</div>
				</a>
			</div>
			<div id="collapse1" class="accordion-body collapse">
				<div class="accordion-inner accordion-highlight">
					<?php
						include('add/index.php');
					?>
				</div>
			</div>
		</div>
<?php
	}
?>





<?php
require_once('../includes/footer.php');
?>
