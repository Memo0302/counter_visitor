# counter_visitor

link to this project in wokwi "https://wokwi.com/projects/372862573787554817"

link to this project in tinkercad  "https://www.tinkercad.com/things/cY48wTwWRQx-pircountervisitor"



Code Review and MySQL Integration Report:

The provided code is intended to count visitors using two PIR sensors and send the visitor count to a MySQL database via an HTTP POST request. Here's a detailed review of the code and suggestions for integrating it with MySQL using ESP32:

Libraries:
The code includes the necessary libraries: WiFi.h and HTTPClient.h. These libraries provide the required functionality for establishing a Wi-Fi connection and making HTTP requests, respectively.

Wi-Fi Connection:
The code attempts to connect to a Wi-Fi network using the provided SSID and password. It waits until the connection is successfully established before proceeding with the program execution. This ensures that the ESP32 is connected to the network before attempting to send data.

PIR Sensor Configuration:
Two PIR sensors are defined with their corresponding pin numbers: PIR1_PIN and PIR2_PIN. The pin modes for both sensors are set to INPUT mode correctly.

Visitor Counting:
The loop() function continuously checks the status of the PIR sensors. It determines if a visitor has entered by monitoring the state changes of the sensors.

PIR1: The code checks if motion is detected by PIR1. If motion is detected and b_PIR1_active is false, it sets b_PIR1_active to true and checks if lastRIPdetected is 0. If it is 0, it sets lastRIPdetected to 1 and prints "Visit started" to the serial monitor.
PIR2: If lastRIPdetected is 1 (indicating the visitor has passed through PIR1) and motion is detected by PIR2, the visitor count is incremented, lastRIPdetected is reset to 0, and the visitor count is sent to the PHP script via the sendVisitorCount() function. A message with the visitor count is printed to the serial monitor.
Serial Output:
The code provides relevant messages and information via the serial monitor. The serial output includes messages such as the successful Wi-Fi connection, visitor count, and HTTP response codes.

Delay:
A delay of 5 seconds is added at the end of each iteration of the loop() function using the delay() function. This delay ensures that there is a time buffer between visitor detections and prevents excessive requests.

MySQL Integration Suggestions:

To integrate the code with a MySQL database, you need to make the following modifications:

Set up a MySQL database: Ensure that you have a MySQL database created with the necessary table to store the visitor count.

Modify the PHP script:
a. The provided URL should point to the PHP script that will handle the HTTP POST request and insert the visitor count into the MySQL database.
b. Update the PHP script to establish a connection to the MySQL database and insert the received visitor count into the appropriate table.

Update the sendVisitorCount() function:
a. Replace the provided URL with the URL of your PHP script that handles the database insertion.
b. Make sure the postData string is constructed correctly to match the expected format by the PHP script.

Verify the HTTP response and error handling:
a. Correct the variable name httpResponseCode to httpCode within the sendVisitorCount() function for consistency.
b. Implement error handling to check for connection failures or HTTP request errors. You can add conditions to handle different response codes appropriately.

Ensure that the ESP32 has access to the network where the MySQL database resides. Make sure the ESP32 is connected to the correct network and that the MySQL server is accessible from the ESP32's network.

By following these steps, you can integrate the code with a MySQL database and successfully store the visitor count received from the ESP32.


```
#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid = "xxxxxx"; // mean my name the network
const char* password = "xxxxx"; // mean my password
String URL = "http://192.168.8.107/visitor/counter_visitor.php";
String URL = "http://xxxxxx/visitor/counter_visitor.php";// xxxx mean my ip adress
const int PIR1_PIN = 26;
const int PIR2_PIN = 27;

int visitors = 0;
int lastRIPdetected = 0;
bool b_PIR1_active = false;

WiFiClient client;

void setup() {
  pinMode(PIR1_PIN, INPUT);
  pinMode(PIR2_PIN, INPUT);

  Serial.begin(9600);
  
  WiFi.begin(ssid, password);
  Serial.println("Connecting to WiFi");
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
   Serial.println("");
   Serial.println("Visitors are welcome");
  }

void loop() {
  // ----------- check PIR1 ----------------
  if (digitalRead(PIR1_PIN) == HIGH) {
    if (!b_PIR1_active) {
      b_PIR1_active = true;

      if (lastRIPdetected == 0) {  
        lastRIPdetected = 1;
        Serial.println("Visit started");
      }
    }
  } else {
    b_PIR1_active = false;  // reenable PIR1
    if (lastRIPdetected == 1) {  // if we were in PIR1 before
      lastRIPdetected = 2;  // Set lastRIPdetected to 2 to indicate PIR1 has been crossed
    }
  }

  // ----------- check PIR2 ----------------
  if (lastRIPdetected == 2 && digitalRead(PIR2_PIN) == HIGH) {
    visitors++;
    lastRIPdetected = 0;
    Serial.println("Visitor entered. Visitors: " + String(visitors));
    sendVisitorCount(visitors);  // Send visitor count to PHP script
    
  }
  
}

void sendVisitorCount(int count) {
  // Construct the POST request
  String postData = "count=" + String(count);
  HTTPClient http;
  // Make the request
  http.begin(URL);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  int httpCode = http.POST(postData);
  // Check the response
  if (httpCode > 0) {
    Serial.print("HTTP Response code: ");
    Serial.println(httpCode);
    String response = http.getString();
    Serial.println(response);
  } else {
    Serial.print("Error code: ");
    Serial.println(httpCode);
    }
 
  http.end();
}
