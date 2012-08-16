<?php
/**
 *------------------------------------------------------------------------------
 * Administrative Section Index Page
 *------------------------------------------------------------------------------
 *
 * Users with sufficient permission will be able to access this page to manage
 * the site. Site-wide administrators (level 3) will be able to view and edit customer 
 * information and information of individual user accounts. Customer 
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
require_once('../includes/header.php');

require_once('../includes/footer.php');
?>