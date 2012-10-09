<?php
/**
 *------------------------------------------------------------------------------
 * Dashboard Index File
 *------------------------------------------------------------------------------
 *
 */
require_once('../includes/header.php');

systemSwitch($config);
?>
<?
$db = new db($config);

if(!isset($_SESSION['buildingID']) || !isset($_SESSION['SysID'])) {

    // Get the building info for this user's customer account
    $query = 'SELECT * FROM buildings WHERE customerID = :customerID';
    $buildingsBind[':customerID'] = $_SESSION['customerID'];

    $buildings = $db -> fetchAll($query, $buildingsBind);
    pprint($buildings);
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
            header('Location: buildings');
            break;
    }

    /**
     * If they have more than one system send them to a page where they can choose
     * what they want a dashboard for. Otherwise set sysID as a session
     * variable.
     */
    $query = 'SELECT sysID FROM SystemConfig WHERE buildingID = :buildingID';
    $systemConfigBind[':buildingID'] = $buildings[0]['buildingID'];

    $sysConfigs = $db -> fetchAll($query, $systemConfigBind);

    switch(count($sysConfigs)) {
        case 0:
            gtfo($config);
            break;
        case 1:
            $_SESSION['SysID'] = $sysConfigs[0]['SysID'];
            break;
        default:
            header('Location: buildings');
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
    }else{
        // Content goes here
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Date Time',
           'Water In 1',
           'Water Out 1',
           'Air In',
           'Air Out',
           'Air Outside',
           'In 1',
           'In 2',
           'In 3',
           'In 4',
           'In 5',
           'In 6',
           'In 7',
           'In 8',
           'Flow 1 GPM',
           'Pressure PSI'],
            ["13:48", 49.44, 43.03, 64.85,91.06, 66.65, 1, 0, 0, 1, 0, 0, 0, 1, 5.62, 37.50],
            ["13:48", 49.44, 43.03, 65.07,91.18, 67.21, 1, 0, 0, 1, 0, 0, 0, 1, 5.65, 37.50],
            ["13:48", 49.55, 43.03, 64.74,91.06, 68.45, 1, 0, 0, 1, 0, 0, 0, 1, 5.47, 37.56],
            ["13:48", 49.55, 43.03, 64.85,91.18, 68.22, 1, 0, 0, 1, 0, 0, 0, 1, 5.65, 37.50],
            ["13:48", 49.55, 43.03, 64.96,91.18, 68.56, 1, 0, 1, 1, 0, 0, 0, 1, 7.07, 13.03],
            ["13:49", 49.55, 43.03, 64.96,91.29, 69.01, 1, 0, 1, 1, 0, 0, 0, 1, 7.58, 12.77],
            ["13:49", 49.44, 42.80, 64.85,91.51, 69.57, 1, 0, 1, 1, 0, 0, 0, 1, 7.58, 12.74],
        ]);

        var options = {
            axisColor   : '#f00',
            baseline    : 10,
            chartArea   : {
                            left   : 'auto',
                            top    : 'auto',
                            width  : 'auto',
                            height : 300,
                          },
            colors      : ['red','orange','yellow', 'green', 'blue', 'indigo', 'violet'],
            focusTarget : 'datum',
            height      : 700,
            lineWidth   : 1,
            legend      : {
                          position  :'bottom',
                          alignment :'center',
                          textStyle :{
                                      color    : '#000',
                                      fontSize : 12}},
            pointSize   : 2,
            smoothLine  : true,
            title       : 'Sample Data From RFP',
            titleX      : 'Date time',
            titleY      : 'Temperature',
            vAxis       : {
                            baselineColor:'#ccc',
                            gridlines : {
                                color:'#ccc'
                                }
                          },
        };

        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);

}    </script>
    <div class="row">
        <div id="chart_div" class="span12"></div>
    </div>
<?php
    }

}

?>
<?php
require_once('../includes/footer.php');
?>
