PHP scripts are divided in two sections: 
		
	* Local
	* Remote

Local directory contains php scripts intended to be accessible only when using application
locally (on the same LAN). Scripts are described below:

	* add_employee.php
		- Add employee in the system

	* add_rfid_card.php
		- Add rfid to existing employee

	* change_password.php
		- Change employee's password to login
	
	* checker_login.php
		- Log in to the system

	* checker_logout.php
		- Log out to the system

	* main.php
		- Menu application

	* record_log.php
		- Record login or logout

	* view_all_logs.php
		- View all history logs by user logged in
	
	* view_logs.php
		- View logs based on date by user logged in


Remote directory contains scripts intended to be accessible only remotely. No recording of
login or logout is implemented on this version. XLS Reporting and email sending were implemented. The following files were added or modified:

	* mail_class.php
		- implementation to send email

	* view_all_logs.php
		- View all history logs for all users
	
	* view_logs.php
		- View logs from all users by date and create and send report via mail

	

	