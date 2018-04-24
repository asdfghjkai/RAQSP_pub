#include <SimpleDHT.h>

/*Arduino slave code for RAQSP
 * https://github.com/kaizaii/RAQSP_pub
 * Last Updated: 12 March 2018
 * Version 0.2.0
 * Release Ready: YES (TESTING) with NO COMPILE OR RUNTIME ERRORS
 * Changes: Code made tidier and comments added as a method of explanation
 *
 */


String Version = "0.2.0";

//DHT Sensor
int dht_Pin = 2;
SimpleDHT11 dhtsensor; //using the SIMPLEDHT library

//MQ4
int mq4_pin = A0; //Analogue (analog) pin which the MQ4 sensor is read from

//MQ7
int mq7_pin = A1; //Read in pin (Analogue/analog)
int mq7_5v = 4; //These two pins control the transistors and output voltage through potential divider
int mq7_lowv = 5;

//Run once
void setup() {
  Serial.begin(9600); //begin the serial communication
}

//Run until reboot
void loop() {
  //This code executes when there is data waiting on the serial buffer, such as when a command is sent
  if (Serial.available() > 0)
  {
    String option = Serial.readString(); //Read in the contents of the serial buffer
    if (option == "TEMP")
    {
      Serial.println(getTemperature()); //NOTE: All outputs include new line characters and carriage returns
    }                                   //Therefore account for \r\n on host side
    else if (option == "HUMIDITY")
    {
      Serial.println(getHumidity());
    }
    else if (option == "MQ4")
    {
      Serial.println(getMQ4(mq4_pin));
    }
    else if (option == "MQ7")
    {
      Serial.println(getMQ7());
    }
    else if (option == "MQ4B")
    {
      Serial.println(getMQ4Beta());
    }
  }
}
//Function that returns the temperature value from the sensor - centigrade
double getTemperature()
{
  delay(500); //allow the sensor to take a reading - slow reads
  byte temperature = 0;
  dhtsensor.read(dht_Pin, &temperature, NULL, NULL);
  return (double) temperature; //implicit conversion
}

//Returns Humidity Value (%)
double getHumidity()
{
  delay(500); //allow the sensor to take a reading
  byte humidity = 0;
  dhtsensor.read(dht_Pin, NULL, &humidity, NULL);
  return (double) humidity; //implicit conversion
}

//A method for calculating CH4 levels, using a tutorial that computes the value locally
//Ref: https://www.geekstips.com/mq4-sensor-natural-gas-methane-arduino/
float getMQ4(int readPin)
{
  //TAKEN FROM AN ONLINE SOURCE _ THANK YOU
  float m = -0.318; //Slope
  float b = 1.133; //Y-Intercept
  float sensor_volt; //Define variable for sensor voltage
  float RS_air; //Define variable for sensor resistance
  float R0; //Define variable for R0
  float sensorValue; //Define variable for analog readings
  for (int x = 0 ; x < 500 ; x++) //Start for loop
  {
    sensorValue = sensorValue + analogRead(readPin); //Add analog values of sensor 500 times
  }
  sensorValue = sensorValue / 500.0; //Take average of readings
  sensor_volt = sensorValue * (5.0 / 1023.0); //Convert average to voltage
  RS_air = ((5.0 * 10.0) / sensor_volt) - 10.0; //Calculate RS in fresh air
  R0 = RS_air / 4.4; //Calculate R0


  //then apply
  float RS_gas; //Define variable for sensor resistance
  float ratio; //Define variable for ratio
  sensorValue = analogRead(readPin); //Read analog values of sensor
  RS_gas = ((5.0*10.0)/sensor_volt)-10.0; //Get value of RS in a gas
  ratio = RS_gas/R0;  // Get ratio RS_gas/RS_air

  double ppm_log = (log10(ratio)-b)/m; //Get ppm value in linear scale according to the the ratio value
  double ppm = pow(10, ppm_log); //Convert ppm value to log scale
  float percentage = ppm/10000; //Convert to percentage
  return ppm; //or percentage dependant on how much is present

}


//Mathematics calculated using a tutorial from sparkfun
//Ref: https://learn.sparkfun.com/tutorials/hazardous-gas-monitor/build-it
double getMQ7()
{
  //Start by heating the sensor
  digitalWrite(mq7_5v, HIGH); //transistor switching
  delay(60000); //wait 60 seconds to heat
  digitalWrite(mq7_5v, LOW);
  delay(50); //50ms to allow switching
  digitalWrite(mq7_lowv, HIGH);
  delay(90000); //90 seconds at a low value
  int readV = analogRead(mq7_pin);
  digitalWrite(mq7_lowv, LOW); //turn off all power to conserve resources
  double ppm = 3.027*exp(1.0698*(readV*5.0/1023)); //taken from tutorial
  return ppm;
}
//An alternative method of calculating the CH4 values using mathematical approximation
//Ref: https://learn.sparkfun.com/tutorials/hazardous-gas-monitor/calculate-gas-sensor-ppm
double getMQ4Beta()
{
  double value = analogRead(mq4_pin);
  double ppm = 10.938*exp(1.7742*(value*5.0/1023));
  return ppm;
}
