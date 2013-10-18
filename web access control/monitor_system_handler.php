<?php

        if ($_GET['status'] == 'on') exec ("python /var/www/html/php/controlAcceso/python_serial_scripts/python_serial_on.py");
        else if ($_GET['status'] =='off') exec("python python_serial_scripts/python_serial_off.py");

?>
<html>
	<head>
		<script type="text/javascript">
			window.close();
		</script>
	</head>
	<body>
	</body>
</html>
