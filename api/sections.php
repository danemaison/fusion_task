<?php

include_once "../helpers.php";

if ($request['method'] === 'POST') {
  $body = $request['body'];
  $required_fields = ['section_name', 'client_id'];
  check_required_body_fields($required_fields, $body);

  $link = get_db_link();
  $result = create_section($link, $body);

  $response['status'] = 201;
  $response['body'] = $result;
  send_response($response);
}

if ($request['method'] === 'PUT') {
  $id = $request['query']['id'] ?? null;
  if(!intval($id)){
    throw new ApiError('missing query parameter `id`', 400);
  }

  $body = $request['body'];
  $required_fields = ['section_name', 'client_id'];
  check_required_body_fields($required_fields, $body);

  $link = get_db_link();
  $updated_row = update_section_by_id($link, $body, $id);

  $response['body'] = $updated_row;
  send_response($response);
}

if ($request['method'] === 'DELETE') {
  $id = $request['query']['id'] ?? null;
  if(!intval($id)){
    throw new ApiError('missing query parameter `id`', 400);
  }

  $link = get_db_link();
  $deleted = delete_by_id($link, $id, 'sections');
  if(!$deleted){
    throw new ApiError('cannot find section with `id` $id', 404);
  }

  send_response($response);
}

function create_section($link, $fields){
  $section_name = $fields['section_name'];
  $client_id = $fields['client_id'];
  check_foreign_key($link, $client_id, 'clients');

  $query = "INSERT INTO `sections` (name, client_id) VALUES (?, ?)";

  $stmt = $link->prepare($query);
  $stmt->bind_param('si', $section_name, $client_id);
  $stmt->execute();
  $id = $stmt->insert_id;
  $stmt->close();

  $result = read_by_id($link, $id, 'sections');
  return $result;
}

function update_section_by_id($link, $fields, $id){
  $section_name = $fields['section_name'];
  $client_id = $fields['client_id'];
  check_foreign_key($link, $client_id, 'clients');

  $query = "UPDATE `sections` SET `name` = ?, `client_id` = ? WHERE `id` = {intval($id)}";

  $stmt = $link->prepare($query);
  $stmt->bind_param('si', $section_name, $client_id);
  $stmt->execute();
  $stmt->close();

  $updated = read_by_id($link, $id, 'sections');
  return $updated;
}

?>
