<?php
/**
 *------------------------------------------------------------------------------
 * New User - Administrative Section
 *------------------------------------------------------------------------------
 *
 */
require_once('../../../includes/pageStart.php');

$db = new db($config);

if($_SESSION['authLevel'] < 2) {
    gtfo($config);
}
if (isset($_GET['BldID']))  {
     $PBuilingID=$_GET['BldID'];
     
    
} else {  header('Location: ../../?a=e'); //a = Alert  e = error(generic) }
}
if(count($_POST) > 0) {  
    // insert record for this system
        $query = 'INSERT INTO Alarm_Permissions (UserID, BuildingID, BuildAuthLevel, SystemAlarms, MaintenanceAlarms, AdminAlarms)
            VALUES(:UserID, :BuildingID, :BuildAuthLevel, :SystemAlarms, :MaintenanceAlarms, :AdminAlarms
)';
        $bind[':UserID'] = $_POST['userID'];
        $bind[':BuildingID'] = $PBuilingID;
        $bind[':BuildAuthLevel'] =  $_POST['authLevel'];
       
        $bind[':SystemAlarms'] = ((isset($_POST['checkOP']))? 1 : 0 );
        $bind[':MaintenanceAlarms'] = ((isset($_POST['checkMA']))? 1 : 0 );
        $bind[':AdminAlarms'] = ((isset($_POST['checkAD']))? 1 : 0 );

        if($db -> execute($query, $bind)) {
         echo("<h3><font color='blue'><b>User has been successfully added to this system</b></font></h3>");
        }else{
            header('Location: ../../?a=e'); //a = Alert  e = error(generic)
        }

     //   die(require_once('../../../includes/footer.php'));
    
}


require_once('../../../includes/header.php');


$query = "SELECT userID, lastName, firstName FROM users WHERE users.userID not in ".
         "(select UserID from Alarm_Permissions where BuildingID=".$_GET['BldID'].")";


$userlist = $db -> fetchAll($query);

?>

        <div class="row">
            <div class="span8 offset2">
                <h1>Add an Existing CLD Site User</h1>
            </div>
        </div>

        <form class="validate" action="existlist.php?BldID=<?=$PBuilingID ?>" method="POST">
            <div class="row">
                <div class="span6">
                    <label for="userID"><b>  Select from Listing of Existing Users </b>  
                        <select id="customerID" class="span6" name="userID">
<?php
foreach($userlist as $ul) {
?>
                            <option value="<?php echo $ul['userID']; ?>"<?php
if(isset($_GET['id']) && intval($_GET['id']) == $ul['userID']) {
    echo ' selected';
}
?>>
                              <?php echo $ul['firstName']." ".$ul['lastName']; ?>
                            </option>
<?php
}
?>
                        </select>
                        </select>
                    </label>
                </div>
                <div class="span6">
                    <label for="authLevel"><b>  Authorization Level </b>  
                        <select id="authLevel" class="span6" name="authLevel">
                            <option value="1">User</option>
                            <option value="2">Manager</option>
                           
                        </select>
                    </label>
                </div>               

               
            </div>
            <div class="row">
                 <div class="span5 align-center">
                 <h3><b> Alarm Authorization </b></h3></div>
          </div>
          <div class="row">
  
             <span class="span3 align-left"> 
                       <input type="checkbox" name="checkAD" value="1"><b>  Administration </b>
             </span> 
             <span class="span3 align-left"> 
                       <input type="checkbox" name="checkOP" value="1"><b>    Operational </b>
             </span>          
             <span class="span3 align-left">           
                       <input type="checkbox" name="checkMA" value="1"><b><font color="#888888">   Maintenance </b></font>
             </span>           
          </div>
            <BR>
            <div class="row">
                <div class="span10 offset1">
                    <button class="btn btn-success pull-left" type="submit">
                        <i class="icon-ok icon-white"></i>
                        Save
                    </button>
                    <a href="../../" class="btn pull-right">
                        <i class="icon-remove"></i>
                        Cancel
                    </a>
                </div>
            </div>
            <hr><hr>
            <div class="row">
                <div class="span10 offset4 align-center">
                    <a href="newforsys.php?BldID=<?=$PBuilingID ?>" style="color:white">
                    <button class="btn btn-success pull-left" type="submit">
                        <i class="icon-plus icon-white"></i>
                        Add New CLD Site User </a>
                    </button>
                    
                </div>
            </div>
            
        </form>

<?php
require_once('../../../includes/footer.php');
?>
