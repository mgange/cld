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
        action="<?=$config['base_domain'] . $config['base_dir']?>login/login.php"
        method="POST">
            <div class="row">
                <div class="span8 offset2 well">
                    <div class="row">
                        <div class="span4 ">
                            <label for="username">Username &nbsp;
                                <input id="username" class="span4" type="text" name="username" autofocus="autofocus">
                            </label>
                        </div>
                        <div class="span4">
                            <label for="password">Password &nbsp;
                                <input id="password" class="span4" type="password" name="password" >
                            </label>
                        </div>
                        <div class="span6 offset1">
                            <input class="submit btn btn-large btn-block" type="submit" name="submit" value="Sign In">
                        </div>
                    </div>
                </div>
            </div>
        </form>
