<?php 
  session_start();
  if (empty($_SESSION["user"])) header("Location: index.php");
  $conn = include("connection.php");
  if (!$conn) {
    echo "An error has occured.";
    exit;
  }

  // Gets the text from the user's row in the database
  $diarytext = "";
  $query = "SELECT text FROM users WHERE id = $1";
  $result = pg_query_params($conn, $query, array($_SESSION["user"]["userid"]));
  if ($result && $row = pg_fetch_array($result)) {
    $diarytext = $row["text"];
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous"></head>
  <title>Document</title>
  <style>
    body {
      background: url(./background.jpg);
      background-size: cover;
      background-repeat: no-repeat;
      min-height: 100vh;
    }
    textarea {
      width: 100%;
      min-height: calc(100vh - 6rem);
      padding: 0.5rem;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-dark bg-dark">
    <div class="container">
      <span class="text-light"><? echo $_SESSION["user"]["useremail"]; ?></span>
      <a href="index.php?logout=1" class="btn btn-sm btn-danger">Logout</a>
    </div>
  </nav>

  <div class="container mt-4">
    <textarea oninput="inputChange(this.value)"><? echo $diarytext; ?></textarea>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
 
  <script>
    // Updates the diary text after every change
    function inputChange(inputText) {
      let formData = new FormData();
      formData.append("data", inputText);
      fetch("./updatediary.php", {
        method: "post",
        credentials: 'same-origin',
        body: formData
      })
    }
  </script>
</body>
</html>