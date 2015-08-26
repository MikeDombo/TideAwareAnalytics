<!-- Author: Michael Dombrowski
Website: MikeDombrowski.com
Github: github.com/md100play/TideAwareAnalytics/
-->
<?php

$database = "################";
$user = "#################";
$pass = "**********************";

$link = mysqli_connect("localhost", $user, $pass, $database) or die("Error " . mysqli_error($link));


function distance($lat1, $lon1, $lat2, $lon2) {
	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	return $miles;
}

if (isset($_GET['location'])){
	if (isset($_GET['ID'])){
		$us = 0;
			if ($_GET['US'] == "TRUE"){
				$us = 1;
			}
		$result = mysqli_query($link, "SELECT `Unique` from `Users` WHERE `ID`='".$_GET['ID']."'");
		$row = mysqli_fetch_array($result);
		if ($row != NULL){
			mysqli_query($link, "UPDATE `Users` SET `Last Time`='".time()."' WHERE `ID`= '".$_GET['ID']."'");
			mysqli_query($link, "UPDATE `Users` SET `Tide Locations`='".$_GET['settings']."' WHERE `ID`= '".$_GET['ID']."'");
			mysqli_query($link, "UPDATE `Users` SET `Version`='".$_GET['ver']."' WHERE `ID`= '".$_GET['ID']."'");
			mysqli_query($link, "UPDATE `Users` SET `US`='".$us."' WHERE `ID`= '".$_GET['ID']."'");
			}
		else {
			$unique = intval(mysqli_fetch_array(mysqli_query($link, "SELECT `Unique` from `Users` ORDER BY `Unique` DESC LIMIT 1"))[0])+1;
			mysqli_query($link, "INSERT INTO `Users` (`Unique`, `ID`, `First Time`, `Last Time`, `Tide Locations`, `Version`, `US`, `Lookup`) VALUES ('".$unique."', '".$_GET['ID']."', '".time()."', '".time()."', '".$_GET['settings']."', '".$_GET['ver']."', '".$us."', '".json_encode(array())."')");
			}
		
		$table = "Users";
		$loc = $_GET['location'];
		$row = mysqli_fetch_array(mysqli_query($link, "SELECT * from `Users` WHERE `ID`='".$_GET['ID']."'"));
		if(isset($row['Lookup'])){
			$arr = json_decode($row['Lookup'], True);
			if (isset($arr[$loc])){
				array_unshift($arr[$loc], strval(time()));
			}
			else if (explode(",", $loc)[0] != $loc && count($arr)>0){
				$close=False;
				foreach ($arr as $k => $v){
					if (explode(",", $k)[0] != strlen($k) && count(explode(",", $k))>1 && count(explode(",", $loc))>1){
						$lat1 = floatval(explode(",", $k)[0]);
						$lon1 = floatval(explode(",", $k)[1]);
						$lat2 = floatval(explode(",", $loc)[0]);
						$lon2 = floatval(explode(",", $loc)[1]);
						$distance = distance($lat1, $lon1, $lat2, $lon2);
						if ($distance <= 25){
							array_unshift($v, time());
							$close=True;
							}
						}
					}
			if(!$close){
				$arr[$loc] = array(strval(time()));
				}
			}
			else{
				$arr[$loc] = array(strval(time()));
			}
			mysqli_query($link, "UPDATE `Users` SET `Lookup`='".json_encode($arr)."' WHERE `ID`= '".$_GET['ID']."'");
		}
	}
}

?>
