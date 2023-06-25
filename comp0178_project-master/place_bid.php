<?php require("utilities.php") ?>

<?php
session_start();
// TODO: Extract $_POST variables, check they're OK, and attempt to make a bid.
// Notify user of success/failure and redirect/give navigation options.


// Check parameters
$item_id_err = $bid_price_err = 0;

if (!isset($_GET['item_id'])) {
    $item_id_err = 1;
}

if (!isset($_POST['bid_price'])) {
    $bid_price_err = 1;
}

if ($item_id_err == 1 || $bid_price_err == 1) {
    if ($item_id_err == 1) {echo "Please select an item to place a bid.";}
    if ($bid_price_err == 1) {echo "Please enter a bid price to place a bid.";}
    header("refresh:3;url=browse.php?");
}

// Get info
else {
    $item_id = $_GET['item_id'];
    $buyer_id = $_SESSION['user_id'];
    $bid_price = $_POST['bid_price'];

    // Query for user's role
    $mysqli = get_connection();
    $query_role = "
    SELECT user_id, role_name = 'buyer' AS is_buyer
    FROM users
    JOIN roles USING (role_id)
    WHERE user_id = ?
    ";
    $stmt_role = $mysqli->prepare($query_role);
    $stmt_role->bind_param('i', $buyer_id);
    $stmt_role->execute();
    $result_role = $stmt_role->get_result();
    $row = mysqli_fetch_array($result_role);
    $is_buyer = $row['is_buyer'];

    // Query for auction end datetime
    $mysqli = get_connection();
    $query_end = '
    SELECT auction_end_datetime
    FROM items
    WHERE items.item_id = ?
    ';
    $stmt_end = $mysqli->prepare($query_end);
    $stmt_end->bind_param('i', $item_id);
    $stmt_end->execute();
    $result_end = $stmt_end->get_result();
    $row = mysqli_fetch_array($result_end);
    $end_time = date_create_from_format('Y-m-d H:i:s', $row['auction_end_datetime']);
    $now = new DateTime();

    // Confirm whether the user is a buyer
    if ($is_buyer == 0) {
        echo "Please login as a buyer to place bids.";
        header("refresh:3;url=listing.php?item_id=$item_id");
    }

    // Confirm whether the auction has ended
    elseif ($now > $end_time) {
        echo "The auction has ended.";
        header("refresh:3;url=listing.php?item_id=$item_id");
    }

    else {
        // Define the minimum price for the new bid
        $mysqli = get_connection();
        $query_price = '
            SELECT minimum_bid, IFNULL(current_price, starting_price) AS current_price
            FROM items
            LEFT JOIN (SELECT item_id, MAX(bid_price) AS current_price FROM bids GROUP BY item_id) bids USING (item_id)
            WHERE items.item_id = ?
            ';
        $stmt_price = $mysqli->prepare($query_price);
        $stmt_price->bind_param('i', $item_id);
        $stmt_price->execute();
        $result_price = $stmt_price->get_result();
        $row = mysqli_fetch_array($result_price);
        if ($row == null) {
            echo "Item not found!";
            header("refresh:3;url=browse.php");
        }
        else {
            $minimum_price = $row['current_price'] + $row['minimum_bid'];
            // Determine if the entered price is acceptable
            if ($bid_price < $minimum_price) {
                echo "Your bid price should be equal to or greater than $minimum_price.";
                header("refresh:3;url=listing.php?item_id=$item_id");
            }
            else {
                $mysqli = get_connection();
                $query = "INSERT INTO bids (buyer_id, item_id, bid_price)" .
                    "VALUES (?, ?, ?)";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('iid', $buyer_id, $item_id, $bid_price);
                $result = $stmt->execute();
                if ($result) {
                    echo "Your bid has been placed successfully!";
                    header("refresh:3;url=listing.php?item_id=$item_id");
                }
                else {
                    echo "Unsuccessful bid. Error: " . mysqli_error($mysqli);
                    header("refresh:3;url=listing.php?item_id=$item_id");
                    return;
                }

                // Get item info
                $query = "SELECT item_name FROM items WHERE item_id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('i', $item_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = mysqli_fetch_array($result);
                $item_name = $row['item_name'];

                // Notify bidders
                $bidder_message = "A new bid has been placed on an item you bid: $item_name.\n" .
                  "Bid price: $bid_price";
                $query = "
                    INSERT INTO notifications (user_id, message)  
                    SELECT DISTINCT buyer_id, '$bidder_message' FROM bids WHERE item_id = ? AND buyer_id != ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('ii', $item_id, $buyer_id);
                $stmt->execute();

                // Notify watchers
                $watcher_message = "A new bid has been placed on your watch item: $item_name.\n" .
                    "Bid price: $bid_price";
                $query = "
                  INSERT INTO notifications (user_id, message)  
                  SELECT buyer_id, '$watcher_message' FROM item_watches 
                  WHERE item_id = ? AND buyer_id NOT IN (SELECT buyer_id FROM bids WHERE item_id = ?)";   // Exclude bidders
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('ii', $item_id, $item_id);
                $stmt->execute();
            }
        }
    }
}
?>