<?php include_once("header.php")?>
<?php require_once("utilities.php")?>

<div class="container">

<h2 class="my-3">Recommendations for you</h2>

<?php
  // This page is for showing a buyer recommended items based on their bid 
  // history. It will be pretty similar to browse.php, except there is no 
  // search bar. This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.

  
  // Check user's credentials (cookie/session).

    //session_start(); //called in header.php
    if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'buyer') {
        header('Location: browse.php');
    }else{
        $buyer_id=$_SESSION['user_id'];
    }

  // Perform a query to pull up auctions they might be interested in.
    $mysqli = get_connection();
    $buyer_recommendations_query = "SELECT r.item_id, i.item_name, i.item_description, bid_price, num_Of_bids, i.auction_end_datetime
              FROM item_recommendations r
              INNER JOIN items i ON i.item_id = r.item_id AND auction_end_datetime > NOW()
              LEFT JOIN (SELECT item_id, MAX(bid_price) AS bid_price, COUNT(item_id) AS num_of_bids FROM bids GROUP BY item_id) final_price_item ON final_price_item.item_id = r.item_id
              WHERE r.buyer_id = ?
              ORDER BY item_score DESC";

    $stmt = $mysqli->prepare($buyer_recommendations_query);
    $stmt->bind_param('i', $user_id);

    $stmt->execute();
    $result = $stmt->get_result();


  // Loop through results and print them out as list items.

    if ($result) {

        if (mysqli_num_rows($result) == 0) {
            echo('<div class="text-center"><b>There are no recommendations for you. </b></div>');

        } else {
            while ($row = mysqli_fetch_row($result)) {
                $end_date = date_create_from_format('Y-m-d H:i:s', $row[5]);
                print_listing_li($row[0], $row[1], $row[2], $row[3], $row[4], $end_date);
            }
        }

    } else {
        echo('<div class="text-center">Recommendations suggestion failed. Error:' . mysqli_error($mysqli));
    }