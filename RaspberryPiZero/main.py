#!/usr/bin/env python3
#Code for RAQSP - to submit readings
#Kai Chance 2018
#Tested with python3
#This simple script connects to the RESTful service, submitting data for storage externally
#PREREQUISITES: Python3, pySerial, Requests, knowledge of server URL and device identifiers below

#Imports
from time import gmtime, strftime, localtime
import serial #pySerial required
import string
import requests #requests library required
import time

#Setup
devID = 1001 #Devices hardcoded identifier
sensePort = "/dev/ttyUSB1" #1 on boot, 2 on reinsertion - Arduino
radPort = "/dev/ttyUSB0" #Radiation sensor
defURL = "http://127.0.0.1/api/app/submitReading.php" #The use of a domain name will be advantageous - directly address the REST api

#Function which takes in a request string and port id, then applys the request upon the port - formatting the return
def readSerial(port, request):
    print("SER: Open Port\n") #logging
    seri = serial.Serial(port) #open using the passed in parameter
    seri.flush() #remove any data existing in the buffer potentially from previous requests
    time.sleep(10) #This is key to ensuring the Arduino has fully booted (Arduinos tend to reboot upon serial connection)
    print("SER: Encode and Request\n")
    seri.write(request)
    #time.sleep(2) #used in testing
    t = seri.readline() #Reads the serial buffer until it detects a new line character
    seri.close() #close the connection and access to the port
    seri.__del__() #dispose of the connection as a safety measure (python3+ only)
    return t.decode().strip('\r\n') #converts from a byte literal and removes the escape characters provided by the Arduino

#Takes in a delay time, port and request before executing - Similar implementation to readSerial()
def radCount(delayV, port):
    print("SER: Open Port\n")
    ser = serial.Serial(port)
    time.sleep(5) #allow to settle
    ser.flush() #Empty all buffers
    print("SER: Counting\n")
    time.sleep(delayV) #sleep for the predefined time to allow counts to build
    length = ser.inWaiting() #After sleep, count the length(amnt) of waiting bytes - data
    ser.close() #close, and dispose
    ser.__del__()
    #print(length) #debugging
    #Occasionally, the sensor decides upon init to precede its response with 13 characters. As such, use this to account for this anomoly
    if((length - 13) > 0):
            return str((length - 13) * (60/delayV))#Converts to a recognised unit cpm(counts per minute) through basic arithmetic
    else:
            return str((length) * (60/delayV)) #""

#Function which utilises the requests library to submit data to the REST service
def submit(baseURL, datetime, deviceid, sensorid, value):
    print("Building URL\n")
    builturl = baseURL + "?datetime=" + datetime + "&deviceid=" + str(deviceid) + "&sensorid=" + sensorid + "&value=" + str(value) #build through concatenation
    #then submit
    print("Submit Data\n")
    print(builturl + "\n")
    f = requests.get(builturl) #Submit a get request, params in the URL used
    #f.read() #not required - here for completeness

#Effectively switch code, which ensures the correct parameters are passed into the each function
def execCode(sensor):
    if(sensor == "rad"):
        #exec the radiation scripts
        submit(defURL, timeFormatted(), devID, "SFGEIGER", radCount(30,radPort))
    elif (sensor == "HUMIDITY"):
        #pass forward
        submit(defURL, timeFormatted(), devID, "DHT11H", readSerial(sensePort, sensor.encode('ascii'))) #pySerial lib requires ASCII submission of chars
    elif (sensor == "TEMP"):
        submit(defURL, timeFormatted(), devID, "DHT11T", readSerial(sensePort, sensor.encode('ascii')))
    elif (sensor == "MQ4B"):
        submit(defURL, timeFormatted(), devID, "MQ4", readSerial(sensePort, sensor.encode('ascii')))
    elif (sensor == "MQ7"):
        submit(defURL, timeFormatted(), devID, "MQ7", readSerial(sensePort, sensor.encode('ascii')))

#Gets the current system time (accounting for localisation) and returns in the format required for DATETIME MySQL insertion
def timeFormatted():
    return strftime("%Y-%m-%d %H:%M:%S", localtime())

#Loops for as long as the program is executed - runs for each sensor in series and then sleeps for one minute, sleep value can be changed
while True:
    #Run the exec code;
    print("Get Radiation Data\n")
    execCode("rad")
    print("Get Humidity Data\n")
    execCode("HUMIDITY")
    print("Get Temperature Data\n")
    execCode("TEMP")
    print("Get CH4 Data\n")
    execCode("MQ4B")
    print("Get CO Data\n")
    execCode("MQ7")

    print("WAIT 1 min\n")

    time.sleep(60) #sleep
