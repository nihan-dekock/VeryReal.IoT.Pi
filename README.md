# VeryReal.IoT.Pi
 Raspberry Pi IoT daemon written in Python 3 with a PHP web app (used from mobile devices) to control home electronics, receive Telegram notifications when the alarm is triggered and arm/disarm the alarm system remotely.

### Purpose
This is a prototype project that is working on my Raspberry Pi 3 Model B running Raspbian GNU/Linux 8 and the purpose is as follows:
* I am able to control electronics like lights in my house remotely via a web mobile app.
* I hooked it up with my alarm system and get Telegram notifications whenever the alarm is triggered.
* I can remotely see which alarm zone was triggered and arm or disarm the alarm system via the app.
* By configuring different GPIO pins in the app you can monitor and control different IoT devices remotely using your Pi.

**Supports**
* Raspberry Pi (Tested on Pi 3 Model B)
* Raspbian 8 with LAMP stack and Python

### Setup
* The details of the GPIO wiring of the Pi is not part of the scope of this project, please contact me if you need help, I still have some hand-drawn schematics.
* Ensure that you have the LAMP stack installed on the Pi, Apache and MySql is running and that you have firewall rules configured.
* Install Python and MySql Python bindings
```
sudo apt-get install python3
sudo apt-get install python-mysqldb
```
* Setup the MySql database using the script under /MySql/NeeMan.sql
* Rename the following files and edit the configuration properties to match your environment:
  * IoTDaemon/config.json.rename --> IoTDaemon/config.json
  * IoTDaemon/telegram.conf.rename --> IoTDaemon/telegram.conf
* To change the GPIO pin configurations for alarms, electronics, etc. edit IoTDaemon/nihanbeams.py (will be moved to config in future versions).
* Open IoTDaemon/nihanbeams.py in the Python 3 IDE on the Pi and ensure that it compiles and runs without errors.
* To make it run every time the Pi boots up edit /etc/rc.local
```
sudo nano /etc/rc.local
```
* and add the following lines to the bottom of the file: 
```
python3 /{InstallPath}/IoTDaemon/nihanbeams.py &
exit 0
```
* (Note the ampersand at the end to fork the process)
* Edit the file /www/html/scripts/app.js and change the values of <b>jsonUrlLocal</b> and <b>jsonUrlRemote</b> to point to your Pi's apache server.
* Rename the following file and edit the configuration properties to match your environment:
  * www/html/config.php.rename --> www/html/config.php
* If all went well, the app should now work, I will make setup easier in future versions :-)



### TODO
* Implement better security, currently in config files for prototyping purposes.
* Refactor code and perform better error handling.
* Implement BootStrap styles on web app.
* Make web app configurable to easily edit electronics, monitoring zones, styles, etc.
* Explore .NET core version running in Docker containers. (Yes .NET Core now a GPIO library)


#### Authors
* [nihan-dekock](https://github.com/nihan-dekock)


#### License
This project is licensed under the GNU License - see the [LICENSE.md](LICENSE.md) file for details...
