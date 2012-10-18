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
        <table class="table">
        	<tr>
        		<td style="width:50%; border-top:0px">
        			<table class="table">
       					<tr>
			        		<th>Customer Name:</th>
			        		<td><?php echo $customerInfo['customerName']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>Customer Location:</th>
			        		<td><?php echo $customerInfo['addr1'] . "<br>" . (isset($customerInfo['addr2']) ? $customerInfo['addr2'] . "<br>" : "") . $customerInfo['city'] . ", " . $customerInfo['state'] . " " . $customerInfo['zip']; ?></td>
			        	</tr>
			        	<?php if(isset($customerInfo['email1']) || isset($customerInfo['email2'])){ ?>
			        	<tr>
			        		<th>Customer Email(s):</th>
			        		<td>
			        		<?php
			        			if(isset($customerInfo['email1'])){
			        				echo $customerInfo['email1'];
			        				if(isset($customerInfo['email2'])){
			        					echo "<br>" . $customerInfo['email2'];
			        				}
			        			}else echo $customerInfo['email2'];
			        		?>
			        		</td>
			        	</tr>
			        	<?php } ?>
			        	<tr>
			        		<th>Building Location:</th>
			        		<td><?php echo $buildingInfo['address1'] . "<br>" . (isset($buildingInfo['address2']) ? $buildingInfo['address2'] . "<br>" : "") . $buildingInfo['city'] . ", " . $buildingInfo['state'] . " " . $buildingInfo['zip']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>System Location:</th>
			        		<td><?php echo $systemInfo['LocationMainSystem']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>Install Date:</th>
			        		<td><?php echo $systemInfo['InstallDate']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>Installer:</th>
			        		<td><?php echo $systemInfo['Installer']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>Maintainer:</th>
			        		<td><?php echo $systemInfo['Maintainer']; ?></td>
			        	</tr>
			        </table>
			    </td>
			    <td style="width:50%; border-top:0px">
			        <table class="table">
			        	<tr>
			        		<th>DAMID:</th>
			        		<td><?php echo $systemInfo['DAMID']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>System Type:</th>
			        		<td><?php echo $systemInfo['Systype']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>Configuration:</th>
			        		<td><?php echo $systemInfo['Configuration']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>Heat Exchange Unit:</th>
			        		<td><?php echo $systemInfo['HeatExchangeUnit']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>Number of Sensor Group:</th>
			        		<td><?php echo $systemInfo['NumofSensGrp']; ?></td>
			        	</tr>
			        	<tr>
			        	<tr>
			        		<th>Number of Digital Sensor Channels:</th>
			        		<td><?php echo $systemInfo['NumDigSenChan']; ?></td>
			        	</tr>
			        		<th>Number of Temperature Sensor Channels:</th>
			        		<td><?php echo $systemInfo['NumTempSenChan']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>Number of Flow Control Channels:</th>
			        		<td><?php echo $systemInfo['NumFlowCntlChan']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>Number of Analog Channels:</th>
			        		<td><?php echo $systemInfo['NumAnlgChan']; ?></td>
			        	</tr>
			        	<tr>
			        		<th>Number of RSM's:</th>
			        		<td><?php echo $systemInfo['NumofRSM']; ?></td>
			        	</tr>
			        	<?php
			        		for($i=0;$i<$systemInfo['NumofRSM'];$i++){
			        			$rsmNum = "LocationRSM" . ($i + 1);
								echo "<tr>
					        			<th>Location of RSM " . ($i + 1) . ":</th>
					        			<td>" . $systemInfo[$rsmNum] . "</td>
					        		</tr>";
					        	echo  $systemInfo['LocationRSM" . ($i + 1) . "'];
							}
						?>
			        </table>
			    </td>
			</tr>
		</table>

<?php
require_once('../includes/footer.php');
?>
