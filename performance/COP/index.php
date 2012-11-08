<?php
/**
 *------------------------------------------------------------------------------
 * COP Index Page
 *------------------------------------------------------------------------------
 *
 *
 */

require_once('../../includes/pageStart.php');

if(count($_POST) > 0) {
    /**
     * If the date/time form is submitted it builds a url based on the values sent.
     */
    if(isset($_POST['date']) && $_POST['date'] != ''
    && isset($_POST['time']) && $_POST['time'] != '' ) {
        if(substr($_POST['time'], -2, 2) == "PM") {
            $hour = intval(substr($_POST['time'], 0, 2)) + 12;
        }else{
            $hour = intval(substr($_POST['time'], 0, 2));
        }
        $minute = substr($_POST['time'], 3, 2);
        $seconds = '00';

        $location = './?date=' . $_POST['date'] . '&time=' . $hour . ':' . $minute . ':' . $seconds;
        if(isset($range) && $range > 0) {
            $location .= '&range=' . $range;
        }
    }
    if(isset($_POST['range']) && $_POST['range'] != '') {
        /**
         * The time range to be displayed is also added to the url. Users may
         * select a date/time/range, a date/time, or just a range.
         */
        if(isset($location)) {
            $location .= '&';
        }else{
            $location = './?';
        }
        $location .= 'range=' . $_POST['range'];
    }
    /**
     * The page redirects to the built url and loads this file for a second
     * time. This avoids POST issues when refreshign the page.
     */
    header('Location: ' . $location);
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

// TODO(Geoff Young): use prepared statement
    $query = "SELECT
    SourceHeader.Recnum,       SensorCalc.DateStamp,
    SensorCalc.TimeStamp,    SensorCalc.CalcResult4,
    SensorCalc.CalcResult5,    SensorCalc.CalcResult6,
    SensorCalc.CalcResult7,
    SourceData0.DigIn01,       SourceData0.DigIn02,
    SourceData0.DigIn03,       SourceData0.DigIn04,
    SourceData0.DigIn05
    FROM SourceHeader, SourceData0, SensorCalc
    WHERE SensorCalc.CalcResult5 != 'NULL'
    AND SensorCalc.CalcResult4 != 'NULL'
    AND SourceHeader.SourceID = 0
    AND SensorCalc.CalcGroup = ";
    if(isset($_GET['group'])){$query .= $_GET['group'];}else{$query .= '2';}
if(isset($_GET['date']) && isset($_GET['time'])) {
    $query .= "
    AND SourceHeader.DateStamp =  '" . $date . "'
    AND SourceHeader.TimeStamp <=  '" . $time . "'
    AND SourceHeader.Recnum = SourceData0.HeadID
    AND SourceHeader.Recnum = SensorCalc.HeadID
    AND SourceHeader.SysID = " . $_SESSION['SysID'] . "
    OR SourceHeader.DateStamp <  '" . $date . "'
    AND SourceHeader.Recnum = SourceData0.HeadID
    AND SourceHeader.Recnum = SensorCalc.HeadID
    AND SourceHeader.SysID = " . $_SESSION['SysID'] . "
    ";
}else{
    $query .= "
    AND SourceHeader.Recnum = SensorCalc.HeadID
    AND SourceHeader.Recnum = SourceData0.HeadID
    AND SourceHeader.SysID = " . $_SESSION['SysID'] . "
    ";
}
$query .= "
    ORDER BY SourceHeader.DateStamp DESC , SourceHeader.TimeStamp DESC
    LIMIT 0 , ";
if(isset($_GET['range']) && withinRange($_GET['range'], 0, 7)) {
    $query .= intval($_GET['range'])*120;
}else{
    $query .= '480';
}

/**
 * The query orders by date and time descending so that it will get date going
 * backwards from the specified time. Now that it's selected array_reverse() is
 * used to correct the order for the graph.
 */
$result = array_reverse( $db -> fetchAll($query) );

// TODO(Geoff Young): divide only the sensors by 100
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

// TODO(Geoff Young): Get this from the database instead
$systemMap = array(
    'Senchan01' => 'Water In',
    'Senchan02' => 'Water In 2',
    'Senchan03' => 'Water Out',
    'Senchan04' => 'Water out 2',
    'Senchan05' => 'Air In',
    'Senchan06' => 'Air Out',
    'Senchan07' => 'Outside',
    'Senchan08' => 'Mech RT(Aux 1)',
    'FlowPress01' => 'Flow',
    'FlowPress02' => 'Pressure',
    'FlowPress03' => 'Flow',
    'FlowPress04' => 'Flow (RSM)',
    'CalcResult4' => 'COP HP',
    'CalcResult5' => 'COP total',
    'CalcResult6' => 'COP Rolling Avg. HP',
    'CalcResult7' => 'COP Rolling Avg total'
);
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
                title: {text: ''}
              }
              ];
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
<?php if($key == 'FlowPress01'){ ?>
                    color: '#aaa',
                    yAxis: 1,
                    zIndex: 1,
<?php }else{ ?>
                    zIndex: 10,
<?php } ?>
                    data: [<?php echoJSarray(eval('return $'. $key . ';'), null); ?>]
                },
<?php
}
?>
            ];
            </script>

        <div class="row">
            <h1 class="span7 offset2">COP - <span class="building-name"><?php echo $buildingName; ?></span></h1>
            <div class="span2">
<?php
if(isset($_GET['date']) && isset($_GET['time'])) {
?>
                <a href="./" class="btn btn-mini">
                    Current Data
                    <i class="icon-arrow-right"></i>
                </a>
<?php
}
?>
                <br>
                <a href="../full_graph<?php
                if(isset($_GET['date'])) {
                    echo '?date=' . $_GET['date']
                       . '&time=' . $_GET['time'];
                }
                if(isset($_GET['range'])) {
                    echo (isset($_GET['date']))?'&':'?';
                    echo 'range=' . intval($_GET['range']);
                }
                    ?>" class="btn btn-mini span2" style="margin-top: 6px;">Full Graph</a>
            </div>
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
                                </select>
                            </label>
                        </div>
                        <br>
                        <input class="btn btn-info btn-large btn-block" type="submit" value="Submit">
                    </form>
                </div>
            </div>

<?php
require_once('../../includes/footer.php');
?>
