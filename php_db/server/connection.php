<?php

$host = $_ENV["PGHOST"];
$port = 5432;
$user = $_ENV["PGUSER"];
$password = $_ENV["PGPASSWORD"];
$dbname = $user;

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
return $conn;

?>