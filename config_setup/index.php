


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







        <div class="row">
            <h1 class="span8 offset2">System Configuration Parameters</h1>
        </div>


       <div id="accordionContainer">
           <div class="accordion-group" style="border:0px">
                <div class="accordion-heading">
                    <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordionContainer"
                    href="#collapse1">
                        <div class="row">
                            <h2 class="span8 offset3">System Hardware Listings1</h2>
                        </div>
                    </a>
                </div>
                <div id="collapse1" class="accordion-body collapse">
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




            <div class="accordion-group" style="border:0px">
                <div class="accordion-heading">
                    <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordionContainer"
                    href="#collapse2">
                        <div class="row">
                            <h2 class="span8 offset3">Sensor Hardware Listings</h2>

                        </div>
                    </a>
                </div>
                <div id="collapse2" class="accordion-body collapse">
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






            <div class="accordion-group" style="border:0px">
                <div class="accordion-heading">
                    <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordionContainer"
                    href="#collapse3">
                        <div class="row">
                            <h2 class="span8 offset3">System Parameter Listings</h2>

                        </div>
                    </a>
                </div>
                <div id="collapse3" class="accordion-body collapse">
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
        </div>



<?php
require_once('../includes/footer.php');
?>
