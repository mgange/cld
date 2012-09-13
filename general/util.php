<?php
/**
 * A utilities file, for functions, classes, what have you.
 */

function hashPassword($pass)
{
    return sha1($pass);
}

function pprint($arr) {
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
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

    public function execute($query, $bind = array())
    {
        $sth = parent::prepare($query);

        if($sth)
        {
            if($sth -> execute($bind)) {
                return true;
            }else{
                return false;
            }
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

/**
 * An array of state names that might be used in several places, but really I
 * just don't want clogging up other pages.
 */
$state_list = array(
    ''  =>"",
    'AL'=>"Alabama",
    'AK'=>"Alaska",
    'AZ'=>"Arizona",
    'AR'=>"Arkansas",
    'CA'=>"California",
    'CO'=>"Colorado",
    'CT'=>"Connecticut",
    'DE'=>"Delaware",
    'DC'=>"District Of Columbia",
    'FL'=>"Florida",
    'GA'=>"Georgia",
    'HI'=>"Hawaii",
    'ID'=>"Idaho",
    'IL'=>"Illinois",
    'IN'=>"Indiana",
    'IA'=>"Iowa",
    'KS'=>"Kansas",
    'KY'=>"Kentucky",
    'LA'=>"Louisiana",
    'ME'=>"Maine",
    'MD'=>"Maryland",
    'MA'=>"Massachusetts",
    'MI'=>"Michigan",
    'MN'=>"Minnesota",
    'MS'=>"Mississippi",
    'MO'=>"Missouri",
    'MT'=>"Montana",
    'NE'=>"Nebraska",
    'NV'=>"Nevada",
    'NH'=>"New Hampshire",
    'NJ'=>"New Jersey",
    'NM'=>"New Mexico",
    'NY'=>"New York",
    'NC'=>"North Carolina",
    'ND'=>"North Dakota",
    'OH'=>"Ohio",
    'OK'=>"Oklahoma",
    'OR'=>"Oregon",
    'PA'=>"Pennsylvania",
    'RI'=>"Rhode Island",
    'SC'=>"South Carolina",
    'SD'=>"South Dakota",
    'TN'=>"Tennessee",
    'TX'=>"Texas",
    'UT'=>"Utah",
    'VT'=>"Vermont",
    'VA'=>"Virginia",
    'WA'=>"Washington",
    'WV'=>"West Virginia",
    'WI'=>"Wisconsin",
    'WY'=>"Wyoming"
);
