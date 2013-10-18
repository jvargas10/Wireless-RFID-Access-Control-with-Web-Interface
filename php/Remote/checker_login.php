<?php
    
   //Function to sanitize values received from the form. Prevents SQL injection
   function clean($str) {
        $str = @trim($str);
        if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
        }
        return mysql_real_escape_string($str);
   }

   	$validation = 0;

    //getting cookie
	$cookie = $_COOKIE['id_checker'];
	if ($cookie){
		$splited_cookie = split("#", $cookie);
		$id = $splited_cookie[0];
		$cookie_value = $splited_cookie[1];
		
		//validating cookie value
		$conn = mysql_connect("localhost" , "your_user", "your_password") or die("Cannot connect to the database server...");
			
		$rs = mysql_query("use checador", $conn) or die("Cannot selected the database...");
			
		$sql = "select username, password from admins where id = $id ";
		
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

	if ($validation == 0){
		header("Location:checker_login.php");
		exit;
	}

        
    $username = "";
	$password = "";
	$error_login = "";


	if (isset($_POST['send'])){

		$username = clean($_POST['username']);
		$password = clean($_POST['password']);
		
		if ($username != "" and $password != "" ){

			$password = md5($password);
			$validation = 1;

			$conn = mysql_connect("localhost" , "your_user", "your_password") or die("Cannot connect to the database server...");
			$rs = mysql_query("use checador", $conn) or die("Cannot selected the database...");
			
			$sql = "select id from admins where username=\"$username\" and password=\"$password\"";
			
			$rs = mysql_query($sql, $conn) or die("Cannot execute the query to the database...");

			if ($id = mysql_fetch_array($rs)){
				$id = $id['id'];
				//closing mysql connection
				if($conn) mysql_close($conn);
				
				//create cookie
				$cookie_value = $id."#".md5($username.$password);
				setcookie("id_checker", $cookie_value, time()+600);
				
				//Redirecting to record.php
				header("Location:view_logs.php"); exit;
			}else $error_login = "Sorry your credentials are not valid...";

			if ($conn) mysql_close($conn);
		}
	}

?>

<html>
	<head>
		<title>Login</title>
		<script type="text/javascript" src="TacoComponents/MooTools/mootools.js"></script>
                <script type="text/javascript" src="TacoComponents/MenuMatic/MenuMatic.js"></script>
                <link rel="stylesheet" type="text/css" href="TacoComponents/MenuMatic/MenuMatic_myNavigationMenu.css" />

                <style type="text/css">
                        body{
                                background-color:#EEE9E9;
                        }
                        #myNavigationMenu{
                                width:275px;
                                margin: 0 auto;
                        }
			#login{
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
                        <li><a href="view_logs.php">View Logs</a></li>
                        <li><a href="change_password.php">Change Password</a></li>
                        <?php echo $logout_option; ?>
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
                </script>

                <!-- END COMPONENT Navigation Menu - Taco HTML Edit -->
		<br><br><br><br>
		<table id="login">
			<form method="post">
				<tr>
					<td>Username:</td>
					<td><input type="text" name="username"/></td>
					<td style="color:red;"><?php echo $error_login ?></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input type="password" name="password"  /></td>
					<td></td>
				</tr>
				<td colspan="2" align="right"><input type="submit" name="send" value="Login"/></td>
			</form>
		</table>
		
	</body>
</html>
