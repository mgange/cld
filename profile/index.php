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
require_once('../includes/pageStart.php');


if(count($_POST) > 0) {
    $query = 'UPDATE users
              SET firstName = :firstName,
                  lastName = :lastName,
                  email = :email
              WHERE userID = :userID';
    $bind[':firstName'] = $_POST['firstName'];
    $bind[':lastName']  = $_POST['lastName'];
    $bind[':email']     = $_POST['email'];
    $bind[':userID']     = $_SESSION['userID'];
    $db = new db($config);
    $resp = $db -> execute($query, $bind);
    unset($bind);
    if($resp == true) {
        header('Location: ./?a=s'); // a = Alert  s = Success
    }else{
        header('Location: ./?a=pe'); // a = Alert  pe = Profile Error
    }
}


require_once('../includes/header.php');


$query = 'SELECT * FROM users WHERE userID = :userID LIMIT 0,1';
$bind[':userID'] = $_SESSION['userID'];
$db = new db($config);
$results = $db -> fetchRow($query, $bind);

?>

        <div class="row">
            <h1 class='span8 offset2'>User Profile</h1>
        </div>

        <div class="row">
            <div class="span6 offset3">
                <a href="password?id=<?php echo intval($_GET['id']); ?>&a=pww" class="btn btn-warning pull-right">
                        <i class="icon-refresh icon-white"></i>
                        Reset Password
                    </a>
            </div>
        </div>

        <form class="validate" action='./' method='POST' accept-charset='utf-8'>
            <div class="row">
                <label class='span3 offset3'>First Name <br>
                    <input
                        class="text span3"
                        type='text'
                        name='firstName'
                        value='<?php echo $results['firstName'] ?>'
                    >
                </label>
                <label class='span3'>Last Name <br>
                    <input
                        class="text span3"
                        type='text'
                        name='lastName'
                        value='<?php echo $results['lastName'] ?>'
                    >
                </label>
            </div>
            <div class="row">
                <label class='span6 offset3'>Email <br>
                    <input
                        class='email span6'
                        type='email'
                        name='email'
                        value='<?php echo $results['email'] ?>'
                    >
                </label>
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
        </form>

<?php
require_once('../includes/footer.php');
?>
