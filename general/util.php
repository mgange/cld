<?php
/**
 * A utilities file, for functions, classes, what have you.
 */

/**
 * Run one-way encryption on a password string using the crypt() function and a
 * salt stored in the config array. Hashing prevents any passwords stored in the
 * database being stolen if the database is compromised, and then used to
 * compromise both the application and the accounts of users on other services
 * if they do not use unique passwords.
 */
function hashPassword($config, $pass)
{
    return crypt($pass, $config['salt']);
}

/**
 * Returns true if two strings passed to it are the same and not empty.
 */
function comparePasswords($pass, $repass)
{
    if($pass != $repass || $pass == '') {
        return false;
    }else{
        return true;
    }
}

/**
 * Redirect users to the homepage with an Unauthorized Access message. Useful
 * any time you're restricting access, e.g. people viewing another customer
 * accout's data or pages meant for a higher authLevel.
 */
function gtfo($config){
    header('Location: ' . $config['base_domain'] . $config['base_dir'] . '?a=ua');
}

/**
 * Pretty Print
 * For development it's convenient to print and array. This will output and array
 * wrapped in <pre> tags to maintain formatting. This is not intended for
 * production use.
 */
function pprint($arr, $label = '')
{
    echo "<div class='well'>";
    echo '<pre>';
    if($label != '') {
        echo ucfirst($label) . ': ';
    }
    print_r($arr);
    echo '</pre>';
    echo "<small style='color:#f55;float:left;text-align:center;width:100%;'>";
    echo "pprint() is for development use only.";
    echo "</small>";
    echo "</div>";
}

/**
 * returns the array passed to it with empty values( == '' ) removed. Probably
 * most useful for parsing URIs.
 */
function arrayRemoveEmpty($array)
{
    foreach ($array as $key => $value) {
        if ($array[$key] == '') {
            unset($array[$key]);
        }
    }
    return $array;
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
