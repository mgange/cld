<?php
/**
 *------------------------------------------------------------------------------
 * Stages Index Page
 *------------------------------------------------------------------------------
 *
 */

require_once('../includes/pageStart.php');


checkSystemSet($config);

$db = new db($config);

$buildingNames = $db -> fetchRow('SELECT SysName FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$buildingName = $buildingNames['SysName'];

$numRSM = $db -> fetchRow('SELECT NumofRSM FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$numRSM = $numRSM['NumofRSM'];

$endTime = strtotime('now');

ini_set('memory_limit','200M');
$startTime = $endTime - (86400*10);

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
        var zoomType = 'xy';
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
    unset($params['z']);
}
?>
            </div>

            <div
                id="chart"
                class="chart-container data"
                style="min-width: 400px; min-height: 500px; margin: 0 auto">
            </div>

<?php
require_once('../includes/footer.php');
?>
