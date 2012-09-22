<?php
/**
 *------------------------------------------------------------------------------
 * New User - Administrative Section
 *------------------------------------------------------------------------------
 *
 */
require_once('../../../includes/header.php');

$db = new db($config);

/**
 * Only site admins are allowed to add new users(??).
 */
if($_SESSION['authLevel'] != 3) {
    gtfo($config);
}

if(count($_POST) > 0) {
    $query = 'SELECT username FROM users WHERE username = :username';
    $UNBind[':username'] = $_POST['username'];
    if($db -> numRows($query, $UNBind) > 0) {
        header('Location: ./?a=une'); //a = Alert  une = Username Error
    }elseif(! comparePasswords($_POST['pass'], $_POST['repass'])) {
        header('Location: ./?a=pe'); //a = Alert  pe = Password Error
    }else{
        $query = 'INSERT INTO users (customerID, username, password, email, firstName, lastName, authLevel)
            VALUES(:customerID, :username, :password, :email, :firstName, :lastName, :authLevel)';
        $bind[':customerID'] = $_POST['customerID'];
        $bind[':username'] = $_POST['username'];
        $bind[':password'] = hashPassword($_POST['password']);
        $bind[':email'] = $_POST['email'];
        $bind[':firstName'] = $_POST['firstName'];
        $bind[':lastName'] = $_POST['lastName'];
        $bind[':authLevel'] = $_POST['authLevel'];

        if($db -> execute($query, $bind)) {
            header('Location: ../../?a=s'); //a = Alert  s = Success(generic)
        }else{
            header('Location: ../../?a=e'); //a = Alert  e = error(generic)
        }

        die(require_once('../../../includes/footer.php'));
    }
}

$query = 'SELECT customerID, customerName FROM customers WHERE 1';
$customers = $db -> fetchAll($query);

?>

        <div class="row">
            <div class="span6 offset3">
                <h1>new user</h1>
            </div>
        </div>

        <form action="./" method="POST">
            <div class="row">
                <div class="span6">
                    <label for="customerID">Customer Account
                        <select id="customerID" class="span6" name="customerID">
<?php
foreach($customers as $cust) {
?>
                            <option value="<?php echo $cust['customerID']; ?>"<?php
if(isset($_GET['id']) && intval($_GET['id']) == $cust['customerID']) {
    echo ' selected';
}
?>>
                                <?php echo $cust['customerName']; ?>
                            </option>
<?php
}
?>
                        </select>
                        </select>
                    </label>
                </div>
                <div class="span6">
                    <label for="username">User Name
                        <input id="username" class="span6" type="text" name="username">
                    </label>
                </div>

                <div class="span6">
                    <label for="firstName">First Name
                        <input id="firstName" type="text" class="span6" name="firstName">
                    </label>
                </div>
                <div class="span6">
                    <label for="lastName">Last Name
                        <input id="lastName" type="text" class="span6" name="lastName">
                    </label>
                </div>

                <div class="span6">
                    <label for="email">Email
                        <input class="span6" type="text" name="email">
                    </label>
                </div>
                <div class="span6">
                    <label for="authLevel">Authorization Level
                        <select id="authLevel" class="span6" name="authLevel">
                            <option value="1">User</option>
                            <option value="2">Manager</option>
                            <option value="3">Administrator</option>
                        </select>
                    </label>
                </div>
            </div>

            <div class="row">
                <div class="span6">
                    <label for="pass">Password
                        <input id="pass" class="span6" type="password" name="pass">
                    </label>
                </div>
                <div class="span6">
                    <label for="repass">Re-Type Password
                        <input id="repass" class="span6" type="password" name="repass">
                    </label>
                </div>
            </div>

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
        </form>

<?php
require_once('../../../includes/footer.php');
?>
