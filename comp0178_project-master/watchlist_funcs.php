 <?php

 require_once ('utilities.php');

if (!isset($_POST['functionname']) || !isset($_POST['arguments'])) {
  return;
}


// Extract arguments from the POST variables:
$item_id = $_POST['arguments'][0];

// Retrieve user_id from session:
session_start();;
$user_id = $_SESSION['user_id'];

$mysqli = get_connection();

// Confirm that the user is buyer:
$query = "
  SELECT user_id, role_name = 'buyer' AS is_buyer
  FROM users
  JOIN roles USING (role_id)
  WHERE user_id = ?
  ";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
  echo "User not found";
  return;
} elseif ($result->fetch_assoc()['is_buyer'] == 0) {
  echo "User is not a buyer";
  return;
}

if ($_POST['functionname'] == "add_to_watchlist") {
  $query = "INSERT IGNORE INTO item_watches (buyer_id, item_id) VALUES (?, ?)";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ii", $user_id, $item_id);
  if ($stmt->execute()) {
    $res = "success";
  } else {
    $res = "failure";
  }
}
else if ($_POST['functionname'] == "remove_from_watchlist") {
  $query = "DELETE FROM item_watches WHERE buyer_id = ? AND item_id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param("ii", $user_id, $item_id);
  if ($stmt->execute()) {
    $res = "success";
  } else {
    $res = "failure";
  }
}

// Note: Echoing from this PHP function will return the value as a string.
// If multiple echo's in this file exist, they will concatenate together,
// so be careful. You can also return JSON objects (in string form) using
// echo json_encode($res).
echo $res;

?>