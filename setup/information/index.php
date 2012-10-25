<?php
/**
 *------------------------------------------------------------------------------
 * Information Index Page
 *------------------------------------------------------------------------------
 *
 */
require_once('../../includes/pageStart.php');

checkSystemSet($config);

if($_SESSION['authLevel'] < 3) {
    gtfo($config);
}

$db = new db($config);
$SysId = $_SESSION['SysID'];
$BuildingID = $_SESSION['buildingID'];
$query = "SELECT * FROM SystemConfig WHERE SysId = $SysId AND BuildingID = $BuildingID";
$systemInfo = $db -> fetchRow($query);
$query = "SELECT * FROM buildings WHERE buildingID = $BuildingID";
$buildingInfo = $db -> fetchRow($query);
$query = "SELECT * FROM customers WHERE customerID = $buildingInfo[CustomerID]";
$customerInfo = $db -> fetchRow($query);

if(count($_POST) > 0) {
    $query = "UPDATE customers,SystemConfig SET customers.email1 = :email1, customers.email2 = :email2, SystemConfig.Maintainer = :maintainer WHERE customers.customerID = $customerInfo[customerID] AND SystemConfig.SysId = $SysId AND SystemConfig.BuildingID = $BuildingID";
    $bind[':email1'] = $_POST['email1'];
    $bind[':email2'] = $_POST['email2'];
    $bind[':maintainer'] = $_POST['maintainer'];

    if($db -> execute($query, $bind)) {
        header('Location: ../?a=ss'); //a = Alert  ss = Secondary Success(generic)
    }else{
        header('Location: ./?a=e'); //a = Alert  e = error(generic)
    }

    die(require_once('../../includes/footer.php'));
}

require_once('../../includes/header.php');

?>

<div class="row">
    <h1 class="span8 offset2">'<?php echo $systemInfo['SysName']; ?>' Information</h1>
</div>

<form action="./" method="POST">
    <div class="row">
        <div class="span6">
            <label for="name">Customer Name
                <input readonly="true" type="text" class="span6" value="<?=$customerInfo['customerName']?>">
            </label>
            <label for="cLocation">Customer Location
                <textarea style="resize:none" readonly="true" class="span6" rows="<?=isset($customerInfo['addr2']) ? 3 : 2?>"><?=$customerInfo['addr1'] . "\n" . (!empty($customerInfo['addr2']) ? $customerInfo['addr2'] . "\n" : "") . $customerInfo['city'] . ", " . $customerInfo['state'] . " " . $customerInfo['zip']?></textarea>
            </label>
            <label for="email1">Customer Email
                <input name="email1" type="text" class="span6" value="<?=$customerInfo['email1']?>">
            </label>
            <label for="email2">Alternate Email
                <input name="email2" type="text" class="span6" value="<?=$customerInfo['email2']?>">
            </label>
            <label for="bLocation">Building Location
                <textarea readonly="true" style="resize:none" class="span6" rows="<?=isset($buildingInfo['address2']) ? 3 : 2?>"><?=$buildingInfo['address1'] . "\n" . (!empty($buildingInfo['address2']) ? $buildingInfo['address2'] . "\n" : "") . $buildingInfo['city'] . ", " . $buildingInfo['state'] . " " . $buildingInfo['zip']?></textarea>
            </label>
            <label for="installDate">Install Date
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['InstallDate']?>">
            </label>
            <label for="installer">Installer
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['Installer']?>">
            </label>
            <label for="maintainer">Maintainer
                <input name="maintainer" type="text" class="span6" value="<?=$systemInfo['Maintainer']?>">
            </label>
        </div>
        <div class="span6">
            <label for="maintainer">DAMID
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['DAMID']?>">
            </label>
            <label for="sysType">System Type
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['Systype']?>">
            </label>
            <label for="config">Configuration
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['Configuration']?>">
            </label>
            <label for="heatExchange">Heat Exchange Unit
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['HeatExchangeUnit']?>">
            </label>
            <label for="NumofSensGrp">Number of Sensor Group
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['NumofSensGrp']?>">
            </label>
            <label for="NumDigSenChan">Number of Digital Sensor Channels
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['NumDigSenChan']?>">
            </label>
            <label for="NumTempSenChan">Number of Temperature Sensor Channels
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['NumTempSenChan']?>">
            </label>
            <label for="NumFlowCntlChan">Number of Flow Control Channels
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['NumFlowCntlChan']?>">
            </label>
            <label for="NumAnlgChan">Number of Analog Channels
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['NumAnlgChan']?>">
            </label>
            <label for="NumTempSenChan">Number of RSM's
                <input readonly="true" type="text" class="span6" value="<?=$systemInfo['NumofRSM']?>">
            </label>
            <?php
                for($i=0;$i<$systemInfo['NumofRSM'];$i++){
                    $rsmNum = "LocationRSM" . ($i + 1);
                    echo "<label for=\"locationRSM . ($i+1) . \">Location of RSM " . ($i + 1) . "
                        <input readonly=\"true\" type=\"text\" class=\"span6\" value=\"" . $systemInfo[$rsmNum] . "\">";
                    //echo $systemInfo['$rsmNum'];
                }
            ?>
        </div>
    </div>
    <div class="row">
        <div class="span10 offset1">
            <button type="submit" class="btn btn-success">
                <i class="icon-pencil icon-white"></i>
                Update
            </button>
            <a href="../" class="btn pull-right">
                <i class="icon-remove"></i>
                Cancel
            </a>
        </div>
    </div>
    <input type="hidden" name="customerID" value="<?=$customerInfo['customerID']?>">
</form>

<?php
require_once('../../includes/footer.php');
?>
