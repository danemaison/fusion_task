<?php

// Set up a request and response global array
$request = [
  'method' => $_SERVER['REQUEST_METHOD'],
  'query'=> $_GET,
  'body' => json_decode(file_get_contents('php://input'), true) ?? []
];

$response = [
  'status' => 200,
  'headers'=>[
    'Content-Type' => 'application/json'
  ]
];

function get_db_link(){
  // get credentials from env...
  $host = 'localhost';
  $user = 'root';
  $pass = 'root';
  $db = 'fusion';
  $link = new mysqli($host, $user, $pass, $db);
  if (!$link){
    throw new ApiError('Webservice unavailable', 503);
  }
  return $link;
}

function read_by_id($link, $id, $table){
  $query = "SELECT * FROM `{$table}` WHERE `id` = {intval($id)}";
  $result = $link->query($query)->fetch_assoc();
  return $result;
}

function delete_by_id($link, $id, $table){
  $query = "DELETE FROM `{$table}` WHERE `id`={intval($id)}";
  $link->query($query);
  return $link->affected_rows > 0;
}

function check_required_body_fields($required_fields, $body)
{
  foreach ($required_fields as $field) {
    if (!array_key_exists($field, $body)) {
      throw new ApiError("$field is a required field", 400);
    }
  }
}

// create an api error class so we can prescribe errors
class ApiError extends Error {}

set_exception_handler(function($error){
  if ($error instanceof ApiError){
    $status = $error->getCode();
    $message = $error->getMessage();
  }else{
    // if we didn't throw the error, something bad happened
    $status = 500;
    $message = 'An unexpected error has occured';
  }

  // set up a response
  print("$status error: " . $message);
})

?>
