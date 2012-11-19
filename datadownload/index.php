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
