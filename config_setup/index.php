


<?php
/**
 *------------------------------------------------------------------------------
 * Maintenance Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');

checkSystemSet($config);

require_once('../includes/header.php');

$SysID=$_SESSION['SysID'];
unset($_SESSION['buildingID']);

unset($_SESSION['SetStep']);
unset($_SESSION['SystemStart']);
unset($_SESSION['SystemComp']);
unset($_SESSION['SetUpSensMap']);
unset($_SESSION['SetUpAlarm']);
unset($_SESSION['SetUpStatus']);
unset($_SESSION['SetUpComp']);


$_SESSION['S1']=false;
$_SESSION['S2']=false;
$_SESSION['S3']=false;

 ?>





<?php if(isset($_SESSION['authLevel']) && (intval($_SESSION['authLevel']) == 3 )) { // A ?>

 <div class="row">
            <h1 class="span8 offset2">System Configuration Parameters</h1>
        </div>

<?php if ($_SESSION['S2']!=true and $_SESSION['S3']!=true ) { // S1 ?>
       <div class="accordion-group" style="border:0px">
            <div class="accordion-heading">
                <a class="accordion-toggle"
                data-toggle="collapse"
                data-parent="#accordion2"
                href="#collapse2">
                    <div class="row">
                        <h2 class="span8 offset3">System Hardware Listings</h2>
                    </div>
                </a>
            </div>
            <div id="collapse2" class="accordion-body collapse">
                <div class="accordion-inner">
                    <div class="row">
                        <div class="span12">

                         <?php
                             $_SESSION['LType']="Hardware System ";
                             $_SESSION['S1']=true;
                             $_SESSION['S2']=false;
                             $_SESSION['S3']=false;
                             include('Lists/index.php');
                         ?>
                        </div>

                    </div>

                </div>

            </div>

        </div>
<?php } else  { // from S1  ?>
<div class="row">
                        <h2 class="span8 offset3">System Hardware Listings</h2>

                    </div>
<?php } // from S1 ?>
<?php if ($_SESSION['S1']!=true and $_SESSION['S3']!=true ) { // S2 ?>

        <div class="accordion-group" style="border:0px">
            <div class="accordion-heading">
                <a class="accordion-toggle"
                data-toggle="collapse"
                data-parent="#accordion2"
                href="#collapse3">
                    <div class="row">
                        <h2 class="span8 offset3">Sensor Hardware Listings</h2>

                    </div>
                </a>
            </div>
            <div id="collapse3" class="accordion-body collapse<?=($_SESSION['S2']==true) ? "" : "in"?>">
                <div class="accordion-inner">
                    <div class="row">
                        <div class="span12">

                            <?php
                               $_SESSION['LType']="Hardware Sensor";
                               $_SESSION['S1']=false;
                               $_SESSION['S2']=true;
                               $_SESSION['S3']=false;
                               include('Lists/index.php');
                            ?>

                        </div>

                    </div>

                </div>

            </div>

       </div>
<?php } else  { // from S2  ?>
<div class="row">
                        <h2 class="span8 offset3">Sensor Hardware Listings</h2>

                    </div>
<?php } // from S2 ?>

<?php if ($_SESSION['S1']!=true and $_SESSION['S2']!=true ) { //S3 ?>

<div class="accordion-group" style="border:0px">
            <div class="accordion-heading">
                <a class="accordion-toggle"
                data-toggle="collapse"
                data-parent="#accordion2"
                href="#collapse4">
                    <div class="row">
                        <h2 class="span8 offset3">System Parameter Listings</h2>

                    </div>
                </a>
            </div>
            <div id="collapse4" class="accordion-body collapse<?=($_SESSION['S3']==true) ? "" : "in"?>">
                <div class="accordion-inner">
                    <div class="row">
                        <div class="span12">

                           <?php
                              $_SESSION['LType']="System Parameter";
                              $_SESSION['S1']=false;
                              $_SESSION['S2']=false;
                              $_SESSION['S3']=true;
                              include('Lists/index.php');
                           ?>
                        </div>

                    </div>

                </div>

            </div>

        </div>
     <?php } else  { // from S3  ?>
<div class="row">
                        <h2 class="span8 offset3">System Parameter Listings</h2>

                    </div>
<?php } // from s3?>
     <?php } // from A?>
<?php
require_once('../includes/footer.php');
?>
