<?php
/**
 *------------------------------------------------------------------------------
 * Status Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/header.php');
?>

        <div class="row">
            <h1 class="span8 offset2">Status</h1>
        </div>

        <div class="row">
            <div class="status-container span10 offset1">
                <div class="status-Back map">
                    <img src="../status/image/WebBackGroundHeatingMode.png" alt="Heat Exchanger">
                </div>
                <div class="status-OpenLoop">
                    <img src="../status/image/WebOpenLoop.png" alt="Open Loop ">
                </div>t
                  <div class="status-OpenLoopDryWell">
                    <img src="../status/image/WebOpenLoopDryWell.png" alt="Open Loop Dry Well">
                </div>
                  <div class="status-ClosedLoop hidden">
                    <img src="../status/image/WebClosedLoop.png" alt="Closed Loop">
                </div>
            </div>
        </div>

<?php
require_once('../includes/footer.php');
?>
