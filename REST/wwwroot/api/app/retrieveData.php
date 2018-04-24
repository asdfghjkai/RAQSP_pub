<?php
//Ref: https://www.codeofaninja.com/2017/02/create-simple-rest-api-in-php.html
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// include database and object files
include_once '../config/database.php';
include_once '../objects/datadef.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare product object
$data = new SensorDataOut($db);
// set ID property of product to be edited
$data->devID = isset($_GET['devID']) ? $_GET['devID'] : die();
$data->resLimit = isset($_GET['limit']) ? $_GET['limit'] : die();

// read the details of product to be edited
$stmt = $data->getData();
$num = $stmt->rowCount();
// create array
if($num>0){

    // products array
    $data_arr=array();
    $data_arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);

        $data_item=array(
            "VALUE" => $VALUE,
            "SENSOR_NAME" => $SENSOR_NAME,
            "DATETIME" => $DATETIME,
            "UNIT" => $UNIT
        );

        array_push($data_arr["records"], $data_item);
    }

    echo json_encode($data_arr);
}

else{
    echo json_encode(
      array("message" => "No Results.")
    );
}
?>
