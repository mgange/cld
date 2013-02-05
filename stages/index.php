<?php
/**
 *------------------------------------------------------------------------------
 * Stages Index Page
 *------------------------------------------------------------------------------
 *
 */
/**
 * Silly Functions
 */
function pickTable($SourceID)
{
    switch ($SourceID) {
        case '0':
            $table = 'SourceData0';
            break;
        case '4':
            $table = 'SourceData4';
            break;
        case '99':
            $table = 'SensorCalc';
            break;
        default:
            $table = 'SourceData1';
            break;
    }
    return $table;
}
function pageName($zone)
{
    if($zone == 0) {
        return 'Main';
    }else{
        return 'RSM';
    }
}

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

// Date Stuff
if(isset($_GET['start']) && isset($_GET['end'])) {
    $endTime   = $_GET['end'];
    $startTime = $_GET['start'];
}else{
    $endTime = date('Y-m-d');
    $startTime = date('Y-m-d', strtotime('-1 week'));
}
$params['start'] = $startTime;
$params['end']   = $endTime;

// Zone Stuff
if(isset($_GET['z'])) {
    $zone = intval($_GET['z']);
}else{
    $zone = 0;
}
if($zone > 4){ $zone++; }


$query = "
SELECT
    SysMap.SourceID,
    SysMap.SysID,
    SysMap.SensorColName,
    SysMap.SensorName,
    SysMap.SensorRefName,
    SysMap.*,
    WebRefTable.SensorLabel,
    WebRefTable.WebSubPageName
FROM SysMap, WebRefTable
WHERE WebRefTable.WebSubPageName = '" . pageName($zone) . "'
  AND SysMap.WebSensRefNum = WebRefTable.WebSensRefNum
  AND SysMap.SensorRefName = 'OutsideAir'
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
foreach($systemSensors as $customSensor) {
    $sensors[$customSensor['SensorRefName']] = $customSensor;
}

//Tables Stuff
$tablesUsed = array(pickTable($zone));
foreach($sensors as $sensor) {
    if(!in_array(pickTable($sensor['SourceID']), $tablesUsed)) {
        array_push($tablesUsed, pickTable($sensor['SourceID']));
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
    ";
    foreach($sensors as $sensor){
        $query .= pickTable($sensor['SourceID']) . '.' . $sensor['SensorColName'] . ",
    ";
    }
    $query .=
    pickTable($zone).".DigIn01,
    ".pickTable($zone).".DigIn02,
    ".pickTable($zone).".DigIn03,
    ".pickTable($zone).".DigIn04,
    ".pickTable($zone).".DigIn05
FROM
    SourceHeader";
foreach($tablesUsed as $table){
    $query .= ", " . $table;
}
$query .= "
WHERE SourceHeader.SysID = " . $_SESSION['SysID'];
foreach($tablesUsed as $table) {
    $query .= "
  AND SourceHeader.Recnum = " . $table . ".HeadID";
}
$query .= "
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
        $datapoints[$res['DateStamp']]++;
    }
    // $outsideAir[$result[0]['DateStamp']] = round($outsideAir[$result[0]['DateStamp']]/count($result), 2);
}
foreach($outsideAir as $k => $v) {
    $outsideAir[$k] = round( $v / $datapoints[$k] , 2);
}

$data = array_reverse($data);
$outsideAir = array_reverse($outsideAir);

require_once('../includes/header.php');
?>
        <script>
        var chartType = 'column';
        var legend = {enabled: 1};
        var zoomType = 'xy';
        var plotOptions = {
            line: {
                stacking: 'normal'
            },
            column: {
                borderColor: '#999',
                borderWidth: 1,
                shadow: 0,
                stacking: 'percent'
            }
        };
        var tooltip = {
            enabled: 1,
            formatter: function() {
                    if(this.series.name == 'Outside Air') {
                        return this.x+'<br><strong>'+this.y+'°</strong>'
                    }
                    if(this.series.name == 'Heating Degree Days' || this.series.name == 'Cooling Degree Days') {
                        return this.x+'<br><strong>'+this.y+'</strong>'
                    }else{
                        return this.x+'<br>'+
                        this.series.name+' <strong>'+Highcharts.numberFormat(this.percentage, 1) +'%</strong>'
                    }
            }
        }
        var yAxisData = [
            {
                max: 100,
                maxPadding: 0,
                title: {text: '% Time in Each Stage'}
            },
            {
                maxPadding: 0,
                opposite: 1,
                title: {
                    text: 'Temperature'
                }
            },
            {
                max: 100,
                min: 0,
                opposite: 1,
                title: {
                    text: 'Degree Days'
                }
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
<?php
foreach(end($data) as $stage => $val) {
?>
            {
                name: <?php echo "'" . $stage . "'"; ?>,
                color: '<?php echo $statusIndex[$stage]['color'] ?>',
                data: [<?php
                $j = 1;
                foreach($data as $date => $arr) {
                    echo intval($arr[$stage]);
                    if($j < count($data)) {
                        echo ', ';
                    }
                    $j++;
                }
                ?>],
                // type: 'column'
            },
<?php
}
?>
            {
                name: 'Outside Air',
                data: [<?php
                $i = 1;
                foreach($outsideAir as $date => $temp) {
                    echo $temp;
                    if($i < count($outsideAir)) {
                        echo ', ';
                    }
                    $i++;
                }
                ?>],
                type: 'line',
                yAxis: 1
            }
<?php
if(min($outsideAir) < 65) {
?>
            ,{
                name: 'Heating Degree Days',
                data: [<?php
                $i = 1;
                foreach($outsideAir as $date => $temp) {
                    if($temp < 65) {
                        echo 65 - $temp;
                    }else{
                        echo 'null';
                    }
                    if($i < count($outsideAir)) {
                        echo ', ';
                    }
                    $i++;
                }
                ?>],
                type: 'line',
                yAxis: 2
            }
<?php
}
if(max($outsideAir) > 65) {
?>
            ,{
                name: 'Cooling Degree Days',
                data: [<?php
                $i = 1;
                foreach($outsideAir as $date => $temp) {
                    if($temp > 65) {
                        echo $temp - 65;
                    }else{
                        echo 'null';
                    }
                    if($i < count($outsideAir)) {
                        echo ', ';
                    }
                    $i++;
                }
                ?>],
                type: 'line',
                yAxis: 2
            }
<?php
}
?>
];
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
                    if(isset($_GET['z']) && $_GET['z'] > 0) {
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
                    class="btn btn-mini <?php if(!isset($_GET['z']) || $_GET['z'] == 0){echo ' active';} ?>"
                    href="./<?php
                        $params['z'] = 0;
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
    $params['z'] = 0;
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
