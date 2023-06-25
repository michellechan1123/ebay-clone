<?php include_once("header.php")?>
<?php require_once("utilities.php")?>

<?php


// STEP 1: Check user's credentials (cookie/session).
// Redirect non-buyer and non-seller away from this page
// Use session to retrieve item_id
if (!isset($_SESSION['account_type'])) {
    header('Location: browse.php');
} else {
    $item_id = $_GET['item_id'];
}


// STEP 2: Use item_id to make a query to the database.
$mysqli = get_connection();
$query = 'SELECT item_name, item_description, starting_price, reserve_price,
                 minimum_bid as minimum_increment, auction_end_datetime, c.category_name,
                 MAX(bids.bid_price) as max_bid,
                 COUNT(bids.bid_datetime) AS num_bids 
          FROM (items JOIN categories c on items.category_id = c.category_id)
          LEFT JOIN bids on bids.item_id = items.item_id
          WHERE items.item_id= ? 
          GROUP BY  items.item_id';

    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    $result = $stmt->get_result();


// STEP 3: Use the query results as item variables
while ($row = mysqli_fetch_assoc($result)) {
    $title = $row["item_name"];
    $description = $row["item_description"];
    $starting_price = $row["starting_price"];
    $category_name = $row["category_name"];
    $minimum_increment = $row["minimum_increment"];
    if (is_null($row["max_bid"])) $current_price = $starting_price;
    else($current_price = $row["max_bid"]);
    $num_bids = $row["num_bids"];
    $reserve = $row["reserve_price"];
    $end_time = date_create_from_format('Y-m-d H:i:s', $row['auction_end_datetime']);
}


  // TODO: Note: Auctions that have ended may pull a different set of data,
  //       like whether the auction ended in a sale or was cancelled due
  //       to lack of high-enough bids. Or maybe not.

  // Calculate time to auction end:
  $now = new DateTime();

  if ($now < $end_time) {
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
  }

  $user_id = $_SESSION['user_id'];
  if (isset($_SESSION['user_id'])) {
    $has_session = true;
    $user_id = $_SESSION['user_id'];
    $is_buyer = $_SESSION['account_type'] == 'buyer';
  } else {
    $has_session = false;
    $user_id = null;
    $is_buyer = false;
  }
  $watching = false;
  if ($is_buyer) {
      $query = 'SELECT * FROM item_watches WHERE buyer_id = ? AND item_id = ?';
      $stmt = $mysqli->prepare($query);
      $stmt->bind_param('ii', $user_id, $item_id);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result->num_rows == 0) {
        $watching = false;
      } else {
        $watching = true;
      }
    }
?>

<head>
  <link rel="stylesheet" href="css/custom.css">
  <style>
      .label {
          border-radius: 25px;
          color: white;
          padding: 8px;
          length: em;
          font-family: Arial;}

      .ongoing {background-color: #008000;} /* Green */
      .ended {background-color: #B22222;} /* Red */
      .fail {background-color: #e7e7e7; color: black;} /* Gray */
      .listing-category {
          color: #808080;
          margin-top: 10px;
          margin-bottom: 20px;
      }

      th{
          padding: 10px;
          text-align: left;
          color: #808080;
      }

      }
  </style>
</head>
<body>


<div class="container">
<?php ?>
<div class="row"> <!-- Row #1 with auction title + watch button -->
  <div class="col-sm-8"> <!-- Left col -->
    <h2 class="my-3"><?php echo($title); ?></h2>
  </div>
  <div class="col-sm-4 align-self-center"> <!-- Right col -->
<?php if($_SESSION['account_type'] == 'buyer'):
    /* The following watchlist functionality uses JavaScript, but could
       just as easily use PHP as in other places in the code */
  if ($now < $end_time):
?>
    <div id="watch_nowatch" <?php if ($has_session && $watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
    </div>
    <div id="watch_watching" <?php if (!$has_session || !$watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
      <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
    </div>
  <?php endif /* Print nothing otherwise */ ?>
<?php endif /* Print nothing otherwise */ ?>
  </div>
</div>

<div class="row"> <!-- Row #2 with auction description + bidding info -->
  <div class="col-sm-8"> <!-- Left col with item info -->
    <table class="table-borderless">
      <tr>
        <th> • Description:</th>
        <td><?php echo ($description); ?></td>
      </tr>
      <tr>
        <th> • Category:</th>
        <td><?php echo ($category_name); ?></td>
      </tr>
      <tr>
        <th> • Starting Price:</th>
        <td><?php echo"£ ". ($starting_price); ?></td>
      </tr>
      <tr>
        <th> • Bid Increment:</th>
        <td><?php echo"£ ".($minimum_increment); ?></td>
      </tr>
      <tr>
        <th> • Number of bids:</th>
        <td><?php echo ($num_bids); ?></td>
      </tr>
    </table>
  </div>

  <div class="col-sm-4"> <!-- Right col with bidding info -->
    <div class="row">
        <?php if ($now > $end_time):
            if($current_price >= $reserve):
                echo("<div class='label ended'> Item Sold</div>");
            else:
                echo("<div class='label fail'>Item Not Sold</div>"); endif;?>
      <div class="row">
      <?php echo("<br>Auction ended on ".date_format($end_time, 'j M '))?>
      </div>
      <div class="row">
    <p class="lead">Maximum bid: £<?php echo(number_format($current_price, 2)) ?></p>
      </div>
        <?php else:
            echo("<div class='label ongoing'>Ongoing Auction</div>");?>
        <div class="row">
            <?php echo("<br>Auction ends ".date_format($end_time, 'j M H:i') . $time_remaining) ?>
        </div>
      <div class="row">
    <p class="lead">Current bid: £<?php echo(number_format($current_price, 2)) ?></p>
      </div>
    <!-- if user is logged in as seller, they wouldn't see place bid button -->
    <?php if($_SESSION['account_type'] == 'buyer'):
        $price_hint= $minimum_increment + $current_price ; ?>
    <!-- Bidding form -->
    <form method="POST" action="place_bid.php?item_id=<?php echo($item_id)?>" >
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text">£</span>
        </div>
	    <input type="number" step=0.01 min=<?php echo($price_hint)?> class="form-control" id="bid" name="bid_price" placeholder=<?php echo("Minimum:".$price_hint)?>>
      </div>
      <button type="submit" class="btn btn-outline-success form-control">Place bid</button>
    </form>
    <?php endif ?>
<?php endif ?>


  </div> <!-- End of right col with bidding info -->

</div> <!-- End of row #2 -->



<?php include_once("footer.php")?>


<script>
// JavaScript functions: addToWatchlist and removeFromWatchlist.

function addToWatchlist(button) {
  console.log("These print statements are helpful for debugging btw");

  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo($item_id);?>]},

    success:
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();

        if (objT == "success") {
          $("#watch_nowatch").hide();
          $("#watch_watching").show();
        }
        else {
          var mydiv = document.getElementById("watch_nowatch");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func

function removeFromWatchlist(button) {
  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo($item_id);?>]},

    success:
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();

        if (objT == "success") {
          $("#watch_watching").hide();
          $("#watch_nowatch").show();
        }
        else {
          var mydiv = document.getElementById("watch_watching");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func
</script>