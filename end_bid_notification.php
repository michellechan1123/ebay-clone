<?php

require("utilities.php");

while (1) {

  $mysqli = get_connection();
  # Get all closed but not notified auctions
  $query = "
SELECT items.item_id,
       item_name,
       item_description,
       seller_id,
       max_bid_price,
       reserve_price,
       IF(max_bid_price >= reserve_price, top_bids.buyer_id, NULL) AS winner_id,
       IF(max_bid_price >= reserve_price, winners.firstname, NULL) AS winner_name,
       IF(max_bid_price >= reserve_price, top_bids.bid_price, NULL) AS winner_price
FROM items
         LEFT JOIN (
    SELECT item_id,
           MAX(bid_price) AS max_bid_price
    FROM bids
   GROUP BY item_id
) top_bid_prices ON top_bid_prices.item_id = items.item_id
         LEFT JOIN (
    SELECT buyer_id, item_id, bid_price
    FROM bids
) top_bids ON top_bids.item_id = items.item_id AND top_bids.bid_price = top_bid_prices.max_bid_price
LEFT JOIN users winners ON winners.user_id = top_bids.buyer_id
WHERE auction_end_datetime < NOW() AND auction_end_notified = 0
";
  $result = $mysqli->query($query);
  if ($result->num_rows == 0) {
    echo "No auctions to notify\n";
  } else {
    while ($row = $result->fetch_assoc()) {
      $item_id = $row['item_id'];
      $item_name = $row['item_name'];
      $item_description = $row['item_description'];
      $seller_id = $row['seller_id'];
      $max_bid_price = $row['max_bid_price'];
      $reserve_price = $row['reserve_price'];
      $winner_id = $row['winner_id'];
      $winner_name = $row['winner_name'];
      $winner_price = $row['winner_price'];

      # Send notification to seller and winner
      $mysqli->begin_transaction();
      if ($winner_id) {
        $buyer_message = "Congratulations! You have won the auction for $item_name!";
        $mysqli->query("
      INSERT INTO notifications (user_id, message) VALUES ($winner_id, '$buyer_message')
      ");
        $seller_message = "You have sold $item_name to $winner_name for £$winner_price!";
      } elseif ($max_bid_price != null) {
        $seller_message = "Sorry, $item_name was not sold as top bid price £$max_bid_price did not meet the reserve price of £$reserve_price";
      } else {
        $seller_message = "Sorry, $item_name was not sold as no bids were received";
      }
      $mysqli->query("
    INSERT INTO notifications (user_id, message) VALUES ($seller_id, '$seller_message')
    ");
      $mysqli->query("
    UPDATE items SET auction_end_notified = 1 WHERE item_id = $item_id
    ");
      $mysqli->commit();
      echo "Notified $item_id $item_name\n";
    }
  }

  sleep(5);
}

