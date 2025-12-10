<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sensor_data";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$sql = "SELECT * FROM aquarium_readings ORDER BY id DESC";
$result = $conn->query($sql);

$ids = $waterLevels = $temps = $phs = $timestamps = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $ids[] = $row["id"];
        $waterLevels[] = $row["water_level"];
        $temps[] = $row["temperature"];
        $phs[] = $row["ph"];
        $timestamps[] = $row["timestamp"];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Aquarium Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta http-equiv="refresh" content="5">
    <style>
        body { font-family: Arial; background:#f4f4f4; padding:20px;}
        h1{text-align:center;}
        table{margin:20px auto; border-collapse:collapse; width:80%; background:#fff; box-shadow:0 0 10px rgba(0,0,0,0.1);}
        th,td{padding:12px; border:1px solid #ddd; text-align:center;}
        th{background:#007BFF; color:#fff;}
        tr:nth-child(even){background:#f9f9f9;}
        canvas{display:block; margin:0 auto; background:#fff; padding:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
    </style>
</head>
<body>
    <h1>Aquarium Sensor Dashboard</h1>
    <canvas id="sensorChart" width="800" height="400"></canvas>
    <script>
        const ctx = document.getElementById('sensorChart').getContext('2d');
        const sensorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($timestamps); ?>,
                datasets: [
                    { label:'Water Level (%)', data: <?php echo json_encode($waterLevels); ?>, borderColor:'rgba(54,162,235,1)', backgroundColor:'rgba(54,162,235,0.2)', tension:0.3 },
                    { label:'Temperature (°C)', data: <?php echo json_encode($temps); ?>, borderColor:'rgba(255,99,132,1)', backgroundColor:'rgba(255,99,132,0.2)', tension:0.3 },
                    { label:'pH', data: <?php echo json_encode($phs); ?>, borderColor:'rgba(75,192,192,1)', backgroundColor:'rgba(75,192,192,0.2)', tension:0.3 }
                ]
            },
            options: {
                responsive:true,
                plugins:{ legend:{position:'top'}, title:{display:true, text:'Aquarium Sensor Readings'} },
                scales:{ x:{title:{display:true, text:'Timestamp'}, ticks:{maxRotation:90,minRotation:45}}, y:{beginAtZero:true} }
            }
        });
    </script>

    <table>
        <caption>All Readings</caption>
        <tr>
            <th>#</th><th>Water Level (%)</th><th>Temperature (°C)</th><th>pH</th><th>Timestamp</th>
        </tr>
        <?php
        if (!empty($ids)) {
            for ($i=0;$i<count($ids);$i++){
                echo "<tr>
                        <td>{$ids[$i]}</td>
                        <td>{$waterLevels[$i]}</td>
                        <td>{$temps[$i]}</td>
                        <td>{$phs[$i]}</td>
                        <td>{$timestamps[$i]}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No data available</td></tr>";
        }
        ?>
    </table>
</body>
</html>
