<?php

include_once 'helpers.php';

if ($request['method'] === 'POST') {
  $name = $request['body']['client_name'] ?? '';

  if(empty($name)){
    throw new ApiError('client_name is required', 400);
  }

  $link = get_db_link();
  $client_id = create_client($name, $link);

  // send response
  print_r($client_id);

}

if ($request['method'] === 'PUT') {

  $id = $request['query']['id'] ?? null;
  $name = $request['body']['client_name'] ?? '';
  if(!intval($id)){
    throw new ApiError('missing query parameter `id`', 400);
  } else if(empty($name)){
    throw new ApiError('client_name is a required field', 400);
  }

  $link = get_db_link();
  $updated = update_client_by_id($id, $name, $link);
  if(!$updated){
    throw new ApiError("cannot update with provided id $id", 400);
  }

  // send response
  print_r($updated);

}

if ($request['method'] === 'DELETE') {
  $id = $request['query']['id'] ?? null;
  if(!intval($id)){
    throw new ApiError('an id must be specified', 400);
  }

  $link = get_db_link();
  $result = delete_by_id($link, $id, 'clients');
  if(!$result){
    throw new ApiError("cannot find client with id $id", 404);
  }

  // send response
  print_r($result);

}

function create_client($clientName, $link){
  $query = "INSERT INTO `clients` (`name`)
  VALUES (?)";
  $stmt = $link->prepare($query);
  $stmt->bind_param('s', $clientName);
  $stmt->execute();
  $id = $stmt->insert_id;
  $stmt->close();
  $created = read_by_id($link, $id, 'clients');
  return $created;
}

function update_client_by_id($id, $name, $link)
{
  $query = "UPDATE `clients`
    SET `name` = ?
    WHERE `id` = {intval($id)}
  ";

  $stmt = $link->prepare($query);
  $stmt->bind_param('s', $name);
  $stmt->execute();
  $stmt->close();
  $updated = read_by_id($link, $id, 'clients');
  return $updated;
}
