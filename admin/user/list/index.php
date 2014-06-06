<?php
/**
 *------------------------------------------------------------------------------
 * Profile Information - User listing
 *------------------------------------------------------------------------------
 *
 */
require_once('../../../includes/pageStart.php');

$db = new db($config);

/**
 * Either get a userID from the url(if viewing a form) or POST(if handling a
 * submission), or kick the user out because there's no userID to edit.
 */


$query = 'SELECT * FROM users WHERE Active=1';

$users = $db -> fetchall($query);


if ($_SESSION['authLevel']<>3) {
   
        // Users/managers(authLevel = 1/2) don't belong here
        gtfo($config);
        break;
}

require_once('../../../includes/header.php');
?>    
 <div class="row">
            <h1 class="span8 offset2">User Profiles</h1>
        </div>       


<?php 
      foreach ($users as $user) {

?>

          

      
         <div class="row">
                <div class="span3 offset3">
                   <h3>
                       <a href="../?id=<?php echo $user['userID'] ?>"><?php echo $user['firstName']." ".$user['lastName']; ?></a>
                   </h3>
                </div>   
        </div>
                
<?php  }
require_once('../../../includes/footer.php');
?>
