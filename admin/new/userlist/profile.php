<?php
/**
 *------------------------------------------------------------------------------
 * Profile Information - Administrative Section building specific userlist
 *------------------------------------------------------------------------------
 *
 */
require_once('../../../includes/pageStart.php');

$db = new db($config);

/**
 * Either get a userID from the url(if viewing a form) or POST(if handling a
 * submission), or kick the user out because there's no userID to edit.
 */
if(isset($_GET['id'])) {
    $userID = intval($_GET['id']);
    $PBuildingID=intval($_GET['BldID']);
}elseif(isset($_POST['userID'])) {
    $userID = intval($_POST['userID']);
    $PBuildingID=intval($_POST['BldID']);
}else{
    gtfo($config);
}

$query = 'SELECT * FROM users join Alarm_Permissions on users.userID=Alarm_Permissions.userID
          WHERE users.userID = :userID and BuildingID= :buildingID';
$bind[':userID'] = intval($userID);
$bind[':buildingID'] = intval($PBuildingID);
$user = $db -> fetchRow($query, $bind);
$bind = null;
$AuthLevel=$user['BuildAuthLevel'];

switch($_SESSION['authLevel']) {
    case 1:
        // Users(authLevel = 1) don't belong here
        gtfo($config);
        break;

    case 2:
        /**
          * Managers(authLevel = 2) must stay within their customer account,
          * can't edit admins, and can't edit older managers
          */
        if($_SESSION['customerID'] != $user['customerID']
            || $user['authLevel'] > 2
            || ($user['authLevel'] == 2 && $user['userID'] < $_SESSION['userID'])) {
            gtfo($config);
        }
        // And if it's your own profile just go to /profile
        if($user['userID'] == $_SESSION['userID']) {
       //    header('Location: ' . $config['base_domain'] . $config['base_dir'] . 'profile');
        }
        break;

    case 3:
        // Admins(authLevel = 3) can't manager older admins
        if($user['authLevel'] == 3 && $user['userID'] < $_SESSION['userID']) {
     //       gtfo($config);
        }
        // And if it's your own profile just go to /profile
        if($user['userID'] == $_SESSION['userID']) {
     //      header('Location: ' . $config['base_domain'] . $config['base_dir'] . 'profile');
        }
        break;
}

if(count($_POST) > 0) {
    $query = 'UPDATE users SET
        firstName = :firstName,
        lastName = :lastName,
        email = :email
        WHERE userID = :userID';
    $bind[':firstName'] = $_POST['firstName'];
    $bind[':lastName'] = $_POST['lastName'];
    $bind[':email'] = $_POST['email'];
    $bind['userID'] = $userID;
    
    if($db -> execute($query, $bind)) {
        
       $bind=Null;    
        header('Location: ../?a=s'); // a = Alert  s = Success   
        
       $query = 'UPDATE Alarms_Permissions SET
         BuildAuthLevel = :BuildAuthLevel
       
         WHERE userID = :userID and BuildingID=: buildingID';
         
         $bind[':userID'] = $userID;
         $bind[':BuildAuthLevel'] = $_POST['BuildAuthLevel'];
         $bind[':buildingID'] = $PBuildingID;
         $db -> execute($query, $bind); 
        
    }else{
        header('Location: ../?a=pe'); // a = Alert  pe = Profile Information
}
}


require_once('../../../includes/header.php');

?>

        <div class="row">
            <h1 class="span8 offset2">User Profile</h1>
        </div>

        <div class="row">
            <div class="span6 offset3">
                <a href="password?id=<?php echo intval($_GET['id']); ?>" class="btn btn-warning pull-right">
                        <i class="icon-refresh icon-white"></i>
                        Reset Password
                    </a>
            </div>
        </div>

        <form class="validate" action="new/userlist/profile.php" method="POST">
            <div class="row">
                <div class="span3 offset3">
                    <label for="name">First Name
                         <input class="text span3" type="text" name="firstName" value="<?php echo $user['firstName']; ?>">
                    </label>
                </div>
                <div class="span3">
                    <label for="name">Last Name
                         <input class="text span3" type="text" name="lastName" value="<?php echo $user['lastName']; ?>">
                    </label>
                </div>
                <div class="span6 offset3">
                    <label for="email">Email
                        <input class="email span6" type="email" name="email" value="<?php echo $user['email']; ?>">
                    </label>
                </div>
                
                 <div class="span6 offset3">
                    <label for="authLevel">Authorization Level for this Building
                        <select id="authLevel" class="span6" name="authLevel">
                            <?php 
                              if ($AuthLevel==1) {
                               echo("<option value='1' selected>User</option>");
                            } else { echo("<option value='1'>User</option>");}
                            
                             if ($AuthLevel==2) {
                               echo("<option value='2' selected>Manager</option>");
                            } else { echo("<option value='2'>User</option>");}
                          ?>
                           
                        </select>
                    </label>
                </div>      
            </div>
            <div class='row'>
                <div class='span6 offset3'>
                    <button class='btn btn-success pull-left' type='submit'>
                        <i class="icon-ok icon-white"></i>
                        Save
                    </button>
                    <a href="../../" class="btn pull-right">
                        <i class="icon-remove"></i>
                        Cancel
                    </a>
                </div>
            </div>
            <input type="hidden" name="userID" value="<?php echo $user['userID']; ?>">
            <input type="hidden" name="BldID" value="<?php echo $PBuildingID; ?>">
        </form>

        <br><br><br><br>

        <div class="row">
            <div class="span6 offset3">
                <a href="remove.php?id=<?php echo intval($_GET['id'])?>&BldID=<?php echo intval($PBuildingID)?>" class="btn btn-danger pull-right confirm">
                    <i class="icon-trash icon-white"></i>
                    Remove User From this Building
                </a>
            </div>
        </div>
<?php
require_once('../../../includes/footer.php');
?>
