<?php
/**
 *------------------------------------------------------------------------------
 * Global Navigation File
 *------------------------------------------------------------------------------
 *
 * This page is imported into the navbar when a user is logged in. It should
 * include site specific links and can build them out based on the users
 * information in the $_SESSION array or site data in the global $config array.
 * The markup is indented enough to align it with the navbar markup when a page
 * is generated( if that matters to you).
 *
 */
?>

            <button type="button" class="btn btn-navbar pull-right" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

<?php
if(isset($_SESSION['userID'])) {
?>
            <div class="btn-group pull-right">
                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                    <i class="icon-user"></i> <?php
if($_SESSION['firstName'] != '' && $_SESSION['lastName'] != '') {
    echo $_SESSION['firstName'] . ' ' . $_SESSION['lastName'];
}else{
    echo $_SESSION['username'];
}
                     ?> <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $config['base_domain'] . $config['base_dir']; ?>profile">Profile</a></li>
<?php if(isset($_SESSION['authLevel']) && $_SESSION['authLevel'] >= 2){ ?>
                    <li><a href="<?php echo $config['base_domain'] . $config['base_dir']; ?>admin">Admin</a></li>
<?php } ?>
                    <li class="divider"></li>
                    <li><a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>login/logout.php">Sign Out</a></li>

                </ul>
            </div>
<?php
}
?>
            <div class="nav-collapse collapse" >
                <ul class="nav">
                    <li>
                        <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>about">About CLD</a>
                    </li>
                    <li>
                        <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>contact">Contact Us</a>
                    </li>
<?php
if(isset($_SESSION['userID'])) {
?>
                    <li>
                        <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>dashboard">Dashboard</a>
                    </li>
<?php
}
?>
                </ul>
<?php
if(!isset($_SESSION['userID'])) {
    require_once('./includes/signIn.php');
}
?>
            </div>
