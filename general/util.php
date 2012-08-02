<?php
/**
 * A utilities file, for functions, classes, what have you.
 */

function hashPassword($pass)
{
    return sha1($pass);
}

function dbQuery($config, $query, $bind = array())
{
    $DBH = new PDO(
        "mysql:host=" . $config['dbHost'] . ";dbname=" . $config['dbName'] ,
        $config['dbUser'],
        $config['dbPass']
    );

    $STH = $DBH->prepare($query);
    $STH->execute($bind);
    return $STH->fetch(PDO::FETCH_ASSOC);
}

?>