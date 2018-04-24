<?php
//Ref: https://www.codeofaninja.com/2017/02/create-simple-rest-api-in-php.html
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once '../config/database.php';
include_once '../objects/datadef.php';

// instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// initialize object
$sensordata = new Devices($db);

// query products
$stmt = $sensordata->getDeviceList();
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){

    // products array
    $sensordata_arr=array();
    $sensordata_arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);

        $sensordata_item=array(
            "DEVICEID" => $DEVICEID,
            "loc_LATI" => $loc_LATI,
            "loc_LONG" => $loc_LONG
        );

        array_push($sensordata_arr["records"], $sensordata_item);
    }

    echo json_encode($sensordata_arr);
}

else{
    echo json_encode(
        array("message" => "No Results.")
    );
}
?>
