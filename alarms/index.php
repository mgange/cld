<?php
/**
 *------------------------------------------------------------------------------
 * Alarms Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');

checkSystemSet($config);

require_once('../includes/header.php');

$db = new db($config);
$SysID=$_SESSION["SysID"];
// first get DAMID for this System from SysMap
$query = "SELECT * FROM SystemConfig, buildings WHERE
buildings.buildingID=SystemConfig.BuildingID AND SystemConfig.SysID=".$SysID;
$sysDAMID = $db -> fetchRow($query);

$SysName=$sysDAMID[SysName];


?>

        <div class="row">
            <h1 class="span6 offset2">Alarms &nbsp;&nbsp; <font color="blue">   System - <?php  echo $SysName; ?></font></h1>
        </div>
        <?php
        	$query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 1 AND SysID = " . $SysID;
        	$results = $db -> fetchAll($query);
        ?>
        <div class="row">
        	<h3 style="text-align:left">Active</h3>
        	<table class="table">
        		<tr style="background-color:#00FFFF">
        			<th>Date</th>
        			<th>Time</th>
        			<th>Description</th>
        			<th>Severity Level</th>
        			<th>Zone</th>
        			<th>Alarm Type</th>
        			<th>Duration (Hrs:Mins)</th>
        			<th>Resolution</th>
        			<th>Notes</th>
        			<th>Email Sent</th>
        		</tr>
    			<?php
    				foreach ($results as $value) {
    					$dateTime = date_create($value['TimeStamp_Start']);
    					$date = date_format($dateTime, 'm/d/Y');
    					$time = date_format($dateTime, 'g:i:s A');
    					$durationTime = time($value['Alarm_Duration']);
    					echo date('g:i', '');
    			?>
        		<tr>
        			<td><?=$date?></td>
        			<td><?=$time?></td>
        			<td><?//=$value['Description'];?></td>
        			<td><?//=$value['Alarm_l'];?></td>
        			<td></td>
        			<td></td>
        			<td><?=$duration?></td>
        			<td><?=$value['Resolution']?></td>
        			<td><?=$value['Notes']?></td>
        			<td><?=$value['Alarm_Email_Sent']?></td>
        		</tr>
        		<?php
        				unset($value);
        			}
        		?>
        	</table>
        </div>
        <br>
        <div class="row">
        	<h3 style="text-align:left">History</h3>
        	<table class="table">
        		<tr style="background-color:#00FFFF">
        			<th>Date</th>
        			<th>Time</th>
        			<th>Description</th>
        			<th>Severity Level</th>
        			<th>Alarm Type</th>
        			<th>Resolution</th>
        			<th>Notes</th>
        			<th>Email Sent</th>
        		</tr>
        		<tr>
        			<td>Info</td>
        			<td>Info</td>
        			<td>Info</td>
        			<td>Info</td>
        			<td>Info</td>
        			<td>Info</td>
        			<td>Info</td>
        			<td>Info</td>
        		</tr>
        	</table>
        </div>

<?php
require_once('../includes/footer.php');
?>
