



<?php
/**
 *------------------------------------------------------------------------------
 *Data Download page
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
 *
 */
require_once('../includes/pageStart.php');

checkSystemSet($config);

require_once('../includes/header.php');

 $SysID=$_SESSION["SysID"];

 ?>
        <div class="row">
            <h1 class="span8 offset2">Extracts Data from MySql Data table based on date range and sends data to excel<BR> 

 * Choose Tables to include in excel <BR>

 * each table on Separate tab <BR>

 * SourceHeader<BR>
 * SourceData0<BR>
 * SourceData1<BR>
 * SourceData4<BR>
 * SensorCalc<BR></h1>  
        </div>    
       
        <div class="row">     
            <h2>    First select table or tables to extract <BR>

            second select date range
            third get data<BR>
            fourth send to excel<BR>
            </h2>
           
        </div>
 use OAS code from OAS_HC_MassDataDownloadDaily as guide<BR>
Accessible by System Admin or Building Manager authorization only
   
   <?php
require_once('../includes/footer.php');
?>