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
function echoJSarray($array, $wrapper=''){

    $i=1;
    foreach($array as $val) {
        echo $wrapper . $val/100 . $wrapper;
        if($i < count($array)) {echo ', ';}
        $i++;
    }
}

checkSystemSet($config);

$db = new db($config);
$query = 'SELECT SourceHeader.Recnum,
                SourceHeader.DateStamp,         SourceHeader.TimeStamp,
                SourceData0.Senchan01,          SourceData0.Senchan02,
                SourceData0.Senchan03,          SourceData0.Senchan04,
                SourceData0.Senchan05,          SourceData0.Senchan06,
                                                SourceData0.Senchan08
          FROM SourceHeader, SourceData0
          WHERE SourceHeader.Recnum = SourceData0.HeadID
            AND SourceHeader.SysID = :SysID
          ORDER BY SourceHeader.DateStamp DESC,
                   SourceHeader.TimeStamp DESC
          LIMIT 0,120';
$bind[':SysID'] = $_SESSION['SysID'];

// array_reverse() because the most recent data belongs at the end of the graph
$result = array_reverse( $db -> fetchAll($query, $bind) );

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
            var categories = [<?php echoJSarray($Stamp, "'") ?>];
            var recnums = [<?php echoJSarray($Recnum); ?>]
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
                    data: [<?php echoJSarray(eval('return $'. $key . ';')); ?>]
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
