<?php
/**
 *------------------------------------------------------------------------------
 * Performance Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');

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

// TODO(Geoff Young): use prepared statement
    $query = "SELECT
     SourceHeader.Recnum,       SourceHeader.DateStamp,
     SourceHeader.TimeStamp,    SourceData0.Senchan01,
     SourceData0.Senchan02,     SourceData0.Senchan03,
     SourceData0.Senchan04,     SourceData0.Senchan05,
     SourceData0.Senchan06,     SourceData0.Senchan07,
     SourceData0.FlowPress01,   SourceData0.FlowPress02
    FROM SourceHeader, SourceData0";
if(isset($_GET['date']) && isset($_GET['time'])) {
    $query .= "
    WHERE SourceHeader.DateStamp =  '" . $date . "'
    AND SourceHeader.TimeStamp <=  '" . $time . "'
    AND SourceHeader.Recnum = SourceData0.HeadID
    AND SourceHeader.SysID = 1
    OR SourceHeader.DateStamp <  '" . $date . "'
    AND SourceHeader.Recnum = SourceData0.HeadID
    AND SourceHeader.SysID = 1
    ";
}else{
    $query .= "
    WHERE SourceHeader.Recnum = SourceData0.HeadID
    AND SourceHeader.SysID = 1
    ";
}
$query .= "ORDER BY SourceHeader.DateStamp DESC , SourceHeader.TimeStamp DESC
    LIMIT 0 , 500";

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
                    name: "<?php echo $key; ?>",
                    data: [<?php echoJSarray(eval('return $'. $key . ';'), null, 100); ?>]
                },
<?php
}
?>
            ];

            </script>

        <div class="row">
            <h1 class="span8 offset2">Performance</h1>
        </div>


            <div id="chart" class="chart-container data" style="min-width: 400px; min-height: 500px; margin: 0 auto"></div>

<?php
require_once('../includes/footer.php');
?>
