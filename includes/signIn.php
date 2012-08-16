<?php
/**
 *------------------------------------------------------------------------------
 * Sign In Form
 *------------------------------------------------------------------------------
 *
 * This page is imported into the navbar when a user is NOT logged in. The 
 * markup is indented enough to align it with the navbar markup when a page is 
 * generated( if that matters to you).
 *
 */
?>
            <form 
            class="sign-in-form pull-right form-inline" 
            action="<?php echo $_SESSION['base_url']; ?>login/login.php" 
            method="POST">
                <input type="text" name="username" placeholder="Username" >
                <input type="password" name="password" placeholder="Password" >

                <input class="submit btn btn-small" type="submit" name="submit" value="Sign In">
            </form>
