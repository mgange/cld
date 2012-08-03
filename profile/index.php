<?php
require_once('../includes/header.php');
?>
<?php

if(isset($_POST['submit'])) {
    // TODO(Geoff Young): handle form submissions
}

if(isset($_GET['action'])) {
    switch($_GET['action']) {
        case 'password':
?>
        <form action='./' method='POST'>
        <div class='row'>
            <div class='span8 offset2'>
                <h1>Reset Password</h1>
            </div>
        </div>
        <div class='row'>
            <div class='span3 offset3'>
                <label>New Password <br>
                    <input class='span3' type='password'>
                </label>
            </div>
            <div class='span3'>
                <label>Retype New Password <br>
                    <input class='span3' type='password'>
                </label>
            </div>
        </div>
        <div class='row'>
            <div class='span6 offset3'>
                <button 
                    class='btn pull-right' 
                    type='submit' 
                    name='submit' 
                    value='submit'
                >
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
                    <button class='btn pull-left' type='submit'>
                        <i class="icon-ok"></i>
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