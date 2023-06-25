<?php include_once("header.php")?>
<?php require_once("utilities.php")?>

<div class="container">

    <h2 class="my-3">My listings</h2>

<?php
  // This page is for showing a user the auction listings they've made.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.

// STEP 1: Check user's credentials (cookie/session).
// Redirect people without buying privileges away from this page
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'seller') {
    header('Location: browse.php');
} else {
    $user_id = $seller_id = $_SESSION['user_id'];
}


// STEP 2: Perform a query to pull up their auctions.
$mysqli = get_connection();
$query = 'SELECT DISTINCT items.item_id, items.item_name, items.item_description, 
              items.auction_end_datetime, items.starting_price,
              COUNT(bids.bid_datetime) AS num_bids,
              MAX(bids.bid_price) as max_bid   
              FROM (items LEFT JOIN bids on bids.item_id = items.item_id)
              JOIN users on users.user_id = items.seller_id
              WHERE users.user_id= ?
              GROUP BY items.item_id';

$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Loop through results and print them out as list items.
    if ($result->num_rows > 0) {
        // output data of each row
        while($row = mysqli_fetch_assoc($result)) {
            $item_id = $row["item_id"];
            $title = $row["item_name"];
            $description = $row["item_description"];
            $starting_price = $row["starting_price"];
            if (is_null($row["max_bid"])) $current_price = $starting_price;
            else($current_price = $row["max_bid"]);
            $num_bids = $row["num_bids"];
            $end_date = date_create_from_format('Y-m-d H:i:s', $row['auction_end_datetime']);
            // function defined in utilities.php
            print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);
        }
    } else {
        // if the user has no listings
        echo "There is no listing in your seller account.";
    }

?>


<?php include_once("footer.php")?>