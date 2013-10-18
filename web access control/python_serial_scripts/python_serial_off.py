import serial

ser = serial.Serial("/dev/ttyUSB0",19200)
ser.write("off\0")
ser.close()