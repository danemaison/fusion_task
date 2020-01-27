<?php

include_once '../helpers.php';

if ($request['method'] === 'POST') {

  $body = $request['body'];
  $required_fields = ['link_name', 'section_id'];
  check_required_body_fields($required_fields, $body);

  $db_link = get_db_link();
  $result = create_link($db_link, $body);

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
  $required_fields = ['link_name', 'section_id'];
  check_required_body_fields($required_fields, $body);

  $db_link = get_db_link();
  $updated_row = update_link_by_id($db_link, $body, $id);

  $response['body'] = $updated_row;
  send_response($response);
}

if ($request['method'] === 'DELETE') {
  $id = $request['query']['id'] ?? null;
  if(!intval($id)){
    throw new ApiError('missing query parameter `id`', 400);
  }

  $db_link = get_db_link();
  $result = delete_by_id($db_link, $id, 'links');
  if(!$result){
    throw new ApiError('cannot find link with `id` $id', 404);
  }

  send_response($response);
}


function create_link($db_link, $fields)
{
  $link_name = $fields['link_name'];
  $section_id = $fields['section_id'];
  check_foreign_key($db_link, $section_id, 'sections');

  $query = 'INSERT INTO `links` (name, section_id) VALUES (?, ?)';

  $stmt = $db_link->prepare($query);
  $stmt->bind_param('si', $link_name, $section_id);
  $stmt->execute();
  $id = $stmt->insert_id;
  $stmt->close();

  $result = read_by_id($db_link, $id, 'links');
  return $result;
}

function update_link_by_id($db_link, $fields, $id)
{
  $link_name = $fields['link_name'];
  $section_id = $fields['section_id'];
  check_foreign_key($db_link, $section_id, 'sections');

  $query = "UPDATE `links` SET `name` = ?, `section_id` = ? WHERE `id` = {intval($id)}";

  $stmt = $db_link->prepare($query);
  $stmt->bind_param('si', $link_name, $section_id);
  $stmt->execute();
  $stmt->close();

  $updated = read_by_id($db_link, $id, 'links');
  return $updated;
}

?>
