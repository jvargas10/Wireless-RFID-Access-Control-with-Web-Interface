<?php
	$cookie = $_COOKIE['id_checker'];
	$validation = 0;
	$logout_option = "";
	$menu_size="369px;";

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
	if ($validation){
		$menu_size = "450px;";
		$logout_option = "<li><a href=\"checker_logout.php\">LogOut</a></li>";
	}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

	<head>
		<title>Main</title>
		<script type="text/javascript" src="TacoComponents/MooTools/mootools.js"></script>
		<script type="text/javascript" src="TacoComponents/MenuMatic/MenuMatic.js"></script>
		<link rel="stylesheet" type="text/css" href="TacoComponents/MenuMatic/MenuMatic_myNavigationMenu.css" />
	
		<style type="text/css">
			body{
				background-color:#EEE9E9;
			}
			#myNavigationMenu{
				width:<?php echo $menu_size ?>;
				margin:0 auto;
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
	</body>
</html>
