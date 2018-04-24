#Will contain the necessary scripting to clone and setup the application for a RPI Zero Node
#Untested - currently a notepad until testing can be carried out
sudo apt-get update
sudo apt-get upgrade
pip install pySerial
git clone git://github.com/requests/requests.git
cd requests
pip install .
sudo apt-get install daemontools daemontools-run
sudo mkdir /etc/service/RAQSP
cp /home/pi/RAQSP/run /etc/service/RAQSP/run
sudo chmod u+x /etc/service/RAQSP/run
shutdown -r now
