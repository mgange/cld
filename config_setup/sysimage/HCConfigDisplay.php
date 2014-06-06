<?php

require_once('../../includes/pageStart.php');

//checkSystemSet($config);

require_once('../../includes/header.php');





$HCMode=$_GET['HCMode'];
$HEImage=$_GET['HE'];
$LPImage=$_GET['LP'];
$LoopTop=$_GET['LPTop'];
$LoopLeft=$_GET['LPLeft'];
   

if ($HCMode=="Heat") {
       $Title="Heating Configuration";
      
        }
   else {
       
       $Title="Cooling Configuration";
        

       
        }






?>
  <h3 align="center"><b><?=$Title?></b></h3>
       <div class="status-container span10 offset0" style="height:800px;width:100%;float:left"> 
           

               <div class="status-Back map">
                   <img src="<?php echo $HEImage ?>" alt="Heat Exchanger">
               </div>
               <div class="status-OpenLoop " style="left: <?=$LoopLeft?>px; top: <?=$LoopTop?>px" > 
                   
                   <img  src="<?php echo $LPImage ?>" alt="Exchange Loop ">
               </div>
       </div>