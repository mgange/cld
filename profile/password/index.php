<?php
/**
 *------------------------------------------------------------------------------
 * Password Reset - Profile Section
 *------------------------------------------------------------------------------
 *
 */
require_once('../../includes/pageStart.php');

$db = new db($config);

if(count($_POST) > 0) {
    if(! comparePasswords($_POST['pass'], $_POST['repass'])) {
        header('Location: ../?a=pe'); //a = Alert  pe = Password Error
    }else{
        $query = 'UPDATE users SET password = :password WHERE userID = :userID';
        $bind[':password'] = hashPassword($config, $_POST['pass']);
        $bind[':userID'] = intval($_SESSION['userID']);

        if($db -> execute($query, $bind)) {
            header('Location: ../../login/logout.php');
        }else{
            header('Location: ../?a=e'); // a = Alert  e = Error(generic)
        }
    }

}

require_once('../../includes/header.php');

?>
        <div class="row">
            <div class="span8 offset2">
                <h1>Reset Password</h1>
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
                    <a class="btn pull-right" href="../">
                        <i class="icon-remove"></i>
                        Cancel
                    </a>
                    <button class="btn btn-success pull-left" type="submit" value="submit">
                        <i class="icon-ok icon-white"></i>
                        Save
                    </button>
                </div>
            </div>
        </form>
<?php

require_once('../../includes/footer.php');
?>
