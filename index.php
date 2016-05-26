<?php
    // Script zur Umformatierung der mongolab-Nighscout-Daten, damit diese von Garmin IQ weiterverwendet werden können.
    // Andreas May, Hamburg, www.laufen-mit-diabetes.de
	
	// Mongolab-URL mit der übertragenen Api ergänzen
    $_datenbank = $_GET[database];
    if($_datenbank == "") { $_datenbank = "nightscout"; }
    $_url = "https://api.mongolab.com/api/1/databases/".$_datenbank."/collections/entries?l=26&s={%27date%27:-1}&f={%27_id%27:0,%20%27direction%27:1,%20%27sgv%27:1,%27date%27:1}&apiKey=".$_GET["api"];
 
    $_lang = $_GET["sprache"];
    
    // Auswertung der Abfrage, Löschen der eckigen Klammer (eine für Garmin ungültige json-Abfrage)
    $_result = implode('', file($_url));
    // $_buffer = substr("$_result", 2, -2);
    $_daten = json_decode($_result);

    $_ausgabe = "[";
    for($i = 0; $i < sizeof($_daten); $i++) {
        if ($_daten[$i]->{'sgv'} > 10 && $_daten[$i]->{'date'}) {
            $_messzeit = substr($_daten[$i]->{'date'}, 0, -3);
            $_differenz = time() - $_messzeit;
            $_minuten = (int)($_differenz / 60);
            if ($_lang == 0) {
                if ($_minuten > 99) {
                    $_minutenverz = $_minuten . ' min.';
                } else {
                    $_minutenverz = $_minuten . ' minute' . ($_minuten != 1 ? 's' : '');
                }
            } else {
                if ($_minuten > 99) {
                    $_minutenverz = $_minuten . ' Min.';
                } else {
                    $_minutenverz = $_minuten . ' Minute' . ($_minuten != 1 ? 'n' : '');
                }
            }
            $_blutzucker = $_daten[$i]->{'sgv'};
            $_richtung = $_daten[$i]->{'direction'};

	    // Nummerierung der Pfeile, um Pfeilausgabe am Garmin vorzubereiten
	    if ($_richtung == "DoubleUp") {$_pfeil = 7; }
	    else if ($_richtung == "SingleUp") {$_pfeil = 6; }
	    else if ($_richtung == "FortyFiveUp") {$_pfeil = 5; }
            else if ($_richtung == "Flat") {$_pfeil = 4; }
	    else if ($_richtung == "FortyFiveDown") {$_pfeil = 3; }
	    else if ($_richtung == "SingleDown") {$_pfeil = 2; }
	    else if ($_richtung == "DoubleDown") {$_pfeil = 1; }
            else { $_pfeil = 0; }
            
            $_ausgabe .= '{"blutzucker":"'.$_blutzucker.'", "differenzMinuten":'.$_minuten.', "verzoegerung":"'.$_minutenverz.'", "pfeil":"'.$_pfeil.'"},';
            
        }     
    }
    $_ausgabe = substr($_ausgabe, 0, -1);
    $_ausgabe .= "]";
    echo $_ausgabe;

?>
