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

$db = new db($config);

if(isset($_POST['from']) && isset($_POST['until'])) {

    require_once('class.excelXML.php');

    $bind[':SysID'] = $_SESSION['SysID'];
    $bind[':from'] = date('Y-m-d', strtotime($_POST['from']));
    $bind[':until'] = date('Y-m-d', strtotime($_POST['until']));

    $query = "SELECT
    SourceHeader.Recnum AS ID,
    SourceHeader.DateStamp AS Date,
    SourceHeader.TimeStamp AS Time,
    SourceData0.*
    FROM SourceHeader, SourceData0
    WHERE SysID = :SysID
    AND SourceHeader.Recnum = SourceData0.HeadID
    AND SourceHeader.DateStamp >= :from
    AND SourceHeader.DateStamp < :until
    ORDER BY SourceHeader.DateStamp DESC, SourceHeader.TimeStamp DESC
    LIMIT 0,10
    ";
    $main = $db->fetchAll($query, $bind);

    $query = "SELECT
    SourceHeader.Recnum AS ID,
    SourceHeader.DateStamp AS Date,
    SourceHeader.TimeStamp AS Time,
    SourceData1.*
    FROM SourceHeader, SourceData1
    WHERE SysID = :SysID
    AND SourceHeader.Recnum = SourceData1.HeadID
    AND SourceHeader.DateStamp >= :from
    AND SourceHeader.DateStamp < :until
    ORDER BY SourceHeader.DateStamp DESC, SourceHeader.TimeStamp DESC
    LIMIT 0,10
    ";
    $rsm = $db->fetchAll($query, $bind);

    $query = "SELECT
    SourceHeader.Recnum AS ID,
    SourceHeader.DateStamp AS Date,
    SourceHeader.TimeStamp AS Time,
    SourceData4.*
    FROM SourceHeader, SourceData4
    WHERE SysID = :SysID
    AND SourceHeader.Recnum = SourceData4.HeadID
    AND SourceHeader.DateStamp >= :from
    AND SourceHeader.DateStamp < :until
    ORDER BY SourceHeader.DateStamp DESC, SourceHeader.TimeStamp DESC
    LIMIT 0,10
    ";
    $modbus = $db->fetchAll($query, $bind);

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

    // Put all those rows in a worksheet
    $excel->create_worksheet('Main');

    // RSM VALUES

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
    $excel->create_worksheet('RSM');

    // MODBUS GATEWAY VALUES

    // Create an empty array for header row values
    unset($headers);
    $headers = array();

    // Added the key names to the header row
    foreach($rsm[0] as $key => $val) {
        array_push($headers, $key);
    }
    $excel->add_row($headers, 'header');

    // Write each DB record as a spreadsheet row
    foreach($modbus as $row) {
        $excel->add_row($row);
    }
    $excel->create_worksheet('Modbus Gateway');


    $xml = $excel->generate();
    $excel->download('Download.xls');
}

require_once('../includes/header.php');

$from = date("Y-m-d", time() - 60 * 60 * 24);
$until = date('Y-m-d');
?>

        <div class="row">
            <h1 class="span8 offset2">Data Download</h1>
        </div>

        <form class="validate" action="./" method="POST">
            <div class="row">
                <div class="span4 offset1">
                    <label for="from"><h4>From</h4>
                        <input class="datepick text span4 offset1" id="from" type="text" name="from" value="<?php echo $from; ?>">
                    </label>
                </div>
                <div class="span5 offset2">
                    <label for="until"><h4>Until</h4>
                        <input class="datepick text span4 offset1" id="until" type="text" name="until" value="<?php echo $until; ?>">
                    </label>
                </div>
            </div>

            <br><br>

            <div class="row">
                <div class="span6 offset3">
                    <button class="btn btn-info btn-large btn-block"  type="submit">
                        <i class="icon-download-alt icon-white"></i>
                        Begin Download
                    </button>
                </div>
            </div>
        </form>


<?php
require_once('../includes/footer.php');
?>
