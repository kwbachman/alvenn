<link rel="stylesheet" type="text/css" href="table.css">
<style type="text/css">
.zui-table{
    border-collapse: collapse;
}
</style>
<?php
function getLatLong($zip){
	$url = "https://us1.locationiq.com/v1/search.php?key=53a8762756a5cd&postalcode=".urlencode($zip)."&countrycodes=us&format=json";
	$result_string = file_get_contents($url);
	$result = json_decode($result_string, true);
	return $result;
}

function calculateDistance($latitude1, $longitude1, $latitude2, $longitude2) {
	$theta = $longitude1 - $longitude2;
	$miles = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
	$miles = acos($miles);
	$miles = rad2deg($miles);
	$miles = $miles * 60 * 1.1515;
	return $miles;
}

$val = getLatLong($_POST["zip"]);
echo "Zip: ".$_POST["zip"]."<br>";
echo "Latitude: ".$val[0]['lat']."<br>";
echo "Longitude: ".$val[0]['lon']."<br>";
echo "Miles: ".$_POST["miles"]."<br>";

#$servername = "localhost";
$servername = "mysql";
$username = "dba";
$password = "dbaalv1";
$dbname = "alvenn";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT institution.institution_id, institution.name, location.address, location.city, location.state, location.latitude, location.longitude
FROM alvenn.institution, alvenn.location
where institution.institution_id = location.institution_id;";

$result = $conn->query($sql);
$matches = array();
if ($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		// check if the selected distance is within the specified zip code
		$mileCalc = calculateDistance($val[0]['lat'], $val[0]['lon'], $row['latitude'], $row['longitude']);
		// add matching colleges to an html table
		if ($mileCalc < $_POST["miles"])
			$matches[] = $row;
			//echo "<tr><td>".$row["institution_id"]."</td><td>".$row["name"]."</td><td>".$row["city"]."</td><td>".$row["state"]."</td></tr>";
	}
	echo "Result Count: ".count($matches)."<br>";
	echo "<table class=\"zui-table\"><tr><th>ID</th><th>Name</th><th>City</th><th>State</th></tr>";
	foreach ($matches as $match) {
		//echo $product['name'] . ': $' . $product['price'] . "<br />\n";
		echo "<tr><td>".$match["institution_id"]."</td><td>".$match["name"]."</td><td>".$match["city"]."</td><td>".$match["state"]."</td></tr>";
	}
	echo "</table>";
} else {
	echo "0 results";
}

echo "<br>";

/* $result->data_seek(0);
if ($result->num_rows > 0) {
	echo "<table id=\"colleges\"><tr><th>College</th><th>Nickname</th></tr>";
	// output data of each row
	while($row = $result->fetch_assoc()) {
		echo "<tr><td>".$row["institution_name"]."</td><td>".$row["alias"]."</td></tr>";
	}
	echo "</table>";
} else {
	echo "0 results";
} */ 

$conn->close();

?>