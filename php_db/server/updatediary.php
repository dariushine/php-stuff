<?php 
session_start();
if (isset($_SESSION["user"]) && isset($_POST["data"])) {
  $conn = include("connection.php");
  if (!$conn) {
    echo "An error has occured.";
    exit;
  }

  // Updates an user's diary text
  $query = "UPDATE users SET text = $1 WHERE id = $2";
  $result = pg_query_params($conn, $query, array($_POST["data"], $_SESSION["user"]["userid"]));
  if ($result) http_response_code(200);
} else echo (implode(" ,", array("test", "test2")));
?>