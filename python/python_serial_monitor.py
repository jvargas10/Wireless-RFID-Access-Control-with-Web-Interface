import serial
import _mysql
import hashlib
import time


command = ""

try:
	ser = serial.Serial("/dev/ttyUSB0",19200, timeout=0)
except:
	print "Failed to connect on /dev/ttyUSB0"
	exit()

flag_while = 1

while flag_while:
	time.sleep(0.005)
	try:
		serin = ser.read()
	except: 
		print "Failed to read data"
		flag_while = 0
	else:
		if serin != '\0': 
			if serin != '\n': command += serin
		else:
			print "command: " + command
			commandLenght = len(command)
			if commandLenght>3:
				
				if command[0] == "1": rec_type = "login"
				elif command[0] == "2": rec_type = "logout"				
				unix_hour = time.time()
				flag=0

				try:

	        		db = _mysql.connect("localhost","your_user","your_password")
        			db.query("use checador") 
	        		db.query("select owner from rfid_cards where code=\""+hashlib.md5(command[commandLenght-12:]).hexdigest()+"\"")
	        		r = db.use_result()
					r = r.fetch_row(1,1)
	                              
					if r:
						flag = 1
						message = ""
						
						if command[0] != "0":
							flag = 0
							id_employee = r[0]["owner"]
							message = "Recorded " + rec_type 			
	        				db.query("insert into logins(id_employee, unix_hour, rec_type, method, comment, created_at) values(\""+ id_employee +"\","+str(unix_hour)+", \""+ rec_type + "\", \"rfid\", \"\", now())")
							flag = 1

				except _mysql.Error, e: print e #print "Login could not be recorded"

				finally:
	        		if db: db.close()
	        			if flag: 
							try:
								ser.write("on\0")
								print message
							except:
								print "Failed to send acknowledge data"
			command = ""
			ser.flushInput()
			ser.flushOutput()
try:
	ser.close()
except: 
	print "Failed to close serial port..."
