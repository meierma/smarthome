<?php
require_once("DatabaseConnection.php");

class Climate
{
    private $databaseConnection;

    const SERVER_IP = "192.168.0.152";

    const TEMPERATURE = 'Temperature';

    const HUMIDITY = 'Humidity';

    const SENSOR_ID = 'TSensor_ID';

    const DEVICE_ID = 'Device_ID';

    const SENSOR_NAME = 'TSensor_Name';

    public function __construct()
    {
        $this->databaseConnection = new DatabaseConnection();
    }

    public function getAllClimateSensorsFromDBAsJson()
    {
        $arr = $this->getAllClimateSensorsFromDB();
        echo json_encode($arr);
    }

    public function getAllClimateSensorsFromDB()
    {
        $con = $this->databaseConnection->connectDatabase();
        $sql = "SELECT TSensor_ID, Device_ID, TSensor_Name, TSensor_Description FROM sensors";

        $result = mysqli_query($con, $sql) or die (mysqli_error($con));
        while ($row = mysqli_fetch_array($result)) {
            $TSensor_ID = $row[self::SENSOR_ID];
            $Device_ID = $row[self::DEVICE_ID];
            $TSensor_Name = $row[self::SENSOR_NAME];
            $TSensor_Description = $row['TSensor_Description'];

            $TSensor_ID = stripcslashes(utf8_encode($TSensor_ID));
            $Device_ID = stripcslashes(utf8_encode($Device_ID));
            $TSensor_Name = stripcslashes(utf8_encode($TSensor_Name));
            $TSensor_Description = stripcslashes(utf8_encode($TSensor_Description));

            $arr[] = array(self::SENSOR_ID => $TSensor_ID, self::DEVICE_ID => $Device_ID, self::SENSOR_NAME => $TSensor_Name, 'TSensor_Description' => $TSensor_Description);

        }
        return $arr;
    }

    public function getClimateHistory()
    {
        $range = $_GET['range'];
        $sensorId = $_GET['sensorId'];
        $arr = array();

        if ($range == "now") {
            $arr = $this->getClimateDataNow($sensorId);
        } else if ($range == "year") {
            $arr = $this->getClimateDataYear($sensorId);
        } else if ($range == "month") {
            $arr = $this->getClimateDataMonth($sensorId);
        } else if ($range == "week") {
            $arr = $this->getClimateDataWeek($sensorId);
        } else if ($range == "day") {
            $arr = $this->getClimateDataDay($sensorId);
        }

        echo json_encode($arr);
    }

    public function resetClimateData()
    {
        $sensorId = $_GET['sensorId'];
        $climateData['climateDataNow'] = $this->getClimateDataNow($sensorId);
        $climateData['climateDataDay'] = $this->getClimateDataDay($sensorId);

        echo json_encode($climateData);
    }

    public function initClimateData()
    {
        $climateData['sensorDataArr'] = $this->getAllClimateSensorsFromDB();
        $climateData['climateDataNow'] = $this->getClimateDataNow($climateData['sensorDataArr'][0][self::SENSOR_ID]);
        $climateData['climateDataDay'] = $this->getClimateDataDay($climateData['sensorDataArr'][0][self::SENSOR_ID]);

        echo json_encode($climateData);
    }

    public function getClimateDataNow($TSensor_ID)
    {
        $con = $this->databaseConnection->connectDatabase();

        $sql = "SELECT Timestamp, Temperature, Humidity FROM climateHistory WHERE TSensor_ID='$TSensor_ID' ORDER BY Timestamp DESC Limit 1";

        $result = mysqli_query($con, $sql) or die (mysqli_error($con));
        while ($row = mysqli_fetch_array($result)) {
            $Timestamp = $row['Timestamp'];
            $Temperature = $row[self::TEMPERATURE];
            $Humidity = $row[self::HUMIDITY];

            $Timestamp = stripcslashes(utf8_encode($Timestamp));
            $Temperature = stripcslashes(utf8_encode($Temperature));
            $Humidity = stripcslashes(utf8_encode($Humidity));


            $arr = array('Timestamp' => $Timestamp, self::TEMPERATURE => $Temperature, self::HUMIDITY => $Humidity);
        }
        return $arr;
    }

    public function getClimateDataDayAverage($TSensor_ID)
    {
        $con = $this->databaseConnection->connectDatabase();

        $sql = "SELECT HOUR(Timestamp) as 'Hour', AVG(Temperature) as 'Temp', AVG(Humidity) as 'Hum' FROM climateHistory WHERE TSensor_ID='$TSensor_ID' AND DATE(Timestamp) = CURDATE() GROUP BY HOUR(Timestamp)";

        $result = mysqli_query($con, $sql) or die (mysqli_error($con));
        while ($row = mysqli_fetch_array($result)) {

            $Hour = $row['Hour'];
            $Temp = $row['Temp'];
            $Hum = $row['Hum'];

            $Hour = stripcslashes(utf8_encode($Hour));
            $Temp = stripcslashes(utf8_encode($Temp));
            $Hum = stripcslashes(utf8_encode($Hum));

            $arr[] = array('Hour' => $Hour, 'Temp' => $Temp, 'Hum' => $Hum);
        }
        return $arr;
    }

    public function getClimateDataDay($TSensor_ID)
    {
        $con = $this->databaseConnection->connectDatabase();

        $sql = "SELECT Timestamp , Temperature, Humidity FROM climateHistory WHERE TSensor_ID='$TSensor_ID' AND DATE(Timestamp) = CURDATE()";

        $result = mysqli_query($con, $sql) or die (mysqli_error($con));
        while ($row = mysqli_fetch_array($result)) {

            $Timestamp = $row['Timestamp'];
            $Temperature = $row['Temperature'];
            $Humidity = $row['Humidity'];

            $Timestamp = stripcslashes(utf8_encode($Timestamp));
            $Temperature = stripcslashes(utf8_encode($Temperature));
            $Humidity = stripcslashes(utf8_encode($Humidity));

            $arr[] = array('Timestamp' => $Timestamp, 'Temp' => $Temperature, 'Hum' => $Humidity);
        }
        return $arr;
    }

    public function getClimateDataWeek($TSensor_ID)
    {
        $con = $this->databaseConnection->connectDatabase();

        $sql = "SELECT DAY(`Timestamp`) as \"Day\", AVG(`Temperature`) as \"Temp\", AVG(`Humidity`) as \"Hum\" FROM `climateHistory` WHERE `TSensor_ID` = '$TSensor_ID' AND WEEK(`Timestamp`) = WEEK(CURDATE()) GROUP BY DAY(`Timestamp`), MONTH('Timestamp')";

        $result = mysqli_query($con, $sql) or die (mysqli_error($con));
        while ($row = mysqli_fetch_array($result)) {
            $Day = $row['Day'];
            $Temp = $row['Temp'];
            $Hum = $row['Hum'];

            $Day = stripcslashes(utf8_encode($Day));
            $Temp = stripcslashes(utf8_encode($Temp));
            $Hum = stripcslashes(utf8_encode($Hum));

            $arr[] = array('Day' => $Day, 'Temp' => $Temp, "Hum" => $Hum);
        }
        return $arr;
    }

    public function getClimateDataMonth($TSensor_ID)
    {
        $con = $this->databaseConnection->connectDatabase();

        $sql = "SELECT WEEK(`Timestamp`) as \"Week\", AVG(`Temperature`) as \"Temp\", AVG(`Humidity`) as \"Hum\" FROM `climateHistory` WHERE `TSensor_ID` = '$TSensor_ID' AND MONTH(`Timestamp`) = MONTH(CURDATE()) GROUP BY WEEK(`Timestamp`)";

        $result = mysqli_query($con, $sql) or die (mysqli_error($con));
        while ($row = mysqli_fetch_array($result)) {
            $Week = $row['Week'];
            $Temp = $row['Temp'];
            $Hum = $row['Hum'];

            $Week = stripcslashes(utf8_encode($Week));
            $Temp = stripcslashes(utf8_encode($Temp));
            $Hum = stripcslashes(utf8_encode($Hum));

            $arr[] = array('Week' => $Week, 'Temp' => $Temp, "Hum" => $Hum);
        }
        return $arr;
    }

    public function getClimateDataYear($TSensor_ID)
    {
        $con = $this->databaseConnection->connectDatabase();

        $sql = "SELECT MONTH(Timestamp) as \"Month\", AVG(`Temperature`) as \"Temp\" , AVG(`Humidity`) as \"Hum\" FROM `climateHistory` WHERE `TSensor_ID` = '$TSensor_ID' AND YEAR(`Timestamp`) = YEAR(CURDATE()) GROUP BY MONTH(`Timestamp`)";

        $result = mysqli_query($con, $sql) or die (mysqli_error($con));
        while ($row = mysqli_fetch_array($result)) {
            $Month = $row['Month'];
            $Temp = $row['Temp'];
            $Hum = $row['Hum'];

            $Month = stripcslashes(utf8_encode($Month));
            $Temp = stripcslashes(utf8_encode($Temp));
            $Hum = stripcslashes(utf8_encode($Hum));

            $arr[] = array('Month' => $Month, 'Temp' => $Temp, "Hum" => $Hum);
        }
        return $arr;
    }

    public function getDeviceIDByIP()
    {
        $IP = $this->getRealIpAddr();
        $con = $this->databaseConnection->connectDatabase();
        $sql = "SELECT Device_ID FROM devices WHERE IP_Adress = '$IP'";

        $result = mysqli_query($con, $sql) or die (mysqli_error($con));
        $Device_ID = "";
        while ($row = mysqli_fetch_array($result)) {
            $Device_ID = $row[self::DEVICE_ID];
            $Device_ID = stripcslashes(utf8_encode($Device_ID));
        }
        return $Device_ID;
    }

    public function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = "UNKNOWN";
        }
        return $ip;
    }

    public function getRegTSensors()
    {
        $Device_ID = $this->getDeviceIDByIP();
        $con = $this->databaseConnection->connectDatabase();
        $sql = "SELECT TSensor_ID, TSensor_Name, GPIO, Sensor FROM sensors WHERE Device_ID = '$Device_ID'";

        $result = mysqli_query($con, $sql) or die (mysqli_error($con));
        while ($row = mysqli_fetch_array($result)) {

            $TSensor_ID = $row[self::SENSOR_ID];
            $TSensor_Name = $row[self::SENSOR_NAME];
            $GPIO = $row['GPIO'];
            $Sensor = $row['Sensor'];

            $TSensor_ID = stripcslashes(utf8_encode($TSensor_ID));
            $TSensor_Name = stripcslashes(utf8_encode($TSensor_Name));
            $GPIO = stripcslashes(utf8_encode($GPIO));
            $Sensor = stripcslashes(utf8_encode($Sensor));

            $arr[] = array(self::SENSOR_ID => $TSensor_ID, self::SENSOR_NAME => $TSensor_Name, 'GPIO' => $GPIO, 'Sensor' => $Sensor);
        }
        echo json_encode($arr);
    }

    public function insertTemperatureAndHumidityIntoDB()
    {

        $Device_ID = $this->getDeviceIDByIP();
        $TSensor_ID = $_GET[self::SENSOR_ID];
        $Temperature = $_GET[self::TEMPERATURE];
        $Humidity = $_GET[self::HUMIDITY];

        if ((!empty($TSensor_ID)) && (!empty($Temperature)) && (!empty($Humidity))) {
            $con = $this->databaseConnection->connectDatabase();

            $sql = "SELECT TSensor_ID FROM sensors WHERE Device_ID = $Device_ID";
            $result = mysqli_query($con, $sql) or die (mysqli_error($con));
            $Permission = false;
            while ($row = mysqli_fetch_array($result)) {
                $TSID = $row[self::SENSOR_ID];
                if ($TSID == $TSensor_ID && !$Permission) {
                    $Permission = true;
                }
            }
            if ($Permission) {
                $sql = "INSERT INTO climateHistory (TSensor_ID, Temperature, Humidity) VALUES ($TSensor_ID, $Temperature, $Humidity)";
                mysqli_query($con, $sql) or die (mysqli_error($con));
                echo("OK");

            } else {
                echo("Error no permission");
            }
        }
    }
}