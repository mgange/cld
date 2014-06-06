<script type="text/javascript">
    function imgsubmit(doc) {
       
        doc.form.submit();
    }
    
</script>



<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../includes/pageStart.php');

//checkSystemSet($config);

require_once('../../includes/header.php');
$db = new db($config);
$Entry = array();
$IFileName = array();

if (isset($_POST['id'])) { 
    $Recnum=$_POST['id'];
  } else {
    $Recnum=$_GET['id'];
  }

$query="Select * from SysConfigDefaults where recnum=".$Recnum." limit 1";
   
$SelRow = $db -> fetchrow($query);    


    $ItemName=$SelRow['ItemName'];
    $Loc="/cld/status/image/";
    

if ($_POST==NULL) {
    $SysImage1=$SelRow['SysImage1'];
    $SysImage2=$SelRow['SysImage2'];
    $LoopImage1=$SelRow['LoopImage1'];
    $LoopImage2=$SelRow['LoopImage2'];
    $LoopTop=$SelRow['LoopPosTop'];
    $LoopLeft=$SelRow['LoopPosLeft'];
   
}   

  else  {
    $SysImage1=$_POST['SysImage1'];
    $SysImage2=$_POST['SysImage2'];
    $LoopImage1=$_POST['LoopImage1'];
    $LoopImage2=$_POST['LoopImage2'];
    $LoopTop=$_POST['LoopPosTop'];
    $LoopLeft=$_POST['LoopPosLeft'];
   

    
    if  (isset($_POST['Update']) and ($_POST['Update']== "Update"))  {
        
       $bind[':SysImage1'] =  $_POST['SysImage1'];
       $bind[':SysImage2'] =  $_POST['SysImage2'];
       $bind[':LoopImage1'] =  $_POST['LoopImage1'];
       $bind[':LoopImage2'] =  $_POST['LoopImage2'];
       $bind[':LoopPosTop'] =  $_POST['LoopPosTop'];
       $bind[':LoopPosLeft'] =  $_POST['LoopPosLeft'];
       
       $query="Update SysConfigDefaults set
           SysImage1 = :SysImage1,SysImage2 = :SysImage2,LoopImage1 = :LoopImage1,LoopImage2 = :LoopImage2,
           LoopPosTop = :LoopPosTop,LoopPosLeft = :LoopPosLeft
           where Recnum=".$Recnum;  
    
        try {
                $db -> execute($query,$bind);
                   echo("<font color='blue' size=3><b>Changes have been Saved</font>");  
                   
                } catch (Exception $e) {
                
                    $Mess=$e->getMessage();
                    echo '<BR>Caught exception: ',  $e->getMessage(), "\n";
                   
                }
       }
  }
  ?>  
 <?php
 
 

   $path="c:/wamp/www/cld/status/image/";

	// Opens the folder
	$folder = opendir($path);
        // Loop trough all files in the folder
        $i=0;
	while (($Entry[$i] = readdir($folder)) != "") { 
           if (($Entry[$i] !=".") and ($Entry[$i]!=".."))  {$i++; }
           $j=$i;
	}       
   
	// Close folder
	$folder = closedir($folder);
    
        
    
   

    function dropdown ($Entry,$parmname,$compare,$j)
    {         
        
     
        echo("<select name='".$parmname."' class='select-submit span2' onchange='imgsubmit(this);'>");
        
       
      //   while ($Entry[$i] != "") {
       for ($i=0;$i<$j;$i++)   {   
             
               if($compare == $Entry[$i])
                   {
                       echo "<option selected='selected' value='" . $Entry[$i] . "'>" . $Entry[$i]. "</option>";

                   }
                       else  { echo "<option value='" . $Entry[$i] . "'>" . $Entry[$i] . "</option>";
                   }
               
            }    
          echo("</select>");
            
         
     }

?>
 <div class="row">
            <h2 class="span8 offset2">Images for System Configuration - <?=$ItemName?></h2>
  </div> 
<form action="/cld/config_setup/sysimage/sysimage.php" method="post" id="images">
 <div class="reference-list">
    <div class="row list-headings align-center">
        <h4>
        <div class="span2"></div>    
        <div class="span2">System Image - Heating</div>
        <div class="span2">System Image - Cooling</div>
        <div class="span2">Loop Image - Heating</div>
        <div class="span2">Loop Image - Cooling</div>
        
        </h4>
    </div>
    <div class="row">
         <div class="span2">
          
            <h4> File Name </h4>
         </div> 
    
         <div class="span2">
           <?php dropdown($Entry,"SysImage1",$SysImage1,$j);  ?>
         </div>     
         <div class="span2">
          <?php dropdown($Entry,"SysImage2",$SysImage2,$j);  ?>
        </div>       
         <div class="span2">
          <?php dropdown($Entry,"LoopImage1",$LoopImage1,$j);  ?>
        </div>  
        <div class="span2">
          <?php dropdown($Entry,"LoopImage2",$LoopImage2,$j);  ?>
         </div>  
     
    </div>
    <hr>
  
    <hr>

     <div class="row">
         <div class="span2">
          
            <h4> Image </h4>
         </div> 
    
         <div class="span2">
          
             <img  src="<?=$Loc.$SysImage1?>" Alt="System Image Heating"> </img>
         </div>     
         <div class="span2">
          
               <img  src="<?=$Loc.$SysImage2?>" Alt="System Image Cooling"> 
         </div>       
         <div class="span2">
          
               <img  src="<?=$Loc.$LoopImage1?>" Alt="Loop Image Heating"> 
         </div>  
          <div class="span2">
          
               <img  src="<?=$Loc.$LoopImage2?>" Alt="Loop Image Cooling"> 
         </div>  
     
    </div>
      <hr>     

      <hr>
   <div class="accordion-group" style="border:0px">
            <div class="accordion-heading">
                <a class="accordion-toggle"
                     href="../sysimage/HCConfigDisplay.php?HCMode=Heat&HE=<?=$Loc.$SysImage1 ?>&LP=<?=$Loc.$LoopImage1 ?>
                      &LPTop=<?=$LoopTop ?>&LPLeft=<?=$LoopLeft?> ">
                    <div class="row">
                        <h2 class="span8 offset3">+ Display Heating Configuration</h2>
                    </div>
                </a>
            </div>
        </div>
        <div class="accordion-group" style="border:0px">
            <div class="accordion-heading">
                <a class="accordion-toggle"
                   href="../sysimage/HCConfigDisplay.php?HCMode=Cool&HE=<?=$Loc.$SysImage2 ?>&LP=<?=$Loc.$LoopImage2 ?>
                      &LPTop=<?=$LoopTop ?>&LPLeft=<?=$LoopLeft?> ">
                    <div class="row">
                        <h2 class="span8 offset3">+ Display Cooling Configuration</h2>
                    </div>
                </a>
            </div>
        </div>  
      
 <div class="row">
        <div class="span3 align-center">
            <b>Loop Position Top - px </b>
            <input type="text" Name="LoopPosTop" value="<?=$LoopTop?>"  onchange="imgsubmit(this);">
        </div>
     
       <div class="span3 align-center">
           <b>Loop Position Left - px </b>
            <input type="text" Name="LoopPosLeft" value="<?=$LoopLeft?>" onchange="imgsubmit(this);" >
            
        </div>
 </div>
  <div class="row">
        <div class="span3 align-center">
            <a class="btn btn-Medium" style="width:200px" href="../upload">
                <i class="icon-upload"></i>
                Upload New Image
            </a>
        </div>
     
       <div class="span4 align-center">
            <button type="submit" class="btn btn-success" name="Update" value="Update">
                <i class="icon-pencil icon-white"></i>
                Save Changes
            </button>
        </div>
 </div>     
     <input type="hidden" Name="id" value="<?=$Recnum?>">
   
 </div>
</form>