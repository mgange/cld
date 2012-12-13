<?php
/**
 *------------------------------------------------------------------------------
 * Stages Index Page
 *------------------------------------------------------------------------------
 *
 */

function buildURLparameters($arr) {
    if(count($arr) < 1) {
        return '';
    }else{
        $seperator = '?';
        foreach($arr as $key => $val) {
            $url .= $seperator . $key . '=' . $val;
            $seperator = '&';
        }
        return $url;
    }
}
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

    if(isset($_POST['z']) && $_POST['z'] == 'rsm') {
        $params['z'] = 'rsm';
    }
    /**
     * The page redirects to the built url and loads this file for a second
     * time. This avoids POST issues when refreshign the page.
     */
    header('Location: ./' . buildURLparameters($params));
}

checkSystemSet($config);

if(isset($_GET['date']) && isset($_GET['time'])) {
    $datetime = date_create($_GET['date'] . ' ' . $_GET['time']);
    $date = date_format($datetime, 'Y-m-d');
    $time = date_format($datetime, 'H:i:s');
    $startTime = $time;
    $endTime = $time;
}

$db = new db($config);

$buildingNames = $db -> fetchRow('SELECT SysName FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$buildingName = $buildingNames['SysName'];

$zoneTable = 'SourceData';
$zoneTable .= (isset($_GET['z']) && $_GET['z'] == 'rsm')?'1':'0';

/* The range of time(in seconds) that will be displayed */
if(isset($_GET['range']) && withinRange($_GET['range'], 0, 25)) {
    $range = intval($_GET['range'])*3600;
}else{
    $range = 14400;
}

if(isset($_GET['date']) && isset($_GET['time'])) {
    $endTime = strtotime($_GET['date'] . ' ' . $_GET['time']);
}else{
    $endTime = strtotime('now');
}

$startTime = $endTime - $range;

$query = "
SELECT DISTINCT
    SourceHeader.Recnum,
    SourceHeader.DateStamp,
    SourceHeader.TimeStamp,
    ".$zoneTable.".DigIn01,
    ".$zoneTable.".DigIn02,
    ".$zoneTable.".DigIn03,
    ".$zoneTable.".DigIn04,
    ".$zoneTable.".DigIn05
FROM
    SourceHeader, " . $zoneTable . "
WHERE SourceHeader.SysID = " . $_SESSION['SysID'] . "
  AND SourceHeader.Recnum = ".$zoneTable.".HeadID
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

$result = $db -> fetchAll($query);


$totals["System Off"]   = 0;
$totals["Fan Only"]     = 0;
$totals["Stage 1 Heat"] = 0;
$totals["Stage 2 Heat"] = 0;
$totals["Emerg. Heat"]  = 0;
$totals["Stage 3 Heat"] = 0;
$totals["Stage 1 Cool"] = 0;
$totals["Stage 2 Cool"] = 0;

foreach($result as $datapoint) {
    $stage = Systemlogic(
    $datapoint['DigIn04'],
    $datapoint['DigIn01'],
    $datapoint['DigIn02'],
    $datapoint['DigIn03'],
    $datapoint['DigIn05'],
    0);
    $totals[$stage]++;
}

require_once('../../includes/header.php');
?>
        <script>
        var chartType = 'column';
        var legend = {enabled: 0};
        var plotOptions = {column: {}}
        var tooltipEnable = 0;
        var yAxisData = [
            {
                title: {text: 'Minutes in Each Stage'}
              }];
        var xAxisOptions = [
            {
                title: {text: 'Stages'}
            }];
        var categories = [<?php
foreach($totals as $stage => $count) {
    echo "'" . $stage . "', ";
}
?>
        ];
        var data = [
        {
            data: [<?php
$i = 0;
foreach($totals as $stage => $count) {
    $i++;
    echo round($count/2);
    if($i < count($totals)){echo ', ';}
}
?>
            ]
        }
        ];
        </script>


        <div class="row">
            <h1 class="span7 offset2">
                Time / Stage -
                <span class="building-name">
                    <?php
                        echo $buildingName;
                        if($_GET['z'] =='rsm') {
                            echo ' - RSM';
                        }
                    ?>
                </span>
            </h1>
            <div class="btn-group span3">
                <a class="btn btn-mini span1<?php if($_GET['z']!='rsm'){echo ' active';} ?>" href="./">Main</a>
                <a class="btn btn-mini span1<?php if($_GET['z']=='rsm'){echo ' active';} ?>" href="./?z=rsm">RSM</a>
            </div>

            <div
                id="chart"
                class="chart-container data"
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
                                        if(isset($_GET['date'])) {
                                            echo $_GET['date'];
                                        }else{
                                            echo date('o-m-d');
                                        }
                                    ?>">
                            </label>
                            <label class="span2" for="time">Time &nbsp;
                                <input
                                    id="time"
                                    class="timepick span2"
                                    type="text"
                                    name="time"
                                    value="<?php
                                        if(isset($_GET['date'])) {
                                            echo $_GET['time'];
                                        }else{
                                            echo date('h:i A');
                                        }
                                    ?>">
                            </label>
                            <label class="span2" for="range">Range &nbsp;
                                <select
                                    id="range"
                                    class="span2"
                                    type="text"
                                    name="range"
                                    >
<?php
if(isset($_GET['range'])) { $range = intval($_GET['range']);}else{$range = 4;}
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
                        <input type="hidden" name="z" value="<?php echo ($_GET['z']=='rsm')?'rsm':'main'; ?>">
                    </form>
                </div>
                <div class="span3">
<?php
if(isset($_GET['date']) && isset($_GET['time'] )) {
?>
                    <a class="btn btn-mini span2" href="./" style="margin-top: 6px;">
                        Current Data
                    </a>
                    <br>
<?php
}
?>
                    <a class="btn btn-mini span2" href="../" style="margin-top: 6px;">
                        Performance
                    </a>
                    <a class="btn btn-mini span2" href="../COP" style="margin-top: 6px;">
                        COP
                    </a>
                    <a class="btn btn-mini span2" href="../full_graph" style="margin-top: 6px;">
                        Full Graph
                    </a>
                </div>
            </div>

<?php
require_once('../../includes/footer.php');
?>
