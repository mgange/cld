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
require_once('../includes/pageStart.php');

$db = new db($config);

if($_SESSION['authLevel'] < 2) {
    gtfo($config);
}

switch($_SESSION['authLevel']) {
    case 1:
        header('Location: ../?a=ua'); // a = Alert  ua = Unauthorized Access
        break;
    case 2:
        $where = 'WHERE customerID = ' . $_SESSION['customerID'];
        break;
    case 3:
        $where = 'WHERE 1';
        break;
}
?>

<?php
$query = 'SELECT * FROM customers ' . $where;
$customers = $db -> fetchAll($query);

$query = 'SELECT * FROM users ' . $where . ' AND active = 1';
$users = $db -> fetchAll($query);

$query = 'SELECT * FROM buildings ' . $where;
$buildings = $db -> fetchAll($query);

$query = 'SELECT * FROM MaintainResource WHERE 1';
$maintainers = $db -> fetchAll($query);

require_once('../includes/header.php');
?>


        <div class="row">
            <h1 class="span8 offset2">Site Administration</h1>
<?php
if($_SESSION['authLevel'] > 2) {
?>
            <h2 class="span8 offset2">Customers</h2>
<?php
}
?>
        </div>

        <div id="accordion">
<?php
foreach($customers as $cust) {
?>
        <div class="accordion-group">
            <div class="accordion-heading">
                <a class="accordion-toggle"
                    data-toggle="collapse"
                    data-parent="#accordion"
                    href="#collapse<? echo $cust['customerID']; ?>">
                <h3><?php echo $cust['customerName']; ?></h3>
                </a>
            </div>
            <div id="collapse<?php echo $cust['customerID']; ?>" class="accordion-body collapse<?php if(count($customers) == 1){echo ' in';} ?>">
                <div class="accordion-inner">
                    <div class="row">
                        <div class="span5">
                            <p><?php echo $cust['addr1']; ?></p>
                            <p><?php echo $cust['addr2']; ?></p>
                            <p><?php echo $cust['city'].', '.$cust['state'].' '.$cust['zip']; ?></p>
                        </div>
                        <div class="span5">
                            <p><a href="mailto:<?php echo $cust['email1']; ?>"><?php echo $cust['email1']; ?></a></p>
                            <p><a href="mailto:<?php echo $cust['email2']; ?>"><?php echo $cust['email2']; ?></a></p>
                            <a href="customer?id=<?php echo $cust['customerID']; ?>" class="btn">
                            <i class="icon-edit"></i>
                            Edit Customer Info
                        </a>
                        </div>
                    </div>

                    <br><br>

                    <div class="row">
                        <div class="span5">
                            <h4>Buildings</h4>
<?php
foreach($buildings as $building) {
    if($building['CustomerID'] == $cust['customerID']) {
?>
                            <span class="span5"><p><?php echo $building['buildingName']; ?></p></span>
<?php
    }
}
if($_SESSION['authLevel'] == 3) {
?>
                        <div class="span5">
                            <br>
                            <a href="<?php echo $config['base_domain'] . $config['base_dir']; ?>setup/new_system" class="btn btn-small btn-success">
                                <i class="icon-plus icon-white"></i>
                                Add Building
                            </a>
                        </div>
<?php
}
?>
                        </div>

                        <div class="span5">
                        <h4>User</h4>
<?php
            foreach($users as $user) {
                if($user['customerID'] == $cust['customerID']) {
?>
                            <span class="span5">
                                <a href="user?id=<?php echo $user['userID']; ?>">
                                    <?php
                                echo $user['firstName'].' '.$user['lastName'];
                                ?>

                                </a>
<?php
if($user['authLevel'] == 2) {
?>
                               <span class="label label-info">Manager</span><?php
}
if($user['authLevel'] == 3) {
?>
                                <span class="label label-important">Site Admin</span><?php
}
?>
                            </span>
<?php
                }
            }
            ?>
<?php
if($_SESSION['authLevel'] == 3) {
?>
                        <div class="span5">
                            <br>
                            <a href="new/user?id=<?php echo $cust['customerID']; ?>" class="btn btn-small btn-success">
                                <i class="icon-plus icon-white"></i>
                                Add User
                            </a>
                        </div>
<?php
}
?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
}
?>
        </div>
<?php
if($_SESSION['authLevel'] == 3) {
?>

        <div class="row">
            <br>
            <div class="span12">
                <a href="new/customer" class="btn btn-success pull-right">
                    <i class="icon-plus icon-white"></i>
                    Add New Customer
                </a>
            </div>
        </div>
<?php
}


if($_SESSION['authLevel'] > 2) {
?>
            <h2 class="span8 offset2">Maintainers</h2>
<?php
}
foreach($maintainers as $resource) {
?>
            <div class="row">
                <h4 class="span3"><?=$resource['Name']?></h4>
                <h5 class="span4">
                    <?=$resource['Company']?>
                    <small>&#40;<?=$resource['Category']?>&#41;</small>
                </h5>

                <div class="btn-group span2 offset1">
                    <a href="maintainer/edit?id=<?=$resource['Recnum']?>" class="btn btn-mini">
                        <i class="icon-edit"></i>
                        Edit
                    </a>
                    <a href="maintainer/remove?id=<?=$resource['Recnum']?>" class="btn btn-mini btn-danger confirm">
                        <i class="icon-remove icon-white"></i>
                        Delete
                    </a>
                </div>

            </div>
<?php
}
?>
            <a href="maintainer/new" class="btn btn-success pull-right">
                <i class="icon-plus icon-white"></i>
                Add New Maintainer
            </a>
<?php
require_once('../includes/footer.php');
?>
