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
            <h1 class="span8 offset2">'<?=$systemInfo['SysName']?>' Information</h1>
        </div>

        <div class="row">
            <div class="span6">

                <hr>

                <div class="row">
                    <p class="span3"><strong>Customer Name:</strong></p>
                    <p class="span3"><?=$customerInfo['customerName']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Customer Location:</strong></p>
                    <p class="span3"><?=$customerInfo['addr1'] . "<br>" . (!empty($customerInfo['addr2']) ? $customerInfo['addr2'] . "<br>" : "") . $customerInfo['city'] . ", " . $customerInfo['state'] . " " . $customerInfo['zip']?></p>
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
                    <p class="span3"><?=$buildingInfo['address1'] . "<br>" . (!empty($buildingInfo['address2']) ? $buildingInfo['address2'] . "<br>" : "") . $buildingInfo['city'] . ", " . $buildingInfo['state'] . " " . $buildingInfo['zip']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Install Date:</strong></p>
                    <p class="span3"><?=$systemInfo['InstallDate']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Installer:</strong></p>
                    <p class="span3"><?=$systemInfo['Installer']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Maintainer:</strong></p>
                    <p class="span3"><?=$systemInfo['Maintainer']?></p>
                </div>
            </div>

            <div class="span6">

                <hr>

                <div class="row">
                    <p class="span3"><strong>DAMID:</strong></p>
                    <p class="span3"><?=$systemInfo['DAMID']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>System Type:</strong></p>
                    <p class="span3"><?=$systemInfo['Systype']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Configuration:</strong></p>
                    <p class="span3"><?php
                        switch ($systemInfo['Configuration']){
                            case 1:
                                echo "Open Loop System with Dry Well";
                                break;
                            case 2:
                                echo "Open Loop System without Dry Well";
                                break;
                            case 3:
                                echo "Closed Loop System";
                                break;
                        }
                    ?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Heat Exchange Unit:</strong></p>
                    <p class="span3"><?=$systemInfo['HeatExchangeUnit']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of Sensor Group:</strong></p>
                    <p class="span3"><?=$systemInfo['NumofSensGrp']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of Digital Sensor Channels:</strong></p>
                    <p class="span3"><?=$systemInfo['NumDigSenChan']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of Temperature Sensor Channels:</strong></p>
                    <p class="span3"><?=$systemInfo['NumTempSenChan']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of Flow Control Channels:</strong></p>
                    <p class="span3"><?=$systemInfo['NumFlowCntlChan']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of Analog Channels:</strong></p>
                    <p class="span3"><?=$systemInfo['NumAnlgChan']?></p>
                </div>

                <hr>

                <div class="row">
                    <p class="span3"><strong>Number of RSM's:</strong></p>
                    <p class="span3"><?=$systemInfo['NumofRSM']?></p>
                </div>

                <hr>

                <?php
                    for($i=0;$i<$systemInfo['NumofRSM'];$i++){
                        $rsmNum = "LocationRSM" . ($i + 1);
                        echo "<div class=\"row\">
                    <p class=\"span3\"><strong>Location of RSM " . ($i + 1) . ":</strong></p>
                    <p class=\"span3\">" . $systemInfo[$rsmNum] . "</p>
                </div>";
                    }
                ?>

            </div>
        </div>


<?php
require_once('../includes/footer.php');
?>
