<?php

require_once("settings.php");

// This stops CORS issues
header("access-control-allow-origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$METHOD = $_SERVER['REQUEST_METHOD'];
$REQUEST_BODY = file_get_contents("php://input");

function generateRandomString($length = 10) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

if ($METHOD=='OPTIONS') {
  header("Allow: GET,POST,PUT,DELETE,OPTIONS");
} else if ($METHOD=='GET') {
  if (empty($_GET['id'])) {
    http_response_code(400);
    print "Blob ID cannot be empty";
    die;
  }
  $id = $db->real_escape_string($_GET['id']);
  $result = $db->query("SELECT data FROM blobs WHERE id='$id'");
  if ($result->num_rows==0) {
    http_response_code(404);
    print "Not a valid blob ID";
    die;
  }
  http_response_code(200);
  print $result->fetch_row()[0];
} else if ($METHOD=='POST') {
  $id = generateRandomString();
  $data = $db->real_escape_string($REQUEST_BODY);
  $sql = "INSERT INTO blobs SET id='$id', data='$data'";
  $db->query($sql);
  if (!empty($db->error)) {
    http_response_code(400);
    print $db->error;
    die;
  }
  http_response_code(201);
  $location = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "?id=$id";
  header("Location: $location");
  header("X-Jsonblob: $id");
  echo $id;
} else if ($METHOD=='PUT') {
  if (empty($_GET['id'])) {
    http_response_code(400);
    print "Blob ID cannot be empty";
    die;
  }
  $id = $db->real_escape_string($_GET['id']);
  $data = $db->real_escape_string($REQUEST_BODY);
  $sql = "REPLACE INTO blobs SET id='$id', data='$data'";
  $db->query($sql);
  if (!empty($db->error)) {
    http_response_code(400);
    print $db->error;
    die;
  }
  http_response_code(201);
  $location = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  header("Location: $location");
  header("X-Jsonblob: $id");
  echo $id;
} else if ($METHOD=='DELETE') {
  if (empty($_GET['id'])) {
    http_response_code(400);
    print "Blob ID cannot be empty";
    die;
  }
  $id = $db->real_escape_string($_GET['id']);
  $db->query("DELETE FROM blobs WHERE id='$id'");
  if ($db->affected_rows==0) {
    http_response_code(404);
  }
  echo "Deleted";
} else {
  http_response_code(405);
  echo "METHOD NOT ALLOWED";
}

?>