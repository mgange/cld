<?php
/**
 *------------------------------------------------------------------------------
 * Password Reset - Administrative Section
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
}elseif(isset($_POST['userID'])) {
    $userID = intval($_POST['userID']);
}else{
    gtfo($config);
}

$query = 'SELECT customerID, authLevel FROM users WHERE userID = :userID';
$bind[':userID'] = intval($userID);
$user = $db -> fetchRow($query, $bind);
$bind = null;

/**
 * Who can be here?
 */
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

    if($_POST['pass'] != $_POST['repass']) {
        header('Location: ../?id=' . intval($_POST['userID']) . '&a=pm');
    }else{
        if($_POST['pass'] == '') {
            header('Location: ../?id=' . intval($_POST['userID']) . '&a=ef');
        }
        $query = 'UPDATE users SET password = :password WHERE userID = :userID';
        $bind[':password'] = hashPassword($config, $_POST['pass']);
        $bind[':userID'] = intval($_POST['userID']);

        if($db -> execute($query, $bind)) {
            header('Location: ../?id=' . intval($_POST['userID']) . '&a=s');
        }else{
            header('Location: ../?id=' . intval($_POST['userID']) . '&a=pe');
        }
    }
}


require_once('../../../includes/header.php');


?>

        <div class="row">
            <div class="span6 offset3">
                <h1>Password Reset</h1>
            </div>
        </div>

        <form class="validate" action="./" method="POST">
            <div class="row">
                <div class="span3 offset3">
                    <label>New Password <br>
                        <input class="text span3" type="password" name="pass">
                    </label>
                </div>
                <div class="span3">
                    <label>Retype New Password <br>
                        <input class="text span3" type="password" name="repass">
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="span6 offset3">
                    <a class="btn pull-right" href="../?id=<?php echo intval($_GET['id']); ?>">
                        <i class="icon-remove"></i>
                        Cancel
                    </a>
                    <button class="btn btn-success pull-left" type="submit" value="submit">
                        <i class="icon-ok icon-white"></i>
                        Save
                    </button>
                </div>
            </div>
            <input type="hidden" name="userID" value="<?php echo intval($_GET['id']); ?>">
        </form>

<?php

require_once('../../../includes/footer.php');
?>
