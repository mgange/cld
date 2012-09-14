<?php
/**
 *------------------------------------------------------------------------------
 * Profile Section Index Page
 *------------------------------------------------------------------------------
 *
 * Any user can access this section to manage their own profile information.
 * Users can edit their details or reset their password. Forms in this section
 * submit to this page where data validation and database interactions are
 * handled.
 *
 */
require_once('../includes/header.php');



if(count($_POST) > 0) {
    echo '<pre>';
    print_r($_POST);
    echo 'POST count: ' . count($_POST);
    echo '</pre>';
    // handle password resets
    if(isset($_POST['pass'])){
        if($_POST['pass'] !== $_POST['repass']) {
            header('Location: ./?action=password&a=pm'); // a = Alert  pm = Password Match
        }elseif($_POST['pass'] === '') {
            header('Location: ./?action=password&a=ef'); // a = Alert  ef = Empty Fields
        }else{echo "UPDATE PASSWORD";
           $db = new db($config);
           try {
               $query = 'UPDATE users SET password = :password where userID = :userID';
                $bind[':password'] = hashPassword($_POST['pass']);
                $bind[':userID'] = $_SESSION['userID'];
                $resp = $db -> execute($query, $bind);
                unset($bind);
                if($resp == 1){
                    header('Location: ../login/logout.php');
                }else{
                    header('Location: ../?a=pf'); // a = Alert   pf = Password Failure
                }
           }
           catch(Exception $e){
               echo '<pre>';
               echo $e;
               echo '</pre>';
           }
        }
    }

    // handle profile info changes
    if(isset($_POST['firstName']) || isset($_POST['lastName']) || isset($_POST['email'])) {
        $query = 'UPDATE users
                  SET firstName = :firstName,
                      lastName = :lastName,
                      email = :email
                  WHERE userID = :userID';
        $bind[':firstName'] = $_POST['firstName'];
        $bind[':lastName']  = $_POST['lastName'];
        $bind[':email']     = $_POST['email'];
        $bind[':userID']     = $_SESSION['userID'];
        echo '<pre>';print_r($bind);echo '</pre>';
        $db = new db($config);
        $resp = $db -> execute($query, $bind);
        unset($bind);
        if($resp == true) {
            header('Location: ./?a=s'); // a = Alert  s = Success
        }else{
            header('Location: ./?a=pe'); // a = Alert  pe = Profile Error
        }
    }
}

if(isset($_GET['action'])) {
    switch($_GET['action']) {
        case 'password':
?>
        <div class='row'>
            <div class='span8 offset2'>
                <h1>Reset Password</h1>
            </div>
        </div>
        <form action='./' method='POST'>
        <div class="row">
            <div class='span3 offset3'>
                <label>New Password <br>
                    <input class='span3' type='password' name="pass">
                </label>
            </div>
            <div class='span3'>
                <label>Retype New Password <br>
                    <input class='span3' type='password' name="repass">
                </label>
            </div>
        </div>
        <div class='row'>
            <div class='span6 offset3'>
                <a
                    class='btn pull-right'
                    href="./"
                >
                    <i class="icon-remove"></i>
                    Cancel
                </a>
                <button
                    class='btn btn-success pull-left'
                    type='submit'
                    name='submit'
                    value='submit'
                >
                    <i class="icon-ok icon-white"></i>
                    Save
                </button>
            </div>
        </div>
        </form>
<?php
            break;
        default:
            header('Location: ./');
            break;
    }
}else{
$query = 'SELECT * FROM users WHERE userID = :userID LIMIT 0,1';
$bind[':userID'] = $_SESSION['userID'];
$db = new db($config);
$results = $db -> fetchRow($query, $bind);

?>

        <div class="row">
            <h1 class='span8 offset2'>User Profile</h1>
        </div>
        <form action='./' method='POST' accept-charset='utf-8'>
            <div class="row">
                <label class='span3 offset3'>First Name <br>
                    <input
                        type='text'
                        name='firstName'
                        value='<?php echo $results['firstName'] ?>'
                        placeholder='First Name'
                    >
                </label>
                <label class='span3'>Last Name <br>
                    <input
                        type='text'
                        name='lastName'
                        value='<?php echo $results['lastName'] ?>'
                        placeholder='First Name'
                    >
                </label>
            </div>
            <div class="row">
                <label class='span6 offset3'>Email <br>
                    <input
                        class='span6'
                        type='text'
                        name='email'
                        value='<?php echo $results['email'] ?>'
                        placeholder='First Name'
                    >
                </label>
            </div>
            <div class="row">
                &nbsp;
            </div>
            <div class='row'>
                <div class='span8 offset2'>
                    <button class='btn btn-success pull-left' type='submit'>
                        <i class="icon-ok icon-white"></i>
                        Save
                    </button>
                    <a href="./?action=password" class="btn btn-warning pull-right">
                        <i class="icon-refresh icon-white"></i>
                        Reset Password
                    </a>
                </div>
            </div>
        </form>
<?php
}

?>
<?php
require_once('../includes/footer.php');
?>
