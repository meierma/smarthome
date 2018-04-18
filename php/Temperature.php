<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

$Call = $_GET['Call'];

//-----------------Login MySQL Server-----------------

$config = parse_ini_file("../config/config.ini");

$MySQL_IP = $config["database_hostname"];
$MySQL_Username = $config["database_username"];
$MySQL_Password = $config["database_password"];
$MySQL_Database = $config["database_name"];
//----------------------------------------------------

if($Call == "getSensors"){ getSensors($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database); }
if($Call == "getHistoryTempHum"){ getHistoryTempHum($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database); }


function getSensors($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database)
{
  $arr[] = getSensorData($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);
  echo json_encode($arr);
}

function getSensorData($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database){
  $con = mysqli_connect($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);
  $sql = "SELECT TSensor_ID, Device_ID, TSensor_Name, TSensor_Description FROM tempSensors";

  $result = mysqli_query($con, $sql) or die (mysqli_error());
  while($row = mysqli_fetch_array($result))
  {
    $TSensor_ID = $row['TSensor_ID'];
    $Device_ID = $row['Device_ID'];
    $TSensor_Name = $row['TSensor_Name'];
    $TSensor_Description = $row['TSensor_Description'];

    $TSensor_ID = stripcslashes(utf8_encode($TSensor_ID));
    $Device_ID = stripcslashes(utf8_encode($Device_ID));
    $TSensor_Name = stripcslashes(utf8_encode($TSensor_Name));
    $TSensor_Description = stripcslashes(utf8_encode($TSensor_Description));

    $arr[] = array('TSensor_ID' => $TSensor_ID, 'Device_ID' => $Device_ID, 'TSensor_Name' => $TSensor_Name, 'TSensor_Description' => $TSensor_Description);

  }
  mysqli_close($con);
  return $arr;
}

function getHistoryTempHum($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database)
{
  if($range == "now"){
    $arr[] = getClientDataNow($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);
  }
  else if($range == "year"){
    $arr[] = getClientDataYear($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);
  }
  else if($range == "month"){
    //SELECT MONTH(`Timestamp`) as Month, DAY(`Timestamp`) as Day, AVG(`Temperature`) as Temp, AVG(`Humidity`) as Hum FROM `tempHistory` WHERE `TSensor_ID` = 1 AND YEAR(`Timestamp`) = YEAR(CURDATE()) GROUP BY MONTH(`Timestamp`),DAY(`Timestamp`)
    $arr[] = getClientDataMonth($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);
  }
  else if($range == "week"){
    $arr[] = getClientDataWeek($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);
  }
  else if($range == "day"){
    $arr[] = getClientDataDay($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);
  }

  echo json_encode($arr);
}

function initClimate($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database)
{
  $sensorDataArr[] = getSensorData($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);

}

function getClientDataNow($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database)
{
  $TSensor_ID = $_GET['TSensor_ID'];
  $range = $_GET['range'];
  $con = mysqli_connect($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);

  $sql = "SELECT Timestamp, Temperature, Humidity FROM tempHistory WHERE TSensor_ID= $TSensor_ID ORDER BY timestamp DESC Limit 1";

  $result = mysqli_query($con,$sql) or die (mysqli_error());
  while($row = mysqli_fetch_array($result))
  {
    $Timestamp = $row['Timestamp'];
    $Temperature = $row['Temperature'];
    $Humidity = $row['Humidity'];

    $Timestamp = stripcslashes(utf8_encode($Timestamp));
    $Temperature = stripcslashes(utf8_encode($Temperature));
    $Humidity = stripcslashes(utf8_encode($Humidity));


    $arr[] = array('Timestamp' => $Timestamp, 'Temperature' => $Temperature, 'Humidity' => $Humidity);
  }
  mysqli_close($con);
  return $arr;
}

function getClientDataDay($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database)
{
  $TSensor_ID = $_GET['TSensor_ID'];
  $range = $_GET['range'];
  $con = mysqli_connect($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);

  $sql = "SELECT HOUR(`Timestamp`) as \"Hour\", AVG(`Temperature`) as \"Temp\", AVG(`Humidity`) as \"Hum\" FROM `tempHistory` WHERE `TSensor_ID` = '$TSensor_ID' AND DATE(`timestamp`) = CURDATE() GROUP BY HOUR(`Timestamp`)";

  $result = mysqli_query($con,$sql) or die (mysqli_error());
  while($row = mysqli_fetch_array($result))
  {
    $Hour = $row['Hour'];
    $Temp = $row['Temp'];
    $Hum = $row['Hum'];

    $Hour = stripcslashes(utf8_encode($Hour));
    $Temp = stripcslashes(utf8_encode($Temp));
    $Hum = stripcslashes(utf8_encode($Hum));

    $arr[] = array('Hour' => $Hour, 'Temp' => $Temp, "Hum" => $Hum);
  }
  mysqli_close($con);
  return $arr;
}

function getClientDataWeek($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database)
{
  $TSensor_ID = $_GET['TSensor_ID'];
  $range = $_GET['range'];
  $con = mysqli_connect($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);

  $sql = "SELECT DAY(`Timestamp`) as \"Day\", AVG(`Temperature`) as \"Temp\", AVG(`Humidity`) as \"Hum\" FROM `tempHistory` WHERE `TSensor_ID` = '$TSensor_ID' AND WEEK(`Timestamp`) = WEEK(CURDATE()) GROUP BY DAY(`Timestamp`), MONTH('Timestamp')";

  $result = mysqli_query($con,$sql) or die (mysqli_error());
  while($row = mysqli_fetch_array($result))
  {
    $Day = $row['Day'];
    $Temp = $row['Temp'];
    $Hum = $row['Hum'];

    $Day = stripcslashes(utf8_encode($Day));
    $Temp = stripcslashes(utf8_encode($Temp));
    $Hum = stripcslashes(utf8_encode($Hum));

    $arr[] = array('Day' => $Day, 'Temp' => $Temp, "Hum" => $Hum);
  }
  mysqli_close($con);
  return $arr;
}

function getClientDataMonth($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database)
{
  $TSensor_ID = $_GET['TSensor_ID'];
  $range = $_GET['range'];
  $con = mysqli_connect($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);

  $sql = "SELECT WEEK(`Timestamp`) as \"Week\", AVG(`Temperature`) as \"Temp\", AVG(`Humidity`) as \"Hum\" FROM `tempHistory` WHERE `TSensor_ID` = '$TSensor_ID' AND MONTH(`Timestamp`) = MONTH(CURDATE()) GROUP BY WEEK(`Timestamp`)";

  $result = mysqli_query($con,$sql) or die (mysqli_error());
  while($row = mysqli_fetch_array($result))
  {
    $Week = $row['Week'];
    $Temp = $row['Temp'];
    $Hum = $row['Hum'];

    $Week = stripcslashes(utf8_encode($Week));
    $Temp = stripcslashes(utf8_encode($Temp));
    $Hum = stripcslashes(utf8_encode($Hum));

    $arr[] = array('Week' => $Week, 'Temp' => $Temp, "Hum" => $Hum);
  }
  mysqli_close($con);
  return $arr;
}

function getClientDataYear($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database)
{
  $TSensor_ID = $_GET['TSensor_ID'];
  $range = $_GET['range'];
  $con = mysqli_connect($MySQL_IP, $MySQL_Username, $MySQL_Password, $MySQL_Database);

  $sql = "SELECT MONTH(Timestamp) as \"Month\", AVG(`Temperature`) as \"Temp\" , AVG(`Humidity`) as \"Hum\" FROM `tempHistory` WHERE `TSensor_ID` = '$TSensor_ID' AND YEAR(`Timestamp`) = YEAR(CURDATE()) GROUP BY MONTH(`Timestamp`)";

  $result = mysqli_query($con,$sql) or die (mysqli_error());
  while($row = mysqli_fetch_array($result))
  {
    $Month = $row['Month'];
    $Temp = $row['Temp'];
    $Hum = $row['Hum'];

    $Month = stripcslashes(utf8_encode($Month));
    $Temp = stripcslashes(utf8_encode($Temp));
    $Hum = stripcslashes(utf8_encode($Hum));

    $arr[] = array('Month' => $Month, 'Temp' => $Temp, "Hum" => $Hum);
  }
  mysqli_close($con);
  return $arr;
}

?>
