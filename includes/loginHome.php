<?php
/**
 *------------------------------------------------------------------------------
 * Homepage File for All Users
 *------------------------------------------------------------------------------
 *
 * This page is displayed at the root of the sites structure but NOT to logged
 * in users. It is a placeholder that will be replaced by site specific
 * information such as news, a blog, or marketing information for the site.
 * Logged in users get link to Geothermal Monitoring System
 *
 */

if(!isset($_SESSION['userID'])) {
    require_once('./includes/signIn.php');
}
?>
        <div class="hero-unit" >
            <h2>Continuous Live Data (CLD) is an Industry Leader in Alternative Energy Systems. Their
                Geothermal Monitoring System provides home owners and building
                managers with real time system status, system efficiency monitors
                and long term insight into their energy savings realizations.
            </h2>
        </div>
        <div class="hero-unit" >
            <h3 class="align-center">
<?php
if( isset($_SESSION['userID'])){
?>
                <a href="systems" >Geothermal System Monitor</a>
<?php
}else{
?>
                    Please Sign In above to Monitor your Systems
<?php
}
?>
            </h3>
        </div>

        <div class="row">
            <div class="span6">
                <h3>Learn More about Geothermal Systems</h3>

<?php
/**
 * 'Learn More' links pulled in from another file.
 * The links list is generated from a json object in /includes/learnMore.js
 */
$file = $_SESSION['base_domain'] . $_SESSION['base_dir'] . 'includes/learnMore.json';
$fh = fopen($file, 'r');
$links = fread($fh, 9999);
fclose($fh);

$obj = json_decode($links);
foreach(get_object_vars($obj) as $title => $link) {
?>
                <p>
                    <a href="<?php

                    if ($link !="#")  {echo $link; }?>" target="_blank">
                    <?php if ($link !="#") {echo $title;} ?></a>
                </p>
<?php
}
?>
            </div>
           <div class="span6">
                <h3>Home Geothermal System</h3>
                <img src="img/geothermal-heat-pump-systems.jpg" />
           </div>
        </div>
