Python Serial Monitor Script:

Server Interface to Arduino Uno microcontroller. It opens communication on serial port ttyUSB0.
If data is received (RFID hexadecimal code) with proper length then data is encrypted and 
queried to the database to check if a user is registered with the RFID code received.

If a result set is return (the user exists) the login or logout is recorded in the database 
based on the first characters of the data received where:
	
	1 -> login -> signal sent to Arduino Uno to open door
	2 -> logout

If first character is equal to 0, then server only sends signal to open door.

===============================================================================================

Service Checker System Script:

This script watches over execution of python_serial_monitor.py on background. 
If the script execution is finished then it will attempt to execute it again.

This script runs automatically when server is turned on. The execution is
signaled on Linux Server /etc/rc.local file as follows:

	python /path_to_file/service_checker_system.py &


