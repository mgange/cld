<?php
/**
 *------------------------------------------------------------------------------
 * Alarm Limits Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../../includes/pageStart.php');

checkSystemSet($config);

if($_SESSION['authLevel'] < 3) {
    gtfo($config);
}

$db = new db($config);
$SysId = $_SESSION['SysID'];
$BuildingID = $_SESSION['buildingID'];
$query = "SELECT * FROM SystemConfig WHERE SysId = $SysId AND BuildingID = $BuildingID";
$systemInfo = $db -> fetchRow($query);
$query = "SELECT * FROM buildings WHERE buildingID = $BuildingID";
$buildingInfo = $db -> fetchRow($query);
$query = "SELECT * FROM customers WHERE customerID = $buildingInfo[CustomerID]";
$customerInfo = $db -> fetchRow($query);

if(isset($_GET['z'])){
  switch($_GET['z']){
    case 1:
      $zone = 1;
      break;
    case 2:
      $zone = 2;
      break;
    case 3:
      $zone = 3;
      break;
    case 4:
      $zone = 5;
      break;
  }
}

$query = "SELECT Recnum,SensorName,SensorUnits,AlarmUpLimit,AlarmLoLimit,AlertPercent FROM SysMap WHERE SensorActive = 1 " . (isset($zone) ? "AND SourceID = $zone" : "AND (SourceID = 0 OR SourceID = 4)");
$sysMap = $db -> fetchAll($query);

if(count($_POST) > 0) {
    foreach ($sysMap as $resultRow) {
        $loFlag = "LoChange" . $resultRow['Recnum'];
        $hiFlag = "HiChange" . $resultRow['Recnum'];
        $percentFlag = "PercentChange" . $resultRow['Recnum'];
        $loValue = "Lo" . $resultRow['Recnum'];
        $hiValue = "Hi" . $resultRow['Recnum'];
        $percentValue = "Percent" . $resultRow['Recnum'];
        if($_POST[$loFlag] || $_POST[$hiFlag] || $_POST[$percentFlag]){
            if(!is_numeric($_POST[$loValue]) || !is_numeric($_POST[$hiValue]) || !is_numeric($_POST[$percentValue])){
                header('Location: ./?a=ne'); //a = Alert  ne = number error(generic)
                die(require_once('../../includes/footer.php'));
            }
            $query = "UPDATE SysMap SET AlarmUpLimit = :hiValue2, AlarmLoLimit = :loValue2, AlertPercent = :percent WHERE Recnum = $resultRow[Recnum]";
            $bind[':hiValue2'] = $_POST[$hiValue];
            $bind[':loValue2'] = $_POST[$loValue];
            $bind[':percent'] = $_POST[$percentValue];

            if(!($db -> execute($query, $bind))){
                header('Location: ./?a=e'); //a = Alert  e = error(generic)
                die(require_once('../../includes/footer.php'));
            }
        }
    }
    header('Location: ../?a=ss'); //a = Alert  ss = Secondary Success(generic)
    die(require_once('../../includes/footer.php'));
}

require_once('../../includes/header.php');

?>

<script type="text/javascript">
    function LimitChangeHi(v){
        document.getElementsByName("Hi" + v)[0].innerHTML="<input type=\"hidden\" name=\"HiChange" + v + "\" value=\"1\">";
    }
    function LimitChangeLo(v){
        document.getElementsByName("Lo" + v)[0].innerHTML="<input type=\"hidden\" name=\"LoChange" + v + "\" value=\"1\">";
    }
    function LimitChangePercent(v){
        document.getElementsByName("Percent" + v)[0].innerHTML="<input type=\"hidden\" name=\"PercentChange" + v + "\" value=\"1\">";
    }
</script>

<div class="row">
    <h1 class="span8 offset2">'<?=$systemInfo['SysName']?>' Alarm Limits - <?=isset($zone) ? "RSM " . $zone : "MAIN"?></h1>
    <h5 style="float:right"><?php
        if(isset($zone)){
            echo "<a href=\"./\">MAIN</a><br>";
        }
        for($i=0;$i<$systemInfo['NumofRSM'];$i++){
            if($zone != ($i + 1)) echo "<a href=\"./?z=" . ($i + 1) . "\">RSM " . (($i == 3) ? ($i + 2) : ($i + 1)) . "</a><br>";
        }
    ?></h5>
</div>

<form action="./" method="POST">
	<div class="row">
        <br>
		<h4 class="span3">Sensor</h4>
		<h4 class="span3">Low Limit</h4>
		<h4 class="span3">High Limit</h4>
        <h4 class="span3">Percent Threshold</h4>
	</div>
    <hr>
<?php
	foreach ($sysMap as $resultRow) {
        if(isset($resultRow['AlarmUpLimit']) | isset($resultRow['AlarmLoLimit']) | isset($resultRow['AlertPercent'])){
?>
	<div class="row over">
		<p class="span3" style="margin-top:10px"><strong><?=$resultRow['SensorName']?></strong></p>
		<p class="span3" style="margin-top:10px"><?php if(isset($resultRow['AlarmLoLimit'])) { ?><input name="Lo<?=$resultRow['Recnum']?>" onchange="LimitChangeLo(<?=$resultRow['Recnum']?>)" type="text" class="span2" style="text-align:right" value="<?=$resultRow['AlarmLoLimit']?>"> <?php echo $resultRow['SensorUnits']; } ?></p>
        <p class="span3" style="margin-top:10px"><?php if(isset($resultRow['AlarmUpLimit'])) { ?><input name="Hi<?=$resultRow['Recnum']?>" onchange="LimitChangeHi(<?=$resultRow['Recnum']?>)" type="text" class="span2" style="text-align:right" value="<?=$resultRow['AlarmUpLimit']?>"> <?php echo $resultRow['SensorUnits']; } ?></p>
        <p class="span3" style="margin-top:10px"><?php if(isset($resultRow['AlertPercent'])) { ?><input name="Percent<?=$resultRow['Recnum']?>" onchange="LimitChangePercent(<?=$resultRow['Recnum']?>)" type="text" class="span2" style="text-align:right" value="<?=$resultRow['AlertPercent']?>"> %<?php } ?></p>
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
	<input type="hidden" name="customerID" value="<?=$customerInfo['customerID']?>">
</form>

<?php
	require_once('../../includes/footer.php');
?>
