<?php
	$database = "$$$$$$$$$$$$$$$$$$";
	$user = "*****************";
	$pass = "####################";
	$link = mysqli_connect("localhost", $user, $pass, $database) or die("Error " . mysqli_error($link));
	
	$startDate = date("U", strtotime("today -1 week"));
	$endDate = date("U", strtotime("today"));
	
	if (isset($_GET['start']) || isset($_GET['end'])){
		if(strtotime($_GET['start'])>0){
			$startDate = strtotime($_GET['start']);
			if(!strtotime($_GET['end'])>0){
				$endDate = date("U", strtotime("+1 day", $startDate));
			}
		}
		if(strtotime($_GET['end'])>0){
			$endDate = strtotime($_GET['end']);
			if(!strtotime($_GET['start'])>0){
				$startDate = date("U", strtotime("-1 day", $endDate));
			}
		}
	}
	
	function generateChart($result){
		$final = array();
		$dat = array();
		while($row = mysqli_fetch_array($result)){
			if(isset($row['US Versions'])){
				$us = json_decode($row['US Versions'], True);
				foreach($us as $a){
					if(isset($dat[$a['label']])){
						$dat[$a['label']] = ["ver"=>1, "count"=>$dat[$a['label']]["count"]+1];
					}
					else{
						$dat[$a['label']] = ["ver"=>1, "count"=>$a['num']];
					}
				}
			}
			if(isset($row['Non US Versions'])){
				$nus = json_decode($row['Non US Versions'], True);
				foreach($nus as $a){
					if(isset($dat[$a['label']])){
						$dat[$a['label']] = ["ver"=>0, "count"=>$dat[$a['label']]["count"]+1];
					}
					else{
						$dat[$a['label']] = ["ver"=>0, "count"=>$a['num']];
					}
				}
			}			
		}
		foreach($dat as $k=>$v){
			$ver = $v["ver"];
			if($ver == 1){
				$ver = "US Version";
			}
			else{
				$ver = "Non-US Version";
			}
			$tmp["label"] = $ver." ".$k;
			$tmp["data"] = $v["count"];
			array_push($final, $tmp);
		}
		return $final;
	}

	function generateChart2($result){
		$final = array();
		$dat = array();
		while($row = mysqli_fetch_array($result)){
			if(isset($row['US Versions'])){
				$us = json_decode($row['US Versions'], True);
				foreach($us as $a){
					if(isset($dat[$a['label']])){
						$dat[$a['label']] = ["ver"=>1, "count"=>$dat[$a['label']]["count"]+1];
					}
					else{
						$dat[$a['label']] = ["ver"=>1, "count"=>$a['num']];
					}
				}
			}
			if(isset($row['Non US Versions'])){
				$nus = json_decode($row['Non US Versions'], True);
				foreach($nus as $a){
					if(isset($dat[$a['label']])){
						$dat[$a['label']] = ["ver"=>0, "count"=>$dat[$a['label']]["count"]+1];
					}
					else{
						$dat[$a['label']] = ["ver"=>0, "count"=>$a['num']];
					}
				}
			}			
		}
		foreach($dat as $k=>$v){
			$ver = $v["ver"];
			if($ver == 1){
				$ver = "US Version";
			}
			else{
				$ver = "Non-US Version";
			}
			$found = false;
			for($i=0; $i< count($final); $i=$i+1){
				if($final[$i]['label'] == $ver){
					$final[$i]['data'] = $final[$i]['data']+$v['count'];
					$found = true;
				}
			}
			if($found==false){
				$tmp["label"] = $ver;
				$tmp["data"] = $v["count"];
				array_push($final, $tmp);
			}
		}
		return $final;
	}
?>

<html>
	<head>
		<title>Tide Aware Analytics</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"></link>
		<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		<style type="text/css">
			body {
				padding-top: 50px;
			} 
			.piechart {
				position: relative;
				width: 100%;
				height: 100%;
				max-height: 400px;
			}
			.zoom-plot {
				position: relative;
				width: 100%;
				height: 100%;
				max-height: 400px;
			}
			.flotTip {
				  padding: 3px 5px;
				  background-color: #000;
				  z-index: 100;
				  color: #fff;
				  box-shadow: 0 0 10px #555;
				  opacity: .7;
				  filter: alpha(opacity=70);
				  border: 2px solid #fff;
				  -webkit-border-radius: 4px;
				  -moz-border-radius: 4px;
				  border-radius: 4px;
			}
			.vdivide [class*='col-']:after {
			  background: #e0e0e0;
			  width: 1px;
			  content: "";
			  display:block;
			  position: absolute;
			  top:0;
			  bottom: 0;
			  right: 0;
			  min-height: 70px;
			}
			@media (min-width: 768px){#date{padding-right: 30px; padding-top: 10px;}}
			@media (max-width: 768px){#date{padding-left: 20px;} body{padding-top: 120px;}}
		</style>
	</head>
	<body>
		<script>
			$(function() {
				$("#from").datepicker({
				  defaultDate: "-1w",
				  changeMonth: true,
				  numberOfMonths: 1,
				  onClose: function( selectedDate ) {
					$("#to").datepicker( "option", "minDate", selectedDate );
					document.location = "<?php echo "?start=";?>"+selectedDate<?php 
					if(isset($_GET['end'])){
						echo "+\"&end=".date("m/d/Y", $endDate)."\"";
					}?>;
				  }
				});
				$("#to").datepicker({
				  defaultDate: "today",
				  changeMonth: true,
				  numberOfMonths: 1,
				  onClose: function( selectedDate ) {
					$("#from").datepicker( "option", "maxDate", selectedDate );
					document.location = "<?php
					if(isset($_GET['start'])){
						echo "?start=".date("m/d/Y", $startDate)."&end=";
					}
					else {
						echo "?end=";
					}
					?>"+selectedDate;
				  }
				});
			});
		</script>
		<nav class="navbar navbar-inverse navbar-fixed-top">
				<div class="navbar-header">
					<a class="navbar-brand" href="#">Tide Aware Analytics</a>
				</div>
				<ul class="nav navbar-nav navbar-right" id="date">
					<li>
						<label for="from" style="color: #9d9d9d;">From</label>
						<input type="text" id="from" name="from" placeholder="<?php echo date("m/d/Y", $startDate);?>">
					</li>
					<li>
						<label for="to" style="color: #9d9d9d;">To</label>
						<input type="text" id="to" name="to" placeholder="<?php echo date("m/d/Y", $endDate);?>">
					</li>
				 </ul>
		</nav>
		
		<div class="container-fluid">
			<div class="row vdivide">
					<div class="col-md-4">
					<script type="text/javascript">
						function since(){
							window.alert("Counting began <?php echo date("U", 1439933414);?>");
						}
					</script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.js"></script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.pie.js"></script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.tooltip.js"></script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.time.js"></script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.selection.js"></script>
					<script language="javascript" type="text/javascript" src="/flot/jquery.flot.stack.js"></script>
					
					<h2><a href="#" onclick="since()">Total Number of Users Ever: <?php echo(intval(mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) from `Users`"))[0]));?></a></h2>
					<h3>Total US Users: <?php echo(intval(mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) from `Users` WHERE `US`='1'"))[0]));?></h3>
					<h3>Total Non-US Users: <?php echo(intval(mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) from `Users` WHERE `US`='0'"))[0]));?></h3>
					<hr>
					<h2>Total Number of Users Past Month: <?php echo mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from `Users` HAVING `Last Time` > '".intval(date("U", strtotime("-1 month")))."') AS T"))[0];?></h2>
					<h3>US Users In Past Month: <?php echo mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from `Users` WHERE `US`='1' HAVING `Last Time` > '".intval(date("U", strtotime("-1 month")))."') AS T"))[0];?></h3>
					<h3>Non-US Users In Past Month: <?php echo mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from `Users` WHERE `US`='0' HAVING `Last Time` > '".intval(date("U", strtotime("-1 month")))."') AS T"))[0];?></h3>
					<hr>
					<h2>Total Number of Users Past Week: <?php echo mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from `Users` HAVING `Last Time` > '".intval(date("U", strtotime("-1 week")))."') AS T"))[0];?></h2>
					<h3>US Users In Past Week: <?php echo mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from `Users` WHERE `US`='1' HAVING `Last Time` > '".intval(date("U", strtotime("-1 week")))."') AS T"))[0];?></h3>
					<h3>Non-US Users In Past Week: <?php echo mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from `Users` WHERE `US`='0' HAVING `Last Time` > '".intval(date("U", strtotime("-1 week")))."') AS T"))[0];?></h3>
					<hr>
					<h2>Total Number of Users: <?php echo mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from (SELECT `Last Time` from `Users` HAVING `Last Time` >= '".$startDate."') AS T HAVING `Last Time`< '".$endDate."') AS X"))[0];?></h2>
					<h3>US Users: <?php echo mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from (SELECT `Last Time` from `Users` WHERE `US` = '1' HAVING `Last Time` >= '".$startDate."') AS T HAVING `Last Time`< '".$endDate."') AS X"))[0];?></h3>
					<h3>Non-US Users: <?php echo mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from (SELECT `Last Time` from `Users` WHERE `US` = '0' HAVING `Last Time` >= '".$startDate."') AS T HAVING `Last Time`< '".$endDate."') AS X"))[0];?></h3>
					<hr>
					<h2>Total Lookups: <?php 
						$result = mysqli_query($link, "SELECT `Lookup`, `Last Time` from (SELECT `Lookup`, `Last Time` from `Users` HAVING `Last Time` >= '".$startDate."') AS T");
						$count = 0;
						while($row = mysqli_fetch_array($result)){
							$row = json_decode($row['Lookup'], True);
							foreach($row as $k=>$v){
								foreach($v as $s){
									if($s>=intval($startDate) && $s<intval($endDate)){
										$count = $count+1;
									}
								}
							}
						}
						echo $count;
					?></h2>
					<hr>
					<h2>Average Lookups Per User: <?php 
						$result = mysqli_query($link, "SELECT `Lookup` from `Users`");
						$arr = array();
						while($row = mysqli_fetch_array($result)){
							$row = json_decode($row['Lookup'], True);
							foreach($row as $k=>$v){
								array_push($arr, count($v));
							}
						}
						echo substr(array_sum($arr) / count($arr), 0, 5);
					?></h2>
				</div>
				<div class="col-md-4">
					<?php
					$result = mysqli_query($link, "SELECT `Lookup` from `Users`; ");
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							if(isset($arr[$k])){
								$arr[$k] = $arr[$k]+count($v);
							}
							else {
								$arr[$k] = count($v);
							}
						}
					}
					arsort($arr);
					$alltime = array();
					foreach($arr as $k=>$v){
						array_push($alltime, $k);
						array_push($alltime, $v);
					}
					
					$result = mysqli_query($link, "SELECT `Lookup` FROM (SELECT * FROM `Users` HAVING `Last Time` > '".intval(date("U", strtotime("-1 month")))."') AS T");
					echo mysqli_error($link);
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							if(isset($arr[$k])){
								$arr[$k] = $arr[$k]+count($v);
							}
							else {
								$arr[$k] = count($v);
							}
						}
					}
					arsort($arr);
					$month = array();
					foreach($arr as $k=>$v){
						array_push($month, $k);
						array_push($month, $v);
					}
					
					$result = mysqli_query($link, "SELECT `Lookup` from (SELECT * from `Users` HAVING `Last Time` > '".intval(date("U", strtotime("-1 week")))."') AS T");
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							if(isset($arr[$k])){
								$arr[$k] = $arr[$k]+count($v);
							}
							else {
								$arr[$k] = count($v);
							}
						}
					}
					arsort($arr);
					$week = array();
					foreach($arr as $k=>$v){
						array_push($week, $k);
						array_push($week, $v);
					}
					
					$result = mysqli_query($link, "SELECT `Lookup` from (SELECT `Lookup`, `Last Time` from (SELECT `Lookup`, `Last Time` from `Users` HAVING `Last Time` >= '".$startDate."') AS T HAVING `Last Time`< '".$endDate."') AS X");
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							if(isset($arr[$k])){
								$arr[$k] = $arr[$k]+count($v);
							}
							else {
								$arr[$k] = count($v);
							}
						}
					}
					arsort($arr);
					$day = array();
					foreach($arr as $k=>$v){
						array_push($day, $k);
						array_push($day, $v);
					}
					?>
					<h2>Top Locations of All Time:</h2> <div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Lookups</th></tr></thread>
					<tr><td><?php echo $alltime[0]?></td><td><?php echo $alltime[1]?></td></tr>
					<tr><td><?php echo $alltime[2]?></td><td><?php echo $alltime[3]?></td></tr>
					<tr><td><?php echo $alltime[4]?></td><td><?php echo $alltime[5]?></td></tr>
					</table></div>
					<hr>
					<h3>Top Locations of Last Month:</h3><div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Lookups</th></tr></thread>
					<tr><td><?php echo $month[0]?></td><td><?php echo $month[1]?></td></tr>
					<tr><td><?php echo $month[2]?></td><td><?php echo $month[3]?></td></tr>
					<tr><td><?php echo $month[4]?></td><td><?php echo $month[5]?></td></tr>
					</table></div>
					<hr>
					<h3>Top Locations of Last Week:</h3><div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Lookups</th></tr></thread>
					<tr><td><?php echo $week[0]?></td><td><?php echo $week[1]?></td></tr>
					<tr><td><?php echo $week[2]?></td><td><?php echo $week[3]?></td></tr>
					<tr><td><?php echo $week[4]?></td><td><?php echo $week[5]?></td></tr>
					</table></div>
					<hr>
					<h3>Top Locations:</h3><div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Lookups</th></tr></thread>
					<tr><td><?php echo $day[0]?></td><td><?php echo $day[1]?></td></tr>
					<tr><td><?php echo $day[2]?></td><td><?php echo $day[3]?></td></tr>
					<tr><td><?php echo $day[4]?></td><td><?php echo $day[5]?></td></tr>
					</table></div>
				</div>
				<div class="col-md-4">
					<?php
					$result = mysqli_query($link, "SELECT `Lookup` from `Users`; ");
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							if(isset($arr[$k])){
								$arr[$k] = $arr[$k]+1;
							}
							else {
								$arr[$k] = 1;
							}
						}
					}
					arsort($arr);
					$alltime = array();
					foreach($arr as $k=>$v){
						array_push($alltime, $k);
						array_push($alltime, $v);
					}
					
					$result = mysqli_query($link, "SELECT `Lookup` from (SELECT * from `Users` HAVING `Last Time` > '".intval(date("U", strtotime("-1 month")))."') AS T");
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							if(isset($arr[$k])){
								$arr[$k] = $arr[$k]+1;
							}
							else {
								$arr[$k] = 1;
							}
						}
					}
					arsort($arr);
					$month = array();
					foreach($arr as $k=>$v){
						array_push($month, $k);
						array_push($month, $v);
					}
					
					$result = mysqli_query($link, "SELECT `Lookup` from (SELECT * from `Users` HAVING `Last Time` > '".intval(date("U", strtotime("-1 week")))."') AS T");
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							if(isset($arr[$k])){
								$arr[$k] = $arr[$k]+1;
							}
							else {
								$arr[$k] = 1;
							}
						}
					}
					arsort($arr);
					$week = array();
					foreach($arr as $k=>$v){
						array_push($week, $k);
						array_push($week, $v);
					}
					
					$result = mysqli_query($link, "SELECT `Lookup` from (SELECT `Lookup`, `Last Time` from (SELECT `Lookup`, `Last Time` from `Users` HAVING `Last Time` >= '".$startDate."') AS T HAVING `Last Time`< '".$endDate."') AS X");
					$arr = array();
					while($row = mysqli_fetch_array($result)){
						$row = json_decode($row['Lookup'], True);
						foreach($row as $k=>$v){
							if(isset($arr[$k])){
								$arr[$k] = $arr[$k]+1;
							}
							else {
								$arr[$k] = 1;
							}
						}
					}
					arsort($arr);
					$day = array();
					foreach($arr as $k=>$v){
						array_push($day, $k);
						array_push($day, $v);
					}
					?>
					<h2>Most Common Locations of All Time:</h2><div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Users</th></tr></thread>
					<tr><td><?php echo $alltime[0]?></td><td><?php echo $alltime[1]?></td></tr>
					<tr><td><?php echo $alltime[2]?></td><td><?php echo $alltime[3]?></td></tr>
					<tr><td><?php echo $alltime[4]?></td><td><?php echo $alltime[5]?></td></tr>
					</table></div>
					<hr>
					<h3>Most Common Locations of Last Month:</h3><div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Users</th></tr></thread>
					<tr><td><?php echo $month[0]?></td><td><?php echo $month[1]?></td></tr>
					<tr><td><?php echo $month[2]?></td><td><?php echo $month[3]?></td></tr>
					<tr><td><?php echo $month[4]?></td><td><?php echo $month[5]?></td></tr>
					</table></div>
					<hr>
					<h3>Most Common Locations of Last Week:</h3><div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Users</th></tr></thread>
					<tr><td><?php echo $week[0]?></td><td><?php echo $week[1]?></td></tr>
					<tr><td><?php echo $week[2]?></td><td><?php echo $week[3]?></td></tr>
					<tr><td><?php echo $week[4]?></td><td><?php echo $week[5]?></td></tr>
					</table></div>
					<hr>
					<h3>Most Common Locations:</h3><div class="table-responsive"><table class="table table-striped"><thread><tr><th>Location</th><th>Number of Users</th></tr></thread>
					<tr><td><?php echo $day[0]?></td><td><?php echo $day[1]?></td></tr>
					<tr><td><?php echo $day[2]?></td><td><?php echo $day[3]?></td></tr>
					<tr><td><?php echo $day[4]?></td><td><?php echo $day[5]?></td></tr>
					</table></div>
				</div>
			</div>
			<hr width=100%>
			<div class="row vdivide">
				<div class="col-md-6">
					<h1>Daily Unique Users</h1>
					<script type="text/javascript">
						$(function() {
							var d = <?php 
								$result = mysqli_query($link, "SELECT * from `Daily`");
								$US = array();
								$nUS = array();
								while($row = mysqli_fetch_array($result)){
									if(intval($row['Date']) != strtotime("today")){
										array_push($US, array(intval($row['Date']), intval($row['US'])));
										array_push($nUS, array(intval($row['Date']), intval($row['Non US'])));
									}
									else{
										array_push($US, array(intval($row['Date']), intval(mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from (SELECT * FROM `Users` WHERE `US`='1' HAVING `Last Time`< '".time()."') AS X HAVING `Last Time` >= '".date("U", strtotime("today"))."') AS T"))[0])));
										array_push($nUS, array(intval($row['Date']), intval(mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT `Last Time` from (SELECT * FROM `Users` WHERE `US`='0' HAVING `Last Time`< '".time()."') AS X HAVING `Last Time` >= '".date("U", strtotime("today"))."') AS T"))[0])));
									}
								}
								echo "[{label: 'US Users', data: ".json_encode($US)."}, {label: 'Non-US Users', data: ".json_encode($nUS)."}];"
							?>
							
							for (var i = 0; i < d[0]['data'].length; ++i) {
								d[0]['data'][i][0] *= 1000;
							}
							for (var i = 0; i < d[1]['data'].length; ++i) {
								d[1]['data'][i][0] *= 1000;
							}

							function weekendAreas(axes) {
								var markings = [],
									d = new Date(axes.xaxis.min);
								d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
								d.setUTCSeconds(0);
								d.setUTCMinutes(0);
								d.setUTCHours(0);
								var i = d.getTime();
								do {
									markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
									i += 7 * 24 * 60 * 60 * 1000;
								} while (i < axes.xaxis.max);

								return markings;
							}

							var options = {
								xaxis: {
									mode: "time",
									tickSize: [1, "day"],
									tickLength: 5
								},
								selection: {
									mode: "x"
								},
								grid: {
									markings: weekendAreas,
									hoverable: true,
									clickable: true
								},
								series: {
									points: {
										show:true
									},
									lines: {
										show: true,
										lineWidth: 1,
										fill: true
									},
									stack: true
								},
								tooltip: {
									show: true,
									content: "%x: %s = %y",
									shifts: {
									  x: 20,
									  y: 0
									},
									defaultTheme: false
								}
							};

							var plot = $.plot("#visitors", d, options);
							var overview = $.plot("#overview", d, {
								series: {
									lines: {
										show: true,
										lineWidth: 1
									},
									shadowSize: 0
								},
								xaxis: {
									ticks: [],
									mode: "time"
								},
								yaxis: {
									ticks: [],
									min: 0,
									autoscaleMargin: 0.1
								},
								selection: {
									mode: "x"
								},
								legend: {
									show:false
								}
							});
							
							$("#visitors").bind("plotselected", function (event, ranges) {
								$.each(plot.getXAxes(), function(_, axis) {
									var opts = axis.options;
									opts.min = ranges.xaxis.from;
									opts.max = ranges.xaxis.to;
								});
								plot.setupGrid();
								plot.draw();
								plot.clearSelection();

								// don't fire event on the overview to prevent eternal loop

								overview.setSelection(ranges, true);
							});

							$("#overview").bind("plotselected", function (event, ranges) {
								plot.setSelection(ranges);
							});
							
							$("#overview").bind("plotunselected", function (event) {
								var axes = plot.getAxes(),
									xaxis = axes.xaxis.options,
									yaxis = axes.yaxis.options;
								xaxis.min = null;
								xaxis.max = null;
								yaxis.min = null;
								yaxis.max = null;
								plot.setupGrid();
								plot.draw();
							});
							
							$("#visitors").bind("plothover", function(event, pos, obj) {
								if (!obj) {
									return;
								}
								$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
							});

							$("#visitors").bind("plotclick", function (event, pos, item) {
								if (item) {
									$("#clickdata").text(" - click point " + item.dataIndex + " in " + item.series.label);
									plot.highlight(item.series, item.datapoint);
									if (!obj) {
										return;
									}
									$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
								}
							});
						});
					</script>
					<div class="zoom-plot">
						<div id="visitors" class="zoom-plot"></div>
					</div>
					<div class="zoom-plot" style="height:100px;">
						<div id="overview" class="zoom-plot"></div>
					</div>
				</div>
				<div class="col-md-6">
					<h1>Total Lookups</h1>
					<script type="text/javascript">
						$(function() {
							var d = <?php 
								$result = mysqli_query($link, "SELECT `Lookups`, `Date` from `Daily`");
								$arr = array();
								while($row = mysqli_fetch_array($result)){
									array_push($arr, array(intval($row['Date']), intval($row['Lookups'])));
								}
								echo "[{label: 'Total Lookups', data: ".json_encode($arr)."}];"
							?>
							
							for (var i = 0; i < d[0]['data'].length; ++i) {
								d[0]['data'][i][0] *= 1000;
							}

							function weekendAreas(axes) {
								var markings = [],
									d = new Date(axes.xaxis.min);
								d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
								d.setUTCSeconds(0);
								d.setUTCMinutes(0);
								d.setUTCHours(0);
								var i = d.getTime();
								do {
									markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
									i += 7 * 24 * 60 * 60 * 1000;
								} while (i < axes.xaxis.max);

								return markings;
							}

							var options = {
								xaxis: {
									mode: "time",
									tickSize: [1, "day"],
									tickLength: 5
								},
								selection: {
									mode: "x"
								},
								grid: {
									markings: weekendAreas,
									hoverable: true,
									clickable: true
								},
								series: {
									points: {
										show:true
									},
									lines: {
										show: true,
										lineWidth: 1,
										fill: true
									},
									stack: true
								},
								tooltip: {
									show: true,
									content: "%x: %s = %y",
									shifts: {
									  x: 20,
									  y: 0
									},
									defaultTheme: false
								}
							};

							var plot2 = $.plot("#lookups", d, options);
							var overview2 = $.plot("#overview2", d, {
								series: {
									lines: {
										show: true,
										lineWidth: 1
									},
									shadowSize: 0
								},
								xaxis: {
									ticks: [],
									mode: "time"
								},
								yaxis: {
									ticks: [],
									min: 0,
									autoscaleMargin: 0.1
								},
								selection: {
									mode: "x"
								},
								legend: {
									show:false
								}
							});
							
							$("#lookups").bind("plotselected", function (event, ranges) {
								$.each(plot2.getXAxes(), function(_, axis) {
									var opts = axis.options;
									opts.min = ranges.xaxis.from;
									opts.max = ranges.xaxis.to;
								});
								plot2.setupGrid();
								plot2.draw();
								plot2.clearSelection();

								// don't fire event on the overview2 to prevent eternal loop

								overview2.setSelection(ranges, true);
							});

							$("#overview2").bind("plotselected", function (event, ranges) {
								plot2.setSelection(ranges);
							});
							
							$("#overview2").bind("plotunselected", function (event) {
								var axes = plot2.getAxes(),
									xaxis = axes.xaxis.options,
									yaxis = axes.yaxis.options;
								xaxis.min = null;
								xaxis.max = null;
								yaxis.min = null;
								yaxis.max = null;
								plot2.setupGrid();
								plot2.draw();
							});
							
							$("#lookups").bind("plothover", function(event, pos, obj) {
								if (!obj) {
									return;
								}
								$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+")</span>");
							});

							$("#lookups").bind("plotclick", function (event, pos, item) {
								if (item) {
									$("#clickdata").text(" - click point " + item.dataIndex + " in " + item.series.label);
									plot.highlight(item.series, item.datapoint);
									if (!obj) {
										return;
									}
									$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
								}
							});
						});
					</script>
					<div class="zoom-plot">
						<div id="lookups" class="zoom-plot"></div>
					</div>
					<div class="zoom-plot" style="height:100px;">
						<div id="overview2" class="zoom-plot"></div>
					</div>
				</div>
			</div>
			<hr width=100%>
			<div class="row vdivide">
				<div class="col-md-12">
					<h1>Version Analytics</h1>
					<hr>
					<div class="row">
						<div class="col-sm-3">
							<h2>All Versions</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php echo json_encode(generateChart(mysqli_query($link, "SELECT `US Versions`, `Non US Versions`, `Date` FROM (SELECT `US Versions`, `Non US Versions`, `Date` FROM `Daily` HAVING `Date` >= '".$startDate."') AS X HAVING `Date` < '".$endDate."'")));
									?>;
									var placeholder = $("#placeholder");
									placeholder.unbind();
									$.plot(placeholder, data, {
										series: {
											pie: { 
												innerRadius: 0.5,
												show: true,
												label: {
													show: true,
													radius: .65,
													background: {
														opacity: 0.7,
														color: '#000'
													}
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: false},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  }
									});
									placeholder.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
								function labelFormatter(label, series) {
									return "<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>" + label + "<br/>" + Math.round(series.percent) + "%</div>";
								}
							</script>
							<div id="placeholder" class="piechart"></div>
						</div>
						<div class="col-sm-3">
							<h2>US Versions</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php echo json_encode(generateChart(mysqli_query($link, "SELECT `US Versions`, `Date` FROM (SELECT `US Versions`, `Date` FROM `Daily` HAVING `Date` >= '".$startDate."') AS X HAVING `Date` < '".$endDate."'")));?>;
									var placeholder2 = $("#placeholder2");
									placeholder2.unbind();
									$.plot(placeholder2, data, {
										series: {
											pie: { 
												innerRadius: 0.5,
												show: true,
												label: {
													show: true,
													radius: .65,
													background: {
														opacity: 0.7,
														color: '#000'
													}
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: false},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  }
									});
									placeholder2.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder2.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder2" class="piechart"></div>
						</div>
						<div class="col-sm-3">
							<h2>Non-US Versions</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php echo json_encode(generateChart(mysqli_query($link, "SELECT `Non US Versions`, `Date` FROM (SELECT `Non US Versions`, `Date` FROM `Daily` HAVING `Date` >= '".$startDate."') AS X HAVING `Date` < '".$endDate."'")));?>;
									var placeholder3 = $("#placeholder3");
									placeholder3.unbind();
									$.plot(placeholder3, data, {
										series: {
											pie: { 
												innerRadius: 0.5,
												show: true,
												label: {
													show: true,
													radius: .65,
													background: {
														opacity: 0.7,
														color: '#000'
													}
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: false},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  }
									});
									placeholder3.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder3.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder3" class="piechart"></div>
						</div>
						<div class="col-sm-3">
							<h2>US vs. Non-US</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php 
									echo json_encode(generateChart2(mysqli_query($link, "SELECT `US Versions`, `Non US Versions`, `Date` FROM (SELECT `US Versions`, `Non US Versions`, `Date` FROM `Daily` HAVING `Date` >= '".$startDate."') AS X HAVING `Date` < '".$endDate."'")));?>;
									var placeholder6 = $("#placeholder6");
									placeholder6.unbind();
									$.plot(placeholder6, data, {
										series: {
											pie: { 
												innerRadius: 0.5,
												show: true,
												label: {
													show: true,
													radius: .65,
													background: {
														opacity: 0.7,
														color: '#000'
													}
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: false},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  }
									});
									placeholder6.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder6.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder6" class="piechart"></div>
						</div>
					</div>
				</div>
			</div>
			<hr width="100%"></hr>
			
			<div class="row vdivide">
				<div class="col-md-6">
					<h2>New vs. Returning</h2>
					<hr>
					<div class="row">
						<div class="col-sm-4">
							<h2>Past Month</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php 
									$new = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".date("U", strtotime("-1 month"))."') AS X HAVING `First Time` >= '".date("U", strtotime("-1 month"))."') AS T"))[0];
									$returning = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".date("U", strtotime("-1 month"))."') AS X HAVING `First Time` < '".date("U", strtotime("-1 month"))."') AS T"))[0];
									$arr = [['label'=>"New Users", 'data'=>$new], ['label'=>"Returning Users", 'data'=>$returning]];
									echo json_encode($arr);
									?>;
									var placeholder4 = $("#placeholder4");
									placeholder4.unbind();
									$.plot(placeholder4, data, {
										series: {
											pie: { 
												innerRadius: 0.5,
												show: true,
												label: {
													show: true,
													radius: .65,
													background: {
														opacity: 0.7,
														color: '#000'
													}
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: false},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  }
									});
									placeholder4.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder4.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder4" class="piechart"></div>
						</div>
						<div class="col-sm-4">
							<h2>Past Week</h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php 
									$new = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".date("U", strtotime("-1 week"))."') AS X HAVING `First Time` >= '".date("U", strtotime("-1 week"))."') AS T"))[0];
									$returning = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".date("U", strtotime("-1 week"))."') AS X HAVING `First Time` < '".date("U", strtotime("-1 week"))."') AS T"))[0];
									$arr = [['label'=>"New Users", 'data'=>$new], ['label'=>"Returning Users", 'data'=>$returning]];
									echo json_encode($arr);
									?>;
									var placeholder7 = $("#placeholder7");
									placeholder7.unbind();
									$.plot(placeholder7, data, {
										series: {
											pie: { 
												innerRadius: 0.5,
												show: true,
												label: {
													show: true,
													radius: .65,
													background: {
														opacity: 0.7,
														color: '#000'
													}
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: false},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  }
									});
									placeholder7.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder7.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder7" class="piechart"></div>
						</div>
						<div class="col-sm-4">
							<h2><?php echo date("m/d/y", $startDate)." to ".date("m/d/y", $endDate);?></h2>
							<script type="text/javascript">
								$(function() {
									var data = <?php 
									$new = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".$startDate."') AS X HAVING `First Time` >= '".$startDate."') AS T"))[0];
									$returning = mysqli_fetch_array(mysqli_query($link, "SELECT COUNT(1) FROM (SELECT * FROM (SELECT * FROM `Users` HAVING `Last Time` >= '".$startDate."') AS X HAVING `First Time` < '".$startDate."') AS T"))[0];
									$arr = [['label'=>"New Users", 'data'=>$new], ['label'=>"Returning Users", 'data'=>$returning]];
									echo json_encode($arr);
									?>;
									var placeholder5 = $("#placeholder5");
									placeholder5.unbind();
									$.plot(placeholder5, data, {
										series: {
											pie: { 
												innerRadius: 0.5,
												show: true,
												label: {
													show: true,
													radius: .65,
													background: {
														opacity: 0.7,
														color: '#000'
													}
												}
											}
										},
										grid: {
											hoverable: true,
											clickable: true
										},
										legend: {show: false},
										tooltip: {
											show: true,
											content: "%s=%p.0%, n=%n", // show percentages, rounding to 2 decimal places
											shifts: {
											  x: 20,
											  y: 0
											},
											defaultTheme: false
										  }
									});
									placeholder5.bind("plothover", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										var percent = parseFloat(obj.series.percent).toFixed(2);
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " (" + percent + "%)</span>");
									});
									placeholder5.bind("plotclick", function(event, pos, obj) {
										if (!obj) {
											return;
										}
										$("#hover").html("<span style='font-weight:bold; color:" + obj.series.color + "'>" + obj.series.label + " ("+ obj.datapoint[1]+"%)</span>");
									});
								});
							</script>
							<div id="placeholder5" class="piechart"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
