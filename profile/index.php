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

        <form action='./' method='POST' accept-charset='utf-8'>
            <div class="row">
                <label class='span3 offset3'>First Name <br>
                    <input
                        class="span3"
                        type='text'
                        name='firstName'
                        value='<?php echo $results['firstName'] ?>'
                        placeholder='First Name'
                    >
                </label>
                <label class='span3'>Last Name <br>
                    <input
                        class="span3"
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
            <div class='row'>
                <div class='span6 offset3'>
                    <button class='btn btn-success pull-left' type='submit'>
                        <i class="icon-ok icon-white"></i>
                        Save
                    </button>
                    <a href="password" class="btn pull-right">
                        <i class="icon-remove"></i>
                        Cancel
                    </a>
                </div>
            </div>
        </form>

<?php
require_once('../includes/footer.php');
?>
