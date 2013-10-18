<?php
	if (isset($_POST['create_report'])){
	
		include 'mail_class.php'	

		$csv_data = $_POST['csv_data'];
       	$file_name = "/tmp/report_checker";
      	$file = fopen($file_name, "w");
    	fwrite($file, $csv_data);
       	fclose($file);

       	exec("python /var/www/html/php/xls.py",$output);
	}
?>
