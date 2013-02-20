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
$sensors['Power01']['SourceID'] = 4;
$sensors['Power01']['SensorColName'] = 'Power01';
$sensors['Power01']['SensorRefName'] = 'Power01';
$sensors['Power01']['SensorLabel'] = 'Power01';
$sensors['Power01']['WebSubPageName'] = 'Main';

$sensors['CalcResult1']['SourceID'] = 99;
$sensors['CalcResult1']['SensorColName'] = 'CalcResult1';
$sensors['CalcResult1']['SensorRefName'] = 'CalcResult1';
$sensors['CalcResult1']['SensorLabel'] = 'CalcResult1';
$sensors['CalcResult1']['WebSubPageName'] = 'Main';

//Tables Stuff
$tablesUsed = array(pickTable($zone));
foreach($sensors as $sensor) {
    if(!in_array(pickTable($sensor['SourceID']), $tablesUsed)) {
        array_push($tablesUsed, pickTable($sensor['SourceID']));
    }
}

// Set defaults
$start = date('Y-m-d', strtotime('-4 days'));
$end = date('Y-m-d');
$elec = 0.17;
$oil = 3.75;
$gas = 1.15;
// Override defaults if possible
if(count($_GET) > 0) {
    $start = $_GET['start'];
    $end = $_GET['end'];
    $elec = $_GET['elec'];
    $oil = $_GET['oil'];
    $gas = $_GET['gas'];
}
$elec1M = ($elec / (3412 * 1) * 1000000);
$oil1M  = ($oil / (138690 * 0.82)) * 1000000;
$gas1M  = ($gas / (100000 * 0.82)) * 1000000;
$daysToDisplay = floor((strtotime($end) - strtotime($start)) / (60 * 60 * 24));

// Get values from the database
for ($i=0; $i <= $daysToDisplay; $i++) {

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
LIMIT 10
";

$bind[':SysID']  = $_SESSION['SysID'];
$bind[':date'] = date('Y-m-d', strtotime($end.' - '.$i.' days'));

$results = $db->fetchAll($query, $bind);

// Combine datapoints into 1 per recnum
foreach($results as $result) {
    $datapoints[$result['DateStamp'] . $result['TimeStamp']]['Recnum'] = $result['Recnum'];
    $datapoints[$result['DateStamp'] . $result['TimeStamp']]['DateStamp'] = $result['DateStamp'];
    $datapoints[$result['DateStamp'] . $result['TimeStamp']]['TimeStamp'] = $result['TimeStamp'];
    $datapoints[$result['DateStamp'] . $result['TimeStamp']]['CalcResult1'] = $result['CalcResult1'];

    $datapoints[$result['DateStamp'] . $result['TimeStamp']]['Power01'] += $result['Power01'];

    $data[$result['DateStamp']]['Air'] += $result[$sensors['OutsideAir']['SensorColName']]/100;
    $data[$result['DateStamp']]['Points']++;
}


foreach($datapoints as $vals) {
    $data[$vals['DateStamp']]['DateStamp'] = $vals['DateStamp'];
    $data[$vals['DateStamp']]['CalcResult1'] += $vals['CalcResult1'];
    $data[$vals['DateStamp']]['Power01'] += $vals['Power01'];
    $data[$vals['DateStamp']]['Count']++;
}

foreach($data as $d) {
    $data[$d['DateStamp']]['AbsorbedBTU'] = $d['CalcResult1']/$d['Count'];
    $data[$d['DateStamp']]['ElectUsage'] = $d['Power01']/$d['Count'];
    $data[$d['DateStamp']]['CostOfOperation'] =  $data[$d['DateStamp']]['ElectUsage'] * $elec;
    $data[$d['DateStamp']]['OilCost']   = ($data[$d['DateStamp']]['AbsorbedBTU'] / 1000000) * $oil1M;
    $data[$d['DateStamp']]['GasCost']   = ($data[$d['DateStamp']]['AbsorbedBTU'] / 1000000) * $gas1M;
    $data[$d['DateStamp']]['ElectCost'] = ($data[$d['DateStamp']]['AbsorbedBTU'] / 1000000) * $elec1M;
    unset($data[$d['DateStamp']]['CalcResult1']);
    unset($data[$d['DateStamp']]['Power01']);
    unset($data[$d['DateStamp']]['Count']);
}

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
                title: {text: 'Costs'}
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
                name: 'Oil Cost',
                data: [<?php
                $i = 1;
                foreach($data as $date => $vals) {
                    echo $vals['OilCost'];
                    if($i < count($data)) {echo ', ';}
                    $i++;
                }
                ?>]
            },
            {
                name: 'Gas Cost',
                data: [<?php
                $i = 1;
                foreach($data as $date => $vals) {
                    echo $vals['GasCost'];
                    if($i < count($data)) {echo ', ';}
                    $i++;
                }
                ?>]
            },
            {
                name: 'Electric Cost',
                data: [<?php
                $i = 1;
                foreach($data as $date => $vals) {
                    echo $vals['ElectCost'];
                    if($i < count($data)) {echo ', ';}
                    $i++;
                }
                ?>]
            },
            {
                name: 'Outside Air',
                type: 'line',
                data: [<?php
                $i = 1;
                foreach($data as $date => $vals) {
                    echo $vals['Air'] / $vals['Points'];
                    if($i < count($data)) {echo ', ';}
                    $i++;
                }
                ?>],
                yAxis: 1
            }
        ];
        </script>

        <div class="row">
            <h1 class="span10 offset1">Energy Comparison</h1>
        </div>

        <div
            id="chart"
            class="chart-container data"
            style="min-width: 400px; min-height: 500px; margin: 0 auto">
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
