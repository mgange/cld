<?php
/**
 *------------------------------------------------------------------------------
 * Stages Index Page
 *------------------------------------------------------------------------------
 *
 */

require_once('../includes/pageStart.php');

checkSystemSet($config);

if(count($_POST) > 0) {
    if(isset($_POST['startTime']) && isset($_POST['endTime'])) {
        $params['start'] = date('Y-m-d', strtotime($_POST['startTime']));
        $params['end']   = date('Y-m-d', strtotime($_POST['endTime']));
    }
    if(isset($_POST['z'])) {
        $params['z'] = $_POST['z'];
    }

    header('Location: ./' . buildURLparameters($params));
}

$db = new db($config);

$buildingNames = $db -> fetchRow('SELECT SysName FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$buildingName = $buildingNames['SysName'];

$numRSM = $db -> fetchRow('SELECT NumofRSM FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$numRSM = $numRSM['NumofRSM'];

if(isset($_GET['start']) && isset($_GET['end'])) {
    $endTime   = $_GET['end'];
    $startTime = $_GET['start'];
}else{
    $endTime = date('Y-m-d');
    $startTime = date('Y-m-d', strtotime('-1 week'));
}
$params['start'] = $startTime;
$params['end']   = $endTime;

if(isset($_GET['z']) && $_GET['z'] != 'main') {
    $zoneTable = '1';
    $SourceID = intval($_GET['z']);
}else{
    $zoneTable = '0';
    $SourceID = 0;
}
if($SourceID == 4) { $SourceID++; }

$query = "
SELECT
    SysMap.SensorColName,
    SysMap.SensorRefName
FROM SysMap
WHERE
    (
        (SysMap.SensorRefName = 'OutsideAir' AND SysMap.SourceID = '$SourceID')
     OR (SysMap.SensorRefName = 'SysCOP'     AND SysMap.SourceID = 99)
    )
";

$defaultSensors = $db->fetchAll($query . "AND SysMap.DAMID = '000000000000'");

/* Put all the defaults in an associative array */
$sensors = array();
foreach($defaultSensors as $sensor) {
    $sensors[$sensor['SensorRefName']] = $sensor;
}

/* Get the custom table.column values specific to the current SysID */
$systemSensors = $db->fetchAll($query . "AND SysMap.SysID = " . $_SESSION['SysID']);
/* Override the default table.column value if there is a custom value to replace it */
foreach($sensors as $key=>$value) {
    foreach($systemSensors as $custom) {
        if($key == $custom['SensorRefName']) {
            $sensors[$key] = $custom;
        }
    }
}


$daysToDisplay = floor((strtotime($endTime) - strtotime($startTime))/(60*60*24));

$maxDays = 180;
if(! withinRange($daysToDisplay, 1, $maxDays+1)) {
    $daysToDisplay = $maxDays;
}

for ($i=0; $i <= $daysToDisplay; $i++) {

    $query = "
SELECT DISTINCT
    SourceHeader.Recnum,
    SourceHeader.DateStamp,
    SourceHeader.TimeStamp,
    SensorCalc." . $sensors['SysCOP']['SensorColName'] . ",
    SourceData".$zoneTable."." . $sensors['OutsideAir']['SensorColName'] . ",
    SourceData".$zoneTable.".DigIn01,
    SourceData".$zoneTable.".DigIn02,
    SourceData".$zoneTable.".DigIn03,
    SourceData".$zoneTable.".DigIn04,
    SourceData".$zoneTable.".DigIn05
FROM
    SourceHeader, SensorCalc, SourceData" . $zoneTable . "
WHERE SourceHeader.SysID = " . $_SESSION['SysID'] . "
  AND SourceHeader.Recnum = SensorCalc.HeadID
  AND SourceHeader.Recnum = SourceData".$zoneTable.".HeadID
  AND SourceHeader.DateStamp = '" . date('Y-m-d', strtotime($endTime."- ".$i." day")) . "'
ORDER BY
    SourceHeader.DateStamp DESC,
    SourceHeader.TimeStamp DESC
";

    $result = $db -> fetchAll($query);

    foreach($result as $res) {
        /* Touch all the system stages so they're created in the correct order */
        $data[$res['DateStamp']]["System Off"]   += 0;
        $data[$res['DateStamp']]["Fan Only"]     += 0;
        $data[$res['DateStamp']]["Emerg. Heat"]  += 0;
        $data[$res['DateStamp']]["Stage 3 Heat"] += 0;
        $data[$res['DateStamp']]["Stage 2 Heat"] += 0;
        $data[$res['DateStamp']]["Stage 1 Heat"] += 0;
        $data[$res['DateStamp']]["Stage 2 Cool"] += 0;
        $data[$res['DateStamp']]["Stage 1 Cool"] += 0;

        $stage = Systemlogic(
            $res['DigIn04'],
            $res['DigIn01'],
            $res['DigIn02'],
            $res['DigIn03'],
            $res['DigIn05'],
            0
        );
        $data[$res['DateStamp']][$stage]++;
        $outsideAir[$res['DateStamp']] += $res[$sensors['OutsideAir']['SensorColName']]/100;
        $calcResult[$res['DateStamp']] += $res[$sensors['SysCOP']['SensorColName']];
    }
    $outsideAir[$result[0]['DateStamp']] = round($outsideAir[$result[0]['DateStamp']]/count($result), 2);
    $calcResult[$result[0]['DateStamp']] = round($calcResult[$result[0]['DateStamp']]/count($result), 2);
}

$data = array_reverse($data);
$outsideAir = array_reverse($outsideAir);
$calcResult = array_reverse($calcResult);

require_once('../includes/header.php');
?>
        <script>
        var chartType = 'area';
        var legend = {enabled: 1};
        var zoomType = 'xy';
        var plotOptions = {
            line: {
                stacking: 'normal'
            },
            area: {
                stacking: 'percent',
                lineWidth: 1,
                marker: {
                    lineWidth: 1,
                    radius: 3
                }
            }
        };
        var tooltip = {
            enabled: 1,
            formatter: function() {
                    if(this.series.name == 'Outside Air') {
                        return this.x+'<br><strong>'+this.y+'°</strong>'
                    }
                    if(this.series.name == 'System COP') {
                        return this.x+'<br><strong>'+this.y+'</strong>'
                    }else{
                        return this.x+'<br>'+
                        this.series.name+' <strong>'+Highcharts.numberFormat(this.percentage, 1) +'%</strong>'
                    }
            }
        }
        var yAxisData = [
            { // Primary Axis
                max:100,
                opposite: true,
                title: {text: 'Average Daily Temperature'},
                type: 'line'

            },
            { // Secondary Axis
                max: 100,
                title: {text: '% Time in Each Stage'},
                type: 'area'
            },

            { // Third-iary Axis
                opposite: true,
                title: {text: 'System COP'},
                type: 'line'
            }
        ];
        var categories = [<?php
            $i = 1;
            foreach($data as $date => $vals) {
                echo "'" . date('M d, Y', strtotime($date)) . "'";
                if($i < count($data)) {
                    echo ", ";
                }
                $i++;
            }
        ?>];
        var data = [
            {
                name: 'Outside Air',
                type: 'line',
                yAxis: 0,
                zIndex: 10,
                data: [<?php
                $i = 1;
                foreach($outsideAir as $date => $temp) {
                    echo $temp;
                    if($i < count($outsideAir)) {
                        echo ', ';
                    }
                    $i++;
                }
                ?>]
            },
            {
                name: 'System COP',
                type: 'line',
                yAxis: 2,
                zIndex: 10,
                data: [<?php
                $i = 1;
                foreach($calcResult as $date => $temp) {
                    echo $temp;
                    if($i < count($calcResult)) {
                        echo ', ';
                    }
                    $i++;
                }
                ?>]
            },
<?php
$i = 1;
foreach(end($data) as $stage => $val) {
?>
            {
                name: <?php echo "'" . $stage . "'"; ?>,
                color: '<?php echo $statusIndex[$stage]['color'] ?>',
                yAxis: 1,
                data: [<?php
                $j = 1;
                foreach($data as $date => $arr) {
                    echo $arr[$stage];
                    if($j < count($data)) {
                        echo ', ';
                    }
                    $j++;
                }
                ?>]
            }<?php
            if($i < count(end($data))) {
                echo ",";
            }
            ?>
<?php
    $i++;
}
?>];
        </script>


        <div class="row">
            <h1 class="span7 offset2">
                % Time / Stage -
                <span class="building-name">
                    <?php
                        echo $buildingName;
                    ?>
                </span>
                <?php
                    if(isset($_GET['z']) && $_GET['z'] != 'main') {
                        echo ' - RSM';
                        if($numRSM > 1){echo '-'.intval($_GET['z']);}
                    }
                    ?>
            </h1>
<?php
if($numRSM > 0) {

?>
            <div class="rsmToggle btn-group span3">
                <a
                    class="btn btn-mini <?php if(!isset($_GET['z']) || $_GET['z'] == 'main'){echo ' active';} ?>"
                    href="./<?php
                        $params['z'] = 'main';
                        echo buildURLparameters($params);
                    ?>">
                    Main
                </a>
<?php
    for ($i=1; $i <= $numRSM; $i++) {
?>
                <a
                    class="btn btn-mini<?php if($_GET['z']==$i){echo ' active';} ?>"
                    href="./<?php
                        $params['z'] = $i;
                        echo buildURLparameters($params);
                    ?>">
                    RSM<?php
                    if($numRSM > 1){echo '-'.$i;}
                    ?>
                </a>
<?php
    }
}
/* Now that the RSM links are made the current RSM value has to be reset */
if(isset($_GET['z'])) {
    $params['z'] = $_GET['z'];
}else{
    $params['z'] = 'main';
}
?>
            </div>

            <div
                id="chart"
                class="chart-container data"
                style="min-width: 400px; min-height: 500px; margin: 0 auto">
            </div>

            <div class="row">
                <div class="span6 offset3">
                    <h5 class="align-center">Display Data</h5>
                    <form class="form-inline" action="./" method="POST">
                        <div class="row">
                            <label class="span3" for="startTime">From
                                <input
                                    id="startTime"
                                    class="datepick span3"
                                    type="text" name="startTime"
                                    value="<?php echo $startTime; ?>">
                            </label>
                            <label class="span3" for="endTime">Until
                                <input
                                    id="endTime"
                                    class="datepick span3"
                                    type="text"
                                    name="endTime"
                                    value="<?php echo $endTime; ?>">
                            </label>
                        </div>
                        <br>
                        <input class="btn btn-info btn-large btn-block" type="submit" value="Submit">
                        <input type="hidden" name="z" value="<?php echo $params['z']; ?>">
                    </form>
                </div>
            </div>

<?php
require_once('../includes/footer.php');
?>
