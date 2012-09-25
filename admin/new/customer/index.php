<?php
/**
 *------------------------------------------------------------------------------
 * New Customer - Administrative Section
 *------------------------------------------------------------------------------
 *
 */
require_once('../../../includes/header.php');

$db = new db($config);

if($_SESSION['authLevel'] != 3) {
    gtfo($config);
}

if(count($_POST) > 0) {
   $query = 'INSERT INTO customers
       (customerName, addr1, addr2, city, state, zip, email1, email2)
       VALUES(:customerName, :addr1, :addr2, :city, :state, :zip, :email1, :email2)';
    $bind[':customerName'] = $_POST['customerName'];
    $bind[':addr1']  = $_POST['addr1'];
    $bind[':addr2']  = $_POST['addr2'];
    $bind[':city']   = $_POST['city'];
    $bind[':state']  = $_POST['state'];
    $bind[':zip']    = $_POST['zip'];
    $bind[':email1'] = $_POST['email1'];
    $bind[':email2'] = $_POST['email2'];
pprint($bind);
pprint($_POST);
    if($db -> execute($query, $bind)) {
        header('Location: ../../?a=s');
    }else{
        header('Location: ../../a=e');
    }
}

?>

        <div class="row">
            <div class="span6 offset3">
                <h1>Add a New Customer</h1>
            </div>
        </div>

        <form action="./" method="POST">
            <div class="row">
                <div class="span12">
                    <label for="name">Customer Name
                        <input type="text" class="span12" name='customerName'>
                    </label>
                </div>

                <div class="span6">
                    <label for="addr1">Address 1
                        <input type="text" id="addr1" class="span6" name="addr1">
                    </label>
                    <label for="addr1">Address 2
                        <input type="text" id="addr2" class="span6" name="addr2">
                    </label>
                    <label for="city">City
                        <input type="text" id="city" class="span6" name="city">
                    </label>
                    <div class="row">
                        <div class="span3">
                            <label for="state">State
                                <select name="state" id="state" class="span3">
<?php
foreach($state_list as $abbr => $state) {
?>
                                    <option value="<?php echo $abbr; ?>">
                                        <?php echo $state;?>

                                    </option>
<?php
}
?>
                                </select>
                            </label>
                        </div>
                        <div class="span3">
                            <label for="zip">Zip
                                <input type="text" class="span3" name="zip">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="span6">
                    <label for="email1">Email
                        <input type="email" class="span6" name="email1">
                    </label>
                    <label for="email1">Alternate Email
                        <input type="email" class="span6" name="email2">
                    </label>
                </div>
            </div>
            <div class="row">
                <div class="span10 offset1">
                    <button type="submit" class="btn btn-success">
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
