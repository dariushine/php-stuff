<?php 

session_start();

$conn = include("connection.php");

if (!$conn) {
  echo "An error has occurred, please try again later.";
  exit;
}

// On logout
if (isset($_GET["logout"])) {
  session_unset();
  setcookie("remember","", time() - 60*60);
  unset($_COOKIE["remember"]);
}

// Redirect logged users
if(isset($_SESSION["user"])) header("Location: ./loggedin.php");

// Check cookies
if (empty($_SESSION["user"]) && isset($_COOKIE["remember"])) {
  list($selector, $authenticator) = explode(':', $_COOKIE['remember']);
  $query = "SELECT * FROM auth_tokens WHERE selector = $1";
  $result = pg_query_params($conn, $query, array($selector));
  if ($result && $row = pg_fetch_array($result)) {
    if (password_verify(base64_decode($authenticator), $row["token"]) && strtotime($row["expires"]) > time()) {
      $userid = $row["userid"];
      $query = "SELECT id, email FROM users WHERE id = $1";
      $result = pg_query_params($conn, $query, array($userid));
      // Checks if the cookie matches the token in the db
      if ($result && $row = pg_fetch_array($result)) {
        // If so, starts the user session
        $_SESSION["user"] = array(
          "userid" => $row["id"],
          "useremail" => $row["email"]
        );

        // And generates new token & cookie
        $authenticator = random_bytes(33);
        $query = "UPDATE auth_tokens SET token = $1, expires = $2 WHERE selector = $3";
        $result = pg_query_params($query, array(password_hash($authenticator, PASSWORD_DEFAULT), date('Y-m-d\TH:i:s', time() + 604800), $selector));
        if ($result) {
          setcookie(
            'remember',
             $selector.':'.base64_encode($authenticator),
             time() + 604800,
             false
          );
          header("Location: ./loggedin.php");
        }
      }
    }
  }
}

// Valid post request
if (isset($_POST["email"], $_POST["password"])) {
  
  $email = $_POST["email"];
  $userpassword = $_POST["password"];
  $option = $_POST["option"];

  $error = "";

  // Error checking
  if($email == "") $error = $error . "<li>Email is required.</li>"; // Empty email
  else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = $error . "<li>Enter a valid email address.</li>"; // Invalid email
  if ($userpassword == "") $error= $error . "<li>Password is required.</li>"; // Empty password
  if (!isset($_POST["register"]) && !isset($_POST["login"])) $error = $error . "<li>Invalid command.</li>"; // Wrong button

  // Start processing
  if(!$error) {

    // Register
    if (isset($_POST["register"])) {
      $query = "SELECT * FROM users WHERE email = $1";
      $result = pg_query_params($conn, $query, array($email));

      if ($result) {
        if (pg_fetch_all($result)) $error = $error . "<li>That email address is already in use.</li>";
        else {
          // Registers the user
          $userpassword = password_hash($userpassword, PASSWORD_DEFAULT);
          $query = "INSERT INTO users (email, password, text) VALUES ($1, $2, $3)";
          $result = pg_query_params($conn, $query, array($email,$userpassword,"Welcome. Type here to edit your diary."));
          if ($result) {
            // Stores the user data in a session after register
            $query = "SELECT id, email FROM users WHERE email = $1";
            $result = pg_query_params($conn, $query, array($email));
            if ($result) {
              $row = pg_fetch_array($result);
              $_SESSION["user"] = array(
                "userid" => $row["id"],
                "useremail" => $row["email"]
              );
            }
            // Redirects the user to the logged in page
            header("Location: ./loggedin.php");
          }
          else echo "There was an error.";
        }
      }
    } 
    // Login
    else if (isset($_POST["login"])) {
      $query = "SELECT * FROM users WHERE email = $1";
      $result = pg_query_params($conn, $query, array($email));
      if ($result) {
        $row = pg_fetch_array($result);
        // Checks if the user exists and the password is correct
        if ($row && password_verify($userpassword, $row["password"])) {
          $_SESSION["user"] = array(
            "userid" => $row["id"],
            "useremail" => $row["email"]
          );
          // If the user activated the "remember" checkmark, generates a token and a cookie
          if (isset($_POST["remember"]) && $_POST["remember"] == "on") { 
            $selector = base64_encode(random_bytes(9));
            $authenticator = random_bytes(33);

            $query = "INSERT INTO auth_tokens (selector, token, userid, expires) VALUES ($1, $2, $3, $4)";
            $result = pg_query_params($conn, $query, array($selector, password_hash($authenticator, PASSWORD_DEFAULT), $row["id"], date('Y-m-d\TH:i:s',time() + 604800)));
            if ($result) {
            setcookie(
              'remember',
              $selector.':'.base64_encode($authenticator),
              time() + 604800,
              false
            );
            }
          }
        // And finally redirects them to logged in page
        header("Location: ./loggedin.php");
        } else {
          $error = $error . "<li>Wrong username or password</li>";
        }
      }
    }
  }
  // Save any error in a cool Bootstrap format
  if ($error != "") $error = "<div class='alert alert-danger'>The following error(s) were found:<ul>" . $error . "</ul></div>";

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
      background: url(./background.jpg);
      background-size: cover;
      background-repeat: no-repeat;
    }
    .container {
      min-height: 100vh;
    }
    .box {
      background-color: hsl(214, 100%, 5%);
      padding: 2rem;
      border-radius: 0.3rem;
      margin: 1rem 0 1rem 0;
    }
    ul {
      margin-bottom: 0;
    }
  </style>

</head>
<body>
  <div class="container d-flex justify-content-center align-items-center">
    <div class="box text-light text-center">
      <h1>Secret Diary</h1>
      <? echo $error ?>

      <p class="lead">Store your thoughts permanently and securely.</p>

      <div class="d-none" id="registerContainer">
        <p>Interested? Sign up now.</p>
        <form method="post" id="formRegister">
          <div class="form-group">
            <input class="form-control" type="email" name="email" id="inputEmailRegister" placeholder="Email address" required>
          </div>
          <div class="form-group">
            <input class="form-control" type="password" name="password" id="inputPasswordRegister" placeholder="Password" required>
          </div>
          <input class="btn btn-primary mt-3" type="submit" name="register" value="Register">
        </form>
        <p class="mt-3"><a class="text-muted" href="javascript:toggleForm()">Already have an account? Sign in here</a></p>
      </div>

      <div id="loginContainer">
        <p>Login and edit your diary.</p>

        <form method="post" id="formLogin">
          <div class="form-group">
            <input class="form-control" type="email" name="email" id="inputEmailLogin" placeholder="Email address" required>
          </div>
          <div class="form-group">
            <input class="form-control" type="password" name="password" id="inputPasswordLogin" placeholder="Password" required>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="inputRemember">
            <label class="form-check-label" for="inputRemember">Remember</label>
          </div>
          <input class="btn btn-primary mt-3 px-5" type="submit" name="login" value="Login">
        </form>
        <p class="mt-3 mb-0"><a class="text-muted" href="javascript:toggleForm()">Don't have an account? Sign up here</a></p>
      </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

<script>
// Switches visibility between the two forms
function toggleForm() {
  registerForm = document.getElementById("registerContainer");
  loginForm = document.getElementById("loginContainer");
  if (registerForm.classList.contains("d-none")) {
    loginForm.classList.add("d-none");
    registerForm.classList.remove("d-none");
    
  } else {
  registerForm.classList.add("d-none");
  loginForm.classList.remove("d-none");
  }
}
</script>

</body>
</html>