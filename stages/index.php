<?php
/**
 *------------------------------------------------------------------------------
 * Stages Index Page
 *------------------------------------------------------------------------------
 *
 */

require_once('../includes/pageStart.php');

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

$db = new db($config);

$buildingNames = $db -> fetchRow('SELECT SysName FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$buildingName = $buildingNames['SysName'];

$numRSM = $db -> fetchRow('SELECT NumofRSM FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$numRSM = $numRSM['NumofRSM'];

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

ini_set('memory_limit','200M');
$startTime = $endTime - (86400*30);

$zoneTable = 'SourceData';
if(isset($_GET['z']) && withinRange(intval($GET['z']), -1, $numRSM + 1)) {
    $params['z'] = intval($_GET['z']);
    $zoneTable .= intval($_GET['z']);
}else{
    $zoneTable .= '0';
}

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

$result = array_reverse($db -> fetchAll($query));

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
}

require_once('../includes/header.php');
?>
        <script>
        var chartType = 'area';
        var legend = {enabled: 1};
        var plotOptions = {
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
                    return this.x+'<br>'+
                    this.series.name+' <strong>'+Highcharts.numberFormat(this.percentage, 1) +'%</strong>'
            }
        }
        var yAxisData = [
            {
                title: {text: '% Time in Each Stage'}
              }];
        var xAxisOptions = [
            {
                title: {text: 'Stages'}
            }];
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
$i = 1;
foreach(end($data) as $stage => $val) {
?>
            {
                name: <?php echo "'" . $stage . "'"; ?>,
                color: '<?php echo $statusIndex[$stage]['color'] ?>',
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
                    if($_GET['z'] != 'main') {
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
    unset($params['z']);
}
?>
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
                        <input type="hidden" name="z" value="<?php echo ($_GET['z']=='rsm')?'rsm':'main'; ?>">
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
                        href="../performance/<?php
                            echo buildURLparameters($params);
                        ?>"
                        style="margin-top: 6px;">
                        Performance
                    </a>
                    <a
                        class="btn btn-mini span2"
                        href="../performance/COP/<?php echo buildURLparameters($params); ?>"
                        style="margin-top: 6px;">
                        COP
                    </a>
                    <a
                        class="btn btn-mini span2"
                        href="../performance/full_graph/<?php echo buildURLparameters($params); ?>"
                        style="margin-top: 6px;">
                        Full Graph
                    </a>
                </div>
            </div>

<?php
require_once('../includes/footer.php');
?>
