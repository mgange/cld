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
		$query = "SELECT * FROM MaintainLog WHERE SysID = " . $systemID;
		$logList = $db -> fetchAll($query);
		if(count($logList) == 0) echo "<br><h3 class=\"span6\" style=\"text-align:right\">No Maintenance Scheduled</h4><br><br>";
		else{
			//List all maintenance
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
