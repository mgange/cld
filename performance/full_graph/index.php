<?php
/**
 *------------------------------------------------------------------------------
 * Full Graph
 *------------------------------------------------------------------------------
 *
 */

require_once('../../includes/pageStart.php');

if(count($_POST) > 0) {
    if(
        isset($_POST['date']) && $_POST['date'] != ''&&
        isset($_POST['time']) && $_POST['time'] != ''
    ) {
        $endTime = strtotime($_POST['date'] . ' ' . $_POST['time']);
        $params['date'] = date('Y-m-d', $endTime);
        $params['time'] = date('H:i:s', $endTime);
    }

    if(
        isset($_POST['range'])
        && $_POST['range'] != ''
        && withinRange(intval($_POST['range']), 0, 25)
    ) {
        $params['range'] = intval($_POST['range']);
    }

    /**
     * The page redirects to the built url and loads this file for a second
     * time. This avoids POST issues when refreshign the page.
     */
    header('Location: ./' . buildURLparameters($params));
}

checkSystemSet($config);

$db = new db($config);

$buildingNames = $db -> fetchRow('SELECT SysName FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$buildingName = $buildingNames['SysName'];

$zone = 'Main';

/* Get the default table.column values for the values to be graphed */
$query = "
SELECT
    SysMap.SourceID,
    SysMap.SensorColName,
    SysMap.SensorName,
    SysMap.SensorRefName,
    WebRefTable.SensorLabel
FROM SysMap, WebRefTable
WHERE (
       (SysMap.SensorRefName = 'WaterIn'    AND WebRefTable.WebSubPageName = '$zone')
    OR (SysMap.SensorRefName = 'WaterOut'   AND WebRefTable.WebSubPageName = '$zone')
    OR (SysMap.SensorRefName = 'AirIn'      AND WebRefTable.WebSubPageName = '$zone')
    OR (SysMap.SensorRefName = 'AirOut'     AND WebRefTable.WebSubPageName = '$zone')
    OR (SysMap.SensorRefName = 'OutsideAir' AND WebRefTable.WebSubPageName = '$zone')
    OR (SysMap.SensorRefName = 'FlowMain'   AND WebRefTable.WebSubPageName = '$zone')
    OR (SysMap.SensorRefName = 'Pressure'   AND WebRefTable.WebSubPageName = '$zone')
    OR (SysMap.SensorRefName = 'SysCOP'     AND WebRefTable.WebSubPageName = '$zone')
    OR (SysMap.SensorRefName = 'HPCOP'      AND WebRefTable.WebSubPageName = '$zone')
    )
  AND SysMap.SensorRefName = WebRefTable.SensorName";
if($zone == 'Main') {
    $query .= "
  AND SysMap.SourceID != 1
  AND SysMap.SourceID != 2
  AND SysMap.SourceID != 3
  AND SysMap.SourceID != 5
    ";
}
$defaultSensors = $db->fetchAll($query . "
    AND DAMID = '000000000000'");

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

/* An array of tables being used to buid the FROM part of a query */
$tablesUsed = array('0');
foreach($sensors as $sensor) {
    if(!in_array($sensor['SourceID'], $tablesUsed)) {
        array_push($tablesUsed, $sensor['SourceID']);
    }
}

/* The range of time(in hours) that will be displayed */
if(isset($_GET['range']) && withinRange($_GET['range'], 0, 25)) {
    $range = intval($_GET['range']);
    $params['range'] = $range;
}else{
    $range = 4;
}

if(isset($_GET['date']) && isset($_GET['time'])) {
    $endTime = strtotime($_GET['date'] . ' ' . $_GET['time']);
    $params['date'] = date('Y-m-d', $endTime);
    $params['time'] = date('H:i:s', $endTime);
}else{
    $endTime = strtotime('now');
}

$startTime = $endTime - ($range*3600);

$zoneTable = 'SourceData0';

$query = "SELECT DISTINCT
    SourceHeader.Recnum,
    SourceHeader.DateStamp,
    SourceHeader.TimeStamp,
    ";
foreach($sensors as $sensor) {
     $query .= $tablesIndex[$sensor['SourceID']] . "." . $sensor['SensorColName'] . ",
    ";
}
$query .=
      $zoneTable.".DigIn01,
    ".$zoneTable.".DigIn02,
    ".$zoneTable.".DigIn03,
    ".$zoneTable.".DigIn04,
    ".$zoneTable.".DigIn05";
$query .= "
FROM
    SourceHeader";
foreach($tablesUsed as $table) {
    $query .= ", " . $tablesIndex[$table];
}
$query .= "
WHERE SourceHeader.SysID = " . $_SESSION['SysID'];
foreach($tablesUsed as $table) {
    $query .= "
  AND SourceHeader.Recnum = ". $tablesIndex[$table].".HeadID";
}
$query .= "
  AND
(
    (
        (
                SourceHeader.DateStamp = '" . date('Y-m-d', $endTime) . "'
            AND SourceHeader.TimeStamp <= '" . date('H:i:s', $endTime) . "'
        )
        OR
            SourceHeader.DateStamp < '" . date('Y-m-d', $endTime) . "'
    )
    AND
    (
        (
                SourceHeader.DateStamp = '" . date('Y-m-d', $startTime) . "'
            AND SourceHeader.TimeStamp >= '" . date('H:i:s', $startTime) . "'
        )
        OR
            SourceHeader.DateStamp > '" . date('Y-m-d', $startTime) . "'
    )
)
ORDER BY
    SourceHeader.DateStamp DESC,
    SourceHeader.TimeStamp DESC
";

/**
 * The query orders by date and time descending so that it will get date going
 * backwards from the specified time. Now that it's selected array_reverse() is
 * used to correct the order for the graph.
 */
$result = array_reverse( $db -> fetchAll($query) );


foreach($result as $resultRow) {
    foreach($resultRow as $key => $val) {
        $vals[$key][$resultRow['Recnum']] = $val;
    }
}
extract($vals);

/* Get a list of date/time stamps for chart labels */
foreach($result as $val) {
    $dateTime = strtotime($val['DateStamp'].' '.$val['TimeStamp']);
    $Stamp[$val['Recnum']]  =   date('g:i:s A', $dateTime);
    $Stamp[$val['Recnum']] .= '<br>';
    $Stamp[$val['Recnum']] .= date('M. j, Y', $dateTime);
}

foreach($sensors as $sensor) {
    $systemMap[$sensor['SensorColName']] = $sensor['SensorLabel'];
}

$statusIndex['System Off'] = array(
    'text' => 'System Off',
    'color' => 'rgba(255, 255, 255, 0)'
);
$statusIndex['Fan Only'] = array(
    'text' => 'Fan Only',
    'color' => 'rgba(137, 255, 93, 0.2)'
);
$statusIndex['Stage 1 Heat'] = array(
    'text' => 'Stage 1 Heat',
    'color' => 'rgba(232, 193, 6, 0.2)'
);
$statusIndex['Stage 2 Heat'] = array(
    'text' => 'Stage 2 Heat',
    'color' => 'rgba(255, 131, 7, 0.2)'
);
$statusIndex['Emerg. Heat'] = array(
    'text' => 'Emerg. Heat',
    'color' => 'rgba(255, 0, 0, 0.2)'
);
$statusIndex['Stage 3 Heat'] = array(
    'text' => 'Stage 3 Heat',
    'color' => 'rgba(232, 88, 35, 0.2)'
);
$statusIndex['Stage 1 Cool'] = array(
    'text' => 'Stage 1 Cool',
    'color' => 'rgba(30, 155, 255, 0.2)'
);
$statusIndex['Stage 2 Cool'] = array(
    'text' => 'Stage 2 Cool',
    'color' => 'rgba(15, 72, 232, 0.2)'
);
$statusIndex['Invalid State'] = array(
    'text' => 'Invalid State',
    'color' => 'rgba(0, 0, 0, 0.5)'
);

require_once('../../includes/header.php');
?>
            <script type="text/javascript">
            var yAxisData = [
              {
                title: {text: 'Temperature / Pressure'}
              },
              {
                title: {text: 'Flow Rate (Gallons/Minute)',
                        style: {
                          color: '#aaa'
                        }
                      },
                max: 10,
                opposite: true
              }
              ];
            var plotOptions = {
                  line: {
                      allowPointSelect: false,
                      dataLabels: {
                          enabled: false
                      },
                      enableMouseTracking: true
                      },
                      lineWidth: 1,
                      series: {
                        marker: {
                          enabled: false,
                          radius: 2
                        },
                        point: {
                          events: {
                            click: function(){
                              if(!Modernizr.touch) {
                               loadStatus(recnums[this.x]);
                              }
                            }
                          }
                        }
                      },
                      shadow: false
              };
            var tooltip = {animate: false,
                        crosshairs: [
                        { // Vertical
                          color: '#729472',
                          dashStyle: 'solid',
                          width: 1
                        },
                        { // Horizontal
                          color: '#eee',
                          dashStyle: 'solid',
                          width: 1
                        }
                        ],
                        enabled: (typeof tooltipEnable != 'undefined')?tooltipEnable:1
              };
            var recnums = [<?php echoJSarray($Recnum); ?>]
            var categories = [<?php echoJSarray($Stamp, "'") ?>];
            xPlotBands = [
<?php


$i = 0;

/* Set a starting point for the plotBands in the graph */
$currStatus = Systemlogic(
    $result[0]['DigIn04'],
    $result[0]['DigIn01'],
    $result[0]['DigIn02'],
    $result[0]['DigIn03'],
    $result[0]['DigIn05'],
    0);

echo "
{
    from: " . $i . ",";

foreach($result as $datapoint) {
    $datapointStatus = Systemlogic(
        $datapoint['DigIn04'],
        $datapoint['DigIn01'],
        $datapoint['DigIn02'],
        $datapoint['DigIn03'],
        $datapoint['DigIn05'],
        0);

    if($datapointStatus != $currStatus) {
    /**
     * Every time the current datapoint's system status is different than
     * the previous datapoint's the plotband closes and a new one is started.
     * The plotBand's color and label text is drawn from the $statusIndex array
     * defined earlier.
     */
        echo "
    to: " . $i . ",
    label: {
        style: {
            fontSize: '1.2em'
        },
    text: '" . $statusIndex[$currStatus]['text'] . "',
    rotation: -30,
    y: 34},
    color: '" . $statusIndex[$currStatus]['color'] . "'
},";
        if($i < count($result)) {
            echo "
{
    from: " . $i . ",";
        }
    }

    $currStatus = $datapointStatus;
    $i++;
}
/**
 * Once plotBands have been drawn over every datapoint in the results set the
 * last band is closed.
 */
echo "
    to: " . $i . ",
    label: {
        style: {
            fontSize: '1.2em'
        },
    text: '" . $statusIndex[$currStatus]['text'] . "',
    rotation: -30,
    y: 34},
    color: '" . $statusIndex[$datapointStatus]['color'] . "'
}";
?>
            ];
            var data = [
<?php

/**
 * Because all of the fields selected in the query are used in the graph
 * anything that doesn't belong in that graph must be removed from the results
 * set.
 * e.g. record numbers and data labels
 */
for ($i=0; $i < count($result); $i++) {
    unset($result[$i]['Recnum']);
    unset($result[$i]['DateStamp']);
    unset($result[$i]['TimeStamp']);
    unset($result[$i]['DigIn01']);
    unset($result[$i]['DigIn02']);
    unset($result[$i]['DigIn03']);
    unset($result[$i]['DigIn04']);
    unset($result[$i]['DigIn05']);
}
foreach($result[0] as $key => $val) {
?>
                {
                    name: "<?php echo $systemMap[$key]; ?>",
<?php if(preg_match('/FlowPress0(1|3)/', $key)){ ?>
                    color: '#aaa',
                    yAxis: 1,
                    zIndex: 1,
<?php }else{ ?>
                    zIndex: 10,
<?php } ?>
                    data: [<?php
                    if($key == 'CalcResult4' || $key == 'CalcResult5') {
                        echoJSarray(eval('return $'. $key . ';'), null, 1, 10);
                    }else{
                        echoJSarray(eval('return $'. $key . ';'), null, 100, 0);
                    }
                    ?>]
                },
<?php
}
?>
            ];

            </script>

        <div class="row">
            <h1 class="span7 offset2">
                Full Graph -
                <span class="building-name">
                    <?php
                        echo $buildingName;
                    ?>
                </span>
            </h1>
        </div>

            <div
                id="chart"
                class="chart-container data<?php if(!isset($_GET['date'])){echo ' refresh';} ?>"
                style="min-width: 400px; min-height: 500px; margin: 0 auto">
            </div>

            <br>
            <div class="row">
                <h5 class="span12 align-center">Date/Time Filter</h5>
            </div>
            <div class="row">
                <div class="span6 offset3">
                    <form class="form-inline" action="./" method="POST">
                        <div class="row">
                            <label class="span2" for="date">Date &nbsp;
                                <input
                                    id="date"
                                    class="datepick span2"
                                    type="text"
                                    name="date"
                                    value="<?php
/**
 * Auto-till the form with previously submitted values. If there are no values
 * to use then fill it with the current date/time and the default time range.
 */
                                        echo date('o-m-d', $endTime);
                                    ?>">
                            </label>
                            <label class="span2" for="time">Time
                                <input
                                    id="time"
                                    class="timepick span2"
                                    type="text"
                                    name="time"
                                    value="<?php
                                        echo date('h:i A', $endTime);
                                    ?>">
                            </label>
                            <label class="span2" for="range">Range
                                <select
                                    id="range"
                                    class="span2"
                                    type="text"
                                    name="range"
                                    >
<?php
for ($i=1; $i <= 6; $i++) {
?>
                                    <option value="<?php echo $i; ?>"<?php
if($range == $i) {
    echo ' selected';
}
                                    ?>>
                                        <?php echo $i . ' Hour'; if($i > 1){ echo 's'; } ?>

                                    </option>
<?php
}
?>
                                    <option value="12"<?=($range == 12) ? ' selected' : ''?>>12 Hours</option>
                                    <option value="24"<?=($range == 24) ? ' selected' : ''?>>24 Hours</option>
                                </select>
                            </label>
                        </div>
                        <br>
                        <input class="btn btn-info btn-large btn-block" type="submit" value="Submit">
                    </form>
                </div>
                <div class="span3">
<?php
if(isset($_GET['date']) && isset($_GET['time'] )) {
    $currentData['range'] = $range;
    if(isset($_GET['z'])){$currentData['z'] = $_GET['z'];}
?>
                    <a
                        class="btn btn-mini span2"
                        href="./<?php echo buildURLparameters($currentData); ?>"
                        style="margin-top: 6px;">
                        Current Data
                    </a>
                    <br>
<?php
}
?>
                    <a
                    class="btn btn-mini span2"
                    href="../<?php echo buildURLparameters($params); ?>"
                    style="margin-top: 6px;">
                        Performance
                    </a>
                    <a
                    class="btn btn-mini span2"
                    href="../COP/<?php echo buildURLparameters($params); ?>"
                    style="margin-top: 6px;">
                        COP
                    </a>
                </div>
            </div>

<?php
require_once('../../includes/footer.php');
?>
