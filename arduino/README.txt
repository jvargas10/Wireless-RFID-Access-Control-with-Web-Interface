The following script controls microcontroller Arduino UNO to do the following tasks:

1.- Control of Multicolor LED to cycle over colors as follows:


			 LIGHT OFF(0)
			     /\
			    /  \
			   /    \
		     (3)BLUE    RED(1)
			   \	/
			    \  /
			     \/
		           GREEN(2)

	Where colors means:
		RED 	-> Open Door
		GREEN	-> Login and open door
		BLUE	-> Logout

	Note: the led color is changed by pressing the push button. Color indicate the action to perform.


2.- Open Lock (electric lock) via RFID and server grant signal.
3.- Open Lock via switch.
4.- Serial communication to RFID Sensor (Innovation ID-20). Software serial.
5.- Serial communication to Server (Xbee PRO). Hardware serial.
6.- Receives data from server via web interaction (html webpage to open door)
