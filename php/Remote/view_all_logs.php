<?php

	$username = "";
	$rec_type = "";
	$created_at = "";
	$method = "";
	$comment = "";
	$table = "";
	$validation = 0;

    //getting cookie
	$cookie = $_COOKIE['id_checker'];
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
			$username = $row['username'];
			if ($cookie_value == md5($username.$row['password'])){
				$validation = 1;
			}
		}	
		
		if ($conn) mysql_close($conn);
	}
	
	if ($validation == 0){
		header("Location:checker_login.php");
		exit;
	}
	
	//getting logs
	$conn = mysql_connect("localhost" , "your_user", "your_password") or die("Cannot connect to the database server...");
	
	$rs = mysql_query("use checador", $conn) or die("Cannot selected the database...");
	
	$sql = "select rec_type, method, created_at, comment from logins where id_employee = $id ";
	
	$rs = mysql_query($sql, $conn) or die("Cannot execute the query to the database...");
	
	if ($rs){
		while($row = mysql_fetch_array($rs)){
			$rec_type = $row['rec_type'];
			$created_at = $row['created_at'];
			$method = $row["method"];
			$comment = $row["comment"];
			$table .= "<tr><td align=\"center\">$created_at</td><td align=\"center\">$rec_type</td><td align=\"center\">$method</td><td align=\"center\">$comment</td></tr>";
		}	
	}
	
	if($conn) mysql_close($conn);
				
?>
<html>
	<head>
		<title>Logs</title>
	</head>
	<body>
		<table cellpadding="5" cellspacing="5" border="1">
			<thead>
				<th>Time</th>
				<th>Recorded Type</th>
				<th>Method</th>
				<th>Comment</th>
			</thead>
			<tfoot></tfoot>
			<tbody>
				<?php echo $table ?>
			</tbody>
		</table>
	</body>
</html>
