


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

$SysID=$_SESSION["SysID"];


 ?>


        <div class="row">
            <h1 class="span8 offset2">System Setup</h1>
        </div>

<?php if(isset($_SESSION['authLevel']) && intval($_SESSION['authLevel']) == 3  ){ ?>
                           <div class="row">
                                <h2 class="span8 offset3 "><a href="new_system/">&nbsp;&nbsp;New System Setup</a></h2>
                           </div>


  <?php } ?>

  <?php if(isset($_SESSION['authLevel']) && (intval($_SESSION['authLevel']) == 3  or intval($_SESSION['authLevel']) == 2)) { ?>

         <div class="accordion-group">
             <div class="accordion-heading">
                <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordion2"
                    href="#collapse2">
                            <div class="row">
                                <h2 class="span8 offset3">+ Modify Existing System</h2>
                            </div>
                </a>
            </div>
            <div id="collapse2" class="accordion-body collapse">
                <div class="accordion-inner">
                    <div class="row">
                        <div class="span5">
                             <h2 class="span8 offset3"><a href="information/">- Building Information2</a></h2>
                             <h2 class="span8 offset3"><a href="sensor_mapping?id=<?php echo $SysID; ?>">- Sensor Mapping2</a></h2>
                             <h2 class="span8 offset3"><a href="alarm_limits/">- Alarm Limits2</a></h2>
                             <h2 class="span8 offset3">- Maintenance2</h2>
                        </div>

                    </div>




                    </div>

                </div>
        <?php } ?>
            </div>


















   <?php
require_once('../includes/footer.php');
?>
