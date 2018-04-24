<?php
//Ref: https://www.codeofaninja.com/2017/02/create-simple-rest-api-in-php.html
//Using above reference material, condensing into a single file as opposed to a collection
//Contains the data definitions and calls of all associated functions
class Devices{

    // database connection and table name
    private $conn;
    private $table_name = "devices";

    // object properties
    public $DEVICEID;
    public $loc_LATI;
    public $loc_LONG;
    public $PUBLIC;
    public $OWNER_ID;
    public $HW_ID;
    public $SW_VER;
    public $UPDATE;

    //use for create
    public $subID;
    public $subloc_LATI;
    public $subloc_LONG;
    public $subPUBLIC;
    public $subOWNER_ID;
    public $subHW_ID;
    public $subSW_VER;
    public $subUPDATE;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }
    //Returns all devices, for use in the mobile app with mapping etc
    function getDeviceList(){

        // select all query
        $query = "SELECT DEVICEID, loc_LATI, loc_LONG FROM " . $this->table_name;

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        return $stmt;
    }
    //Allows the creation of devices
    function createDevice()
    {
      // select all query
      $query = "INSERT INTO devices(DEVICEID,loc_LATI,loc_LONG,PUBLIC,OWNER_ID,HW_ID,SW_VER,UPDATE) VALUES(" . $this->subID . "," . $this->subloc_LATI . "," . $this->subloc_LONG . "," . $this->subPUBLIC . "," . $this->subOWNER_ID . "," . $this->subHW_ID . "," . $this->subSW_VER . "," . $this->subUPDATE . ")";

      // prepare query statement
      $stmt = $this->conn->prepare($query);
      // execute query
      $stmt->execute();

      return $stmt;
    }
}

class Owners{

    // database connection and table name
    private $conn;
    private $table_name = "owners";

    //for Creation
    public $subName;
    public $subMail;
    public $subTel;

    // object properties
    public $OWNERID;
    public $NAME;
    public $C_MAIL;
    public $C_TEL;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }
    //Allows creation of owners
    function createOwner()
    {
      // select all query
      $query = "INSERT INTO owners(NAME,C_MAIL,C_TEL) VALUES ('" . $this->subName . "','" . $this->subMail . "','" . $this->subTel . "')";
      // prepare query statement
      $stmt = $this->conn->prepare($query);
      // execute query
      $stmt->execute();
      return $stmt;
    }
}
//This class manages the sensors themselves, not the data
class Sensordb{

    // database connection and table name
    private $conn;
    private $table_name = "sensordb";

    // object properties
    public $SENSORID;
    public $SENSOR_NAME;
    public $MANUFACTURER;
    public $URL;
    public $CALIBRATION_DATA;
    public $UNIT;

    //create params
    public $subID;
    public $subName;
    public $subManufacturer;
    public $subURL;
    public $subCalib;
    public $subUnit;

    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }
    //Creation of sensor for DB
    function createSensor()
    {
      // select all query
      $query = "INSERT INTO sensordb(SENSORID,SENSOR_NAME,MANUFACTURER,URL,CALIBRATION_DATA,UNIT) VALUES ('" . $this->subID . "','" . $this->subName . "','" . $this->subManufacturer . "','" . $this->subURL . "','" . $this->subCalib . "','" . $this->subUnit . "')";
      // prepare query statement
      $stmt = $this->conn->prepare($query);
      // execute query
      $stmt->execute();

      return $stmt;
    }
}

//This class manages sensor data
class SensorDataOut
{
  private $conn;
  private $table_name="sensordata";

  public $VALUE;
  public $SENSOR_NAME;
  public $DATETIME;
  public $UNIT;
  public $sensorcount;

  public $devID;
  public $resLimit;

  // constructor with $db as database connection
  public function __construct($db){
      $this->conn = $db;


  }
  //retrieve data based on passed in arguments
  function getData()
  {
    // select all query
    $query = "SELECT sensordata.VALUE, sensordb.SENSOR_NAME, sensordata.DATETIME, sensordb.UNIT
    FROM sensordata
    INNER JOIN devices ON sensordata.DEVICEID = devices.DEVICEID
    INNER JOIN sensordb ON sensordata.SENSORID = sensordb.SENSORID
    WHERE sensordata.DEVICEID = ?" . " ORDER BY sensordata.DATETIME DESC LIMIT " . $this->resLimit;

    // prepare query statement
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1,$this->devID);
    // execute query
    $stmt->execute();
    return $stmt;
  }

  //retrieve all data values
  function getAllData()
  {
    // select all query
    $query = "SELECT sensordata.VALUE, sensordb.SENSOR_NAME, sensordata.DATETIME, sensordb.UNIT FROM sensordata INNER JOIN devices ON sensordata.DEVICEID = devices.DEVICEID INNER JOIN sensordb ON sensordata.SENSORID = sensordb.SENSORID ORDER BY sensordata.DATETIME DESC";
    // prepare query statement
    $stmt = $this->conn->prepare($query);
    // execute query
    $stmt->execute();
    return $stmt;
  }

  function countSensors()
  {
    $query = "SELECT COUNT(DISTINCT sensordb.SENSOR_NAME) as sensorcount
    FROM sensordb
    INNER JOIN sensordata on sensordb.SENSORID = sensordb.SENSORID
    WHERE sensordata.DEVICEID = " . $devID;

    // prepare query statement
    $stmt = $this->conn->prepare($query);
    //$stmt->bindParam(1,$this->devID);
    // execute query
    $stmt->execute();
    return $stmt;
  }



}

//this class manages data insertion RE sensor values
class SensorDataIn
{
  private $conn;
  private $table_name="sensordata";

  public $DATETIME;
  public $DEVICEID;
  public $SENSORID;
  public $VALUE;

  // constructor with $db as database connection
  public function __construct($db){
      $this->conn = $db;


  }
  //Function for data insertion using params given
  function insertData()
  {
    // select all query

    $query = "INSERT INTO sensordata(DATETIME, DEVICEID, SENSORID, VALUE) VALUES(?,?,?,?)";

    // prepare query statement
    $stmt = $this->conn->prepare($query);

    $stmt->bindParam(1,$this->DATETIME);
    $stmt->bindParam(2,$this->DEVICEID);
    $stmt->bindParam(3,$this->SENSORID);
    $stmt->bindParam(4,$this->VALUE);

    // execute query
    $stmt->execute();

    return $stmt;
  }
}
?>
