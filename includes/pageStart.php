<?php
/**
 *------------------------------------------------------------------------------
 * Global Page Start File
 *------------------------------------------------------------------------------
 *
 * This file should be included at the beginning of every page regardless of wether
 * or not it is displayed to the user. It will include the site-wide
 * configuration and the utilitiesfile from the 'general' directory. Because the
 * path to the included assets is built from variables in the SESSION array
 * visitors must arrive at the siteshomepage or they will get a 'need the path'
 * error.
 *
 * PHP version 5.3.0
 */
if(session_id() == '') {
    session_start();
}

/* Try to load the site-wide configuration and utilitiees */
if(! include_once(__DIR__ . '/../config/config.php')) {
    die('The site\'s configuration could not be loaded.');
}
if(! require_once(__DIR__ . '/../general/util.php')) {
    die('The site\'s utilities could not be loaded.');
}

/**
 * The base domain and directory are needed to build links to resources like
 * javascript and stylesheets. If they're not set then the user hasn't been to
 * the homepage(and therefore hasn't been to a login page). Since you can't
 * reliably know the homepage without these values we'll just dump them  back to
 * the domain root.
 */
if(!isset($_SESSION['base_domain']) || !isset($_SESSION['base_dir'])) {
    $_SESSION['base_domain'] = $config['base_domain'];
    $_SESSION['base_dir'] = $config['base_dir'];
}

/* Get rid of index.php in the URL so it doesn't interfere with breafcrumbs */
if(!isset($_SESSION['userID']) && $_SERVER['SCRIPT_NAME'] !== '/' . $config['base_dir'] . 'index.php'){
    header('Location: ' . $config['base_domain'] . $config['base_dir']);
}

/* Override default timezone if one is specified in config.php */
if(isset($config['time_zone']) && $config['time_zone'] != '') {
    date_default_timezone_set($config['time_zone']);
}

/* Get rid of the index.php in the url */
if(preg_match('/index.php$/', $_SERVER['REQUEST_URI'])) {
    header('Location: ./');
}

/**
 * Check time since last activity and force new login is needed. If the session
 * hasn't expired then update that last_activity to the current time
 */
if(isset($_SESSION['last_activity'])) {
    if(time() - $_SESSION['last_activity'] > $config['sess_expiration']) {
        header('Location: '
            . $config['base_domain']
            . $config['base_dir']
            . 'login/logout.php');
    }else{
        $_SESSION['last_activity'] = time();
    }
}
/* Check time since SESSION data was updated and refresh if needed */
if(isset($_SESSION['last_update'])) {
    if(time()-$_SESSION['last_update'] > $config['sess_time_to_update']) {
        require_once(__DIR__ . '/../includes/sessionUpdate.php');
    }
}
?>
