<?php include_once("header.php") ?>
<?php require_once("utilities.php") ?>

    <div class="container">

    <h2 class="my-3">My bids</h2>

<?php
// This page is for showing a user the auctions they've bid on.
// It will be pretty similar to browse.php, except there is no search bar.
// This can be started after browse.php is working with a database.
// Feel free to extract out useful functions from browse.php and put them in
// the shared "utilities.php" where they can be shared by multiple files.


// Define a function print_mybids_li that prints an HTML <li> element containing bids
function print_mybids_li($item_id, $title, $desc, $user_top_bid_price, $item_top_bid_price, $end_time, $is_winning, $is_winner)
{

    // Truncate long descriptions
    if (strlen($desc) > 250) {
        $desc_shortened = substr($desc, 0, 250) . '...';
    }
    else {
        $desc_shortened = $desc;
    }

    // Check the bid status
    if (!is_null($is_winner)) {
        $time_remaining = 'Auction ended on ' . date('d/m/Y', date_timestamp_get($end_time));
        if ($is_winner == '1') {
            $bid_status = 'Congrats! You won the auction.';
        }
        else if ($is_winner == '0') {
            $bid_status = 'You lost. The final bid: £' . number_format($item_top_bid_price, 2);
        }
    }
    else {
        // Get interval:
        $now = new DateTime();
        $time_to_end = date_diff($now, $end_time);
        $time_remaining = display_time_remaining($time_to_end) . ' remaining';
        if ($is_winning == True) {
            $bid_status = 'You are now the highest bidder.';
        }
        else {
            $bid_status = 'Outbid! Current highest bid: £' . number_format($item_top_bid_price, 2);
        }
    }

    // Print HTML
    echo('
      <li class="list-group-item d-flex justify-content-between">
      <div class="p-2 mr-5"><h5><a href="listing.php?item_id=' . $item_id . '">' . $title . '</a></h5>' . $desc_shortened . '</div>
      <div class="text-center text-nowrap"><span style="font-size: 1.5em">£' . number_format($user_top_bid_price, 2) . '</span><br/>' . $bid_status . '<br/>' . $time_remaining . '</div>
      </li>'
    );
}


// STEP 1: check user's credentials (cookie/session).
// Redirect people without buying privileges away from this page
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'buyer') {
    header('Location: browse.php');
}else{
    $user_id=$_SESSION['user_id'];
}

// Query of the auctions they've bid on
// If the user made multiple bids for the same item, only the highest bid will be displayed
$mysqli = get_connection();
$query = '
    SELECT items.item_id,
           item_name,
           item_description,
           user_top_bid_price,
           item_top_bid_price,
           user_top_bid_price = item_top_bid_price AS is_winning,
           IF(auction_end_datetime < NOW(), IF(user_top_bid_price = item_top_bid_price 
              AND user_top_bid_price >= items.reserve_price, 1, 0),
              NULL) AS is_winner,
           auction_end_datetime
    FROM items
        JOIN (SELECT item_id, buyer_id, MAX(bid_price) AS user_top_bid_price
            FROM bids GROUP BY item_id, buyer_id) AS user_top_bid
            ON items.item_id = user_top_bid.item_id
        JOIN (SELECT item_id, MAX(bid_price) AS item_top_bid_price FROM bids GROUP BY item_id) AS item_top_bid
            ON items.item_id = item_top_bid.item_id
    WHERE user_top_bid.buyer_id = ?
    ';
$stmt = $mysqli->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$num_results = $result->num_rows;

// Show message if no records found
if ($num_results == 0) {
    echo "No records found.";
}

// Loop through results and print them out as list items.
while ($row = mysqli_fetch_array($result)) {
    $item_id = $row['item_id'];
    $title = $row['item_name'];
    $description = $row['item_description'];
    $user_top_bid_price = $row['user_top_bid_price'];
    $item_top_bid_price = $row['item_top_bid_price'];
    $end_date = date_create_from_format('Y-m-d H:i:s', $row['auction_end_datetime']);
    $is_winning = $row['is_winning'];
    $is_winner = $row['is_winner'];

    // Call the function print_mybids_li
    print_mybids_li($item_id, $title, $description, $user_top_bid_price, $item_top_bid_price, $end_date, $is_winning, $is_winner);
}
?>

<?php include_once("footer.php") ?>