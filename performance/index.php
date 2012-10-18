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
        echo $wrapper . $val . $wrapper;
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
                SourceData0.Senchan07,          SourceData0.Senchan08,
                SourceData0.FlowPress02,        SourceData0.FlowPress03,
                SourceData0.AngMux01,           SourceData0.AngMux02,
                SourceData0.AngMux03,           SourceData0.AngMux04,
                SourceData0.AngMux05,           SourceData0.AngMux06,
                SourceData0.AngMux07,           SourceData0.AngMux08

          FROM SourceHeader, SourceData0

          WHERE SourceHeader.Recnum = SourceData0.HeadID
            AND SourceHeader.SysID = :SysID
          ORDER BY SourceHeader.DateStamp DESC,
                   SourceHeader.TimeStamp DESC
          LIMIT 0,120';
$bind[':SysID'] = $_SESSION['SysID'];

// array_reverse() because the most recent data belongs at the end of the graph
$result = array_reverse( $db -> fetchAll($query, $bind) );

foreach($result as $val) {
    $Stamp[$val['Recnum']]   = $val['DateStamp'] . '<br>' . $val['TimeStamp'];
    $Recnum[$val['Recnum']]   = $val['Recnum'];
    $Senchan01[$val['Recnum']]   = $val['Senchan01']/100;
    $Senchan02[$val['Recnum']]   = $val['Senchan02']/100;
    $Senchan03[$val['Recnum']]   = $val['Senchan03']/100;
    $Senchan04[$val['Recnum']]   = $val['Senchan04']/100;
    $Senchan05[$val['Recnum']]   = $val['Senchan05']/100;
    $Senchan06[$val['Recnum']]   = $val['Senchan06']/100;
    $Senchan07[$val['Recnum']]   = $val['Senchan07']/100;
    $Senchan08[$val['Recnum']]   = $val['Senchan08']/100;
    $FlowPress02[$val['Recnum']] = $val['FlowPress02']/100;
    $FlowPress03[$val['Recnum']] = $val['FlowPress03']/100;
    $AngMux01[$val['Recnum']]    = $val['AngMux01']/100;
    $AngMux02[$val['Recnum']]    = $val['AngMux02']/100;
    $AngMux03[$val['Recnum']]    = $val['AngMux03']/100;
    $AngMux04[$val['Recnum']]    = $val['AngMux04']/100;
    $AngMux05[$val['Recnum']]    = $val['AngMux05']/100;
    $AngMux06[$val['Recnum']]    = $val['AngMux06']/100;
    $AngMux07[$val['Recnum']]    = $val['AngMux07']/100;
    $AngMux08[$val['Recnum']]    = $val['AndMux08']/100;
}

// pprint($Senchan01, 'Senchan01');
// pprint($Senchan02, 'Senchan02');
// pprint($Senchan03, 'Senchan03');
// pprint($Senchan04, 'Senchan04');
// pprint($Senchan05, 'Senchan05');
// pprint($Senchan06, 'Senchan06');
// pprint($Senchan07, 'Senchan07');
// pprint($Senchan08, 'Senchan08');
// pprint($FlowPress02, 'FlowPress02');
// pprint($FlowPress03, 'FlowPress03');
// pprint($AngMux01, 'AngMux01');
// pprint($AngMux02, 'AngMux02');
// pprint($AngMux03, 'AngMux03');
// pprint($AngMux04, 'AngMux04');
// pprint($AngMux05, 'AngMux05');
// pprint($AngMux06, 'AngMux06');
// pprint($AngMux07, 'AngMux07');
// pprint($AngMux08, 'AngMux08');

require_once('../includes/header.php');
?>
            <script type="text/javascript">
            var data = [
                {
                    name: "<?php echo printVarName($Senchan01);?>",
                    data: [<?php echoJSarray($Senchan01);?>]
                },
                {
                    name: "<?php echo printVarName($Senchan02);?>",
                    data: [<?php echoJSarray($Senchan02);?>]
                },
                {
                    name: "<?php echo printVarName($Senchan03);?>",
                    data: [<?php echoJSarray($Senchan03);?>]
                },
                {
                    name: "<?php echo printVarName($Senchan04);?>",
                    data: [<?php echoJSarray($Senchan04);?>]
                },
                {
                    name: "<?php echo printVarName($Senchan05);?>",
                    data: [<?php echoJSarray($Senchan05);?>]
                },
                {
                    name: "<?php echo printVarName($Senchan06);?>",
                    data: [<?php echoJSarray($Senchan06);?>]
                },
                {
                    name: "<?php echo printVarName($Senchan07);?>",
                    data: [<?php echoJSarray($Senchan07);?>]
                },
                {
                    name: "<?php echo printVarName($Senchan08);?>",
                    data: [<?php echoJSarray($Senchan08);?>]
                },
                {
                    name: "<?php echo printVarName($FlowPress02);?>",
                    data: [<?php echoJSarray($FlowPress02);?>]
                },
                {
                    name: "<?php echo printVarName($FlowPress03);?>",
                    data: [<?php echoJSarray($FlowPress03);?>]
                },
                {
                    name: "<?php echo printVarName($AngMux01);?>",
                    data: [<?php echoJSarray($AngMux01);?>]
                },
                {
                    name: "<?php echo printVarName($AngMux02);?>",
                    data: [<?php echoJSarray($AngMux02);?>]
                },
                {
                    name: "<?php echo printVarName($AngMux03);?>",
                    data: [<?php echoJSarray($AngMux03);?>]
                },
                {
                    name: "<?php echo printVarName($AngMux04);?>",
                    data: [<?php echoJSarray($AngMux04);?>]
                },
                {
                    name: "<?php echo printVarName($AngMux05);?>",
                    data: [<?php echoJSarray($AngMux05);?>]
                },
                {
                    name: "<?php echo printVarName($AngMux06);?>",
                    data: [<?php echoJSarray($AngMux06);?>]
                },
                {
                    name: "<?php echo printVarName($AngMux07);?>",
                    data: [<?php echoJSarray($AngMux07);?>]
                },
                {
                    name: "<?php echo printVarName($AngMux08);?>",
                    data: [<?php echoJSarray($AngMux08);?>]
                }
            ];
            var categories = [<?php echoJSarray($Stamp, "'") ?>];
            var recnums = [<?php echoJSarray($Recnum); ?>]

            </script>

        <div class="row">
            <h1 class="span8 offset2">Performance</h1><?php echo $db -> numRows($query, $bind) . ' results'; ?>
        </div>


            <div id="chart" class="chart-container data" style="min-width: 400px; height: 600px; margin: 0 auto"></div>

<?php
require_once('../includes/footer.php');
?>
