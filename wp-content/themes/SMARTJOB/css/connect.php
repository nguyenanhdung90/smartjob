<?php
$servername = "125.212.225.108";
$username = "smartjobvn";
$password = "DCV@Smart#12";
$dbname = "smartjobvn";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM wp_posts where post_status='publish'  ORDER BY ID DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        //echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
		echo $row["post_title"]."|".$row["post_name"];
    }
} else {
    echo "0 results";
}
$conn->close();
?> 
