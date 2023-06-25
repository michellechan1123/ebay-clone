<?php

require_once("utilities.php");

if (!isset($_POST['notification_id'])) {
  return;
}

// Retrieve user_id from session:
session_start();
$user_id = $_SESSION['user_id'];

// Extract arguments from the POST variables:
$notification_id = $_POST['notification_id'];

$mysqli = get_connection();
$query = "UPDATE notifications SET is_read = !is_read WHERE notification_id = ? AND user_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $notification_id, $user_id);
$stmt->execute();
$stmt->close();
$mysqli->close();

// Note: Echoing from this PHP function will return the value as a string.
// If multiple echo's in this file exist, they will concatenate together,
// so be careful. You can also return JSON objects (in string form) using
// echo json_encode($res).
echo 'success';
