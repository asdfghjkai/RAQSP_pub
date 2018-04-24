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
$data = new Sensordb($db);
// set ID property of product to be edited
$data->subID = isset($_GET['id']) ? $_GET['id'] : die();
$data->subName = isset($_GET['name']) ? $_GET['name'] : die();
$data->subManufacturer = isset($_GET['manufacturer']) ? $_GET['manufacturer'] : die();
$data->subURL = isset($_GET['url']) ? $_GET['url'] : die();
$data->subCalib = isset($_GET['calib']) ? $_GET['calib'] : die();
$data->subUnit = isset($_GET['unit']) ? $_GET['unit'] : die();

// read the details of product to be edited
$stmt = $data->createSensor();
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
