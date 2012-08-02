<pre><?php
session_start();
require_once('../config/config.php');
require_once('../general/util.php');

if(isset($_POST['submit'])){
    try{
    /* Connect by creating a ew database object. */
        $DBH = new PDO(
            "mysql:host=" . $config['dbHost'] . ";dbname=" . $config['dbName'],
            $config['dbUser'],
            $config['dbPass']
        );
        /* Bind values to be used in the prepared statement. */
        $bind = array(
            ":username" => $_POST['username'],
            ":password" => hashPassword($_POST['password'])
        );
        /* Execute the query and see if any results come back. */
        $query = "SELECT * FROM users WHERE username = :username AND password = :password";
        $STH = $DBH->prepare($query);
        $STH->execute($bind);
        $results = $STH->fetch(PDO::FETCH_ASSOC);

        if(count($results) > 0) {
            $_SESSION['userID']     = $results['userID'];
            $_SESSION['customerID'] = $results['customerID'];
            $_SESSION['username']   = $results['username'];
            $_SESSION['authLevel']  = 3;//$results['authLevel'];
            $_SESSION['email']      = $results['email'];
            $_SESSION['last_activity'] = time();
        }

    } catch (Exception $e) {
        echo $e->getMessage();
    }

    header('Location: ../');
}

?>