<?php include_once("header.php")?>
<?php require_once("utilities.php") ?>

    <div class="container">

<h2 class="my-3">Browse listings</h2>
        <div id="searchSpecs">
            <!-- When this form is submitted, this PHP page is what processes it.
                 Search/sort specs are passed to this page through parameters in the URL
                 (GET method of passing data to a page). -->

          <?php
          $selected_cat = $_GET['cat'] ?? 'all';
          $selected_keyword = $_GET['keyword'] ?? '';
          $selected_sort = $_GET['order_by'] ?? 'pricelow';
          ?>
            <form method="get" action="browse.php">
                <div class="row">
                    <div class="col-md-5 pr-0">
                        <div class="form-group">
                            <label for="keyword" class="sr-only">Search keyword:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
            <span class="input-group-text bg-transparent pr-0 text-muted">
              <i class="fa fa-search"></i>
            </span>
                                </div>
                                <input type="text" class="form-control border-left-0" name="keyword"
                                       placeholder="Search for anything" value="<?php echo $selected_keyword ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 pr-0">
                        <div class="form-group">
                            <label for="cat" class="sr-only">Search within:</label>
                            <select class="form-control" name="cat">
                                <option <?php echo $selected_cat == 'all' ? 'selected' : '' ?> value="all">All
                                    categories
                                </option>
                              <?php
                              $mysqli = get_connection();
                              $query = 'SELECT category_id, category_name FROM categories';
                              $result = $mysqli->query($query);
                              while ($row = mysqli_fetch_array($result)) {
                                $selected = $selected_cat == $row['category_id'] ? 'selected' : '';
                                echo('<option ' . $selected . ' value="' . $row['category_id'] . '">' . $row['category_name'] . '</option>');
                              }
                              ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 pr-0">
                        <div class="form-inline">
                            <label class="sr-only" for="order_by">Sort by:</label>
                            <select class="form-control" name="order_by">
                                <option <?php echo $selected_sort == 'pricelow' ? 'selected' : '' ?> value="pricelow">
                                    Price (low to high)
                                </option>
                                <option <?php echo $selected_sort == 'pricehigh' ? 'selected' : '' ?> value="pricehigh">
                                    Price (high to low)
                                </option>
                                <option <?php echo $selected_sort == 'date' ? 'selected' : '' ?> value="date">Soonest
                                    expiry
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1 px-0">
                        <button type="submit" class="btn btn-secondary">Search</button>
                    </div>
                </div>
            </form>
        </div> <!-- end search specs bar -->


    </div>

<?php

$where = array();
$parameter_types = '';
$parameter_values = array();

// Retrieve these from the URL
if (isset($_GET['keyword']) && $_GET['keyword'] != '') {
  $where[] = 'item_name LIKE ?';
  $parameter_types .= 's';
  $parameter_values[] = "%{$_GET['keyword']}%";
}

if (isset($_GET['cat']) && $_GET['cat'] != 'all') {
  $where[] = 'category_id = ?';
  $parameter_types .= 'i';
  $parameter_values[] = $_GET['cat'];
}

$ordering = match ($_GET['order_by'] ?? 'pricelow') {
  'pricelow' => 'current_price',
  'pricehigh' => 'current_price DESC',
  default => 'auction_end_datetime'     # including 'date'
};

if (!isset($_GET['page'])) {
  $curr_page = 1;
} else {
  $curr_page = $_GET['page'];
}

/* Use above values to construct a query. Use this query to
   retrieve data from the database. (If there is no form data entered,
   decide on appropriate default value/default query to make. */

$where_string = implode(' AND ', $where);
$query = '
SELECT 
       item_id, 
       item_name, 
       item_description, 
       auction_end_datetime, 
       IFNULL(current_price, starting_price) AS current_price, 
       IFNULL(num_bids, 0) AS num_bids
FROM `items`
LEFT JOIN (
    SELECT item_id, MAX(bid_price) AS current_price, COUNT(*) AS num_bids FROM bids GROUP BY item_id
) bids USING (item_id)
';
if ($where) {
  $query .= ' WHERE ' . $where_string;
}
$query .= ' ORDER BY ' . $ordering;
$mysqli = get_connection();
$stmt = $mysqli->prepare($query);
if ($parameter_values) {
  $stmt->bind_param($parameter_types, ...$parameter_values);
}
$stmt->execute();
$result = $stmt->get_result();

/* For the purposes of pagination, it would also be helpful to know the
   total number of results that satisfy the above query */
$num_results = $result->num_rows;
$results_per_page = 10;
$max_page = ceil($num_results / $results_per_page);
?>

    <div class="container mt-5">

      <?php
      if ($num_results == 0) {
        echo 'No item found!';
      }
      ?>

        <ul class="list-group">

          <?php
          while ($row = mysqli_fetch_array($result)) {
            // Demonstration of what listings will look like using dummy data.
            $item_id = $row['item_id'];
            $title = $row['item_name'];
            $description = $row['item_description'];
            $current_price = $row['current_price'];
  $num_bids = $row['num_bids'];
  $end_date = date_create_from_format('Y-m-d H:i:s', $row['auction_end_datetime']);

// This uses a function defined in utilities.php
  print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date);
}
?>

</ul>

<!-- Pagination for results listings -->
<nav aria-label="Search results pages" class="mt-5">
  <ul class="pagination justify-content-center">

    <?php

  // Copy any currently-set GET variables to the URL.
  $querystring = "";
  foreach ($_GET as $key => $value) {
    if ($key != "page") {
      $querystring .= "$key=$value&amp;";
    }
  }

    $high_page_boost = max(3 - $curr_page, 0);
  $low_page_boost = max(2 - ($max_page - $curr_page), 0);
  $low_page = max(1, $curr_page - 2 - $low_page_boost);
  $high_page = min($max_page, $curr_page + 2 + $high_page_boost);

    if ($curr_page != 1) {
    echo('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
  }

    for ($i = $low_page; $i <= $high_page; $i++) {
    if ($i == $curr_page) {
      // Highlight the link
      echo('
    <li class="page-item active">');
    }
    else {
      // Non-highlighted link
      echo('
    <li class="page-item">');
    }

      // Do this in any case
    echo('
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }

    if ($max_page > 0 && $curr_page != $max_page) {
    echo('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
  }
?>

  </ul>
</nav>


</div>



<?php include_once("footer.php")?>