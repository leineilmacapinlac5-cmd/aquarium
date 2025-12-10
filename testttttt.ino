#include <WiFi.h>
#include <HTTPClient.h>
#include <OneWire.h>
#include <DallasTemperature.h>

// ---------- IR Sensor Pins ----------
#define TRIG_PIN 5
#define ECHO_PIN 18

// ---------- Temperature Sensor Pins ----------
#define TEMP_PIN 4
OneWire oneWire(TEMP_PIN);
DallasTemperature sensors(&oneWire);

// ---------- pH Sensor Pin ----------
#define PH_PIN 34

// ---------- WiFi Credentials ----------
#define SECRET_SSID     "leineil"
#define SECRET_PASS     "12345678"

// ---------- Server ----------
const char* serverName = "http://172.19.84.27/esp32/save_data.php";


void setup() {
  Serial.begin(115200);
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  sensors.begin();

  WiFi.begin(SECRET_SSID, SECRET_PASS);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected!");
}

void loop() {
  // --- IR Water Level ---
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  long duration = pulseIn(ECHO_PIN, HIGH);
  float distanceCM = duration * 0.034 / 2;
  int waterLevelPercent = map(distanceCM * 10, 0, 1016, 100, 0); // approximate
  waterLevelPercent = constrain(waterLevelPercent, 0, 100);

  // --- Temperature ---
  sensors.requestTemperatures();
  float temperatureC = sensors.getTempCByIndex(0);

  // --- pH ---
  int phRaw = analogRead(PH_PIN);
  float phValue = phRaw * (14.0 / 4095.0);

  Serial.printf("Water Level: %d%% | Temp: %.2f°C | pH: %.2f\n", waterLevelPercent, temperatureC, phValue);

  // --- Send to PHP Server ---
  if(WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "water_level=" + String(waterLevelPercent) + 
                      "&temperature=" + String(temperatureC) + 
                      "&ph=" + String(phValue);

    int httpResponseCode = http.POST(postData);

    if(httpResponseCode>0){
      Serial.println("Data sent successfully");
    } else {
      Serial.printf("Error sending data: %d\n", httpResponseCode);
    }
    http.end();
  }

  delay(2000); // Send every 2 seconds
}