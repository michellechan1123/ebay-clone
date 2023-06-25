<?php include_once("header.php")?>
<?php require_once("utilities.php")?>

<div class="container my-5">

    <?php

    // This function takes the form data and adds the new auction to the database.

    /* #1: Connect to MySQL database (perhaps by requiring a file that
                already does this). */

    $mysqli = get_connection();

    /* #2: Extract form data into variables. Because the form was a 'post'
                form, its data can be accessed via $POST['auctionTitle'],
                $POST['auctionDetails'], etc. Perform checking on the data to
                make sure it can be inserted into the database. If there is an
                issue, give some semi-helpful feedback to user. */

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name_err = $category_err = $starting_price_err = $reserve_price_err = $minimum_bid_err = $end_date_err = 0;
        $err_count = 0;

        $auction_start_datetime = date("Y-m-d H:i:s");
        $item_description = $_POST['auctionDetails'];

        $item_name = $_POST['auctionTitle'];
        if (empty($item_name) or !preg_match("/^[a-zA-Z-' ]*$/", $item_name)) {
            $name_err = 1;
            $err_count += 1;
        }

        $category_id = (int)$_POST['auctionCategory'];
        $validate_category_query = "SELECT category_id FROM categories WHERE category_id = $category_id";
        $category_stmt = $mysqli->prepare($validate_category_query);
        $category_stmt->execute();
        $category_result = $category_stmt->get_result();

        $row = mysqli_fetch_row($category_result);
        if ($row) {
            $category_id = $row[0];
        } else {
            $category_err = 1;
            $err_count += 1;
        }

        $starting_price = (float)$_POST['auctionStartPrice'];
        if (empty($starting_price) or $starting_price < 0) {
            $starting_price_err = 1;
            $err_count += 1;
        }

        $reserve_price = (float)$_POST['auctionReservePrice'];
        if (!empty($reserve_price) && $reserve_price < $starting_price) {
            $reserve_price_err = 1;
            $err_count += 1;
        }

        if (empty($_POST['auctionMinimumBid'])) {
            $minimum_bid = 1;
        } elseif ((float)$_POST['auctionMinimumBid'] <= 0) {
            $minimum_bid_err = 1;
            $err_count += 1;
        } else {
            $minimum_bid = (float)$_POST['auctionMinimumBid'];
        }

        $auction_end_datetime = strftime('%Y-%m-%d %H:%M:%S', strtotime($_POST['auctionEndDate']));
        if (empty($auction_end_datetime) or $auction_end_datetime < $auction_start_datetime) {
            $end_date_err = 1;
            $err_count += 1;
        }

        // Retrieve user_id from session:
        $seller_id = $_SESSION['user_id'];

/* #3: If everything looks good, make the appropriate call to insert data into the database. */
        if ($err_count == 0) {
            $insert_query = "
                    INSERT INTO items (item_name, item_description, category_id, seller_id, minimum_bid, starting_price, reserve_price, auction_start_datetime, auction_end_datetime)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $mysqli->prepare($insert_query);
            $stmt->bind_param("ssiidddss", $item_name, $item_description, $category_id, $seller_id, $minimum_bid, $starting_price, $reserve_price, $auction_start_datetime, $auction_end_datetime);
            $insert_result = $stmt->execute();

            /* #5: If all is successful, let user know. */
            if ($insert_result) {

                echo "<ul>
                        <br><b>Item name</b>: $item_name</br>
                        <br><b>Item description</b>: $item_description</br>
                        <br><b>Category</b>: $category_id </br>
                        <br><b>Starting price</b>: $starting_price</br>
                        <br><b>Reserve price</b>: $reserve_price</br>
                        <br><b>Minimum bid</b>: £$minimum_bid</br>
                        <br><b>Auction start date</b>: $auction_start_datetime</br>
                        <br><b>Auction end date</b>: $auction_end_datetime</br>
                     </ul><br>";


                echo('<div class="text-center"><b>Auction successfully created! </b><a href="mylistings.php">Click</a> on this if you are not being redirected.</div>');
                header("refresh:4;url=mylistings.php");
            } else {
                echo('<div class="text-center">Auction creation failed. Error:' . mysqli_error($mysqli));
            }


        } else {
            header("Location:create_auction.php?success=0&&name=".$name_err."&&category=".$category_err."&&starting_price=".$starting_price_err."&&reserve_price=".$reserve_price_err."&&minimum_bid=".$minimum_bid_err."&&end_date=".$end_date_err);
        }

    };
    ?>

</div>


<?php include_once("footer.php")?>
