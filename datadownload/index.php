<?php
/**
 *------------------------------------------------------------------------------
 * Data Download page
 *------------------------------------------------------------------------------
 * Extracts Data from MySql Data table based on date range and sends data to excel
 * Choose Tables to include in excel
 * each table on Separate tab
 * SourceHeader
 * SourceData0
 * SourceData1
 * SourceData4
 * SensorCalc
 *
 * Accessible by System Admin or Building Manager authorization only
 *------------------------------------------------------------------------------
 * Extracts Data from MySql Data table based on date range and sends data to excel
 * Choose Tables to include in excel
 * each table on Separate tab
 * SourceHeader
 * SourceData0
 * SourceData1
 * SourceData4
 * SensorCalc
 */

/**
 * Silly Functions
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
function sourceName($SourceID)
{
    switch ($SourceID) {
        case '0':
            $name = 'DAM';
            break;
        case '4':
            $name = 'Modbus';
            break;
        case '99':
            $name = 'Sensor Calculations';
            break;
        default:
            $name = 'RSM ';
            if($SourceID > 4) {
                $SourceID--;
            }
            $name .= $SourceID;
            break;
    }
    return $name;
}



require_once('../includes/pageStart.php');

$db = new db($config);

checkSystemSet($config);

$SysID = $_SESSION['SysID'];

////////// Handle POST data ////////////////////////////////////////////////////
if(count($_POST) > 0) {
    // Keep an eye on the tables that are being queried
    $tablesUsed = array('SourceHeader');

    $headings = array('Record ID', 'Date', 'Time');

    // Get the dates and get the out of the POST array
    $from = $_POST['from'];
    $until = $_POST['until'];
    unset($_POST['from']);
    unset($_POST['until']);

    if(count($_POST) == 0) {
        header('Location: ./?a=e');
    }

    $query = "
SELECT DISTINCT
    SourceHeader.Recnum,
    SourceHeader.DateStamp,
    SourceHeader.TimeStamp,
    ";
    $i = 1;
    foreach($_POST as $k => $v) { // Select all the fields from the POST
        $query .= str_replace('_', '.', $k);
        if($i < count($_POST)) { // Add commas, except at the end
            $query .= ",
    ";
        }
        $i++;
        // While we're looping over these check which tables are used and keep track of them
        if(!in_array( preg_replace('/_.*/', '', $k), $tablesUsed )) {
            array_push($tablesUsed, preg_replace('/_.*/', '', $k));
        }
        array_push($headings, $v);
    }
    $query .= "
FROM
    ";
    $i = 1;
    foreach ($tablesUsed as $table) { // List the tables we're selecting from
        $query .= $table;
        if($i < count($tablesUsed)) { // Add commas, except at the end
            $query .= ", ";
        }
        $i++;
    }
    $query .= "
WHERE SourceHeader.SysID = $SysID";
    array_shift($tablesUsed);
    foreach ($tablesUsed as $table) { // Join all the tables being used to SourceHeader
        $query .= "
  AND SourceHeader.Recnum = " . $table . ".HeadID";
    }
    // Only get records in the date range
    $query .= "
  AND SourceHeader.DateStamp >= '$from'
  AND SourceHeader.DateStamp <= '$until'
ORDER BY DateStamp ASC, TimeStamp ASC";

    header("Content-type: text/csv");
    header("Cache-Control: no-store, no-cache");
    header('Content-Disposition: attachment; filename="Download.csv"');

    $outstream = fopen("php://output",'w');

    try{
        $results = $db->fetchAll($query);
    }catch(Exception $e) {
        header('Location: ./?a=e');
    }
    array_unshift($results, $headings);

    foreach($results as $row) {
        fputcsv($outstream, $row, ',', '"');
    }

    fclose($outstream);
    die();

////////// End of POST hondling ////////////////////////////////////////////////
}

$savedSetsQuery = 'SELECT * FROM SavedDownloads WHERE UserID = :UserID and SysID = :SysID';
$savedSetsBind[':UserID'] = intval($_SESSION['userID']);
$savedSetsBind[':SysID'] = intval($_SESSION['SysID']);
$savedSets = $db->fetchAll($savedSetsQuery, $savedSetsBind);

require_once('../includes/header.php');

$buildingNames = $db -> fetchRow('SELECT SysName FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$buildingName = $buildingNames['SysName'];

$numRSM = $db -> fetchRow('SELECT NumofRSM FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$numRSM = $numRSM['NumofRSM'];

?>
        <h1 class="span10 offset1">
            Data Download -
            <span class="building-name">
                <?php
                    echo $buildingName;
                ?>
            </span>
        </h1>
        <form action="./" method="POST">
            <div class="row">
                <div class="span4 offset2">
                    <label class="span3">
                        <h4 class="pull-left">From</h4>
                        <input
                            class="datepick text span3 "
                            type="text"
                            name="from"
                            value="<?php echo date('Y-m-d', strtotime('-1 day')); ?>">
                    </label>
                </div>

                <div class="span4">
                    <label class="span3">
                        <h4 class="pull-left">Until</h4>
                        <input
                            class="datepick text span3 "
                            type="text"
                            name="until"
                            value="<?php echo date('Y-m-d'); ?>">
                    </label>
                </div>
            </div>

            <br>

            <div class="row">
                <div class="span10">

<?php
for ($i=0; $i < 100; $i++) {
if($i == 0 || $i == 4 || $i == 99 || $i <= $numRSM) {

    /* Get the default table.column values for the values to be graphed */
    $query = "
    SELECT
        SysMap.SourceID,
        SysMap.SysID,
        SysMap.SensorColName,
        SysMap.SensorName,
        SysMap.SensorRefName,
        WebRefTable.SensorLabel,
        WebRefTable.WebSubPageName
    FROM SysMap, WebRefTable
    WHERE SysMap.WebSensRefNum = WebRefTable.WebSensRefNum
      AND SysMap.SourceID = $i
      AND WebRefTable.Inhibit = 0
    ";

    /* Put all the defaults in an associative array */
    $sensors = array();
    foreach($db->fetchAll($query . "AND DAMID = '000000000000'") as $sensor) {
        $sensors[$sensor['SourceID'] . $sensor['SensorRefName']] = $sensor;
    }

    /* Get the custom table.column values specific to the current SysID */
    /* Override the default table.column value if there is a custom value to replace it */
    foreach($db->fetchAll($query . "AND SysMap.SysID = " . $SysID) as $k => $v) {
        $sensors[$v['SourceID'] . $v['SensorRefName']] = $v;
    }

    if(count($sensors) > 0) {
?>
            <div class="row">
                <h3 class="span8 offset2"><?php echo sourceName($i); ?></h3>
<?php
        foreach($sensors as $sensor) {
            $table = pickTable($sensor['SourceID']);
?>
                <label class="span2" style="margin-bottom: 10px;">
                    <input
                        type="checkbox"
                        name="<?php echo $table.'_'.$sensor['SensorColName']; ?>"
                        value="<?php echo $sensor['SensorName']; ?>"
                    >
                    <?php
                    echo $sensor['SensorName'];
                    if($sensor['SysID'] > 0) {
                        echo '-'.$sensor['WebSubPageName'];
                    }
                    ?>

                </label>
<?php

        }
?>
            </div>
            <br>
<?php
    }
}
}

?>
                </div>
                <div class="span2">
                    <div class="row">

                        <br>

                        <div class="btn-group pull-right span2">
                            <button class="check-all btn btn-mini"><i class="icon-ok-circle"></i> Check All</button>
                            <button class="uncheck-all btn btn-mini"><i class="icon-remove-circle"></i> Uncheck All</button>
                        </div>

                        <br><br>

                        <h4 class="span2">Saved Downloads</h4>
                        <div class="saved-set-list span2">
<?php
foreach($savedSets as $key => $set) {
?>
                        <div class="span2">
                            <a class="delete-saved-set close" href="delete.php?id=<?php echo $set['Recnum'] ?>">&times;</a>
                            <a class="saved-set" href="#<?php echo $key; ?>"><?php echo $set['Name']; ?></a>
                        </div>
<?php
}
?>
                        </div>
                        <div class="clearfix">
                            <br>
                            <button class="save-download-set btn btn-mini span2">Save Current Selection</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <button class="btn btn-large btn-info span6 offset3">
                    Download
                </button>
            </div>
        </form>
        <script>
            var SavedSets =
            [<?php
            $i = 1;
            foreach($savedSets as $set){
            echo '[' . $set['Fields'] .']';
            if($i < count($savedSets)){echo',';}
            $i++;
            }
            ?>]
        </script>
<?php
require_once('../includes/footer.php');
?>
