<?php
/**
 *------------------------------------------------------------------------------
 * Nightly Downloads Script
 *------------------------------------------------------------------------------
 *
 */

if(php_sapi_name() !== "cli") {
    die(header("HTTP/1.0 404 Not Found"));
}

ini_set('max_execution_time', 0);

function termout($msg, $color='cyan')
{
	if(php_sapi_name() == "cli") {
		$colors = array(
			'red'     =>"\033[0;31m",
			'green'   =>"\033[0;32m",
			'yellow'  =>"\033[0;33m",
			'blue'    =>"\033[0;34m",
			'magenta' =>"\033[0;35m",
			'cyan'    =>"\033[0;36m",
		);
		echo $colors[$color].$msg."\033[0m\n";
	}else{
		echo "<p style='font-family:monospace;white-space:pre;'>$msg</p>";
	}
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
function makeName($arr)
{
    $name = $arr['SourceID'].'_'.$arr['SensorColName'];
    if($arr['SensorAddress'] != '' && $arr['SensorAddress'] != 'NA') {
        $name .= '_' . $arr['SensorAddress'];
    }
    return $name;
}


require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../general/util.php');

date_default_timezone_set($config['time_zone']);

$db = new db($config);

/**
 * -12 to make sure it's yesterday. This should run at 12:01 AM, but just in case.
 */
$dump_date = date('Y-m-d', strtotime('-12 hours'));

$systems = $db->fetchAll('select SysID from SystemConfig where NightlyReports = 1');

foreach($systems as $result_key => $result_value) {
    $SysID = $result_value['SysID'];

    $buildingNames = $db -> fetchRow('SELECT SysName FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $SysID));
    $buildingName = $buildingNames['SysName'];

    $numRSM = $db -> fetchRow('SELECT NumofRSM FROM SystemConfig WHERE SysID = :SysID', array(':SysID' => $SysID));
    $numRSM = $numRSM['NumofRSM'];


    termout("Outputting report for System #$SysID ($buildingName) ...");

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

if($numRSM < 1) {
    foreach($sensors as $key => $sensor) {
        if($sensor['SourceID'] == 1) {
            unset($sensors[$key]);
        }
    }
}

/*//////////////////////////////*/
// die();
/*//////////////////////////////*/

$sensor_names = array();
foreach($sensors as $sensor) {
    array_push($sensor_names, makeName($sensor));
}
////////////////////////////////////////////////////////////////////////////////
/* Output Reports */
if(count($sensor_names) > 0) {
    /* Set the date range being downloaded */
    $from = $dump_date;
    $until = $dump_date;

    /* I need to declare these arrays before adding to them or PHP will yell at me  ;_;  */
    $tablesUsed = array();
    $cols = array();
    $addrUsed = array();
    /**
     * Now we can add the distinct tables and table.col locations to their apropriate arrays
     * They'll be formatted as table_col_address, so we can split the on the _ character
     */
    foreach($sensors as $key => $val) {
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

    try{

    $storage_dir = "../storage/$SysID";
    if(!is_dir($storage_dir)) {
        mkdir($storage_dir);
    }
    $outfile = $storage_dir . "/$dump_date.csv";
    $file = fopen($outfile, 'w');

    $titles = array('Date', 'Time');
    foreach($sensor_names as $k => $v) {
        $title = $sensors[$v]['SensorName'] . '.' . $sensors[$v]['SourceID'];
        if(preg_match('/Power0[0-9]{1}/', $v)) {
            $title .= ' Addr. ' . $sensors[$v]['SensorAddress'];
        }elseif(preg_match('/.*(ThermStat[0-9]{2}|ThermMode|BS[0-9]{2}|LCDTemp|HeatingSetPoint|CoolingSetPoint).*/', $v)) {
            $title .= ' Addr. ' . $sensors[$v]['SensorAddress'];
        }
        array_push($titles, $title);
    }
    fputcsv($file, $titles, ',', '"');

    foreach($data as $d) {
        $row = array($d['Date'], $d['Time']);
        foreach($sensor_names as $k => $v) {
            array_push($row, $d[$v]);
        }
        fputcsv($file, $row, ',', '"');
    }
    fclose($file);
    termout("OK\n", 'green');

    }catch(Exception $e){
        termout('ERROR', 'red');
        print_r($e);
    }

}

/**
 * Take a break if the server is working on something.
 */
$sleeping = 1;
while($sleeping == 1) {
    sleep(1);
    $jobs = $db->fetchAll('SHOW FULL PROCESSLIST');
    if(count($jobs) < 3) {
        $sleeping = 0;
    }else{
        termout("Waiting for ".count($jobs)." queries.\n", "yellow");
    }
}

} // End of for loop
termout('DONE');
?>