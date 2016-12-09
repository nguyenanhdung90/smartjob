<!DOCTYPE html>
<html>
<body>

<?php
/*
$cars = array(
"Daikin Vietnam", "Fujitsu Vietnam", "Bridgestone Vietnam", "Ricoh Imanging Vietnam", "Gendai Sougo", "Nissho Electronics", "Pioneer Soft Vietnam",
"Toyota Corolla Nankai", "JX NOEV", "Yazaki YHV", "Aruku Corp", "Edison Inc", "Joyo Bank", "Nichirei Foods",
"Nifty Corp", "Kuroda Kagaku", "Uchida Yoko", "Tokyo Gas Asia", "Sekisho Group - Energy", "Joyo Bank", "Nichirei Foods",
);
sort($cars);

$clength = count($cars);
for($x = 0; $x < $clength; $x++) {
    echo $cars[$x];
    echo "<br>";
}
*/
/* $string="11,12,19";

$de= explode(",",$string);
if (in_array(11, $de))
{
	echo "Match found";
	unset($de[0]);
    $foo2 = array_values($de);
	var_dump($foo2);$P$Bj53us93Q6xrdsR2IPiaJCl4RTCSwC.
} 
echo md5("DCV-123456!@#$");
*/
$servername = "125.212.225.137";
$username = "smartjobvn";
$password = "DCV@SmartJob#123";
$dbname = "smartjobvn";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "SELECT * FROM wp_posts  ";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
/*      while($row = $result->fetch_assoc()) {
        echo "id: " . $row["ID"]."<br>";
    }  */
	echo $result->num_rows;
} else {
    echo "0 results";
}
$conn->close();
?>

</body>
</html>