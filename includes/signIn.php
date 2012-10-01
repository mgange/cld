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
                <label for="username">Username &nbsp;
                    <input id="username" type="text" name="username" >
                </label>
                <label for="password">Password &nbsp;
                    <input id="password" type="password" name="password" >
                </label>

                <input class="submit btn" type="submit" name="submit" value="Sign In">
            </form>
