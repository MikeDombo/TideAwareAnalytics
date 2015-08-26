<!-- Author: Michael Dombrowski
Website: MikeDombrowski.com
Github: github.com/md100play/TideAwareAnalytics/
-->
<?php
	$database = "####################";
	$user = "$$$$$$$$$$$$$$";
	$pass = "*******************";
	$link = mysqli_connect("localhost", $user, $pass, $database) or die("Error " . mysqli_error($link));
	$today = date("U", strtotime("today"));
	for ($i=0; $i<3; $i=$i+1){
		$today = date("U", strtotime('-'.$i. 'days', $today));
		doData($link, $today);
	}
	
	function doData($link, $today){
		$result = mysqli_query($link, "SELECT `Date` from `Daily` WHERE `Date`='".$today."'");
		$row = mysqli_fetch_array($result);
		$USUsers = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from (SELECT * FROM `Users` WHERE `US`='1' HAVING `Last Time`< '".date("U", strtotime('+1 day', $today))."') AS X HAVING `Last Time` >= '".$today."') AS T"))[0];
		$nusUsers = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from (SELECT * FROM `Users` WHERE `US`='0' HAVING `Last Time`< '".date("U", strtotime('+1 day', $today))."') AS X HAVING `Last Time` >= '".$today."') AS T"))[0];
		$new = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `First Time` <= '".date("U", strtotime('+1 day', $today))."') AS X HAVING `First Time` >= '".$today."') AS T"))[0];
		$returning = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".$today."') AS X HAVING `First Time` < '".$today."') AS T"))[0];
		$usVer = json_encode(getVersion(mysqli_query($link, "SELECT * from `Users` WHERE `US`='1' HAVING `Last Time` >= '".$today."'")));
		$nusVer = json_encode(getVersion(mysqli_query($link, "SELECT * from `Users` WHERE `US`='0' HAVING `Last Time` >= '".$today."'")));
		
		$res = mysqli_query($link, "SELECT `Last Time`, `Lookup` FROM (SELECT `Last Time`, `Lookup` FROM `Users` HAVING `Last Time`< '".date("U", strtotime('+1 day', $today))."') AS T HAVING `Last Time`>='".$today."'");
		$lookups = 0;
		while($resrow = mysqli_fetch_array($res)){
			$resrow = json_decode($resrow['Lookup'], True);
			foreach($resrow as $k=>$v){
				foreach($v as $s){
					if($s>=intval(date("U", strtotime('-1 day', $today)))){
						$lookups = $lookups+1;
					}
				}
			}
		}
		if ($row != NULL){
			mysqli_query($link, "UPDATE `Daily` SET `US`='".$USUsers."', `Non US`='".$nusUsers."', `New Users`='".$new."', `Returning Users`='".$returning."', `US Versions`='".$usVer."', `Non US Versions`='".$nusVer."', `Lookups`='".$lookups."'WHERE `Date` = '".$today."'");
		}
		else{
			mysqli_query($link, "INSERT INTO `Daily` (`Date`, `US`, `Non US`, `New Users`, `Returning Users`, `US Versions`, `Non US Versions`, `Lookups`) VALUES ('".$today."', '".$USUsers."', '".$nusUsers."', '".$new."', '".$returning."', '".$usVer."', '".$nusVer."', '".$lookups."')");
		}
		echo mysqli_error($link);
	}

	function getVersion($result){
		$dat = array();
		while($row = mysqli_fetch_array($result)){
			if(isset($dat[$row["Version"]])){
				$dat[$row["Version"]] = ["ver"=>$row["US"], "count"=>$dat[$row["Version"]]["count"]+1];
			}
			else{
				$dat[$row["Version"]] = ["ver"=>$row["US"], "count"=>1];
			}
		}
		$final = array();
		foreach($dat as $k=>$v){
			$tmp["label"] = $k;
			$tmp["num"] = $v["count"];
			array_push($final, $tmp);
		}
		return $final;
	}
?>
