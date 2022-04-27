# Puller
Mqtt Puller für openWB

Diese Project stellt eine Hilfsfunktion bereit die beim testen der openWB[_lite] verwendet werden kann.
Sie dient dazu das die zu testende OpenWB teilweise einer Master-OpenWB folgt, statt alles selbst zu erledigen.
Bei den EVU/WR/BAT Modulen ginge das ja, aber ein Angeschlossenen Auto und den Ladeleistungs-Zähler der original Wallbox kann man nicht so einfach abfragen.

Vorgehen

Bei der zu steuernden openWB (client) beim der Ladepunkten folgendes Einstellen
LP1 
- Anbindung: MQTT
- Ladeleistungmodul: HTTP
- URl-Strom Phase 3: http://192.168.208.61/puller/puller.php?retwith=lla3&pullfrom=192.168.208.64&pull=lp1,ll1,soc1,evu,wr1,bat,lp2,ll2,soc2

Die Parameter bedeuten:
-  http://192.168.208.61/puller/puller.php Dort ist das Script abgelegt. Es muss auf dem "Clienten"' installiert werden.
-  retwith=lla3     Liefere den bisherigen Ramdiskwert von lla3 als Returnwert des main.sh um die Daten die per MQTT geholt werden nicht zu verfälschen
-  pullfrom=192.168.208.64  IP Adresse der "Master" openWB, also die Quelle der Daten
-  pull=lp1,ll1,soc1,lp2,ll2,soc2,evu,wr1,bat Liste von Modulen die unterstütz werden sollen. Hier alle.

Alle Module die jetzt als Type "MQTT" beim Clienten eingetragen werden, bekommen Ihre Daten per Subcrition aus dem MQTT des Masters
Hierbei werden alle Werte an das jeweilig /set/ topic gesendet.
```

	lp1 
		openWB/lp/1/boolPlugStat   -> openWB/set/lp/1/plugStat
		openWB/lp/1/boolChargeStat -> openWB/set/lp/1/chargeStat
	lp2
		openWB/lp/2/boolPlugStat   -> openWB/set/lp/2/plugStat
		openWB/lp/2/boolChargeStat -> openWB/set/lp/2/chargeStat
	ll1
		openWB/lp/1/ChargePointEnabled  -> openWB/set/lp/1/ChargePointEnabled
		openWB/lp/1/W 		-> openWB/set/lp/1/W
		openWB/lp/1/kWhCounter	-> openWB/set/lp/1/kWhCounter
		openWB/lp/1/VPhase1	-> openWB/set/lp/1/VPhase1
		openWB/lp/1/VPhase2	-> openWB/set/lp/1/VPhase2
		openWB/lp/1/VPhase3	-> openWB/set/lp/1/VPhase3
		openWB/lp/1/APhase1	-> openWB/set/lp/1/APhase1
		openWB/lp/1/APhase2	-> openWB/set/lp/1/APhase2
		openWB/lp/1/APhase3	-> openWB/set/lp/1/APhase3
		openWB/lp/1/HzFrequenz	-> openWB/set/lp/1/HzFrequenz
	ll2
		openWB/lp/2/ChargePointEnabled ->  openWB/set/lp/2/ChargePointEnabled
		openWB/lp/2/W 		-> openWB/set/lp/2/W
		openWB/lp/2/kWhCounter  -> openWB/set/lp/2/kWhCounter
		openWB/lp/2/VPhase1 -> openWB/set/lp/2/VPhase1
		openWB/lp/2/VPhase2 -> openWB/set/lp/2/VPhase2
		openWB/lp/2/VPhase3 -> openWB/set/lp/2/VPhase3
		openWB/lp/2/APhase1 -> openWB/set/lp/2/APhase1
		openWB/lp/2/APhase2 -> openWB/set/lp/2/APhase2
		openWB/lp/2/APhase3 -> openWB/set/lp/2/APhase3
	evu
		openWB/evu/W - >         openWB/set/evu/W
		openWB/evu/VPhase1 -> openWB/set/evu/VPhase1
		openWB/evu/VPhase2 -> openWB/set/evu/VPhase2
		openWB/evu/VPhase3 -> openWB/set/evu/VPhase3
		openWB/evu/APhase1 -> openWB/set/evu/APhase1
		openWB/evu/APhase2 -> openWB/set/evu/APhase2
		openWB/evu/APhase3 -> openWB/set/evu/APhase3
		openWB/evu/Hz      -> openWB/set/evu/HzFrequenz
		openWB/evu/WhImported -> openWB/set/evu/WhImported
		openWB/evu/WhExported -> openWB/set/evu/WhExported
	wr1
		openWB/pv/W - >  openWB/set/pv/
		openWB/pv/WhCounter   -> openWB/set/pv/WhCounte
		openWB/pv/1/WhCounter -> openWB/set/pv/1/WhCounte
		openWB/pv/DailyYieldKwh   -> openWB/pv/DailyYieldKw
		openWB/pv/MonthlyYieldKwh -> openWB/pv/MonthlyYieldKw
		openWB/pv/YearlyYieldKwh  -> openWB/pv/YearlyYieldKw
	bat
		openWB/housebattery/WhImported -> openWB/set/houseBattery/WhImporte
		openWB/housebattery/WhExported -> openWB/set/houseBattery/WhExporte
		openWB/housebattery/%Soc       -> openWB/set/houseBattery/%So
		openWB/housebattery/soctarget  -> openWB/housebattery/soctarge
		openWB/housebattery/faultState -> openWB/set/houseBattery/faultStat
		openWB/housebattery/faultStr   -> openWB/set/houseBattery/faultSt
		openWB/housebattery/W          -> openWB/set/houseBattery/
	soc1
		openWB/lp/1/%Soc  -> openWB/set/lp/1/%Soc
		openWB/lp/1/socKM -> openWB/set/lp/1/socKM
	soc2
		openWB/lp/2/%Soc  -> openWB/set/lp/2/%Soc
		openWB/lp/2/socKM -> openWB/set/lp/2/socKM

´´´    
    

