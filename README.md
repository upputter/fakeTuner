# fakeTuner für Sagem My Du@l Radio 700

## Motivation
Anfang 2025 hat der Webradio-Listendienst vTuner.com die Unterstütztung für mein gutes altes WLAN-Radio "My Du@l Radio 700" eingestellt. Die Subdomain `sagem.vtuner.com` ist einfach nicht mehr! :(

Da ich das WLAN-Radio nicht als Schrott einstufen wollte - es funktionierte bis zur Dienstabschaltung ja einwandfrei - habe ich nach einer Lösung gesucht, dem Gerät die Radiosender wieder verfügbar zu machen.

## Nachforschung
Die Dienstleistung von vTuner besteht darin, die Adressen der Webradiostreams den Endgeräten im XML-Format zur Verfügung zu stellen.

Ich bin nicht der erste Mensch auf dem Planeten, der von der Abschaltung eines vTuner-Dienstes betroffen ist. Namhafte HiFi-Hersteller wie Marantz, Denon oder Sony haben ebenfalls vTuner genutzt und lassen ihre Kunden nach einer Abschaltung teilweise im Regen stehen.

Es gibt aber Hilfen. Mögliche Alternativen sind Werkzeuge wie [YCast](https://github.com/milaq/YCast) oder [Ytuner](https://github.com/coffeegreg/YTuner). Sie stellen den Geräten die benötigten Informationen wieder bereit.
Innerhalb des lokalen Netzwerkes wird hierfür die `*.vtuner.com`-Adresse auf einen eigenen Dienst umgeleitet.

Für mein altes Sagem funktionieren diese Alternativen leider nicht. Die XML-Daten sind falsch formatiert! Das Radio zeigt nur angeschnittene Einträge und ungültige Streams an.

## Lösung
Egal wie, das WLAN-Radio muss mit einem alternativen Dienst kommunizieren, um die Adressen der Radiosender zu erhalten.
Hierzu habe ich ein kleines Script mit dem Arbeitstitel `fakeTuner` geschrieben.

### Router konfigurieren

Nach Möglichkeit im Router `sagem.vtuner.com` auf einen Rechner im lokalen Netz umleiten.

Wenn der Router bspw. DNSmasq zur Verfügung stellt, diesen Dienst aktivieren und folgende Option setzen: 
```
address=/.vtuner.com/192.168.0.123
```
**192.168.0.123** ist die IP-Adresse des Zielrechners auf dem der Dienst läuft, der die Daten bereitstellen soll.

### Radiosender konfigurieren
In der Datei `stationsList.php` wird ein Array erwartet, welches aus den jeweiligen Einträgen der Radiosender besteht.
Hier ein Beispiel für zwei Radiosender. Nur die Werte `title` und `url` werden benötigt.
```php
$stations = [
    [
        'title' => 'NDR Kultur',
        'url' => 'http://icecast.ndr.de/ndr/ndrkultur/live/mp3/128/stream.mp3?1728425224079&aggregator=web',
    ],
    [
        'title' => 'DLF',
        'url' => 'http://st01.sslstream.dlf.de/dlf/01/128/mp3/stream.mp3?aggregator=web'
    ]
];
```
Die Adressen der Sender können auf https://radio-browser.info gefunden werden. Das Sagem unterstützt MP3-Streams.

### FakeTuner starten

Das Gerät beginnt alle Sitzungen mit der Anfrage an die Adresse `http://sagem.vtuner.com/setupapp/amit/asp/BrowseXML/loginXML.asp`. Ein Webserver muss auf dem Zielrechner (Bsp.-IP 192.168.0.123) auf Port `80` laufen.

Entweder der Webserver stellt die gewünschte Ordnerstruktur bereit oder per `.htaccess` / `vHost`-Konfiguration werden die Anfragen umgeleitet (es wird von einem Apache Webserver ausgegangen).

Ausgehend von folgender beispielhaften Struktur ...

```
|- Webserver-Wurzel
  |- .htaccess
  |- /fakeTuner/
    |- /index.php
    |- /stationsList.php
```
 .. kann die `.htaccess`-Datei so aussehen:
 ```
 <IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteRule ^setupapp/amit/asp/BrowseXML/loginXML\.asp$ fakeTuner/vTuner\.php
</IfModule>
```

Alle Anfragen werden auf den `fakeTuner` umgeleitet.

## Wie es funktioniert
Inspiriert von den zuvor genannten alternativen Werkzeugen (YCast/YTuner) ermöglicht der `fakeTuner` eine Pseudo-Anmeldung für das WLAN-Radio und spielt eine kleine Liste von Webradios aus. Diese können dann als lokale Favoriten (Herzchen-Taste auf der Fernbedienung) gespeichert werden. Für das schnöde Hören der Radiosender, muss die hier gezeigte Lösung nicht genutzt werden, nur wenn die Liste der lokal gespeicherten Sender aktualisiert werden soll.
Der Trick in der Lösung ist die Formatierung der XML-Daten. Das Sagem WLAN-Radio möchte nämlich für jedes XML-Tag eine neue Zeile - ohne Einrückungen!

So kann eine Liste mit einem Eintrag (NDR-Kultur) aussehen, die vom Sagem WLAN-Radio gelesen werden kann:
```xml
<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>
<ListOfItems>
<ItemCount>8</ItemCount>
<Item>
<ItemType>Station</ItemType>
<StationId>FAV_3f1c3e17a18fa43ec94852424cad5def</StationId>
<StationName>NDR Kultur</StationName>
<StationUrl>http://icecast.ndr.de/ndr/ndrkultur/live/mp3/128/stream.mp3?1728425224079&aggregator=web</StationUrl>
<StationDesc>Klassik</StationDesc>
<StationFormat>Klassik</StationFormat>
<StationBandWidth>None</StationBandWidth>
<StationMime>MP3</StationMime>
<Relia>3</Relia>
</Item>
</ListOfItems>
```

