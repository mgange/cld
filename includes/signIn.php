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
        action="<?php echo $_SESSION['base_url']; ?>login/login.php"
        method="POST">
            <div class="row">
                <div class="span6">
                    <label for="username">Username &nbsp;
                        <input id="username" class="span6" type="text" name="username" >
                    </label>
                </div>
                <div class="span6">
                    <label for="password">Password &nbsp;
                        <input id="password" class="span6" type="password" name="password" >
                    </label>
                </div>
                <div class="span10 offset1">
                <input class="submit btn btn-large btn-block span10" type="submit" name="submit" value="Sign In">
                </div>
            </div>
        </form>
