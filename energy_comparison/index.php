<?php
/**
 *------------------------------------------------------------------------------
 * Energy Comparison
 *------------------------------------------------------------------------------
 *
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
require_once('../includes/pageStart.php');

checkSystemSet($config);

// Handle form submission
if(count($_POST) > 0) {
    header('Location: ./' . buildURLparameters($_POST));
}

$db = new db($config);

$query = "
SELECT
    SysMap.SourceID,
    SysMap.SensorColName,
    SysMap.SensorRefName,
    WebRefTable.SensorLabel,
    WebRefTable.WebSubPageName
FROM SysMap, WebRefTable
WHERE WebRefTable.WebSubPageName = 'Main'
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

// Add sensors that I know the location of and can't select from the SysMap
$EnergySensor['SourceID'] = 4;
$EnergySensor['SensorColName'] = 'Power01';
$EnergySensor['SensorRefName'] = 'Power01';
$EnergySensor['SensorLabel'] = 'Power01';
$EnergySensor['WebSubPageName'] = 'Main';

$sensors['CalcResult1']['SourceID'] = 99;
$sensors['CalcResult1']['SensorColName'] = 'CalcResult1';
$sensors['CalcResult1']['SensorRefName'] = 'CalcResult1';
$sensors['CalcResult1']['SensorLabel'] = 'CalcResult1';
$sensors['CalcResult1']['WebSubPageName'] = 'Main';

//Tables Stuff
$tablesUsed = array(pickTable(1));
foreach($sensors as $sensor) {
    if(!in_array(pickTable($sensor['SourceID']), $tablesUsed)) {
        array_push($tablesUsed, pickTable($sensor['SourceID']));
    }
}

// Set defaults
$start = date('Y-m-d', strtotime('-1 week'));
$end = date('Y-m-d');
$elec = 0.17;
$oil = 3.75;
$gas = 1.15;
$elecefficiency = 1;
$oilefficiency = 0.82;
$gasefficiency = 0.82;
// Override defaults if possible
if(count($_GET) > 0) {
    $start = $_GET['start'];
    $end = $_GET['end'];
    $elec = $_GET['elec'];
    $oil = $_GET['oil'];
    $gas = $_GET['gas'];
}
$elec1M = ($elec / (3412 * $elecefficiency) * 1000000);
$oil1M  = ($oil / (138690 * $oilefficiency)) * 1000000;
$gas1M  = ($gas / (100000 * $gasefficiency)) * 1000000;
$daysToDisplay = floor((strtotime($end) - strtotime($start)) / (60 * 60 * 24));

$data = array(); // I'll store some data in here

// Get values from the database
for ($i=0; $i <= $daysToDisplay; $i++) {

$thisDate = date('Y-m-d', strtotime($end.' - '.$i.' days'));
$data[$thisDate] = array();

$query = "
SELECT
    SourceHeader.Recnum,
    SourceHeader.DateStamp,
    SourceHeader.TimeStamp";
foreach($sensors as $sensor) {
    $query .= ",
    " . pickTable($sensor['SourceID']) . "." . $sensor['SensorColName'];
}
    $query .= "
FROM SourceHeader";
foreach($tablesUsed as $table) {
    $query .= ", " . $table;
}
$query .= "
WHERE SourceHeader.SysID = :SysID
  AND SourceHeader.DateStamp = :date";
foreach($tablesUsed as $table) {
    $query .= "
  AND SourceHeader.Recnum = " . $table . ".HeadID";
}
$query .= "
ORDER BY SourceHeader.DateStamp ASC, SourceHeader.TimeStamp ASC
";

$bind[':SysID']  = $_SESSION['SysID'];
$bind[':date'] = $thisDate;

$results = $db->fetchAll($query, $bind);

foreach($results as $dataPoint) {
    $data[$dataPoint['DateStamp']]['AbsorbedBTU'] += $dataPoint['CalcResult1'];
    $data[$dataPoint['DateStamp']]['Air'] += $dataPoint[$sensors['OutsideAir']['SensorColName']] / 100;
    $data[$dataPoint['DateStamp']]['count']++;
}

// Get HP and WP energy use at the begining and end of the day
$q = "
SELECT
    SourceHeader.DateStamp,
    SourceHeader.TimeStamp,
    SourceData4.Power04
FROM SourceHeader, SourceData4
WHERE SourceHeader.Recnum = SourceData4.HeadID
  AND SourceHeader.DateStamp = :date
  AND SourceHeader.SysID = :SysID
";
$b[':SysID'] = $_SESSION['SysID'];
$b[':date'] = date('Y-m-d', strtotime($end.' - '.$i.' days'));

$HPEnergyEnd   = $db->fetchRow($q . "AND SourceData4.PwrSubAddress = 1 ORDER BY SourceHeader.TimeStamp DESC LIMIT 0,1", $b);
$HPEnergyBegin = $db->fetchRow($q . "AND SourceData4.PwrSubAddress = 1 ORDER BY SourceHeader.TimeStamp  ASC LIMIT 0,1", $b);
$WPEnergyEnd   = $db->fetchRow($q . "AND SourceData4.PwrSubAddress = 3 ORDER BY SourceHeader.TimeStamp DESC LIMIT 0,1", $b);
$WPEnergyBegin = $db->fetchRow($q . "AND SourceData4.PwrSubAddress = 3 ORDER BY SourceHeader.TimeStamp  ASC LIMIT 0,1", $b);


$data[$thisDate]['KWH'] = ( ($HPEnergyEnd['Power04'] - $HPEnergyBegin['Power04']) + ($WPEnergyEnd['Power04'] - $WPEnergyBegin['Power04']) );
$data[$thisDate]['COO'] = $data[$thisDate]['KWH'] * $elec;
$data[$thisDate]['OilEquiv'] = ($data[$thisDate]['AbsorbedBTU'] / 1000000) * $oil1M;
$data[$thisDate]['GasEquiv'] = ($data[$thisDate]['AbsorbedBTU'] / 1000000) * $gas1M;
$data[$thisDate]['ElecEquiv'] = ($data[$thisDate]['AbsorbedBTU'] / 1000000) * $elec1M;

} // Now Im done getting values from the database

$data = array_reverse($data);

require_once('../includes/header.php');
?>
        <script type="text/javascript">
        var chartType = 'column';
        var legend = {enabled: 1};
        var plotOptions = {
            column: {
            }
        };
        var yAxisData = [
            {
                title: {text: 'BTUs'}
            },
            {
                title: {text: 'Kilowatt Hours'}
            },
            {
                opposite: 1,
                title: {text: 'Dollars'}
            },
            {
                opposite: 1,
                title: {text: 'Temperature'}
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
                name: 'Absorbed BTUs',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['AbsorbedBTU'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                yAxis: 0
            },
            {
                name: 'Elect Usage',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['KWH'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                yAxis: 1
            },
            {
                name: 'Cost of Operation',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['COO'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'line',
                yAxis: 2
            },
            {
                name: 'Oil Equiv Cost',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['OilEquiv'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'line',
                yAxis: 2
            },
            {
                name: 'Gas Equiv Cost',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['GasEquiv'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'line',
                yAxis: 2
            },
            {
                name: 'Elec Equiv Cost',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['ElecEquiv'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'line',
                yAxis: 2
            },
            {
                name: 'OutsideAir',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['Air'] / $day['count'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'line',
                yAxis: 3
            }
        ];
        </script>

        <div class="row">
            <h1 class="span10 offset1">Energy Comparison</h1>
        </div>

        <div
            id="chart"
            class="chart-container data"
            style="min-height: 500px; margin: 0 auto">
        </div>

        <form action="./" method="POST">
        <!-- Date Range -->
        <div class="row">
            <div class="span3 offset3">
                <label>
                    From <br>
                    <input
                        class="datepick span3"
                        type="text"
                        name="start"
                        value="<?php echo $start; ?>">
                </label>
            </div>
            <div class="span3">
                <label>
                    From <br>
                    <input
                        class="datepick span3"
                        type="text"
                        name="end"
                        value="<?php echo $end; ?>">
                </label>
            </div>
        </div>

        <br>

        <!-- Prices -->
        <div class="row">
            <div class="span2 offset3">
                <label>
                    Electricity Price <br>
                    <input
                        class="span2"
                        type="text"
                        name="elec"
                        value="<?php echo $elec; ?>">
                </label>
            </div>
            <div class="span2">
                <label>
                    Oil Price <br>
                    <input
                        class="span2"
                        type="text"
                        name="oil"
                        value="<?php echo $oil; ?>">
                </label>
            </div>
            <div class="span2">
                <label>
                    Gas Price <br>
                    <input
                        class="span2"
                        type="text"
                        name="gas"
                        value="<?php echo $gas; ?>">
                </label>
            </div>
        </div>

        <br>

        <!-- Go Time -->
        <div class="row">
            <button class="btn btn-primary btn-large btn-block span6 offset3">Submit</button>
        </div>
        </form>

<?php
require_once('../includes/footer.php');
?>
