<?php

   //Function to sanitize values received from the form. Prevents SQL injection
   function clean($str) {
   	$str = @trim($str);
	if(get_magic_quotes_gpc()) {
		$str = stripslashes($str);
	}
	return mysql_real_escape_string($str);	
   }
	
   $confirmation = "";
   $first_name = "";
   $second_name = "";
   $join_date = "";
   $quit_date = "";
   $working_hours = "";
   $salary = "";
   $status = "";
   $username = "";
   $password = "";
   $password_confirmation = "";
   $error_first_name = ""; 
   $error_second_name = ""; 
   $error_join_date = ""; 
   $error_working_hours = ""; 
   $error_username = ""; 
   $error_password = "";
   $error_password_confirmation = ""; 
   
   
   if (isset($_POST["send"])){

		$conn = mysql_connect("localhost", "your_user", "your_password") or die("Cant connect to the database...");

   		$first_name = clean($_POST['first_name']);
	   	$second_name = clean($_POST['second_name']);
		$join_date = clean($_POST['join_date']);
		$quit_date = clean($_POST['quit_date']);
		$working_hours = clean($_POST['working_hours']);
		$salary = clean($_POST['salary']);
		$status = clean($_POST['status']);
		$username = clean($_POST['username']);
		$password = clean($_POST['password']);
		$password_confirmation = clean($_POST['password_confirmation']);
		
		$validation = 1;
		
		if ($first_name == ""){
			$validation = 0;
			$error_first_name = "Insert a first name"; 
		}
		if ($second_name == ""){
			$validation = 0;
			$error_second_name = "Insert a second name"; 
		}
		if ($join_date == ""){
			$validation = 0;
			$error_join_date = "Insert a join date"; 
		}
		if ($working_hours == ""){
			$validation = 0;
			$error_working_hours = "Insert a valid working hours"; 
		}
		if ($username == ""){
			$validation = 0;
			$error_username = "Insert a username"; 
		}
		if ($password and $password_confirmation){
			if($password != $password_confirmation){
				$error_password = "Passwords doesnt match.";
				$validation = 0;
			}else $password=md5($password); 
		}else{
			$validation = 0;
			if ($password == ""){
				$error_password = "Insert a password"; 
			}
			if ($password_confirmation == ""){
				$error_password_confirmation = "Insert a password confirmation"; 
			}
		}
		
		if ($validation ==1){
			if ($salary == "") $salary = '0';
			$salary = floatval($salary);
			$status = intval($status);
			
			mysql_query("set names utf8");
			
			#selecting a database
			$rs = mysql_query("use empleados", $conn) or die("Cant select the specified database...");
			
			#creating the query
			$sql = "insert into employees(first_name, second_name, join_date, quit_date, working_hours, salary, status, username, password, rfid_card) ";
			$sql .= "values (\"$first_name\", \"$second_name\", \"$join_date\", \"$quit_date\", \"$working_hours\", $salary, $status,\"$username\",\"$password\", 0)";
			
			#executing the query
			$rs = mysql_query($sql,$conn) or die(mysql_error($conn));
			
			if ($rs){ 
				$confirmation = "Employee Recorded Successfully.";
				$first_name = "";
   				$second_name = "";
   				$join_date = "";
   				$quit_date = "";
   				$working_hours = "";
   				$salary = "";
   				$status = "";
   				$username = "";
   				$password = "";
   				$password_confirmation = "";
			}
			else $confirmation = "Employee could not be recorded. Try Again.";			
		}
		if ($conn) mysql_close($conn);
   }
?>

<html>
	<head>
		<title>New Employee</title>
		<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css" />
    	<script src="http://code.jquery.com/jquery-1.8.2.js"></script>
    	<script src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>

	    <script>      
    		$(function() {
    			
        		$( "#datepicker_join" ).datepicker({ dateFormat: "yy-mm-dd" });
    		});
    		$(function() {
        		$( "#datepicker_quit" ).datepicker({ dateFormat: "yy-mm-dd" });
    		});
    	</script>

	<style type="text/css">
		#error{ color:red;}	
	</style>

	</head>
	<body>
		<?php echo $confirmation ?>
		<form method="post">
			<table>
				<tr>
					<td>First Name:</td>
					<td><input type="text" name="first_name" value = "<?php echo $first_name?>"/></td>
					<td id="error"><?php echo $error_first_name ?></td>
				</tr>
				<tr>
					<td>Second Name:</td>
					<td><input type="text" name="second_name" value="<?php echo $second_name?>"/></td>
					<td id="error"><?php echo $error_second_name ?></td>
				</tr>
				<tr>
					<td>Join Date:</td>
					<td><input type="text" name="join_date" id="datepicker_join" value="<?php echo $join_date?>"/></td>
					<td id="error"><?php echo $error_join_date ?></td>
				</tr>
				<tr>
					<td>Quit Date:</td>
					<td><input type="text" name="quit_date" id="datepicker_quit" value="<?php echo $quit_date?>"/></td>
					<td></td>
				</tr>
				<tr>
					<td>Working Hours:</td>
					<td><input type="text" name="working_hours" value="<?php echo $working_hours?>"/></td>
					<td id="error"><?php echo $error_working_hours ?></td>
				</tr>
				<tr>
					<td>Salary:</td>
					<td><input type="text" name="salary" value="<?php echo $salary?>" /></td>
					<td></td>
				</tr>
				<tr>
					<td>Status:</td>
					<td>	
						<select name="status">
							<option value="1">Activo</option>
							<option value="0">Inactivo</option>
						</select>
					</td>
					<td></td>
				</tr>
				<tr>
					<td>User:</td>
					<td><input type="text" name="username" value="<?php echo $username?>"/></td>
					<td id="error"><?php echo $error_username ?></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input type="password" " name="password" value="<?php echo $password?>"/></td>
					<td id="error"><?php echo $error_password ?></td>
				</tr>
				<tr>
					<td>Confirm Password:</td>
					<td><input type="password" " name="password_confirmation" value="<?php echo $password_confirmation?>"/></td>
					<td id="error"><?php echo $error_password_confirmation ?></td>
				</tr>
				<tr>
					<td colspan="3">
						<input type="submit" name="send" value="Create"/>
					</td>
				</tr>
			</table>
		</form>
		
	</body>
</html>
