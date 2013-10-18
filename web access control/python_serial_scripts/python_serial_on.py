import serial

try:
	ser = serial.Serial("/dev/ttyUSB0",19200, timeout=0)
	ser.write("on\0")
	ser.flushOutput()

except: pass

finally: 
	ser.close()

