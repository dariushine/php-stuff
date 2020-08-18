<?php 
  $city = "";
  if (isset($_GET["city"])) {
    $city = str_replace(" ","-", $_GET["city"]);
    $city_url = "https://www.weather-forecast.com/locations/$city/forecasts/latest";
    $file_headers = @get_headers($city_url);
    if($file_headers && $file_headers[0] != 'HTTP/1.0 404 Not Found') {
      $text = file_get_contents($city_url);
      $first_step = explode("class=\"phrase\">", $text);
      $second_step = explode("</span>" , $first_step[1] );
      $raised = trim($second_step[0]);
      $text = "<div class='alert alert-success' role='alert0'>$raised</div>";
    } else {
      $text = "<div class='alert alert-danger' role='alert0'>The city could not be found.</div>";
    }

  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous"></head>

  <style>
    body {
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url(./background.jpg);
      background-size: cover;
      background-repeat: no-repeat;
      min-height: 100vh;
    }

    .container {
      padding-top: 15vh;
    }
  </style>

<body>
  <div class="container text-center text-light">
      <h1>What's the weather?</h1>
      <p class="lead">Enter the name of the city.</p>
      <form method="get" class="mb-3">
        <div class="form-group mx-auto" style="max-width: 25rem;">
          <input class="form-control" type="text" required name="city" id="inputCity" placeholder="City" value="<? echo $city; ?>">
        </div>
        <input class="btn btn-primary" type="submit" value="Search">
      </form>
      <div id="result" style="max-width: 25rem; margin: auto;">
        <? if($text) echo $text; ?>
      </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</body>
</html>