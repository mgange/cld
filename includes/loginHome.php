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
?>
<div class="hero-unit" ><h2>
<?php
   {
   echo 'CLD is a Industry Leader in Alternative Energy Systems. Their GeoThermal';
   echo ' Monitoring System provides home owners and building managers with real system status and';
   echo ' system effiency monitors and long term insight into their energy savings realizations.';
   }
?>
    </h2></div>
<div class="hero-unit" ><h3><center>
<?php
if( isset($_SESSION['userID'])){
   echo '<a href="dashboard/index.php" >GeoThermal System Monitor</a>';
   }
else {  echo '<font color="blue">';
        echo 'Please Sign In above to Monitor your Systems';
        echo '<font color="black">';
     }
?>
     </center> </h3></div>      
<!--<pre>
   user    : user
    manager : manager
    admin   : admin  
</pre> -->
<div class="row">
    <div class="span6">
        <h3>Learn More about GeoThermal Systems</h2>
        <p><a href="http://energy.gov/energysaver/articles/geothermal-heat-pumps">
                Energy.Gov - GeoThermal Heat Pumps"</a></p></h3>
    </div>
   <div class="span6">
        <h3>Home Geothermal System</h3>
        <p><img src="../cld/Images/geothermal-heat-pump-systems.jpg" width="414" height="342" longdesc="geothermal-heat-pump-systems.jpg" /></p>
   </div>
</div>
<!-- 
<hr>

<div class="row">
    <div class="span4">
        <h4>Section</h4>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce congue
            laoreet fringilla. Vivamus sed arcu sed libero pulvinar facilisis at
            id felis. Nullam at felis at magna malesuada adipiscing ut quis mi.
        </p>

    </div>
    <div class="span4">
        <h4>Section</h4>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce congue
            laoreet fringilla. Vivamus sed arcu sed libero pulvinar facilisis at
            id felis. Nullam at felis at magna malesuada adipiscing ut quis mi.
        </p>
    </div>
    <div class="span4">
        <h4>Section</h4>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce congue
            laoreet fringilla. Vivamus sed arcu sed libero pulvinar facilisis at
            id felis. Nullam at felis at magna malesuada adipiscing ut quis mi.
        </p>
    </div>
</div>

<hr>

<div class="row">
    <div class="span3">
        <h4>Details</h4>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce congue
            laoreet fringilla. Vivamus sed arcu sed libero pulvinar facilisis at
            id felis. Nullam at felis at magna malesuada adipiscing ut quis mi.
        </p>
    </div>
    <div class="span3">
        <h4>Details</h4>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce congue
            laoreet fringilla. Vivamus sed arcu sed libero pulvinar facilisis at
            id felis. Nullam at felis at magna malesuada adipiscing ut quis mi.
        </p>
    </div>
    <div class="span3">
        <h4>Details</h4>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce congue
            laoreet fringilla. Vivamus sed arcu sed libero pulvinar facilisis at
            id felis. Nullam at felis at magna malesuada adipiscing ut quis mi.
        </p>
    </div>
    <div class="span3">
        <h4>Details</h4>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce congue
            laoreet fringilla. Vivamus sed arcu sed libero pulvinar facilisis at
            id felis. Nullam at felis at magna malesuada adipiscing ut quis mi.
        </p>
    </div>
</div>
-->