<?php


header('Content-Type: text/plain');
   
   
function logf($s)
{
 error_log("$s\n", 3, "/var/www/html/puller/puller.log");
} 
 
 
 $retwith= ( isset($_GET['retwith']) ? $_GET['retwith'] : ''); 
 $pullfrom= ( isset($_GET['pullfrom']) ? $_GET['pullfrom'] : ''); 

 $pull= ( isset($_GET['pull']) ? $_GET['pull'] : ''); 
 $pullx = explode(",", $pull);
 $mods=[];
 $mods['lp1']=0;
 $mods['lp2']=0;
 $mods['ll1']=1;	// da hängt der puller per html dran
 $mods['ll2']=0;
 $mods['soc1']=0;
 $mods['soc2']=0;
 $mods['evu']=0;
 $mods['wr1']=0;
 $mods['wr2']=0;
 $mods['bat']=0;
 foreach( $pullx as $p)  $mods[$p]=true;
 logf( print_r($mods,true) );

 logf("retwith:$retwith pullfrom:$pullfrom pull:$pull");
 		  
		    
  
 $myConfigFile = $_SERVER['DOCUMENT_ROOT'].'/openWB/openwb.conf';

// prepare key/value array
 $settingsArray = [];

  try {
		if ( !file_exists($myConfigFile) ) {
			throw new Exception('Konfigurationsdatei nicht gefunden.');
		 }
		 
		// first read config-lines in array
 		$settingsFile = file($myConfigFile);

	 	foreach($settingsFile as $line) 
 		{
			// split line at char '='
			$splitLine = explode('=', $line, 2);
			// trim parts
			$splitLine[0] = trim($splitLine[0]);
			$splitLine[1] = trim($splitLine[1]); // do not trim single quotes, we will need them later
			// push key/value pair to new array
			if( preg_match("/mqtt/" , $splitLine[1]) )
				$settingsArray[$splitLine[0]] = $splitLine[1];
		}
    } catch ( Exception $e ) {
		$msg = $e->getMessage();
		echo "<script>alert('$msg');</script>";
	}	
 logf( print_r($settingsArray, true) );
/*
    [evsecon] => mqttevse
    [evsecons1] => mqttevse
    [ladeleistungs1modul] => mqttlllp2
    [wattbezugmodul] => bezug_mqtt
    [pvwattmodul] => wr_mqtt
    [socmodul] => soc_mqtt
    [socmodul1] => soc_mqtt
    [speichermodul] => speicher_mqtt

*/ 
 if( $mods['lp1']  &&   $settingsArray['evsecon'] != 'mqttevse' ) $mods['lp1']=0;
 if( $mods['lp2']  &&   $settingsArray['evsecons1'] != 'mqttevse' ) $mods['lp2']=0;
 // imemer da hier der puller dranghängt if( $mods['ll1']  &&   $settingsArray['ladeleistungmodul'] != 'mqttevse' ) $mods['ll1']=0;
 if( $mods['ll2']  &&   $settingsArray['ladeleistungs1modul'] != 'mqttlllp2' ) $mods['ll2']=0;
 if( $mods['evu']  &&   $settingsArray['wattbezugmodul'] != 'bezug_mqtt' ) $mods['evu']=0;
 if( $mods['wr1']  &&   $settingsArray['pvwattmodul'] != 'wr_mqtt' ) $mods['wr1']=0;
 if( $mods['wr2']  &&   $settingsArray['pvwattmodul2'] != 'wr_mqtt2' ) $mods['wr2']=0;
 if( $mods['bat']  &&   $settingsArray['speichermodul'] != 'speicher_mqtt' ) $mods['bat']=0;
  
 logf( print_r($mods,true) );
 $sum=0;
 foreach($mods as $m)
    if($m==1) 
      $sum++;
 if($sum>0)
  {
   logf(" $sum aktiver module, starte daemon");
   exec("./puller.sh 1 &", $output, $retval);
  }
  else
  {
   logf(" keine aktiven module, Stoppe daemon");
   exec("./puller/puller.sh 0 &", $output, $retval);
  }
 logf(print_r($output,true));
  
   
# exec("./soc_citigo/getsoc.sh $para >>./soc_citigo/getsoc.log 2>&1 & ", $output,$retval);
# echo "Returned with status $retval and output:\n";
# print_r($output);
# echo "\n";
 

 # Nehme letzten ermittelten Wert mit zurueck, warte nicht auf das Ende der aktuellen abfrage
 $x  = file_get_contents("/var/www/html/openWB/ramdisk/$retwith");
 echo "$x"
?>
