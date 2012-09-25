<?php
/**
 *------------------------------------------------------------------------------
 * Edit Customer Info - Administrative Section
 *------------------------------------------------------------------------------
 *
 */

require_once('../../includes/header.php');

$db = new db($config);

if(count($_POST) > 0) {
    pprint($_POST);

    $query = 'UPDATE customers SET
        addr1  = :addr1,
        addr2  = :addr2,
        city   = :city,
        state  = :state,
        zip    = :zip,
        email1 = :email1,
        email2 = :email2
        WHERE customerID = :customerID';
    $bind[':addr1'] = $_POST['addr1'];
    $bind[':addr2'] = $_POST['addr2'];
    $bind[':city'] = $_POST['city'];
    $bind[':state'] = $_POST['state'];
    $bind[':zip'] = $_POST['zip'];
    $bind[':email1'] = $_POST['email1'];
    $bind[':email2'] = $_POST['email2'];
    $bind[':customerID'] = intval($_POST['customerID']);

    $response = $db -> execute($query, $bind);
    if($response) {
        header('Location: ../?a=s'); // TODO(Geoff Young): make the alert more vague
    }else{
        header('Location: ../?a=e'); // TODO(Geoff Young): make the alert more vague
    }

    die(require_once('../../includes/footer.php'));
}

?>

<h1>Customer Info</h1>
<?php

if(! isset($_GET['id']) || $_GET['id'] == '') {
    header('Location: ../?a=ua'); // a = Alert  ua = Unauthorized Access
}

switch($_SESSION['authLevel']) {
    case 1:
        header('Location: ../../?a=ua'); // a = Alert  ua = Unauthorized Access
        break;

    case 2:
        if($_SESSION['customerID'] != intval($_GET['id'])) {
            header('Location: ../?a=ua'); // a = Alert  ua = Unauthorized Access
        }
        break;
}

$query = 'SELECT * FROM customers WHERE customerID = :customerID';
$bind[':customerID'] = intval($_GET['id']);
$customer = $db -> fetchRow($query, $bind);

    ?>
<form action="./?id=<?php echo $_GET['id']; ?>" method="POST">
    <div class="row">
        <div class="span12">
            <label for="name">Customer Name
                <input type="text" class="span12" value="<?php echo $customer['customerName']; ?>">
            </label>
        </div>

        <div class="span6">
            <label for="addr1">Address 1
                <input type="text" id="addr1" class="span6" name="addr1" value="<?php echo $customer['addr1']; ?>">
            </label>
            <label for="addr1">Address 2
                <input type="text" id="addr2" class="span6" name="addr2" value="<?php echo $customer['addr2']; ?>">
            </label>
            <label for="city">City
                <input type="text" id="city" class="span6" name="city" value="<?php echo $customer['city']; ?>">
            </label>
            <div class="row">
                <div class="span3">
                    <label for="state">State
                        <select name="state" id="state" class="span3">
<?php
foreach($state_list as $abbr => $state) {
?>
                            <option value="<?php echo $abbr; ?>"<?php
                             if($customer['state'] == $abbr){ echo ' selected'; }
                                ?>>
                                <?php echo $state; ?>

                            </option>
<?php
}
?>
                        </select>
                    </label>
                </div>
                <div class="span3">
                    <label for="zip">Zip
                        <input type="text" class="span3" name="zip" value="<?php echo $customer['zip']; ?>">
                    </label>
                </div>
            </div>
        </div>

        <div class="span6">
            <label for="email1">Email
                <input type="email" class="span6" name="email1" value="<?php echo $customer['email1'] ?>">
            </label>
            <label for="email1">Alternate Email
                <input type="email" class="span6" name="email2" value="<?php echo $customer['email2'] ?>">
            </label>
        </div>
    </div>
    <div class="row">
        <div class="span10 offset1">
            <button type="submit" class="btn btn-success">
                <i class="icon-ok icon-white"></i>
                Save
            </button>
            <a href="../" class="btn pull-right">
                <i class="icon-remove"></i>
                Cancel
            </a>
        </div>
    </div>
    <input type="hidden" name="customerID" value="<?php echo $customer['customerID']; ?>">
</form>
<?php

require_once('../../includes/footer.php');
?>
