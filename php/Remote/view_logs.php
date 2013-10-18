<?php

	//Function to sanitize values received from the form. Prevents SQL injection
   	function clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
        }
        return mysql_real_escape_string($str);
   	}


	$username = "";
	$rec_type = "";
	$created_at = "";
	$method = "";
	$comment = "";
	$table = "";
	$date = "";
	$validation =0;	

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
	
	$message = "";

	if (isset($_POST['create_report'])){
		include 'mail_class.php';

		$flag = 0;
		$csv_data = $_POST['csv_data'];
		$file_name = "/tmp/report_checker";
		$file = fopen($file_name, "w");
		if ($file != FALSE){
			if(fwrite($file, $csv_data)!=FALSE){
				$flag = 1;
				fclose($file);
			}
		}

		if ($flag == 1){
			exec("python /path_to_file/php/xls.py",$output);

                	$file_name = "Formato de Entradas y Salidas.xls";
                	$file = '/tmp/'.$file_name;

               		 $head = array(
					'to' => array('anEmail1'=>'Name1', 'anEmail1'=>'Name2'),
                                        'from' => array('anEmail' =>'Avisos'),
                                        'cc'   => array('anEmail3'=>'Name3'),
                                        'bcc'  => array('anEmail4'=>'Name 4'),
                        	);

                	$subject = date("Ymd")." Reporte de Entradas y Salidas";
                	$body ='';
                	$body.="<div style='font-family:Arial;font-size:10pt;'>";
                	$body.=    "<br>"."Archivos Adjuntos:";
                	$body.=    "<br>"."* ".$file_name;
                	$body.=    "<br>"."";
                	$body.=    "<br>"."-Admin";
                	$body.="</div>";
                	$files = array($file);

                	//mail::send($head,$subj,$body);//$files is optional param
                	if(mail::send($head,$subject,$body, $files)) $message = "Report Sent Sucessfully!";
			else $message = "Report could not be sent...";

		}

	}

	if (isset($_POST['update'])){

		//connecting to the database
		$conn = mysql_connect("localhost" , "your_user", "your_password") or die("Cannot connect to the database server...");

		if($conn){

			$rs = mysql_query("use checador", $conn) or die("Cannot selected the database...");

			$checkboxes_checked = $_POST['chk'];
			for ($i=0; $i<sizeof($checkboxes_checked); $i++){
				$splited_data = split("#",$checkboxes_checked[$i]);
				$id = $splited_data[0];
				$text_field = $splited_data[1];
				$new_comment = clean($_POST["txt$text_field"]);
				$sql = "update logins set comment=\"$new_comment\" where id=$id";
				$rs = mysql_query($sql, $conn) or die("Cannot update record with id = $id...<br>");
			}
			mysql_close($conn);
		}
		
	}

	
	if (isset($_POST['send'])){
		$date = $_POST['date'];
		
		if ($date != ""){
			
			//getting logs
			$conn = mysql_connect("localhost" , "your_user", "your_password") or die("Cannot connect to the database server...");
	
			$rs = mysql_query("use checador", $conn) or die("Cannot selected the database...");
	
			$sql = "select b.first_name, b.second_name, a.id, a.rec_type, a.method, a.created_at, a.comment from checador.logins a, empleados.employees b where a.id_employee = b.id and created_at like '$date%'";
	
			$rs = mysql_query($sql, $conn) or die("Cannot execute the query to the database...");
							
			if ($rs){
				$table = "<table cellpadding=\"5\" cellspacing=\"1\" border=\"1\" id=\"logs\">
						<thead>
							<th>Name</th>
							<th>Time</th>
							<th>Recorded Type</th>
							<th>Method</th>
							<th>Comment</th>
							<th>Update</th>
						</thead>
						<tfoot></tfoot>
						<tbody>";
				$i = 1;
				$csv_data = "";
				$submit_state = "disabled";
				while($row = mysql_fetch_array($rs)){
					$id = $row['id'];
					$first_name = $row['first_name'];
					$second_name = $row['second_name'];
					$rec_type = $row['rec_type'];
					$created_at = $row['created_at'];
					$method = $row['method'];
					$comment = $row['comment'];

					$csv_data .= $first_name." ".$second_name.",\t ".$created_at.",\t ".$rec_type.",\t ".$method.",\t ".$comment."\r\n";
					$table .= "<tr>
									<td align=\"center\">$first_name $second_name</td>
									<td align=\"center\">$created_at</td>
									<td align=\"center\">$rec_type</td>
									<td align=\"center\">$method</td>
									<td align=\"center\"><input type=\"text\" value=\"$comment\" name=\"txt$i\" disabled=\"true\" size=\"70\" maxlength=\"100\"/></td>
									<td align=\"center\"><input type=\"checkbox\" value=\"$id#$i\" name=\"chk[]\" onclick=\"javascript:toggleTextFieldState(this,'chk[]', $i,'update', 'update_form')\"/></td>
								</tr>";
					$i++;
				}	
				
				if ($csv_data != "") $submit_state = "";
				$table.="<tr><td colspan=\"4\" align=\"center\"><form method=\"post\" name=\"report\"><input type=\"submit\" name=\"create_report\" value=\"Send Report\" $submit_state><input type=\"hidden\" value=\"$csv_data\" name=\"csv_data\"/></form></td>
					<td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"update\" value=\"Update\" disabled=\"true\"/></td></tr></tbody></table>";
			}
	
			if($conn) mysql_close($conn);
		}
	}			
?>
<html>
	<head>
		<title>Logs</title>
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
                                width:350px;
                                margin: 0 auto;
                        }
                        #date{
                                width:auto;
                                margin: 0 auto;
                        }
			#logs{ width:auto; margin: 0 auto;}
                </style>

	    	<script>      
    			$(function() {
    			
        			$( "#datepicker" ).datepicker({ dateFormat: "yy-mm-dd" });
    			});
    		</script>

    		<script type="text/javascript">
    			function toggleTextFieldState(checkbox, checkbox_name, text_number, submit_name, form_name){
				
					//Toggling text field state (enable, disable)
					var checkbox_value = checkbox.value;
	    			if (checkbox.checked == true){
	    				document.forms[form_name].elements["txt"+text_number].disabled = false;
	        		}else{
	        			document.forms[form_name].elements["txt"+text_number].disabled = true;
	        		}
	        		
	        		setSubmitState(checkbox_name, submit_name, form_name);
	        		
	        	}
        	
	        	function setSubmitState(checkboxes_name, submit_name, form_name){
	        		var checkboxes = document.getElementsByName(checkboxes_name);
	        		var checkboxes_checked = 0;
	        		
	        		for(var i=0; i<checkboxes.length; i++){
	        			if (checkboxes[i].checked) checkboxes_checked += 1;
	        		}
	        		
	        		if (checkboxes_checked > 0) document.forms[form_name].elements[submit_name].disabled = false;
	        		else document.forms[form_name].elements[submit_name].disabled = true;
        		}
    		</script>
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
			<table id="date">
				<tr>
					<td><input type="text" name="date" id="datepicker" /></td>
					<td><input type="submit" name="send" value="View" /></td>
				</tr>
				<tr><td colspan="2" align="center" style="color:green;"><?php echo $message ?></td></tr>
			</table>
		</form><br>

		<form method="post" name="update_form">
			<?php echo $table;  ?> <!--echo "<br><br>$csv_data"; ?>-->
		</form>
		
		
	</body>
</html>
