<?php
	
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
			$db_password = $row['password'];
			if ($cookie_value == md5($username.$db_password)){
				$validation = 1;
			}
		}	
		
		if ($conn) mysql_close($conn);
	}
	$validation = 1;
	
	if ($validation == 0){
		header("Location:checker_login.php");
		exit;
	}else{
		if(setcookie("id_checker","",time()-3600)){
			$message = "You have been logged out. Redirecting....";
		}else{
			$message = "Cookie could not be deleted...";
		}	
	}
	

?>
<html>
	<head>
		<title>Logout</title>
		<meta http-equiv="refresh" content="3; URL=main.php">
		<style type="text/css">
			body{
					background-color:#EEE9E9;
				}
			#message{ text-align:center; }
		</style>
	</head>
	<body>
		<h1 id="message"><?php echo $message ?></h1>
	</body>
</html>
