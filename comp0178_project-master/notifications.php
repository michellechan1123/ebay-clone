<?php include_once("header.php") ?>
<?php require_once("utilities.php") ?>

<div class="container">

    <h2 class="my-3">Notifications</h2>

  <?php
  //$user_id = $_SESSION['user_id'];
  $user_id = $_SESSION['user_id'];   # TODO: get user_id from session

  $mysqli = get_connection();
  $query = '
    SELECT notification_id, message, is_read, created_datetime
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_datetime DESC
  ';
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    echo '<ul class="list-group">';
    while ($row = $result->fetch_assoc()) {
      $notification_id = $row['notification_id'];
      $message = $row['message'];
      $is_read = $row['is_read'];
      $created_datetime = $row['created_datetime'];
      $is_read_str = $is_read ? '' : '<span class="badge badge-success">New</span>';
      $is_read_class = $is_read ? ' text-muted bg-light' : '';
      echo '<li class="list-group-item' . $is_read_class . '"  onclick="toggleIsRead(' . $notification_id . ')">'
        . $message . ' ' . $is_read_str . '<br><p class="text-secondary">' . $created_datetime . '</p></li>';
    }
    echo '</ul>';
  } else {
    echo '<p>No notifications</p>';
  }

  ?>

  <?php include_once("footer.php") ?>

    <script>
        function toggleIsRead(notification_id) {
            console.log("These print statements are helpful for debugging btw");

            $.ajax('notification_toggle_read.php', {
                type: "POST",
                data: {notification_id: notification_id},
                success:
                    function () {
                        console.log("Success");
                        location.reload();
                    },
                error:
                    function () {
                        console.log("Error");
                    }
            }); // End of AJAX call

        }
    </script>
