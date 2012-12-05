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
require_once('../includes/pageStart.php');

checkSystemSet($config);

$dataTables = array("SourceData0", "SourceData1", "SourceData4", "SensorCalc");

$db = new db($config);

if(isset($_POST['date'])) {
    if(isset($_POST['source']) && withinRange(intval($_POST['source']), -1, 4)) {
        $source = $dataTables[intval($_POST['source'])];
        $query = "SELECT
        SourceHeader.Recnum AS ID,
        SourceHeader.DateStamp AS Date,
        SourceHeader.TimeStamp AS Time,
        " . $source . ".*
        FROM SourceHeader, " . $source . "
        WHERE SourceHeader.SysID = :SysID
        AND SourceHeader.Recnum = " . $source . ".HeadID
        AND SourceHeader.DateStamp = :date
        ";
        $bind[':SysID'] = $_SESSION['SysID'];
        $bind[':date'] = $_POST['date'];
try{
        $result = $db->fetchAll($query, $bind);
}catch(Exception $e){echo $e->getMessage();}
        header("Content-type: text/csv");
        header("Cache-Control: no-store, no-cache");
        header('Content-Disposition: attachment; filename="Download.csv"');

        $outstream = fopen("php://output",'w');

        foreach($result as $row) {
            fputcsv($outstream, $row, ',', '"');
        }

        fclose($outstream);
        die();
    }elseif (isset($_POST['fileType']) && $_POST['fileType'] == 'xls') {

        require_once('class.excelXML.php');

        $bind[':SysID'] = $_SESSION['SysID'];
        $bind[':date'] = date('Y-m-d', strtotime($_POST['date']));

        ini_set('memory_limit','200M');
        ini_set('max_execution_time','60');

        // Create new object
        $excel = new excel_xml();

        //Define styles for header rows
        $header_style = array(
            'size'       => '12',
            'color'      => '#ffffff',
            'bgcolor'    => '#aaaaff'
        );
        $excel->add_style('header', $header_style);

        // MAIN VALUES
        $query = "SELECT
        SourceHeader.Recnum AS ID,
        SourceHeader.DateStamp AS Date,
        SourceHeader.TimeStamp AS Time,
        SourceData0.*
        FROM SourceHeader, SourceData0
        WHERE SysID = :SysID
        AND SourceHeader.Recnum = SourceData0.HeadID
        AND SourceHeader.DateStamp = :date
        ORDER BY SourceHeader.DateStamp DESC, SourceHeader.TimeStamp DESC
        ";
        $main = $db->fetchAll($query, $bind);
        // Create an empty array for header row values
        $headers = array();

        // Added the key names to the header row
        foreach($main[0] as $key => $val) {
            array_push($headers, $key);
        }
        $excel->add_row($headers, 'header');

        // Write each DB record as a spreadsheet row
        foreach($main as $row) {
            $excel->add_row($row);
        }
        unset($main);

        // Put all those rows in a worksheet
        $excel->create_worksheet('Main');

        // RSM VALUES
        $query = "SELECT
        SourceHeader.Recnum AS ID,
        SourceHeader.DateStamp AS Date,
        SourceHeader.TimeStamp AS Time,
        SourceData1.*
        FROM SourceHeader, SourceData1
        WHERE SysID = :SysID
        AND SourceHeader.Recnum = SourceData1.HeadID
        AND SourceHeader.DateStamp = :date
        ORDER BY SourceHeader.DateStamp DESC, SourceHeader.TimeStamp DESC
        ";
        $rsm = $db->fetchAll($query, $bind);
        // Create an empty array for header row values
        unset($headers);
        $headers = array();

        // Added the key names to the header row
        foreach($rsm[0] as $key => $val) {
            array_push($headers, $key);
        }
        $excel->add_row($headers, 'header');

        // Write each DB record as a spreadsheet row
        foreach($rsm as $row) {
            $excel->add_row($row);
        }
        unset($rsm);
        $excel->create_worksheet('RSM');

        // MODBUS GATEWAY VALUES
        $query = "SELECT
        SourceHeader.Recnum AS ID,
        SourceHeader.DateStamp AS Date,
        SourceHeader.TimeStamp AS Time,
        SourceData4.*
        FROM SourceHeader, SourceData4
        WHERE SysID = :SysID
        AND SourceHeader.Recnum = SourceData4.HeadID
        AND SourceHeader.DateStamp = :date
        ORDER BY SourceHeader.DateStamp DESC, SourceHeader.TimeStamp DESC
        ";
        $modbus = $db->fetchAll($query, $bind);
        // Create an empty array for header row values
        unset($headers);
        $headers = array();

        // Added the key names to the header row
        foreach($modbus[0] as $key => $val) {
            array_push($headers, $key);
        }
        $excel->add_row($headers, 'header');

        // Write each DB record as a spreadsheet row
        foreach($modbus as $row) {
            $excel->add_row($row);
        }
        unset($modbus);
        $excel->create_worksheet('Modbus Gateway');

        // SENSOR CALC VALUES
        $query = "SELECT
        SourceHeader.Recnum AS ID,
        SourceHeader.DateStamp AS Date,
        SourceHeader.TimeStamp AS Time,
        SensorCalc.*
        FROM SourceHeader, SensorCalc
        WHERE SourceHeader.SysID = :SysID
        AND SourceHeader.Recnum = SensorCalc.HeadID
        AND SourceHeader.DateStamp = :date
        ORDER BY SourceHeader.DateStamp DESC, SourceHeader.TimeStamp DESC
        ";
        $calc = $db->fetchAll($query, $bind);
        // Create an empty array for header row values
        unset($headers);
        $headers = array();

        // Added the key names to the header row
        foreach($calc[0] as $key => $val) {
            array_push($headers, $key);
        }
        $excel->add_row($headers, 'header');

        // Write each DB record as a spreadsheet row
        foreach($calc as $row) {
            $excel->add_row($row);
        }
        unset($calc);
        $excel->create_worksheet('Sensor Calc');


        $xml = $excel->generate();
        $excel->download('Download.xls');

    }
}

require_once('../includes/header.php');

$date = date("Y-m-d", time() - 60 * 60 * 24);
?>

        <div class="row">
            <h1 class="span8 offset2">Data Download</h1>
        </div>

        <form class="validate" action="./" method="POST">
            <div class="row">
                <div class="span4 offset4">
                    <label for="date"><h4 class="pull-left">Date</h4>
                        <input class="datepick text span4 offset1" id="date" type="text" name="date" value="<?php echo $date; ?>">
                    </label>
                </div>
            </div>

            <br><br>

            <div class="row">
                <div class="dropdown span2 offset4">
                    <button class="btn btn-info btn-large btn-block dropdown-toggle" data-toggle="dropdown">
                        .CSV &nbsp;&nbsp;
                        <b class="caret"></b>
                    </button>
                    <ul class="dropdown-menu span2" role="menu" aria-labelledby="dLabel">
                        <li>
                            <button class="btn btn-block" type="submit" name="source" value="0">Main</button>
                        </li>
                        <li>
                            <button class="btn btn-block" type="submit" name="source" value="1">RSM</button>
                        </li>
                        <li>
                            <button class="btn btn-block" type="submit" name="source" value="2">Modbus</button>
                        </li>
                        <li>
                            <button class="btn btn-block" type="submit" name="source" value="3">Calculations</button>
                        </li>
                    </ul>
                </div>

                <button class="btn btn-info btn-large span2"  type="submit" name="fileType" value="xls">
                    .XLS
                </button>
            </div>


        </form>


<?php
require_once('../includes/footer.php');
?>
