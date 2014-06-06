<?php
/**
 *------------------------------------------------------------------------------
 *Setup Configuration Parameter Page
 *------------------------------------------------------------------------------
 *
 */


/* function to determine if two values for the same item are the same
 * checking is performed on input and then unigue values are locked in
 * returns true for blank or a non unique value record
 * returns false if all ok
 * used for value and name fields
 */

function  checkvalue($config,$Field,$Val,$Grp,$SubGrp,$Recnum)
{
    $Rtcode=0;
    if ($Val=="" or $Val==NULL)
    {
        $Rtcode= 1;  // blank
    } else
    {
       $dbc = new db($config);
       $chkquery="Select *  from SysConfigDefaults where ConfigGroup='".$Grp."' and ConfigSubGroup='".$SubGrp."' and ".$Field."='".$Val."'";

       $Errrow = $dbc -> numRows($chkquery);
       $SelRow = $dbc -> fetchRow($chkquery);


      if ($Errrow>0 and $Recnum!=$SelRow['Recnum']) {$Rtcode=2;}
    }

  return $Rtcode;
}
require_once('../includes/pageStart.php');

//checkSystemSet($config);

require_once('../includes/header.php');
$db = new db($config);
//$SysID=$_SESSION['SysID'];
$LType="";
if (isset($_SESSION['LTYPE'])) { $LType=$_SESSION['LType']; }
$_SESSION['LType']=$LType;  
$PostFlag=false;
$PostLtype="";

$Pmode="";


if ($_POST) {
$PostLtype=$_POST['Fmtype'];
//need switch to restore original FmtypeS for inserts

$NumUprec=$_POST['reccount'];


  
      
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



}
$Precnum=array();
$Psubgrp=array();
$Pitem=array();
$Pvalue=array();
$Padj=array();

$errflag=array (array());
 $DBUpdateok=true;
 $CumErr=false;

$Uperr=false;


if ($PostFlag==true)
{
for ($i=0;$i<$NumUprec;$i++)
  {
      for ($j=0;$j<5;$j++)
      {
          $errflag[$i][$j]=0;
      }



    $Precnum[$i]=$_POST['recnum'.$i];
 // no error checking defined for recnum
    $Psubgrp[$i]=$_POST['subgrp'.$i];
// no error checking defined for subgrp
    $Pitem[$i]=$_POST['item'.$i];
    $errflag[$i][2]= checkvalue($config,"ItemName", $Pitem[$i],$PostLtype,$Psubgrp[$i],$Precnum[$i]);

    $Pvalue[$i]=$_POST['value'.$i];
    $errflag[$i][3]= checkvalue($config,"AssignedValue", $Pvalue[$i],$PostLtype,$Psubgrp[$i],$Precnum[$i]);

   $Padj[$i]=$_POST['adj'.$i];
   if (($Padj[$i]=="")  or (is_numeric($Padj[$i])==false))  {$Padj[$i]=1;}
  // if blank or not a number set to 1

   $CumErr=$CumErr || $errflag[$i][0] ||   $errflag[$i][1]   || $errflag[$i][2]   || $errflag[$i][3]     || $errflag[$i][4]          ;
// set cumulative error flag
   $c=$i+1;
   }
 

 // initialize poaotions for the new reccrds to the existing matriz'
    
    for ($k=0;$k<$Addrec+1;$k++) {
        $Psubgrp[$k+$c]="";
        $Pitem[$k+$c]="";
        $Pvalue[$k+$c]="";
        $Padj[$k+$c]="";
    }
}

// save records only if no errors
if($PostFlag==true and $CumErr==false)
   {


      for ($i=0;$i<$NumUprec;$i++) {

       //first look for existing record if precnum is defined

if ($Precnum[$i]!="")
 {

        $Upquery = "UPDATE SysConfigDefaults SET ItemName ='".$Pitem[$i]."',AdjFactor=".$Padj[$i]." WHERE Recnum =".$Precnum[$i];
   // echo($Upquery."<br>") ;
     try {
         $response = $db -> execute($Upquery);
          $DBUpdateok=$response;

         }
          catch (Exception $e)
         {
          throw new Exception;
            echo  "Error = ",0,$e;
            $Uperr=true;
         }
        }
   else  {
        if ($Pmode=="Update")
            { //echo("Insert");

              // first find last recnum
             $qryRecnum="Select Recnum from SysConfigDefaults order by Recnum desc limit 1";
             $result = $db -> fetchRow($qryRecnum);
             $ValRecnum=$result['Recnum']+1;

             $Inquery = "Insert into SysConfigDefaults (Recnum,ItemName,AssignedValue,ConfigSubGroup,ConfigGroup,AdjFactor)
                 Values (".$ValRecnum.",'".$Pitem[$i]."','".$Pvalue[$i]."','".$Psubgrp[$i]."','".$PostLtype."','".$Padj[$i]."')";
          //echo("||".$Inquery);
        try {
          $response = $db -> execute($Inquery);
                $DBUpdateok=$response;

            }
               catch (Exception $e)
            {
              throw new Exception;
                echo  " Error = ",0,$e;
                $Uperr=true;
             }
            }
       }

    }

 }
 ?>



        <div class="row">
            <h1 class="span8 offset2">System Configuration Parameters</h1>
        </div>


       <div id="accordionContainer">
           <div class="accordion-group" style="border:0px">
                <div class="accordion-heading">
                    <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordionContainer"
                    href="#collapse1">
                        <div class="row">
                            <h2 class="span8 offset3">System Hardware Listings</h2>
                        </div>
                    </a>
                </div>
                <div id="collapse1" class="accordion-body collapse <?php if($PostLtype =="HardwareSystem"){echo ' in';}  ?>">
                    <div class="accordion-inner">
                        <div class="row">
                            <div class="span12">

                             <?php
                                 $form = "HardwareSystem";
                                $_SESSION['LType'] = "HardwareSystem";
                                // $_POST['Submit1'] = "";
                                 include('Lists/index.php');
                             ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>




            <div class="accordion-group" style="border:0px">
                <div class="accordion-heading">
                    <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordionContainer"
                    href="#collapse2">
                        <div class="row">
                            <h2 class="span8 offset3">Sensor Hardware Listings</h2>

                        </div>
                    </a>
                </div>
                <div id="collapse2" class="accordion-body collapse <?php if($PostLtype =="HardwareSensor"){echo ' in';}  ?>">
                    <div class="accordion-inner">
                        <div class="row">
                            <div class="span12">

                                <?php
                                   $form = "HardwareSensor";
                                $_SESSION['LType'] = "HardwareSensor";
                                 //   $_POST['Submit1'] = "";
                                   include('Lists/index.php');
                                ?>

                            </div>

                        </div>

                    </div>

                </div>

           </div>






            <div class="accordion-group" style="border:0px">
                <div class="accordion-heading">
                    <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordionContainer"
                    href="#collapse3">
                        <div class="row">
                            <h2 class="span8 offset3">System Parameter Listings</h2>

                        </div>
                    </a>
                </div>
                <div id="collapse3" class="accordion-body collapse <?php if($PostLtype =="SystemParameter"){echo ' in';}  ?>">
                    <div class="accordion-inner">
                        <div class="row">
                            <div class="span12">

                               <?php
                                  $form = "SystemParameter";
                                  $_SESSION['LType'] = "SystemParameter";
                                 // $_POST['Submit1'] = "";
                                  include('Lists/index.php');
                               ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>



<?php
require_once('../includes/footer.php');
?>
