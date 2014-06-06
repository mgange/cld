
<?php
/**
 *------------------------------------------------------------------------------
 *System Configuraton Parameters Lisitng
 * Session[LType is set during load of calling page
 * Selects ConfigGoup in query
 *
 *------------------------------------------------------------------------------
 *
 */

//require_once('../../includes/pageStart.php');
$db = new db($config);
//echo '<pre>'.$_SESSION['LType'].'</pre><br>';
if($_SESSION['authLevel'] != 3) {
  gtfo($config);
}
$PostFlag=false;
$Addrec=0;
$Pmode="";
if ($_POST) {
    
   if (isset($_POST['Update'])) {
     if  ($_POST['Update']=="Update")  {         
         $Pmode="Update";
         $PostFlag=true;
     }   
 }
 
 if (isset($_POST['Add'])) {
    if  ($_POST['Add']=="Add")  {         
         $Pmode="Add";
         $PostFlag=true;
     }   
 } 
    
    
    if (isset($_POST['Addrec'])==true and $Pmode=="Add") {$Addrec=$_POST['Addrec']+1; } else {$Addrec=0;}
//echo($_POST['Add']."|".$_POST["Update"]."|.".$PostFlag);

}
if ($PostFlag!=true)
{
$LType=$_SESSION['LType'];

//Select query to display existing records before initial post
   $query= "Select * from SysConfigDefaults where ConfigGroup='".$form."' order by ConfigSubGroup ";
//echo($query);
    $NumUprec = $db -> numRows($query);

    $SelRow = $db -> fetchAll($query);
   $i=0;
 foreach ($SelRow as $resultRow)
    {
    $Precnum[$i] =$resultRow['Recnum'];
    $Psubgrp[$i]=$resultRow['ConfigSubGroup'];
    $Pitem[$i]=$resultRow['ItemName'];
    $Pvalue[$i]=$resultRow['AssignedValue'];
    $Padj[$i]=$resultRow['AdjFactor'];
    $i=$i+1;
    
    }
   
    
   
    

}
?>
<!-- define form header -->
<form action='./' method='POST'>
    <input type="hidden" name="Fmtype" value="<?php echo $form; ?>" />
<table class="table table-bordered span12 background-color:blue" style="border: 4px ">
   <tr>
       <th><p class="span2"><h4 style="width:160px">Sub Group</h4></p></th>
       <th><p class="span5"><h4>Name/Model</h4></p></th>

      <?php if ($LType=="HardwareSensor") { echo("<th><p class='span2'><h4>Adjust Factor</h4></p></th>");} ?>
      <th><p class="span2"><h4>Value</h4></p></th>
    </tr>
<?php
$i=0;

for ($i=0;$i<$NumUprec;$i++)
// Loop thru records from posts in this loop
    {  ?>



<tr>

    <td>    <p class="span2" style="margin-top:10px;text-align:absolute">

                  <input readonly class='span2' size='45' type='text' style='max-width:100%' name='subgrp<?=$i?>' value="<?=$Psubgrp[$i]?>">
           </p> </td>
    <td>
         <?php if ($_POST) {
         if($errflag[$i][2]==1) {echo("<font color=red><b>Field must not be left blank</b></font>");}
         if($errflag[$i][2]==2) {echo("<font color=red><b>Field must have a unique value</b></font>");} } ?>
        <p class="span5" style="margin-top:10px;text-align:absolute"><input class="span5" type="text"  name="item<?=$i?>" value="<?=$Pitem[$i]?>">


        </p></td>


    <?php if ($LType=="HardwareSensor") {
             echo("<td> <p class='span2' style='margin-top:10px;text-align:absolute'><input class='span2' size='45' type='text' style='max-width:100%' name='adj".$i."' value=".$Padj[$i]."></p></td>");
       } else { echo("<input type='hidden' name='adj".$i."' value='".$Padj[$i]."'>");}
      ?> 
    
    
    
    
    
      
       <td> 
         <?php if ($_POST) {
           if($errflag[$i][3]==1) {echo("<font color=red><b>Field must not be left blank</b></font>");}
         if($errflag[$i][3]==2) {echo("<font color=red><b>Field must have a unique value</b></font>");} } ?>
        <p class="span2" style="margin-top:10px;text-align:absolute"><input Readonly class="span2" size="45" type="text" style="max-width:100%" name="value<?=$i?>" value="<?=$Pvalue[$i]?>"></p></td>
       </td>
      <?php 
       if ($LType=="SystemParameter") {
           if ($Psubgrp[$i]=="Configuration")  {
              echo("<td>");
              echo("<div class='span1 align-center'>");
              echo("<a class='btn btn-small' href='sysimage/sysimage.php?id=".$Precnum[$i]."&type=Image' target='_parent'>");
             // echo("<button onclick('openWin()')");
           //   echo("<button class='btn btn-small' onclick='openWin()'>");
              echo("  <i class='icon-upload'></i> System Images</a>");
              echo("</div>");
              echo("</td>"); 
            } else  echo("<td>-</td>");
       } 
       ?>
       
</tr>







   <input type="hidden" name="recnum<?=$i?>" value="<?=$Precnum[$i]?>">

<?php

   //$i=$i+1;

    }


   $j=0;

  for ($j=0;$j<$Addrec;$j++)
       {?>

   <tr>

    <td>    <p class="span2" style="margin-top:10px;text-align:absolute">
             <?php

                 $query="Select distinct(ConfigSubGroup) from SysConfigDefaults where ConfigGroup='".$form."' order by ConfigSubGroup";
                  MySQL_Pull_Down($config,$query,"subgrp".($j+$i),"ConfigSubGroup","ConfigSubGroup",$Psubgrp[$j+$i],false,"span4","");

              ?>

           </p>
    </td>
    <td>    <p class="span5" style="margin-top:10px;text-align:absolute"><input class="span5" type="text"  name="item<?=$i+$j?>" value="<?=$Pitem[$j+$i]?>"></p></td>


    <?php if ($LType=="HardwareSensor") {
             echo("<td> <p class='span2' style='margin-top:10px;text-align:absolute'><input class='span2' size='45' type='text' style='max-width:100%' name='adj".($i+$j)."' value=".$Padj[$j+$i]."></p></td>");
       } else { echo("<input type='hidden' name='adj".($i+$j)."' value='".$Padj[$j+$i]."'>");}
      ?>



    <td>    <p class="span2" style="margin-top:10px;text-align:absolute"><input  class="span2" size="45" type="text" style="max-width:100%" name="value<?=$i+$j?>" value="<?=$Pvalue[$j+$i]?>"></p></td>
 
   </tr>
  
   
    
   
   
   
             <input type='hidden' name='recnum<?=($i+$j)?>' value=''>
<?php     } ?>

 <?php if($Pmode=="Update")
     {
     if ($CumErr!=true and $Uperr==false)
         {
            $_POST['Update']="";
            $_POST['Add']="";
            echo("<font color=blue size='4'><b>Save was Successful</b></font>");
         } else
         {
           echo("<font color=red size='4'><b>Save was Unsuccessful</b></font>");
         }

      }
   ?>


</table>
    <div class="row">


        <div class="span4 offset1">
            <button type="submit" class="btn btn-success" name="Update" value="Update">
                <i class="icon-pencil icon-white"></i>
                Save
            </button>
        </div>

        <div class="span2 ">
            <button type="submit" class="btn btn-success" name="Add" value="Add">
                <i class="icon-plus icon-white "></i>
                Add a New Rec
            </button>
        </div>
            <div class="span2 offset1">


            <a href="../config_setup/" class="btn pull-right">
                <i class="icon-remove"></i>

                Cancel
            </a>
           </div>

    </div>
    <input type="hidden" name="reccount" value="<?=$NumUprec+$Addrec?>">
     <input type="hidden" name="Addrec" value="<?=$Addrec?>">

</form>


