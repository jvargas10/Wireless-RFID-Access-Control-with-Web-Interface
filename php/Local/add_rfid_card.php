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
	$rfid_code = "";
	$status = "";
	$owner = "";
	$error_rfid_code = ""; 
	$error_owner = ""; 
			
	#connecting to mysql
	$conn = mysql_connect("localhost", "your_user", "your_password") or die("Cant connect to the database...");
	
	mysql_query("set names utf8");	
			

	if (isset($_POST['send'])){
		$rfid_code = clean($_POST['rfid_code']);
		$status = clean($_POST['status']);
		$owner = clean($_POST['owner']);
		
		$validation = 1;
		
		if ($rfid_code == ''){
			$validation = 0;
			$error_rfid_code = "Insert a valid code"; 
		}
		if ($owner == '0'){
			$validation = 0;
			$owner = intval($owner);
			$error_owner = "Select an owner"; 
		}
		
		if ($validation == 1){
			
			$status = intval($status);
			
			#selecting a database
			$rs = mysql_query("use checador", $conn) or die("Cant select the specified database...");
			
			#creating the query
			$sql = "insert into rfid_cards(code, status, owner) values (\"".md5($rfid_code)."\", $status, $owner)";
			
			#executing the query
			$rs = mysql_query($sql,$conn);
			
			#validating query execution
			if ($rs){
				$confirmation = "RFID Card Recorded";
				$rfid_code = "";

				#updating employee rfid card field
				
				#selecting database empleados
				$rs = mysql_query("use empleados", $conn) or die("Cant select the specified database...");
				
				$sql = "update employees set rfid_card = 1 where id = $owner";
				
				#executing the query
				$rs_update = mysql_query($sql,$conn);
				
				if ($rs_update) $confirmation .= "<br>Employee RFID Card Field has been updated to 1";
				else $confirmation .= "<br>Employee RFID Card Fied could not be updated.";
				
			}else $confirmation = "Sorry the RFID Card could not be recorded. Try Again.";
		}
	}
    
?>
<html>
	<head>
		<title>New RFID Card</title>
	
		<style text="text/css">
			#error{ color:red; }
		</style>

	</head>
	<body>
		<?php echo $confirmation ?>
		<form method="post">
			<table>
				<tr>
					<td>RFID Code:</td>
					<td><input type="text" name="rfid_code" value = "<?php echo $rfid_code ?>" /></td>
					<td id="error"><?php echo $error_rfid_code ?></td>
				</tr>
				<tr>
					<td>Status:</td>
					<td>
						<select name="status">
							<option value="1">Active</option>
							<option value="0">Inactive</option>
						</select>
					</td>
					<td></td>
				</tr>
				<tr>
					<td>Owner:</td>
					<td>
						<select name="owner">
							<option value="0">Select an owner</option>
							<?php
								#getting employees names and ids from database empleados

								#selecting database empleados
        							$rs = mysql_query("use empleados", $conn) or die("Cant select the specified database...");

        							#creating the query (selecting only active employees without rfid card)
        							$sql = "select id,first_name,second_name from employees where status=1 and rfid_card =0";

        							#executing the query
        							$rs_employees = mysql_query($sql,$conn);

								while($row = mysql_fetch_array($rs_employees)){
									$name = $row["first_name"]." ".$row["second_name"];
									$id = $row["id"];
									echo "<option value='$id'>$name</option>";
								}
								if($conn) mysql_close($conn);
							?>
						</select>
					</td>
					<td id="error"><?php echo $error_owner ?></td>
				</tr>
				<tr>
					<td colspan="2" align="left">
						<input type="submit" name="send" value="Create"/></tr>
					</td>
			</table>
		</form>
		
	</body>
</html>
