<?php

	$record_type = "";
	$comment = "";
	$confirmation = "";
	$validation = 0;

	//getting cookie
	$cookie = $_COOKIE["id_checker"];
	if ($cookie){
		$splited_cookie = split("#", $cookie);
		$id = $splited_cookie[0];
		$cookie_value = $splited_cookie[1];
				
		//validating cookie value
		$conn = mysql_connect("localhost" , "your_user", "your_password") or die("Cannot connect to the database server...");
			
		$rs = mysql_query("use empleados", $conn) or die("Cannot selected the database...");
			
		$sql = "select username, password from employees where id = $id ";
		
		$rs = mysql_query($sql, $conn) or die("Cannot execute the query to the database...");
	
		if($row = mysql_fetch_array($rs)){
			if ($cookie_value == md5($row['username'].$row['password'])){
				$validation = 1;
			}
		}
		if ($conn) mysql_close($conn);	
	}
	
	if ($validation == 0){
		header("Location:checker_login.php");
		exit;
	}
	
    if (isset($_POST['send'])){
    	$record_type = $_POST['record_type'];
		
		if ($record_type != '0'){
			$comment = $_POST['comment'];
			
			$unix_hour = time();
			
			$conn = mysql_connect("localhost" , "your_user", "your_password") or die("Cannot connect to the database server...");
			
			$rs = mysql_query("use checador", $conn) or die("Cannot selected the database...");
			
			$sql = "insert into logins(id_employee, unix_hour, rec_type, method, created_at, comment) ";
			$sql .= "values($id, $unix_hour, \"$record_type\", \"web\", now(), \"$comment\" )";
			
			$rs = mysql_query($sql, $conn) or die("Cannot execute the query to the database...");
			
			if($rs){
				$datetime = date("Y-m-d H:i:s");
				$confirmation = ucfirst($record_type. " recorded at ". $datetime );
			}
			if ($conn) mysql_close($conn);
		}
    }
?>

<html>
	<head>
		<title>Record</title>
		<script type="text/javascript" src="TacoComponents/MooTools/mootools.js"></script>
                <script type="text/javascript" src="TacoComponents/MenuMatic/MenuMatic.js"></script>
                <link rel="stylesheet" type="text/css" href="TacoComponents/MenuMatic/MenuMatic_myNavigationMenu.css" />
	
		<style type="text/css">
                        body{
                                background-color:#EEE9E9;
                        }
                        #myNavigationMenu{
                                width:450px;
                                margin: 0 auto;
                        }
			#record{
				width:auto;
				margin: 0 auto;
			}
                </style>

	</head>
	<body>

		<!-- BEGIN COMPONENT Navigation Menu - Taco HTML Edit -->
                <!--
                For Navigation Menu to appear correctly in Internet Explorer, make sure that you have a valid
                doctype declaration at the beginning of your document such as:
                <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 TRANSITIONAL//EN" "http://www.w3.org/TR/html4/loose.dtd">
                -->
                <ul id="myNavigationMenu">
                        <li><a href="record_log.php">Record Log</a></li>
                        <li><a href="view_logs.php">View Logs</a></li>
                        <li><a href="change_password.php">Change Password</a></li>
                        <li><a href="checker_logout.php">LogOut</a></li>
                </ul>
                <!-- Create a MenuMatic Instance -->
                <script type="text/javascript" >
                        window.addEvent('load', function() {
                                var myMenu = new MenuMatic({
                                        id: 'myNavigationMenu',
                                        subMenusContainerId: 'myNavigationMenu_menuContainer',
                                        orientation: 'horizontal',
                                        effect: 'slide & fade',
                                        duration: 800,
                                        hideDelay: 1000,
                                        opacity: 100});
                        });
                </script><br><br><br><br>

		<table id="record">
			<form method="post">
				<tr>
					<td>
						<select name="record_type">
							<option value="0">Select a Record Type</option>
							<option value="login">Login</option>
							<option value="logout">Logout</option>
						</select>
					</td><tr>
					<td><textarea cols="40" rows="10" name="comment"></textarea><tr>
					<td><input type="submit" name="send" value="Record"/></td>
				</tr>
				<tr>
					<td colspan="2">
						<?php echo $confirmation ?>
					</td>
				</tr>
			</form>
		</table>
		
	</body>
</html>
