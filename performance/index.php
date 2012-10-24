<?php
/**
 *------------------------------------------------------------------------------
 * Performance Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');

if(isset($_POST['date']) && isset($_POST['time'])) {
    if(substr($_POST['time'], -2, 2) == "PM") {
        $hour = intval(substr($_POST['time'], 0, 2))+12;
    }else{
        $hour = intval(substr($_POST['time'], 0, 2));
    }
    $minute = substr($_POST['time'], 3, 2);
    $seconds = '00';
    header('Location: ./?date=' . $_POST['date'] . '&time=' . $hour . ':' . $minute . ':' . $seconds);
}

/**
 * Returns the name of a variable as a string.
 * e.g. printVarName($foo); returns "foo"
 * @param  any    $var Any variable
 * @return string      The name of the variable passed
 */
function printVarName($var) {
    foreach($GLOBALS as $var_name => $value) {
        if ($value === $var) {
            return $var_name;
        }
    }
    return false;
}

/**
 * echoes out the values of an array seperated by commas, with no comma after
 * the last value
 * @param  array  $array Values to e outputted
 * @param  string $wrapper An element to put before and after the value, that
 * defaults to nothing. It could be a quote character if one is needed.
 * e.g. outputting strings.
 * @return null
 */
function echoJSarray($array, $wrapper='', $divisor=1){

    $i=1;
    foreach($array as $val) {
        echo $wrapper;
        if($divisor != 1){echo $val/$divisor;}else{echo $val;}
        echo $wrapper;
        if($i < count($array)) {echo ', ';}
        $i++;
    }
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
     SourceHeader.Recnum,       SourceHeader.DateStamp,
     SourceHeader.TimeStamp,    SourceData0.Senchan01,
     SourceData0.Senchan03,
     SourceData0.Senchan05,
     SourceData0.Senchan06,     SourceData0.Senchan07,
     SourceData0.FlowPress01,   SourceData0.FlowPress02
    FROM SourceHeader, SourceData0";
if(isset($_GET['date']) && isset($_GET['time'])) {
    $query .= "
    WHERE SourceHeader.DateStamp =  '" . $date . "'
    AND SourceHeader.TimeStamp <=  '" . $time . "'
    AND SourceHeader.Recnum = SourceData0.HeadID
    AND SourceHeader.SysID = " . $_SESSION['SysID'] . "
    OR SourceHeader.DateStamp <  '" . $date . "'
    AND SourceHeader.Recnum = SourceData0.HeadID
    AND SourceHeader.SysID = " . $_SESSION['SysID'] . "
    ";
}else{
    $query .= "
    WHERE SourceHeader.Recnum = SourceData0.HeadID
    AND SourceHeader.SysID = " . $_SESSION['SysID'] . "
    ";
}
$query .= "ORDER BY SourceHeader.DateStamp DESC , SourceHeader.TimeStamp DESC
    LIMIT 0 , 480";

// array_reverse() because the most recent data belongs at the end of the graph
$result = array_reverse( $db -> fetchAll($query, $bind) );

// TODO(Geoff Young): divide only the sensors by 100
foreach($result as $resultRow) {
    foreach($resultRow as $key => $val) {
        $vals[$key][$resultRow['Recnum']] = $val;
    }
}
extract($vals);

// Get a list of date/time stamps for chart labels
foreach($result as $val) {
    $Stamp[$val['Recnum']]   = $val['DateStamp'] . '<br>' . $val['TimeStamp'];
}

// TODO(Geoff Young): Get this from the database instead
$systemMap = array(
    'Senchan01' => 'Water In 1',
    'Senchan02' => 'Water In 2',
    'Senchan03' => 'Water Out 1',
    'Senchan04' => 'Water out 2',
    'Senchan05' => 'Air In',
    'Senchan06' => 'Air Out',
    'Senchan07' => 'Outside',
    'Senchan08' => 'Mech RT(Aux 1)',
    'FlowPress01' => 'Flow',
    'FlowPress02' => 'Pressure',
    'FlowPress03' => 'Flow',
    'FlowPress04' => 'Flow (RSM)'
);

require_once('../includes/header.php');
?>
            <script type="text/javascript">
            var recnums = [<?php echoJSarray($Recnum); ?>]
            var categories = [<?php echoJSarray($Stamp, "'") ?>];
            var data = [
<?php

// Remove some undesirables
for ($i=0; $i < count($result); $i++) {
    unset($result[$i][Recnum]);
    unset($result[$i][DateStamp]);
    unset($result[$i][TimeStamp]);
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
                    data: [<?php echoJSarray(eval('return $'. $key . ';'), null, 100); ?>]
                },
<?php
}
?>
            ];
xPlotBands = [];
            </script>

        <div class="row">
            <h1 class="span8 offset2">Performance - <span class="building-name"><?php echo $buildingName; ?></span></h1>
            <div class="span2">
                <span class="align-left">
<?php
$numRows = $db -> numRows($query)/2;
if($numRows > 60) {
    echo floor($numRows/60) . ' Hour';
    if(floor($numRows/60) > 1) {echo 's';}
}else{
    echo $numRows . ' Minutes';
}
if(isset($_GET['date']) && isset($_GET['time'])) {
?>
                </span>
                <br>
                <a href="./" class="btn btn-mini">
                    Current Data
                    <i class="icon-arrow-right"></i>
                </a>
<?php
}
?>
            </div>
        </div>


            <div id="chart" class="chart-container data" style="min-width: 400px; min-height: 500px; margin: 0 auto"></div>

            <br>
            <div class="row">
                <h5 class="span12 align-center">Date/Time Filter</h5>
            </div>
            <div class="row">
                <div class="span6 offset3">
                    <form class="form-inline" action="./" method="POST">
                        <div class="row">
                            <label class="span3" for="date">Date &nbsp;
                                <input
                                    id="date"
                                    class="datepick span3"
                                    type="text"
                                    name="date"
                                    value="<?php if(isset($_GET['date'])){echo $_GET['date'];} ?>">
                            </label>
                            <label class="span3" for="time">Time &nbsp;
                                <input
                                    id="time"
                                    class="timepick span3"
                                    type="text"
                                    name="time"
                                    value="<?php if(isset($_GET['date'])){echo $_GET['time'];} ?>">
                            </label>
                        </div>
                        <br>
                        <input class="btn btn-block" type="submit" value="Submit">
                    </form>
                </div>
            </div>

<?php
require_once('../includes/footer.php');
?>
