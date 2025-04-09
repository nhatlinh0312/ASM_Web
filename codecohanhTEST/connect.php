<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "doboi";

// tao ket noi
$conn = new mysqli($servername, $username, $password, $dbname);

//kiem tra ket noi
if (!$conn) {
    die("Connection fail: " . mysqli_connect_error());

}
