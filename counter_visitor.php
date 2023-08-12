<?php
$hostname = "localhost"; 
$username = "root"; 
$password = ""; 
$database = "visitor_db"; 

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) { 
	die("Connection failed: " . mysqli_connect_error()); 
} 

echo "Database connection is OK"; 

if(isset($_POST["count"])) {

	$v = $_POST["count"];
	

	$sql = "INSERT INTO counter_visitor (count) VALUES (".$v.")"; 

	if (mysqli_query($conn, $sql)) { 
		echo "\nNew record created successfully"; 
	} else { 
		echo "Error: " . $sql . "<br>" . mysqli_error($conn); 
	}
}

?>