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

//$SysID=$_SESSION['SysID'];
unset($_SESSION['buildingID']);
unset($_SESSION['SysID']);

unset($_SESSION['SetupStep']);

 ?>


        <div class="row">
            <h1 class="span8 offset2">System Setup</h1>
        </div>

<?php if(isset($_SESSION['authLevel']) && intval($_SESSION['authLevel']) == 3  ){ ?>
       <div class="row">
            <h2 class="span8 offset3"><a href="new_system/">New System Setup</a></h2>
       </div>

  <?php } ?>

  <?php if(isset($_SESSION['authLevel']) && (intval($_SESSION['authLevel']) == 3  or intval($_SESSION['authLevel']) == 2)) { ?>

       <div class="row">
            <h2 class="span8 offset3"><a href="edit_system/">Modify Existing System</a></h2>
       </div>
        <?php } ?>
        </div>

<?php
    require_once('../includes/footer.php');
?>
