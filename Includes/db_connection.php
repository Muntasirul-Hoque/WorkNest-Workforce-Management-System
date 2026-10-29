<?php
function getDBConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "worknest_db";  // ← MUST be worknest_db (with 's')
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    mysqli_set_charset($conn, "utf8");
    return $conn;
}
$GLOBALS['conn'] = getDBConnection();
$conn = $GLOBALS['conn'];
?>