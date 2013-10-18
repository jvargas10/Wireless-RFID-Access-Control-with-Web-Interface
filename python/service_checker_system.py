#!/usr/bin/python

"""This starts service to a given host. If the service process ever dies then
this script will detect that and restart it.""" 

import pexpect
import time
import os
import threading
from subprocess import Popen, PIPE, STDOUT

class ProcessThread(threading.Thread):

    def __init__(self):
        threading.Thread.__init__(self)
        self.running = True

    def stop_service(self):
        self.running = False

    def start_service(self):
    	try:
            service = pexpect.spawn(process_command)
            time.sleep (1) # Cygwin is slow to update process status.

            p = Popen('ps -eo pid,lstart,cmd | grep "' + process_command + '"', shell=True, stdin=PIPE, stdout=PIPE, stderr=STDOUT, close_fds=True)
            output = p.stdout.read().split("\n")[0]
            command = "echo $'\nSerial Communication restarted, new processs info:\n%s' >> /path_to_log_file/service_checker_system_log" % output
            os.system(command)
            service.expect(pexpect.EOF, timeout=None)
	 
        except Exception, e:
            command = "echo $'\nSerial Communication TIMEOUT reached at : ' %s >> /path_to_log_file/service_checker_system_log" % time.asctime()
            os.system(command)
            pass

      
    def run(self):  
        self.start_service()
        threading.Thread.__init__(self) 	

process_command = 'python /path_to_script/python_serial_monitor.py &'


def get_process_info ():

    # This seems to work on both Linux and BSD, but should otherwise be considered highly UNportable.
    ps = pexpect.run ('ps ax -O ppid')
    pass


def main ():

    t = ProcessThread()    

    while True:
	try:
        ps = pexpect.spawn ('pgrep -f "%s"' % process_command)
        time.sleep (2)
        ppid = ps.read()
        ps.close()

        if not ppid:
            t.start()
            time.sleep(2)
	   
    except pexpect.EOF:
        pass

	
if __name__ == '__main__':
    main ()

