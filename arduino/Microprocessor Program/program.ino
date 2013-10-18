#include <SoftwareSerial.h>

//setup sofware serial port
#define rfidReaderRXPin 3
#define rfidReaderTXPin 8
SoftwareSerial rfidReader = SoftwareSerial(rfidReaderRXPin,rfidReaderTXPin);

//setup button
#define buttonInt  0 //Pin 2

//setup LEDs
#define redLED     7
#define greenLED   6
#define blueLED    5

//setup inputs
#define door_switch  4

//setup variables
volatile int selectedLED = redLED;
volatile int flag = 1;
volatile char controlSignal = '0';

char prefix[] = "0|";
long startTime;
int prescaler = 100;
int waitSignal = 0;

String commandString = "";         // a string to hold incoming data
String command="";
boolean commandComplete = false;  // whether the string is complete

String cardCode = "";
boolean cardCodeComplete = false;

void setup() {
  
  //setting pin 13 to digital OUTPUT and pin 4 to INPUT
  pinMode(13,OUTPUT);
  pinMode(door_switch,INPUT_PULLUP);
  
  //setting software serial pin modes
  pinMode(rfidReaderRXPin, INPUT);
  pinMode(rfidReaderTXPin, OUTPUT);
  
  //setting LEDs pin modes
  pinMode(redLED, OUTPUT);
  pinMode(greenLED,OUTPUT);
  pinMode(blueLED,OUTPUT);
  
  //initialize rfidReader (software serial)
  rfidReader.begin(9600);
  
  // initialize serial (hardware serial):
  Serial.begin(19200);
  
  // reserve 16 bytes for the inputString:
  commandString.reserve(16);
  
  //attaching interruption
  attachInterrupt(buttonInt, swap, FALLING);
}

//The interrupt function
void swap(){
  if (waitSignal == 0){
    flag = 2;
    digitalWrite(selectedLED, HIGH);
    if (selectedLED == redLED){
      selectedLED = greenLED;
      controlSignal = '1';
    }else if (selectedLED == greenLED){
      selectedLED = blueLED;
      controlSignal = '2';
    }else{ 
      selectedLED = redLED;
      controlSignal = '0';
    }
    digitalWrite(selectedLED, LOW);
  }
}

void loop() {
  
  if (flag == 0){
    if (millis() - startTime > 3000) flag =1;
  }else if (flag == 1){
    controlSignal = '0';
    digitalWrite(greenLED,HIGH); 
    digitalWrite(blueLED,HIGH);
    digitalWrite(redLED,HIGH);
    selectedLED = redLED;
  }else if (flag == 2){
    startTime = millis();
    flag = 0;
  }else if (flag == 3){
    turnOnLock(2000);
  }
  
  if (digitalRead(door_switch) == LOW){
    if (selectedLED != redLED){
      digitalWrite(selectedLED, HIGH);
      selectedLED = redLED;
      digitalWrite(selectedLED,LOW);
    }
    startTime = millis();
    flag = 3;
    waitSignal = 1;
  }
  
  if (rfidReader.available()){
    char inCharCardCode = (char) rfidReader.read();
    
    if (inCharCardCode != '\r' ) cardCode += inCharCardCode;
    else cardCodeComplete = true;
  }

  if (cardCodeComplete){
    cardCodeComplete = false;
    prefix[0] = controlSignal;
    sendRFIDCode(prefix + cardCode);
    cardCode = "";
    waitSignal = 1;  
  }
 
  if (commandComplete){
    commandComplete = false;
    // turn ON lock when "on" state is acknowledge
    if (command == "on"){
      startTime = millis();
      flag = 3;
    }else if (command =="off") turnOffLock();
  }
}

/*
  This routine turns on the lock for a given milliSeconds 
*/
void turnOnLock(int milliSeconds){
    long currentTime = millis();
    long elapsedTime = currentTime - startTime;
    
    if (elapsedTime < milliSeconds){ 
      if (controlSignal != '2') digitalWrite(13, HIGH);
    }else{ 
      digitalWrite(13, LOW);
      flag = 1;
      prescaler = 100;
      waitSignal = 0;
      return; 
    }
    
    if (elapsedTime < prescaler) digitalWrite(selectedLED, LOW);
    else if(elapsedTime < prescaler + 100) digitalWrite(selectedLED, HIGH);
    else prescaler = elapsedTime + 100;
}

/*
  This routine turns off the lock
*/
void turnOffLock(){
  digitalWrite(13,LOW);
}

boolean sendRFIDCode(String code){
  Serial.print(code+'\0');
}

/*
  SerialEvent occurs whenever a new data comes in the
 hardware serial RX.  This routine is run between each
 time loop() runs, so using delay inside loop can delay
 response.  Multiple bytes of data may be available.
 */
void serialEvent() {
  while (Serial.available()) {
    // get the new byte:
    char inCharCommand = (char)Serial.read();
    
    if (inCharCommand == '\0') {
      commandComplete = true;     
      command = commandString;
      Serial.print(command+'\0');
      commandString = "";
    }else commandString += inCharCommand;
  }
}






