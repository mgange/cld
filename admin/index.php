<?php
/**
 *------------------------------------------------------------------------------
 * Administrative Section Index Page
 *------------------------------------------------------------------------------
 *
 * Users with sufficient permission will be able to access this page to manage
 * the site. Site-wide administrators (level 3) will be able to view and edit
 * customer information and information of individual user accounts. Customer
 * administrators (Level 2) will be able to manage their own customer
 * information and the user accounts associated with that customer account.
 * Management of other accounts at the same permission level will be allowed
 * based on account creation date/time.
 * e.g. a site-wide administrator will be able to edit user accounts created
 * after their own, but not after.(It's entirely possible this will change.)
 *
 * PHP version 5.3.0
 *
 */
require_once('../includes/pageStart.php');

$db = new db($config);

if($_SESSION['authLevel'] < 2) {
    gtfo($config);
}

// post backs
$numRec=0;
$SOK=0;



if (count($_POST)>0) {

$numRec=$_POST['numRec'];
for ($i=0;$i<$numRec;$i++) {
 $bind[':checkAD']   =(isset($_POST['checkAD'.$i]))?1:0;
 $bind[':checkMA']   =(isset($_POST['checkMA'.$i]))?1:0;
 $bind[':checkOP']   =(isset($_POST['checkOP'.$i]))?1:0;
 $bind[':Recnum']    =$_POST['Recnum'.$i];

  $upquery="update Alarm_Permissions set SystemAlarms=:checkOP, AdminAlarms=:checkAD,
            MaintenanceAlarms=:checkMA where Recnum=:Recnum";

  //echo($upquery);
    $SOK=$db-> execute($upquery,$bind);

}

}
switch($_SESSION['authLevel']) {
    case 1:
        header('Location: ../?a=ua'); // a = Alert  ua = Unauthorized Access
        break;
    case 2:
        $where = 'WHERE customerID = ' . $_SESSION['customerID'];
        break;
    case 3:
        $where = 'WHERE 1';
        break;
}
?>

<?php
$query = 'SELECT * FROM customers ' . $where;
$customers = $db -> fetchAll($query);

//$query = 'SELECT * FROM users ' . $where . ' AND active = 1';
//$users = $db -> fetchAll($query);


$query = 'SELECT * FROM buildings join SystemConfig on SystemConfig.BuildingID=buildings.BuildingID ' . $where;
$buildings = $db -> fetchAll($query);

$query = 'SELECT * FROM MaintainResource WHERE 1';
$maintainers = $db -> fetchAll($query);

require_once('../includes/header.php');
?>


        <div class="row">
            <h1 class="span8 offset2">Site Administration</h1>
<?php
if($_SESSION['authLevel'] > 2) {
?>
            <h2 class="span8 offset2">Customers</h2>
<?php
}
?>
        </div>

<div id="accordion">
<?php
foreach($customers as $cust) {
  $PBuildingID = -1;
?>
        <div class="accordion-group">
            <div class="accordion-heading">
                <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordion"
                    href="#collapse<? echo $cust['customerID']; ?>">
                <h3><?php echo $cust['customerName']; ?></h3>
                </a>
            </div>
            <div id="collapse<?php echo $cust['customerID']; ?>" class="accordion-body collapse<?php if(count($customers) == 1){echo ' in';} ?>">
                <div class="accordion-inner">
                    <div class="row">
                        <?php $SOK=0; ?>
                        <div class="span5">
                            <p><?php echo $cust['addr1']; ?></p>
                            <p><?php echo $cust['addr2']; ?></p>
                            <p><?php echo $cust['city'].', '.$cust['state'].' '.$cust['zip']; ?></p>
                        </div>
                        <div class="span5">
                            <p><a href="mailto:<?php echo $cust['email1']; ?>"><?php echo $cust['email1']; ?></a></p>
                            <p><a href="mailto:<?php echo $cust['email2']; ?>"><?php echo $cust['email2']; ?></a></p>
                            <a href="customer?id=<?php echo $cust['customerID']; ?>" class="btn">
                            <i class="icon-edit"></i>
                            Edit Customer Info
                        </a>
                        </div>
                    </div>
                    <br><br>
<!-- Header row -->

                    <div class="row">
                        <div class="span3" align-left>
                            <h4>Building - System Name</h4>

                        </div>
                        <div class="span3" align-left>
                            <h4>Users</h4>

                        </div>
                        <div class="span5" align-left>
                            <h4>Alarms</h4>

                        </div>
                    </div>

  <!-- Systems row -->

          
         <?php
              foreach($buildings as $building) {

              if($building['CustomerID'] == $cust['customerID']) {
                       //  $PBuildingID=$building['buildingID'];
                       // find users for this bulding as defined in alarm permissions
                  
                   $query = "SELECT * FROM Alarm_Permissions join users
                             on users.userID=Alarm_Permissions.UserID".
                        " where Alarm_Permissions.SysID=".$building['SysID']."  and users.active = 1";
                     // echo($query);
                      $users = $db -> fetchAll($query);
                  
                  
                  
             echo '   <div class="row"> ';
             echo '   <div class="span3 align-left"><p><b>'.$building['buildingName']." - ".$building['SysName'].'</b></p>';
                 if($_SESSION['authLevel'] >= 2) {
                     
                     echo '  <a href="new/userlist/existlist.php?BldID='.$building['buildingID'].'&SysID='.$building['SysID'].'" class="btn 
                              btn-small btn-success">
                                <i class="icon-plus icon-white"></i>
                                Add User
                            </a> ';
                   }
                     echo '  </span> ';
    
                 

                echo ' </div>';
                    
                    
             echo '   <div class="span3 align-left">';  
                   $i=0;
                 echo'   <form  id="Alarm" method="POST" action="./#collapse'.$cust['customerID'].'">';
                     foreach ($users as $userlist) {
                         
                         
                         echo'  <div>';
                         echo ' <span class="span3 align-left">';
                         echo ' <a href="new/userlist/profile.php?id='.$userlist['userID']."&BldID=".$building['buildingID'].'&SysID='.$building['SysID'].'">';
                         echo ($userlist['firstName'].' '.$userlist['lastName']);
                         echo '</a>';
                          if($userlist['BuildAuthLevel'] == 2) {
                           
                           echo '    <span class="label label-info">Manager</span>';            
                             }
                           if($userlist['authLevel'] == 3) 
                           {
                            echo '     <span class="label label-important">Site Admin</span>';
                            }
                         echo ' </div>';                 
                     $i=$i+1;
                                    
                     }
                   echo '        </div> ';
                   
                 
               //   echo'   <form  id="Alarm" method="POST" action="./#collapse'.$cust['customerID'].'">';
                   $i=0;
                  echo '    <div class="span5 align-left"> ';
                        foreach ($users as $userlist) {
                    If ($userlist['AdminAlarms']==1) {
                            echo("<input type='checkbox' name='checkAD".$i."' value='1' checked><b> 
                             Administration </b>");
                            } else {
                            echo("<input type='checkbox' name='checkAD".$i."' value='0'><b> Administration 
                       </b>");
                                   }



                            If ($userlist['SystemAlarms']==1) {
                            echo("<input type='checkbox' name='checkOP".$i."' value='1' checked><b> Operational 
                       </b>");
                            } else {
                            echo("<input type='checkbox' name='checkOP".$i."' value='0'><b> Operational </b>");
                                   }

                            If ($userlist['MaintenanceAlarms']==1) {
                            echo("<input type='checkbox' name='checkMA".$i."' value='1' checked><b> Maintenance 
                     </b>");
                            } else {
                            echo("<input type='checkbox' name='checkMA".$i."' value='0'><b> Maintenance </b>");
                                   }


                            echo("<input type='hidden' name='Recnum".$i."' value='".$userlist['Recnum']."'>");
                          /*  echo '<span class="span4 align-left">
                             <button type="submit" class="btn btn-success">
                                <i class="icon-plus icon-white"></i>
                                Save Alarm Updates
                                </button> '; */
                         $i=$i+1;
                       
                    

                            if ($SOK==1) {echo("<font color='blue'><b>Alarms Updated</b></font>");} 
                     echo'   <BR> ';
                   }  
                    echo'   <BR> ';
                    
                     echo("<input type='hidden' name='numRec' value='".$i."'>");
                  if ($i>0) {
                    echo '<span class="span4 align-left">
                             <button type="submit" class="btn btn-success">
                                <i class="icon-plus icon-white"></i>
                                Save Alarm Updates
                                </button> '; 
                    }
                   echo '       </form> ';
                  echo '       </div> ';
                echo '       </div> ';
                       }
                    }
                 ?>


            <!-- </div>  -->


       </div>  <!-- Accordion Inner -->
      </div>  <!-- Accordion Body -->


   </div>   <!-- Close accordion group -->
<?php
}    // close for customer loop
?>
</div>   <!-- Close accordion  -->
<?php
if($_SESSION['authLevel'] == 3) {
?>

        <div class="row">
            <br>
            <div class="span3 offset1">
                <a href="new/customer" class="btn btn-success pull-left">
                    <i class="icon-plus icon-white"></i>
                    Add New Customer
                </a>
            </div>
        </div>
<br>
<hr><hr>
<?php
    }
    ?>







 <!-- Maintainers  -->
<div class="well">
<?php



if($_SESSION['authLevel'] > 2) {
?>
          <div class="row">
            <h2 class="span8 offset2">Maintainers</h2>
          </div>
<?php

foreach($maintainers as $resource) {
?>
            <div class="row">
                <h4 class="span3"><?=$resource['Name']?></h4>
                <h5 class="span4">
                    <?=$resource['Company']?>
                    <small>&#40;<?=$resource['Category']?>&#41;</small>
                </h5>

                <div class="btn-group span2 offset1">
                    <a href="maintainer/edit?id=<?=$resource['Recnum']?>" class="btn btn-mini">
                        <i class="icon-edit"></i>
                        Edit
                    </a>
                    <a href="maintainer/remove?id=<?=$resource['Recnum']?>" class="btn btn-mini btn-danger confirm">
                        <i class="icon-remove icon-white"></i>
                        Delete
                    </a>
                </div>

            </div>
<?php
}
?>      <div class="row">
               <div class="span3 offset1">
            <a href="maintainer/new" class="btn btn-success pull-left ">
                <i class="icon-plus icon-white"></i>
                Add New Maintainer
            </a>
               </div>
        </div>
<?php }
?>
</div> <!-- close .well -->
<?php
require_once('../includes/footer.php');
?>