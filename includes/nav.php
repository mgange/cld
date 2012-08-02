<?php
/**
 * 
 */
?>
            <div class="btn-group pull-right">
                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                    <i class="icon-user"></i> <?php echo $_SESSION['username'] ?>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="#">Profile</a></li>
<?php if(isset($_SESSION['authLevel']) && $_SESSION['authLevel'] === 3){ ?>
                    <li><a href="<?php echo $config['base_domain'] . $config['base_dir']; ?>admin">Admin</a></li>
<?php } ?>
                    <li class="divider"></li>
                    <li><a href="<?php echo $_SESSION['base_domain'] . $_SESSION['base_dir']; ?>login/logout.php">Sign Out</a></li>
                </ul>
            </div>