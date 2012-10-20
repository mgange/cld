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

$query = "UPDATE System_Alarms_Status SET Alarm_Duration = TIMEDIFF( NOW() , TimeStamp_Start ) WHERE Alarm_Active = 1";
$db -> execute($query);
?>

        <div class="row">
            <h1 class="span6 offset2">Alarms &nbsp;&nbsp; <font color="blue">   System - <?php  echo $SysName; ?></font></h1>
        </div>
        <?php
        	$query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 1 AND SysID = " . $SysID;
        	$results = $db -> fetchAll($query);
            if(!empty($results)){
        ?>
        <div class="row">
        	<h3 style="text-align:left">Active</h3>
        	<table class="table">
        		<tr style="background-color:#00FFFF">
        			<th>Date</th>
        			<th>Time</th>
        			<th>Description</th>
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
                        $durationTime = substr($value['Alarm_Duration'],0,(strripos($value['Alarm_Duration'],':'))) . "<br>";
                        $query = "SELECT * FROM Alarm_Codes WHERE Alarm_Code = " . $value['Alarm_Code'];
                        $alarm = $db -> fetchRow($query);
                ?>
        		<tr>
        			<td><?=$date?></td>
        			<td><?=$time?></td>
        			<td><?=$alarm['Description']?><span style="float:right;padding-right:20px"><?=($value['Alarm_Level']==1) ? "<img src=\"../img/alarm_Red.png\" />" : "<img src=\"../img/alarm_Yellow.png\" />"?></span></td>
        			<td><?=$value['Alarm_Source']?></td>
        			<td><?php
                        switch($alarm['Alarm_Type']){
                            case 1:
                                echo "<span style=\"border-bottom:dashed 1px gray\" title=\"Sensor " . $value['SensorNo'] . "\">Temperature Sensor</span>";
                                break;
                            case 2:
                                echo "<span style=\"border-bottom:dashed 1px gray\" title=\"Sensor " . $value['SensorNo'] . "\">Flow/Pressure</span>";
                                break;
                        }
                    ?>
                    </td>
        			<td><?=$durationTime?></td>
        			<td><?=$value['Resolution']?></td>
        			<td><?=$value['Notes']?></td>
        			<td><?=(isset($value['Alarm_Email_Sent'])) ? "Yes" : "No"?></td>
        		</tr>
                <?php
                        unset($value);
                    }//end of foreach
                ?>
        	</table>
        </div>
        <?php
            }//end of if(!empty($results))
            $query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 0 AND SysID = " . $SysID;
            $results = $db -> fetchAll($query);
            if(!empty($results)){
        ?>
        <div class="row">
        	<h3 style="text-align:left">History</h3>
            <table class="table">
                <tr style="background-color:#00FFFF">
                    <th>Started</th>
                    <th>Ended</th>
                    <th>Description</th>
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
                        $dateStart = date_format($dateTime, 'm/d/Y');
                        $timeStart = date_format($dateTime, 'g:i:s A');
                        $dateTime = date_create($value['TimeStamp_End']);
                        $dateEnd = date_format($dateTime, 'm/d/Y');
                        $timeEnd = date_format($dateTime, 'g:i:s A');
                        $durationTime = substr($value['Alarm_Duration'],0,(strripos($value['Alarm_Duration'],':'))) . "<br>";
                        $query = "SELECT * FROM Alarm_Codes WHERE Alarm_Code = " . $value['Alarm_Code'];
                        $alarm = $db -> fetchRow($query);
                ?>
                <tr>
                    <td><?=$dateStart . "<br>" . $timeStart?></td>
                    <td><?=$dateEnd . "<br>" . $timeEnd?></td>
                    <td><?=$alarm['Description']?><span style="float:right;padding-right:20px"><?=($value['Alarm_Level']==1) ? "<img src=\"../img/alarm_Red.png\" />" : "<img src=\"../img/alarm_Yellow.png\" />"?></span></td>
                    <td><?=$value['Alarm_Source']?></td>
                    <td><?php
                        switch($alarm['Alarm_Type']){
                            case 1:
                                echo "<span style=\"border-bottom:dashed 1px gray\" title=\"Sensor " . $value['SensorNo'] . "\">Temperature Sensor</span>";
                                break;
                            case 2:
                                echo "<span style=\"border-bottom:dashed 1px gray\" title=\"Sensor " . $value['SensorNo'] . "\">Flow/Pressure</span>";
                                break;
                        }
                    ?>
                    </td>
                    <td><?=$durationTime?></td>
                    <td><?=$value['Resolution']?></td>
                    <td><?=$value['Notes']?></td>
                    <td><?=(isset($value['Alarm_Email_Sent'])) ? "Yes" : "No"?></td>
                </tr>
                <?php
                        unset($value);
                    }//end of foreach
                ?>
            </table>
        </div>
        <?php
            }//end of if(!empty($results))
        ?>

<?php
require_once('../includes/footer.php');
?>
