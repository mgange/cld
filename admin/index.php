<?php
/**
 *------------------------------------------------------------------------------
 * Administrative Section Index Page
 *------------------------------------------------------------------------------
 *
 * Users with sufficient permission will be able to access this page to manage
 * the site. Site-wide administrators (level 3) will be able to view and edit 
 * customer information and information of individual user accounts. Customer 
 * administrators (Level 2) will be able to manage their own customer 
 * information and the user accounts associated with that customer account. 
 * Management of other accounts at the same permission level will be allowed 
 * based on account creation date/time.
 * e.g. a site-wide administrator will be able to edit user accounts created
 * after their own, but not after.(It's entirely possible this will change.)
 *
 * PHP version 5.3.0
 *
 */
require_once('../includes/header.php');
if($_SESSION['authLevel'] < 2) {
    header('Location: ../?a=ua'); // a = Alert  ua = Unauthorized Access
}
// echo '<pre>';print_r($_SESSION);echo '</pre>';
// handle form submissions

// display data
if(isset($_GET['action'])) {
    $db = new db($config);
    
    switch($_GET['action']) {
        case 'customer':
            if(isset($_GET['CID'])) {
                /* Confirm that the user has permission to edit this information */
                if($_SESSION['authLevel'] !== 3 
                && $_SESSION['customerID'] !== $_GET['CID']
                ) {
                    header('Location: ../?a=ua'); // a = Alert  ua = Unauthorized Access
                }
                $CID = intval($_GET['CID']);
                $query = 'SELECT * 
                    FROM customers 
                    WHERE customerID = :customerID 
                    LIMIT 0,1';
                $bind[':customerID'] = $_SESSION['customerID'];
                $customer = $db -> fetchRow($query, $bind);
                unset($bind);
            }else{
                if($_SESSION['authLevel'] < 3) { // Check if the user has permission to make new customer accounts
                    header('Location: ./?a=ua'); // a = Alert  ua = Unauthorized Access
                }else{
                    $customer = array();
                }
            }
?>
        <h1>Customer Information</h1>
        <form action="./" method="POST">
<?php if(isset($_GET['CID'])) { ?>
            <input type="hidden" name="customerID" value="<?php echo $customer['customerID']; ?>">
<?php } ?>
            <div class="row">
                <div class="span8 offset2">
                    <label for="customerName">Customer Name <br>
                        <input 
                            id="customerName" 
                            class="span8" 
                            type="text"
                            name="customerName" 
                            value="<?php echo $customer['customerName']; ?>"
                        >
                    </label>
                </div>

            </div>
            <div class="row">
                <div class="span5 offset1">
                    <label for="addr1">Address <br>
                        <input 
                            id="addr1"
                            class="span5" 
                            type="text"
                            name="addr1"
                            value="<?php echo $customer['addr1']; ?>"
                        >
                    </label>
                    
                    
                    <label for="addr1">Address 2 <br>
                        <input 
                            id="addr2"
                            class="span5" 
                            type="text"
                            name="addr2"
                            value="<?php echo $customer['addr2']; ?>"
                        >
                    </label>
                    
                    <label for="city">City <br>
                        <input 
                            id="city"
                            class="span5" 
                            type="text"
                            name="city"
                            value="<?php echo $customer['city']; ?>"
                        >
                    </label>

                    <label for="state">State <br>
                        <select 
                            id='state'
                            class="span5" 
                            name="state"
                        >
    <?php
    foreach($state_list as $abbreviation => $stateName) {?>
                            <option 
                                value="<?php echo $abbreviation; ?>"
<?php /* Pre-select a state when possible */
if($customer['state'] == $abbreviation) {
    echo ' selected';
}
?>
                            >
                            <?php echo $stateName;?>
                            </option>
    <?php } ?>
                        </select>
                    </label>

                    <label for="zip">Zip Code <br>
                        <input 
                            id="zip"
                            class="span5"
                            type="text"
                            name="zip"
                            value="<?php echo $customer['zip']; ?>"
                            maxlength="5"
                        >
                    </label>
                </div>
                <div class="span5">

                    <label for="email1">Email <br>
                        <input 
                            id="email1"
                            class="span5"
                            type="text"
                            name="email1"
                            value="<?php echo $customer['email1']; ?>"
                        >
                    </label>

                    <label for="email2">Alternate Email <br>
                        <input 
                            id="email2"
                            class="span5"
                            type="text"
                            name="email2"
                            value="<?php echo $customer['email2']; ?>"
                        >
                    </label>
                </div>
            </div>

            <div class="row">
                <div class="span6 offset3">
                    <a class="btn pull-right" href="./">
                        <i class="icon-remove"></i>
                        Cancel
                    </a>
                    <button class="btn btn-success pull-left">
                        <i class="icon-ok icon-white"></i>
                        Save
                    </button>
                </div>
            </div>
        </form>
<?php
echo '<pre>';print_r($customer);echo '</pre>';
            break;

        case 'user':
            echo 'EDIT USER INFO';

        default:
            break;
    }
}else{
    switch($_SESSION['authLevel']) {
        case 1: // authLevel 1 people don't belong here.
            header('Location: ../?a=ua'); // a = Alert  ua = Unauthorized Access
            break;
        case 2:
            $db = new db($config);

            // Get company info and company users
            $query = 'SELECT * FROM customers WHERE customerID = :customerID LIMIT 0,1';
            $bind[':customerID'] = $_SESSION['customerID'];
            $db = new db($config);
            $customer = $db -> fetchRow($query, $bind);

            // Get the companies users
            $query = 'SELECT * FROM users WHERE customerID = :customerID';
            $bind['customerID'] = $_SESSION['customerID'];
            $users = $db -> fetchAll($query, $bind);

            // Display it all
    ?>
        <div class="row">
            <h1 class="span10"><?php echo $customer['customerName'] ?></h1>
            <br>
            <a class="btn pull-right" href="./?action=customer&CID=<?php echo $customer['customerID']; ?>">
                <i class="icon-edit"></i>
                Edit Customer Info
            </a>
        </div>
        <div class="row">
            <p class="span6 offset1"><?php echo $customer['addr1']; ?></p>
            <p class="span4">
                <a href="mailto:<?php echo $customer['email1']; ?>"><?php echo $customer['email1']; ?></a>
            </p>
        </div>
        <div class="row">
            <p class="span6 offset1"><?php echo $customer['addr2']; ?></p>
            <p class="span4">
                <a href="mailto:<?php echo $customer['email2']; ?>"><?php echo $customer['email2']; ?></a>
            </p>
        </div>
        <div class="row">
            <p class="span6 offset1"><?php 
            echo $customer['city'] . ', ' . $customer['state'] . ' ' . $customer['zip']; 
            ?></p>
        </div>

        <table class="table table-striped table-bordered">
            <tr>
                <th>Username</th>
                <th>First Name</th>
                <th>Last Name</th>
            </tr>
    <?php
    foreach($users as $userInfo) {
    ?>
            <tr>
                <td>
                    <a 
<?php if($userInfo['userID'] < $_SESSION['userID']) { ?>
                    href="./?action=user&UID=<?php echo $userInfo['userID']; ?>" 
                    title="Edit user information<?php
    // If a name is available then us it in the title attribute
    if($userInfo['firstName'] != '' && $userInfo['lastName'] != '') {
        echo ' ' . $userInfo['firstName'] . ' ' . $userInfo['lastName'];
    }
    ?>
    "
<?php } ?>
    >
                    <?php echo $userInfo['username']; ?>
    </a>
    <?php // Label Managers
        if($userInfo['authLevel'] == 2) {
    ?>
                    <span class="label label-info">Manager</span>
    <?php // Label site Admins
        }elseif($userInfo['authLevel'] == 3) {
    ?>
                    <span class="label label-warning">Administrator</span>
    <?php
        }
    ?>
                </td>
                <td><?php echo $userInfo['firstName']; ?></td>
                <td><?php echo $userInfo['lastName']; ?></td>
            </tr>
    <?php
    }
    ?>
        </table>
    <?php
            echo '<pre>Company '; print_r($customer); echo '</pre>';
            echo '<pre>Users ';   print_r($users);   echo '</pre>';
            break;
        case 3:
            // get all companies and all users
        echo 'not ready yet';
            break;

        default:
            break;
    }
}
require_once('../includes/footer.php');
?>