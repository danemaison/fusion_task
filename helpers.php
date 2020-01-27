<?php

// Set up a request and response global array
$request = [
  'method' => $_SERVER['REQUEST_METHOD'],
  'query'=> $_GET,
  'body' => json_decode(file_get_contents('php://input'), true) ?? []
];

$response = [
  'status_code' => 200,
  'headers'=>[
    'Content-Type' => 'application/json'
  ]
];

function get_db_link(){
  // get credentials from env
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

function send_response($response){
  // send a response and exit
  http_response_code($response['status_code']);
  forEach($response['headers'] as $key=>$value){
    header("$key: $value");
  }
  if(array_key_exists('body', $response)){
    print(json_encode($response['body']));
  }

  exit;
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

function check_foreign_key($link, $id, $table)
{
  if (!read_by_id($link, intval($id), $table)) {
    throw new ApiError("invalid foreign key $id", 404);
  }
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

  $response = [
    'status_code' => $status,
    'headers' => [
      'Content-Type' => 'application/json'
    ],
    'body' => [
      'error' => $message
    ]
  ];

  send_response($response);
})

?>
