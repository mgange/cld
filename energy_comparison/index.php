<?php
/**
 *------------------------------------------------------------------------------
 * Energy Comparison
 *------------------------------------------------------------------------------
 *
 */
function percentage($val1, $val2, $precision=2)
{
    $division = $val1 / $val2;
    $res = $division * 100;
    $res = round($res, $precision);
    if($res > 100){
        return 100;
    }else{
        return $res;
    }
}

require_once('../includes/pageStart.php');

checkSystemSet($config);

// Handle form submission
if(count($_POST) > 0) {
    header('Location: ./' . buildURLparameters($_POST));
}

$db = new db($config);

$buildingNames = $db -> fetchRow('SELECT SysName FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$buildingName = $buildingNames['SysName'];

// All this, just to see where OutsideAir comes from.
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
$airDefault = $db->fetchRow($query . "AND SysMap.DAMID = '000000000000'");
$airTable = pickTable($airDefault['SourceID']);
$airCol = $airDefault['SensorColName'];

/* Get the custom table.column values specific to the current SysID */
$airCustom = $db->fetchRow($query . "AND SysMap.SysID = " . $_SESSION['SysID']);
if(gettype($airCustom) != 'boolean') {
    $airTable = pickTable($airCustom['SourceID']);
    $airCol = $airCustom['SensorColName'];
}


$query = "
SELECT
    SysMap.SourceID,
    SysMap.SensorColName,
    SysMap.SensorRefName,
    SysMap.SensorAddress,
    SysMap.SensorActive,
    WebRefTable.SensorLabel,
    WebRefTable.WebSubPageName
FROM SysMap, WebRefTable
WHERE WebRefTable.WebSubPageName = 'Main'
  AND SysMap.WebSensRefNum = WebRefTable.WebSensRefNum
  AND (
           SysMap.SensorRefName = 'EnergyHP'
        OR SysMap.SensorRefName = 'EnergyWP'
      )
";
$default = $db->fetchAll($query . "AND SysMap.DAMID = '000000000000'");
foreach ($default as $key => $value) {
    $powerSensors[$value['SensorRefName']] = $value;
}

$custom = $db->fetchAll($query . "AND SysMap.SysID = " . $_SESSION['SysID']);
foreach ($custom as $key => $value) {
    $powerSensors[$value['SensorRefName']] = $value;
    if(!$value['SensorActive']) {
        unset($powerSensors[$value['SensorRefName']]);
    }
}


// Set defaults
$start = date('Y-m-d', strtotime('-1 week'));
$end = date('Y-m-d');
$elec = 0.17;
$oil = 3.75;
$gas = 1.15;
$prop = 2.40;
$elecefficiency = 1;
$oilefficiency = 0.82;
$gasefficiency = 0.82;
$propefficiency = 0.78;
// Override defaults if possible
if(count($_GET) > 0) {
    $start = $_GET['start'];
    $end = $_GET['end'];
    $elec = $_GET['elec'];
    $oil = $_GET['oil'];
    $gas = $_GET['gas'];
    $prop = $_GET['prop'];
    $elecefficiency = percentage($_GET['elecefficiency'],1)/100;
    $oilefficiency  = percentage($_GET['oilefficiency'],1)/100;
    $gasefficiency  = percentage($_GET['gasefficiency'],1)/100;
    $propefficiency  = percentage($_GET['propefficiency'],1)/100;
}
$elec1M  = ($elec / (3412   * $elecefficiency) * 1000000);
$oil1M   = ($oil  / (138690 * $oilefficiency)) * 1000000;
$gas1M   = ($gas  / (100000 * $gasefficiency)) * 1000000;
$prop1M  = ($prop / (91333  * $propefficiency)) * 1000000;

$daysToDisplay = floor((strtotime($end) - strtotime($start)) / (60 * 60 * 24));
if($daysToDisplay > 10) {$daysToDisplay = 10;} // Limit days that can possibly be displayed

$data = array();


$query = "
    SELECT
        AVG(".$airTable.".".$airCol.") AS OutsideAir,
        SUM(SensorCalc.CalcResult1) AS Absorbed,
        AVG(SensorCalc.CalcResult1) AS BTU
    FROM SourceHeader, ".$airTable.", SensorCalc
    WHERE SourceHeader.DateStamp = :date
      AND SourceHeader.SysID = :SysID
      AND SourceHeader.Recnum = ".$airTable.".HeadID
      AND SourceHeader.Recnum = SensorCalc.HeadID
";
$bind[':SysID'] = $_SESSION['SysID'];

// Get values from the database
for ($i=0; $i <= $daysToDisplay; $i++) {

    $thisDate = date('Y-m-d', strtotime($end.' - '.$i.' days'));
    $bind[':date'] = $thisDate;
    $data[$thisDate] = array('KWH' => 0);


    foreach($powerSensors as $name => $sens) {
        $powerQuery = "
        SELECT ".pickTable($sens['SourceID']).".".$sens['SensorColName']."
        FROM SourceHeader, ".pickTable($sens['SourceID'])."
        WHERE SourceHeader.Recnum = ".pickTable($sens['SourceID']).".HeadID
          AND SourceHeader.SysID = :SysID
          AND ".pickTable($sens['SourceID']).".".$sens['SensorColName']." > 0
          AND SourceHeader.DateStamp = :date
          AND ".pickTable($sens['SourceID']).".PwrSubAddress = ".$sens['SensorAddress']."
        ORDER BY SourceHeader.TimeStamp ";
        $endPower = $db->fetchRow($powerQuery."DESC LIMIT 0,1", $bind);
        $startPower = $db->fetchRow($powerQuery."ASC LIMIT 0,1", $bind);

        $data[$thisDate]['KWH'] += $endPower[$sens['SensorColName']] - $startPower[$sens['SensorColName']];
    }

    $result = $db->fetchRow($query, $bind);

    $data[$thisDate]['Absorbed'] = $result['Absorbed'];
    $data[$thisDate]['BTU'] = $result['BTU'] * 24;
    $data[$thisDate]['COO'] = ($data[$thisDate]['KWH']) * $elec;
    $data[$thisDate]['GasEq']  = ($data[$thisDate]['BTU'] / 1000000) * $gas1M;
    $data[$thisDate]['OilEq']  = ($data[$thisDate]['BTU'] / 1000000) * $oil1M;
    $data[$thisDate]['PropEq'] = ($data[$thisDate]['BTU'] / 1000000) * $prop1M;
    $data[$thisDate]['ElecEq'] = ($data[$thisDate]['BTU'] / 1000000) * $elec1M;
    $data[$thisDate]['OutsideAir'] = $result['OutsideAir'];
} // Now Im done getting values from the database


$data = array_reverse($data);


require_once('../includes/header.php');
?>
        <script type="text/javascript">
        var chartType = 'column';
        var legend = {enabled: 1};
        var plotOptions = {
            line: {
                zIndex: 100
            }
        };
        var yAxisData = [
            {
                title: {text: 'BTUs'}
            },
            {
                opposite: 1,
                title: {text: 'Dollars'}
            },
            {
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
                        echo round($day['BTU'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'line',
                yAxis: 0
            },
            {
                name: 'Cost of Operation',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['COO']/100, 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'column',
                yAxis: 1
            },
            {
                name: 'Gas Equiv Cost',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['GasEq'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'column',
                yAxis: 1
            },
            {
                name: 'Oil Equiv Cost',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['OilEq'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'column',
                yAxis: 1
            },
            {
                name: 'Propane Equiv Cost',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['PropEq'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'column',
                yAxis: 1
            },
            {
                name: 'Elec Equiv Cost',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['ElecEq'], 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'column',
                yAxis: 1
            },
            {
                name: 'Outside Air',
                data: [<?php
                    $i = 1;
                    foreach($data as $day) {
                        echo round($day['OutsideAir'] / 100, 2);
                        if($i < count($data)){echo ', ';}
                        $i++;
                    }
                ?>],
                type: 'line',
                yAxis: 2
            }
        ];
        </script>

        <div class="row">
            <h1 class="span10 offset1">
                Energy Comparison -
                <span class="building-name">
                    <?php
                        echo $buildingName;
                    ?>
                </span>
            </h1>
        </div>

        <div
            id="chart"
            class="chart-container data">
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
            <div class="span2 offset2">
                <label>
                    Gas Price <br>
                    <input
                        class="span2"
                        type="text"
                        name="gas"
                        value="<?php echo $gas; ?>">
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
                    Propane Price <br>
                    <input
                        class="span2"
                        type="text"
                        name="prop"
                        value="<?php echo $prop; ?>">
                </label>
            </div>
            <div class="span2">
                <label>
                    Electricity Price <br>
                    <input
                        class="span2"
                        type="text"
                        name="elec"
                        value="<?php echo $elec; ?>">
                </label>
            </div>
        </div>

        <!-- Efficiencies -->
        <div class="row">
            <div class="span2 offset2">
                <label>
                    Gas Efficiency <br>
                    <input
                        class="span2"
                        type="text"
                        name="gasefficiency"
                        value="<?php echo $gasefficiency; ?>">
                </label>
            </div>
            <div class="span2">
                <label>
                    Oil Efficiency <br>
                    <input
                        class="span2"
                        type="text"
                        name="oilefficiency"
                        value="<?php echo $oilefficiency; ?>">
                </label>
            </div>
            <div class="span2">
                <label>
                    Propane Efficiency <br>
                    <input
                        class="span2"
                        type="text"
                        name="propefficiency"
                        value="<?php echo $propefficiency; ?>">
                </label>
            </div>
            <div class="span2">
                <label>
                    Electricity Efficiency <br>
                    <input
                        class="span2"
                        type="text"
                        name="elecefficiency"
                        value="<?php echo $elecefficiency; ?>">
                </label>
            </div>
        </div>

        <br>

        <!-- Go Time -->
        <div class="row">
            <button class="btn btn-primary btn-large span6 offset3">Submit</button>
        </div>
        </form>

<?php
require_once('../includes/footer.php');
?>
