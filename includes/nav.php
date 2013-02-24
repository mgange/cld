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
                <a class="btn dropdown-toggle"
                   data-toggle="dropdown"
                   href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>profile">
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
                        <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>">Home</a>
                    </li>
                    <li>
                        <a href="javascript:void(0);"
                            name=""
                            title=""
                            onclick="window.open(
                                '<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>about',
                                'Popup','   width=850,height=600,dependent=yes,0,status=0,resizable=1,');">
                            About CLD
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);"
                            name=""
                            title=""
                            onclick="window.open(
                                '<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>contact',
                                'Popup','width=850,height=600,dependent=yes,0,status=0,resizable=1,');">
                            Contact Us
                        </a>
                    </li>
<?php
if(isset($_SESSION['userID'])) {
?>
                    <li class="dropdown">
                        <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>systems"
                            role="button"
                            class="dropdown-toggle"
                            data-toggle="dropdown">
                            Systems <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                            <li>
                                <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>information">Information</a>
                            </li>
                            <li>
                                <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>alarms">Alarms</a>
                            </li>
                            <li>
                                <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>status">Status</a>
                            </li>
                            <li>
                                <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>performance">Performance</a>
                            </li>
                            <li>
                                <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>stages">System Stages</a>
                            </li>
                            <li>
                                <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>energy_comparison">Energy Comparison</a>
                            </li>
<?php if(isset($_SESSION['authLevel']) && (intval($_SESSION['authLevel']) == 3  or intval($_SESSION['authLevel']) == 2)){ ?>
                             <li>
                                <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>datadownload">Data Download</a>
                            </li>
<?php } ?>
                            <li class="divider"></li>
                            <li>
                                <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>maintenance">Maintenance</a>
                            </li>

                            <li>
                                <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>systems">Choose System</a>
                            </li>
                        </ul>
                    </li>
<?php
}
?>

<?php
if(isset($_SESSION['userID']) and isset($_SESSION['authLevel']) && (intval($_SESSION['authLevel']) == 3  or intval($_SESSION['authLevel']) == 2)) {
?>
                    <li class="dropdown">
                        <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>Setup"
                            role="button"
                            class="dropdown-toggle"
                            data-toggle="dropdown">
                            Setup <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">

                            <li>
                                <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>setup">System Setup</a>
                            </li>

<?php } ?>

 <?php
if (intval($_SESSION['authLevel']) == 3)  {
?>
                            <li>
                                <a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>config_setup">Setup Parameters</a>
                            </li>
                        </ul>
                    </li>
<?php
}
?>






           </ul>
        </div>
