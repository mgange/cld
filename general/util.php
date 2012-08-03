<?php
/**
 * A utilities file, for functions, classes, what have you.
 */

function hashPassword($pass)
{
    return sha1($pass);
}


class db extends PDO
{

    public function __construct($config)
    {
        parent::__construct(
            "mysql:host=".$config['dbHost'].";dbname=".$config['dbName'], 
            $config['dbUser'], 
            $config['dbPass']
        );

        try
        {
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch (PDOException $e)
        {
            die($e->getMessage());
        }
    }

    public function fetchRow($query, $bind = array())
    {
        # create a prepared statement
        $sth = parent::prepare($query);

        if($sth) 
        {
            # execute query 
            $sth->execute($bind);

            return $sth->fetch(PDO::FETCH_ASSOC);
        } 
        else
        {
            return self::error_info();
        }
    }

    public function fetchAll($query, $bind = array())
    {
        $sth = parent::prepare($query);

        if($sth)
        {
            $sth->execute($bind);

            return $sth->fetchALL(PDO::FETCH_ASSOC);
        } 
        else
        {
            return self::error_info();
        }
    }

    public function numRows($query, $bind = array())
    {
        $sth = parent::prepare($query);

        if($sth) {
            # execute query 
            $sth->execute($bind);

            return $sth->rowCount();
        } 
        else
        {
            return self::error_info();
        }
    }

    public function errorInfo() 
    {
        $this->connection->errorInfo();
    }

    public function __destruct()
    {
        $this->connection = null;
    }
}

?>