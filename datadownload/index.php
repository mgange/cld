<?php
/**
 *------------------------------------------------------------------------------
 * Data Download page
 *------------------------------------------------------------------------------
 *
 */

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
function makeName($arr)
{
    $name = $arr['SourceID'].'_'.$arr['SensorColName'];
    if($arr['SensorAddress'] != '' && $arr['SensorAddress'] != 'NA') {
        $name .= '_' . $arr['SensorAddress'];
    }
    return $name;
}


require_once('../includes/pageStart.php');

$db = new db($config);

checkSystemSet($config);

$SysID = $_SESSION['SysID'];


/* Get all of the saved download selections for the current system */
$savedSetsQuery = 'SELECT * FROM SavedDownloads WHERE UserID = :UserID and SysID = :SysID';
$savedSetsBind[':UserID'] = intval($_SESSION['userID']);
$savedSetsBind[':SysID'] = intval($_SESSION['SysID']);
$savedSets = $db->fetchAll($savedSetsQuery, $savedSetsBind);

/**
 * We need to get the number of thermostats and the number of power meters for
 * the system in SystemConfig. Whichever of those numbers is largest is also the number of SysGroups somehow.
 */
$nsg = $db->fetchRow("SELECT NumOfTherms, NumOfPowers FROM SystemConfig WHERE SysID = " . $SysID);
$numSysGroups = 1;

foreach($nsg as $k => $v) {
    if($v > $numSysGroups) { $numSysGroups = $v; }
}

/**
 * Get the SensorAddresses for the default system, then the system specific
 * ones that override the defaults. So I'll end p with an array in which the
 * keys are SysGroups and the values are arrays of applicable addresses.
 */
$addresses = array();
for($i=1; $i < $numSysGroups; $i++) {
    $addresses[$i] = array();
    $query = "
        SELECT SensorColName, SensorAddress
        FROM SysMap
        WHERE SysID = 0
        AND (
               SensorColName = 'Power01'
            OR SensorColName = 'ThermStat01'
        )
        AND SysGroup = $i";
    foreach($db->fetchAll($query) as $arr) {
        array_push($addresses[$i], $arr['SensorAddress']);
    }
}
/* Now for the addresses specific to $SysID, whatever that may be */
for($i=1; $i < $numSysGroups; $i++) { // in which $i is the SysGroup
    $query = "
        SELECT SensorColName, SensorAddress, SensorActive
        FROM SysMap
        WHERE SysID = $SysID
        AND (
               SensorColName = 'Power01'
            OR SensorColName = 'ThermStat01'
        )
        AND SysGroup = $i";
    foreach($db->fetchAll($query) as $arr) {
        if($arr['SensorActive'] == 0 && in_array($arr['SensorAddress'], $addresses[$i]) ) {
            unset($addresses[$i][ array_search($arr['SensorAddress'], $addresses[$i]) ]);
        }elseif(!in_array($arr['SensorAddress'], $addresses[$i])){
            array_push($addresses[$i], $arr['SensorAddress']);
        }
    }
}


/* So now we're gonna get all the mapped sensors. Yeah, all of them */
$sensors = array();
$query = "
    SELECT
        SysMap.SourceID,                SysMap.SensorColName,SysMap.SysID,
        SysMap.SensorAddress,           SysMap.SensorActive,
        SysMap.SensorRefName,           SysMap.SensorName,
        WebRefTable.SensorLabel
    FROM SysMap, WebRefTable
    WHERE SysMap.WebSensRefNum = WebRefTable.WebSensRefNum
      AND SysMap.SysID = ";
$defaults= $db->FetchAll($query . "0 AND SysMap.SensorActive = 1"); // Using "0" as the SysID indicates default values
$customs = $db->FetchAll($query . $SysID);

/**
 * Put all the default sensors into an array with the keys formatted as
 * table_column_address, where the address is optional(only used if set in the
 * sysmap). The address, if applicable, is padded to two characters.
 */
foreach($defaults as $def) {
    $sensors[makeName($def)] = $def;
}

foreach ($customs as $def) {
    if($def['SensorActive'] == 0 && in_array(makeName($def), $sensors)) {
        unset($sensors[makeName($def)]);
    }elseif($def['SensorActive'] == 1){
        $sensors[makeName($def)] = $def;
    }
}


/*//////////////////////////////*/
// die();
/*//////////////////////////////*/


////////////////////////////////////////////////////////////////////////////////
/* Handle POST requests */
if(count($_POST) > 0) {
    /* Get the date range being downloaded and clean them out of the POST array */
    $from = $_POST['from'];
    $until = $_POST['until'];
    unset($_POST['from']);
    unset($_POST['until']);

    /* I need to declare these arrays before adding to them or PHP will yell at me  ;_;  */
    $tablesUsed = array();
    $cols = array();
    $addrUsed = array();
    /**
     * Now we can add the distinct tables and table.col locations to their apropriate arrays
     * They'll be formatted as table_col_address, so we can split the on the _ character
     */
    foreach($_POST as $key => $val) {
        $place = explode('_', $key);
        if(!in_array($place[0], $tablesUsed)) {
            array_push($tablesUsed, $place[0]);
        }
        if(!in_array($place[0].'.'.$place[1], $cols)) {
            $cols[$key] = pickTable($place[0]).'.'.$place[1];
        }
    }

    /* Start building the query to dump all this data with some identifiers for each record */
    $query = " SELECT
  SourceHeader.Recnum AS RowNumber,
  SourceHeader.DateStamp AS Date,
  SourceHeader.TimeStamp AS Time";

    /* Add each table.column that we parsed out earlier */
    foreach($cols as $key => $col) {
        $query .= ",\n  " . $col . " AS " . preg_replace('/\_([0-9]{1,2}|NA)$/', '', $key);
    }

    /**/
    if(in_array('4', $tablesUsed)) {
        $query .= ",\n  SourceData4.PwrSubAddress,\n  SourceData4.ThermSubAddress";
    }

    /* List the tables we've used */
    $query .= "\n FROM SourceHeader";
    foreach($tablesUsed as $table){
        $query .= ", " . pickTable($table);
    }

    /* Now the conditions I guess */
    $query .= "\n WHERE SourceHeader.SysID = :SysID";
    foreach ($tablesUsed as $table) {
        $query .= "\n   AND SourceHeader.Recnum = " . pickTable($table) . ".HeadID";
    }
    $query .= "\n   AND SourceHeader.DateStamp >= :from";
    $query .= "\n   AND SourceHeader.DateStamp <= :until";

    /* And now to wrap this thing up */
    $query .= "\n ORDER BY SourceHeader.DateStamp ASC, SourceHeader.TimeStamp ASC";

    $bind = array(
        ":from"  => $from,
        ":until" => $until,
        ":SysID" => $SysID
    );

    /**
     * These are the regular expressions that should identify the columns that
     * depend on a PwrSubAddress or a ThermSubAddress.
     */
    $pwrRegex   = '/^[0-9]{1}\_(Power0[0-9]{1})/';
    $thermRegex = '/^[0-9]{1}\_(ThermStat[0-9]{2}|ThermMode|BS[0-9]{2}|LCDTemp|HeatingSetPoint|CoolingSetPoint)/';


    $data = array();
    try{
        $results = $db->fetchAll($query, $bind);
    }catch(Exception $e) {
        // Redirect to the DataDownload page with an error message if something goes wrong
        die(header('Location: ./?a=e'));
    }

foreach($results as $res) {
    if(!isset($data[$res['RowNumber']])) {
        $data[$res['RowNumber']] = array();
    }
    $data[$res['RowNumber']]['Date'] = $res['Date'];
    $data[$res['RowNumber']]['Time'] = $res['Time'];
    foreach($res as $key => $val) {
        if(preg_match($pwrRegex, $key)) {
            $data[$res['RowNumber']][$key.'_'.$res['PwrSubAddress']] = $val;
        }elseif(preg_match($thermRegex, $key)) {
            $data[$res['RowNumber']][$key.'_'.$res['ThermSubAddress']] = $val;
        }else{
            $data[$res['RowNumber']][$key] = $val;
        }
    }
}

    header("Content-type: text/csv");
    header("Cache-Control: no-store, no-cache");
    header('Content-Disposition: attachment; filename="Download.csv"');

    $outstream = fopen("php://output",'w');

    $titles = array('Date', 'Time');
    foreach($_POST as $k => $v) {
        $title = $sensors[$k]['SensorName'] . '.' . $sensors[$k]['SourceID'];
        if(preg_match('/Power0[0-9]{1}/', $k)) {
            $title .= ' Addr. ' . $sensors[$k]['SensorAddress'];
        }elseif(preg_match('/.*(ThermStat[0-9]{2}|ThermMode|BS[0-9]{2}|LCDTemp|HeatingSetPoint|CoolingSetPoint).*/', $k)) {
            $title .= ' Addr. ' . $sensors[$k]['SensorAddress'];
        }
        array_push($titles, $title);
    }
    fputcsv($outstream, $titles, ',', '"');

    foreach($data as $d) {
        $row = array($d['Date'], $d['Time']);
        foreach($_POST as $k => $v) {
            array_push($row, $d[$k]);
        }
        fputcsv($outstream, $row, ',', '"');
    }

/*//////////////////////////////*/
die();
/*//////////////////////////////*/


} // End of POST handling

/* Let's see what building we're looking at */
$buildingNames = $db -> fetchRow('SELECT SysName FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$buildingName = $buildingNames['SysName'];

$numRSM = $db -> fetchRow('SELECT NumofRSM FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $_SESSION['SysID']));
$numRSM = $numRSM['NumofRSM'];

require_once('../includes/header.php');
?>
        <h1 class="span10 offset1">
            Data Download -
            <span class="building-name">
                <?php
                    echo $buildingName;
                ?>
            </span>
        </h1>
        <form class="form-inline" action="./" method="POST">
            <div class="row">
                <div class="span4 offset2">
                    <label class="span3">
                        <h4 class="pull-left">From</h4>
                        <input
                            class="datepick text span3"
                            type="text"
                            name="from"
                            value="<?php echo date('Y-m-d', strtotime('-1 day')); ?>"
                        >
                    </label>
                </div>

                <div class="span4">
                    <label class="span3">
                        <h4 class="pull-left">Until</h4>
                        <input
                            class="datepick text span3"
                            type="text"
                            name="until"
                            value="<?php echo date('Y-m-d'); ?>"
                        >
                    </label>
                </div>
            </div>

            <div class="row">
                <div class="span10">
                    <div class="row">
                        <h3 class="span10">DAM</h3>
<?php
foreach($sensors as $sensor) {
    if($sensor['SourceID'] == 0) { // SourceID of 0 indicates DAM
?>
                        <label class="span2 checkbox" style="margin-bottom: 14px;">
                            <input type="checkbox" name="<?php echo makeName($sensor); ?>">
                            <?=$sensor['SensorName']?>
                        </label>
<?php
    }
}
?>
                    </div>


<?php
for ($i=1; $i <= $numRSM; $i++) {
    $sid = $i;
    if($i >= 4){$sid++;} // Since SourceID of 4 is reserved for the Modbus gateway we'll have to skip over it
?>
                    <div class="row">
                        <h3 class="span10">RSM
<?php
    if($numRSM > 1 && $i > 1) {
        echo ' - ' . $i;
    }
?>
                        </h3>
<?php
    foreach($sensors as $name => $sensor) {
        if($sensor['SourceID'] == $sid) {
?>
                        <label class="span2 checkbox" style="margin-bottom: 14px;">
                            <input type="checkbox" name="<?php echo makeName($sensor); ?>">
                            <?=$sensor['SensorName']?>
                        </label>
<?php
        }
    }
?>
                    </div>
<?php
}
?>

                    <div class="row">
                        <h3 class="span10">Modbus</h3>
<?php
foreach($sensors as $name => $sensor) {
    if($sensor['SourceID'] == 4) { // SourceID of 4 indicates Modbus Gateway
?>
                        <label class="span2 checkbox" style="margin-bottom: 14px;">
                            <input type="checkbox" name="<?php echo makeName($sensor); ?>">
                            <?php
                                echo $sensor['SensorName'];
                                if($sensor['SensorAddress'] != '' && $sensor['SensorAddress'] != 'NA') {
                                    echo " <sup><em> Addr. ".$sensor['SensorAddress']."</em></sup>";
                                }
                            ?>
                        </label>
<?php
    }
}
?>
                    </div>

                    <div class="row">
                        <h3 class="span10">Sensor Calculations</h3>
<?php
foreach($sensors as $name => $sensor) {
    if($sensor['SourceID'] == 99) { // SourceID of 99 indicates SensorCalc
?>
                        <label class="span2 checkbox" style="margin-bottom: 14px;">
                            <input type="checkbox" name="<?php echo makeName($sensor); ?>">
                            <?=$sensor['SensorName']?>
                        </label>
<?php
    }
}
?>
                    </div>

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
