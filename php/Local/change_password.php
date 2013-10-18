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
	$error_password="";
	$confirmation = "";
	$new_password = "";
	$new_password_confirmation = "";
	
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

	if ($validation == 0){
		header("Location:checker_login.php");
		exit;
	}
	
	if (isset($_POST['send'])){
		
		$old_password = clean($_POST['old_password']);
		$new_password = clean($_POST['new_password']);
		$new_password_confirmation = clean($_POST['new_password_confirmation']);

		if (strlen($new_password)>=6){
			if ($new_password == $new_password_confirmation){
				if (md5($old_password) == $db_password ){

					$conn = mysql_connect("localhost" , "your_user", "your_password") or die("Cannot connect to the database server...");

					$rs = mysql_query("use empleados") or die("Cannot select the database...");
					$sql = "update employees set password=\"".md5($new_password)."\" where id = $id ";
					$rs = mysql_query($sql, $conn) or die("Cannot execute the query to the database...");
					if ($rs){ 
						$confirmation = "Your password has been changed sucessfully.";
						$new_password = "";
						$new_password_confirmation = "";						
					}
					if ($conn) mysql_close($conn);
            	}else $confirmation = "Enter your correct password";
			}else $error_password = "New passwords do not match...";
		}else $error_password = "Enter a password with at least 6 characters";
	}
	
?>
<html>
	<head>
		<title>Change Password</title>
		<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css" />
                <script src="http://code.jquery.com/jquery-1.8.2.js"></script>
                <script src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>
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
                        #date{
                                width:auto;
                                margin: 0 auto;
                        }
                        #table_form{ width:auto; margin: 0 auto;}
			#error{ color:red; }
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
		
		<form method="post">
			<table id="table_form">
				<tr>
					<td>Enter your password:</td>
					<td><input type="password" name="old_password"/></td>
					<td></td>
				</tr>
				<tr>
					<td>Enter your new password:</td>
					<td><input type="password" name="new_password" value="<?php echo $new_password ?>" /></td>
					<td id="error"><?php echo $error_password ?></td>
				</tr>
				<tr>
					<td>Reenter your new password:</td>
					<td><input type="password" name="new_password_confirmation" value="<?php echo $new_password_confirmation ?>" /></td>
					<td></td>
				</tr>
				<tr>
					<td colspan="2" align="right"><input type="submit" name="send" value="Change" /></td>
				</tr>
				<tr>
					<td colspan="3" align="center" style="color: red;"><?php echo $confirmation ?></td>
				</tr>
			</table>
		</form>
		
	</body>
</html>
