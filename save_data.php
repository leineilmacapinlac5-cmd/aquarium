<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sensor_data";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check POST data
if(isset($_POST['water_level']) && isset($_POST['temperature']) && isset($_POST['ph'])) {
    $water_level = $_POST['water_level'];
    $temperature = $_POST['temperature'];
    $ph = $_POST['ph'];

    $sql = "INSERT INTO aquarium_readings (water_level, temperature, ph) 
            VALUES ('$water_level', '$temperature', '$ph')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Data Saved";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "No POST data received";
}

$conn->close();
?>
