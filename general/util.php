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
 * @param  array  $config The site-wide config array
 * @param  string $pass   The password being encrypted
 * @return string         The hashed version ot the password
 */
function hashPassword($config, $pass)
{
    return crypt($pass, $config['salt']);
}


/**
 * Returns true if two strings passed to it are the same and not empty.
 * @param  string $pass   The first password being tested
 * @param  string $repass The second password for comparison
 * @return bool           True or false if the passwords match or not
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
 * @param  Array  $config The site-wide config array
 * @return null           Nothing is returned, the page is ust redirected
 */
function gtfo($config){
    header('Location: ' . $config['base_domain'] . $config['base_dir'] . '?a=ua');
}


/**
 * Check if buildingID and SysID are set int the SESSION variable. If no the
 * user is redirected to the system selection page.
 * @param  array  $config The site-wide config array
 */
function checkSystemSet($config)
{
    $db = new db($config);

    if(!isset($_SESSION['buildingID']) || !isset($_SESSION['SysID'])) {

        // Get the building info for this user's customer account
        $buildingQuery = 'SELECT * FROM buildings WHERE customerID = :customerID';
        $buildingsBind[':customerID'] = $_SESSION['customerID'];

        $buildings = $db -> fetchAll($buildingQuery, $buildingsBind);
        /**
         * If they have more than one building send them to a page where they can choose
         * what they want a dashboard for. Otherwise set buildingID as a session
         * variable.
         */
        switch(count($buildings)) {
            case 0:
                gtfo($config);
                break;
            case 1:
                $_SESSION['buildingID'] = $buildings[0]['buildingID'];
                break;
            default:
                header('Location: '
                    . $config['base_domain']
                    . $config['base_dir']
                    . 'systems');
                break;
        }

        /**
         * If they have more than one system send them to a page where they can choose
         * what they want a dashboard for. Otherwise set sysID as a session
         * variable.
         */
        $systemQuery = 'SELECT SysID FROM SystemConfig WHERE buildingID = :buildingID';
        $systemConfigBind[':buildingID'] = $buildings[0]['buildingID'];

        $sysConfigs = $db -> fetchAll($systemQuery, $systemConfigBind);

        switch(count($sysConfigs)) {
            case 0:
                gtfo($config);
                break;
            case 1:
                $_SESSION['SysID'] = $sysConfigs[0]['SysID'];
                break;
            default:
                header('Location: '
                    . $config['base_domain']
                    . $config['base_dir']
                    . 'systems');
                break;
        }

    }else{
        // Double check that the building/system belongs to their customer account
        $buildingResponse = $db -> numRows(
            'SELECT customerID FROM buildings WHERE buildingID = :buildingID',
            array(':buildingID' => intval($_SESSION['buildingID']))
            );
        $sysConfigResponse = $db -> numRows(
            'SELECT buildings.customerID
            FROM buildings LEFT JOIN SystemConfig
            ON buildings.buildingID = SystemConfig.buildingID
            WHERE SystemConfig.SysID = :SysID',
            array(':SysID' => intval($_SESSION['SysID']))
            );
        if(!$buildingResponse || !$sysConfigResponse) {
            gtfo($config);
        }
    }
}


/**
 * COP Calculation based on Water delta , flow rate and power consumption
 * @param int    $WaterIn  Temp. of water coming in
 * @param int    $WaterOut Temp of water going out
 * @param int    $Flow     Flow rate in gallons/min
 * @param int    $Power1   Power sensor value 1
 * @param int    $Power2   Power sensor value 2
 */
  function COPCalc($WaterIn, $WaterOut, $Flow, $Power1, $Power2)
 {
    $COP = 0;
    $ABSBTU = ($WaterIn - $WaterOut) * $Flow * 1 * 8.35;
    $ElecBTU = ($Power1 + $Power2) * 3.412 * 1000;
    // Echo ("ABS-".$ABSBTU."  ---ElecBTU=".$ElecBTU."    WI=".$WaterIn."   WO=".$WaterOut." --Flow=".$Flow);
    if ($ElecBTU == 0) {
        $COP="---";
    }else{
        $COP = number_format(($ABSBTU + $ElecBTU) / $ElecBTU, 2);
    }
    return $COP;
  }


/**
 * Determine the system status based on digital inputs
 * @param bool   $G  SourceData0.DigIn04 or SourceData4.ThermStat01
 * @param bool   $Y1 SourceData0.DigIn01 or SourceData4.ThermStat04
 * @param bool   $Y2 SourceData0.DigIn02 or SourceData4.ThermStat02
 * @param bool   $O  SourceData0.DigIn03 or SourceData4.ThermStat03
 * @param bool   $W  SourceData0.DigIn05 or SourceData4.ThermStat01
 * @param bool   $T  sourceData4.ThermMode
 * @return string    Description of the system status
 */
function Systemlogic($G, $Y1, $Y2, $O, $W, $T)
{
    $SState="Invalid State";
   // if ($TMode==5){$T=1;} else {$T=0;}  // inhibits stages when in emer heat mode
   // Echo($TMode."++".$T);
    if ( !$O and !$W and !$Y2 and  !$Y1 and !$G) {$SState="System Off";}
    if ( !$O and !$W and !$Y2 and  !$Y1 and  $G) {$SState="Fan Only";}
    if ( !$O and !$W and !$Y2 and   $Y1 and  $G and !$T) {$SState="Stage 1 Heat";}
    if ( !$O and !$W and  $Y2 and   $Y1 and  $G and !$T) {$SState="Stage 2 Heat";}
    if ((!$O and  $W and !$Y2 and  !$Y1 and  $G) or $T)  {$SState="Emerg. Heat"; }
    if ( !$O and  $W and  $Y2 and   $Y1 and  $G and !$T) {$SState="Stage 3 Heat";}
    if (  $O and !$W and !$Y2 and   $Y1 and  $G and !$T) {$SState="Stage 1 Cool";}
    if (  $O and !$W and  $Y2 and   $Y1 and  $G and !$T) {$SState="Stage 2 Cool";}
    Return $SState;
}


/**
 * Test a system for emergency heat mode
 * @param int    $InVal
 * @param int    $EM
 */
function Emerglogic($InVal, $EM)
{
    $EmState = 0;
    if ($InVal == true && $EM == false) {
        $EmState=1;
    }else{
        $EmState=0;
    }
   // echo( "<BR>".$EM."--".$InVal."--".$EmState);
   //       if ($EmState==0)   {Echo "S1";} else {echo "S2";}
    Return $EmState;
}


/**
 * unitLabel decodes units and add degree symbol
 * @param string $SenUnitField Marker to indicate type of units
 */
function UnitLabel($SenUnitField)
{
     $deg=htmlentities(chr(176), ENT_QUOTES, 'cp1252');

         switch ($SenUnitField)
         {  case "dF":
             $SUnit=$deg."F";
             break;
            case "dC":
             $SUnit=$deg."C";
                break;
            default : $SUnit=$SenUnitField;
         }

    return $SUnit;
}


/**
 * Check if a value is awithin an acceptable range
 * @param  int    $val The value to test
 * @param  int    $min Minimum limit
 * @param  int    $max Maximum limit
 * @return bool        True or false
 */
function withinRange($val, $min, $max)
{
    if($val > $min && $val < $max) {
        return true;
    }else{
        return false;
    }
}


/**
 * echoes out the values of an array seperated by commas, with no comma after
 * the last value
 * @param  array  $array Values to e outputted
 * @param  string $wrapper An element to put before and after the value, that
 * defaults to nothing. It could be a quote character if one is needed.
 * e.g. outputting strings.
 * @param  integer $max     The maximum allowable value of each array element.
 * @return null
 */
function echoJSarray($array, $wrapper='', $divisor=1, $max = 0){

    $i=1;
    foreach($array as $val) {
        echo $wrapper;
        if($divisor != 1) {
            $val = $val/$divisor;
        }
        if($max != 0 && $val > $max) {
            $val = $max;
        }
        if($val == null) {
            $val = 0;
        }
        echo $val;
        echo $wrapper;
        if($i < count($array)) {echo ', ';}
        $i++;
    }
}


/**
 * Returns the name of a variable as a string.
 * e.g. printVarName($foo); returns "foo"
 * @param  any    $var Any variable
 * @return string      The name of the variable passed
 */
function printVarName($var) {
    foreach($GLOBALS as $var_name => $value) {
        if ($value === $var) {
            return $var_name;
        }
    }
    return false;
}


/**
 * Pretty Print
 * For development it's convenient to print and array. This will output and array
 * wrapped in <pre> tags to maintain formatting. This is not intended for
 * production use.
 * @param  array  $arr   The data being displayed
 * @param  string $label An optional label for the array being dumped
 * @return null          Nothing is returned
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
 * @param  array  $array The data to be processed
 * @return array         The data passed in, without empty values
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


/**
 *------------------------------------------------------------------------------
 * PDO Wrapper Class
 *------------------------------------------------------------------------------
 *
 */
class db extends PDO
{

    /**
     * Establishes a database connection
     * @param Array  $config site-wide config array
     */
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

    /**
     * Executes a query that doesn't need tosend back data from the database
     * @param  string $query Database query
     * @param  array  $bind  Bound values for prepared statements
     * @return bool          True or false based on the successful execution of
     * the query
     */
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

    /**
     * Gets data from a single record in the database
     * @param  string $query Databse query string
     * @param  array  $bind  Values for prepared statements
     * @return array         An array of arrays containind data from a single
     * record in the database
     */
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

    /**
     * Gets data from several records in the database
     * @param  string $query Database query string
     * @param  array  $bind  Values for prepared statements
     * @return array        An array of arrays containind data from several
     * records in the database
     */
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

    /**
     * Check how many records would be retured from a query execution
     * @param  string $query Database query string
     * @param  array  $bind  Values for prepared statements
     * @return int           The row count returned from the query execution
     */
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

    /**
     * Parse an error message so it can be returned be other methods
     * @return string A description of the database error
     */
    public function errorInfo()
    {
        $this->connection->errorInfo();
    }

    /**
     * Close the database connection
     */
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
