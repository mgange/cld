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
            <h1 class="span10 offset2">Alarms - <span class="building-name">   System - <?php  echo $SysName; ?></span></h1>
        </div>
        <?php
            if(!isset($_GET['id'])){
                $arrow = "";
                if(isset($_GET['group']) && isset($_GET['by'])){
                    switch ($_GET['group']){
                        case "datetime":
                            $query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 1 AND SysID = " . $SysID . " group BY TimeStamp_Start " . $_GET['by'];
                            break;
                        case "duration":
                            $query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 1 AND SysID = " . $SysID . " group BY Alarm_Duration " . $_GET['by'];
                            break;
                        case "sensor":
                            $query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 1 AND SysID = " . $SysID . " group BY SensorNo " . $_GET['by'];
                            break;
                    }
                    if($_GET['by'] == "asc") $arrow = "&uarr;";
                    else $arrow = "&darr;";
                }else $query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 1 AND SysID = " . $SysID;
                $results = $db -> fetchAll($query);
                if(!empty($results)){
        ?>
        <div class="row">
            <h3 class="span12">Active (<?=$db -> numRows($query)?> Total)<span style="font-size:75%;float:right"><a href="?id=a">Archive</a></span></h3>
            <table class="table span12">
                <tr class="alarm-header">
                    <th>Date</th>
                    <th><a href="<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "?group=datetime&by=asc" : "?group=datetime&by=desc"?>">Time</a> <?=($_GET['group'] == "datetime") ? $arrow : ''?></th>
                    <th>Description</th>
                    <th>Zone</th>
                    <th><a href="<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "?group=sensor&by=asc" : "?group=sensor&by=desc"?>">Sensor</a> <?=($_GET['group'] == "sensor") ? $arrow : ''?></th>
                    <th><a href="<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "?group=duration&by=asc" : "?group=duration&by=desc"?>">Duration (Hrs:Mins)</a> <?=($_GET['group'] == "duration") ? $arrow : ''?></th>
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
                    <td><?=$alarm['Description']?><span style="float:right;padding-right:20px"><a href="../status/?id=<?=$value['HeadID']?>"><?=($value['Alarm_Level']==1) ? "<img src=\"../img/alarm_Red.png\" />" : "<img src=\"../img/alarm_Yellow.png\" />"?></a></span></td>
                    <td><?php
                        switch($value['SourceID']){
                            case 0:
                            case 4:
                                echo "Main";
                                break;
                            default:
                                echo "RSM" . $value['SourceID'];
                                break;
                        }
                    ?>
                    </td>
                    <td><?php
                        $query = "SELECT SensorRefName FROM SysMap WHERE SensorNo = " . $value['SensorNo'] . " AND SourceID = " . $value['SourceID'];
                        $label = $db -> fetchRow($query);
                        $query = "SELECT SensorLabel FROM WebRefTable WHERE SensorName = '" . $label['SensorRefName'] . "'";
                        $name = $db -> fetchRow($query);
                        echo $name['SensorLabel'];
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
                }else{//end of if(!empty($results))
        ?>
        <div>
            <h3 class="span12">No Active Alarms<span style="font-size:75%;float:right"><a href="?id=a">Archive</a></span></h3>
        </div>
        <?php
                }
            }elseif(isset($_GET['id']) && ($_GET['id'] == 'a')){
                $arrow = "";
                if(isset($_GET['group']) && isset($_GET['by'])){
                    switch ($_GET['group']){
                        case "started":
                            $query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 0 AND SysID = " . $SysID . " group BY TimeStamp_Start " . $_GET['by'];
                            break;
                        case "ended":
                            $query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 0 AND SysID = " . $SysID . " group BY TimeStamp_End " . $_GET['by'];
                            break;
                        case "duration":
                            $query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 0 AND SysID = " . $SysID . " group BY Alarm_Duration " . $_GET['by'];
                            break;
                        case "sensor":
                            $query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 0 AND SysID = " . $SysID . " group BY SensorNo " . $_GET['by'];
                            break;
                    }
                    if($_GET['by'] == "asc") $arrow = "&uarr;";
                    else $arrow = "&darr;";
                }else $query = "SELECT * FROM System_Alarms_Status WHERE Alarm_Active = 0 AND SysID = " . $SysID;
                $results = $db -> fetchAll($query);
                if(!empty($results)){
        ?>
        <div class="row">
            <h3 class="span12">Archive (<?=$db -> numRows($query)?> Total)<span style="font-size:75%;float:right"><a href="./">Active</a></span></h3>
            <table class="table span12">
                <tr class="alarm-header">
                    <th><a href="<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "?id=a&group=started&by=asc" : "?id=a&group=started&by=desc"?>">Started</a> <?=($_GET['group'] == "started") ? $arrow : ''?></th>
                    <th><a href="<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "?id=a&group=ended&by=asc" : "?id=a&group=ended&by=desc"?>">Ended</a> <?=($_GET['group'] == "ended") ? $arrow : ''?></th>
                    <th>Description</th>
                    <th>Zone</th>
                    <th><a href="<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "?id=a&group=sensor&by=asc" : "?id=a&group=sensor&by=desc"?>">Sensor</a> <?=($_GET['group'] == "sensor") ? $arrow : ''?></th>
                    <th><a href="<?=(!isset($_GET['by']) || ($_GET['by'] == "desc")) ? "?id=a&group=duration&by=asc" : "?id=a&group=duration&by=desc"?>">Duration (Hrs:Mins)</a> <?=($_GET['group'] == "duration") ? $arrow : ''?></th>
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
                    <td><?=$alarm['Description']?><span style="float:right;padding-right:20px"><a href="../status/?id=<?=$value['HeadID']?>"><?=($value['Alarm_Level']==1) ? "<img src=\"../img/alarm_Red.png\" />" : "<img src=\"../img/alarm_Yellow.png\" />"?></a></span></td>
                    <td><?php
                        switch($value['SourceID']){
                            case 0:
                            case 4:
                                echo "Main";
                                break;
                            default:
                                echo "RSM" . $value['SourceID'];
                                break;
                        }
                    ?>
                    </td>
                    <td><?php
                        $query = "SELECT SensorRefName FROM SysMap WHERE SensorNo = " . $value['SensorNo'] . " AND SourceID = " . $value['SourceID'];
                        $label = $db -> fetchRow($query);
                        $query = "SELECT SensorLabel FROM WebRefTable WHERE SensorName = '" . $label['SensorRefName'] . "'";
                        $name = $db -> fetchRow($query);
                        echo $name['SensorLabel'];
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
            }
        ?>

<?php
require_once('../includes/footer.php');
?>
