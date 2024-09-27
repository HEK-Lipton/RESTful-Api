<?php

require_once('Database.php');
require_once('Model.php');


// sets up a connection to the DBMS
$pdo = new PDO($dsn,$user,$passwd,$opt);
$method = $_SERVER['REQUEST_METHOD'];
$resource = explode('/',$_REQUEST['resource']);
$input = json_decode(file_get_contents('php://input'), TRUE);

/**
 * This function takes the reponse of the api request and echos it to the javascript interface file
 * 
 * args: $data - the results of the api request on the DBMS
 *       $status - the status code of the api request
 * 
 * returns: None
 */
function http_response($data,$status) {
    $response_status = json_decode($status,true);
    // sets the reponse code ofthe http request
    http_response_code($response_status['status']);
    echo $data;

}

// This set of if statements checks the request method that has been inputted
// into the api request and calls the appropriate function to execute the request
// then it calls the http_response function to send the results to the java scripte
// interface file
if ($method == 'GET') {
    [$data,$status] = get_data($pdo,$resource);
    http_response($data,$status);
}
else if ($method == "POST") {
    [$data,$status] = create_data($pdo,$resource,$input);
    http_response($data,$status);
}
else if ($method == "DELETE") {
    [$data,$status] = delete_data($pdo,$resource);
    http_response($data,$status);
}
else if ($method = "PATCH") {
    [$data,$status] = patch_data($pdo,$resource,$input);
    http_response($data,$status);
}


?>