<?php
/**
 *------------------------------------------------------------------------------
 * Information Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/pageStart.php');

checkSystemSet($config);

require_once('../includes/header.php');

        $db = new db($config);
        $SysId = $_SESSION['SysID'];
        $BuildingID = $_SESSION['buildingID'];
        $query = "SELECT * FROM SystemConfig WHERE SysId = $SysId AND BuildingID = $BuildingID";
        $systemInfo = $db -> fetchRow($query);
        $query = "SELECT * FROM buildings WHERE buildingID = $BuildingID";
        $buildingInfo = $db -> fetchRow($query);
        $query = "SELECT * FROM customers WHERE customerID = $buildingInfo[CustomerID]";
        $customerInfo = $db -> fetchRow($query);
?>
        <div class="row">
            <h1 class="span8 offset2">'<?php echo $systemInfo['SysName']; ?>' Information</h1>
        </div>

        <div class="row">
            <div class="span6">

                <hr>

                <div class="row">
                    <p class="span3"><strong>Customer Name:</strong></p>
                    <p class="span3"><?php echo $customerInfo['customerName']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Customer Location:</strong></p>
                    <p class="span3"><?php echo $customerInfo['addr1'] . "<br>" . (isset($customerInfo['addr2']) ? $customerInfo['addr2'] . "<br>" : "") . $customerInfo['city'] . ", " . $customerInfo['state'] . " " . $customerInfo['zip']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Customer Email:</strong></p>
                    <p class="span3"><?php
                            if(isset($customerInfo['email1'])){
                                echo $customerInfo['email1'];
                                if(isset($customerInfo['email2'])){
                                    echo "<br>" . $customerInfo['email2'];
                                }
                            }else echo $customerInfo['email2'];
                        ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Building Location:</strong></p>
                    <p class="span3"><?php echo $buildingInfo['address1'] . "<br>" . (isset($buildingInfo['address2']) ? $buildingInfo['address2'] . "<br>" : "") . $buildingInfo['city'] . ", " . $buildingInfo['state'] . " " . $buildingInfo['zip']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Install Date:</strong></p>
                    <p class="span3"><?php echo $systemInfo['InstallDate']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Installer:</strong></p>
                    <p class="span3"><?php echo $systemInfo['Installer']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Maintainer:</strong></p>
                    <p class="span3"><?php echo $systemInfo['Maintainer']; ?></p>
                </div>
            </div>

            <div class="span6">

                <hr>

                <div class="row">
                    <p class="span3"><strong>DAMIN:</strong></p>
                    <p class="span3"><?php echo $systemInfo['DAMID']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>System Type:</strong></p>
                    <p class="span3"><?php echo $systemInfo['Systype']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Configuration:</strong></p>
                    <p class="span3"><?php echo $systemInfo['Configuration']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Heat Exchange Unit:</strong></p>
                    <p class="span3"><?php echo $systemInfo['HeatExchangeUnit']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of Sensor Group:</strong></p>
                    <p class="span3"><?php echo $systemInfo['NumofSensGrp']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of Digital Sensor Channels:</strong></p>
                    <p class="span3"><?php echo $systemInfo['NumDigSenChan']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of Temperature Sensor Channels:</strong></p>
                    <p class="span3"><?php echo $systemInfo['NumTempSenChan']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of Flow Control Channels:</strong></p>
                    <p class="span3"><?php echo $systemInfo['NumFlowCntlChan']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of Analog Channels:</strong></p>
                    <p class="span3"><?php echo $systemInfo['NumAnlgChan']; ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of RSM's:</strong></p>
                    <p class="span3"><?php echo $systemInfo['NumofRSM']; ?></p>
                </div>

                <hr>

                <?php
                    for($i=0;$i<$systemInfo['NumofRSM'];$i++){
                        $rsmNum = "LocationRSM" . ($i + 1);
                        echo "<div class=\"row\">
                    <p class=\"span3\"><strong>Location of RSM " . ($i + 1) . ":</strong></p>
                    <p class=\"span3\">" . $systemInfo[$rsmNum] . "</p>
                </div>";
                        echo  $systemInfo['LocationRSM" . ($i + 1) . "'];
                    }
                ?>

            </div>
        </div>


<?php
require_once('../includes/footer.php');
?>
