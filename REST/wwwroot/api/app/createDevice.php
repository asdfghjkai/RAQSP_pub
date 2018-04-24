<?php
//Ref: https://www.codeofaninja.com/2017/02/create-simple-rest-api-in-php.html
//Error Reporting
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);

//Allow the application access to the parameters passed in
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
$data = new Devices($db);
// Set all parameter values, using those passed into the URL
$data->subID = isset($_GET['id']) ? $_GET['id'] : die();
$data->subloc_LATI = isset($_GET['lati']) ? $_GET['lati'] : die();
$data->subloc_LONG = isset($_GET['long']) ? $_GET['long'] : die();
$data->subPUBLIC = isset($_GET['public']) ? $_GET['public'] : die();
$data->subOWNER_ID = isset($_GET['ownerid']) ? $_GET['ownerid'] : die();
$data->subHW_ID = isset($_GET['hwid']) ? $_GET['hwid'] : die();
$data->subSW_VER = isset($_GET['swver']) ? $_GET['swver'] : die();
$data->subUPDATE = isset($_GET['update']) ? $_GET['update'] : die();

// Submit the query
$stmt = $data->createDevice();
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
            //Not required
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
