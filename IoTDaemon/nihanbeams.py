from pushetta import Pushetta
import RPi.GPIO as GPIO
import time
import pymysql.cursors
import pymysql
from time import gmtime, strftime
from datetime import datetime
import subprocess
import telegram_send
import json

#Notification method to send Telegram notifications when alarm is triggered, usess telegram.conf
def sendNotification(token, channel, message):
                telegram_send.send(messages=[message], conf="/home/pi/NeeManSec/telegram.conf")
                #p = Pushetta(token)
                #p.pushMessage(channel, message)


#telegram_send.configure("/home/pi/NeeManSec/telegram.conf", channel=True, group=False, fm_integration=False)

GPIO.setmode(GPIO.BCM)

#Setup GPIO pins
#Alarm siren
SIREN_PIN = 25
#Alarm armed
ARMED_PIN = 24
#Alarm beams
PIR1_PIN = 26
PIR2_PIN = 13
PIR3_PIN = 6
PIR4_PIN = 5

#Setup alarm zones
GPIO.setup(SIREN_PIN, GPIO.IN, GPIO.PUD_UP)
GPIO.setup(ARMED_PIN, GPIO.IN)
GPIO.setup(PIR1_PIN, GPIO.IN, GPIO.PUD_UP)
GPIO.setup(PIR2_PIN, GPIO.IN, GPIO.PUD_UP)
GPIO.setup(PIR3_PIN, GPIO.IN, GPIO.PUD_UP)
GPIO.setup(PIR4_PIN, GPIO.IN, GPIO.PUD_UP)
#Setup GPIO pins for Electronics and Lights
GPIO.setup(14, GPIO.OUT)
GPIO.output(14, 1)
GPIO.setup(15, GPIO.OUT)
GPIO.output(15, 1)
GPIO.setup(18, GPIO.OUT)
GPIO.output(18, 1)
GPIO.setup(22, GPIO.OUT)
GPIO.output(22, 1)

zones = {
    PIR1_PIN : 1,
    PIR2_PIN : 2,
    PIR3_PIN : 3,
    PIR4_PIN : 4
    }

lasttrigtime = {
    PIR1_PIN : "",
    PIR2_PIN : "",
    PIR3_PIN : "",
    PIR4_PIN : ""
    }

#Keep track of last notification time
lastnotification_time = time.time()
elapsed_time = time.time()

#Keep track of first run and startup
firstrun = True
startup = True
lastupdate = datetime.now().strftime("%Y-%m-%d %H:%M:00")

lastArmedState = 1

#Method called when alarm system is armed
def ARMED(PIN):
    global connection
    global lastArmedState
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    pinstate = str(GPIO.input(PIN))
    if lastArmedState != pinstate :
        time.sleep(0.1)
        pinstate = str(GPIO.input(PIN))
        
    if lastArmedState != pinstate :
        lastArmedState = pinstate
    
    msg = "Armed " + now + ". " + pinstate + ". "

#Method called when siren is active
def SIREN(PIN):
    pinstate = str(GPIO.input(PIN))
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    msg = "Siren " + now + ". " + pinstate + ". "
    #print (msg)


#Method called when alarm beam is tripped
def MOTION(PIR_PIN):
               pinstate = str(GPIO.input(PIR_PIN)) # Get pin state
               sendMessageSeconds = 600 # 10minutes
               global lastnotification_time
               global elapsed_time
               global firstrun
               global lastupdate
               global connection
               global startup
               global dbhost
               global dbuser
               global dbpwd
               global database
               global telegramtoken
               global telegramchannel
               elapsed_time = time.time() - lastnotification_time # Keep track of elapsed time to avoid spam

               now = datetime.now().strftime("%Y-%m-%d %H:%M:00")
               
               #Set message that alarm was triggered for specific zone
               msg = "Beam " + str(zones[PIR_PIN]) + " triggered at " + datetime.now().strftime("%Y-%m-%d %H:%M:%S")
               print (msg)
               
               #Set last triggered time for specific zone
               if pinstate == "0" and now != lasttrigtime[PIR_PIN]:
                    lasttrigtime[PIR_PIN] = datetime.now().strftime("%Y-%m-%d %H:%M:00")
                    
                    try:
                        #Update MySql database with zone trigger datetime
                        connection = pymysql.connect(host='" + dbhost + "', user='" + dbuser + "', password='" + dbpwd + "', db='" + database + "', charset='utf8mb4', cursorclass=pymysql.cursors.DictCursor)
                        with connection.cursor() as cursor:
                            sql = "UPDATE ZoneState SET State=1, EventDateTime='" + now + "' WHERE ID=" + str(zones[PIR_PIN])
                            cursor.execute(sql)
                        connection.commit() # Commit transaction

                    finally:
                        connection.close() # Close Db connection
                   
               if firstrun or elapsed_time > sendMessageSeconds:
                   firstrun = False
                   print ("MSG")
                   sendNotification(telegramtoken, telegramchannel, msg) # Send telegram notification that zone was triggered
                   
               lastnotification_time = time.time() # Update last notification time

try:
        #Get config
        with open('config.json') as json_data_file:
            data = json.load(json_data_file)

        #sSetup global vars from config
        dbhost = data['mysql']['host']
        dbuser = data['mysql']['user']
        dbpwd = data['mysql']['pwd']
        database = data['mysql']['db']
        telegramtoken = data['telegram']['token']
        telegramchannel = data['telegram']['channel']
        #Setup GPIO pins for continuous monitoring
        print ("Reading PIR status")

        #Send Telegram notification that system was started
        sendNotification(telegramtoken, telegramchannel, "System started")
        #GPIO.add_event_detect(ARMED_PIN, GPIO.RISING, callback=ARMED)
        GPIO.add_event_detect(PIR1_PIN, GPIO.FALLING, callback=MOTION, bouncetime=200)
        GPIO.add_event_detect(PIR2_PIN, GPIO.FALLING, callback=MOTION, bouncetime=200)
        GPIO.add_event_detect(PIR3_PIN, GPIO.FALLING, callback=MOTION, bouncetime=200)
        GPIO.add_event_detect(PIR4_PIN, GPIO.FALLING, callback=MOTION, bouncetime=200)
        #Loop to keep daemon running in background and monitor pin states
        while 1:
            a = 0
            time.sleep(0.3)
            ARMED(ARMED_PIN)
            SIREN(SIREN_PIN)

            if GPIO.input(ARMED_PIN):
                a = 1 #ARMED(ARMED_PIN)
            else:
                a = 0 #ARMED(ARMED_PIN)
                    
                
except KeyboardInterrupt:
    #Handle keybpard interrupt for testing in terminal
    print ("Exit")

    #Clean up pins after exit
    GPIO.cleanup()
    GPIO.setmode(GPIO.BCM)
    GPIO.setup(SIREN_PIN, GPIO.IN, GPIO.PUD_UP)
#Alarm and beam pins
GPIO.setup(ARMED_PIN, GPIO.IN)
GPIO.setup(PIR1_PIN, GPIO.IN, GPIO.PUD_UP)
GPIO.setup(PIR2_PIN, GPIO.IN, GPIO.PUD_UP)
GPIO.setup(PIR3_PIN, GPIO.IN, GPIO.PUD_UP)
GPIO.setup(PIR4_PIN, GPIO.IN, GPIO.PUD_UP)
#Lights
GPIO.setup(14, GPIO.OUT)
GPIO.output(14, 1)
GPIO.setup(15, GPIO.OUT)
GPIO.output(15, 1)
GPIO.setup(18, GPIO.OUT)
GPIO.output(18, 1)
GPIO.setup(22, GPIO.OUT)
GPIO.output(22, 1)
