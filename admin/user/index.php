<?php
/**
 *------------------------------------------------------------------------------
 * Profile Information - Administrative Section
 *------------------------------------------------------------------------------
 *
 */
require_once('../../includes/pageStart.php');
require_once('../../includes/header.php');

$db = new db($config);

/**
 * Either get a userID from the url(if viewing a form) or POST(if handling a
 * submission), or kick the user out because there's no userID to edit.
 */
if(isset($_GET['id'])) {
    $userID = intval($_GET['id']);
}elseif(isset($_POST['userID'])) {
    $userID = intval($_POST['userID']);
}else{
    gtfo($config);
}

$query = 'SELECT * FROM users WHERE userID = :userID';
$bind[':userID'] = intval($userID);
$user = $db -> fetchRow($query, $bind);
$bind = null;

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
           header('Location: ' . $config['base_domain'] . $config['base_dir'] . 'profile');
        }
        break;

    case 3:
        // Admins(authLevel = 3) can't manager older admins
        if($user['authLevel'] == 3 && $user['userID'] < $_SESSION['userID']) {
            gtfo($config);
        }
        // And if it's your own profile just go to /profile
        if($user['userID'] == $_SESSION['userID']) {
           header('Location: ' . $config['base_domain'] . $config['base_dir'] . 'profile');
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
        header('Location: ../?a=s'); // a = Alert  s = Success
    }else{
        header('Location: ../?a=pe'); // a = Alert  pe = Profile Information
}
}



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

        <form action="./" method="POST">
            <div class="row">
                <div class="span3 offset3">
                    <label for="name">First Name
                         <input class="span3" type="text" name="firstName" value="<?php echo $user['firstName']; ?>">
                    </label>
                </div>
                <div class="span3">
                    <label for="name">Last Name
                         <input class="span3" type="text" name="lastName" value="<?php echo $user['lastName']; ?>">
                    </label>
                </div>
                <div class="span6 offset3">
                    <label for="email">Email
                        <input class="span6" type="email" name="email" value="<?php echo $user['email']; ?>">
                    </label>
                </div>
            </div>
            <div class='row'>
                <div class='span6 offset3'>
                    <button class='btn btn-success pull-left' type='submit'>
                        <i class="icon-ok icon-white"></i>
                        Save
                    </button>
                    <a href="../" class="btn pull-right">
                        <i class="icon-remove"></i>
                        Cancel
                    </a>
                </div>
            </div>
            <input type="hidden" name="userID" value="<?php echo $user['userID']; ?>">
        </form>

        <br><br><br><br>

        <div class="row">
            <div class="span6 offset3">
                <a href="remove?id=<?php echo intval($_GET['id']); ?>" class="btn btn-danger pull-right confirm">
                    <i class="icon-trash icon-white"></i>
                    Remove User
                </a>
            </div>
        </div>
<?php
require_once('../../includes/footer.php');
?>
